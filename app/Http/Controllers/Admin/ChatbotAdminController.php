<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\User;

class ChatbotAdminController extends BaseController
{
    public function index()
    {
        // Dashboard with stats and graphs
        $totalLogs = DB::table('chatbot_logs')->count();
        $totalTokens = DB::table('chatbot_logs')->sum('api_tokens_used');
        $totalUsers = DB::table('chatbot_quotas')->count();
        
        // Last 30 days usage data for graph
        $usageData = DB::table('chatbot_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(api_tokens_used) as tokens'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top users by usage
        $topUsers = DB::table('chatbot_logs')
            ->select('user_id', DB::raw('COUNT(*) as chat_count'), DB::raw('SUM(api_tokens_used) as total_tokens'))
            ->groupBy('user_id')
            ->orderByDesc('chat_count')
            ->limit(10)
            ->get();

        // Enrich with user emails
        foreach ($topUsers as $topUser) {
            $user = User::find($topUser->user_id);
            $topUser->email = $user ? $user->email : 'Unknown';
        }

        return view('admin::Chatbot.index', compact('totalLogs', 'totalTokens', 'totalUsers', 'usageData', 'topUsers'));
    }

    public function logs(Request $request)
    {
        // Paginated chat history with filters
        $query = DB::table('chatbot_logs')
            ->join('users', 'chatbot_logs.user_id', '=', 'users.id')
            ->select('chatbot_logs.*', 'users.email as user_email')
            ->orderByDesc('chatbot_logs.created_at');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('chatbot_logs.user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('chatbot_logs.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('chatbot_logs.created_at', '<=', $request->date_to);
        }

        // Filter by tool
        if ($request->filled('tool')) {
            $query->where('chatbot_logs.tool_called', $request->tool);
        }

        $logs = $query->paginate(50);
        $users = User::orderBy('email')->get();

        return view('admin::Chatbot.logs', compact('logs', 'users'));
    }

    public function quotas()
    {
        // User quota management - show all users
        $quotas = DB::table('chatbot_quotas')
            ->join('users', 'chatbot_quotas.user_id', '=', 'users.id')
            ->select('chatbot_quotas.*', 'users.email as user_email')
            ->orderBy('users.email')
            ->paginate(50);

        // Get users without quotas
        $usersWithoutQuotas = User::whereNotIn('id', function($query) {
            $query->select('user_id')->from('chatbot_quotas');
        })->orderBy('email')->get();

        return view('admin::Chatbot.quotas', compact('quotas', 'usersWithoutQuotas'));
    }

    public function createQuota(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:chatbot_quotas,user_id',
            'daily_limit' => 'required|integer|min:0',
            'monthly_limit' => 'required|integer|min:0',
        ]);

        DB::table('chatbot_quotas')->insert([
            'user_id' => $request->user_id,
            'daily_limit' => $request->daily_limit,
            'monthly_limit' => $request->monthly_limit,
            'daily_used' => 0,
            'monthly_used' => 0,
            'last_reset_daily' => now()->toDateString(),
            'last_reset_monthly' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.chatbot.quotas')->with('success', 'Quota created successfully');
    }

    public function updateQuota(Request $request, $userId)
    {
        $request->validate([
            'daily_limit' => 'required|integer|min:0',
            'monthly_limit' => 'required|integer|min:0',
        ]);

        DB::table('chatbot_quotas')
            ->where('user_id', $userId)
            ->update([
                'daily_limit' => $request->daily_limit,
                'monthly_limit' => $request->monthly_limit,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.chatbot.quotas')->with('success', 'Quota updated successfully');
    }
}

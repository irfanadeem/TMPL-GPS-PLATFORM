<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Helpers\Dashboard\DashboardManager;
use Tobuli\Entities\CronJobCalculation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * @var DashboardManager
     */
    private $dashboardManager;

    public function __construct(DashboardManager $dashboardManager)
    {
        parent::__construct();

        $this->dashboardManager = $dashboardManager;
    }

    public function index(Request $request)
    {
        // Reset dashboard dates to today on page load
        if (!$request->ajax()) {
            $today = Carbon::today()->format('Y-m-d');
            $settings = $this->user->getSettings('dashboard');
            
            if (isset($settings['blocks'])) {
                $modified = false;
                foreach ($settings['blocks'] as &$blockConfig) {
                    if (isset($blockConfig['options'])) {
                        if (isset($blockConfig['options']['from_date'])) {
                            $blockConfig['options']['from_date'] = $today;
                            $modified = true;
                        }
                        if (isset($blockConfig['options']['to_date'])) {
                            $blockConfig['options']['to_date'] = $today;
                            $modified = true;
                        }
                    }
                }
                
                if ($modified) {
                    $this->user->setSettings('dashboard', $settings);
                }
            }
        }

        $blocks = [];

        $settings = $this->user->getSettings('dashboard');

        foreach ($settings['blocks'] as $block => $config) {
            if ( ! $this->dashboardManager->getConfig($block, 'enabled'))
                continue;

            $blocks[$block] = $this->dashboardManager->getFrame($block);
        }

        if ($request->ajax())
            return view('front::Dashboard.modal', ['blocks' => $blocks]);

        return view('front::Dashboard.index', ['blocks' => $blocks]);
    }

    public function blockContent()
    {
        $content = $this->dashboardManager->getContent(request('name'));

        if (is_null($content))
            return ['status' => 0];

        return response()->json(['status' => 1, 'html' => $content]);
    }

    public function updateConfig()
    {
        $config = request('dashboard');

        $this->user->setSettings('dashboard', $config, true);

        return response()->json(['status' => 1]);
    }
    public function updateConfigAllBlock()
    {
        $config = request('dashboard');
        $settings = $this->user->getSettings('dashboard');
        
        foreach ($settings['blocks'] as $key => &$block) {
        if (isset($block['options'])) {
            $block['options']['from_date'] = $config['blocks']['wc_board_form']['options']['from_date'];
            $block['options']['to_date'] = $config['blocks']['wc_board_form']['options']['to_date'];
            $block['options']['department'] = $config['blocks']['wc_board_form']['options']['department'];;
            }
        }
        $this->user->setSettings('dashboard', $settings, true);

        return response()->json(['status' => 1]);
    }

    /**
     * Get enhanced dashboard data using ReportDashboardCommand calculations
     */
    public function getEnhancedDashboardData(Request $request)
    {
        $fromDate = $request->get('from_date', Carbon::today()->format('Y-m-d'));
        $toDate = $request->get('to_date', Carbon::today()->format('Y-m-d'));
        $department = $request->get('department', null);

        $cacheKey = "enhanced_dashboard_data_{$fromDate}_{$toDate}_{$department}";
        
        return Cache::remember($cacheKey, 300, function() use ($fromDate, $toDate, $department) {
            return $this->calculateEnhancedDashboardData($fromDate, $toDate, $department);
        });
    }

    /**
     * Calculate enhanced dashboard data using CronJobCalculation data
     */
    private function calculateEnhancedDashboardData($fromDate, $toDate, $department = null)
    {
        // Get user's accessible devices with department filter
        $devicesQuery = $this->user->accessibleDevicesWithGroups();
        if ($department) {
            $devicesQuery->where('user_device_pivot.group_id', $department);
        }
        $devices = $devicesQuery->get();
        $deviceIds = $devices->pluck('id')->toArray();
        

        if (empty($deviceIds)) {
            return [
                'fuel_data' => [],
                'movement_data' => [],
                'time_data' => [],
                'summary' => []
            ];
        }

        // Get fuel statistics
        $fuelData = $this->getFuelStatistics($fromDate, $toDate, $deviceIds);
        
        // Get movement statistics
        $movementData = $this->getMovementStatistics($fromDate, $toDate, $deviceIds);
        
        // Get time statistics
        $timeData = $this->getTimeStatistics($fromDate, $toDate, $deviceIds);
        
        // Get summary data
        $summaryData = $this->getSummaryData($fromDate, $toDate, $deviceIds);

        return [
            'fuel_data' => $fuelData,
            'movement_data' => $movementData,
            'time_data' => $timeData,
            'summary' => $summaryData,
            'devices_count' => count($deviceIds),
            'date_range' => [
                'from' => $fromDate,
                'to' => $toDate
            ]
        ];
    }

    /**
     * Get fuel statistics from CronJobCalculation
     */
    private function getFuelStatistics($fromDate, $toDate, $deviceIds)
    {
        $query = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('device_id', $deviceIds);

        $dailyData = $query->selectRaw("
                DATE(job_time_from) as date,
                SUM(fuel_consumption) as total_fuel_consumption,
                SUM(total_fuel_theft) as total_fuel_theft,
                SUM(total_fuel_filled) as total_fuel_filled,
                AVG(min_fuel_level) as avg_min_fuel_level,
                AVG(max_fuel_level) as avg_max_fuel_level,
                AVG(fuel_average) as avg_fuel_efficiency
            ")
            ->groupBy(DB::raw('DATE(job_time_from)'))
            ->orderBy('date', 'desc')
            ->limit(15)
            ->get();

        $totalStats = $query->selectRaw("
                SUM(fuel_consumption) as total_fuel_consumption,
                SUM(total_fuel_theft) as total_fuel_theft,
                SUM(total_fuel_filled) as total_fuel_filled,
                AVG(fuel_average) as avg_fuel_efficiency
            ")
            ->first();

        return [
            'daily' => $dailyData,
            'totals' => $totalStats
        ];
    }

    /**
     * Get movement statistics from CronJobCalculation
     */
    private function getMovementStatistics($fromDate, $toDate, $deviceIds)
    {
        $query = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('device_id', $deviceIds);

        $deviceStats = $query->selectRaw("
                device_id,
                devices.name as device_name,
                SUM(distance) as total_distance,
                AVG(fuel_average) as avg_fuel_efficiency
            ")
            ->join('devices', 'devices.id', '=', 'cron_job_calculations.device_id')
            ->groupBy('device_id', 'devices.name')
            ->orderBy('total_distance', 'desc')
            ->limit(10)
            ->get();

        $totalDistance = $query->sum('distance');

        return [
            'by_device' => $deviceStats,
            'total_distance' => $totalDistance
        ];
    }

    /**
     * Get time statistics from CronJobCalculation
     */
    private function getTimeStatistics($fromDate, $toDate, $deviceIds)
    {
        $query = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('device_id', $deviceIds);

        $timeStats = $query->selectRaw("
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as total_moving_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as total_idle_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as total_stop_time
            ")
            ->first();

        return $timeStats;
    }

    /**
     * Get summary data for dashboard overview
     */
    private function getSummaryData($fromDate, $toDate, $deviceIds)
    {
        $query = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('device_id', $deviceIds);

        $summary = $query->selectRaw("
                COUNT(DISTINCT device_id) as active_devices,
                SUM(fuel_consumption) as total_fuel_consumption,
                SUM(distance) as total_distance,
                AVG(fuel_average) as avg_fuel_efficiency,
                SUM(total_fuel_theft) as total_fuel_theft,
                SUM(total_fuel_filled) as total_fuel_filled
            ")
            ->first();

        return $summary;
    }

    /**
     * Clear dashboard cache
     */
    public function clearDashboardCache()
    {
        $pattern = "enhanced_dashboard_data_*";
        
        // Get all cache keys matching the pattern
        $keys = Cache::getRedis()->keys($pattern);
        
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }

        return response()->json(['status' => 1, 'message' => 'Dashboard cache cleared successfully']);
    }
}

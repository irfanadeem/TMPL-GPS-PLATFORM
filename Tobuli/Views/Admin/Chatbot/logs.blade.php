@extends('Admin.Layouts.default')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1>Chatbot Conversation Logs</h1>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Filter Logs</h4>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.chatbot.logs') }}" class="form-inline">
                <div class="form-group">
                    <label>User:</label>
                    <select name="user_id" class="form-control">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>From:</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="form-group">
                    <label>To:</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="form-group">
                    <label>Tool:</label>
                    <select name="tool" class="form-control">
                        <option value="">All Tools</option>
                        <option value="get_device_stats" {{ request('tool') == 'get_device_stats' ? 'selected' : '' }}>Device Stats</option>
                        <option value="get_history_stats" {{ request('tool') == 'get_history_stats' ? 'selected' : '' }}>History Stats</option>
                        <option value="get_events_list" {{ request('tool') == 'get_events_list' ? 'selected' : '' }}>Events List</option>
                        <option value="export_data" {{ request('tool') == 'export_data' ? 'selected' : '' }}>Export Data</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.chatbot.logs') }}" class="btn btn-default">Reset</a>
            </form>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Chat Logs ({{ $logs->total() }} total)</h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Device</th>
                        <th>User Message</th>
                        <th>Bot Response</th>
                        <th>Tool</th>
                        <th>Tokens</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ Formatter::time()->human($log->created_at) }}</td>
                        <td>{{ $log->user_email }}</td>
                        <td>{{ $log->device_name ?? '-' }}</td>
                        <td><small>{{ Str::limit($log->user_message, 50) }}</small></td>
                        <td><small>{{ Str::limit($log->bot_response, 50) }}</small></td>
                        <td>{{ $log->tool_called ?? '-' }}</td>
                        <td>{{ $log->api_tokens_used }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.chatbot.index') }}" class="btn btn-default">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection

@extends('Admin.Layouts.default')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1>Chatbot Analytics Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3>{{ number_format($totalLogs) }}</h3>
                    <p>Total Conversations</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3>{{ number_format($totalTokens) }}</h3>
                    <p>Total API Tokens Used</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3>{{ number_format($totalUsers) }}</h3>
                    <p>Active Users</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>API Usage (Last 30 Days)</h4>
                </div>
                <div class="panel-body">
                    <canvas id="usageChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Top 10 Users by Usage</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User Email</th>
                                <th>Chat Count</th>
                                <th>Total Tokens</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topUsers as $topUser)
                            <tr>
                                <td>{{ $topUser->email }}</td>
                                <td>{{ number_format($topUser->chat_count) }}</td>
                                <td>{{ number_format($topUser->total_tokens) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.chatbot.logs') }}" class="btn btn-primary">View Chat Logs</a>
            <a href="{{ route('admin.chatbot.quotas') }}" class="btn btn-info">Manage Quotas</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    var ctx = document.getElementById('usageChart').getContext('2d');
    var usageData = @json($usageData);
    
    var labels = usageData.map(function(item) { return item.date; });
    var chatCounts = usageData.map(function(item) { return item.count; });
    var tokenCounts = usageData.map(function(item) { return item.tokens; });
    
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Chat Count',
                data: chatCounts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                yAxisID: 'y',
            }, {
                label: 'API Tokens',
                data: tokenCounts,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            }
        },
    });
</script>
@endsection

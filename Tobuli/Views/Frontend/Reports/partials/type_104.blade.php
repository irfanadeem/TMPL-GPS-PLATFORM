@extends('Frontend.Reports.partials.layout')

<style>
    .cold-chain-report {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .cold-chain-report .table {
        margin-bottom: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .cold-chain-report thead tr:first-child th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        padding: 12px 8px;
        border: none;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }
    
    .cold-chain-report thead tr:nth-child(2) th {
        background: linear-gradient(135deg, #7c8ff0 0%, #8b5cb8 100%);
        color: white;
        font-weight: 500;
        padding: 10px 8px;
        border: none;
        font-size: 12px;
    }
    
    .cold-chain-report tbody tr {
        transition: all 0.2s ease;
    }
    
    .cold-chain-report tbody tr:hover {
        background-color: #f8f9ff !important;
        transform: scale(1.01);
        box-shadow: 0 2px 4px rgba(102, 126, 234, 0.1);
    }
    
    .cold-chain-report tbody td {
        padding: 10px 8px;
        vertical-align: middle;
        font-size: 13px;
    }
    
    .cold-chain-report tbody tr:nth-child(odd) {
        background-color: #fafbff;
    }
    
    .cold-chain-report tbody tr:nth-child(even) {
        background-color: #ffffff;
    }
    
    /* Date and Hour columns */
    .cold-chain-report .date-col {
        background-color: #f0f4ff !important;
        font-weight: 600;
        color: #4a5568;
    }
    
    .cold-chain-report .hour-col {
        background-color: #f7f9ff !important;
        font-weight: 500;
        color: #5a67d8;
    }
    
    /* Average values */
    .cold-chain-report .avg-col {
        background-color: #fffbf0 !important;
        font-weight: 500;
        color: #744210;
    }
    
    /* Min values - Cool Blue/Green */
    .cold-chain-report .min-value {
        background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%) !important;
        color: #0c4a6e;
        font-weight: 600;
        font-size: 14px;
    }
    
    .cold-chain-report .min-time {
        background-color: #f0f9ff !important;
        color: #0369a1;
        font-size: 11px;
        font-style: italic;
    }
    
    /* Max values - Warm Red/Orange */
    .cold-chain-report .max-value {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
        color: #7f1d1d;
        font-weight: 600;
        font-size: 14px;
    }
    
    .cold-chain-report .max-time {
        background-color: #fef2f2 !important;
        color: #991b1b;
        font-size: 11px;
        font-style: italic;
    }
    
    /* Summary footer */
    .cold-chain-report tfoot tr {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }
    
    .cold-chain-report tfoot th {
        color: white;
        font-weight: 600;
        padding: 14px 8px;
        border: none;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .cold-chain-report .summary-min {
        background: linear-gradient(135deg, #0c4a6e 0%, #075985 100%) !important;
        color: #ffffff;
        font-weight: 700;
        font-size: 15px;
    }
    
    .cold-chain-report .summary-max {
        background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%) !important;
        color: #ffffff;
        font-weight: 700;
        font-size: 15px;
    }
    
    .cold-chain-report .summary-time {
        font-size: 11px;
        color: #e2e8f0;
        font-weight: 400;
    }
    
    /* Borders */
    .cold-chain-report .table-bordered > thead > tr > th,
    .cold-chain-report .table-bordered > tbody > tr > td,
    .cold-chain-report .table-bordered > tfoot > tr > th {
        border: 1px solid #e2e8f0;
    }
</style>

@section('content')
    @foreach ($report->getItems() as $item)
        <div class="panel panel-default">
            @include('Frontend.Reports.partials.item_heading')

            @if (empty($item['hourly_items']))
                @include('Frontend.Reports.partials.item_empty')
            @else
                <div class="panel-body">
                    <div class="table-responsive cold-chain-report">
                        <table class="table table-striped table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="vertical-align: middle;">Date</th>
                                    <th rowspan="2" style="vertical-align: middle;">Hour</th>
                                    @foreach($item['sensor_info'] as $sensorId => $info)
                                        <th colspan="5" class="text-center">{{ $info['name'] }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach($item['sensor_info'] as $sensorId => $info)
                                        <th class="text-center">Average</th>
                                        <th class="text-center">Min</th>
                                        <th class="text-center">Min Time</th>
                                        <th class="text-center">Max</th>
                                        <th class="text-center">Max Time</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item['hourly_items'] as $key => $data)
                                    <tr>
                                        <td class="date-col">{{ $data['date'] }}</td>
                                        <td class="hour-col">{{ date('h A', strtotime($data['hour'] . ':00')) }}</td>
                                        @foreach($item['sensor_info'] as $sensorId => $info)
                                            @if(isset($data['sensors'][$sensorId]))
                                                @php $sData = $data['sensors'][$sensorId]; @endphp
                                                <td class="text-center avg-col">{{ $sData['avg'] }}</td>
                                                <td class="text-center min-value">{{ $sData['min'] }}</td>
                                                <td class="text-center min-time">{{ Formatter::time()->convert(date('Y-m-d H:i:s', $sData['min_time']), 'H:i:s') }}</td>
                                                <td class="text-center max-value">{{ $sData['max'] }}</td>
                                                <td class="text-center max-time">{{ Formatter::time()->convert(date('Y-m-d H:i:s', $sData['max_time']), 'H:i:s') }}</td>
                                            @else
                                                <td colspan="5" class="text-center">-</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Summary</th>
                                    @foreach($item['sensor_info'] as $sensorId => $info)
                                        @if(isset($item['global_summary'][$sensorId]))
                                            @php $gSum = $item['global_summary'][$sensorId]; @endphp
                                            <th class="text-center">-</th>
                                            <th class="text-center summary-min">{{ $gSum['min'] }}</th>
                                            <th class="text-center summary-time">{{ $gSum['min_time'] ? Formatter::time()->convert(date('Y-m-d H:i:s', $gSum['min_time']), 'Y-m-d H:i:s') : '-' }}</th>
                                            <th class="text-center summary-max">{{ $gSum['max'] }}</th>
                                            <th class="text-center summary-time">{{ $gSum['max_time'] ? Formatter::time()->convert(date('Y-m-d H:i:s', $gSum['max_time']), 'Y-m-d H:i:s') : '-' }}</th>
                                        @else
                                            <th colspan="5"></th>
                                        @endif
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endforeach
@stop

@extends('Frontend.Reports.partials.layout')
@section('scripts')

    <style>
        .demo-placeholder {
            width: 100%;
            height: 150px;
            font-size: 14px;
            line-height: 1.2em;
        }
        .graph-control {
            height: 40px;
        }
        .graph-control-label {
            float: right;
        }
        .graph-control {
            height: 27px;
        }
        .graph-control li {
            display: inline;
        }
        .graph-control-buttons {
            float: right;
            margin-left: 10px;
        }
        .graph-control-buttons img {
            cursor: pointer;
        }
    </style>
    <script>{!! file_get_contents(public_path('assets/js/report.js')) !!}</script>

@stop
@section('content')
    
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="pull-right">{{ Formatter::time()->human($report->getDateFrom()) }} - {{ Formatter::time()->human($report->getDateTo()) }} ({{ Formatter::time()->unit() }})</div>
                <div class="report-bars"></div>
                {{ trans('front.report_type') }}: {{ $report->title() }}
            </div>
            <div id="fuel_fill_count" style="width: 100%; height: 400px;"></div>
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-speed">
                            <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Date & Time</th>
                                <th>Previous Fuel (L)</th>
                                <th>Current Fuel (L)</th>
                                <th>Fill Amount (L)</th>
                                <th>Sensor</th>
                                <th>{{ trans('front.position') }}</th>
                            </tr>
                            </thead>
                            @php
                                $fuel_fill_data = [];
                                $devices_count_date = '';
                                $fuel_fill_total = 0;
                            @endphp
                            @foreach ($report->getItems() as $item)
                                @if ( ! empty($item['table']))
                            <tbody>
                            @foreach ($item['table']['rows'] as $row)
                                    @php
                                        $dateString = Carbon::parse($row['start_at'])->format('Y-m-d');
                                        if($devices_count_date != $dateString){
                                            if(isset($fuel_fill_data[$dateString])){
                                                $fuel_fill_data[$dateString][0]['fuel'] += (float) str_replace(['liter', 'L'], '', $row['fuel_level_difference']);
                                            }else{
                                                $fuel_fill_data[$dateString][] = [
                                                    'fuel' => (float) str_replace(['liter', 'L'], '', $row['fuel_level_difference']),
                                                ];
                                            }
                                        
                                        $devices_count_date = $dateString;
                                 }
                                 $fuel_fill_total += (float) str_replace(['liter', 'L'], '', $row['fuel_level_difference']);
                                    @endphp
                                <tr>
                                    @foreach($item['meta'] as $key => $meta)
                                        <td>{{ $meta['value'] }}</td>
                                    @endforeach
                                    <td>{{ $row['start_at'] }}</td>
                                    <td>{{ str_replace(['liter', 'L'], '', $row['fuel_level_previous']) }}</td>
                                    <td>{{ str_replace(['liter', 'L'], '', $row['fuel_level_current']) }}</td>
                                    <td>{{ str_replace(['liter', 'L'], '', $row['fuel_level_difference']) }}</td>
                                    <td>{{ $row['sensor_name'] }}</td>
                                    <td>{!! $row['location'] !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                                @endif
                                    @endforeach
                        </table>
                    </div>
            <table class="table table-striped table-speed">
                <tr>
                    <td> Total Fuel Fill: {{ $fuel_fill_total }} L</td>
                </tr>
            </table>
            
        </div>
        <script>
            $(document).ready(function () {
                var keys = [];
                var dates = {!! json_encode(array_keys($fuel_fill_data)) !!};
                for (var i in dates) {
                    keys.push([i, dates[i]]);
                }

                var dataset = [];
                var data = {!! json_encode($fuel_fill_data) !!};
                var dataIndex = 0;
                for (var device in data) {
                    var deviceData = [];
                    for (var i = 0; i < data[device].length; i++) {
                        deviceData.push([dataIndex + i * 0.25, data[device][i]['fuel']]);
                    }
                    dataset.push({
                        label: device,
                        data: deviceData,
                        bars: {
                            show: true,
                            barWidth: 0.2,
                            order: dataIndex + 1
                        }
                    });
                    dataIndex++;
                }
                var plot = $.plot($("#fuel_fill_count"), dataset, {
                    yaxis: {
                        font: {
                            size: 12,
                            color: "black",
                        },
                        tickFormatter: function formatter(x) {
                            return x.toString() + ' liters';
                        }
                    },
                    xaxis: {
                        ticks: keys,
                        autoscaleMargin: .05,
                        font: {
                            size: 12,
                            color: "black"
                        }
                    },
                    series: {
                        shadowSize: 1
                    },
                    legend: {
                        show: true,
                        noColumns: 1,
                        labelFormatter: function (label, series) {
                            return '<span>' + label + '</span>';
                        },
                        container: $('#fuel_fill_count_legends'),
                        labelBoxBorderColor: '#fff'
                    },
                    grid: {
                        show: true,
                        borderWidth: 0,
                        borderColor: 'black',
                        backgroundColor: '#fbfcfd',
                        clickable: true
                    }
                });
                $("#fuel_fill_count").bind("plotclick", function (event, pos, item) {
                    if (item) {
                        var x = item.datapoint[0],
                            y = item.datapoint[1],
                            count = item.datapoint[1];

                        $("#tooltip").html(count + " liters")
                            .css({ top: item.pageY + 5, left: item.pageX + 5 })
                            .fadeIn(200);
                    } else {
                        $("#tooltip").hide();
                    }
                });
            });
        </script>
@stop
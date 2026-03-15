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
    <!-- Fallback Flot CDN if not loaded from report.js -->
    <script>
        if (typeof $.plot === 'undefined') {
            console.log('Flot not found in report.js, loading from CDN...');
            document.write('<script src="https://cdn.jsdelivr.net/npm/flot@0.8.3/jquery.flot.min.js"><\/script>');
            document.write('<script src="https://cdn.jsdelivr.net/npm/flot@0.8.3/jquery.flot.time.min.js"><\/script>');
            document.write('<script src="https://cdn.jsdelivr.net/npm/flot@0.8.3/jquery.flot.categories.min.js"><\/script>');
            document.write('<script src="https://cdn.jsdelivr.net/npm/flot@0.8.3/jquery.flot.resize.min.js"><\/script>');
        }
    </script>

@stop
@section('content')
    @php    
        $fuel_tank_count=0;
        $devices_count_id=0;
        $devices_count_date=0;
        $fuel_data = [];
        $totalDistance = 0;
        $totalSeconds = 0;
        $averageDistancePerFuelTank=0;
        
        // Process fuel data for chart before displaying table
        try {
            if (method_exists($report, 'getItems') && $report->getItems()) {
                foreach ($report->getItems() as $item) {
                    if (!empty($item['table']['rows']) && is_array($item['table']['rows'])) {
                        foreach ($item['table']['rows'] as $row) {
                            try {
                                // Safe timestamp parsing
                                $timestamp = null;
                                if (!empty($row['start_at'])) {
                                    try {
                                        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $row['start_at'], 'Asia/Karachi');
                                    } catch (Exception $e) {
                                        // Try alternative format if first fails
                                        try {
                                            $timestamp = Carbon::parse($row['start_at']);
                                        } catch (Exception $e2) {
                                            continue; // Skip this row if date parsing fails
                                        }
                                    }
                                }
                                
                                if (!$timestamp) continue;
                                
                                $dateString = $timestamp->format('Y-m-d');
                                
                                // Calculate fuel consumption for chart data - only count the second value (index 1)
                                $fuelConsum = 0;
                                if (!empty($row['fuel_consumption_list']) && 
                                    is_array($row['fuel_consumption_list']) && 
                                    isset($row['fuel_consumption_list'][1]) && 
                                    isset($row['fuel_consumption_list'][1]['value'])) {
                                    
                                    $fuelValue = (float)str_replace(['L', 'l'], '', $row['fuel_consumption_list'][1]['value']);
                                    if (is_numeric($fuelValue) && $fuelValue >= 0) {
                                        $fuelConsum = $fuelValue;
                                    }
                                }

                                
                                // Safe fuel data array handling
                                if($devices_count_date != $dateString){
                                    if(isset($fuel_data[$dateString]) && is_array($fuel_data[$dateString])){
                                        if (isset($fuel_data[$dateString][1]) && is_array($fuel_data[$dateString][1])) {
                                            $fuel_data[$dateString][1]['fuel'] += $fuelConsum;
                                        } else {
                                            $fuel_data[$dateString][1] = ['fuel' => $fuelConsum];
                                        }
                                    } else {
                                        $fuel_data[$dateString] = [];
                                        $fuel_data[$dateString][1] = ['fuel' => $fuelConsum];
                                    }
                                } elseif(isset($fuel_data[$dateString]) && is_array($fuel_data[$dateString])){
                                    if (isset($fuel_data[$dateString][0]) && is_array($fuel_data[$dateString][0])) {
                                        $fuel_data[$dateString][0]['fuel'] += $fuelConsum;
                                    } else {
                                        $fuel_data[$dateString][0] = ['fuel' => $fuelConsum];
                                    }
                                }
                                $devices_count_date = $dateString;
                                $fuel_tank_count += $fuelConsum;
                                
                                // Safe distance calculation
                                if (!empty($row['distance'])) {
                                    $cleanedDistance = str_replace(['Km', 'L'], '', $row['distance']);
                                    $distanceValue = (float)$cleanedDistance;
                                    if (is_numeric($distanceValue) && $distanceValue >= 0) {
                                        $totalDistance += $distanceValue;
                                    }
                                }
                                
                                // Safe duration parsing
                                $seconds = 0;
                                $duration = $row['duration'] ?? '';
                                if (!empty($duration) && is_string($duration)) {
                                    if (preg_match('/(\d+)min/', $duration, $matches)) {
                                        $seconds += (int)$matches[1] * 60;
                                    }
                                    if (preg_match('/(\d+)s/', $duration, $matches)) {
                                        $seconds += (int)$matches[1];
                                    }
                                }
                                $totalSeconds += $seconds;
                                
                            } catch (Exception $e) {
                                // Log error but continue processing other rows
                                error_log("Error processing fuel report row: " . $e->getMessage());
                                continue;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Log error but don't crash the report
            error_log("Error processing fuel report: " . $e->getMessage());
        }
        
        // Safe calculation of totals
        $totalHours = floor($totalSeconds / 3600);
        $remainingSeconds = $totalSeconds % 3600;
        $totalMinutes = floor($remainingSeconds / 60);
        $finalSeconds = $remainingSeconds % 60;
        
        // Safe average calculation
        if($fuel_tank_count > 0 && $totalDistance > 0) {
            $averageDistancePerFuelTank = ($totalDistance / $fuel_tank_count);
        } else {
            $averageDistancePerFuelTank = 0;
        }
        
        // Ensure values are numeric and non-negative
        $fuel_tank_count = max(0, (float)$fuel_tank_count);
        $totalDistance = max(0, (float)$totalDistance);
        $totalSeconds = max(0, (int)$totalSeconds);
    @endphp
    
    <div class="panel panel-default">
        
        <div id="fuel_count" style="width: 100%; height: 400px;"></div>
        @if ( ! empty($report->getItems()))
            <div class="panel-body no-padding">
                <table class="table table-striped table-speed">
                    <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Start Date & Time</th>
                        <th>Start Location</th>
                        <th>End Date & Time</th>
                        <th>End Location</th>
                        <th>{{ trans('front.duration') }}</th>
                        <th>{{ trans('front.route_length') }}</th>
                        <th>Fuel Consumption:</th>
                           
                        @if ( ! empty($item['table']['rows'][0]['fuel_price_list']))
                            @foreach($item['table']['rows'][0]['fuel_price_list'] as $row)
                                <th>{{ $row['title'] }}</th>
                            @endforeach
                        @endif
                    </tr>
                    </thead>
                    @if (method_exists($report, 'getItems') && $report->getItems())
                        @foreach ($report->getItems() as $item)
                            <?php $device_name='';?>
                            @if ( ! empty($item['meta']) && is_array($item['meta']))
                                @foreach($item['meta'] as $meta)
                                    @if (isset($meta['value']))
                                        <?php $device_name=$meta['value'];?>
                                    @endif
                                @endforeach
                            @endif
                       
                            <tbody>
                            @if(isset($item['error']))
                                <tr>
                                <td>{{$device_name??''}}</td>
                                    <td colspan="9">{{ $item['error'] }}</td>
                                </tr> 
                            @elseif(isset($item['table']['rows']) && is_array($item['table']['rows'])) 
                            @foreach ($item['table']['rows'] as $row)
                                <tr>
                                    <td>{{$device_name??''}}</td>
                                    <td>{{ $row['start_at']??'' }}</td>
                                    <td>{!! $row['location_start']??'' !!}</td>
                                    <td>{{ $row['end_at']??'' }}</td>
                                    <td>{!! $row['location_end']??'' !!}</td>
                                    <td>{{ $row['duration']??'' }}</td>
                                    <td>{{ $row['distance']??'' }}</td>

                                    @if ( ! empty($row['fuel_consumption_list']) && is_array($row['fuel_consumption_list']) && isset($row['fuel_consumption_list'][1]['value']))
                                        <td>{{ $row['fuel_consumption_list'][1]['value'] }}</td>
                                    @else
                                        <td>0L</td>
                                    @endif
                                   
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
                        @endforeach
                    @endif
                    <tfoot><tr>
                        <td>Average Fuel : {{ $averageDistancePerFuelTank?number_format($averageDistancePerFuelTank,2):''  }} Km/L</td>
                        <td colspan="4" style="text-align: end"> Total Duration : {{ floor($totalSeconds / 3600) . "h " . $totalMinutes . "min " . $finalSeconds . "s" }}</td>
                        <td style="text-align: end"> Total Distance: {{ $totalDistance }} Km</td>
                        
                        <td></td>
                        <td colspan="2" style="text-align: end"> Total Fuel Consumption (trip wise): {{ $fuel_tank_count }} L</td>
                        
                    <td></td></tr>
                </tfoot>
                </table>
            </div>
        @endif
        {{--@include('Frontend.Reports.partials.item_total')--}}

    </div>
    <script>
        $(document).ready(function () {
            // Function to create chart
            function createChart() {
                // Check if Flot is available
                if (typeof $.plot === 'undefined') {
                    console.error('Flot library not loaded');
                    console.log('Available jQuery methods:', Object.getOwnPropertyNames($.fn));
                    $("#fuel_count").html('<div style="text-align: center; padding: 50px; color: #666;">Chart library not available - $.plot is undefined</div>');
                    return;
                }
                
                console.log('Flot library is available, proceeding with chart creation...');

            try {
                var data = {!! json_encode($fuel_data) !!};
                console.log('Raw fuel data:', data);
                
                // Safety check for data
                if (!data || typeof data !== 'object') {
                    console.log('Invalid fuel data');
                    $("#fuel_count").html('<div style="text-align: center; padding: 50px; color: #666;">Invalid fuel data</div>');
                    return;
                }
                
                var dataKeys = Object.keys(data);
                console.log('Data keys:', dataKeys);
                console.log('Data length:', dataKeys.length);
                
                // Check if we have data
                if (dataKeys.length === 0) {
                    console.log('No fuel data available');
                    $("#fuel_count").html('<div style="text-align: center; padding: 50px; color: #666;">No fuel data available for the selected period</div>');
                    return;
                }

                var keys = [];
                var dates = dataKeys;
                console.log('Dates:', dates);
                for (var i in dates) {
                    keys.push([i, dates[i]]);
                }

                var dataset = [];
                var dataIndex = 0;
                for (var device in data) {
                    console.log('Processing device:', device, 'Data:', data[device]);
                    
                    // Safety check for device data
                    if (!data[device] || !Array.isArray(data[device])) {
                        console.log('Invalid device data for:', device);
                        continue;
                    }
                    
                    var deviceData = [];
                    for (var i = 0; i < data[device].length; i++) {
                        // Safety check for fuel value
                        var fuelValue = 0;
                        if (data[device][i] && typeof data[device][i]['fuel'] !== 'undefined') {
                            fuelValue = parseFloat(data[device][i]['fuel']) || 0;
                        }
                        deviceData.push([dataIndex + i * 0.25, fuelValue]);
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
            } catch (error) {
                console.error('Error processing fuel data:', error);
                $("#fuel_count").html('<div style="text-align: center; padding: 50px; color: #666;">Error processing fuel data</div>');
                return;
            }
            console.log('Final dataset:', dataset);
            
            if (dataset.length > 0) {
                try {
                    var plot = $.plot($("#fuel_count"), dataset, {
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
                            container: $('#fuel_count_legends'),
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
                    $("#fuel_count").bind("plotclick", function (event, pos, item) {
                        if (item) {
                            var x = item.datapoint[0],
                                y = item.datapoint[1],
                                count = item.datapoint[1]; // Assuming the count is the y value

                            $("#tooltip").html(count + " liters")
                                .css({ top: item.pageY + 5, left: item.pageX + 5 })
                                .fadeIn(200);
                        } else {
                            $("#tooltip").hide();
                        }
                    });
                } catch (error) {
                    console.error('Error creating chart:', error);
                    $("#fuel_count").html('<div style="text-align: center; padding: 50px; color: #666;">Error creating chart: ' + error.message + '</div>');
                }
            } else {
                $("#fuel_count").html('<div style="text-align: center; padding: 50px; color: #666;">No fuel data available for the selected period</div>');
            }
            }
            
            // Try to create chart immediately, if Flot is not available, wait a bit
            if (typeof $.plot !== 'undefined') {
                createChart();
            } else {
                console.log('Flot not available, waiting 500ms for CDN to load...');
                setTimeout(createChart, 500);
            }
        });
    </script>
@stop
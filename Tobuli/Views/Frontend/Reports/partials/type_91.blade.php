@extends('Frontend.Reports.partials.layout')

@section('content')
    @php
        $total_fuel_level = 0;
        $total_distance = 0;
        $total_trips = 0;
        $device_last_fuel_levels = []; // Track last fuel level for each device
    @endphp
    
    <div class="panel panel-default">
        @if (isset($report) && method_exists($report, 'getItems') && !empty($report->getItems()))
            <div class="panel-body no-padding">
                <table class="table table-striped table-speed">
                    <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Start Date/Time</th>
                        <th>End Date/Time</th>
                        <th>{{ trans('front.duration') }}</th>
                        <th>{{ trans('front.position_a') }}</th>
                        <th>{{ trans('front.position_b') }}</th>
                        <th>Travelled (KM)</th>
                        <th>Start Fuel (L)</th>
                        <th>End/Fuel Level(L)</th>
                        
                    </tr>
                    </thead>
                    @foreach ($report->getItems() as $item)
                        <?php $device_name = '';?>
                        @if ( ! empty($item['meta']))
                            @foreach($item['meta'] as $meta)
                                @if (isset($meta['value']))
                                    <?php $device_name = $meta['value'];?>
                                @endif
                            @endforeach
                        @endif
                        <tbody>
                        @if (isset($item['table']['rows']) && is_array($item['table']['rows']))
                            @foreach ($item['table']['rows'] as $row)
                                @php
                                $fuel_start_value = 0;
                                $fuel_start_value_string = '';
                                
                                if(isset($row['fuel_level_start_list'][0]['value'])){
                                    $fuel_start_value_string = $row['fuel_level_start_list'][0]['value'];
                                    $fuel_start_value = preg_replace('/[^0-9.]/', '', $fuel_start_value_string);
                                    $fuel_start_value = is_numeric($fuel_start_value) ? (float)$fuel_start_value : 0;
                                }
                                
                                // Check if we have valid fuel data
                                if($fuel_start_value > 0 && $fuel_start_value <= 2999){
                                    // Calculate totals in the main loop
                                    if (isset($row['distance'])) {
                                        // Extract numeric value from distance string (e.g., "0.11 Km" -> 0.11)
                                        $distance_value = preg_replace('/[^0-9.]/', '', $row['distance']);
                                        $total_distance += is_numeric($distance_value) ? (float)$distance_value : 0;
                                    }
                                    $total_trips++;
                                    
                                    // Track last fuel level for each device
                                    if (isset($row['fuel_level_end_list']) && is_array($row['fuel_level_end_list'])) {
                                        $device_last_fuel_levels[$device_name] = $row['fuel_level_end_list'];
                                    }
                                }
                                @endphp
                                @if($fuel_start_value >= 0 && $fuel_start_value != '' && $fuel_start_value <= 2999)
                                <tr>
                                    <td>{{ $device_name }}</td>
                                    <td>{{ isset($row['start_at']) ? $row['start_at'] : '-' }}</td>
                                    <td>{{ isset($row['end_at']) ? $row['end_at'] : '-' }}</td>
                                    <td>{{ isset($row['duration']) ? $row['duration'] : '-' }}</td>
                                    <td>{!! isset($row['location_start']) ? $row['location_start'] : '-' !!}</td>
                                    <td>{!! isset($row['location_end']) ? $row['location_end'] : '-' !!}</td>
                                    <td>{{ isset($row['distance']) ? $row['distance'] : '0' }}</td>
                                    <td>{{ $fuel_start_value_string }}
                                    </td>
                                    <td>
                                        @if (isset($row['fuel_level_end_list']) && is_array($row['fuel_level_end_list']))
                                            @foreach($row['fuel_level_end_list'] as $fuel_end)
                                                {{ $fuel_end['value'] }}<br>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    </tr>
                                    @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">@if($device_name != ''){{ $device_name }} @endif has no data available</td>
                            </tr>
                        @endif
                        </tbody>
                    @endforeach
                </table>
                
                @php
                    // Calculate total fuel level from last values of each device
                    foreach ($device_last_fuel_levels as $device => $fuel_levels) {
                        foreach ($fuel_levels as $fuel_level) {
                            if (isset($fuel_level['value']) && $fuel_level['value'] != '') {
                                // Extract numeric value from formatted string (e.g., "4 L" -> 4)
                                $fuel_value_string = $fuel_level['value'];
                                $fuel_value = preg_replace('/[^0-9.]/', '', $fuel_value_string);
                                
                                if (is_numeric($fuel_value) && $fuel_value > 0 && $fuel_value <= 2999) {
                                    $total_fuel_level += (float)$fuel_value;
                                }
                            }
                        }
                    }
                @endphp
                
                <table class="table table-striped table-speed">
                    <thead>
                        <tr>
                            <th>Summary</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Total End Fuel Level (at end of trip)</strong></td>
                            <td><strong>{{ number_format($total_fuel_level, 2) }} L</strong></td>
                        </tr>
                        <!-- <tr>
                            <td><strong>Total KM Travelled</strong></td>
                            <td><strong>{{ number_format($total_distance, 2) }} Km</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Total Trips</strong></td>
                            <td><strong>{{ $total_trips }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Average Fuel Level per Device</strong></td>
                            <td><strong>{{ $total_trips > 0 ? number_format($total_fuel_level / count($device_last_fuel_levels), 2) : '0' }} L</strong></td>
                        </tr> -->
                        <tr>
                            <td><strong>Devices with Fuel Data</strong></td>
                            <td><strong>{{ count($device_last_fuel_levels) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Note:</strong> Total End Fuel Level is the combined fuel left in all assets at the end of their trips.</td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="panel-body">
                <div class="alert alert-info text-center">
                    <h4>No Data Available</h4>
                    <p>No fuel level data found for the selected criteria.</p>
                </div>
            </div>
        @endif
    </div>
@stop
<table>
    @foreach ($report->getItems() as $item)
        {{-- Device Name Header --}}
        @if(isset($item['meta']))
            <tr>
                <td colspan="20" style="background-color: #4a5568; color: white; font-weight: bold; font-size: 14px; padding: 10px;">
                    @if(isset($item['meta']['device.name']['value']))
                        Device: {{ $item['meta']['device.name']['value'] }}
                    @endif
                    @if(isset($item['meta']['device.imei']['value']))
                        (IMEI: {{ $item['meta']['device.imei']['value'] }})
                    @endif
                </td>
            </tr>
        @endif

        {{-- Check if there's an error --}}
        @if(isset($item['error']))
            <tr>
                <td colspan="20" style="background-color: #fee2e2; color: #7f1d1d; padding: 10px;">
                    {{ $item['error'] }}
                </td>
            </tr>
        @elseif(!empty($item['hourly_items']))
            {{-- Column Headers --}}
            <thead>
                <tr>
                    <th style="background-color: #667eea; color: white; font-weight: bold;">Date</th>
                    <th style="background-color: #667eea; color: white; font-weight: bold;">Hour</th>
                    @foreach($item['sensor_info'] as $sensorId => $info)
                        <th colspan="5" style="background-color: #667eea; color: white; font-weight: bold; text-align: center;">{{ $info['name'] }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    @foreach($item['sensor_info'] as $sensorId => $info)
                        <th style="background-color: #7c8ff0; color: white;">Average</th>
                        <th style="background-color: #7c8ff0; color: white;">Min</th>
                        <th style="background-color: #7c8ff0; color: white;">Min Time</th>
                        <th style="background-color: #7c8ff0; color: white;">Max</th>
                        <th style="background-color: #7c8ff0; color: white;">Max Time</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- Data Rows --}}
                @foreach($item['hourly_items'] as $key => $data)
                    <tr>
                        <td style="background-color: #f0f4ff; font-weight: bold;">{{ $data['date'] }}</td>
                        <td style="background-color: #f7f9ff;">{{ date('h A', strtotime($data['hour'] . ':00')) }}</td>
                        @foreach($item['sensor_info'] as $sensorId => $info)
                            @if(isset($data['sensors'][$sensorId]))
                                @php $sData = $data['sensors'][$sensorId]; @endphp
                                <td style="background-color: #fffbf0;">{{ $sData['avg'] }}</td>
                                <td style="background-color: #e0f2fe; font-weight: bold;">{{ $sData['min'] }}</td>
                                <td style="background-color: #f0f9ff;">{{ date('H:i:s', $sData['min_time']) }}</td>
                                <td style="background-color: #fee2e2; font-weight: bold;">{{ $sData['max'] }}</td>
                                <td style="background-color: #fef2f2;">{{ date('H:i:s', $sData['max_time']) }}</td>
                            @else
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                
                {{-- Summary Row --}}
                @if(isset($item['global_summary']))
                    <tr>
                        <td style="background-color: #1e293b; color: white; font-weight: bold;" colspan="2">Summary</td>
                        @foreach($item['sensor_info'] as $sensorId => $info)
                            @if(isset($item['global_summary'][$sensorId]))
                                @php $gSum = $item['global_summary'][$sensorId]; @endphp
                                <td style="background-color: #1e293b; color: white;">-</td>
                                <td style="background-color: #0c4a6e; color: white; font-weight: bold;">{{ $gSum['min'] }}</td>
                                <td style="background-color: #1e293b; color: white;">{{ $gSum['min_time'] ? date('Y-m-d H:i:s', $gSum['min_time']) : '-' }}</td>
                                <td style="background-color: #7f1d1d; color: white; font-weight: bold;">{{ $gSum['max'] }}</td>
                                <td style="background-color: #1e293b; color: white;">{{ $gSum['max_time'] ? date('Y-m-d H:i:s', $gSum['max_time']) : '-' }}</td>
                            @else
                                <td colspan="5"></td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            </tbody>
        @else
            <tr>
                <td colspan="20" style="background-color: #fef2f2; color: #7f1d1d; padding: 10px;">
                    No data available for this device.
                </td>
            </tr>
        @endif

        {{-- Spacing between devices --}}
        <tr>
            <td colspan="20" style="height: 20px;"></td>
        </tr>
    @endforeach
</table>

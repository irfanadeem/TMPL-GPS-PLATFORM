<div class="tab-pane-header">
    <div class="form">
        <div class="input-group">
            <div class="form-group search">
                <input class="form-control" placeholder="Search Cold Chain..." onkeyup="app.coldChain.search(this.value)"/>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane-body" id="ajax-coldchain" style="padding: 10px;">
    <h4>Cold Chain Status</h4>
    <div class="table-responsive">
        <table class="table table-list" id="cold-chain-table">
            <thead>
                <tr>
                    <th>{!! trans('validation.attributes.name') !!}</th>
                    <th>Temp</th>
                    <th>Humidity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($coldChainDevices) && count($coldChainDevices) > 0)
                    @foreach($coldChainDevices as $device)
                        @php
                            $tempSensor = $device->sensors->where('type', 'temperature')->first();
                            $humSensor = $device->sensors->where('type', 'humidity')->first();
                            
                            // Fallback: If no humidity type sensor, check if a sensor named "Humidity" exists (in case of wrong type)
                            if (!$humSensor) {
                                $humSensor = $device->sensors->filter(function($s) {
                                    return str_contains(strtolower($s->name), 'humidity');
                                })->first();
                            }
                            
                            // If we fallback to a sensor that was also picked as temp sensor, don't duplicate it
                            if ($tempSensor && $humSensor && $tempSensor->id == $humSensor->id) {
                                if (str_contains(strtolower($tempSensor->name), 'temp')) {
                                    $humSensor = null;
                                } else {
                                    $tempSensor = null;
                                }
                            }

                            if (!$tempSensor && !$humSensor) continue;

                            $tempValObj = $tempSensor ? $tempSensor->getValueCurrent($device) : null;
                            $humValObj = $humSensor ? $humSensor->getValueCurrent($device) : null;
                        @endphp
                        <tr data-device-id="{{ $device->id }}">
                            <td>
                                <strong>{{ $device->name }}</strong>
                            </td>
                            <td>
                                @if($tempSensor)
                                    @php
                                        $tempNum = floatval($tempValObj->getValue());
                                        $tLabel = 'label-success';
                                        $tText = $tempValObj->getFormatted();
                                        
                                        if ($tempNum > 100) {
                                            $tLabel = 'label-danger';
                                            $tText = 'Sensor Disconnected';
                                        } elseif ($tempNum > -5) {
                                            $tLabel = 'label-danger';
                                        } elseif ($tempNum > -15) {
                                            $tLabel = 'label-warning';
                                        }
                                    @endphp
                                    <span class="label {{ $tLabel }}" title="Parameter: {{ $tempSensor->tag_name }}">{{ $tText }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($humSensor)
                                    @php
                                        $humNum = floatval($humValObj->getValue());
                                        $hLabel = 'label-info';
                                        $hText = $humValObj->getFormatted();
                                        
                                        if ($humNum > 100) {
                                            $hLabel = 'label-danger';
                                            $hText = 'Sensor Disconnected';
                                        }
                                    @endphp
                                    <span class="label {{ $hLabel }}" title="Parameter: {{ $humSensor->tag_name }}">{{ $hText }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-default btn-xs" title="History" onclick="app.coldChain.showHistory({{ $device->id }}, '{{ $device->name }}', {{ $tempSensor ? $tempSensor->id : 'null' }}, {{ $humSensor ? $humSensor->id : 'null' }})">
                                    <i class="fa fa-history"></i>
                                </button>
                                @if($tempSensor)
                                <button class="btn btn-warning btn-xs" title="Create Temperature Alert" onclick="app.coldChain.createAlert({{ $device->id }}, '{{ $device->name }}', {{ $tempSensor->id }}, '{{ $tempSensor->name }}')">
                                    <i class="fa fa-bell"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="text-center">No cold chain units found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<style>
    .cold-chain-dashboard {
        background: #111827;
        color: #f3f4f6;
        border-radius: 8px;
        padding: 20px;
        font-family: 'Inter', sans-serif;
    }
    .cc-header {
        text-align: center;
        margin-bottom: 20px;
    }
    .cc-title { font-size: 1.5rem; font-weight: bold; color: #10b981; margin-bottom: 5px; }
    .cc-subtitle { font-size: 0.9rem; color: #9ca3af; }
    
    .cc-date-picker {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
        padding: 15px;
        background: #1f2937;
        border-radius: 8px;
    }
    .cc-date-picker label {
        color: #9ca3af;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .cc-date-btn {
        background: #374151;
        border: 1px solid #4b5563;
        color: #f3f4f6;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .cc-date-btn:hover {
        border-color: #10b981;
        background: #4b5563;
    }
    .cc-date-btn i {
        color: #10b981;
    }
    #cc-selected-date {
        position: absolute;
        left: -9999px;
    }
    
    .cc-card-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    .cc-card {
        background: #1f2937;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid #374151;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 140px;
    }
    .cc-card.maximum { border-left: 5px solid #ef4444; }
    .cc-card.minimum { border-left: 5px solid #10b981; }
    
    .cc-card-label { font-size: 0.85rem; color: #9ca3af; text-transform: uppercase; position: absolute; right: 20px; top: 20px; font-weight: 600; }
    .cc-card-icon { font-size: 1.5rem; margin-bottom: 15px; }
    
    .cc-val-group { margin-top: 10px; }
    .cc-sensor-label { font-size: 0.9rem; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px; font-weight: 500; }
    .cc-temp-val { font-size: 2rem; font-weight: bold; color: #f87171; margin-bottom: 12px; }
    .cc-temp-val .unit { font-size: 1.3rem; color: #f87171; margin-left: 4px; }
    .cc-hum-val { font-size: 2rem; color: #22d3ee; font-weight: bold; }
    .cc-hum-val .unit { font-size: 1.3rem; color: #22d3ee; margin-left: 4px; }
    
    .cc-chart-container {
        background: #1f2937;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        height: 300px;
    }
    .cc-readings {
        background: #1f2937;
        border-radius: 12px;
        padding: 15px;
        max-height: 400px;
        overflow-y: auto;
    }
    .cc-readings::-webkit-scrollbar {
        width: 8px;
    }
    .cc-readings::-webkit-scrollbar-track {
        background: #374151;
        border-radius: 4px;
    }
    .cc-readings::-webkit-scrollbar-thumb {
        background: #10b981;
        border-radius: 4px;
    }
    .cc-readings::-webkit-scrollbar-thumb:hover {
        background: #059669;
    }
    .cc-readings-title { font-weight: 600; margin-bottom: 15px; }
    .cc-reading-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #374151;
    }
    .cc-reading-item:last-child { border-bottom: none; }
    .cc-reading-time { font-size: 0.9rem; color: #9ca3af; }
    .cc-reading-vals { display: flex; gap: 10px; }
    .cc-reading-badge {
        padding: 6px 14px;
        border-radius: 18px;
        font-size: 1rem;
        font-weight: 600;
        background: #374151;
    }
    .cc-reading-badge.t { color: #f87171; background: rgba(248, 113, 113, 0.1); }
    .cc-reading-badge.h { color: #22d3ee; background: rgba(34, 211, 238, 0.1); }
    
    #coldChainHistoryModal .modal-content { border: none; background: transparent; }
</style>

<!-- Date Selection Modal -->
<div class="modal fade" id="coldChainDateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="background: #1f2937; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #374151;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
                <h4 class="modal-title" style="color: #10b981;">Select Date Range</h4>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="cc-date-options">
                    <button class="cc-date-option-btn" data-option="today">
                        <i class="fa fa-calendar-check-o"></i>
                        <span>Today</span>
                    </button>
                    <button class="cc-date-option-btn" data-option="yesterday">
                        <i class="fa fa-calendar-minus-o"></i>
                        <span>Yesterday</span>
                    </button>
                    <button class="cc-date-option-btn" data-option="custom">
                        <i class="fa fa-calendar"></i>
                        <span>Custom Date</span>
                    </button>
                </div>
                <div id="cc-custom-date-container" style="display: none; margin-top: 20px;">
                    <label style="color: #9ca3af; font-size: 0.9rem; margin-bottom: 8px; display: block;">Select Date:</label>
                    <input type="date" id="cc-custom-date-input" style="width: 100%; background: #374151; border: 1px solid #4b5563; color: #f3f4f6; padding: 10px; border-radius: 6px;" />
                    <button id="cc-custom-date-submit" style="width: 100%; margin-top: 15px; background: #10b981; color: white; border: none; padding: 10px; border-radius: 6px; font-weight: 600; cursor: pointer;">Continue</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .cc-date-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .cc-date-option-btn {
        background: #374151;
        border: 1px solid #4b5563;
        color: #f3f4f6;
        padding: 15px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1rem;
        transition: all 0.2s;
        width: 100%;
    }
    .cc-date-option-btn:hover {
        background: #4b5563;
        border-color: #10b981;
    }
    .cc-date-option-btn i {
        color: #10b981;
        font-size: 1.3rem;
    }
    #cc-custom-date-submit:hover {
        background: #059669;
    }
</style>

@section('scripts')
@parent
<script type="text/javascript">
    $(document).ready(function() {
        if (typeof app === 'undefined') return;

        app.coldChain = {
            search: function(val) {
                var filter = val.toLowerCase();
                $("#cold-chain-table tbody tr").each(function() {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(filter) > -1);
                });
            },
            showHistory: function(deviceId, deviceName, tempSensorId, humSensorId) {
                // Store device info
                app.coldChain.currentDeviceId = deviceId;
                app.coldChain.currentDeviceName = deviceName;
                app.coldChain.currentTempSensorId = tempSensorId;
                app.coldChain.currentHumSensorId = humSensorId;
                
                // Show date selection modal first
                $('#coldChainDateModal').modal('show');
                
                // Handle date option selection
                $('.cc-date-option-btn').off('click').on('click', function() {
                    var option = $(this).data('option');
                    
                    if (option === 'custom') {
                        $('#cc-custom-date-container').slideDown();
                        var today = moment().format('YYYY-MM-DD');
                        $('#cc-custom-date-input').val(today);
                    } else {
                        var selectedDate;
                        if (option === 'today') {
                            selectedDate = moment().format('YYYY-MM-DD');
                        } else if (option === 'yesterday') {
                            selectedDate = moment().subtract(1, 'days').format('YYYY-MM-DD');
                        }
                        
                        $('#coldChainDateModal').modal('hide');
                        app.coldChain.openDashboard(selectedDate);
                    }
                });
                
                // Handle custom date submit
                $('#cc-custom-date-submit').off('click').on('click', function() {
                    var customDate = $('#cc-custom-date-input').val();
                    if (customDate) {
                        $('#coldChainDateModal').modal('hide');
                        $('#cc-custom-date-container').hide();
                        app.coldChain.openDashboard(customDate);
                    } else {
                        alert('Please select a date');
                    }
                });
            },
            openDashboard: function(selectedDate) {
                var deviceId = app.coldChain.currentDeviceId;
                var deviceName = app.coldChain.currentDeviceName;
                var tempSensorId = app.coldChain.currentTempSensorId;
                var humSensorId = app.coldChain.currentHumSensorId;
                
                var url = '{{ route("cold_chain.history") }}' + '?device_id=' + deviceId;
                if (tempSensorId) url += '&temp_sensor_id=' + tempSensorId;
                if (humSensorId) url += '&hum_sensor_id=' + humSensorId;
                
                var modalHtml = '<div class="modal fade" id="coldChainHistoryModal" tabindex="-1" role="dialog">' +
                    '<div class="modal-dialog modal-lg" role="document">' +
                    '<div class="modal-content">' +
                    '<div class="cold-chain-dashboard">' +
                        '<button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>' +
                        '<div class="cc-header">' +
                            '<div class="cc-title">' + deviceName + '</div>' +
                            '<div class="cc-subtitle" id="cc-date-info">Loading...</div>' +
                            '<div id="cc-params-info" style="font-size:0.8rem; color:#6b7280; margin-top:5px;"></div>' +
                            '<div class="cc-date-picker">' +
                                '<input type="date" id="cc-selected-date" />' +
                                '<label for="cc-selected-date" class="cc-date-btn">' +
                                    '<i class="fa fa-calendar"></i>' +
                                    '<span id="cc-date-display">Loading...</span>' +
                                '</label>' +
                            '</div>' +
                        '</div>' +
                        '<div class="cc-card-grid" id="cc-stats-container"></div>' +
                        '<div class="cc-readings-title">Temp & Humidity Trend</div>' +
                        '<div class="cc-chart-container" id="cold-chain-chart-container"></div>' +
                        '<div class="cc-readings">' +
                            '<div class="cc-readings-title">Hourly Readings</div>' +
                            '<div id="cc-recent-readings"></div>' +
                        '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
                
                $('#coldChainHistoryModal').remove();
                $('body').append(modalHtml);
                $('#coldChainHistoryModal').modal('show');
                
                // Store current device and sensor IDs for refresh
                app.coldChain.currentDeviceId = deviceId;
                app.coldChain.currentDeviceName = deviceName;
                app.coldChain.currentTempSensorId = tempSensorId;
                app.coldChain.currentHumSensorId = humSensorId;
                
                // Set the selected date
                $('#cc-selected-date').val(selectedDate);
                $('#cc-date-display').text(moment(selectedDate).format('DD MMM YYYY'));
                
                // Make calendar button clickable
                $('.cc-date-btn').off('click').on('click', function(e) {
                    e.preventDefault();
                    $('#cc-selected-date')[0].showPicker();
                });
                
                // Handle date change - auto refresh
                $('#cc-selected-date').off('change').on('change', function() {
                    var newDate = $(this).val();
                    $('#cc-date-display').text(moment(newDate).format('DD MMM YYYY'));
                    app.coldChain.loadData();
                });
                
                // Load initial data
                app.coldChain.loadData();
            },
            refreshData: function() {
                var selectedDate = $('#cc-selected-date').val();
                
                if (!selectedDate) {
                    alert('Please select a date');
                    return;
                }
                
                app.coldChain.loadData();
            },
            loadData: function() {
                var selectedDate = $('#cc-selected-date').val();
                
                // Get full day range (00:00:00 to 23:59:59)
                var from = moment(selectedDate).format('YYYY-MM-DD 00:00:00');
                var to = moment(selectedDate).format('YYYY-MM-DD 23:59:59');
                
                var url = '{{ route("cold_chain.history") }}' + '?device_id=' + app.coldChain.currentDeviceId;
                if (app.coldChain.currentTempSensorId) url += '&temp_sensor_id=' + app.coldChain.currentTempSensorId;
                if (app.coldChain.currentHumSensorId) url += '&hum_sensor_id=' + app.coldChain.currentHumSensorId;
                
                $.getJSON(url + '&from=' + from + '&to=' + to, function(res) {
                    console.log('Cold Chain Response:', res);
                    console.log('Hourly Readings:', res.hourly_readings);
                    
                    var displayDate = moment(selectedDate).format('DD MMM YYYY');
                    $('#cc-date-info').text('Date: ' + displayDate + ' | Last Updated: ' + (res.last_updated || 'N/A'));
                    
                    var paramsText = '';
                    if (res.temp_parameter) paramsText += 'Temp Parameter: ' + res.temp_parameter;
                    if (res.hum_parameter) paramsText += (paramsText ? ' | ' : '') + 'Hum Parameter: ' + res.hum_parameter;
                    $('#cc-params-info').text(paramsText);
                    
                    // Stats - Only Max and Min
                    var statsHtml = '';
                    var types = ['maximum', 'minimum'];
                    var icons = ['fa-line-chart', 'fa-level-down'];
                    
                    types.forEach(function(type, i) {
                        var tVal = res.temp_stats[type.substring(0,3)] || res.temp_stats[type];
                        var hVal = res.hum_stats[type.substring(0,3)] || res.hum_stats[type];
                        
                        var tDisplay = tVal !== null && tVal !== 'N/A' ? tVal + '<span class="unit">°C</span>' : 'N/A';
                        var hDisplay = hVal !== null && hVal !== 'N/A' ? hVal + '<span class="unit">%</span>' : 'N/A';
                        
                        statsHtml += '<div class="cc-card ' + type + '">' +
                            '<div class="cc-card-label">' + type + '</div>' +
                            '<div class="cc-card-icon" style="color: ' + (type === 'maximum' ? '#ef4444' : '#10b981') + '"><i class="fa ' + icons[i] + '"></i></div>' +
                            '<div class="cc-val-group">' +
                                '<div class="cc-sensor-label"><i class="fa fa-thermometer-three-quarters" style="margin-right:8px;"></i>Temperature</div>' +
                                '<div class="cc-temp-val">' + tDisplay + '</div>' +
                                '<div class="cc-sensor-label"><i class="fa fa-tint" style="margin-right:8px;"></i>Humidity</div>' +
                                '<div class="cc-hum-val">' + hDisplay + '</div>' +
                            '</div>' +
                        '</div>';
                    });
                    $('#cc-stats-container').html(statsHtml);
                    
                    // Readings
                    var readingsHtml = '';
                    if (res.hourly_readings && res.hourly_readings.length > 0) {
                        res.hourly_readings.forEach(function(r) {
                            var tempDisplay = r.temp !== null ? r.temp + '°C' : '-';
                            var humDisplay = r.hum !== null ? r.hum + '%' : '-';
                            
                            readingsHtml += '<div class="cc-reading-item">' +
                                '<div class="cc-reading-time"><i class="fa fa-clock-o" style="margin-right:8px;"></i>' + r.time + '</div>' +
                                '<div class="cc-reading-vals">' +
                                    '<div class="cc-reading-badge t"><i class="fa fa-thermometer-half" style="margin-right:5px;"></i>' + tempDisplay + '</div>' +
                                    '<div class="cc-reading-badge h"><i class="fa fa-tint" style="margin-right:5px;"></i>' + humDisplay + '</div>' +
                                '</div>' +
                            '</div>';
                        });
                    } else {
                        readingsHtml = '<div style="text-align:center; color:#9ca3af; padding:20px;">No hourly data available</div>';
                    }
                    $('#cc-recent-readings').html(readingsHtml);

                    // Chart
                    if (res.temp_data.length > 0 || res.hum_data.length > 0) {
                        app.coldChain.drawChart('cold-chain-chart-container', res);
                    } else {
                        $('#cold-chain-chart-container').html('<div class="alert alert-info" style="background:#374151; border:none; color:#f3f4f6;">No data for chart.</div>');
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Cold Chain Error:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);
                    alert('Error loading Cold Chain data: ' + textStatus);
                });
            },
            drawChart: function(containerId, res) {
                if (typeof $.plot === 'undefined') return;

                var tempPoints = [];
                res.temp_data.forEach(function(d) { tempPoints.push([moment(d.t).valueOf(), d.v]); });
                
                var humPoints = [];
                res.hum_data.forEach(function(d) { humPoints.push([moment(d.t).valueOf(), d.v]); });
                
                $.plot("#" + containerId, [
                    { data: tempPoints, label: "Temp (" + res.temp_unit + ")", color: "#3b82f6", lines: { fill: true, fillColor: "rgba(59, 130, 246, 0.1)" } },
                    { data: humPoints, label: "Humidity (" + res.hum_unit + ")", color: "#06b6d4", lines: { show: true } }
                ], {
                    xaxis: { mode: "time", timeformat: "%H:%M", font: { color: "#9ca3af" } },
                    yaxis: { font: { color: "#9ca3af" } },
                    grid: { hoverable: true, borderColor: "#374151", borderWidth: 1, color: "#9ca3af" },
                    legend: { backgroundOpacity: 0, font: { color: "#f3f4f6" } },
                    tooltip: true,
                    tooltipOpts: { content: "%s: %x = %y" }
                });
            },
            createAlert: function(deviceId, deviceName, sensorId, sensorName) {
                // Show a confirmation dialog with alert details
                var message = 'Create a temperature alert for:\n\n' +
                    'Device: ' + deviceName + '\n' +
                    'Sensor: ' + sensorName + '\n' +
                    'Threshold: -12°C or below\n\n' +
                    'To create this alert, please:\n' +
                    '1. Go to Alerts menu\n' +
                    '2. Click "Create Alert"\n' +
                    '3. Select "Sensor Alert (Temperature)" as type\n' +
                    '4. Choose device: ' + deviceName + '\n' +
                    '5. Select sensor and set threshold to -12\n' +
                    '6. Choose condition: "Less than or equal to (≤)"\n' +
                    '7. Configure notifications (Email, SMS, Push, etc.)\n\n' +
                    'Would you like to open the Alerts page now?';
                
                if (confirm(message)) {
                    // Try to open alerts modal if available
                    if (typeof app !== 'undefined' && typeof app.modal !== 'undefined') {
                        // Open alerts creation modal
                        window.location.href = '/alerts';
                    } else {
                        window.location.href = '/alerts';
                    }
                }
            }
        };
    });
</script>
@stop

@extends('Admin.Layouts.mastertable')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<style>
    table.dataTable td, table.dataTable th {
        -webkit-box-sizing: content-box;
        box-sizing: content-box;
        border: 1px solid #ddd;
    }
    
    select.filter-select {
        min-width: 99px;
        background-color: aliceblue;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 4px;
    }
    
    .table-button {
        font-size: 14px !important;
        color: white !important;
        padding: 8px 12px;
        margin: 5px;
        border-radius: 4px;
    }
    
    .table-responsive {
        color: white;
        overflow-x: auto;
    }
    
    .hidden-column {
        display: none !important;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: white;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: white !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #1656A5 !important;
        border: 1px solid #1656A5 !important;
    }
    
    @media print {
        thead select {
            display: none !important;
        }
        
        th[style*="display: none"],
        td[style*="display: none"] {
            display: none !important;
        }
        
        select:disabled {
            display: none !important;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            display: none !important;
        }
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 12px;
        }
        
        .btn {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .table-button {
            font-size: 12px !important;
            padding: 5px 8px;
            margin: 2px;
        }
    }
</style>
@section('content')
       
<!-- Date Range Selection Panel Body -->
    <div class="col-md-12" style="background-color: #1E2944; height: 80px;">   

        <!-- From Date -->
        {!! Form::open(['url'=> route('admin.mastertable.view'), 'method' => 'GET']) !!}
                   
                <div class="col-md-2" style="float: left;">
                    <div class="form-fields">
                        <label for="from_date" class="form-label" style="color: white;">From:</label>
                        <input type="text" class="form-control datepicker" name="dateFrom" style="color: white;" id="dateFrom"
                        value="{{ $request['dateFrom'] ?? 'Select From date'}}">
                    </div>
                </div>

        <!-- To Date -->
                    <div class="col-md-2" style="float: left;">
                        <label for="to_date" class="form-label" style="color: white;">To:</label>
                        <input type="text" class="form-control datepicker" name="dateTo" style="color: white;" id="dateTo"
                        value="{{ $request['dateTo'] ?? 'Select To Date'}}" >
                    </div>

        <!-- Department Dropdown  -->
                    <div class="col-md-2" style="float: left;">
                        <label for="department" class="form-label" style="color: white;">Department:</label>
                        <select class="form-control" id="departmentGroupID" style="color: white;"
                                name="department" data-live-search="true">
                            <option value="" selected>ALL</option>
                            @foreach($groups as $groupRow)
                            <option 
                            {{ isset($request['department']) && $request['department'] == $groupRow['id'] ? 'selected' : '' }}
                            value="{{ $groupRow['id'] }}">{{ $groupRow['title'] }}</option>
                            @endforeach
                            
                        </select>
                    </div>

        <!-- Submit Button -->
                        <div class="col-md-2" style="float: left;">
                            <button type="submit" class="btn text-white form-fields shadow"
                                    style="background-color: #1656A5; margin-top:22px; color: white;">Analytics
                            </button>
                    </div>
                      {!! Form::close() !!}                 
        <!-- Table Setting Button-->
                    <div class="col-md-3" style="float: right; width: 200px;">
                        <button class="btn btn-secondary dropdown-toggle shadow"
                                style="margin-top:22px; background-color: #1656A5; color: white" type="button" id="settingsDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs"></i> Table Settings
                        </button>
                        <ul class="dropdown-menu" id="settingsDropdownMenu" aria-labelledby="settingsDropdown">
                            <li class="dropdown-item">

                                <!-- Main option for Fuel Stats -->
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input column-toggle" id="toggleFuel"
                                           data-group="fuel-stats" checked>
                                    <label class="form-check-label" for="toggleFuel">Fuel Stats</label>
                                </div>
                            </li>
                            <li class="dropdown-item">

                                <!-- Main option for Travel Details -->
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input column-toggle" id="toggleTravel"
                                           data-group="travel-details" checked>
                                    <label class="form-check-label" for="toggleTravel">Travel Details</label>
                                </div>
                            </li>

                            <li class="dropdown-item">

                                <!-- Main option for Engine Hours -->
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input column-toggle" id="toggleEngine"
                                           data-group="engine-hours" checked>
                                    <label class="form-check-label" for="toggleEngine">Engine Hours</label>
                                </div>
                            </li>
                            <li class="dropdown-item">

                                <!-- Main option for Events -->
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input column-toggle" id="toggleEvents"
                                           data-group="events" checked>
                                    <label class="form-check-label" for="toggleEvents">Events</label>
                                </div>
                            </li>
                        </ul>
                    </div>       
    </div>

        <!-- Print Button -->
        <div class="col-md-12" style="background-color:rgb(43, 109, 190);">
            
    </div>

<!-- Event Table -->
    <div class="col-md-12">
        <div class="table-responsive">
            <table id="master_table" class="dash-table"
                   data-filter-control="true" data-show-search-clear-button="false" class="cell-border">
                <thead>
                <!-- Grouped Heading -->
                <tr>
                    <th rowspan="2" data-field="index">Sr#</th>
                    <th rowspan="2" data-field="day">Day</th>
                    <th rowspan="2" data-field="created_at">Date</th>
                    <th rowspan="2" class="department" data-field="department">Department
                    </th>
                    <th rowspan="2" class="asset" data-field="device_name">Asset</th>
                    <th rowspan="2" class="sensor_type" data-field="sensor_type">Sensor</th>
                    <th class="fuel-stats" colspan="5" style=" text-align: center;" data-filter-control="select">Fuel
                        Statistics
                    </th>
                    <th class="travel-details" colspan="2" style="text-align: center;" data-filter-control="select">
                        Travel Details
                    </th>
                    <th class="engine-hours" colspan="3" style="text-align: center;">Engine Hours</th>
                    <th class="events" colspan="3" style="text-align: center;">Events</th>
                </tr>
                <!-- Sub-Headings -->
                <tr>
                    <th class="fuel-stats" id="consumption" data-field="fuel_consumption"
                        >Consumption (Ltr)
                    </th>
                    <th class="fuel-stats" id="theft" data-field="total_fuel_theft"
                        >Theft (Ltr)
                    </th>
                    <th class="fuel-stats" id="refill" data-field="total_fuel_filled"
                        >Refill (Ltr)
                    </th>
                    <th class="fuel-stats" id="level" data-field="max_fuel_level" data-filter-control="select"
                        >Level (Ltr)
                    </th>
                    <th class="fuel-stats" id="temperature" data-field="temperature_avg" data-filter-control="select"
                        >Temperature (°C)
                    </th>
                    <th class="travel-details" id="travelled" data-field="distance" data-filter-control="select">Total Travelled (KM)
                    </th>
                    <th class="travel-details" id="fuelAverage" data-field="fuel_average" data-filter-control="select"
                        >Fuel Average (KM)
                    </th>
                    
                    <th class="engine-hours" id="totalHrs" data-field="total_moving_time" data-filter-control="select">Total Moving
                        (Hrs)
                    </th>
                    <th class="engine-hours" id="idleHrs" data-field="total_idle_time" data-filter-control="select">Idle (Hrs)
                    </th>
                    <th class="engine-hours" id="stopsHrs" data-field="total_stop_time" data-filter-control="select">Stops
                        (Hrs)
                    </th>
                    <th class="events" id="zone_out_count" data-field="zone_out_count" data-filter-control="select">Fence Out
                    </th>
                    <th class="events" id="power_cut_count" data-field="power_cut_count" data-filter-control="select">Power Cut
                    </th>
                    <th class="events" id="fs_temper_count" data-field="fs_temper_count" data-filter-control="select">FS Temper
                       
                    </th>
                </tr>
                </thead>
                <tbody>
                @php $counter = 1; @endphp
                @foreach($data as $row)
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>{{ $row['job_time_from'] ? date('D', strtotime($row['job_time_from'])) : '-' }}</td>
                        <td>{{ $row['job_time_from'] ? date('d-m-Y', strtotime($row['job_time_from'])) : '-' }}</td>
                        <td class="department">{{ $row['department'] }}</td>
                        <td class="asset">{{ $row['device_name'] }}</td>
                        <td class="sensor_type">{{ $row['sensor_type'] }}</td>
                        <td class="fuel-stats">{{ $row['fuel_consumption'] }}</td>
                        <td class="fuel-stats">{{ $row['total_fuel_theft'] }}</td>
                        <td class="fuel-stats">{{ $row['total_fuel_filled'] }}</td>
                        <td class="fuel-stats">{{ $row['max_fuel_level'] < 4500 ? $row['max_fuel_level'] : 'Fuel Temper' }}</td>
                        <td class="fuel-stats temperature-cell" style="{{ ($row['show_temperature'] ?? false) ? '' : 'display: none;' }}">
                            @if($row['show_temperature'] ?? false)
                                Max: {{ $row['temperature_max'] ?? 0 }}°C<br>
                                Min: {{ $row['temperature_min'] ?? 0 }}°C<br>
                                Avg: {{ $row['temperature_avg'] ?? 0 }}°C
                            @else
                                -
                            @endif
                        </td>
                    <td class="travel-details">{{ $row['distance'] }}</td>
                    <td class="travel-details">{{ $row['fuel_average'] }}</td>
                    <td class="engine-hours">{{ $row['total_moving_time'] }}</td>
                    <td class="engine-hours">{{ $row['total_idle_time'] }}</td>
                    <td class="engine-hours">{{ $row['total_stop_time'] }}</td>
                    <td class="events">{{ $row['zone_out_count'] }}</td>
                    <td class="events">{{ $row['power_cut_count'] }}</td>
                    <td class="events">{{ $row['fs_temper_count'] }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="6" style="text-align: right;">Total:</th>
                    <th class="fuel-stats" id="total_fuel_consumption">0</th>
                    <th class="fuel-stats" id="total_fuel_theft">0</th>
                    <th class="fuel-stats" id="total_fuel_filled">0</th>
                    <th class="fuel-stats" id="total_fuel_level">0</th> <!-- Total for fuel level column -->
                    <th class="fuel-stats"></th> <!-- Empty cell for temperature column -->
                    <th class="travel-details" id="total_distance">0</th>
                    <th class="travel-details" id="total_fuel_average">0</th>
                    <th class="engine-hours" id="total_moving_time">0</th>
                    <th class="engine-hours" id="total_idle_time">0</th>
                    <th class="engine-hours" id="total_stop_time">0</th>
                    <th class="events" id="total_zone_out_count">0</th>
                    <th class="events" id="total_power_cut_count">0</th>
                    <th class="events" id="total_fs_temper_count">0</th>
                </tr>
            </tfoot>

            </table>
        </div></div>
</div>
@stop

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
let dataTable;

$(document).ready(function () {
    var logoUrl = "{{ Appearance::getAssetFileUrl('logo') }}";
    
    dataTable = $('#master_table').DataTable({
        responsive: true,
        dom: '<"top"lBfr>t<"bottom"ip>',
        initComplete: function () {
            const api = this.api();

            // Append a new row for filters inside <thead>
            $('#master_table thead').append('<tr class="filter-row"></tr>');
            const filterRow = $('#master_table thead tr.filter-row');

            api.columns().every(function (index) {
                const column = this;
                const headerCell = $(column.header());
                const headerClasses = headerCell.attr('class');
                
                const th = $('<th class="'+headerClasses+'" style="text-align: center;"></th>').appendTo(filterRow);

                if (column.index() === 0) {
                    return; // Skip first column
                }

                // Create a select dropdown
                const select = $('<select class="filter-select"><option value="">All</option></select>')
                    .appendTo(th)
                    .on('change', function () {
                        const val = $(this).val();
                        column.search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
                    });

                // Add unique sorted options
                column.data().unique().sort().each(function (dataValue) {
                    if (dataValue && dataValue.trim() !== '') {
                        select.append(`<option value="${dataValue}">${dataValue}</option>`);
                    }
                });
            });
        },
        buttons: [
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-primary table-button',
                title: 'Master Table Report',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return $(node).css('display') !== 'none';
                    }
                },
                customize: function (win) {
                    $(win.document.body)
                        .prepend(
                            `<div style="text-align: center; margin-bottom: 20px;">
                                <img src="${logoUrl}" style="width: 150px; height: auto; margin-bottom: 10px;">
                                <h2>Master Table Report</h2>
                                <p>Generated on: ${new Date().toLocaleDateString()}</p>
                             </div>`
                        );
                    
                    // Hide filter row in print
                    $(win.document.body).find('.filter-row').hide();
                    
                    // Style the table for print
                    $(win.document.body).find('table').css({
                        'border-collapse': 'collapse',
                        'width': '100%',
                        'font-size': '12px',
                        'margin-top': '20px'
                    });
                    
                    $(win.document.body).find('th, td').css({
                        'border': '1px solid #ddd',
                        'padding': '8px',
                        'text-align': 'left'
                    });
                    
                    // Style the header
                    $(win.document.body).find('thead th').css({
                        'background-color': '#f5f5f5',
                        'font-weight': 'bold'
                    });
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success table-button',
                title: 'Master Table Report',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return $(node).css('display') !== 'none';
                    }
                },
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    
                    // Add header with logo and title
                    var headerXml = `<row r="1">
                                      <c t="inlineStr" r="A1">
                                        <is><t>Master Table Report</t></is>
                                      </c>
                                   </row>
                                   <row r="2">
                                      <c t="inlineStr" r="A2">
                                        <is><t>Generated on: ${new Date().toLocaleDateString()}</t></is>
                                      </c>
                                   </row>
                                   <row r="3">
                                      <c t="inlineStr" r="A3">
                                        <is><t></t></is>
                                      </c>
                                   </row>`;
                    $(sheet).find('sheetData').prepend(headerXml);
                    
                    // Style the header row
                    var headerStyle = `<cellXfs count="2">
                                        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
                                        <xf numFmtId="0" fontId="1" fillId="1" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
                                      </cellXfs>`;
                    $(sheet).find('cellXfs').replaceWith(headerStyle);
                }
            }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: "Loading...",
            emptyTable: "No data available",
            zeroRecords: "No matching records found"
        }
    });
    // Calculate totals function
    function calculateTotals() {
        let totals = {
            fuel_consumption: 0,
            total_fuel_theft: 0,
            total_fuel_filled: 0,
            max_fuel_level: 0,
            distance: 0,
            fuel_average: 0,
            total_moving_time: 0,
            total_idle_time: 0,
            total_stop_time: 0,
            zone_out_count: 0,
            power_cut_count: 0,
            fs_temper_count: 0
        };

        $('#master_table tbody tr').each(function () {
            totals.fuel_consumption += parseFloat($(this).find('td:nth-child(7)').text()) || 0;
            totals.total_fuel_theft += parseFloat($(this).find('td:nth-child(8)').text()) || 0;
            totals.total_fuel_filled += parseFloat($(this).find('td:nth-child(9)').text()) || 0;
            totals.max_fuel_level += parseFloat($(this).find('td:nth-child(10)').text()) || 0;
            // Skip temperature column (11) - no total needed
            totals.distance += parseFloat($(this).find('td:nth-child(12)').text()) || 0;
            totals.fuel_average += parseFloat($(this).find('td:nth-child(13)').text()) || 0;
            totals.total_moving_time += parseFloat($(this).find('td:nth-child(14)').text()) || 0;
            totals.total_idle_time += parseFloat($(this).find('td:nth-child(15)').text()) || 0;
            totals.total_stop_time += parseFloat($(this).find('td:nth-child(16)').text()) || 0;
            totals.zone_out_count += parseFloat($(this).find('td:nth-child(17)').text()) || 0;
            totals.power_cut_count += parseFloat($(this).find('td:nth-child(18)').text()) || 0;
            totals.fs_temper_count += parseFloat($(this).find('td:nth-child(19)').text()) || 0;
        });

        // Update footer with totals
        $('#total_fuel_consumption').text(totals.fuel_consumption.toFixed(2));
        $('#total_fuel_theft').text(totals.total_fuel_theft.toFixed(2));
        $('#total_fuel_filled').text(totals.total_fuel_filled.toFixed(2));
        $('#total_fuel_level').text(totals.max_fuel_level.toFixed(2));
        $('#total_distance').text(totals.distance.toFixed(2));
        $('#total_fuel_average').text(totals.fuel_average.toFixed(2));
        $('#total_moving_time').text(totals.total_moving_time.toFixed(2));
        $('#total_idle_time').text(totals.total_idle_time.toFixed(2));
        $('#total_stop_time').text(totals.total_stop_time.toFixed(2));
        $('#total_zone_out_count').text(totals.zone_out_count);
        $('#total_power_cut_count').text(totals.power_cut_count);
        $('#total_fs_temper_count').text(totals.fs_temper_count);
    }

    calculateTotals();
    $('#departmentGroupID').selectpicker();

    const today = new Date(); 
    const twoMonthsBefore = new Date(today); 
    twoMonthsBefore.setMonth(today.getMonth() - 2); 
    
    // Initialize datepicker
    $('#dateFrom').datepicker({
        format: 'yyyy-mm-dd',    
        autoclose: true,      
        todayHighlight: true,   
        startDate: twoMonthsBefore,
        endDate: today,       
    });
    
    $('#dateTo').datepicker({
        format: 'yyyy-mm-dd',    
        autoclose: true,      
        todayHighlight: true,   
        startDate: twoMonthsBefore,
        endDate: today,       
    });
});

// Column visibility toggle functionality
function toggleColumnGroup(groupClass, show) {
    if (dataTable) {
        // Get column indexes for the group
        const columnIndexes = [];
        dataTable.columns().every(function(index) {
            const column = this;
            const header = $(column.header());
            if (header.hasClass(groupClass)) {
                columnIndexes.push(index);
            }
        });
        
        // Toggle visibility of data columns
        columnIndexes.forEach(function(index) {
            dataTable.column(index).visible(show);
        });
        
        // Toggle visibility of filter row cells
        $('#master_table thead tr.filter-row th.' + groupClass).each(function() {
            const filterCell = $(this);
            if (show) {
                filterCell.show();
            } else {
                filterCell.hide();
            }
        });
        
        // Update colspan for grouped headers
        updateColspanForGroup(groupClass, show);
    }
}

function updateColspanForGroup(groupClass, show) {
    // Find the grouped header row (first row)
    const headerRow = $('#master_table thead tr:first-child');
    
    // Update colspan for the specific group
    headerRow.find('th.' + groupClass).each(function() {
        const $this = $(this);
        
        if (show) {
            // Restore original colspan and show
            if (groupClass === 'fuel-stats') {
                $this.attr('colspan', '5').show();
            } else if (groupClass === 'travel-details') {
                $this.attr('colspan', '2').show();
            } else if (groupClass === 'engine-hours') {
                $this.attr('colspan', '3').show();
            } else if (groupClass === 'events') {
                $this.attr('colspan', '3').show();
            }
        } else {
            // Hide the grouped header
            $this.attr('colspan', '0').hide();
        }
    });
}

// Initialize column visibility controls
document.addEventListener("DOMContentLoaded", function () {
    // Add event listeners for checkboxes
    document.querySelectorAll(".form-check-input.column-toggle").forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            const groupClass = this.dataset.group;
            const isChecked = this.checked;
            toggleColumnGroup(groupClass, isChecked);
        });
    });
});

// Test export functionality
function testExportFunctionality() {
    if (dataTable) {
        // Test if buttons are properly initialized
        const buttons = dataTable.buttons();
        
        // Test column visibility
        const visibleColumns = dataTable.columns().visible();
        
        return true;
    } else {
        return false;
    }
}

// Call test function after table loads
$(document).ready(function() {
    setTimeout(function() {
        testExportFunctionality();
    }, 2000);
});

  
  // Dropdown functionality
  $(document).ready(function() {
    // When the dropdown button is clicked, toggle the dropdown menu
    $('#settingsDropdown').click(function(event) {
      event.stopPropagation(); 
      $('#settingsDropdownMenu').toggle(); 
    });

    // Prevent closing the dropdown when interacting with checkboxes inside it
    $('#settingsDropdownMenu').click(function(event) {
      event.stopPropagation();
    });

    // Close the dropdown if clicked outside of it
    $(document).click(function(event) {
      if (!$(event.target).closest('#settingsDropdown').length) {
        $('#settingsDropdownMenu').hide();
      }
    });
  });

// Download/Export functions
function exportToExcel() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const department = document.getElementById('department').value;
    
    const url = new URL('{{ route("admin.mastertable.download") }}', window.location.origin);
    url.searchParams.append('dataFrom', dateFrom);
    url.searchParams.append('dataTo', dateTo);
    url.searchParams.append('department', department);
    url.searchParams.append('format', 'excel');
    
    window.open(url.toString(), '_blank');
}

function printPDFMTable() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const department = document.getElementById('department').value;
    
    const url = new URL('{{ route("admin.mastertable.print") }}', window.location.origin);
    url.searchParams.append('dataFrom', dateFrom);
    url.searchParams.append('dataTo', dateTo);
    url.searchParams.append('department', department);
    
    window.open(url.toString(), '_blank');
}

</script>
@stop


@extends('Admin.Layouts.mastertable')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<style>
    .fixed-table-pagination{
        display: flex;
        flex-direction: row;
        }
        .bootstrap-table .fixed-table-container .fixed-table-body {
            height: auto !important;
        }
    .table-responsive {
        overflow-x: auto;
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
    .hidden-column {
        display: none !important;
    }
    .table-button {
        font-size: 14px !important;
        color: white !important;
        padding: 8px 12px;
        margin: 5px;
        border-radius: 4px;
        border: none;
    }
    .btn-success {
        background-color: #28a745 !important;
    }
    .btn-primary {
        background-color: #007bff !important;
    }
    .dt-buttons {
        margin-bottom: 10px;
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
                <div class="col-md-2" style="float: left;">
                    <div class="form-fields">
                        <label for="from_date" class="form-label" style="color: white;">From:</label>
                        <input type="text" class="form-control datepicker" name="from_date" style="color: white;" id="from_date"
                        placeholder="Select From date">
                    </div>
                </div>

        <!-- To Date -->
                    <div class="col-md-2" style="float: left;">
                        <label for="to_date" class="form-label" style="color: white;">To:</label>
                        <input type="text" class="form-control datepicker" name="to_date" style="color: white;" id="to_date"
                        placeholder="Select To date">
                    </div>

        <!-- Department Dropdown  -->
                    <div class="col-md-2" style="float: left;">
                        <label for="department" class="form-label" style="color: white;">Department:</label>
                        <select class="form-control" id="departmentGroupID" style="color: white;"
                                name="department" data-live-search="true">
                            <option value="" selected>ALL</option>
                            @foreach($groups as $groupRow) 
                            
                            <option value="{{ $groupRow['id'] }}">{{ $groupRow['title'] }}</option>
                            @endforeach
                            
                        </select>
                    </div>

        <!-- Submit Button -->
                        <div class="col-md-2" style="float: left;">
                            <button type="submit" class="btn text-white form-fields shadow" id="loadTableButton"
                                    style="background-color: #1656A5; margin-top:22px; color: white;">Analytics
                            </button>
                    </div>
                                      
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

            <button class="btn shadow tooltip-btn" id="downloadBtn" onclick="exportToExcel()"
                    style="background-color:transparent;float: right; margin-right: 5px;border: none"
                    data-tooltip="Download">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="rgb(255, 255, 255)">
                    <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z"/>
                </svg>
            </button>
       
            <button class="btn shadow rounded tooltip-btn" id="printBtn" onclick="printPDFMTable()"
                    style="background-color:transparent;float: right; margin-right: 5px;border: none"
                    data-tooltip="Print">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="rgb(255, 255, 255)">
                    <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
                </svg>
            </button>   
    </div>

<!-- Event Table -->
    <div class="col-md-12">
        <div class="table-responsive">
            <table id="table" class="dash-table" data-url="{{url('admin/mastertable/index')}}"
                   data-filter-control="true" data-show-search-clear-button="false" class="table">
                <thead>
                <!-- Grouped Heading -->
                <tr>
                    <th rowspan="2" data-field="index">index</th>
                    <th rowspan="2" data-field="created_at">Date</th>
                    <th rowspan="2" class="department" data-field="protocol" data-filter-control="select">Department
                    </th>
                    <th rowspan="2" class="asset" data-field="device_name" data-filter-control="select">Asset</th>
                    <th rowspan="2" class="sensor_type" data-field="sensor_type" data-filter-control="select">Sensor</th>
                    <th class="fuel-stats" colspan="5" style=" text-align: center;">Fuel
                        Statistics
                    </th>
                    <th class="travel-details" colspan="2" style="text-align: center;">
                        Travel Details
                    </th>
                    <th class="engine-hours" colspan="3" style="text-align: center;">Engine Hours</th>
                    <th class="events" colspan="3" style="text-align: center;">Events</th>
                </tr>
                <!-- Sub-Headings -->
                <tr>
                    <th class="fuel-stats" id="consumption" data-field="fuel_consumption" data-filter-control="select"
                        >Consumption (Ltr)
                    </th>
                    <th class="fuel-stats" id="theft" data-field="total_fuel_theft" data-filter-control="select"
                        >Theft (Ltr)
                    </th>
                    <th class="fuel-stats" id="refill" data-field="total_fuel_filled" data-filter-control="select"
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
                
                </tbody>
                <tfoot>
                <tr>
                    <th class="fuel-stats" id="consumption" data-field="fuel_consumption" data-filter-control="select"
                        >Consumption (Ltr)
                    </th>
                    <th class="fuel-stats" id="theft" data-field="total_fuel_theft" data-filter-control="select"
                        >Theft (Ltr)
                    </th>
                    <th class="fuel-stats" id="refill" data-field="total_fuel_filled" data-filter-control="select"
                        >Refill (Ltr)
                    </th>
                    <th class="fuel-stats" id="level" data-field="max_fuel_level" data-filter-control="select"
                        >Level (Ltr)
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
                </tfoot>
            </table>
        </div></div>
</div>
@stop

@section('javascript')
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
  <script>
  let dataTable;
  
  $(document).ready(function () {
    $('#departmentGroupID').selectpicker();
    
            const today = new Date(); 
            const twoMonthsBefore = new Date(today); 
            twoMonthsBefore.setMonth(today.getMonth() - 2); 
            
            // Initialize datepicker
            $('#from_date').datepicker({
        format: 'yyyy-mm-dd',    
        autoclose: true,      
        todayHighlight: true,   
        startDate: twoMonthsBefore,
        endDate: today,       
    }).datepicker('setDate', today); 
    
    $('#to_date').datepicker({
                format: 'yyyy-mm-dd',    
                autoclose: true,      
                todayHighlight: true,   
                startDate: twoMonthsBefore,
                endDate: today,       
            }).datepicker('setDate', today); 
       
        loadTable();
    
    // Call table on button click
    $('#loadTableButton').click(function() {
        loadTable();
    });
});

function loadTable() {
    var dataFrom = $('#from_date').val() || new Date().toISOString().split('T')[0];
    var dataTo = $('#to_date').val() || new Date().toISOString().split('T')[0];
    var departmentGroupID = $('#departmentGroupID').val();

    // Destroy existing table if it exists
    if ($.fn.DataTable.isDataTable('#table')) {
        $('#table').DataTable().destroy();
    }

    dataTable = $('#table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
        url: "{{ url('/admin/master_table/data') }}",
            type: 'GET',
            data: function(d) {
                d.dataFrom = dataFrom;
                d.dataTo = dataTo;
                d.department = departmentGroupID;
            }
        },
        columns: [
            {
                data: null,
                title: '#',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'created_at', title: 'Date' },
            { data: 'department', title: 'Department' },
            { data: 'device_name', title: 'Asset' },
            { data: 'sensor_type', title: 'Sensor' },
            { data: 'fuel_consumption', title: 'Consumption (Ltr)', className: 'fuel-stats' },
            { data: 'total_fuel_theft', title: 'Theft (Ltr)', className: 'fuel-stats' },
            { data: 'total_fuel_filled', title: 'Refill (Ltr)', className: 'fuel-stats' },
            { data: 'max_fuel_level', title: 'Level (Ltr)', className: 'fuel-stats' },
            { 
                data: 'temperature_avg', 
                title: 'Temperature (°C)', 
                className: 'fuel-stats',
                render: function(data, type, row) {
                    if (row.show_temperature) {
                        return 'Max: ' + (row.temperature_max || 0) + '°C<br>' +
                               'Min: ' + (row.temperature_min || 0) + '°C<br>' +
                               'Avg: ' + (row.temperature_avg || 0) + '°C';
                    }
                    return '-';
                }
            },
            { data: 'distance', title: 'Total Travelled (KM)', className: 'travel-details' },
            { data: 'fuel_average', title: 'Fuel Average (KM)', className: 'travel-details' },
            { data: 'total_moving_time', title: 'Total Moving (Hrs)', className: 'engine-hours' },
            { data: 'total_idle_time', title: 'Idle (Hrs)', className: 'engine-hours' },
            { data: 'total_stop_time', title: 'Stops (Hrs)', className: 'engine-hours' },
            { data: 'zone_out_count', title: 'Fence Out', className: 'events' },
            { data: 'power_cut_count', title: 'Power Cut', className: 'events' },
            { data: 'fs_temper_count', title: 'FS Temper', className: 'events' }
        ],
        dom: '<"top"lBfr>t<"bottom"ip>',
        buttons: [
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
                    // Add header with logo
                    var logoXml = `<row r="1">
                                      <c t="inlineStr" r="A1">
                                        <is><t>Master Table Report</t></is>
                                      </c>
                                   </row>`;
                    $(sheet).find('sheetData').prepend(logoXml);
                }
            },
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
                        'font-size': '12px'
                    });
                    
                    $(win.document.body).find('th, td').css({
                        'border': '1px solid #ddd',
                        'padding': '8px',
                        'text-align': 'left'
                    });
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
}


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
                $this.attr('colspan', '4').show();
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
    console.log('Testing export functionality...');
    
    if (dataTable) {
        console.log('DataTable instance found:', dataTable);
        
        // Test if buttons are properly initialized
        const buttons = dataTable.buttons();
        console.log('Export buttons:', buttons);
        
        // Test column visibility
        const visibleColumns = dataTable.columns().visible();
        console.log('Visible columns:', visibleColumns);
        
        return true;
    } else {
        console.error('DataTable instance not found');
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

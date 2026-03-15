<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Panel</title>
  <!-- jQuery CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <!-- Bootstrap CSS CDN -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

  <!-- Bootstrap Table CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.0/dist/bootstrap-table.min.css">
    <style>
             body{
             background-color: #1656a5;

        }
        .modal-full {
            min-width: 90%;
            margin: 0 auto;
            margin-top: 20px;
        }
        /* Bootstrap shadow */
        .shadow-container {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .btn-container {
            margin-bottom: 20px;
        }
          .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #1e2944;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid white;  
        }

        .dash-table thead tr {
            background-color: #1656a5;
            color: white;
            text-align: left;
            font-size: 14px;
        }

        .dash-table th, td {
            padding: 12px 16px;
        }

        .dash-table tbody tr {
            border-bottom: 1px solid #2a3b5f;
            color: white;
        }

        .dash-table tbody tr:last-child {
            border-bottom: none;
        }

        .dash-table tbody tr:hover {
            background-color: #2b3e65;
        }

        .dash-table tfoot tr {
            background-color: #fabf4e;
            color: black;
        }
        td:hover{
            color: white;
        }
        select {
            width: 100%;
            border: none;
            background: transparent;
            color: black;
        }
        option{
            color: black;
        }
                
  .form-fields{
            margin-right: 10px;
        }
        .form-control{
                background-color: #2a3b5f;
        }
    </style>
    
</head>
<body>
    <div class="container mt-3" style="max-width: 100%">
        <div class="shadow rounded p-3" style="background-color: #1E2944;">
        <div class="panel panel-default">
            <!-- Panel Heading -->
            <div class="panel-heading  text-white p-3" style="background-color: #1656A5;">
                <div class="panel-title">
                    <i class="fas fa-calendar-alt"></i> Analytics
                </div>
            </div>

            <!-- Panel Body -->
            <div class="panel-body" style="width: 100%;">
<div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%;">
    <div class="d-flex flex-wrap align-items-center gap-3">
        <!-- From Date -->
        <div class="form-fields">
            <label for="from_date" class="form-label" style="color: white;">From:</label>
            <input type="date" class="form-control" name="from_date" style="color: white;" id="from_date" value="2024-12-14">
        </div>

        <!-- To Date -->
        <div class="form-fields">
            <label for="to_date" class="form-label" style="color: white;">To:</label>
            <input type="date" class="form-control" name="to_date" style="color: white;" id="to_date" value="2024-12-14">
        </div>

        <!-- Department Dropdown -->
        <div style="width: 30%" class="form-fields">
            <label for="department" class="form-label" style="color: white;">Department:</label>
            <select class="form-control selectpicker" id="department" style="color: white;" name="department" data-live-search="true">
                <option value="ALL" selected>ALL</option>
                <option value="MPO">MPO</option>
                <option value="CDA CARES">CDA CARES</option>
                <option value="Bulkwater">Bulk-Water</option>
            </select>
        </div>
                      
<div class="dropdown form-fields">
  <button class="btn btn-secondary dropdown-toggle shadow" style="margin-top: 30px;background-color: #1656A5" type="button" id="settingsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-cogs"></i> Table Settings
  </button>
  <ul class="dropdown-menu" id="settingsDropdownMenu" aria-labelledby="settingsDropdown">
    <li class="dropdown-item">
        <!-- Main option for Fuel Stats -->
        <div class="form-check">
          <input type="checkbox" class="form-check-input column-toggle" id="toggleFuel" data-group="fuel-stats" checked>
          <label class="form-check-label" for="toggleFuel">Fuel Stats</label>
        </div>
    
    </li>
    
    <li class="dropdown-item">
        <!-- Main option for Travel Details -->
        <div class="form-check">
          <input type="checkbox" class="form-check-input column-toggle" id="toggleTravel" data-group="travel-details" checked>
          <label class="form-check-label" for="toggleTravel">Travel Details</label>
        </div>
        <!-- Sub options for Travel Details -->

    </li>
    
    <li class="dropdown-item">
        <!-- Main option for Engine Hours -->
        <div class="form-check">
          <input type="checkbox" class="form-check-input column-toggle" id="toggleEngine" data-group="engine-hours" checked>
          <label class="form-check-label" for="toggleEngine">Engine Hours</label>
        </div>
        
    </li>
    
    <li class="dropdown-item">
        <!-- Main option for Events -->
        <div class="form-check">
          <input type="checkbox" class="form-check-input column-toggle" id="toggleEvents" data-group="events" checked>
          <label class="form-check-label" for="toggleEvents">Events</label>
        </div>
    </li>
  </ul>
</div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="btn text-white form-fields shadow" style="background-color: #1656A5;margin-top: 30px;color: white">Analytics</button>
        </div>
    </div>
</div>

                                </div>
                            </div>
                        
        </div>
     <div class="shadow rounded p-4 mt-5 mb-5" style="background-color: #1E2944;">
        <div class="btn-container" style="margin-bottom: 60px;">
    <h4 style="float: left; margin-right: 5px;color: white" >Analytics</h4>
            
                         <button class="btn shadow tooltip-btn" id="downloadBtn" style="background-color:transparent;float: right; margin-right: 5px;border: none" data-tooltip="Download">
                           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="#605DFF">
                            <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z" />
                             </svg>
                            </button>
                            <button class="btn shadow rounded tooltip-btn"  id="printBtn" style="background-color:transparent;float: right; margin-right: 5px;border: none" data-tooltip="Print">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF">
                            <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                             </svg>
                             </button>
                             
</div>
                <!-- Event Table -->
                <div class="table-responsive">
                   <table id="table" class="dash-table" data-url="{{url('admin/mastertable/index')}}" data-filter-control="true" data-show-search-clear-button="false" class="table">
      <thead>
        <!-- Grouped Heading -->
        <tr>
          <th rowspan="2" data-field="created_at">Date</th>
          <th rowspan="2" class="department" data-field="protocol" data-filter-control="select">Department</th>
          <th rowspan="2" class="asset" data-field="device_name" data-filter-control="select">Asset</th>
          <th rowspan="2" class="sensor"  data-field="sensor" data-filter-control="select">Sensor</th>
          <th class="fuel-stats" colspan="4" style="background-color: antiquewhite; text-align: center;">Fuel Statistics</th>
          <th class="travel-details" colspan="3" style="background-color: cornsilk; text-align: center;">Travel Details</th>
          <th class="engine-hours" colspan="3" style="text-align: center;">Engine Hours</th>
          <th class="events" colspan="3" style="text-align: center;">Events</th>
        </tr>
        <!-- Sub-Headings -->
<tr>
  <th class="fuel-stats" id="consumption" data-field="consumption" data-filter-control="select" style="background-color: antiquewhite;">Consumption (Ltr)</th>
  <th class="fuel-stats" id="theft" data-field="theft" data-filter-control="select" style="background-color: antiquewhite;">Theft (Ltr)</th>
  <th class="fuel-stats" id="refill" data-field="refill" data-filter-control="select" style="background-color: antiquewhite;">Refill (Ltr)</th>
  <th class="fuel-stats" id="level" data-field="level" data-filter-control="select" style="background-color: antiquewhite;">Level (Ltr)</th>
  <th class="travel-details" id="travelled" data-field="travelled" data-filter-control="select" style="background-color: cornsilk;">Total Travelled (KM)</th>
  <th class="travel-details" id="fuelAverage" data-field="fuelAverage" data-filter-control="select" style="background-color: cornsilk;">Fuel Average (KM)</th>
  <th class="travel-details" id="stopsKm" data-field="stopsKm" data-filter-control="select" style="background-color: cornsilk;">Stops (KM)</th>
  <th class="engine-hours" id="totalHrs" data-field="totalHrs" data-filter-control="select">Total (Hrs)</th>
  <th class="engine-hours" id="idleHrs" data-field="idleHrs" data-filter-control="select">Idle (Hrs)</th>
  <th class="engine-hours" id="stopsHrs" data-field="stopsHrs" data-filter-control="select">Stops (Hrs)</th>
  <th class="events" id="fenceOut" data-field="fenceOut" data-filter-control="select">Fence Out (Hrs)</th>
  <th class="events" id="powerCut" data-field="powerCut" data-filter-control="select">Power Cut (Hrs)</th>
  <th class="events" id="fsTemper" data-field="fsTemper" data-filter-control="select">FS Temper (Hrs)</th>
</tr>


      </thead>
    </table>
                </div>
        </div>
                <!-- Pagination -->
       
            </div>

 <!-- Bootstrap JS and Popper.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  
  <!-- Bootstrap Table JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.0/dist/bootstrap-table.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>

  <!-- Filter Control Extension -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.0/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
  
  <script>
    $(function() {
      $('#table').bootstrapTable();
    });
  </script>
    <script>
    // Print Table Function
function printTable() {
    const table = document.querySelector('table'); // Assuming there's only one table in the document
    const printWindow = window.open('', '', 'height=800,width=1000');
    printWindow.document.write('<html><head><title>Print Table</title></head><body>');
    printWindow.document.write('<h1>Analytics Table</h1>');
    printWindow.document.write(table.outerHTML);  // Add the table HTML content to the window
    printWindow.document.write('</body></html>');
    printWindow.document.close();  // Necessary for IE >= 10
    printWindow.print();  // Trigger the print dialog
}

// Download Table as Excel Function
function downloadTableAsExcel() {
    const table = document.querySelector('table'); // Assuming there's only one table in the document
    const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" }); // Convert the table to a worksheet
    XLSX.writeFile(wb, 'analytics_table.xlsx');  // Download the file as 'analytics_table.xlsx'
}

// Event Listeners for Buttons
document.getElementById('printBtn').addEventListener('click', printTable);
document.getElementById('downloadBtn').addEventListener('click', downloadTableAsExcel);

    </script>
  <script>
document.addEventListener("DOMContentLoaded", function () {
    // Function to toggle visibility of a group of columns
    function toggleColumnGroup(groupClass, show) {
        // Hide or show header group
        document.querySelectorAll(`.${groupClass}`).forEach(th => {
            th.style.display = show ? "" : "none";
        });

        // Hide or show table cells in corresponding columns
        const table = document.getElementById("table");
        table.querySelectorAll(`tbody tr`).forEach(row => {
            row.querySelectorAll(`.${groupClass}`).forEach(cell => {
                cell.style.display = show ? "" : "none";
            });
        });
    }

    // Add event listeners for checkboxes
    document.querySelectorAll(".form-check-input.column-toggle").forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            const groupClass = this.dataset.group; // Get group class from data attribute
            const isChecked = this.checked;
            toggleColumnGroup(groupClass, isChecked);
        });
    });

    // Initialize visibility based on checkbox states
    document.querySelectorAll(".form-check-input.column-toggle").forEach(function (checkbox) {
        const groupClass = checkbox.dataset.group; // Get group class from data attribute
        const isChecked = checkbox.checked;
        toggleColumnGroup(groupClass, isChecked);
    });
});
      
    </script>

<script>
  // Dropdown functionality
  $(document).ready(function() {
    // When the dropdown button is clicked, toggle the dropdown menu
    $('#settingsDropdown').click(function(event) {
      event.stopPropagation(); // Prevent event from propagating to document
      $('#settingsDropdownMenu').toggle(); // Show or hide the dropdown menu
    });

    // Prevent closing the dropdown when interacting with checkboxes inside it
    $('#settingsDropdownMenu').click(function(event) {
      event.stopPropagation(); // Prevent event from closing the dropdown when clicking inside the menu
    });

    // Close the dropdown if clicked outside of it
    $(document).click(function(event) {
      if (!$(event.target).closest('#settingsDropdown').length) {
        $('#settingsDropdownMenu').hide(); // Hide the dropdown menu if clicking outside of it
      }
    });
  });
</script>
    <script>
document.addEventListener("DOMContentLoaded", function () {
    // Handle main option check/uncheck
    document.querySelectorAll(".dropdown-item > .form-check > .form-check-input").forEach(function (mainCheckbox) {
        mainCheckbox.addEventListener("change", function () {
            const isChecked = mainCheckbox.checked;
            const subOptions = mainCheckbox.closest(".dropdown-item").querySelectorAll("ul input[type='checkbox']");
            subOptions.forEach(function (subCheckbox) {
                subCheckbox.checked = isChecked;
            });
        });
    });

    // Handle sub-option check/uncheck
    document.querySelectorAll(".dropdown-item ul input[type='checkbox']").forEach(function (subCheckbox) {
        subCheckbox.addEventListener("change", function () {
            const parentItem = subCheckbox.closest(".dropdown-item");
            const mainCheckbox = parentItem.querySelector(".form-check > .form-check-input");
            const subOptions = parentItem.querySelectorAll("ul input[type='checkbox']");
            const allChecked = Array.from(subOptions).every(function (checkbox) {
                return checkbox.checked;
            });
            mainCheckbox.checked = allChecked;
        });
    });
});


    </script>


</body>
</html>



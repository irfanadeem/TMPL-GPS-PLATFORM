    <link href="https://fonts.cdnfonts.com/css/digital-7-mono" rel="stylesheet">
<style>
            body{
                background-color:#4b5470 !important;
                font-family: 'Montserrat', sans-serif;
            }
            label{
                color: white;
            }
            .btn{
                border-radius: 5px;
            }
            .stat-box{
                text-decoration: none !important;
                cursor: pointer !important;
                border-radius: 10px !important;
            }
            .stat-box{
                text-decoration: none !important;
                cursor: pointer !important;
                border-radius: 5px !important;
            }
            .stat-box:hover{
                background-color: #605DFF !important;
            }
            .form-control{
                border-radius: 4px;
            }
            .dropdown-toggle{
                border-radius: 4px !important;
            }
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #1e2944;
            border-radius: 8px;
            overflow: hidden;
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

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
        }

        .pagination button {
            background-color: #2e4b7c;
            color: white;
            border: none;
            padding: 8px 16px;
            margin: 0 4px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .pagination button.active {
            background-color: #4b6fbf;
        }

        .pagination button:hover {
            background-color: #1b3a69;
        }
    
        .card {
            background-color: #121829;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            margin: auto;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 16px;
            margin: 0;
        }

        .growth-badge {
            background-color: #32cd32;
            color: #000000;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-left: 8px;
        }

        .total-revenue {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }

        .graph {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            height: 150px;
            margin: 20px 0;
            position: relative;
        }

        .bar {
            width: 30px;
            border-radius: 5px;
            background: linear-gradient(180deg, #4b6af6, #9f82ff);
            display: inline-block;
            position: relative;
        }

        .bar span {
            display: block;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.4), transparent);
        }

        .legend {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
        }

        .legend-color {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .fashion-color {
            background: linear-gradient(90deg, #4b6af6, #9f82ff);
        }

        .others-color {
            background: #8e8e8e;
        }

        .map {
        position: relative;
        width: 600px;
        height: 300px;
        border-radius: 10px; /* Optional: Adds rounded corners */
        overflow: hidden; /* Ensures the pseudo-element stays inside the map */
        }
            .map::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('https://103.83.91.81/map.png') no-repeat center center;
        background-size: cover; /* Ensures the image covers the div proportionally */
        opacity: 0.4; /* Adds the desired transparency */
        z-index: 1;
        }

        
        @keyframes bounce-in-out {
        0%, 100% {
            transform: scale(1) translate(-50%, -50%);
        }
        50% {
            transform: scale(1.2) translate(-50%, -50%);
        }
        }


        .region {
        position: absolute;
        background-color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        transform: translate(-50%, -50%);
        cursor: pointer;
        z-index: 2; /* Ensure it's above the background */
            animation: bounce-in-out 1s ease-in-out infinite;

        }

        /* Tooltip on hover */
        .region:hover::after {
        content: attr(data-tooltip); /* Fetches the text from the data-tooltip attribute */
        position: absolute;
        top: -30px; /* Positions the tooltip above the region */
        left: 50%; /* Centers the tooltip horizontally */
        transform: translateX(-50%);
        background-color: #1e1e2f; /* Tooltip background */
        color: white; /* Tooltip text color */
        padding: 5px 10px; /* Adds spacing inside the tooltip */
        border-radius: 5px; /* Rounds tooltip corners */
        white-space: nowrap; /* Prevents wrapping */
        font-size: 12px; /* Font size for tooltip */
        z-index: 3; /* Ensures tooltip appears on top */
        }

        /* Place regions on the map */
        .region-1 {
            top: 40%;
            left: 70%;
        }

        .region-2 {
            top: 30%;
            left: 40%;
        }

        .region-3 {
            top: 50%;
            left: 20%;
        }
            /* Tooltip container */
        .tooltip-btn {
        position: relative;
        }

        .tooltip-btn:hover::after {
        content: attr(data-tooltip); /* Gets text from data-tooltip attribute */
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #1e1e2f;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        white-space: nowrap;
        font-size: 12px;
        z-index: 10;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
        }

        .tooltip-btn:hover::after {
        opacity: 1; /* Tooltip fades in on hover */
        }
                .dash-count {
                    font-family: 'Digital-7 Mono', sans-serif;
                    font-weight: bold;
                    color: rgb(254, 255, 255);
                    font-size: 32px !important;
                }
</style>

    <div class="container" style= "max-width: 100%;">
        <!-- Department & Date Range Selection Block -->
            <div class="container" style=" border-radius: 5px; padding: 5px; margin: 15px auto; max-width: 100%; background-color: #131f3a;">
                <div class="row d-flex align-items-center gap-3" style="border: radius 6px;">
                        <!-- From Date -->
                        <div class="col-md-2 col-xs-12" style="float: left;">
                            <label for="from_date">From:</label>
                            <input type="date" class="form-control" name="from_date" id="from_date" value="2024-12-14">
                        </div>

                        <!-- To Date -->
                        <div class="col-md-2 col-xs-12" style="float: left;">
                            <label for="to_date">To:</label>
                            <input type="date" class="form-control" name="to_date" id="to_date" value="2024-12-14">
                        </div>

                        <!-- Department Dropdown -->
                        <div class="col-xs-12 col-sm-4" style="float: left;">
                            <label for="default_unit_of_altitude" class="control-label">Department</label>
                            <div class="btn-group bootstrap-select form-control" style="width: 100%;">
                                <button type="button" class="btn dropdown-toggle btn-default" data-toggle="dropdown" role="button" data-id="default_unit_of_altitude" title="Meter" aria-expanded="false">
                                    <span class="icon unit-altitude pull-left"></span>
                                    <span class="filter-option pull-left">All</span>&nbsp;<span class="bs-caret">
                                        <span class="caret"></span></span>
                                </button>
                                <div class="dropdown-menu open" role="combobox" style="max-height: 137.75px; overflow: hidden; min-height: 0px;">
                                    <ul class="dropdown-menu inner" role="listbox" aria-expanded="false" style="max-height: 135.75px; overflow-y: auto; min-height: 0px;">
                                        <li data-original-index="0" class="selected"><a tabindex="0" class="" data-tokens="null" role="option" aria-disabled="false" aria-selected="true">
                                            <span class="check-mark"></span><span class="text">MPO</span></a></li>
                                        <li data-original-index="1"><a tabindex="0" class="" data-tokens="null" role="option" aria-disabled="false" aria-selected="false"><span class="check-mark"></span>
                                            <span class="text">Sanitation</span></a></li>
                                    </ul>
                                    <div class="bs-pagination"></div>
                                </div>
                            </div>
                        </div>
                            <button type="submit" class="btn btn-primary w-100 mt-4 shadow rounded" style="margin-top: 20px;background-color:#605DFF;border:none;">Submit</button>
                            <span><a href="https://103.83.91.81/mastertable.html#" class="btn btn-primary w-100 mt-4 shadow rounded" style="margin-top: 21px;border-radius: 5px;background-color:#605DFF;border:none;" role="button">Analytics</a></span>

                        <!-- Submit Button -->
                    <div class="col-md-2 col-xs-2" style="float: right; margin-top: 21px; width:18%;">
                        <button class="btn w-100 mt-4 shadow rounded tooltip-btn" style="background-color:transparent" data-tooltip="Download">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="30" height="30" fill="#605DFF">
                            <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z" />
                            </svg>
                        </button>
                        <button class="btn w-100 mt-4 shadow rounded tooltip-btn" style="background-color:transparent" data-tooltip="Print">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30" fill="#605DFF">
                            <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                            </svg>
                        </button>
                        <button id="btn-email" class="btn w-100 mt-4 shadow rounded tooltip-btn" style="background-color:transparent" data-tooltip="Email">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30" fill="#605DFF">
                            <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        <!-- Engine Stats Block -->
        <div class="container" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color:rgb(69, 66, 241);margin-top: 15px;">
                    <div class="col-md-8">
                        <div class="table-responsive">
                                                <div class="col-xs-8 col-sm-2 col-md-12" style="border-bottom: 1px solid #c0c0c073;padding-bottom: 20px;margin-bottom:20px;">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">Good Morning!, Muhammad..</div>
                                                        <div style="color:rgb(255 234 97);font-size:16px;">Here's what's happening with your vehicals today.</div> 
                                                </div> 
                                        
                                                
                                <div class="col-md-3">
                                    <a class="stat-box" style="background-color:rgb(65, 21, 51);margin-top:10px;" href="https://103.83.91.81/dashenginestats.html#" target="_blank">
                                    <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Engine Hours</div>
                                    <div class="dash-count">3455</div>
                                    </a>
                                </div>

                                <div class="col-md-3">
                                    <a class="stat-box" style="background-color:rgb(19, 58, 53);margin-top:10px;" href="https://103.83.91.81/enginestop.html#" target="_blank">
                                    <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Stop Hours</div>
                                    <div class="dash-count">6547</div>
                                    </a>
                                </div>

                                <div class="col-md-3">
                                    <a class="stat-box" style="background-color:rgb(58, 57, 19);margin-top:10px;" href="https://103.83.91.81/engineidle.html#" target="_blank">
                                    <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Idle Hours</div>
                                    <div class="dash-count">234</div>
                                    </a>
                                </div>
                    
                        </div> 
                    </div>        
                        
            
                            <div class="col-sm-4 col-md-4" style="height: 170px;">
                                <div class="panel panel-transparent" style="height: 170px;background-color:transparent">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <div class="text-center">
                                            
                                                <img src="https://103.83.91.81/truck.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 45%; float:right;">       
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
        </div>

            
        <!-- Event Tiles Block -->
        <div class="container" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
                            <div class="col-md-8">

                                            <div class="table-responsive">
                                                <div class="col-xs-8 col-sm-2 col-md-3" style="border-radius:10px;">
                                                    <a class="stat-box" id="fuel-theft" style="background-color:#a5232357;border:none;" href="https://103.83.91.81/fueltheft.html#" target="_blank">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FUEL THEFT</div>
                                                        <div class="dash-count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">10</div>
                                                        
                                                    </a>
                                                </div>
                                                    <div class="col-xs-8 col-sm-2 col-md-3">
                                                    <a class="stat-box" style="background-color: #d557087a;border: none;" href="https://103.83.91.81/fstemper.html#"  target="_blank">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FS TEMPER</div>
                                                        <div class="dash-count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">02</div>
                                                    
                                                    </a>
                                                </div>
                                                <div class="col-xs-8 col-sm-2 col-md-3">
                                                    <a class="stat-box" style="background-color:#0d90bb40;border: none;" href="https://103.83.91.81/fuelrefill.html#"  target="_blank">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FUEL REFILL</div>
                                                        <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">20</div>
                                                        
                                                    </a>
                                                </div>
                                                <div class="col-xs-8 col-sm-2 col-md-3">
                                                    <a class="stat-box" style="background-color: #40bb0d40;border: none;" href="https://103.83.91.81/accon.html#" target="_blank">
                                                    <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">IGNITION ON</div>
                                                        <div class="dash-count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">70</div>
                                                    
                                                    </a>
                                                </div>
                                                <div class="col-xs-8 col-sm-2 col-md-3">
                                                    <a class="stat-box" style="background-color: #8a000066;border: none;" href="https://103.83.91.81/accoff.html#" target="_blank">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">IGNITION OFF</div>
                                                        <div class="dash-count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">70</div>
                                                    
                                                    </a>
                                                </div>
                                                <div class="col-xs-8 col-sm-2 col-md-3">
                                                    <a class="stat-box" style="background-color: #e6ca0061;border: none;" href="https://103.83.91.81/fenceout.html#" target="_blank">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FENCE OUT</div>
                                                        <div class="dash-count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">10</div>
                                                        
                                                    </a>
                                                
                                                </div>
                                                <div class="col-xs-8 col-sm-2 col-md-3">
                                                    <a class="stat-box" style="background-color:#f51212b8;border: none;" href="https://103.83.91.81/powercut.html#" target="_blank">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">POWER CUT</div>
                                                        <div class="dash-count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">5</div>
                                                        
                                                    </a>
                                                </div>
                                                <div class="col-xs-8 col-sm-2 col-md-3">
                                                    <a class="stat-box" style="background-color:rgba(18, 181, 245, 0.72);border: none;" href="https://103.83.91.81/powercut.html#" target="_blank">
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"></div>
                                                        <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FENCE IN</div>
                                                        <div class="dash-count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">9</div>
                                                        
                                                    </a>
                                                </div>
                                </div>        
                            </div>
            
                            <div class="col-sm-4 col-md-4" style="height: 370px;">
                                <div class="panel panel-transparent" style="height: 370px;border-radius: 10px;">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <div class="text-center">
                                            
                                                <img src="https://103.83.91.81/eventschart.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">       
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
        </div>

                            
        <!-- fuel Stats Block -->  
        <div class="container" style="border-radius: 5px; padding: 16px; margin: 10px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                                <div class="pull-left">FUEL STATISTICS
                                                </div>
                                            <div class="table-responsive">
                                                <div style="float: right;">
                                                    <button class="btn w-100 mt-4 shadow rounded tooltip-btn" id="exportExcel" style="background-color:transparent" data-tooltip="Download">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="#605DFF">
                                                            <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z" />
                                                            </svg>
                                                    </button>
                                                    <button class="btn w-100 mt-4 shadow rounded tooltip-btn"  id="printTable" style="background-color:transparent" data-tooltip="Print">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF">
                                                            <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                                                            </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <!-- Fuel Stats Block -->  
                        <div class="col-md-12">
                            <div class="table-responsive">
                        
                                    <table class="dash-table" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th class="sorting_disabled" >
                                                Date
                                                </th>
                                                <th class="sorting_disabled">
                                                Consumption (L)
                                                </th>
                                                <th class="sorting_disabled">
                                                Theft (L)
                                                </th>
                                                <th class="sorting_disabled">
                                                Refill (L)
                                                </th>
                                                <th class="sorting_disabled">
                                                Level (L)
                                                </th>
                                            
                                            </tr>
                                        </thead>
                                            <tbody>
                                                    <tr>
                                                        <td>01-12-2024</td>
                                                        <td>560</td>
                                                        <td>90</td>
                                                        <td>300</td>
                                                        <td>450</td>
                                                        
                                                    </tr>
                                                    <tr>
                                                        <td>02-12-2024</td>
                                                        <td>340</td>
                                                        <td>60</td>
                                                        <td>400</td>
                                                        <td>650</td>
                                                        
                                                    </tr>
                                                    <tr>
                                                        <td>03-12-2024</td>
                                                        <td>600</td>
                                                        <td>110</td>
                                                        <td>200</td>
                                                        <td>750</td>
                                                    
                                                    </tr>
                                                    <tr>
                                                        <td>04-12-2024</td>
                                                        <td>560</td>
                                                        <td>90</td>
                                                        <td>300</td>
                                                        <td>450</td>
                                                        
                                                    </tr>
                                                    <tr>
                                                        <td>05-12-2024</td>
                                                        <td>560</td>
                                                        <td>90</td>
                                                        <td>300</td>
                                                        <td>450</td>
                                                        
                                                    </tr>
                                                    <tr>
                                                        <td>06-12-2024</td>
                                                        <td>560</td>
                                                        <td>90</td>
                                                        <td>300</td>
                                                        <td>450</td>
                                                    
                                                    </tr>
                                                                    
                                            </tbody>
                                            <thead>
                                            <tr>
                                                <th>
                                                Total
                                                </th>
                                                <th class="sorting_disabled">
                                                3180
                                                </th>
                                                <th class="sorting_disabled">
                                                530
                                                </th>
                                                <th class="sorting_disabled">
                                                1800
                                                </th>
                                                <th class="sorting_disabled">
                                                3200
                                                </th>
                                            
                                            </tr>
                                        </thead>
                                    </table>   
                            </div>   
                    </div>
                <!-- Fuel Chart Block -->  
                    <div class="col-md-12">
                        <img src="https://103.83.91.81/fuelChart_new.png" alt="Fuel Static Chart" class="img-fluid" style="max-width: 100%; border-radius: 10px;margin-bottom: 30px;">       
                    </div>
        </div>         
                        

        <!-- Travel Stats Block --> 
        <div class="container" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                                <div class="pull-left">Travel Statistics
                                                </div>
                                            <div class="table-responsive">
                                                <div style="float: right;">
                                                    <button class="btn w-100 mt-4 shadow rounded tooltip-btn" id="exportExcel2" style="background-color:transparent" data-tooltip="Download">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="#605DFF">
                                                            <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z" />
                                                            </svg>
                                                    </button>
                                                    <button class="btn w-100 mt-4 shadow rounded tooltip-btn"  id="printTable2" style="background-color:transparent" data-tooltip="Print">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF">
                                                            <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                                                            </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                    <div class="col-md-4">
                        <div class="table-responsive">
                            <table class="dash-table" id="dataTable2">
                                <thead>
                                <tr >
                                        <th class="sorting_disabled">
                                        Date
                                        </th>
                                        <th class="sorting_disabled">
                                        Travelled KM
                                        </th>
                                        
                                    </tr>
                                </thead>
                                    <tbody>
                                            <tr>
                                                <td>01-12-2024</td>
                                                <td>560</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>02-12-2024</td>
                                                <td>340</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>03-12-2024</td>
                                                <td>600</td>
                                                
                                            </tr>
                                            <tr>
                                                <td>04-12-2024</td>
                                                <td>560</td>
                                                
                                            </tr>
                                            <tr>
                                                <td>05-12-2024</td>
                                                <td>560</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>05-12-2024</td>
                                                <td>560</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>05-12-2024</td>
                                                <td>560</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>05-12-2024</td>
                                                <td>560</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>05-12-2024</td>
                                                <td>560</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>05-12-2024</td>
                                                <td>560</td>
                                            
                                            </tr>
                                            <thead>
                                            <tr>
                                        <th class="sorting_disabled">
                                        Total KM
                                        </th>
                                        <th class="sorting_disabled">
                                        2620
                                        </th>
                                    </tr>
                                </thead>
                                                            
                                    </tbody>
                            </table>
                        </div>
                    </div>
                                <div class="col-md-8">
                                <img src="https://103.83.91.81/travelchart_new.png" alt="Fuel Static Chart" class="img-fluid" style="max-width: 100%; border-radius: 10px;margin-bottom: 30px;">       
                                </div>
        </div>

        <!-- Fuel Theft Insights Stats Block --> 
        <div class="container" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
                                <div class="col-md-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                    <div class="pull-left">Fuel Theft Insight
                                                    </div>
                                                <div class="table-responsive">
                                                    <div style="float: right;">
                                                        <button class="btn w-100 mt-4 shadow rounded tooltip-btn" id="exportExcel3" style="background-color:transparent" data-tooltip="Download">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="#605DFF">
                                                                <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z" />
                                                                </svg>
                                                        </button>
                                                        <button class="btn w-100 mt-4 shadow rounded tooltip-btn"  id="printTable3" style="background-color:transparent" data-tooltip="Print">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF">
                                                                <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                                                                </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <table class="dash-table" id="dataTable3">
                                            <thead>
                                                <tr>
                                                <th class="sorting_disabled">
                                                Department
                                                </th>
                                                <th class="sorting_disabled">
                                                Vehicle
                                                </th>
                                                <th class="sorting_disabled">
                                                Date&Time
                                                </th>
                                                <th class="sorting_disabled">
                                                Liters
                                                </th>
                                                <th class="sorting_disabled">
                                                Location
                                                </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                    <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                    <td>Sanitation</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                <td>GF:068 (FS)</td>
                                                    <td>01-12-2024</td>
                                                    <td>50</td>
                                                    <td>MAP</td>
                                                </tr>
                                            
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                  
                        <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                        <div class="pull-left">Geofence
                                                        </div>
                                                    <div class="table-responsive">
                                                        <div style="float: right;">
                                                            <button class="btn w-100 mt-4 shadow rounded tooltip-btn" id="exportExcel4" style="background-color:transparent" data-tooltip="Download">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="#605DFF">
                                                                    <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z" />
                                                                    </svg>
                                                            </button>
                                                            <button class="btn w-100 mt-4 shadow rounded tooltip-btn"  id="printTable4" style="background-color:transparent" data-tooltip="Print">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF">
                                                                    <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                                                                    </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <div>
                                        <table class="dash-table" id="dataTable4">
                                            <thead>
                                                <tr>
                                                <th class="sorting_disabled">
                                                Department
                                                </th>
                                                <th class="sorting_disabled">
                                                Device
                                                </th>
                                                <th class="sorting_disabled">
                                                GeoFence IN
                                                </th>
                                                <th class="sorting_disabled">
                                                GeoFence OUT
                                                </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                            
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                            
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                            
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                                <tr>
                                                <td>MPO</td>
                                                    <td>GF:068 (FS)</td>
                                                    <td>10</td>
                                                    <td>10</td>
                                                </tr>
                                            </tbody>
                                        </table>   
                                    </div>
                            </div>
        </div>

        <!-- GeoFence Stats Block --> 
        <div class="container" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
                    <div class="col-md-8">
                        <img src="https://103.83.91.81/fuelaverag.png" alt="Geofence Chart" class="img-fluid" style="max-width: 100%; border-radius: 10px;margin-bottom: 30px;">       
                    </div>
                </div>
    </div>

        <!-- END FUEL EFFECIENCY CHART -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
    <script>
const xValues = [100,200,300,400,500,600,700,800,900,1000];

new Chart("fuelTheft", {
  type: "line",
  data: {
    labels: xValues,
    datasets: [{ 
      data: [860,1140,1060,1060,1070,1110,1330,2210,7830,2478],
      borderColor: "red",
      fill: false
    }, { 
      data: [1600,1700,1700,1900,2000,2700,4000,5000,6000,7000],
      borderColor: "green",
      fill: false
    }, { 
      data: [300,700,2000,5000,6000,4000,2000,1000,200,100],
      borderColor: "blue",
      fill: false
    }]
  },
  options: {
    legend: {display: false}
  }
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
const xValues = [50,60,70,80,90,100,110,120,130,140,150];
const yValues = [7,8,8,9,9,9,10,11,14,14,15];

new Chart("fuelStatic", {
  type: "line",
  data: {
    labels: xValues,
    datasets: [{
      fill: false,
      lineTension: 0,
      backgroundColor: "rgba(0,0,255,1.0)",
      borderColor: "rgba(0,0,255,0.1)",
      data: yValues
    }]
  },
  options: {
    legend: {display: false},
    scales: {
      yAxes: [{ticks: {min: 6, max:16}}],
    }
  }
});
</script>
 <script>
        // Data to populate the chart
        const data = {
            totalFuel: 165458, // Total fuel refill
          
            categories: {
                Consumption: 75, // percentage
                Theft: 25, // percentage
            },
            bars: [40, 60, 80, 70, 90, 100] // Heights in percentages
        };

        // Populate total fuel and growth data
        document.getElementById('total-revenue').textContent = data.totalFuel.toLocaleString();

        // Populate percentages for Consumption and Theft
        document.getElementById('consumption-percentage').textContent = `${data.categories.Consumption}%`;
        document.getElementById('theft-percentage').textContent = `${data.categories.Theft}%`;

        // Generate bars dynamically
        const graphContainer = document.getElementById('graph');
        data.bars.forEach(height => {
            const bar = document.createElement('div');
            bar.classList.add('bar');
            bar.style.height = `${height}%`;

            // Add gradient overlay
            const span = document.createElement('span');
            bar.appendChild(span);

            graphContainer.appendChild(bar);
        });
    </script>
    <script>
var xValues = [];
var yValues = [];
generateData("Math.sin(x)", 0, 10, 0.5);

new Chart("geoFence", {
  type: "line",
  data: {
    labels: xValues,
    datasets: [{
      fill: false,
      pointRadius: 2,
      borderColor: "rgba(0,0,255,0.5)",
      data: yValues
    }]
  },    
  options: {
    legend: {display: false},
    title: {
      display: true,
      text: "y = sin(x)",
      fontSize: 16
    }
  }
});
function generateData(value, i1, i2, step = 1) {
  for (let x = i1; x <= i2; x += step) {
    yValues.push(eval(value));
    xValues.push(x);
  }
}
</script>
     <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        // Print functionality
        document.getElementById("printTable").addEventListener("click", function () {
            const tableContent = document.getElementById("dataTable").outerHTML;
            const newWindow = window.open();
            newWindow.document.write('<html><head><title>Fuel Statics</title></head><body>');
            newWindow.document.write(tableContent);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        });

        // Export to Excel functionality
        document.getElementById("exportExcel").addEventListener("click", function () {
            const table = document.getElementById("dataTable");
            const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            XLSX.writeFile(workbook, "Fuel_Statics.xlsx");
        });
        
                // Print functionality 2
        document.getElementById("printTable2").addEventListener("click", function () {
            const tableContent = document.getElementById("dataTable2").outerHTML;
            const newWindow = window.open();
            newWindow.document.write('<html><head><title>Traveling Statics</title></head><body>');
            newWindow.document.write(tableContent);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        });

        // Export to Excel functionality 2
        document.getElementById("exportExcel2").addEventListener("click", function () {
            const table = document.getElementById("dataTable2");
            const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet2" });
            XLSX.writeFile(workbook, "Traveling_Statics.xlsx");
        });
              
                // Print functionality 3
        document.getElementById("printTable3").addEventListener("click", function () {
            const tableContent = document.getElementById("dataTable3").outerHTML;
            const newWindow = window.open();
            newWindow.document.write('<html><head><title>Fuel Theft Insights</title></head><body>');
            newWindow.document.write(tableContent);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        });

        // Export to Excel functionality 3
        document.getElementById("exportExcel3").addEventListener("click", function () {
            const table = document.getElementById("dataTable3");
            const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet3" });
            XLSX.writeFile(workbook, "FuelTheftInsights.xlsx");
        });
                      // Print functionality 4
        document.getElementById("printTable4").addEventListener("click", function () {
            const tableContent = document.getElementById("dataTable4").outerHTML;
            const newWindow = window.open();
            newWindow.document.write('<html><head><title>GeoFence</title></head><body>');
            newWindow.document.write(tableContent);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        });

        // Export to Excel functionality 4
        document.getElementById("exportExcel4").addEventListener("click", function () {
            const table = document.getElementById("dataTable4");
            const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet4" });
            XLSX.writeFile(workbook, "GeoFence.xlsx");
        });
    </script>
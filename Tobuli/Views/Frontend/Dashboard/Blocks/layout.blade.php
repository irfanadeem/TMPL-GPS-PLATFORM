<style>
            body{
                background-color:#4b5470 !important;
                font-family: 'Montserrat', sans-serif;
            }
            .dashboard-block label{
                color: white;
            }
            .panel-body{
            height: auto !important;
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
        .chart-container, 
.chart-container-fuel, 
.chart-container-travel {
    padding: 20px;
    border-radius: 12px;
    width: 98%;
    margin-left: 15px;
}

/* Specific styles for the primary chart container */
.chart-container {
    height: 500px;
    background-color: #000000;
}

/* Specific styles for the fuel chart container */
.chart-container-fuel {
    height: 500px;
    border: 1px solid #000000;
}
.chart-container-travel {
    height: 300px;
    background-color: rgba(39, 38, 38, 0.87);
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
<div class="dashboard-block @yield('width', 'col-sm-6 col-md-4 col-lg-3')" id="{{ "block_$name" }}">
    <div class="panel panel-default" style="background-color: #171821;">
        <div class="panel-heading">
            <div class="panel-title">
                <div class="pull-left">
                    @yield('header')
                </div>

                <div class="pull-right">
                    @if(View::exists("Frontend.Dashboard.Blocks.$name.options"))
                        <div class="btn-group droparrow" data-position="fixed">
                            <i class="btn icon options"
                               data-toggle="dropdown"
                               aria-haspopup="true"
                               aria-expanded="false"></i>

                            <div class="dropdown-menu dropdown-menu-right">
                                @include("Frontend.Dashboard.Blocks.$name.options", [
                                    'options' => $config['options'],
                                    'block'   => $name,
                                ])
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel-body dashboard-content">
            @yield('body')
        </div>
    </div>

    @yield('scripts')
</div>
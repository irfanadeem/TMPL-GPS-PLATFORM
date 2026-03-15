<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Table Report - Print</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        
        .filters span {
            margin-right: 20px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            font-size: 10px;
        }
        
        .numeric {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .no-print {
            margin-bottom: 20px;
        }
        
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()">Print</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>
    
    <div class="header">
        <h1>Master Table Report</h1>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>
    
    <div class="filters">
        <span>Date Range: {{ $dateFrom }} to {{ $dateTo }}</span>
        @if($department)
            <span>Department: {{ $department }}</span>
        @endif
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Device Name</th>
                <th>Device ID</th>
                <th>Department</th>
                <th>Date</th>
                <th class="numeric">Distance (km)</th>
                <th class="numeric">Max Fuel Level</th>
                <th class="numeric">Fuel Consumption</th>
                <th class="numeric">Fuel Average</th>
                <th>Moving Time</th>
                <th>Idle Time</th>
                <th>Stop Time</th>
                <th class="numeric">Zone Out</th>
                <th class="numeric">Power Cut</th>
                <th class="numeric">FS Temper</th>
                <th>Sensor Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['device_name'] }}</td>
                    <td>{{ $row['device_id'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['created_at'] }}</td>
                    <td class="numeric">{{ number_format($row['distance'], 2) }}</td>
                    <td class="numeric">{{ number_format($row['max_fuel_level'], 2) }}</td>
                    <td class="numeric">{{ number_format($row['fuel_consumption'], 2) }}</td>
                    <td class="numeric">{{ number_format($row['fuel_average'], 2) }}</td>
                    <td>{{ $row['total_moving_time'] }}</td>
                    <td>{{ $row['total_idle_time'] }}</td>
                    <td>{{ $row['total_stop_time'] }}</td>
                    <td class="numeric">{{ $row['zone_out_count'] }}</td>
                    <td class="numeric">{{ $row['power_cut_count'] }}</td>
                    <td class="numeric">{{ $row['fs_temper_count'] }}</td>
                    <td>{{ $row['sensor_type'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" style="text-align: center;">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Total Records: {{ $data->count() }}</p>
        <p>Report generated by Tracker System</p>
    </div>
</body>
</html>

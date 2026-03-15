<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\Device;
use Tobuli\Entities\User;
use Validator;
use Formatter;

class TemperatureController extends ApiController
{
    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required',
            'to_date' => 'required',
            'device_id' => 'required', // Now required for direct position query
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Missing required parameter: ' . $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $user = $this->user;
        $deviceId = $request->get('device_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $interval = $request->get('interval', 'hourly');

        $device = Device::find($deviceId);
        if (!$device || !$user->can('view', $device)) {
            return response()->json(['status' => 0, 'message' => 'Device not found or unauthorized', 'data' => null], 404);
        }

        Formatter::byUser($user);

        // If only date is provided, append time to cover the whole day
        if (strlen($fromDate) == 10) $fromDate .= ' 00:00:00';
        if (strlen($toDate) == 10) $toDate .= ' 23:59:59';

        $fromDate = Formatter::time()->reverse($fromDate);
        $toDate = Formatter::time()->reverse($toDate);

        // Get sensors
        $sensors = $device->sensors()->where('type', 'temperature')->get();
        $tempSensor = $sensors->filter(function($s) { return stripos($s->name, 'temp') !== false; })->first();
        $humSensor = $sensors->filter(function($s) { return stripos($s->name, 'hum') !== false; })->first();

        if (!$tempSensor && !$humSensor) {
             return response()->json(['status' => 0, 'message' => 'No temperature/humidity sensors found for this device', 'data' => null], 404);
        }

        $tempTag = $tempSensor ? $tempSensor->tag_name : null;
        $tempFormula = $tempSensor ? $tempSensor->formula : null;
        $humTag = $humSensor ? $humSensor->tag_name : null;
        $humFormula = $humSensor ? $humSensor->formula : null;

        // Query positions directly
        $tableName = "positions_" . $device->id;
        try {
            $positions = DB::connection('traccar_mysql')
                ->table($tableName)
                ->whereBetween('device_time', [$fromDate, $toDate])
                ->orderBy('device_time', 'ASC')
                ->get();
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error fetching positions: ' . $e->getMessage(), 'data' => null], 500);
        }

        $readings = [];
        foreach ($positions as $pos) {
            $reading = [
                'timestamp' => Formatter::time()->human($pos->device_time),
                'temperature' => null,
                'humidity' => null
            ];

            if ($tempTag) {
                $val = parseTagValue($pos->other, $tempTag);
                if ($val !== null) {
                    $val = (float)$val;
                    if ($tempFormula) {
                        $val = solveEquation(['[value]' => $val], $tempFormula);
                    }
                    $reading['temperature'] = round($val, 2);
                }
            }

            if ($humTag) {
                $val = parseTagValue($pos->other, $humTag);
                if ($val !== null) {
                    $val = (float)$val;
                    if ($humFormula) {
                        $val = solveEquation(['[value]' => $val], $humFormula);
                    }
                    $reading['humidity'] = round($val, 2);
                }
            }

            if ($reading['temperature'] !== null || $reading['humidity'] !== null) {
                $readings[] = $reading;
            }
        }

        // Calculate statistics from raw data before aggregation
        $tempValues = array_filter(array_column($readings, 'temperature'), function($v) { return !is_null($v); });
        $humValues = array_filter(array_column($readings, 'humidity'), function($v) { return !is_null($v); });

        $statistics = [
            'temperature' => [
                'current' => !empty($tempValues) ? end($tempValues) : null,
                'max' => !empty($tempValues) ? max($tempValues) : null,
                'min' => !empty($tempValues) ? min($tempValues) : null,
                'average' => !empty($tempValues) ? round(array_sum($tempValues) / count($tempValues), 2) : null,
                'unit' => '°C'
            ],
            'humidity' => [
                'current' => !empty($humValues) ? end($humValues) : null,
                'max' => !empty($humValues) ? max($humValues) : null,
                'min' => !empty($humValues) ? min($humValues) : null,
                'average' => !empty($humValues) ? round(array_sum($humValues) / count($humValues), 2) : null,
                'unit' => '%'
            ]
        ];

        if ($interval === 'hourly' || $interval === 'daily') {
            $format = $interval === 'hourly' ? 'Y-m-d H:00:00' : 'Y-m-d 00:00:00';
            $grouped = [];
            foreach ($readings as $r) {
                $key = date($format, strtotime($r['timestamp']));
                if (!isset($grouped[$key])) {
                    $grouped[$key] = ['t_sum' => 0, 't_count' => 0, 'h_sum' => 0, 'h_count' => 0];
                }
                if ($r['temperature'] !== null) {
                    $grouped[$key]['t_sum'] += $r['temperature'];
                    $grouped[$key]['t_count']++;
                }
                if ($r['humidity'] !== null) {
                    $grouped[$key]['h_sum'] += $r['humidity'];
                    $grouped[$key]['h_count']++;
                }
            }

            $readings = [];
            foreach ($grouped as $time => $stats) {
                $readings[] = [
                    'timestamp' => $time,
                    'temperature' => $stats['t_count'] > 0 ? round($stats['t_sum'] / $stats['t_count'], 2) : null,
                    'humidity' => $stats['h_count'] > 0 ? round($stats['h_sum'] / $stats['h_count'], 2) : null,
                ];
            }
        }


        return response()->json([
            'status' => 1,
            'message' => 'Success',
            'data' => [
                'device_id' => $deviceId,
                'device_name' => $device->name,
                'from_date' => Formatter::time()->human($fromDate),
                'to_date' => Formatter::time()->human($toDate),
                'interval' => $interval,
                'statistics' => $statistics,
                'readings' => $readings,
                'total_readings' => count($readings)
            ]
        ]);
    }


    public function fleetSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Missing required parameter: ' . $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $user = $this->user;

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        Formatter::byUser($user);
        $fromDate = Formatter::time()->reverse($fromDate);
        $toDate = Formatter::time()->reverse($toDate);

        $accessibleDevices = $user->accessibleDevices();
        $totalDevicesCount = $accessibleDevices->count();
        $deviceIds = $accessibleDevices->pluck('devices.id')->toArray();

        $stats = DB::table('sensor_readings')
            ->whereBetween('timestamp', [$fromDate, $toDate])
            ->whereIn('sensor_type', ['temperature', 'humidity'])
            ->whereIn('device_id', $deviceIds)
            ->select([
                'sensor_type',
                DB::raw('MAX(sensor_value) as max_val'),
                DB::raw('MIN(sensor_value) as min_val'),
                DB::raw('AVG(sensor_value) as avg_val'),
            ])->groupBy('sensor_type')->get()->keyBy('sensor_type');

        $deviceStats = DB::table('sensor_readings')
            ->whereBetween('timestamp', [$fromDate, $toDate])
            ->whereIn('sensor_type', ['temperature', 'humidity'])
            ->whereIn('device_id', $deviceIds)
            ->select([
                'device_id',
                'sensor_type',
                DB::raw('MAX(sensor_value) as max_val'),
                DB::raw('MIN(sensor_value) as min_val'),
                DB::raw('AVG(sensor_value) as avg_val'),
                DB::raw('(SELECT sensor_value FROM sensor_readings sr2 WHERE sr2.device_id = sensor_readings.device_id AND sr2.sensor_type = sensor_readings.sensor_type ORDER BY timestamp DESC LIMIT 1) as current_val')
            ])
            ->groupBy('device_id', 'sensor_type')
            ->get();

        $devicesWithData = $deviceStats->pluck('device_id')->unique()->count();

        $devicesFormatted = [];
        $devicesModels = Device::whereIn('id', $deviceIds)->get()->keyBy('id');

        foreach ($deviceStats as $ds) {
            $id = $ds->device_id;
            if (!isset($devicesFormatted[$id])) {
                $devicesFormatted[$id] = [
                    'device_id' => (string)$id,
                    'device_name' => $devicesModels[$id]->name ?? 'Unknown',
                    'temperature' => null,
                    'humidity' => null,
                ];
            }
            $devicesFormatted[$id][$ds->sensor_type] = [
                'current' => (float)$ds->current_val,
                'max' => (float)$ds->max_val,
                'min' => (float)$ds->min_val,
                'average' => round((float)$ds->avg_val, 2),
            ];
        }

        $response = [
            'status' => 1,
            'message' => 'Success',
            'data' => [
                'from_date' => Formatter::time()->human($fromDate),
                'to_date' => Formatter::time()->human($toDate),
                'total_devices' => $totalDevicesCount,
                'devices_with_data' => $devicesWithData,
                'fleet_statistics' => [
                    'temperature' => [
                        'max' => isset($stats['temperature']) ? (float)$stats['temperature']->max_val : null,
                        'min' => isset($stats['temperature']) ? (float)$stats['temperature']->min_val : null,
                        'average' => isset($stats['temperature']) ? round((float)$stats['temperature']->avg_val, 2) : null,
                        'unit' => '°C'
                    ],
                    'humidity' => [
                        'max' => isset($stats['humidity']) ? (float)$stats['humidity']->max_val : null,
                        'min' => isset($stats['humidity']) ? (float)$stats['humidity']->min_val : null,
                        'average' => isset($stats['humidity']) ? round((float)$stats['humidity']->avg_val, 2) : null,
                        'unit' => '%'
                    ]
                ],
                'devices' => array_values($devicesFormatted)
            ]
        ];

        return response()->json($response);
    }

    public function latest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Missing required parameter: ' . $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $user = $this->user;
        $deviceId = $request->get('device_id');
        $device = Device::find($deviceId);
        if (!$device || !$user->can('view', $device)) {
            return response()->json(['status' => 0, 'message' => 'Device not found or unauthorized', 'data' => null], 404);
        }

        // Get sensors
        $sensors = $device->sensors()->where('type', 'temperature')->get();
        $tempSensor = $sensors->filter(function($s) { return stripos($s->name, 'temp') !== false; })->first();
        $humSensor = $sensors->filter(function($s) { return stripos($s->name, 'hum') !== false; })->first();

        if (!$tempSensor && !$humSensor) {
            return response()->json(['status' => 0, 'message' => 'No temperature/humidity sensors found for this device', 'data' => null], 404);
        }

        Formatter::byUser($user);

        // Get last position
        $tableName = "positions_" . $device->id;
        try {
            $pos = DB::connection('traccar_mysql')
                ->table($tableName)
                ->orderBy('device_time', 'DESC')
                ->first();
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error fetching position: ' . $e->getMessage(), 'data' => null], 500);
        }

        if (!$pos) {
            return response()->json(['status' => 0, 'message' => 'No positions found for this device', 'data' => null], 404);
        }

        $tempVal = null;
        if ($tempSensor) {
            $val = parseTagValue($pos->other, $tempSensor->tag_name);
            if ($val !== null) {
                $val = (float)$val;
                if ($tempSensor->formula) {
                    $val = solveEquation(['[value]' => $val], $tempSensor->formula);
                }
                $tempVal = round($val, 2);
            }
        }

        $humVal = null;
        if ($humSensor) {
            $val = parseTagValue($pos->other, $humSensor->tag_name);
            if ($val !== null) {
                $val = (float)$val;
                if ($humSensor->formula) {
                    $val = solveEquation(['[value]' => $val], $humSensor->formula);
                }
                $humVal = round($val, 2);
            }
        }

        $tempStatus = 'normal';
        if ($tempVal !== null) {
            if ($tempVal > 50 || $tempVal < -20) $tempStatus = 'critical';
            elseif ($tempVal > 40 || ($tempVal < -10 && $tempVal >= -20)) $tempStatus = 'warning';
        }

        $humStatus = 'normal';
        if ($humVal !== null) {
            if ($humVal > 85 || $humVal < 20) $humStatus = 'critical';
            elseif ($humVal > 70 || ($humVal < 30 && $humVal >= 20)) $humStatus = 'warning';
        }

        return response()->json([
            'status' => 1,
            'message' => 'Success',
            'data' => [
                'device_id' => (string)$deviceId,
                'device_name' => $device->name,
                'timestamp' => Formatter::time()->human($pos->device_time),
                'temperature' => [
                    'value' => $tempVal,
                    'unit' => '°C',
                    'status' => $tempStatus
                ],
                'humidity' => [
                    'value' => $humVal,
                    'unit' => '%',
                    'status' => $humStatus
                ]
            ]
        ]);
    }
}



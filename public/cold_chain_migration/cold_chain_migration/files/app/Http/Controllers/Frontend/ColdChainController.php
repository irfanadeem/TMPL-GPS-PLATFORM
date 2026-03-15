<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Tobuli\Entities\DeviceSensor;

class ColdChainController extends Controller
{
    public function index()
    {
        $devices = $this->user->devices()
            ->with(['sensors' => function($query) {
                $query->where('type', 'temperature');
            }])
            ->whereHas('sensors', function($query) {
                $query->where('type', 'temperature');
            })
            ->get();

        return view('front::Objects.tabs.coldchain')->with(compact('devices'));
    }

    public function history()
    {
        $deviceId = request()->get('device_id');
        $sensorId = request()->get('sensor_id');
        $from = request()->get('from');
        $to = request()->get('to');

        $device = $this->user->devices()->find($deviceId);
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $sensor = $device->sensors()->find($sensorId);
        if (!$sensor || $sensor->type !== 'temperature') {
            return response()->json(['error' => 'Sensor not found'], 404);
        }

        // Fetch history data. 
        // In GPSWox, history is usually stored in positions table or dedicated sensor history.
        // Let's check how history is fetched for sensors.
        
        $positions = $device->positions()
            ->whereBetween('time', [$from, $to])
            ->orderBy('time', 'asc')
            ->get(['time', 'other']);

        $data = [];
        foreach ($positions as $position) {
            $value = $sensor->getValue($position->other);
            if ($value !== null) {
                $data[] = [
                    't' => $position->time,
                    'v' => $value
                ];
            }
        }

        return response()->json([
            'sensor' => $sensor->name,
            'unit' => $sensor->unit,
            'data' => $data
        ]);
    }
}

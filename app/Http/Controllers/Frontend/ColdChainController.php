<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Tobuli\Entities\DeviceSensor;
use Formatter;
use Carbon\Carbon;

class ColdChainController extends Controller
{
    public function index()
    {
        $devices = $this->user->devices()
            ->with('sensors')
            ->whereHas('sensors', function($query) {
                $query->where('type', 'temperature');
            })
            ->get();

        return view('front::Objects.tabs.coldchain')->with(['coldChainDevices' => $devices]);
    }

    public function history()
    {
        $deviceId = request()->get('device_id');
        $tempSensorId = request()->get('temp_sensor_id');
        $humSensorId = request()->get('hum_sensor_id');
        
        $from = request()->get('from');
        $to = request()->get('to');

        Formatter::byUser($this->user);

        $device = $this->user->devices()->find($deviceId);
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $tempSensor = $tempSensorId && $tempSensorId != 'null' ? $device->sensors->find($tempSensorId) : null;
        $humSensor = $humSensorId && $humSensorId != 'null' ? $device->sensors->find($humSensorId) : null;

        if (!$tempSensor && !$humSensor) {
            return response()->json(['error' => 'No sensors found'], 404);
        }
        
        // Convert to UTC for database query
        $fromUtc = Formatter::time()->reverse($from);
        $toUtc = Formatter::time()->reverse($to);

        $positions = $device->positions()
            ->whereBetween('time', [$fromUtc, $toUtc])
            ->orderBy('time', 'asc')
            ->get(['time', 'other']);

        $hourlyReadings = [];
        $tempData = [];
        $humData = [];
        
        $tempStats = ['min' => null, 'max' => null];
        $humStats = ['min' => null, 'max' => null];

        $updateStats = function(&$stats, $val) {
            if ($val !== null) {
                if ($stats['min'] === null || $val < $stats['min']) $stats['min'] = $val;
                if ($stats['max'] === null || $val > $stats['max']) $stats['max'] = $val;
            }
        };

        $groupedReadings = [];

        foreach ($positions as $position) {
            $tVal = $tempSensor ? $tempSensor->getValue($position->other, false) : null;
            $hVal = $humSensor ? $humSensor->getValue($position->other, false) : null;
            
            // Format time to user's timezone
            $timeDisplay = Formatter::time()->convert($position->time);
            
            if ($tVal !== null && $tVal !== '-' || $hVal !== null && $hVal !== '-') {
                if ($tVal !== null && $tVal !== '-') {
                    $tValFloat = floatval($tVal);
                    $tempData[] = ['t' => $timeDisplay, 'v' => $tValFloat];
                    $updateStats($tempStats, $tValFloat);
                }
                if ($hVal !== null && $hVal !== '-') {
                    $hValFloat = floatval($hVal);
                    $humData[] = ['t' => $timeDisplay, 'v' => $hValFloat];
                    $updateStats($humStats, $hValFloat);
                }
                
                // Group by hour for the list view
                $hourKey = Formatter::time()->convert($position->time, 'Y-m-d H');
                if (!isset($groupedReadings[$hourKey])) {
                    $groupedReadings[$hourKey] = [
                        'time' => $timeDisplay,
                        'temp' => ($tVal !== null && $tVal !== '-') ? number_format(floatval($tVal), 1) : null,
                        'hum' => ($hVal !== null && $hVal !== '-') ? number_format(floatval($hVal), 1) : null
                    ];
                }
            }
        }
        
        $hourlyReadings = array_reverse(array_values($groupedReadings));

        return response()->json([
            'hourly_readings' => $hourlyReadings,
            'last_updated' => count($hourlyReadings) > 0 ? $hourlyReadings[0]['time'] : 'N/A',
            'temp_parameter' => $tempSensor ? $tempSensor->tag_name : '-',
            'hum_parameter' => $humSensor ? $humSensor->tag_name : '-',
            'temp_stats' => [
                'maximum' => $tempStats['max'], 
                'minimum' => $tempStats['min']
            ],
            'hum_stats' => [
                'maximum' => $humStats['max'], 
                'minimum' => $humStats['min']
            ],
            'temp_data' => $tempData,
            'hum_data' => $humData,
            'temp_unit' => $tempSensor ? $tempSensor->unit_of_measurement : '°C',
            'hum_unit' => $humSensor ? $humSensor->unit_of_measurement : '%'
        ]);
    }
}

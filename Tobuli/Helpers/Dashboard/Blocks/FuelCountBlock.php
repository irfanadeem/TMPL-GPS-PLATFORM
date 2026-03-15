<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\CronJobCalculation;

class FuelCountBlock extends Block
{
    const DAYS_PERIOD = 5;

    /**
     * @return string
     */
    protected function getName()
    {
        return 'fuel_count';
    }

    /**
     * Enhanced fuel count data using CronJobCalculation
     *
     * @return array
     */
    protected function getContent()
    {
        $option = $this->getConfig("options");
        $fromDate = $option['from_date'] ?? Carbon::now()->format('Y-m-d');
        $toDate = $option['to_date'] ?? Carbon::now()->format('Y-m-d');
        $department = $option['department'] ?? null;
        
        
        // Get user's accessible devices with department filter
        $devicesQuery = $this->user->accessibleDevicesWithGroups();
        if ($department) {
            $devicesQuery->where('user_device_pivot.group_id', $department);
        }
        $devices = $devicesQuery->get();
        $deviceIds = $devices->pluck('id')->toArray();
        
        
        if (empty($deviceIds)) {
            return [
                'data' => json_encode([]),
                'keys' => json_encode([]),
            ];
        }
        
        // Get fuel data from CronJobCalculation grouped by department
        $fuelData = $this->getFuelDataByGroup($fromDate, $toDate, $deviceIds);
        
        return [
            'data' => json_encode($fuelData['results']),
            'keys' => json_encode($fuelData['keys']),
            'department' => $department,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }
    
    /**
     * Get fuel data grouped by department using CronJobCalculation
     */
    private function getFuelDataByGroup($fromDate, $toDate, $deviceIds)
    {
        $query = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('cron_job_calculations.device_id', $deviceIds)
            ->where('user_device_pivot.user_id', $this->user->id);
            
        $query->selectRaw("
            device_groups.title as group_name,
            SUM(fuel_consumption) as total_fuel_consumption,
            SUM(total_fuel_theft) as total_fuel_theft,
            SUM(total_fuel_filled) as total_fuel_filled,
            COUNT(DISTINCT cron_job_calculations.device_id) as device_count
        ");
        
        $query->join('devices', 'devices.id', '=', 'cron_job_calculations.device_id')
              ->leftJoin('user_device_pivot', 'user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
              ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id');
              
        $query->groupBy('device_groups.title')
              ->orderBy('device_groups.title');
              
        $data = $query->get();
        
        // Optimized snapshot calculation for Current Fuel Level (>0 AND <2999)
        $snapshotLevelQuery = DB::table('cron_job_calculations as cjc')
            ->join('user_device_pivot', 'user_device_pivot.device_id', '=', 'cjc.device_id')
            ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
            ->selectRaw("
                device_groups.title as group_name,
                SUM(cjc.max_fuel_level) as total_snapshot_level
            ")
            ->whereIn(DB::raw("(cjc.device_id, cjc.id)"), function($query) use ($fromDate, $toDate, $deviceIds) {
                $query->select('device_id', DB::raw('MAX(id)'))
                    ->from('cron_job_calculations')
                    ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
                    ->whereIn('device_id', $deviceIds)
                    ->where('max_fuel_level', '>', 0)
                    ->where('max_fuel_level', '<', 2999)
                    ->groupBy('device_id');
            })
            ->where('user_device_pivot.user_id', $this->user->id)
            ->groupBy('device_groups.title')
            ->get()
            ->pluck('total_snapshot_level', 'group_name')
            ->all();
        
        
        // Initialize arrays
        $results = [];
        $groupSums = [];
        $fuelFillSums = [];
        $fuelTheftSums = [];
        $fuelConsumption = [];
        $groupCounts = [];
        
        $totalFuelLevel = 0;
        $totalFuelFill = 0;
        $totalFuelTheft = 0;
        $totalFuelConsumption = 0;
        
        // Process each group
        foreach ($data as $group) {
            $groupName = $group->group_name ?: 'Unassigned';
            
            // Initialize group data
            if (!isset($groupSums[$groupName])) {
                $groupSums[$groupName] = 0;
                $fuelFillSums[$groupName] = 0;
                $fuelTheftSums[$groupName] = 0;
                $fuelConsumption[$groupName] = 0;
                $groupCounts[$groupName] = 0;
            }
            
            // Add values (ensure they're within reasonable limits)
            $fuelLevel = $snapshotLevelQuery[$groupName] ?? 0;
            $fuelFill = min($group->total_fuel_filled ?? 0, 99999);
            $fuelTheft = min($group->total_fuel_theft ?? 0, 99999);
            $fuelConsum = min($group->total_fuel_consumption ?? 0, 99999);
            
            $groupSums[$groupName] += $fuelLevel;
            $fuelFillSums[$groupName] += $fuelFill;
            $fuelTheftSums[$groupName] += $fuelTheft;
            $fuelConsumption[$groupName] += $fuelConsum;
            $groupCounts[$groupName] += $group->device_count;
            
            $totalFuelLevel += $fuelLevel;
            $totalFuelFill += $fuelFill;
            $totalFuelTheft += $fuelTheft;
            $totalFuelConsumption += $fuelConsum;
        }
        
        // Create results array for chart - each group becomes a series
        $results = [];
        
        // Create series for each group
        $count = 0;
        foreach ($groupSums as $groupName => $fuelLevel) {
            $results[$groupName] = [
                [0, $fuelLevel],           // Fuel Level
                [1, $fuelFillSums[$groupName]],    // Fuel Refill
                [2, $fuelTheftSums[$groupName]],   // Fuel Theft
                [3, $fuelConsumption[$groupName]]  // Fuel Consumption
            ];
            $count++;
        }
        
        // Create keys with fuel metrics and their totals
        $keys = [
            'Fuel Level (' . round($totalFuelLevel, 1) . 'L)',
            'Fuel Refill (' . round($totalFuelFill, 1) . 'L)', 
            'Fuel Theft (' . round($totalFuelTheft, 1) . 'L)',
            'Fuel Consumption (' . round($totalFuelConsumption, 1) . 'L)'
        ];

        
         return [
            'results' => $results,
            'keys' => $keys
         ];
    }

}
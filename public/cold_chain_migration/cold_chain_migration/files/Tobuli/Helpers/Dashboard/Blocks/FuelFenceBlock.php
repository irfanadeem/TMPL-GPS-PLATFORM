<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\CronJobCalculation;

class FuelFenceBlock extends Block
{
    protected function getName()
    {
        return 'fuel_fence';
    }

    protected function getContent()
    {
        $option = $this->getConfig("options");
        $fromDate = $option['from_date'] ?? \Carbon\Carbon::today()->format('Y-m-d');
        $toDate = $option['to_date'] ?? \Carbon\Carbon::today()->format('Y-m-d');
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
                'status' => true,
                'fuel_theft_rows' => collect(),
                'zone_events' => collect()
            ];
        }
        
        // Get fuel theft data from CronJobCalculation
        $fuelTheftData = $this->getFuelTheftData($fromDate, $toDate, $deviceIds, $department);
        
        // Get zone events data
        $zoneEventsData = $this->getZoneEventsData($fromDate, $toDate, $deviceIds, $department);
        
        return [
            'status' => true,
            'fuel_theft_rows' => $fuelTheftData,
            'zone_events' => $zoneEventsData
        ];
    }
    
    /**
     * Get fuel theft data from CronJobCalculation (optimized)
     */
    private function getFuelTheftData($fromDate, $toDate, $deviceIds, $department = null)
    {
        // Build optimized query with proper indexing
        $query = CronJobCalculation::query()
            ->select([
                DB::raw('DATE(job_time_from) as date'),
                'devices.name as device_name',
                DB::raw('COALESCE(device_groups.title, \'Unassigned\') as group_name'),
                DB::raw('SUM(total_fuel_theft) as total_fuel_theft')
            ])
            ->join('devices', 'devices.id', '=', 'cron_job_calculations.device_id')
            ->leftJoin('user_device_pivot', function($join) {
                $join->on('user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
                     ->where('user_device_pivot.user_id', '=', $this->user->id);
            })
            ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('cron_job_calculations.device_id', $deviceIds)
            ->where('total_fuel_theft', '>', 0); // Move condition to WHERE for better performance
            
        // Apply department filter if specified
        if ($department) {
            $query->where('device_groups.id', $department);
        }
              
        $query->groupBy([
                DB::raw('DATE(job_time_from)'),
                'cron_job_calculations.device_id',
                'devices.name',
                'device_groups.title'
            ])
            ->orderBy(DB::raw('DATE(job_time_from)'), 'DESC')
            ->limit(20);
              
        $data = $query->get();
        
        
        return $data;
    }
    
    /**
     * Get zone events data (optimized)
     */
    private function getZoneEventsData($fromDate, $toDate, $deviceIds, $department)
    {
        // Build optimized query with proper indexing and filtering
        $query = $this->user->events()
            ->select([
                DB::raw('DATE(events.created_at) as date'),
                'events.device_id',
                DB::raw('COALESCE(device_groups.title, \'Unassigned\') as group_name'),
                'devices.name as device_name',
                DB::raw('SUM(CASE WHEN events.type LIKE "%zone_out%" THEN 1 ELSE 0 END) AS zone_out_count'),
                DB::raw('SUM(CASE WHEN events.type LIKE "%zone_in%" THEN 1 ELSE 0 END) AS zone_in_count')
            ])
            ->join('devices', 'events.device_id', '=', 'devices.id')
            ->leftJoin('user_device_pivot', function($join) {
                $join->on('user_device_pivot.device_id', '=', 'events.device_id')
                     ->where('user_device_pivot.user_id', '=', $this->user->id);
            })
            ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
            ->whereBetween(DB::raw('DATE(events.created_at)'), [$fromDate, $toDate])
            ->whereIn('events.device_id', $deviceIds)
            ->where(function($q) {
                $q->where('events.type', 'LIKE', '%zone_out%')
                  ->orWhere('events.type', 'LIKE', '%zone_in%');
            }); // Pre-filter zone events for better performance
            
        if ($department) {
            $query->where('device_groups.id', $department);
        }
        
        $query->groupBy([
                DB::raw('DATE(events.created_at)'),
                'events.device_id',
                'device_groups.title'
            ])
            ->havingRaw('zone_out_count > 0 OR zone_in_count > 0')
            ->orderBy(DB::raw('DATE(events.created_at)'), 'DESC')
            ->limit(20);
              
        $data = $query->get();
        
        
        return $data;
    }
}
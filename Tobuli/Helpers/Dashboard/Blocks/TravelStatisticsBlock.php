<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\CronJobCalculation;

class TravelStatisticsBlock extends Block
{
    protected function getName()
    {
        return 'travel_statistics';
    }

    protected function getContent()
    {
        $option = $this->getConfig("options");
        $fromDate = $option['from_date'] ?? '2025-09-22';
        $toDate = $option['to_date'] ?? '2025-09-22';
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
                'travel_data' => collect(),
                'summary' => (object)[],
            ];
        }
        
        // Enhanced travel statistics using ReportDashboardCommand data
        $query = CronJobCalculation::query();
        $query->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('cron_job_calculations.device_id', $deviceIds);
        
        $query->selectRaw("
             devices.name as device_name, 
             ROUND(SUM(DISTINCT(distance))) as total_distance,
             device_groups.title as department,
             fuel_average as avg_fuel_efficiency,
             fuel_consumption as total_fuel_consumption,
             SEC_TO_TIME(TIME_TO_SEC(total_moving_time)) as total_moving_time,
             SEC_TO_TIME(TIME_TO_SEC(total_idle_time)) as total_idle_time,
             SEC_TO_TIME(TIME_TO_SEC(total_stop_time)) as total_stop_time
        ");
        
        $query->join('devices', 'devices.id', '=', 'cron_job_calculations.device_id');
        $query->Join('user_device_pivot', 'user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
              ->Join('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id');
              
        $query->groupByRaw('cron_job_calculations.id, devices.name');
        $query->orderByRaw('total_distance DESC');
        $query->limit(10);
        
        $data = $query->get();
        
        
        // Get summary statistics
        $summaryQuery = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('cron_job_calculations.device_id', $deviceIds)
            ->selectRaw("
                SUM(distance) as total_distance,
                SUM(fuel_consumption) as total_fuel_consumption,
                AVG(fuel_average) as avg_fuel_efficiency,
                COUNT(DISTINCT cron_job_calculations.device_id) as active_devices,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as total_moving_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as total_idle_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as total_stop_time
            ")
            ->first();
            
        
        return [
            'status' => true,
            'travel_data' => $data,
            'summary' => $summaryQuery,
        ];
    }
}
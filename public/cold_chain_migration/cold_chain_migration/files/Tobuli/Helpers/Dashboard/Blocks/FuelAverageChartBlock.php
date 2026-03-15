<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\CronJobCalculation;

class FuelAverageChartBlock extends Block
{
    protected function getName()
    {
        return 'fuel_average_chart';
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
                'fuel_average' => collect(),
                'daily_trends' => collect(),
            ];
        }
        
        // Get fuel average data by device (optimized)
        $fuelAverageData = $this->getFuelAverageByDevice($fromDate, $toDate, $deviceIds, $department);
        
        // Get daily fuel average trends (optimized)
        $dailyTrendsData = $this->getDailyFuelTrends($fromDate, $toDate, $deviceIds, $department);

        return [
            'status' => true,
            'fuel_average' => $fuelAverageData,
            'daily_trends' => $dailyTrendsData,
        ];
    }
    
    /**
     * Get fuel average data by device (optimized)
     */
    private function getFuelAverageByDevice($fromDate, $toDate, $deviceIds, $department = null)
    {
        $query = CronJobCalculation::query()
            ->select([
                'cron_job_calculations.device_id',
                'devices.name as device_name',
                'device_groups.title as group_name',
                DB::raw('AVG(fuel_average) as per_fuel_average'),
                DB::raw('SUM(fuel_consumption) as total_fuel_consumption'),
                DB::raw('SUM(distance) as total_distance'),
                DB::raw('AVG(min_fuel_level) as avg_min_fuel_level'),
                DB::raw('AVG(max_fuel_level) as avg_max_fuel_level'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as total_moving_time'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as total_idle_time'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as total_stop_time')
            ])
            ->join('devices', 'devices.id', '=', 'cron_job_calculations.device_id')
            ->leftJoin('user_device_pivot', function($join) {
                $join->on('user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
                     ->where('user_device_pivot.user_id', '=', $this->user->id);
            })
            ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('cron_job_calculations.device_id', $deviceIds)
            ->where('fuel_average', '>', 0); // Only include devices with valid fuel average
            
        // Apply department filter if specified
        if ($department) {
            $query->where('device_groups.id', $department);
        }
            
        $query->groupBy([
                'cron_job_calculations.device_id',
                'devices.name',
                'device_groups.title'
            ])
            ->orderBy('per_fuel_average', 'desc')
            ->limit(10);
            
        $data = $query->get();
        
        
        return $data;
    }
    
    /**
     * Get daily fuel average trends (optimized)
     */
    private function getDailyFuelTrends($fromDate, $toDate, $deviceIds, $department = null)
    {
        $query = CronJobCalculation::query()
            ->select([
                DB::raw('DATE(job_time_from) as date'),
                DB::raw('AVG(fuel_average) as daily_avg_fuel_efficiency'),
                DB::raw('SUM(fuel_consumption) as daily_fuel_consumption'),
                DB::raw('SUM(distance) as daily_distance'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as daily_moving_time'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as daily_idle_time'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as daily_stop_time')
            ])
            ->join('devices', 'devices.id', '=', 'cron_job_calculations.device_id')
            ->leftJoin('user_device_pivot', function($join) {
                $join->on('user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
                     ->where('user_device_pivot.user_id', '=', $this->user->id);
            })
            ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('cron_job_calculations.device_id', $deviceIds)
            ->where('fuel_average', '>', 0); // Only include days with valid fuel average
            
        // Apply department filter if specified
        if ($department) {
            $query->where('device_groups.id', $department);
        }
            
        $query->groupBy(DB::raw('DATE(job_time_from)'))
            ->orderBy('date', 'desc')
            ->limit(15);
            
        $data = $query->get();
        
        
        return $data;
    }
}
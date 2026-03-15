<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\CronJobCalculation;
use Tobuli\History\DeviceHistory;
use Tobuli\History\Actions\DriveStop;
use Tobuli\History\Actions\Duration;
use Tobuli\History\Actions\Distance;
use Tobuli\History\Actions\FuelReport;
use Tobuli\History\Actions\GroupDrive;
use Tobuli\History\Actions\EngineHours;

class FuelStatisticsBlock extends Block
{
    protected function getName()
    {
        return 'fuel_statistics';
    }

    protected function getContent()
    {
        $option = $this->getConfig("options");
        $fromDate = $option['from_date'] ?? Carbon::today()->format('Y-m-d');
        $toDate = $option['to_date'] ?? Carbon::today()->format('Y-m-d');
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
                'rows' => collect(),
                'daily_data' => collect(),
                'summary' => (object)[],
            ];
        }
        
        // Get data from cron_job_calculations table (our enhanced data)
        $dailyData = $this->getDailyFuelData($fromDate, $toDate, $deviceIds);
        $summaryData = $this->getSummaryFuelData($fromDate, $toDate, $deviceIds);
        
        return [
            'status' => true,
            'rows' => $dailyData, // Keep backward compatibility with existing view
            'daily_data' => $dailyData,
            'summary' => $summaryData,
        ];
    }
    
    /**
     * Get daily fuel data using our enhanced calculations
     */
    private function getDailyFuelData($fromDate, $toDate, $deviceIds)
    {
        $query = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('device_id', $deviceIds)
            ->selectRaw("
                DATE(job_time_from) as date,
                SUM(fuel_consumption) as total_fuel_consumption,
                SUM(total_fuel_theft) as total_fuel_theft,
                SUM(total_fuel_filled) as total_fuel_filled,
                AVG(min_fuel_level) as avg_min_fuel_level,
                AVG(max_fuel_level) as avg_max_fuel_level,
                AVG(fuel_average) as avg_fuel_efficiency,
                SUM(distance) as total_distance,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as total_moving_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as total_idle_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as total_stop_time
            ")
            ->groupBy(DB::raw('DATE(job_time_from)'))
            ->orderBy('date', 'desc')
            ->limit(15);
            
        $data = $query->get();
        
        
        // Add fuel level calculation for each day (optimized)
        foreach ($data as $item) {
            $item->max_fuel_level = $this->calculateFuelLevelForDate($item->date, $deviceIds);
        }
        
        
        return $data;
    }
    
    /**
     * Get summary fuel data
     */
    private function getSummaryFuelData($fromDate, $toDate, $deviceIds)
    {
        $summary = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$fromDate, $toDate])
            ->whereIn('device_id', $deviceIds)
            ->selectRaw("
                SUM(fuel_consumption) as total_fuel_consumption,
                SUM(total_fuel_theft) as total_fuel_theft,
                SUM(total_fuel_filled) as total_fuel_filled,
                AVG(fuel_average) as avg_fuel_efficiency,
                SUM(distance) as total_distance,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as total_moving_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as total_idle_time,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as total_stop_time,
                COUNT(DISTINCT device_id) as active_devices
            ")
            ->first();
            
        
        return $summary;
    }
    
    /**
     * Calculate fuel level for a specific date
     */
    private function calculateFuelLevelForDate($date, $deviceIds)
    {
        $subquery = DB::table('cron_job_calculations as cjc_inner')
            ->select('cjc_inner.max_fuel_level')
            ->whereRaw('DATE(cjc_inner.job_time_from) = DATE(?)', [$date])
            ->whereIn('cjc_inner.device_id', $deviceIds)
            ->whereBetween('cjc_inner.max_fuel_level', [1, 2499]) // Equivalent to > 0 AND < 2500
            ->groupBy('cjc_inner.device_id')
            ->orderByDesc('id');
        
        $fuelLevelQuery = DB::table(DB::raw("({$subquery->toSql()}) as total"))
            ->mergeBindings($subquery)
            ->selectRaw('SUM(max_fuel_level) as total_fuel_level')
            ->first();
            
        return $fuelLevelQuery->total_fuel_level ?? 0;
    }
}
<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\CronJobCalculation;

class WcBoardFormBlock extends Block
{
    protected function getName()
    {
        return 'wc_board_form';
    }

    protected function getContent()
    {
        
        $option = $this->getConfig("options");
        
        // Normalize dates to use today if not set
        $option = $this->normalizeDateOptions($option);
        
        $group = $this->user->deviceGroups()->get()->toArray();
        $devices = $this->getUserDevicesFromCache($option['from_date'], $option['to_date'],$option['department']);
        $ids = $devices ? deviseIds($devices) : [];
        
        
        
        // Enhanced query using ReportDashboardCommand data
        $query = CronJobCalculation::query();
        $query->whereBetween(DB::raw('DATE(job_time_from)'), [$option['from_date'], $option['to_date']]);
        $query->whereIn('device_id', $ids);
        
        // Get time statistics with enhanced data
        $timeStats = $query->selectRaw("
            SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as total_moving_time,
            SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as total_idle_time,
            SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as total_stop_time,
            SUM(fuel_consumption) as total_fuel_consumption,
            SUM(distance) as total_distance,
            AVG(fuel_average) as avg_fuel_efficiency
        ")->first();
        
        
        // Get daily breakdown for charts
        $dailyData = $query->selectRaw("
            DATE(job_time_from) as date,
            SEC_TO_TIME(SUM(TIME_TO_SEC(total_moving_time))) as daily_moving_time,
            SEC_TO_TIME(SUM(TIME_TO_SEC(total_idle_time))) as daily_idle_time,
            SEC_TO_TIME(SUM(TIME_TO_SEC(total_stop_time))) as daily_stop_time,
            SUM(fuel_consumption) as daily_fuel_consumption,
            SUM(distance) as daily_distance
        ")
        ->groupBy(DB::raw('DATE(job_time_from)'))
        ->orderBy('date', 'desc')
        ->limit(15)
        ->get();
        
        return [
            'status' => true,
            'rows' => [$timeStats], // Keep backward compatibility with existing view
            'time_stats' => $timeStats,
            'daily_data' => $dailyData,
            'block' => $this->getName(),
            'options' => $option,
            'groups' => $group
        ];
    }
    private function getUserDevicesFromCache($fromDate, $toDate,$department)
{
    try {
        $cacheKey = "user_devices_" . $fromDate . "_" . $toDate . "_" . ($department ?? 'all');
        $cacheTimestampKey = $cacheKey . "_last_updated";
        $query = $this->user->accessibleDevicesWithGroups();
        if($department){
            $query->where('user_device_pivot.group_id',$department);
        }
        $devices = $query->get()->toArray();
        
        // Ensure we always return an array
        if (!is_array($devices)) {
            $devices = [];
        }
        
        
        // Store data and update timestamp in cache
        Cache::put($cacheKey, $devices, 60 * 60); // Cache for 1 hour
        Cache::put($cacheTimestampKey, now(), 60 * 60); // Store last update timestamp

        return $devices;
    } catch (\Exception $e) {
        return [];
    }
}
}
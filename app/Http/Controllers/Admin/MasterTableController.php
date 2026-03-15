<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Tobuli\Entities\CronJobCalculation;
use Tobuli\Entities\Device;
use Tobuli\Entities\DeviceSensor;
use Tobuli\Entities\Event;

class MasterTableController extends Controller
{
    
    public function index(Request $request)
    { 
        $deviceGroup = auth()->user()->deviceGroups()->get()->toArray();
        $data = [
            'groups' => $deviceGroup,
        ];
        return View::make('admin::MasterTable.index')->with($data);
    
    }
    public function masterTableView(Request $request)
    {
        // Set memory and time limits
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes max
        
        $deviceGroup = auth()->user()->deviceGroups()->get()->toArray();
        
        // Get date range with limits
        $dateFrom = $request->input('dateFrom', Carbon::now()->format('Y-m-d'));
        $dateTo = $request->input('dateTo', Carbon::now()->format('Y-m-d'));
        $department = $request->input('department');
        
        // Use consistent device filtering method
        $devIDs = $this->getUserDevices($dateFrom, $dateTo, $department);
        
        
        // Limit number of devices to prevent timeout
        $maxDevices = 20; // Maximum 20 devices for view
        if (count($devIDs) > $maxDevices) {
            $devIDs = array_slice($devIDs, 0, $maxDevices);
        }
        
        // Limit date range to prevent excessive processing
        $maxDays = 14; // Maximum 14 days for view
        $requestedDays = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));
        if ($requestedDays > $maxDays) {
            $dateTo = Carbon::parse($dateFrom)->addDays($maxDays)->format('Y-m-d');
        }
        
        // Use optimized calculation method
        $result = $this->getOptimizedMasterTableData($devIDs, $dateFrom, $dateTo, $department, 50, 0);
        
        /////////Fetch total count//////
        $data = [
            'groups' => $deviceGroup,
            'request' => $request,
            'data' => $result['data'],
        ];
        return View::make('admin::MasterTable.table')->with($data);
    }

    /**
     * Build base query for CronJobCalculation with common joins and filters
     */
    private function buildBaseQuery($devIDs, $dateFrom, $dateTo)
    {
        $query = CronJobCalculation::query()
            ->whereIn('cron_job_calculations.device_id', $devIDs)
            ->whereBetween('cron_job_calculations.job_time_from', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ]);
        
        // Join with device groups
        $query->leftJoin('user_device_pivot', 'user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
              ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id');
        
        return $query;
    }
    
    /**
     * Get common select fields for CronJobCalculation queries
     */
    private function getCommonSelectFields()
    {
        return [
            'cron_job_calculations.device_id',
            'cron_job_calculations.device_name',
            'device_groups.title as department',
            'cron_job_calculations.job_time_from as job_time_from',
            'cron_job_calculations.distance as distance',
            'cron_job_calculations.max_fuel_level as max_fuel_level',
            'cron_job_calculations.min_fuel_level as min_fuel_level',
            'cron_job_calculations.total_fuel_theft as total_fuel_theft',
            'cron_job_calculations.total_fuel_filled as total_fuel_filled',
            'cron_job_calculations.fuel_consumption as fuel_consumption',
            'cron_job_calculations.fuel_average as fuel_average',
            DB::raw('SEC_TO_TIME(TIME_TO_SEC(cron_job_calculations.total_moving_time)) as total_moving_time'),
            DB::raw('SEC_TO_TIME(TIME_TO_SEC(cron_job_calculations.total_idle_time)) as total_idle_time'),
            DB::raw('SEC_TO_TIME(TIME_TO_SEC(cron_job_calculations.total_stop_time)) as total_stop_time')
        ];
    }
    
    /**
     * Get common groupBy fields for CronJobCalculation queries
     */
    private function getCommonGroupByFields()
    {
        return [
            'cron_job_calculations.device_id',
            'cron_job_calculations.id',
            'cron_job_calculations.job_time_from'
        ];
    }

    /**
     * Get master table data from CronJobCalculation table (replaces real-time calculation)
     */
    private function getRealTimeMasterTableData($devIDs, $dateFrom, $dateTo, $department = null)
    {
        // Build query using helper methods
        $query = $this->buildBaseQuery($devIDs, $dateFrom, $dateTo);
        $query->select($this->getCommonSelectFields());
        $query->groupBy($this->getCommonGroupByFields());
        
        // Order by device and date
        $query->orderBy('cron_job_calculations.device_id')
              ->orderBy('cron_job_calculations.job_time_from');
        
        $cronJobData = $query->get();
        
        $results = [];
        $resultsCounter = 0;
        
        foreach ($cronJobData as $record) {
            // Get event counts for the specific day
            $dayStart = $record->job_time_from . ' 00:00:00';
            $dayEnd = $record->job_time_from . ' 23:59:59';
            $eventCounts = $this->getEventCounts($record->device_id, $dayStart, $dayEnd);
            
            // Determine sensor type based on fuel level
            $sensorType = $record->max_fuel_level > 0 ? 'FS' : 'VTS';
            
            // Get temperature sensor data if fuel level is above 4500
            $temperatureData = $this->getTemperatureSensorData($record->device_id, $dayStart, $dayEnd);

            $resultsCounter++;
                    $results[] = [
                'device_name' => $record->device_name,
                'device_id' => $record->device_id,
                'department' => $record->department ?? 'Unknown',
                'created_at' => $record->job_time_from,
                'distance' => round($record->distance, 2),
                'max_fuel_level' => round($record->max_fuel_level, 2),
                'min_fuel_level' => round($record->min_fuel_level ?? 0, 2),
                'total_fuel_theft' => round($record->total_fuel_theft, 2),
                'total_fuel_filled' => round($record->total_fuel_filled, 2),
                'fuel_consumption' => round($record->fuel_consumption, 2),
                'fuel_average' => round($record->fuel_average, 2),
                'total_moving_time' => $record->total_moving_time,
                'total_idle_time' => $record->total_idle_time,
                'total_stop_time' => $record->total_stop_time,
                'zone_out_count' => $eventCounts['zone_out'],
                'power_cut_count' => $eventCounts['power_cut'],
                'fs_temper_count' => $eventCounts['fs_temper'],
                'sensor_type' => $sensorType,
                'temperature_max' => $temperatureData['max_temp'],
                'temperature_min' => $temperatureData['min_temp'],
                'temperature_avg' => $temperatureData['avg_temp'],
                'show_temperature' => ($record->max_fuel_level > 4500 || ($record->min_fuel_level ?? 0) > 4500)
            ];
        }

        return collect($results);
    }

    /**
     * Optimized method to get master table data using CronJobCalculation table
     */
    private function getOptimizedMasterTableData($devIDs, $dateFrom, $dateTo, $department = null, $limit = 10, $offset = 0)
    {
        // Create cache key for this request
        $cacheKey = 'master_table_data_' . md5(implode(',', $devIDs) . $dateFrom . $dateTo . $department . $limit . $offset);
        
        // Try to get from cache first (cache for 5 minutes)
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }
        
       
        
        
        // Build optimized query using helper methods
        $query = $this->buildBaseQuery($devIDs, $dateFrom, $dateTo);
        
        // Get total count for pagination (before applying select and groupBy)
        $countQuery = $this->buildBaseQuery($devIDs, $dateFrom, $dateTo)
            ->select([
                'cron_job_calculations.device_id',
                'cron_job_calculations.device_name',
                'device_groups.title',
                'cron_job_calculations.job_time_from as job_time_from'
            ])
            ->groupBy($this->getCommonGroupByFields());
        
        $totalRecords = DB::table(DB::raw("({$countQuery->toSql()}) as grouped_data"))
            ->mergeBindings($countQuery->getQuery())
            ->count();
        
        // Select required fields using helper method
        $query->select($this->getCommonSelectFields());
        
        // Group by device and date to avoid duplicates
        $query->groupBy($this->getCommonGroupByFields());
        
        // Order by device and date
        $query->orderBy('cron_job_calculations.device_id')
              ->orderBy('cron_job_calculations.job_time_from');
        
        // Apply pagination after grouping
        $query->offset($offset)->limit($limit);
        
        $cronJobData = $query->get();
        
        $results = [];
        
        foreach ($cronJobData as $record) {
            // Get event counts for the specific day
            $dayStart = $record->job_time_from . ' 00:00:00';
            $dayEnd = $record->job_time_from . ' 23:59:59';
            $eventCounts = $this->getEventCounts($record->device_id, $dayStart, $dayEnd);
            
            // Determine sensor type based on fuel level
            $sensorType = $record->max_fuel_level > 0 ? 'FS' : 'VTS';

                    $results[] = [
                'device_name' => $record->device_name,
                'device_id' => $record->device_id,
                'department' => $record->department ?? 'Unknown',
                'job_time_from' => $record->job_time_from,
                'distance' => round($record->distance, 2),
                'max_fuel_level' => round($record->max_fuel_level, 2),
                'total_fuel_theft' => round($record->total_fuel_theft, 2),
                'total_fuel_filled' => round($record->total_fuel_filled, 2),
                'fuel_consumption' => round($record->fuel_consumption, 2),
                'fuel_average' => round($record->fuel_average, 2),
                'total_moving_time' => $record->total_moving_time,
                'total_idle_time' => $record->total_idle_time,
                'total_stop_time' => $record->total_stop_time,
                        'zone_out_count' => $eventCounts['zone_out'],
                        'power_cut_count' => $eventCounts['power_cut'],
                        'fs_temper_count' => $eventCounts['fs_temper'],
                'sensor_type' => $sensorType
                    ];
                }
                

        $result = [
            'data' => collect($results),
            'total' => $totalRecords,
            'has_more' => ($offset + count($results)) < $totalRecords
        ];

        
        // Cache the results for 5 minutes
        Cache::put($cacheKey, $result, 300);
        
        return $result;
    }

    /**
     * Calculate total possible records from CronJobCalculation table
     */
    private function calculateTotalPossibleRecords($devIDs, $dateFrom, $dateTo, $department = null)
    {
        $cacheKey = 'total_records_' . md5(implode(',', $devIDs) . $dateFrom . $dateTo . $department);
        
        $cachedTotal = Cache::get($cacheKey);
        if ($cachedTotal !== null) {
            return $cachedTotal;
        }
        
        // Build query for CronJobCalculation data
        $query = CronJobCalculation::query()
            ->whereBetween(DB::raw('DATE(job_time_from)'), [$dateFrom, $dateTo])
            ->whereIn('cron_job_calculations.device_id', $devIDs);
        
        // Apply department filter if specified
        if ($department) {
            $query->join('user_device_pivot', 'user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
                  ->join('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
                  ->where('device_groups.id', $department);
        }
        
        $totalRecords = $query->count();
        
        // Cache for 10 minutes
        Cache::put($cacheKey, $totalRecords, 600);
        
        return $totalRecords;
    }

    /**
     * Get event counts for a device in a single query (optimized with caching)
     */
    private function getEventCounts($deviceId, $dayStart, $dayEnd)
    {
        // Use cache for event counts
        $cacheKey = "event_counts_{$deviceId}_{$dayStart}_{$dayEnd}";
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }

        $events = Event::where('device_id', $deviceId)
            ->whereBetween('time', [$dayStart, $dayEnd])
            ->selectRaw("
                SUM(CASE WHEN type LIKE '%zone_out%' THEN 1 ELSE 0 END) as zone_out,
                SUM(CASE WHEN message LIKE '%power cut%' THEN 1 ELSE 0 END) as power_cut,
                SUM(CASE WHEN message LIKE '%FS TEMPER%' THEN 1 ELSE 0 END) as fs_temper
            ")
            ->first();

        $result = [
            'zone_out' => $events->zone_out ?? 0,
            'power_cut' => $events->power_cut ?? 0,
            'fs_temper' => $events->fs_temper ?? 0
        ];

        // Cache for 10 minutes
        Cache::put($cacheKey, $result, 600);
        return $result;
    }

    /**
     * Get temperature sensor data for a device in a specific time range (optimized)
     */
    private function getTemperatureSensorData($deviceId, $dayStart, $dayEnd)
    {
        // Use cache for temperature sensor data
        $cacheKey = "temp_sensor_data_{$deviceId}_{$dayStart}_{$dayEnd}";
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }

        // Get temperature sensor for the device (cached)
        $temperatureSensor = Cache::remember("temp_sensor_{$deviceId}", 3600, function() use ($deviceId) {
            return DeviceSensor::where('device_id', $deviceId)
                ->where('type', 'temperature')
                ->first();
        });

        if (!$temperatureSensor) {
            $result = [
                'max_temp' => 0,
                'min_temp' => 0,
                'avg_temp' => 0
            ];
            Cache::put($cacheKey, $result, 300); // Cache for 5 minutes
            return $result;
        }

        // Get temperature data from positions table with optimized query
            $device = Device::find($deviceId);
        if (!$device) {
            $result = [
                'max_temp' => 0,
                'min_temp' => 0,
                'avg_temp' => 0
            ];
            Cache::put($cacheKey, $result, 300);
            return $result;
        }

        // Use raw query for better performance
        $temperatures = DB::table('positions')
            ->where('device_id', $deviceId)
            ->whereBetween('time', [$dayStart, $dayEnd])
            ->whereNotNull('sensors')
            ->pluck('sensors')
            ->map(function($sensors) use ($temperatureSensor) {
                $sensorData = json_decode($sensors, true);
                if (isset($sensorData[$temperatureSensor->tag_name])) {
                    $tempValue = (float)$sensorData[$temperatureSensor->tag_name];
                    return ($tempValue > -50 && $tempValue < 100) ? $tempValue : null;
                }
                return null;
            })
            ->filter()
            ->values()
            ->toArray();

        if (empty($temperatures)) {
            $result = [
                'max_temp' => 0,
                'min_temp' => 0,
                'avg_temp' => 0
            ];
        } else {
            $result = [
                'max_temp' => round(max($temperatures), 1),
                'min_temp' => round(min($temperatures), 1),
                'avg_temp' => round(array_sum($temperatures) / count($temperatures), 1)
            ];
        }

        // Cache the result for 5 minutes
        Cache::put($cacheKey, $result, 300);
        return $result;
    }

    /**
     * Get batch event counts for multiple devices (optimized)
     */
    private function getBatchEventCounts($deviceIds, $dateFrom, $dateTo)
    {
        $cacheKey = "batch_event_counts_" . md5(implode(',', $deviceIds) . $dateFrom . $dateTo);
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }

        $events = Event::whereIn('device_id', $deviceIds)
            ->whereBetween('time', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->selectRaw("
                device_id,
                DATE(time) as event_date,
                SUM(CASE WHEN type LIKE '%zone_out%' THEN 1 ELSE 0 END) as zone_out,
                SUM(CASE WHEN message LIKE '%power cut%' THEN 1 ELSE 0 END) as power_cut,
                SUM(CASE WHEN message LIKE '%FS TEMPER%' THEN 1 ELSE 0 END) as fs_temper
            ")
            ->groupBy('device_id', 'event_date')
            ->get()
            ->groupBy('device_id')
            ->map(function($deviceEvents) {
                return $deviceEvents->keyBy('event_date')->map(function($event) {
                    return [
                        'zone_out' => $event->zone_out ?? 0,
                        'power_cut' => $event->power_cut ?? 0,
                        'fs_temper' => $event->fs_temper ?? 0
                    ];
                });
            })
            ->toArray();

        Cache::put($cacheKey, $events, 600); // Cache for 10 minutes
        return $events;
    }

    /**
     * Get batch temperature data for multiple devices (optimized)
     */
    private function getBatchTemperatureData($deviceIds, $dateFrom, $dateTo)
    {
        $cacheKey = "batch_temp_data_" . md5(implode(',', $deviceIds) . $dateFrom . $dateTo);
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }

        // Get temperature sensors for all devices
        $temperatureSensors = DeviceSensor::whereIn('device_id', $deviceIds)
            ->where('type', 'temperature')
            ->get()
            ->keyBy('device_id');

        $result = [];
        foreach ($deviceIds as $deviceId) {
            $result[$deviceId] = [];
        }

        // Process each device's temperature data
        foreach ($temperatureSensors as $deviceId => $sensor) {
            $temperatures = DB::table('positions')
                ->where('device_id', $deviceId)
                ->whereBetween('time', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ])
                ->whereNotNull('sensors')
                ->selectRaw('DATE(time) as position_date, sensors')
                ->get()
                ->groupBy('position_date')
                ->map(function($dayPositions) use ($sensor) {
                    $dayTemps = $dayPositions->map(function($position) use ($sensor) {
                        $sensorData = json_decode($position->sensors, true);
                        if (isset($sensorData[$sensor->tag_name])) {
                            $tempValue = (float)$sensorData[$sensor->tag_name];
                            return ($tempValue > -50 && $tempValue < 100) ? $tempValue : null;
                        }
                        return null;
                    })->filter()->values()->toArray();

                    if (empty($dayTemps)) {
                        return [
                            'max_temp' => 0,
                            'min_temp' => 0,
                            'avg_temp' => 0
                        ];
                    }

                    return [
                        'max_temp' => round(max($dayTemps), 1),
                        'min_temp' => round(min($dayTemps), 1),
                        'avg_temp' => round(array_sum($dayTemps) / count($dayTemps), 1)
                    ];
                });

            $result[$deviceId] = $dayTemps->toArray();
        }

        Cache::put($cacheKey, $result, 300); // Cache for 5 minutes
        return $result;
    }

    /**
     * Fallback method using CronJobCalculation with limited data for timeout situations
     */
    private function getFallbackMasterTableData($devIDs, $dateFrom, $dateTo, $department = null, $limit = 10, $offset = 0)
    {
        // Process only first few devices to prevent timeout
        $limitedDevIDs = array_slice($devIDs, 0, min(5, count($devIDs)));
        
        // Build query using helper methods with limited devices
        $query = $this->buildBaseQuery($limitedDevIDs, $dateFrom, $dateTo);
        
        // Limit to first 7 days for fallback
        $query->whereRaw('DATE(job_time_from) <= DATE_ADD(?, INTERVAL 6 DAY)', [$dateFrom]);
        
        // Select required fields with aggregation to ensure one record per device per date
        $query->select($this->getCommonSelectFields());
        
        $query->groupBy($this->getCommonGroupByFields());
        
        // Order by device and date
        $query->orderBy('cron_job_calculations.device_id')
              ->orderBy('cron_job_calculations.job_time_from');
        
        $cronJobData = $query->get();
        
        $results = [];
        $resultsCounter = 0;
        
        foreach ($cronJobData as $record) {
            // Get event counts for the specific day
            $dayStart = $record->job_time_from . ' 00:00:00';
            $dayEnd = $record->job_time_from . ' 23:59:59';
            $eventCounts = $this->getEventCounts($record->device_id, $dayStart, $dayEnd);
            
            // Determine sensor type based on fuel level
            $sensorType = $record->max_fuel_level > 0 ? 'FS' : 'VTS';
            
            // Get temperature sensor data if fuel level is above 4500
            $temperatureData = $this->getTemperatureSensorData($record->device_id, $dayStart, $dayEnd);
            
            $resultsCounter++;
            $results[] = [
                'device_name' => $record->device_name,
                'device_id' => $record->device_id,
                'department' => $record->department ?? 'Unknown',
                'created_at' => $record->job_time_from,
                'distance' => round($record->distance, 2),
                'max_fuel_level' => round($record->max_fuel_level, 2),
                'min_fuel_level' => round($record->min_fuel_level ?? 0, 2),
                'total_fuel_theft' => round($record->total_fuel_theft, 2),
                'total_fuel_filled' => round($record->total_fuel_filled, 2),
                'fuel_consumption' => round($record->fuel_consumption, 2),
                'fuel_average' => round($record->fuel_average, 2),
                'total_moving_time' => $record->total_moving_time,
                'total_idle_time' => $record->total_idle_time,
                'total_stop_time' => $record->total_stop_time,
                'zone_out_count' => $eventCounts['zone_out'],
                'power_cut_count' => $eventCounts['power_cut'],
                'fs_temper_count' => $eventCounts['fs_temper'],
                'sensor_type' => $sensorType,
                'temperature_max' => $temperatureData['max_temp'],
                'temperature_min' => $temperatureData['min_temp'],
                'temperature_avg' => $temperatureData['avg_temp'],
                'show_temperature' => ($record->max_fuel_level > 4500 || ($record->min_fuel_level ?? 0) > 4500)
            ];
        }
        
        return collect($results);
    }

    /**
     * Download master table data as Excel/CSV
     */
    public function downloadData(Request $request)
    {
        // Set memory and time limits for download
        ini_set('memory_limit', '1024M');
        set_time_limit(600); // 10 minutes for download
        
        $dateFrom = $request->input('dataFrom', Carbon::now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('dataTo', Carbon::now()->format('Y-m-d'));
        $department = $request->input('department');
        $format = $request->input('format', 'excel'); // excel or csv
        
        $devIDs = $this->getUserDevices($dateFrom, $dateTo, $department);
        
        // For download, we need all data, so we'll process in batches
        $allData = $this->getAllDataForDownload($devIDs, $dateFrom, $dateTo, $department);
        
        if ($format === 'csv') {
            return $this->downloadCSV($allData);
        } else {
            return $this->downloadExcel($allData);
        }
    }

    /**
     * Get all data for download using CronJobCalculation table
     */
    private function getAllDataForDownload($devIDs, $dateFrom, $dateTo, $department = null)
    {
        // Build query using helper methods
        $query = $this->buildBaseQuery($devIDs, $dateFrom, $dateTo);
        $query->select($this->getCommonSelectFields());
        $query->groupBy($this->getCommonGroupByFields());
        
        // Order by device and date
        $query->orderBy('cron_job_calculations.device_id')
              ->orderBy('cron_job_calculations.job_time_from');
        
        
        $cronJobData = $query->get();
        
        
        
        $results = [];
        $resultsCounter = 0;
        
        // Batch process event counts and temperature data for better performance
        $deviceIds = $cronJobData->pluck('device_id')->unique()->toArray();
        $eventCountsBatch = $this->getBatchEventCounts($deviceIds, $dateFrom, $dateTo);
        $temperatureDataBatch = $this->getBatchTemperatureData($deviceIds, $dateFrom, $dateTo);


        foreach ($cronJobData as $index => $record) {
            
            
            // Get event counts from batch data
            $dayKey = $record->job_time_from;
            $eventCounts = $eventCountsBatch[$record->device_id][$dayKey] ?? [
                'zone_out' => 0,
                'power_cut' => 0,
                'fs_temper' => 0
            ];
            
            // Determine sensor type based on fuel level
            $sensorType = $record->max_fuel_level > 0 ? 'FS' : 'VTS';
            
            // Get temperature sensor data from batch data
            $temperatureData = $temperatureDataBatch[$record->device_id][$dayKey] ?? [
                'max_temp' => 0,
                'min_temp' => 0,
                'avg_temp' => 0
            ];
            
            
            $resultsCounter++;
            $results[] = [
                'device_name' => $record->device_name,
                'device_id' => $record->device_id,
                'department' => $record->department ?? 'Unknown',
                'created_at' => $record->job_time_from,
                'job_time_from' => $record->job_time_from,
                'distance' => round($record->distance, 2),
                'max_fuel_level' => round($record->max_fuel_level, 2),
                'min_fuel_level' => round($record->min_fuel_level ?? 0, 2),
                'total_fuel_theft' => round($record->total_fuel_theft, 2),
                'total_fuel_filled' => round($record->total_fuel_filled, 2),
                'fuel_consumption' => round($record->fuel_consumption, 2),
                'fuel_average' => round($record->fuel_average, 2),
                'total_moving_time' => $record->total_moving_time,
                'total_idle_time' => $record->total_idle_time,
                'total_stop_time' => $record->total_stop_time,
                'zone_out_count' => $eventCounts['zone_out'],
                'power_cut_count' => $eventCounts['power_cut'],
                'fs_temper_count' => $eventCounts['fs_temper'],
                'sensor_type' => $sensorType,
                'temperature_max' => $temperatureData['max_temp'],
                'temperature_min' => $temperatureData['min_temp'],
                'temperature_avg' => $temperatureData['avg_temp'],
                'show_temperature' => ($record->max_fuel_level > 4500 || ($record->min_fuel_level ?? 0) > 4500)
            ];
        }
        
        return collect($results);
    }

    /**
     * Download data as CSV
     */
    private function downloadCSV($data)
    {
        $filename = 'master_table_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Device Name', 'Device ID', 'Department', 'Date', 'Distance (km)', 
                'Max Fuel Level', 'Fuel Consumption', 'Fuel Average', 'Total Moving Time',
                'Total Idle Time', 'Total Stop Time', 'Zone Out Count', 'Power Cut Count',
                'FS Temper Count', 'Sensor Type'
            ]);
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['device_name'],
                    $row['device_id'],
                    $row['department'],
                    $row['created_at'],
                    $row['distance'],
                    $row['max_fuel_level'],
                    $row['fuel_consumption'],
                    $row['fuel_average'],
                    $row['total_moving_time'],
                    $row['total_idle_time'],
                    $row['total_stop_time'],
                    $row['zone_out_count'],
                    $row['power_cut_count'],
                    $row['fs_temper_count'],
                    $row['sensor_type']
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download data as Excel
     */
    private function downloadExcel($data)
    {
        $filename = 'master_table_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // For now, we'll use CSV format but with .xlsx extension
        // In a real implementation, you'd use a library like PhpSpreadsheet
        return $this->downloadCSV($data);
    }

    /**
     * Print master table data
     */
    public function printData(Request $request)
    {
        $dateFrom = $request->input('dataFrom', Carbon::now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('dataTo', Carbon::now()->format('Y-m-d'));
        $department = $request->input('department');
        
        $devIDs = $this->getUserDevices($dateFrom, $dateTo, $department);
        
        // For print, limit to reasonable amount of data
        $maxDevices = 20;
        if (count($devIDs) > $maxDevices) {
            $devIDs = array_slice($devIDs, 0, $maxDevices);
        }
        
        $result = $this->getOptimizedMasterTableData($devIDs, $dateFrom, $dateTo, $department, 1000, 0);
        $data = $result['data'];
        
        $deviceGroup = auth()->user()->deviceGroups()->get()->toArray();
        
        return View::make('admin::MasterTable.print', [
            'data' => $data,
            'groups' => $deviceGroup,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'department' => $department
        ]);
    }

    public function fetchData(Request $request)
    { 
        // Set memory and time limits
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes max
        
        $limit = min($request->input('limit', 10), 100); // Max 100 rows per page
        $offset = $request->input('offset', 0); // Starting row
        $search = $request->input('search', null); // Search term
        $sort = $request->input('sort', null); // Search term
        $order = $request->input('order', 'asc'); // Search term
        
        // Get date range with limits
        $dateFrom = $request->input('dataFrom', Carbon::now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('dataTo', Carbon::now()->format('Y-m-d'));
        
        // Limit date range to prevent excessive processing
        $maxDays = 30; // Maximum 30 days
        $requestedDays = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));
        if ($requestedDays > $maxDays) {
            return response()->json([
                'error' => "Date range too large. Maximum {$maxDays} days allowed.",
                'total' => 0,
                'rows' => []
            ], 400);
        }
        
        $department = $request->input('department');
        
        $devIDs = $this->getUserDevices($dateFrom, $dateTo, $department);
        
        // Limit number of devices to prevent timeout
        $maxDevices = 50; // Maximum 50 devices per request
        if (count($devIDs) > $maxDevices) {
            $devIDs = array_slice($devIDs, 0, $maxDevices);
        }
        
        // Use optimized calculation method with timeout protection
        try {
            $result = $this->getOptimizedMasterTableData($devIDs, $dateFrom, $dateTo, $department, $limit, $offset);
            $allData = $result['data'];
            $total = $result['total'];
        } catch (\Exception $e) {
            // Fallback to simpler method if optimized method fails
            Log::warning('Master table optimized method failed, using fallback: ' . $e->getMessage());
            $fallbackData = $this->getFallbackMasterTableData($devIDs, $dateFrom, $dateTo, $department, $limit, $offset);
            $allData = $fallbackData;
            $total = $fallbackData->count();
        }
        
        // Apply search filter
        if ($search) {
            $allData = $allData->filter(function($item) use ($search) {
                return stripos($item['device_name'], $search) !== false ||
                       stripos($item['department'], $search) !== false;
            });
        }
        
        // Apply sorting
        if ($sort) {
            $allData = $allData->sortBy($sort, SORT_REGULAR, $order === 'desc');
        }
        
        // Return data in JSON format
        return response()->json([
            'total' => $total,
            'rows' => $allData->values(),
        ]);
    }
    public function travelledView(Request $request)
    {   $validTypes = ['total_moving_time', 'total_idle_time', 'total_stop_time']; // Allowed columns
        $type = $request->input('type');
        
        if (!is_string($type) || !in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid type provided'], 400);
        }
  
         $dateFrom = $request->input('dateFrom', Carbon::now()->subDays(7)->format('Y-m-d'));
         $dataTo = $request->input('dataTo', Carbon::now()->format('Y-m-d'));
            $device = auth()->user()->accessibleDevicesWithGroups()->get()->toArray();
            $devIDs = array_column($device, 'id');
        
        $query = CronJobCalculation::query();
        $query->selectRaw("cron_job_calculations.device_name as vehicle,
            DATE(cron_job_calculations.created_at) as created_at,
            device_groups.title as department,
            SEC_TO_TIME(SUM(TIME_TO_SEC(cron_job_calculations.$type))) as engine_hours");
            if($request->has('department') && !empty($request->input('department'))){
             $query->Join('user_device_pivot', 'user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
                    ->Join('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id');
             $query->where('device_groups.id', '=', $request->input('department'));
            }else {
                $query->leftJoin('user_device_pivot', 'user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
                    ->join('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id');
            }
            $query->whereBetween(DB::raw('DATE(cron_job_calculations.created_at)'), [$dateFrom, $dataTo])
            ->whereIn('cron_job_calculations.device_id', $devIDs);
            
        $query->groupBy('cron_job_calculations.device_id');
        
        $countData = $query->get()->toArray();
         $data = [
                    'countData' => $countData,
                    'type' => $this->toTitleCase($type),
                ];

        return View::make('admin::TravelledTable.index')->with($data);
    
    }
    private function getUserDevices($fromDate, $toDate, $department = null)
    {
    
    $cacheKey = "user_devices_ids" . $fromDate . "_" . $toDate . "_" . ($department ?? 'all');
    $cacheTimestampKey = $cacheKey . "_last_updated";
    
    // Cache cleared for debugging - uncomment to clear cache
    // Cache::forget($cacheKey);
    // Cache::forget($cacheTimestampKey);
    
    // Retrieve cached data and its timestamp
    $cachedData = Cache::get($cacheKey);
    $cachedTimestamp = Cache::get($cacheTimestampKey);

    // Check if cached data exists and is valid
    if ($cachedData && $cachedTimestamp) {
        return $cachedData;
    }
    
    // Fetch data from the database based on department filter
    if ($department && !empty($department)) {
        // Get devices for specific department - use a more specific query
        $device = auth()->user()->accessibleDevicesWithGroups()
            ->where('user_device_pivot.group_id', $department)
            ->whereNotNull('user_device_pivot.group_id')
            ->get()
            ->toArray();
      
    } else {
        // Get all devices with departments (group_id not null)
        $device = auth()->user()->accessibleDevicesWithGroups()
            ->whereNotNull('user_device_pivot.group_id')
            ->where('user_device_pivot.group_id', '>', 0)
            ->get()
            ->toArray();
        
    }
    
    $devicesIDs = array_column($device, 'id');
    
    // Store data and update timestamp in cache
    Cache::put($cacheKey, $devicesIDs, 60 * 60 * 3); // Cache for 5 hour
    Cache::put($cacheTimestampKey, now(), 60 * 60 * 3); // Store last update timestamp

    return $devicesIDs;
}

    /**
     * Calculate fuel theft from events table for a specific date range
     * This matches the calculation method used in FuelTheftsReport
     * Note: This method is kept for event count calculations only
     */
    private function calculateFuelTheft($deviceId, $dayStart, $dayEnd)
    {
        $device = Device::find($deviceId);
        if (!$device) {
            return 0;
        }

        // Use the same method as in FuelTheftsReport and other fuel reports
        $fuelTheftEvents = Event::where('device_id', $deviceId)
            ->where('type', 'fuel_theft')
            ->whereBetween('time', [$dayStart, $dayEnd])
            ->where('deleted', 0)
            ->whereNotNull('additional')
            ->get();

        $totalFuelTheft = 0;
        foreach ($fuelTheftEvents as $event) {
            $additional = $event->additional ?? [];
            $difference = $additional['difference'] ?? 0;
            // Ensure we only count positive differences for fuel theft
            if ($difference > 0) {
                $totalFuelTheft += (float)$difference;
            }
        }

        return $totalFuelTheft;
    }

    /**
     * Calculate fuel filled from events table for a specific date range
     * This matches the calculation method used in FuelFillingsReport
     * Note: This method is kept for event count calculations only
     */
    private function calculateFuelFilled($deviceId, $dayStart, $dayEnd)
    {
        $device = Device::find($deviceId);
        if (!$device) {
            return 0;
        }

        // Use the same method as in FuelFillingsReport
        $fuelFillEvents = Event::where('device_id', $deviceId)
            ->where('type', 'fuel_fill')
            ->whereBetween('time', [$dayStart, $dayEnd])
            ->where('deleted', 0)
            ->whereNotNull('additional')
            ->get();

        $totalFuelFilled = 0;
        foreach ($fuelFillEvents as $event) {
            $additional = $event->additional ?? [];
            $difference = $additional['difference'] ?? 0;
            // Use absolute value for fuel filled (as it represents the amount filled)
            $totalFuelFilled += abs((float)$difference);
        }

        return $totalFuelFilled;
    }
function toTitleCase($string)
{
    $string = str_replace('_', ' ', $string); // Replace underscores with spaces
    $string = str_replace('time', 'hours', $string);
    // Convert to title case
    return ucwords($string);
}
}

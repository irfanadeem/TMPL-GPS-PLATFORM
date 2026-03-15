<?php

namespace App\Console\Commands;

use App\Jobs\AbstractConfirmFuelLevel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tobuli\Entities\CronJobCalculation;
use Tobuli\Entities\Device;
use Tobuli\Entities\Event;
use Tobuli\History\DeviceHistory;
use Tobuli\Entities\Geofence;
use Tobuli\History\Actions\AppendDiemRateGeofences;
use Tobuli\History\Group;
use Tobuli\History\Stats\StatSum;
use Tobuli\Services\DiemRateService;
use Tobuli\History\Actions\AppendMoveState;

class ReportDashboardHourlyCommand extends Command
{
    const SECONDS_GAP = 3600; // 1 hour in seconds
    private $idleDuration = 0;
    private $timeTo = 0;
    private $timeFrom = 0;
    protected $group;

    public function __construct() {
        parent::__construct();
    }

    static public function required()
    {
        return [
            AppendMoveState::class,
        ];
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ReportDashboardHourly 
                            {--start-time= : Starting time for calculation (Y-m-d H:i:s format)}
                            {--device= : Specific device ID to process (optional)}
                            {--force : Force recalculation even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate daily fuel data using trip-based approach (like summary reports) - processes ALL trips for the entire day and replaces previous values';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = $this->option('start-time');
        
        if (!$startTime) {
            $this->error('Please provide --start-time parameter (Y-m-d H:i:s format)');
            return 1;
        }

        try {
            $startTime = Carbon::parse($startTime);
        } catch (\Exception $e) {
            $this->error('Invalid start time format. Please use Y-m-d H:i:s format');
            return 1;
        }

        // Calculate end time (end of the same day)
        $endTime = $startTime->copy()->endOfDay();
        
        $this->info("Starting daily fuel calculation from: {$startTime} to: {$endTime}");
        $this->info("Time zone: " . $startTime->timezone->getName());
        
        // Get devices to process
        $deviceQuery = Device::where('active', 1);
        
        // Filter by specific device if provided
        if ($this->option('device')) {
            $deviceQuery->where('id', $this->option('device'));
            $this->info("Processing specific device ID: " . $this->option('device'));
        }
        
        $devices = $deviceQuery->get();
        
        $totalProcessedCount = 0;
        $totalErrorCount = 0;
        $totalSkippedCount = 0;
        $totalDevicesWithFuelSensor = 0;
        $totalDevicesWithoutFuelSensor = 0;
        
        // Process each device for the entire day using trip-based approach (like summary reports)
        $this->info("Processing trip-based daily data for all devices - calculating ALL trips for the day");
        
        $dailyStats = $this->processDailyData($devices, $startTime, $endTime);
        
        $totalProcessedCount += $dailyStats['processed'];
        $totalErrorCount += $dailyStats['errors'];
        $totalSkippedCount += $dailyStats['skipped'];
        $totalDevicesWithFuelSensor += $dailyStats['withFuelSensor'];
        $totalDevicesWithoutFuelSensor += $dailyStats['withoutFuelSensor'];
        
        // Display final summary
        $this->info("=== FINAL SUMMARY ===");
        $this->info("Total processed: {$totalProcessedCount}");
        $this->info("Total errors: {$totalErrorCount}");
        $this->info("Total skipped: {$totalSkippedCount}");
        $this->info("Devices with fuel sensor: {$totalDevicesWithFuelSensor}");
        $this->info("Devices without fuel sensor: {$totalDevicesWithoutFuelSensor}");
        
        return 0;
    }

    /**
     * Process data for the entire day (one record per device per date)
     * Uses trip-based calculation like summary reports - calculates ALL trips for the day
     */
    private function processDailyData($devices, $timeFrom, $timeTo)
    {
        $processedCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $devicesWithFuelSensor = 0;
        $devicesWithoutFuelSensor = 0;
        
        foreach ($devices as $device) {
            try {
                // Check if device has fuel sensors
                $hasFuelSensor = $this->deviceHasFuelSensor($device);
                
                if ($hasFuelSensor) {
                    $devicesWithFuelSensor++;
                    $this->line("Processing device with fuel sensor: {$device->name}");
                } else {
                    $devicesWithoutFuelSensor++;
                    $this->line("Device without fuel sensor: {$device->name}");
                }

                // Calculate fuel data for the entire day using trip-based approach
                $fuelData = $this->calculateFuelDataForDeviceTripBased($device, $timeFrom, $timeTo);
                
                if ($fuelData) {
                    // Check if we should skip or update existing record
                    $shouldSkip = $this->shouldSkipUpdate($device, $timeFrom, $fuelData);
                    
                    if ($shouldSkip) {
                        $skippedCount++;
                        $this->line("Skipping device {$device->name} - no changes detected");
                        continue;
                    }
                    
                    // Save or update the calculation result
                    $this->saveOrUpdateCalculationResult($device, $timeFrom, $timeTo, $fuelData);
                    $processedCount++;
                } else {
                    $this->line("No fuel data calculated for device: {$device->name}");
                }

            } catch (\Exception $e) {
                $errorCount++;
                $this->error("Error processing device {$device->name}: " . $e->getMessage());
                Log::error("Error in ReportDashboardHourlyCommand for device {$device->id}: " . $e->getMessage());
            }
        }
        
        return [
            'processed' => $processedCount,
            'errors' => $errorCount,
            'skipped' => $skippedCount,
            'withFuelSensor' => $devicesWithFuelSensor,
            'withoutFuelSensor' => $devicesWithoutFuelSensor
        ];
    }

    /**
     * Check if device has fuel sensors
     */
    private function deviceHasFuelSensor($device)
    {
        return $device->sensors && $device->sensors->whereIn('type', ['fuel_tank', 'fuel_consumption'])->count() > 0;
    }

    /**
     * Calculate fuel data for a specific device using trip-based approach (like summary reports)
     * This calculates ALL trips for the entire day, not just a specific time period
     */
    private function calculateFuelDataForDeviceTripBased($device, $timeFrom, $timeTo)
    {
        try {
            // Use the existing history system to get trip-based data for the entire day
            $history = new DeviceHistory($device);
            
            // Set the same configuration as DeviceHistoryReport
            $history->setConfig([
                'stop_speed'        => $device->min_moving_speed,
                'min_fuel_fillings' => $device->min_fuel_fillings,
                'min_fuel_thefts'   => $device->min_fuel_thefts,
            ]);
            
            $history->setRange($timeFrom, $timeTo);
            $history->registerActions([
                \Tobuli\History\Actions\DriveStop::class,
                \Tobuli\History\Actions\Duration::class,
                \Tobuli\History\Actions\Distance::class,
                \Tobuli\History\Actions\FuelReport::class,
                \Tobuli\History\Actions\GroupDrive::class,
                \Tobuli\History\Actions\EngineHours::class,
            ]);
            
            $data = $history->get();
            
            if (empty($data) || empty($data['groups'])) {
                Log::info("No trip data found for device {$device->id}");
                return null;
            }
            
            // Calculate totals from ALL trips for the day (like FuelSummaryReport)
            $totalDistance = 0;
            $totalMovingTime = 0;
            $totalStopTime = 0;
            $totalIdleTime = 0;
            $totalFuelConsumption = 0;
            $tripCount = 0;
            
            Log::info("Processing " . count($data['groups']->all()) . " trips for device {$device->id}");
            
            foreach ($data['groups']->all() as $group) {
                $tripCount++;
                
                // Get distance from each trip using the same method as summary reports
                if ($group->stats()->has('distance')) {
                    $distanceFormatted = $group->stats()->get('distance')->human();
                    $distance = $this->parseDistance($distanceFormatted);
                    $totalDistance += $distance;
                    Log::info("Trip {$tripCount} distance: {$distance}km (formatted: {$distanceFormatted}, total: {$totalDistance}km)");
                }
                
                // Get duration data from each trip
                if ($group->stats()->has('drive_duration')) {
                    $totalMovingTime += $group->stats()->get('drive_duration')->value();
                }
                if ($group->stats()->has('stop_duration')) {
                    $totalStopTime += $group->stats()->get('stop_duration')->value();
                }
                if ($group->stats()->has('engine_idle')) {
                    $totalIdleTime += $group->stats()->get('engine_idle')->value();
                }
                
                // Get fuel consumption from each trip using the same method as summary reports
                $groupData = $this->getDataFromGroup($group, ['fuel_consumption_list']);
                if (!empty($groupData['fuel_consumption_list']) && 
                    is_array($groupData['fuel_consumption_list']) && 
                    isset($groupData['fuel_consumption_list'][1]) && 
                    isset($groupData['fuel_consumption_list'][1]['value'])) {
                    
                    $fuelValue = (float)str_replace(['L', 'l'], '', $groupData['fuel_consumption_list'][1]['value']);
                    if (is_numeric($fuelValue) && $fuelValue >= 0) {
                        $totalFuelConsumption += $fuelValue;
                        Log::info("Trip {$tripCount} fuel consumption: {$fuelValue}L (total: {$totalFuelConsumption}L)");
                    }
                }
            }
            
            // Calculate fuel theft and filled from events (same as before)
            $fuelTheft = $this->calculateFuelTheftFromEvents($device->id, $timeFrom, $timeTo);
            $fuelFilled = $this->calculateFuelFilledFromEvents($device->id, $timeFrom, $timeTo);
            
            // Calculate fuel levels from positions (same as before)
            $fuelLevels = $this->calculateFuelLevels($device, $timeFrom, $timeTo);
            
            // Calculate fuel average
            $fuelAverage = $this->calculateFuelAverage($totalFuelConsumption, $totalDistance);
            
            Log::info("Trip-based calculation for device {$device->id}: Distance={$totalDistance}km, Consumption={$totalFuelConsumption}L, Trips={$tripCount}");
            
            return [
                'fuel_theft' => $fuelTheft,
                'fuel_filled' => $fuelFilled,
                'fuel_consumption' => $totalFuelConsumption,
                'fuel_levels' => $fuelLevels,
                'movement_data' => [
                    'distance' => $totalDistance,
                    'moving_time' => $totalMovingTime,
                    'idle_time' => $totalIdleTime,
                    'stop_time' => $totalStopTime
                ],
                'fuel_average' => $fuelAverage,
                'calculated_at' => Carbon::now(),
                'time_from' => $timeFrom,
                'time_to' => $timeTo,
                'trip_count' => $tripCount
            ];
            
        } catch (\Exception $e) {
            Log::error("Error calculating trip-based fuel data for device {$device->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate fuel data for a specific device and time period (24-hour sum)
     * @deprecated Use calculateFuelDataForDeviceTripBased instead
     */
    private function calculateFuelDataForDevice($device, $timeFrom, $timeTo)
    {
        try {
            // Calculate fuel theft from events (sum over 24 hours)
            $fuelTheft = $this->calculateFuelTheftFromEvents($device->id, $timeFrom, $timeTo);
            
            // Calculate fuel filled from events (sum over 24 hours)
            $fuelFilled = $this->calculateFuelFilledFromEvents($device->id, $timeFrom, $timeTo);
            
            // Calculate fuel levels from positions (current level + 24-hour stats)
            $fuelLevels = $this->calculateFuelLevels($device, $timeFrom, $timeTo);
            
            // Calculate fuel consumption (sum over 24 hours)
            $fuelConsumption = $this->calculateFuelConsumption($device, $timeFrom, $timeTo);
            
            // Calculate distance, moving time, idle time, and stop time
            $movementData = $this->calculateMovementData($device, $timeFrom, $timeTo);
            
            // Calculate fuel average (fuel efficiency: Km/L)
            $fuelAverage = $this->calculateFuelAverage($fuelConsumption, $movementData['distance']);
            
            // Debug: Log fuel average calculation
            Log::info("Fuel average calculation for device {$device->id}: Distance={$movementData['distance']}km, Consumption={$fuelConsumption}L, Average={$fuelAverage}km/L");
            
            return [
                'fuel_theft' => $fuelTheft,
                'fuel_filled' => $fuelFilled,
                'fuel_consumption' => $fuelConsumption,
                'fuel_levels' => $fuelLevels,
                'movement_data' => $movementData,
                'fuel_average' => $fuelAverage,
                'calculated_at' => Carbon::now(),
                'time_from' => $timeFrom,
                'time_to' => $timeTo
            ];
            
        } catch (\Exception $e) {
            Log::error("Error calculating fuel data for device {$device->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate fuel theft from events table (same as reports)
     * Handles duplicate events for multiple users by grouping unique events
     */
    private function calculateFuelTheftFromEvents($deviceId, $timeFrom, $timeTo)
    {
        $fuelTheftEvents = Event::where('device_id', $deviceId)
            ->where('type', 'fuel_theft')
            ->whereBetween('time', [$timeFrom, $timeTo])
            ->where('deleted', 0)
            ->whereNotNull('additional')
            ->get();

        $totalFuelTheft = 0;
        $processedEvents = []; // Track processed events to avoid duplicates

        foreach ($fuelTheftEvents as $event) {
            $additional = $event->additional ?? [];
            $difference = $additional['difference'] ?? 0;
            
            if ($difference > 0) {
                // Create a unique key for this event based on device, time, and additional data
                // This prevents counting the same event multiple times for different users
                $eventKey = $event->device_id . '_' . $event->time . '_' . $event->type . '_' . 
                           md5(json_encode($additional));
                
                if (!isset($processedEvents[$eventKey])) {
                    $processedEvents[$eventKey] = true;
                    $totalFuelTheft += (float)$difference;
                }
            }
        }

        return $totalFuelTheft;
    }

    /**
     * Calculate fuel filled from events table (same as reports)
     * Handles duplicate events for multiple users by grouping unique events
     */
    private function calculateFuelFilledFromEvents($deviceId, $timeFrom, $timeTo)
    {
        $fuelFillEvents = Event::where('device_id', $deviceId)
            ->where('type', 'fuel_fill')
            ->whereBetween('time', [$timeFrom, $timeTo])
            ->where('deleted', 0)
            ->whereNotNull('additional')
            ->get();

        $totalFuelFilled = 0;
        $processedEvents = []; // Track processed events to avoid duplicates

        foreach ($fuelFillEvents as $event) {
            $additional = $event->additional ?? [];
            $difference = $additional['difference'] ?? 0;
            
            if ($difference != 0) {
                // Create a unique key for this event based on device, time, and additional data
                // This prevents counting the same event multiple times for different users
                $eventKey = $event->device_id . '_' . $event->time . '_' . $event->type . '_' . 
                           md5(json_encode($additional));
                
                if (!isset($processedEvents[$eventKey])) {
                    $processedEvents[$eventKey] = true;
                    $totalFuelFilled += abs((float)$difference);
                }
            }
        }

        return $totalFuelFilled;
    }

    /**
     * Calculate fuel levels from positions using the same logic as FuelReport
     */
    private function calculateFuelLevels($device, $timeFrom, $timeTo)
    {
        try {
            // Get positions for the time period
            $positions = $device->positions()
                ->whereBetween('time', [$timeFrom, $timeTo])
                ->orderBy('time', 'asc')
                ->get();

            if ($positions->isEmpty()) {
                return [
                    'current_fuel_level' => 0,
                    'min_fuel_level' => 0,
                    'max_fuel_level' => 0,
                    'avg_fuel_level' => 0
                ];
            }

            // Get fuel tank sensors
            $fuelSensors = $device->sensors->filter(function($sensor) {
                return in_array($sensor->type, ['fuel_tank']);
            });

            if ($fuelSensors->isEmpty()) {
                return [
                    'current_fuel_level' => 0,
                    'min_fuel_level' => 0,
                    'max_fuel_level' => 0,
                    'avg_fuel_level' => 0
                ];
            }

            $fuelLevels = [];
            $firstFuelLevel = null;
            $lastFuelLevel = null;

            foreach ($positions as $position) {
                // Process fuel tanks data like AppendFuelTanks does
                $fuelTanks = [];
                foreach ($fuelSensors as $sensor) {
                    $value = $this->getSensorValue($sensor, $position);
                    if (!is_null($value)) {
                        $value = floatval($value);
                    }
                    $fuelTanks[$sensor->id] = $value;
                }

                // Process fuel levels
                foreach ($fuelTanks as $sensorId => $level) {
                    if (!is_null($level)) {
                            $fuelLevels[] = $level;
                            
                        // Set first fuel level (start of day)
                        if ($firstFuelLevel === null) {
                            $firstFuelLevel = $level;
                        }
                        
                        // Update last fuel level (end of day)
                        $lastFuelLevel = $level;
                    }
                }
            }

            // Debug logging
            if (empty($fuelLevels)) {
                Log::info("No fuel levels found for device {$device->id}. Sensors: " . $fuelSensors->count() . ", Positions: " . $positions->count());
                if ($positions->count() > 0) {
                    $firstPosition = $positions->first();
                    Log::info("First position sensors_values: " . json_encode($firstPosition->sensors_values));
                }
            }

            return [
                'current_fuel_level' => $lastFuelLevel ?? 0,
                'min_fuel_level' => $firstFuelLevel ?? 0,  // First fuel level (start of day)
                'max_fuel_level' => $lastFuelLevel ?? 0,   // Last fuel level (end of day)
                'avg_fuel_level' => !empty($fuelLevels) ? array_sum($fuelLevels) / count($fuelLevels) : 0
            ];

        } catch (\Exception $e) {
            Log::error("Error calculating fuel levels for device {$device->id}: " . $e->getMessage());
            return [
                'current_fuel_level' => 0,
                'min_fuel_level' => 0,
                'max_fuel_level' => 0,
                'avg_fuel_level' => 0
            ];
        }
    }


    /**
     * Calculate fuel consumption using the existing history system
     */
    private function calculateFuelConsumption($device, $timeFrom, $timeTo)
    {
        try {
            // Use the existing history system to get accurate data
            $history = new DeviceHistory($device);
            
            // Set the same configuration as DeviceHistoryReport
            $history->setConfig([
                'stop_speed'        => $device->min_moving_speed,
                'min_fuel_fillings' => $device->min_fuel_fillings,
                'min_fuel_thefts'   => $device->min_fuel_thefts,
            ]);
            
            $history->setRange($timeFrom, $timeTo);
            $history->registerActions([
                \Tobuli\History\Actions\DriveStop::class,
                \Tobuli\History\Actions\Duration::class,
                \Tobuli\History\Actions\Distance::class,
                \Tobuli\History\Actions\FuelReport::class,
                \Tobuli\History\Actions\GroupDrive::class,
            ]);
            
            $data = $history->get();
            
            // Get fuel consumption from groups (same as FuelSummaryReport)
            $totalConsumption = 0;
            
            if (isset($data['groups'])) {
                foreach ($data['groups']->all() as $group) {
                    $groupData = $this->getDataFromGroup($group, [
                        'fuel_consumption_list'
                    ]);
                    
                    // Debug: Log group data
                    Log::info("Group data for device {$device->id}: " . json_encode($groupData));
                    
                    // Debug: Log group stats
                    $groupStats = $group->stats()->all();
                    Log::info("Group stats for device {$device->id}: " . json_encode(array_keys($groupStats)));
                    
                    // Sum fuel consumption from group data (same as FuelSummaryReport)
                    if (!empty($groupData['fuel_consumption_list']) && 
                        is_array($groupData['fuel_consumption_list']) && 
                        isset($groupData['fuel_consumption_list'][1]) && 
                        isset($groupData['fuel_consumption_list'][1]['value'])) {
                        
                        $fuelValue = (float)str_replace(['L', 'l'], '', $groupData['fuel_consumption_list'][1]['value']);
                        if (is_numeric($fuelValue) && $fuelValue >= 0) {
                            $totalConsumption += $fuelValue;
                            Log::info("Added fuel consumption: {$fuelValue}, Total so far: {$totalConsumption}");
                        }
                    } else {
                        Log::info("No fuel consumption data found in group");
                    }
                }
            }

            // Debug: Log all available stats
            if (isset($data['root'])) {
                $stats = $data['root']->stats()->all();
                Log::info("Available root stats for device {$device->id}: " . json_encode(array_keys($stats)));
            }
            
            if (isset($data['groups'])) {
                $groupsArray = $data['groups']->all();
                Log::info("Number of groups for device {$device->id}: " . count($groupsArray));
            }

            Log::info("Fuel consumption from history system for device {$device->id}: Total={$totalConsumption}");

            return $totalConsumption;

        } catch (\Exception $e) {
            Log::error("Error calculating fuel consumption using history system for device {$device->id}: " . $e->getMessage());
            
            // Fallback to manual calculation if history system fails
            return $this->calculateFuelConsumptionManual($device, $timeFrom, $timeTo);
        }
    }

    /**
     * Manual fuel consumption calculation (fallback)
     */
    private function calculateFuelConsumptionManual($device, $timeFrom, $timeTo)
    {
        try {
            // Get positions for the time period
            $positions = $device->positions()
                ->whereBetween('time', [$timeFrom, $timeTo])
                ->orderBy('time', 'asc')
                ->get();

            if ($positions->isEmpty()) {
                return 0;
            }

            // Get fuel tank sensors
            $fuelSensors = $device->sensors->filter(function($sensor) {
                return in_array($sensor->type, ['fuel_tank']);
            });

            if ($fuelSensors->isEmpty()) {
                return 0;
            }

            $totalConsumption = 0;
            $lastFuelLevel = [];
            $usedDiffs = [];

            foreach ($positions as $position) {
                // Process fuel tanks data like AppendFuelTanks does
                $fuelTanks = [];
                foreach ($fuelSensors as $sensor) {
                    $value = $this->getSensorValue($sensor, $position);
                    if (!is_null($value)) {
                        $value = floatval($value);
                    }
                    $fuelTanks[$sensor->id] = $value;
                }

                // Process fuel consumption like FuelReport does
                foreach ($fuelTanks as $sensorId => $level) {
                    if (!is_null($level)) {
                        if (isset($lastFuelLevel[$sensorId])) {
                            $prev = $lastFuelLevel[$sensorId];
                            $curr = $level;

                            // Detect refill (level increase > 5) - reset duplicate tracker
                            if (($curr - $prev) > 5) {
                                $usedDiffs[$sensorId] = [];
                            }

                            // Calculate consumption
                            $diff = $prev - $curr;

                            // Validate difference (same as FuelReport)
                            if ($diff < 0 || $diff > 1) {
                                $diff = 0;
                            }

                            // Prevent duplicate differences (per tank)
                            $hash = "{$prev}-{$curr}";
                            if (isset($usedDiffs[$sensorId][$hash])) {
                                $diff = 0;
                            }

                            // Save valid consumption
                            if ($diff >= 0) {
                                $usedDiffs[$sensorId][$hash] = true;
                                $totalConsumption += $diff;
                            }
                        }

                        // Save current value for next comparison
                        $lastFuelLevel[$sensorId] = $level;
                    }
                }
            }

            Log::info("Manual fuel consumption calculation for device {$device->id}: Total={$totalConsumption}");

            return $totalConsumption;

        } catch (\Exception $e) {
            Log::error("Error in manual fuel consumption calculation for device {$device->id}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate movement data using the existing history system
     */
    private function calculateMovementData($device, $timeFrom, $timeTo)
    {
        try {
            // Use the existing history system to get accurate data
            $history = new DeviceHistory($device);
            
            // Set the same configuration as DeviceHistoryReport
            $history->setConfig([
                'stop_speed'        => $device->min_moving_speed,
                'min_fuel_fillings' => $device->min_fuel_fillings,
                'min_fuel_thefts'   => $device->min_fuel_thefts,
            ]);
            
            $history->setRange($timeFrom, $timeTo);
            $history->registerActions([
                \Tobuli\History\Actions\DriveStop::class,
                \Tobuli\History\Actions\Duration::class,
                \Tobuli\History\Actions\Distance::class,
                \Tobuli\History\Actions\FuelReport::class,
                \Tobuli\History\Actions\GroupDrive::class,
                \Tobuli\History\Actions\EngineHours::class,
            ]);
            
            // Debug: Log the exact time range being used
            Log::info("Movement calculation time range for device {$device->id}: {$timeFrom} to {$timeTo}");
            
            $data = $history->get();
            
            // Debug: Log position count and actual time range
            if (isset($data['root'])) {
                $positionCount = $data['root']->stats()->has('position_count') ? $data['root']->stats()->get('position_count')->value() : 0;
                Log::info("Position count for device {$device->id}: {$positionCount}");
            }
            
            // Get distance from groups (same as FuelSummaryReport)
            $totalDistance = 0;
            if (isset($data['groups'])) {
                foreach ($data['groups']->all() as $group) {
                    // Get distance directly from group stats (same as summary reports)
                    if ($group->stats()->has('distance')) {
                        $distance = $group->stats()->get('distance')->value();
                        $totalDistance += $distance;
                        Log::info("Group distance for device {$device->id}: {$distance} (total: {$totalDistance})");
                    }
                }
                Log::info("Total distance from groups for device {$device->id}: {$totalDistance}");
            }
            
            // Debug: Also show root stats distance for comparison
            $rootDistance = 0;
            if (isset($data['root']) && $data['root']->stats()->has('distance')) {
                $rootDistance = $data['root']->stats()->get('distance')->value();
                Log::info("Distance from root stats for device {$device->id}: {$rootDistance}");
            }

            // Get duration data from root stats
            $totalMovingTime = 0;
            $totalStopTime = 0;
            $totalIdleTime = 0;
            
            if (isset($data['root'])) {
                if ($data['root']->stats()->has('drive_duration')) {
                    $totalMovingTime = $data['root']->stats()->get('drive_duration')->value();
                }
                if ($data['root']->stats()->has('stop_duration')) {
                    $totalStopTime = $data['root']->stats()->get('stop_duration')->value();
                }
                if ($data['root']->stats()->has('engine_idle')) {
                    $totalIdleTime = $data['root']->stats()->get('engine_idle')->value();
                }
            }
            
            // Debug: Log duration data
            Log::info("Duration data for device {$device->id}: Moving={$totalMovingTime}s, Stop={$totalStopTime}s, Idle={$totalIdleTime}s");

            // Debug: Log all available stats
            if (isset($data['root'])) {
                $stats = $data['root']->stats()->all();
                Log::info("Available stats for device {$device->id}: " . json_encode(array_keys($stats)));
            }

            Log::info("Movement data from history system for device {$device->id}: Distance={$totalDistance}, Moving={$totalMovingTime}s, Stop={$totalStopTime}s, Idle={$totalIdleTime}s");

            return [
                'distance' => $totalDistance,
                'moving_time' => $totalMovingTime,
                'idle_time' => $totalIdleTime,
                'stop_time' => $totalStopTime
            ];

        } catch (\Exception $e) {
            Log::error("Error calculating movement data using history system for device {$device->id}: " . $e->getMessage());
            
            // Fallback to manual calculation if history system fails
            return $this->calculateMovementDataManual($device, $timeFrom, $timeTo);
        }
    }

    /**
     * Manual movement data calculation (fallback)
     */
    private function calculateMovementDataManual($device, $timeFrom, $timeTo)
    {
        try {
            // Get positions for the time period
            $positions = $device->positions()
                ->whereBetween('time', [$timeFrom, $timeTo])
                ->orderBy('time', 'asc')
                ->get();

            if ($positions->isEmpty()) {
                return [
                    'distance' => 0,
                    'moving_time' => 0,
                    'idle_time' => 0,
                    'stop_time' => 0
                ];
            }

            $totalDistance = 0;
            $movingTime = 0;
            $idleTime = 0;
            $stopTime = 0;
            $previousPosition = null;
            $movingCount = 0;
            $idleCount = 0;
            $stopCount = 0;

            // Get device configuration
            $stopSpeed = $device->min_moving_speed ?? 1; // km/h
            $stopSeconds = 10; // seconds
            
            Log::info("Device {$device->id} configuration: min_moving_speed={$device->min_moving_speed}, stopSpeed={$stopSpeed}");

            foreach ($positions as $position) {
                // Calculate duration since last position
                $duration = 0;
                if ($previousPosition) {
                    $duration = strtotime($position->time) - strtotime($previousPosition->time);
                }

                // Calculate distance - only between consecutive valid positions (same as AppendDistanceGPS)
                if ($previousPosition && $position->valid && $previousPosition->valid) {
                    $distance = $this->calculateDistance(
                        $previousPosition->latitude,
                        $previousPosition->longitude,
                        $position->latitude,
                        $position->longitude
                    );
                    $totalDistance += $distance;
                }

                // Determine movement state
                $isMoving = $this->isMoving($position, $stopSpeed);
                $isEngineOn = $this->isEngineOn($position);

                // Debug logging for first few positions
                if ($positions->first() === $position) {
                    Log::info("Movement calculation for device {$device->id}: Speed={$position->speed}, Valid={$position->valid}, EngineOn={$isEngineOn}, Moving={$isMoving}, Duration={$duration}, StopSpeed={$stopSpeed}");
                }

                // Debug logging for all positions (limit to first 10)
                if ($positions->search($position) < 10) {
                    Log::info("Position {$positions->search($position)}: Time={$position->time}, Speed={$position->speed} (type: " . gettype($position->speed) . "), Valid={$position->valid}, EngineOn={$isEngineOn}, Moving={$isMoving}, Duration={$duration}");
                }

                // Calculate time based on movement state
                // Only add duration if it's reasonable (not too large gaps)
                if ($duration > 0 && $duration < 3600) { // Less than 1 hour gap
                    if ($isMoving) {
                        $movingTime += $duration;
                        $movingCount++;
                    } else if ($isEngineOn) {
                        $idleTime += $duration;
                        $idleCount++;
                    } else {
                        $stopTime += $duration;
                        $stopCount++;
                    }
                }

                $previousPosition = $position;
            }

            // Debug logging
            Log::info("Manual movement data for device {$device->id}: Distance={$totalDistance}, Moving={$movingTime} (count: {$movingCount}), Idle={$idleTime} (count: {$idleCount}), Stop={$stopTime} (count: {$stopCount}), TotalPositions={$positions->count()}");
            
            // Additional debug for distance calculation
            $validPositions = $positions->where('valid', true)->count();
            Log::info("Distance calculation details for device {$device->id}: ValidPositions={$validPositions}, TotalDistance={$totalDistance}");

            return [
                'distance' => $totalDistance,
                'moving_time' => $movingTime,
                'idle_time' => $idleTime,
                'stop_time' => $stopTime
            ];

        } catch (\Exception $e) {
            Log::error("Error in manual movement data calculation for device {$device->id}: " . $e->getMessage());
            return [
                'distance' => 0,
                'moving_time' => 0,
                'idle_time' => 0,
                'stop_time' => 0
            ];
        }
    }

    /**
     * Check if position is moving
     */
    private function isMoving($position, $stopSpeed)
    {
        // Must be valid position
        if (!$position->valid) {
            return false;
        }

        // Check if speed is valid and above threshold
        $speed = floatval($position->speed);
        if (is_null($position->speed) || $speed < $stopSpeed) {
            return false;
        }

        // If we have engine data, engine must be on
        if (isset($position->engine)) {
            return (bool)$position->engine;
        }

        // If no engine data, assume moving if speed is above threshold
        return true;
    }

    /**
     * Check if engine is on
     */
    private function isEngineOn($position)
    {
        // Check if position has engine data
        if (isset($position->engine)) {
            return (bool)$position->engine;
        }

        // Check sensors_values for engine data
        if (!empty($position->sensors_values)) {
            $values = $position->sensors_values;
            if (is_string($values)) {
                $values = json_decode($values, true);
            }
            if (is_array($values)) {
                foreach ($values as $value) {
                    if (isset($value['tag_name']) && $value['tag_name'] === 'engine') {
                        return $value['val'] === '1' || $value['val'] === 1;
                    }
                }
            }
        }

        // If no engine data, determine based on speed and movement
        $speed = floatval($position->speed);
        
        // If speed is 0 or null, assume engine is off
        if (is_null($position->speed) || $speed == 0) {
            return false;
        }

        // If there's movement (speed > 0), assume engine is on
        return $speed > 0;
    }

    /**
     * Calculate distance between two coordinates using the same formula as the existing system
     * This matches the getDistance() function used in AppendDistanceGPS
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (is_null($lat1) || is_null($lon1) || is_null($lat2) || is_null($lon2))
            return 0;

        $lat1 = (float)$lat1;
        $lon1 = (float)$lon1;
        $lat2 = (float)$lat2;
        $lon2 = (float)$lon2;

        if (($lat1 == 0 && $lon1 == 0) || ($lat2 == 0 && $lon2 == 0))
            return 0;

        if ($lat1 == $lat2 && $lon1 == $lon2)
            return 0;

        // Use the same formula as getDistance() in Helper.php (AppendDistanceGPS)
        $result = rad2deg((acos(cos(radians($lat2)) * cos(radians($lat1)) * cos(radians($lon2) - radians($lon1)) + sin(radians($lat2)) * sin(radians($lat1))))) * 111.045;
        if (is_nan($result))
            $result = 0;

        return $result;
    }

    /**
     * Get data from group (same as DeviceHistoryReport::getDataFromGroup)
     */
    private function getDataFromGroup($group, $keys)
    {
        if (is_string($keys))
            $keys = [$keys];

        $result = [];

        foreach ($keys as $key) {
            switch ($key) {
                case 'fuel_consumption_list':
                    $stats = $group->stats()->like('fuel_consumption_');

                    foreach ($stats as $stat) {
                        $result[$key][] = [
                            'title' => 'Fuel Consumption (' . $stat->getName() . ')',
                            'value' => $stat->human()
                        ];
                    }
                    break;
                case 'distance':
                    if ($group->stats()->has('distance')) {
                        $result[$key] = $group->stats()->get('distance')->human();
                    }
                    break;
                default:
                    // For other keys, we can add them as needed
                    break;
            }
        }

        return $result;
    }
    
    /**
     * Parse distance from formatted string (same as FuelSummaryReport)
     */
    private function parseDistance($distance)
    {
        $cleanedDistance = str_replace(['Km', 'L'], '', $distance);
        return (float)$cleanedDistance;
    }
    
    /**
     * Calculate fuel average (fuel efficiency: Km/L)
     * Same calculation as FuelSummaryReport
     */
    private function calculateFuelAverage($fuelConsumption, $distance)
    {
        if ($fuelConsumption > 0 && $distance > 0) {
            return round($distance / $fuelConsumption, 2);
        }
        
        return 0;
    }
    
    /**
     * Convert seconds to time format (HH:MM:SS)
     */
    private function secondsToTimeFormat($seconds)
    {
        if ($seconds <= 0) {
            return '00:00:00';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    /**
     * Get sensor value from position (same as Sensor::getPositionStoredValue)
     */
    private function getSensorValue($sensor, $position)
    {
        if (empty($position->sensors_values)) {
            return null;
        }

        $values = $position->sensors_values;
        
        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        if (!is_array($values)) {
            return null;
        }

        foreach ($values as $value) {
            if ($value['id'] != $sensor->id) {
                continue;
            }

            $saved = $value['val'] ?? null;
            if ($saved == '-') {
                $saved = null;
            }
            return $saved;
        }

        return null;
    }

    /**
     * Check if we should skip updating the record (all values are the same)
     */
    private function shouldSkipUpdate($device, $timeFrom, $fuelData)
    {
        try {
            // Find existing record for this device and date
            $existingRecord = CronJobCalculation::where('device_id', $device->id)
                ->whereDate('job_time_from', $timeFrom->toDateString())
                ->first();

            if (!$existingRecord) {
                return false; // No existing record, don't skip
            }

            // Compare values (with small tolerance for floating point differences)
            $tolerance = 0.001;
            
            $valuesMatch = 
                abs($existingRecord->total_fuel_theft - $fuelData['fuel_theft']) < $tolerance &&
                abs($existingRecord->total_fuel_filled - $fuelData['fuel_filled']) < $tolerance &&
                abs($existingRecord->fuel_consumption - $fuelData['fuel_consumption']) < $tolerance &&
                abs($existingRecord->fuel_average - $fuelData['fuel_average']) < $tolerance &&
                abs($existingRecord->min_fuel_level - $fuelData['fuel_levels']['min_fuel_level']) < $tolerance &&
                abs($existingRecord->max_fuel_level - $fuelData['fuel_levels']['max_fuel_level']) < $tolerance &&
                abs($existingRecord->avg_fuel_level - $fuelData['fuel_levels']['avg_fuel_level']) < $tolerance &&
                abs($existingRecord->distance - $fuelData['movement_data']['distance']) < $tolerance &&
                abs($existingRecord->total_moving_time - $fuelData['movement_data']['moving_time']) < $tolerance &&
                abs($existingRecord->total_idle_time - $fuelData['movement_data']['idle_time']) < $tolerance &&
                abs($existingRecord->total_stop_time - $fuelData['movement_data']['stop_time']) < $tolerance;

            return $valuesMatch;

        } catch (\Exception $e) {
            Log::error("Error checking skip condition for device {$device->id}: " . $e->getMessage());
            return false; // Don't skip on error
        }
    }

    /**
     * Save or update calculation result to database
     */
    private function saveOrUpdateCalculationResult($device, $timeFrom, $timeTo, $fuelData)
    {
        try {
            // Use updateOrCreate to handle both insert and update
            $record = CronJobCalculation::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'job_time_from' => $timeFrom->startOfDay(),
                    'job_time_to' => $timeTo->endOfDay()
                ],
                [
                'device_id' => $device->id,
                    'device_name' => $device->name,
                    'job_time_from' => $timeFrom->startOfDay(),
                    'job_time_to' => $timeTo->endOfDay(),
                    'total_fuel_theft' => $fuelData['fuel_theft'],
                    'total_fuel_filled' => $fuelData['fuel_filled'],
                    'fuel_consumption' => $fuelData['fuel_consumption'],
                    'fuel_average' => $fuelData['fuel_average'],
                'min_fuel_level' => $fuelData['fuel_levels']['min_fuel_level'],
                'max_fuel_level' => $fuelData['fuel_levels']['max_fuel_level'],
                'avg_fuel_level' => $fuelData['fuel_levels']['avg_fuel_level'],
                    'distance' => $fuelData['movement_data']['distance'],
                    'total_moving_time' => $this->secondsToTimeFormat($fuelData['movement_data']['moving_time']),
                    'total_idle_time' => $this->secondsToTimeFormat($fuelData['movement_data']['idle_time']),
                    'total_stop_time' => $this->secondsToTimeFormat($fuelData['movement_data']['stop_time']),
                'calculated_at' => $fuelData['calculated_at'],
                    'created_at' => $record->created_at ?? Carbon::now(),
                'updated_at' => Carbon::now()
                ]
            );

            $action = $record->wasRecentlyCreated ? 'Created' : 'Updated';
            $this->line("{$action} calculation for device: {$device->name} ({$timeFrom->toDateString()})");

        } catch (\Exception $e) {
            Log::error("Error saving/updating calculation result for device {$device->id}: " . $e->getMessage());
            throw $e;
        }
    }
}

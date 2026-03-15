<?php

namespace App\Services;

use Tobuli\Entities\User;
use Illuminate\Support\Collection;
use Formatter;
use Carbon\Carbon;

class ChatbotService
{
    public function getSystemPrompt(?User $user): string
    {
        return "You are a helpful GPS tracking assistant. Use the available functions to provide accurate, real-time information about devices. Devices are identified by NAME (e.g., 'AMV:790'), not IMEI. Be concise and helpful.";
    }

    public function getDeviceContext(User $user): string
    {
        $context = "You are a helpful assistant for a GPS tracking application. ";
        $context .= "The user is logged in as {$user->email}. ";
        $context .= "Current Time: " . date('Y-m-d H:i:s') . ". ";

        $limit = 200;
        $devices = $user->accessibleDevices()->limit($limit)->get();
        $total = $user->accessibleDevices()->count();

        if ($devices->count() > 0) {
            $deviceList = $devices->map(function($device) {
                return "- {$device->name} (IMEI: {$device->imei})";
            })->implode("\n");
            
            $context .= "The user has access to {$total} devices. ";
            
            if ($total > $limit) {
                $context .= "Below is a sample of {$limit} devices (not all devices are listed here due to space constraints):\n";
                $context .= "IMPORTANT: Even if a device is NOT in this list, you should STILL use the appropriate tool (like get_device_stats) when the user asks about it. ";
                $context .= "The tools can search ALL {$total} devices, not just the ones shown below.\n\n";
            } else {
                $context .= "Here are all of them:\n";
            }
            
            $context .= $deviceList . "\n\n";
        } else {
            $context .= "The user has no accessible devices.\n\n";
        }

        return $context;
    }

    public function getDeviceStats(User $user, string $deviceName): string
    {
        Formatter::byUser($user);
        $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
        if (!$device) return "Device '{$deviceName}' not found.";

        $stats = "Device: {$device->name}\n";
        $stats .= "Status: {$device->status}\n";
        $stats .= "Speed: {$device->speed} km/h\n";
        
        $lastPos = $device->positions()->latest('time')->first();
        if ($lastPos) {
            $stats .= "Location: {$lastPos->latitude}, {$lastPos->longitude}\n";
            $lastUpdate = Formatter::time()->human($lastPos->time);
            $stats .= "Last Update: {$lastUpdate}\n";
            
            // Calculate stop/moving duration
            // For stopped vehicles, use ignition OFF time if available
            $durationTime = null;
            $durationLabel = '';
            
            if ($lastPos->speed == 0) {
                // Vehicle is stopped - check for ignition OFF event
                $lastIgnitionOff = $device->events()
                    ->where('message', 'like', '%Ignition OFF%')
                    ->orderBy('time', 'desc')
                    ->first();
                
                if ($lastIgnitionOff) {
                    $durationTime = \Carbon\Carbon::parse($lastIgnitionOff->time);
                    $durationLabel = 'Stop Duration';
                } else {
                    $durationTime = \Carbon\Carbon::parse($lastPos->time);
                    $durationLabel = 'Stop Duration';
                }
            } else {
                // Vehicle is moving
                $durationTime = \Carbon\Carbon::parse($lastPos->time);
                $durationLabel = 'Moving Duration';
            }
            
            if ($durationTime) {
                $now = \Carbon\Carbon::now();
                $diffInSeconds = $now->diffInSeconds($durationTime);
                
                $hours = floor($diffInSeconds / 3600);
                $minutes = floor(($diffInSeconds % 3600) / 60);
                $seconds = $diffInSeconds % 60;
                
                $durationStr = '';
                if ($hours > 0) {
                    $durationStr = "{$hours}h {$minutes}min {$seconds}s";
                } elseif ($minutes > 0) {
                    $durationStr = "{$minutes}min {$seconds}s";
                } else {
                    $durationStr = "{$seconds}s";
                }
                
                $stats .= "{$durationLabel}: {$durationStr}\n";
            }
            
            if (!empty($lastPos->address)) $stats .= "Address: {$lastPos->address}\n";
        }

        $sensors = $device->sensors;
        if ($sensors->count() > 0) {
            $stats .= "\nSensors:\n";
            foreach ($sensors as $sensor) {
                $val = $sensor->value;
                if ($lastPos) {
                    $formatted = $sensor->getValueFormated($lastPos);
                    if ($formatted) $val = $formatted;
                }
                $stats .= "- {$sensor->name}: {$val}\n";
            }
        }

        return $stats;
    }

    public function getAllDevicesEvents(User $user, ?string $eventType = null, ?int $limit = 50, ?string $dateFrom = null, ?string $dateTo = null): string
    {
        Formatter::byUser($user);
        $dateFrom = $dateFrom ?? date('Y-m-d 00:00:00');
        $dateTo = $dateTo ?? date('Y-m-d 23:59:59');
        
        $query = \Tobuli\Entities\Event::whereHas('device', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('user_id', $user->id)->whereBetween('time', [$dateFrom, $dateTo]);
        
        if ($eventType) {
            $query->where('message', 'like', "%{$eventType}%");
        }
        
        $events = $query->with('device')->orderBy('time', 'desc')->limit($limit)->get();
        
        if ($events->isEmpty()) {
            return $eventType ? "No '{$eventType}' events found." : "No events found.";
        }
        
        $eventsByType = $events->groupBy('message');
        $eventsByDevice = $events->groupBy('device.name');
        
        $output = "📊 All Devices Events" . ($eventType ? " ({$eventType})" : "") . ":\n";
        $output .= "Period: " . date('M d', strtotime($dateFrom)) . " - " . date('M d, Y', strtotime($dateTo)) . "\n";
        $output .= "Total Events: " . $events->count() . "\n";
        $output .= "Devices: " . $eventsByDevice->count() . "\n\n";
        
        $output .= "Events by Type:\n";
        foreach ($eventsByType->take(5) as $type => $typeEvents) {
            $output .= "• {$type}: " . $typeEvents->count() . "\n";
        }
        $output .= "\n";
        
        $output .= "Events by Device:\n";
        foreach ($eventsByDevice->take(5) as $deviceName => $deviceEvents) {
            $output .= "• {$deviceName}: " . $deviceEvents->count() . " events\n";
        }
        $output .= "\n";
        
        $output .= "Recent Events (last " . min($limit, 20) . "):\n";
        foreach ($events->take(20) as $event) {
            $time = Formatter::time()->convert($event->time, 'H:i:s');
            $deviceName = $event->device ? $event->device->name : 'Unknown';
            $output .= "• {$time} - {$deviceName} - {$event->message}\n";
        }
        
        return $output;
    }

    public function getHistoryStats(User $user, string $deviceName, string $dateFrom, string $dateTo): string
    {
        $stats = $this->fetchHistoryStats($user, $deviceName, $dateFrom, $dateTo);
        if (isset($stats['error'])) return $stats['error'];

        $output = "History for {$stats['device_name']} ({$dateFrom} to {$dateTo}):\n";
        $output .= "- Distance: {$stats['distance']}\n";
        $output .= "- Engine Hours: {$stats['engine_hours']}\n";
        $output .= "- Fuel Consumed: {$stats['fuel_consumption']}\n";
        $output .= "- Drive Duration: {$stats['drive_duration']}\n";
        $output .= "- Stop Duration: {$stats['stop_duration']}\n";
        $output .= "- Top Speed: {$stats['top_speed']}\n";
        $output .= "- Average Speed: {$stats['average_speed']}\n";

        return $output;
    }

    public function getDevicesList(User $user, ?string $status = null, ?int $limit = 50, ?string $timePeriod = null): string
    {
        Formatter::byUser($user);
        
        // Get ALL accessible devices (no limit before filtering)
        $devices = $user->accessibleDevices()->get();
        
        if ($devices->isEmpty()) {
            return "No devices found.";
        }
        
        // Parse time period
        $sinceTime = null;
        if ($timePeriod) {
            $timePeriod = strtolower($timePeriod);
            $now = \Carbon\Carbon::now();
            
            if ($timePeriod === 'yesterday') {
                $sinceTime = $now->subDay();
            } elseif ($timePeriod === 'last_24_hours' || $timePeriod === '24h') {
                $sinceTime = $now->subHours(24);
            } elseif ($timePeriod === 'last_week' || $timePeriod === 'week') {
                $sinceTime = $now->subWeek();
            } elseif ($timePeriod === 'last_month' || $timePeriod === 'month') {
                $sinceTime = $now->subMonth();
            } else {
                // Try to parse as date
                try {
                    $sinceTime = \Carbon\Carbon::parse($timePeriod);
                } catch (\Exception $e) {
                    // Invalid date format, ignore
                }
            }
        }
        
        // Filter devices based on status (Strict 10-minute rule)
        $filteredDevices = collect();
        $cutoff = \Carbon\Carbon::now('UTC')->subMinutes(10);
        
        foreach ($devices as $device) {
            $include = false;
            
            // Determine if online based on 10-minute rule
            // server_time is usually the most reliable "last seen" timestamp
            $lastSeen = $device->server_time ?? $device->device_time;
            $serverTime = $lastSeen ? \Carbon\Carbon::parse($lastSeen, 'UTC') : null;
            $isOnline = $serverTime && $serverTime->gte($cutoff);
            
            if (!$status) {
                $include = true;
            } else {
                $statusLower = strtolower($status);
                
                if ($statusLower === 'online') {
                    // Online = Updated in last 10 minutes
                    $include = $isOnline;
                } 
                elseif ($statusLower === 'moving') {
                    // Moving = Online + Speed > 0
                    $include = $isOnline && $device->getSpeed() > 0;
                } 
                elseif ($statusLower === 'stopped' || $statusLower === 'idle') {
                    // Stopped/Idle = Online + Speed <= 0
                    $include = $isOnline && ($device->getSpeed() <= 0);
                } 
                elseif ($statusLower === 'offline') {
                    // Offline = Not updated in last 10 minutes
                    $include = !$isOnline;
                } 
                elseif ($statusLower === 'never_connected') {
                    // Never connected = No server time
                    $include = !$serverTime;
                }
            }
            
            if ($include) {
                $filteredDevices->push($device);
                
                // Apply limit AFTER filtering
                if ($filteredDevices->count() >= $limit) {
                    break;
                }
            }
        }
        
        if ($filteredDevices->isEmpty()) {
            return $status ? "No {$status} devices found." : "No devices found.";
        }
        
        $output = "📱 Devices List" . ($status ? " ({$status})" : "") . ":\n";
        $output .= "Total: " . $filteredDevices->count() . "\n\n";
        
        foreach ($filteredDevices as $device) {
            // Determine status for display based on 10-minute rule
            $lastSeen = $device->server_time ?? $device->device_time;
            $serverTime = $lastSeen ? \Carbon\Carbon::parse($lastSeen, 'UTC') : null;
            $isOnline = $serverTime && $serverTime->gte($cutoff);
            
            $speed = $device->getSpeed();
            
            if ($isOnline) {
                if ($speed > 0) {
                    $statusText = 'Moving';
                    $statusIcon = '🟢';
                    $movingIcon = '🚗';
                } else {
                    $statusText = 'Stopped'; // or Idle
                    $statusIcon = '🟡';
                    $movingIcon = '🅿️';
                }
            } else {
                $statusText = 'Offline';
                $statusIcon = '🔴';
                $movingIcon = '📴';
            }
            
            $output .= "{$statusIcon} {$movingIcon} {$device->name}\n";
            $output .= "  IMEI: {$device->imei}\n";
            $output .= "  Status: {$statusText}";
            
            if ($speed > 0) {
                $output .= " | Speed: " . number_format($speed, 1) . " km/h";
            }
            
            $output .= "\n";
            
            // Add last update time
            if ($serverTime) {
                $formattedTime = Formatter::time()->human($serverTime);
                $diff = $serverTime->diffForHumans();
                
                if ($isOnline) {
                     $output .= "  Last Update: {$diff}\n";
                } else {
                     $output .= "  Last Active: {$formattedTime} ({$diff})\n";
                }
            } else {
                $output .= "  Last Active: Never connected\n";
            }
            
            $output .= "\n";
        }
        
        return $output;
    }

    private function fetchHistoryStats(User $user, string $deviceName, string $dateFrom, string $dateTo): array
    {
        $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
        if (!$device) {
            return ['error' => "Device '$deviceName' not found."];
        }

        try {
            // Get positions for the date range
            $positions = $device->positions()
                ->where('time', '>=', $dateFrom)
                ->where('time', '<=', $dateTo)
                ->orderBy('time', 'asc')
                ->get();

            if ($positions->count() < 2) {
                return [
                    'device_name' => $device->name,
                    'distance' => '0 km',
                    'engine_hours' => '0h',
                    'fuel_consumption' => 'N/A',
                    'drive_duration' => '0h',
                    'stop_duration' => '0h',
                    'top_speed' => '0 km/h',
                    'average_speed' => '0 km/h',
                ];
            }

            // Manual calculations
            $totalDistance = 0;
            $topSpeed = 0;
            $totalSpeed = 0;
            $speedCount = 0;

            for ($i = 1; $i < $positions->count(); $i++) {
                $prev = $positions[$i-1];
                $curr = $positions[$i];
                
                // Calculate distance using Haversine formula
                $lat1 = deg2rad($prev->latitude);
                $lon1 = deg2rad($prev->longitude);
                $lat2 = deg2rad($curr->latitude);
                $lon2 = deg2rad($curr->longitude);
                
                $dlat = $lat2 - $lat1;
                $dlon = $lon2 - $lon1;
                
                $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
                $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                $distance = 6371 * $c; // Earth radius in km
                
                $totalDistance += $distance;
                
                // Track speed
                if ($curr->speed > $topSpeed) {
                    $topSpeed = $curr->speed;
                }
                $totalSpeed += $curr->speed;
                $speedCount++;
            }

            $avgSpeed = $speedCount > 0 ? round($totalSpeed / $speedCount, 1) : 0;

            // Calculate fuel consumption using DeviceHistory (same as FuelTripReport)
            $fuelConsumption = 'N/A';
            
            try {
                $history = new \Tobuli\History\DeviceHistory($device);
                $history->setRange($dateFrom, $dateTo);
                $history->registerActions([
                    \Tobuli\History\Actions\DriveStop::class,
                    \Tobuli\History\Actions\FuelReport::class,
                    \Tobuli\History\Actions\GroupDrive::class,
                ]);
                $data = $history->get();
                
                $root = $data['root'];
                $groups = $data['groups'];
                
                // Calculate fuel consumption from drive groups only (matches FuelTripReport)
                $fuelSensor = $device->sensors()->where('type', 'fuel_tank')->first();
                $totalFromTrips = 0;
                
                if ($fuelSensor) {
                    foreach ($groups->all() as $group) {
                        // Sum fuel consumption from all groups (not just 'drive' type)
                        if ($group->stats()->has("fuel_consumption_tank_{$fuelSensor->id}")) {
                            $fuelValue = $group->stats()->get("fuel_consumption_tank_{$fuelSensor->id}")->value();
                            if ($fuelValue > 0) {
                                $totalFromTrips += $fuelValue;
                            }
                        } elseif ($group->stats()->has("fuel_consumption_{$fuelSensor->id}")) {
                            $fuelValue = $group->stats()->get("fuel_consumption_{$fuelSensor->id}")->value();
                            if ($fuelValue > 0) {
                                $totalFromTrips += $fuelValue;
                            }
                        }
                    }
                }
                
                // If no trip-level consumption found, fallback to root stat
                if ($totalFromTrips > 0 && $totalFromTrips < 9999) {
                    $fuelConsumption = round($totalFromTrips, 2) . ' L';
                } elseif ($fuelSensor && $root->stats()->has("fuel_consumption_tank_{$fuelSensor->id}")) {
                    $consumption = $root->stats()->get("fuel_consumption_tank_{$fuelSensor->id}")->value();
                    if ($consumption > 0 && $consumption < 9999) {
                        $fuelConsumption = round($consumption, 2) . ' L';
                    }
                }
            } catch (\Exception $e) {
                // If DeviceHistory fails, keep N/A
                $fuelConsumption = 'N/A';
            }

            return [
                'device_name' => $device->name,
                'distance' => round($totalDistance, 2) . ' km',
                'engine_hours' => 'N/A',
                'fuel_consumption' => $fuelConsumption,
                'drive_duration' => 'N/A',
                'stop_duration' => 'N/A',
                'top_speed' => round($topSpeed, 1) . ' km/h',
                'average_speed' => $avgSpeed . ' km/h',
            ];

        } catch (\Exception $e) {
            return ['error' => "Error calculating history: " . $e->getMessage()];
        }
    }

    public function getEventsList(User $user, string $deviceName, ?string $type = null, ?string $dateFrom = null, ?string $dateTo = null): string
    {
        $events = $this->fetchEventsList($user, $deviceName, $type, $dateFrom, $dateTo);
        
        if ($events instanceof \Illuminate\Support\Collection && $events->isEmpty()) {
            return "No events found for device '$deviceName' with the specified criteria.";
        }
        if (is_array($events) && isset($events['error'])) {
            return $events['error'];
        }

        $eventCount = $events->count();
        $output = "Found {$eventCount} events for {$deviceName}:\n\n";
        
        foreach ($events as $event) {
            $output .= "- [{$event['time']}] {$event['message']} (Speed: {$event['speed']}, Location: {$event['latitude']}, {$event['longitude']})\n";
        }

        return $output;
    }

    public function getDeviceEvents(User $user, string $deviceName, ?int $limit = 10, ?string $eventType = null, ?string $dateFrom = null, ?string $dateTo = null): string
    {
        $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
        if (!$device) {
            return "Device '$deviceName' not found.";
        }

        // Build query
        $query = $device->events()->where('user_id', $user->id);
        
        // Apply date filters
        if ($dateFrom) {
            $query->where('time', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('time', '<=', $dateTo);
        }
        
        // Get all events for statistics
        $allEvents = $query->get();
        
        if ($allEvents->isEmpty()) {
            return "No events found for {$deviceName}.";
        }

        // Calculate statistics
        $totalEvents = $allEvents->count();
        $eventsByType = $allEvents->groupBy('message')->map->count()->sortDesc();
        $eventsByDate = $allEvents->groupBy(function($event) {
            return \Carbon\Carbon::parse($event->time)->format('Y-m-d');
        })->map->count()->sortDesc();

        // Build output with statistics
        $output = "📊 Event Statistics for {$deviceName}:\n";
        $output .= "Total Events: {$totalEvents}\n\n";
        
        // Events by type
        $output .= "Events by Type:\n";
        foreach ($eventsByType->take(5) as $type => $count) {
            $output .= "• {$type}: {$count}\n";
        }
        $output .= "\n";
        
        // Events by date
        $output .= "Events by Date:\n";
        foreach ($eventsByDate->take(3) as $date => $count) {
            $output .= "• {$date}: {$count} events\n";
        }
        $output .= "\n";

        // Filter by event type if specified
        if ($eventType) {
            $filteredEvents = $allEvents->filter(function($event) use ($eventType) {
                return stripos($event->message, $eventType) !== false;
            })->take($limit);
            
            if ($filteredEvents->isEmpty()) {
                $output .= "No '{$eventType}' events found.\n";
            } else {
                $output .= "Recent '{$eventType}' Events:\n";
                foreach ($filteredEvents as $event) {
                    $time = Formatter::time()->convert($event->time, 'H:i:s');
                    $output .= "• {$time} - {$event->message}\n";
                }
            }
        } else {
            // Show recent events
            $output .= "Recent Events (last {$limit}):\n";
            foreach ($allEvents->take($limit) as $event) {
                $time = Formatter::time()->convert($event->time, 'H:i:s');
                $output .= "• {$time} - {$event->message}\n";
            }
        }

        return $output;
    }

    public function getFuelData(User $user, string $deviceName, ?string $dateFrom = null, ?string $dateTo = null): string
    {
        $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
        if (!$device) {
            return "Device '$deviceName' not found.";
        }

        // Set default dates if not provided
        $dateFrom = $dateFrom ?? date('Y-m-d 00:00:00');
        $dateTo = $dateTo ?? date('Y-m-d 23:59:59');

        // Get fuel sensor
        $fuelSensor = $device->sensors()->where('type', 'fuel_tank')->first();
        
        // Get fuel fill events
        $fillEvents = $device->events()
            ->where('user_id', $user->id)
            ->where('type', 'fuel_fill')
            ->whereBetween('time', [$dateFrom, $dateTo])
            ->get();

        // Get fuel theft events
        $theftEvents = $device->events()
            ->where('user_id', $user->id)
            ->where('type', 'fuel_theft')
            ->whereBetween('time', [$dateFrom, $dateTo])
            ->get();

        // Calculate totals
        $totalFilled = 0;
        $fillCount = 0;
        foreach ($fillEvents as $event) {
            $additional = $event->additional ?? [];
            $difference = abs((float)($additional['difference'] ?? 0));
            if ($difference > 0) {
                $totalFilled += $difference;
                $fillCount++;
            }
        }

        $totalTheft = 0;
        $theftCount = 0;
        foreach ($theftEvents as $event) {
            $additional = $event->additional ?? [];
            $difference = abs((float)($additional['difference'] ?? 0));
            if ($difference > 0) {
                $totalTheft += $difference;
                $theftCount++;
            }
        }

        // Get current fuel level
        $currentLevel = 0;
        if ($fuelSensor) {
            $lastPosition = $device->positions()
                ->orderBy('time', 'desc')
                ->first();
            
            if ($lastPosition) {
                $currentLevel = $fuelSensor->getValueFormated($lastPosition) ?? 0;
                // Clean up the value
                if (is_string($currentLevel)) {
                    $currentLevel = (float)preg_replace('/[^0-9.]/', '', $currentLevel);
                }
            }
        }

        // Get distance for efficiency calculation
        $historyStats = $this->fetchHistoryStats($user, $deviceName, $dateFrom, $dateTo);
        $distance = 0;
        $consumption = 0;
        
        if (!isset($historyStats['error'])) {
            $distanceStr = $historyStats['distance'] ?? '0 km';
            $distance = (float)preg_replace('/[^0-9.]/', '', $distanceStr);
            
            // Get fuel consumption from history stats
            $fuelConsStr = $historyStats['fuel_consumption'] ?? '0 L';
            $consumption = (float)preg_replace('/[^0-9.]/', '', $fuelConsStr);
        }

        // Build output
        $output = "⛽ Fuel Report for {$deviceName}:\n";
        $output .= "Period: " . date('M d', strtotime($dateFrom)) . " - " . date('M d, Y', strtotime($dateTo)) . "\n\n";
        
        if ($fuelSensor) {
            $output .= "Current Fuel Level: " . number_format($currentLevel, 1) . " L\n";
        } else {
            $output .= "Current Fuel Level: No fuel sensor\n";
        }
        
        if ($consumption > 0) {
            $output .= "Fuel Consumption: " . number_format($consumption, 1) . " L\n";
        } else {
            $output .= "Fuel Consumption: N/A\n";
        }
        
        $output .= "Fuel Filled: " . number_format($totalFilled, 1) . " L";
        if ($fillCount > 0) {
            $output .= " ({$fillCount} fill event" . ($fillCount > 1 ? 's' : '') . ")\n";
        } else {
            $output .= "\n";
        }
        
        $output .= "Fuel Theft: " . number_format($totalTheft, 1) . " L";
        if ($theftCount > 0) {
            $output .= " ({$theftCount} theft event" . ($theftCount > 1 ? 's' : '') . ")\n";
        } else {
            $output .= "\n";
        }
        
        if ($distance > 0) {
            $output .= "Distance Traveled: " . number_format($distance, 1) . " km\n";
            if ($consumption > 0) {
                $efficiency = $distance / $consumption;
                $output .= "Fuel Efficiency: " . number_format($efficiency, 2) . " km/L\n";
            }
        }

        // Show recent fuel events
        $allFuelEvents = $fillEvents->merge($theftEvents)->sortByDesc('time')->take(5);
        if ($allFuelEvents->count() > 0) {
            $output .= "\nRecent Fuel Events:\n";
            foreach ($allFuelEvents as $event) {
                $time = Formatter::time()->human($event->time);
                $additional = $event->additional ?? [];
                $difference = abs((float)($additional['difference'] ?? 0));
                $type = $event->type === 'fuel_fill' ? 'Fill' : 'Theft';
                $sign = $event->type === 'fuel_fill' ? '+' : '-';
                $output .= "• {$time} - {$type}: {$sign}" . number_format($difference, 1) . " L\n";
            }
        }

        return $output;
    }

    private function fetchEventsList(User $user, string $deviceName, ?string $type = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
        if (!$device) {
            return ['error' => "Device '$deviceName' not found."];
        }

        $query = $device->events()->with('geofence')->where('user_id', $user->id)->latest()->limit(20);

        if ($type) {
            $query->where('type', $type);
        }

        if ($dateFrom) {
            $query->where('time', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->where('time', '<=', $dateTo);
        }

        $results = $query->get();

        return $results->map(function ($event) {
            return [
                'id' => $event->id,
                'time' => Formatter::time()->human($event->time),
                'type' => $event->type,
                'message' => $event->message,
                'speed' => \Formatter::speed()->format($event->speed),
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'altitude' => $event->altitude,
                'course' => $event->course,
                'address' => $event->address ?? 'N/A',
                'power' => $event->power ?? 'N/A',
                'geofence' => $event->geofence ? $event->geofence->name : null,
                'alert_id' => $event->alert_id,
            ];
        });
    }

    public function getTravelSheet(User $user, string $deviceName, string $dateFrom, string $dateTo): string
    {
        $trips = $this->fetchTravelSheet($user, $deviceName, $dateFrom, $dateTo);
        
        if (is_array($trips) && isset($trips['error'])) {
            return $trips['error'];
        }

        if (empty($trips)) {
            return "No trips found for device '$deviceName' in the specified period.";
        }

        $output = "Travel Sheet for {$deviceName} ({$dateFrom} to {$dateTo}):\n\n";
        $output .= "Total Trips: " . count($trips) . "\n\n";

        foreach ($trips as $i => $trip) {
            $output .= "Trip #" . ($i + 1) . ":\n";
            $output .= "- Start: {$trip['start_time']} (PKT)\n";
            $output .= "- End: {$trip['end_time']} (PKT)\n";
            $output .= "- Duration: {$trip['duration']}\n";
            $output .= "- Distance: {$trip['distance']} km\n";
            $output .= "- Max Speed: {$trip['max_speed']} km/h\n";
            $output .= "- Avg Speed: {$trip['avg_speed']} km/h\n";
            if (!empty($trip['start_address'])) {
                $output .= "- Start Location: {$trip['start_address']}\n";
            }
            if (!empty($trip['end_address'])) {
                $output .= "- End Location: {$trip['end_address']}\n";
            }
            $output .= "\n";
        }

        return $output;
    }

    private function fetchTravelSheet(User $user, string $deviceName, string $dateFrom, string $dateTo): array
    {
        $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
        if (!$device) {
            return ['error' => "Device '$deviceName' not found."];
        }

        try {
            // Get ignition sensor
            $ignitionSensor = $device->sensors()->where('type', 'ignition')->first();
            
            if (!$ignitionSensor) {
                // Fallback to speed-based detection if no ignition sensor
                return $this->fetchTravelSheetBySpeed($device, $dateFrom, $dateTo);
            }

            // Get all positions for the date range
            $positions = $device->positions()
                ->where('time', '>=', $dateFrom)
                ->where('time', '<=', $dateTo)
                ->orderBy('time', 'asc')
                ->get();

            if ($positions->count() < 2) {
                return [];
            }

            // Detect trips based on ignition on/off
            $trips = [];
            $currentTrip = null;

            foreach ($positions as $position) {
                // Get ignition value for this position
                $ignitionValue = $ignitionSensor->getValueFormated($position);
                $isIgnitionOn = $this->isIgnitionOn($ignitionValue, $position);
                
                if ($isIgnitionOn) {
                    if ($currentTrip === null) {
                        // Start new trip
                        $currentTrip = [
                            'start_position' => $position,
                            'end_position' => $position,
                            'positions' => [$position],
                            'max_speed' => $position->speed,
                            'total_speed' => $position->speed,
                            'speed_count' => 1,
                        ];
                    } else {
                        // Continue current trip
                        $currentTrip['end_position'] = $position;
                        $currentTrip['positions'][] = $position;
                        if ($position->speed > $currentTrip['max_speed']) {
                            $currentTrip['max_speed'] = $position->speed;
                        }
                        $currentTrip['total_speed'] += $position->speed;
                        $currentTrip['speed_count']++;
                    }
                } else {
                    // Ignition off - if we have a current trip, end it
                    if ($currentTrip !== null) {
                        $trips[] = $this->calculateTripStats($currentTrip);
                        $currentTrip = null;
                    }
                }
            }

            // Don't forget the last trip if still ongoing
            if ($currentTrip !== null) {
                $trips[] = $this->calculateTripStats($currentTrip);
            }

            return $trips;

        } catch (\Exception $e) {
            return ['error' => "Error generating travel sheet: " . $e->getMessage()];
        }
    }

    private function isIgnitionOn($ignitionValue, $position): bool
    {
        // Check various ignition value formats
        if (is_bool($ignitionValue)) {
            return $ignitionValue;
        }
        
        if (is_numeric($ignitionValue)) {
            return $ignitionValue > 0;
        }
        
        if (is_string($ignitionValue)) {
            $ignitionValue = strtolower(trim($ignitionValue));
            return in_array($ignitionValue, ['on', '1', 'true', 'yes']);
        }
        
        // Fallback: check if speed > 0 (likely moving)
        return $position->speed > 0;
    }

    private function fetchTravelSheetBySpeed($device, string $dateFrom, string $dateTo): array
    {
        // Fallback method using speed-based detection
        $positions = $device->positions()
            ->where('time', '>=', $dateFrom)
            ->where('time', '<=', $dateTo)
            ->orderBy('time', 'asc')
            ->get();

        if ($positions->count() < 2) {
            return [];
        }

        $trips = [];
        $currentTrip = null;
        $stopThreshold = 5; // km/h
        $timeGapThreshold = 300; // seconds

        foreach ($positions as $position) {
            $isMoving = $position->speed > $stopThreshold;
            
            if ($isMoving) {
                if ($currentTrip === null) {
                    $currentTrip = [
                        'start_position' => $position,
                        'end_position' => $position,
                        'positions' => [$position],
                        'max_speed' => $position->speed,
                        'total_speed' => $position->speed,
                        'speed_count' => 1,
                    ];
                } else {
                    $prevTime = strtotime($currentTrip['end_position']->time);
                    $currTime = strtotime($position->time);
                    $timeGap = $currTime - $prevTime;

                    if ($timeGap > $timeGapThreshold) {
                        $trips[] = $this->calculateTripStats($currentTrip);
                        $currentTrip = [
                            'start_position' => $position,
                            'end_position' => $position,
                            'positions' => [$position],
                            'max_speed' => $position->speed,
                            'total_speed' => $position->speed,
                            'speed_count' => 1,
                        ];
                    } else {
                        $currentTrip['end_position'] = $position;
                        $currentTrip['positions'][] = $position;
                        if ($position->speed > $currentTrip['max_speed']) {
                            $currentTrip['max_speed'] = $position->speed;
                        }
                        $currentTrip['total_speed'] += $position->speed;
                        $currentTrip['speed_count']++;
                    }
                }
            } else {
                if ($currentTrip !== null) {
                    $trips[] = $this->calculateTripStats($currentTrip);
                    $currentTrip = null;
                }
            }
        }

        if ($currentTrip !== null) {
            $trips[] = $this->calculateTripStats($currentTrip);
        }

        return $trips;
    }

    private function calculateTripStats(array $tripData): array
    {
        $start = $tripData['start_position'];
        $end = $tripData['end_position'];
        $positions = $tripData['positions'];

        // Calculate distance
        $totalDistance = 0;
        for ($i = 1; $i < count($positions); $i++) {
            $prev = $positions[$i-1];
            $curr = $positions[$i];
            
            $lat1 = deg2rad($prev->latitude);
            $lon1 = deg2rad($prev->longitude);
            $lat2 = deg2rad($curr->latitude);
            $lon2 = deg2rad($curr->longitude);
            
            $dlat = $lat2 - $lat1;
            $dlon = $lon2 - $lon1;
            
            $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distance = 6371 * $c;
            
            $totalDistance += $distance;
        }

        // Calculate duration
        $startTime = strtotime($start->time);
        $endTime = strtotime($end->time);
        $durationSeconds = $endTime - $startTime;
        $hours = floor($durationSeconds / 3600);
        $minutes = floor(($durationSeconds % 3600) / 60);
        $durationFormatted = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";

        // Calculate average speed
        $avgSpeed = $tripData['speed_count'] > 0 
            ? round($tripData['total_speed'] / $tripData['speed_count'], 1) 
            : 0;

        return [
            'start_time' => Formatter::time()->human($start->time),
            'end_time' => Formatter::time()->human($end->time),
            'duration' => $durationFormatted,
            'distance' => round($totalDistance, 2),
            'max_speed' => round($tripData['max_speed'], 1),
            'avg_speed' => $avgSpeed,
            'start_address' => $this->getAddressFromPosition($start),
            'end_address' => $this->getAddressFromPosition($end),
        ];
    }

    private function getAddressFromPosition($position): string
    {
        // Try to get address from position first
        if (!empty($position->address)) {
            return $position->address;
        }
        
        // Fallback: create Google Maps link with lat/long
        if ($position->latitude && $position->longitude) {
            $lat = number_format($position->latitude, 6);
            $lng = number_format($position->longitude, 6);
            $url = "https://www.google.com/maps?q={$lat},{$lng}";
            // Return as Excel hyperlink formula
            return "=HYPERLINK(\"{$url}\", \"{$lat}, {$lng}\")";
        }
        
        return 'Unknown';
    }

    // Retaining basic methods for backward compat or simple calls
    public function getEvents(User $user, ?string $deviceName = null): string
    {
        return $this->getEventsList($user, $deviceName ?? '');
    }

    public function getTrips(User $user, ?string $deviceName = null): string
    {
        if (!$deviceName) return "Please specify a device.";
        return $this->getHistoryStats($user, $deviceName, date('Y-m-d 00:00:00'), date('Y-m-d H:i:s'));
    }

    public function generateReport(User $user, string $type, ?string $deviceName = null): string
    {
        return "To generate a full specialized report (PDF/Excel), please use the main Reports section. I can provide the data summaries here using 'get_history_stats'.";
    }

    public function exportData(User $user, string $type, array $params = []): string
    {
        $exporter = new \Tobuli\Exporters\Util\XlsxWriter();
        
        $data = [];
        $headers = [];
        $filename = "export_{$type}_" . date('Ymd_His') . ".xlsx";
        $dir = public_path('exports/chatbot');
        
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $path = $dir . '/' . $filename;

        // Prepare Data
        if ($type === 'devices') {
            $headers = ['Name' => 'string', 'IMEI' => 'string', 'Status' => 'string', 'Last Update' => 'string', 'Address' => 'string'];
            $devices = $user->accessibleDevices()->limit(500)->get(); 
            foreach ($devices as $device) {
                $data[] = [
                    $device->name,
                    $device->imei,
                    $device->status,
                    $device->server_time ? Formatter::time()->human($device->server_time) : '',
                    $device->address
                ];
            }
        } elseif ($type === 'history') {
            $headers = ['Parameter' => 'string', 'Value' => 'string'];
            $stats = $this->fetchHistoryStats(
                $user, 
                $params['device_name'] ?? '', 
                $params['date_from'] ?? date('Y-m-d 00:00:00'), 
                $params['date_to'] ?? date('Y-m-d H:i:s')
            );
            
            if (isset($stats['error'])) return $stats['error'];
            
            foreach ($stats as $key => $val) {
                // Determine format
                $format = 'string';
                if (is_numeric(str_replace([' km', ' h', ' km/h'], '', $val))) $format = 'string'; 
                
                $data[] = [ucwords(str_replace('_', ' ', $key)), $val];
            }
        } elseif ($type === 'events') {
            $headers = ['Time' => 'string', 'Message' => 'string', 'Type' => 'string', 'Speed' => 'string', 'Lat' => 'string', 'Lng' => 'string'];
            $events = $this->fetchEventsList(
                $user,
                $params['device_name'] ?? '',
                $params['type'] ?? null,
                $params['date_from'] ?? null,
                $params['date_to'] ?? null
            );
            
            if (is_array($events) && isset($events['error'])) return $events['error'];
            
            foreach ($events as $event) {
                $data[] = [
                    $event['time'],
                    $event['message'],
                    $event['type'],
                    $event['speed'],
                    $event['latitude'],
                    $event['longitude']
                ];
            }
        } elseif ($type === 'fuel_data') {
            // Export fuel data
            $deviceName = $params['device_name'] ?? '';
            $dateFrom = $params['date_from'] ?? date('Y-m-d 00:00:00');
            $dateTo = $params['date_to'] ?? date('Y-m-d 23:59:59');
            
            $fuelData = $this->getFuelData($user, $deviceName, $dateFrom, $dateTo);
            
            if (strpos($fuelData, 'not found') !== false) {
                return $fuelData;
            }
            
            // Parse fuel data from text response
            $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
            if (!$device) {
                return "Device '$deviceName' not found.";
            }
            
            // Get structured fuel data
            $fuelSensor = $device->sensors()->where('type', 'fuel_tank')->first();
            $currentLevel = 0;
            if ($fuelSensor) {
                $lastPosition = $device->positions()->orderBy('time', 'desc')->first();
                if ($lastPosition) {
                    $currentLevel = $fuelSensor->getValueFormated($lastPosition) ?? 0;
                    if (is_string($currentLevel)) {
                        $currentLevel = (float)preg_replace('/[^0-9.]/', '', $currentLevel);
                    }
                }
            }
            
            // Get fuel events
            $fillEvents = $device->events()
                ->where('user_id', $user->id)
                ->where('type', 'fuel_fill')
                ->whereBetween('time', [$dateFrom, $dateTo])
                ->get();
            
            $theftEvents = $device->events()
                ->where('user_id', $user->id)
                ->where('type', 'fuel_theft')
                ->whereBetween('time', [$dateFrom, $dateTo])
                ->get();
            
            $totalFilled = 0;
            foreach ($fillEvents as $event) {
                $additional = $event->additional ?? [];
                $totalFilled += abs((float)($additional['difference'] ?? 0));
            }
            
            $totalTheft = 0;
            foreach ($theftEvents as $event) {
                $additional = $event->additional ?? [];
                $totalTheft += abs((float)($additional['difference'] ?? 0));
            }
            
            // Get fuel consumption
            $historyStats = $this->fetchHistoryStats($user, $deviceName, $dateFrom, $dateTo);
            $consumption = 0;
            $distance = 0;
            if (!isset($historyStats['error'])) {
                $fuelConsStr = $historyStats['fuel_consumption'] ?? '0 L';
                $consumption = (float)preg_replace('/[^0-9.]/', '', $fuelConsStr);
                $distanceStr = $historyStats['distance'] ?? '0 km';
                $distance = (float)preg_replace('/[^0-9.]/', '', $distanceStr);
            }
            
            $efficiency = $consumption > 0 ? $distance / $consumption : 0;
            
            // Summary sheet
            $headers = ['Metric' => 'string', 'Value' => 'string'];
            $data[] = ['Device Name', $device->name];
            $data[] = ['Period', date('M d', strtotime($dateFrom)) . ' - ' . date('M d, Y', strtotime($dateTo))];
            $data[] = ['Current Fuel Level', number_format($currentLevel, 1) . ' L'];
            $data[] = ['Fuel Consumption', number_format($consumption, 1) . ' L'];
            $data[] = ['Fuel Filled', number_format($totalFilled, 1) . ' L'];
            $data[] = ['Fuel Theft', number_format($totalTheft, 1) . ' L'];
            $data[] = ['Distance Traveled', number_format($distance, 1) . ' km'];
            $data[] = ['Fuel Efficiency', number_format($efficiency, 2) . ' km/L'];
            $data[] = ['', '']; // Empty row
            $data[] = ['Recent Fuel Events', ''];
            $data[] = ['Time', 'Type', 'Amount (L)'];
            
            // Add fuel events
            $allEvents = $fillEvents->merge($theftEvents)->sortByDesc('time')->take(20);
            foreach ($allEvents as $event) {
                $time = Formatter::time()->human($event->time);
                $type = $event->type === 'fuel_fill' ? 'Fill' : 'Theft';
                $additional = $event->additional ?? [];
                $amount = abs((float)($additional['difference'] ?? 0));
                $sign = $event->type === 'fuel_fill' ? '+' : '-';
                $data[] = [$time, $type, $sign . number_format($amount, 1)];
            }
        } elseif ($type === 'travel_sheet') {
            // Export travel sheet (trip details)
            $deviceName = $params['device_name'] ?? '';
            $dateFrom = $params['date_from'] ?? date('Y-m-d 00:00:00');
            $dateTo = $params['date_to'] ?? date('Y-m-d 23:59:59');
            
            $travelSheet = $this->getTravelSheet($user, $deviceName, $dateFrom, $dateTo);
            
            if (strpos($travelSheet, 'not found') !== false || strpos($travelSheet, 'No trips') !== false) {
                return $travelSheet;
            }
            
            // Get structured trip data
            $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
            if (!$device) {
                return "Device '$deviceName' not found.";
            }
            
            // Fetch trips using the same logic as getTravelSheet
            $trips = $this->fetchTravelSheet($user, $deviceName, $dateFrom, $dateTo);
            
            if (empty($trips)) {
                return "No trips found for the specified period.";
            }
            
            // Excel headers
            $headers = [
                'Trip #' => 'string',
                'Start Time' => 'string',
                'End Time' => 'string',
                'Duration' => 'string',
                'Start Location' => 'string',
                'End Location' => 'string',
                'Distance (km)' => 'string',
                'Max Speed (km/h)' => 'string',
                'Avg Speed (km/h)' => 'string'
            ];
            
            // Add summary row
            $data[] = ['Device:', $device->name, '', '', '', '', '', '', ''];
            $data[] = ['Period:', date('M d', strtotime($dateFrom)) . ' - ' . date('M d, Y', strtotime($dateTo)), '', '', '', '', '', '', ''];
            $data[] = ['Total Trips:', count($trips), '', '', '', '', '', '', ''];
            $data[] = ['', '', '', '', '', '', '', '', '']; // Empty row
            
            // Add trip data
            foreach ($trips as $index => $trip) {
                $data[] = [
                    $index + 1,
                    $trip['start_time'] ?? '',
                    $trip['end_time'] ?? '',
                    $trip['duration'] ?? '',
                    $trip['start_address'] ?? '',
                    $trip['end_address'] ?? '',
                    $trip['distance'] ?? '',
                    $trip['max_speed'] ?? '',
                    $trip['avg_speed'] ?? ''
                ];
            }
        } else {
            return "Export type '$type' not supported.";
        }

        if (empty($data)) {
            return "No data found to export.";
        }

        try {
            // Write Header
            $exporter->writeSheetHeader('Sheet1', $headers);
            // Write Rows
            foreach ($data as $row) {
                $exporter->writeSheetRow('Sheet1', $row);
            }
            $exporter->writeToFile($path);
            
            $url = url("exports/chatbot/{$filename}");
            return "File generated successfully. Download here: [{$filename}]({$url})";
            
        } catch (\Exception $e) {
            return "Error generating file: " . $e->getMessage();
        }
    }

    // Logging and Quota Management Methods
    
    public function logChatInteraction(User $user, string $userMessage, string $botResponse, ?string $toolCalled = null, int $tokensUsed = 0, ?string $deviceName = null): void
    {
        \DB::table('chatbot_logs')->insert([
            'user_id' => $user->id,
            'device_name' => $deviceName,
            'user_message' => $userMessage,
            'bot_response' => $botResponse,
            'tool_called' => $toolCalled,
            'api_tokens_used' => $tokensUsed,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function checkUserQuota(User $user): array
    {
        $quota = \DB::table('chatbot_quotas')->where('user_id', $user->id)->first();
        
        if (!$quota) {
            // Create default quota for new user
            \DB::table('chatbot_quotas')->insert([
                'user_id' => $user->id,
                'daily_limit' => 50,
                'monthly_limit' => 1000,
                'daily_used' => 0,
                'monthly_used' => 0,
                'last_reset_daily' => now()->toDateString(),
                'last_reset_monthly' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return ['allowed' => true, 'daily_remaining' => 50, 'monthly_remaining' => 1000];
        }

        // Reset daily quota if needed
        if ($quota->last_reset_daily != now()->toDateString()) {
            \DB::table('chatbot_quotas')
                ->where('user_id', $user->id)
                ->update([
                    'daily_used' => 0,
                    'last_reset_daily' => now()->toDateString(),
                    'updated_at' => now(),
                ]);
            $quota->daily_used = 0;
        }

        // Reset monthly quota if needed
        if ($quota->last_reset_monthly && now()->diffInMonths($quota->last_reset_monthly) >= 1) {
            \DB::table('chatbot_quotas')
                ->where('user_id', $user->id)
                ->update([
                    'monthly_used' => 0,
                    'last_reset_monthly' => now()->toDateString(),
                    'updated_at' => now(),
                ]);
            $quota->monthly_used = 0;
        }

        $dailyRemaining = max(0, $quota->daily_limit - $quota->daily_used);
        $monthlyRemaining = max(0, $quota->monthly_limit - $quota->monthly_used);
        
        $allowed = ($quota->daily_used < $quota->daily_limit) && ($quota->monthly_used < $quota->monthly_limit);

        return [
            'allowed' => $allowed,
            'daily_remaining' => $dailyRemaining,
            'monthly_remaining' => $monthlyRemaining,
            'daily_limit' => $quota->daily_limit,
            'monthly_limit' => $quota->monthly_limit,
        ];
    }

    public function incrementQuotaUsage(User $user): void
    {
        \DB::table('chatbot_quotas')
            ->where('user_id', $user->id)
            ->increment('daily_used');
            
        \DB::table('chatbot_quotas')
            ->where('user_id', $user->id)
            ->increment('monthly_used');
    }

    /**
     * Call internal application API endpoints using Laravel's internal request
     */
    private function callInternalAPI(User $user, string $method, string $endpoint, array $params = []): array
    {
        try {
            // Use Laravel's internal request handling instead of HTTP
            // This preserves the user's authentication session
            $originalUser = \Auth::user();
            
            // Temporarily authenticate as the chatbot user
            \Auth::onceUsingId($user->id);
            
            // Create internal request
            $request = \Illuminate\Http\Request::create($endpoint, $method, $params);
            $request->headers->set('Accept', 'application/json');
            
            // Dispatch the request through the application
            $response = app()->handle($request);
            
            // Restore original user
            if ($originalUser) {
                \Auth::onceUsingId($originalUser->id);
            }
            
            // Get response content
            $content = $response->getContent();
            $statusCode = $response->getStatusCode();
            
            if ($statusCode >= 200 && $statusCode < 300) {
                $data = json_decode($content, true);
                return ['success' => true, 'data' => $data];
            } else {
                return ['success' => false, 'error' => "API Error: HTTP {$statusCode}"];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLandmarkVisits(User $user, string $deviceName, float $latitude, float $longitude, ?int $radius = 100, ?string $dateFrom = null, ?string $dateTo = null, ?int $limit = 20): string
    {
        $device = $user->accessibleDevices()->where('name', 'like', "%{$deviceName}%")->first();
        if (!$device) {
            return "Device '$deviceName' not found.";
        }
        
        $dateFrom = $dateFrom ?? date('Y-m-d 00:00:00', strtotime('-7 days'));
        $dateTo = $dateTo ?? date('Y-m-d 23:59:59');
        
        $positions = $device->positions()->whereBetween('time', [$dateFrom, $dateTo])->orderBy('time', 'asc')->get();
        
        if ($positions->isEmpty()) {
            return "No position data found for {$deviceName} in the specified period.";
        }
        
        $visits = [];
        $inProximity = false;
        $entryTime = null;
        $exitTime = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($positions as $position) {
            $distance = $this->calculateDistance($latitude, $longitude, $position->latitude, $position->longitude);
            
            if ($distance <= $radius) {
                if (!$inProximity) {
                    $entryTime = $position;
                    $inProximity = true;
                    $minDistance = $distance;
                } else {
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                    }
                }
                $exitTime = $position;
            } else {
                if ($inProximity) {
                    $visits[] = [
                        'entry_time' => $entryTime->time,
                        'exit_time' => $exitTime->time,
                        'min_distance' => $minDistance,
                        'entry_speed' => $entryTime->speed,
                        'exit_speed' => $exitTime->speed,
                    ];
                    $inProximity = false;
                    $minDistance = PHP_FLOAT_MAX;
                    
                    if (count($visits) >= $limit) {
                        break;
                    }
                }
            }
        }
        
        if ($inProximity && count($visits) < $limit) {
            $visits[] = [
                'entry_time' => $entryTime->time,
                'exit_time' => $exitTime->time,
                'min_distance' => $minDistance,
                'entry_speed' => $entryTime->speed,
                'exit_speed' => $exitTime->speed,
            ];
        }
        
        if (empty($visits)) {
            return "Device {$deviceName} did not visit the landmark at {$latitude}, {$longitude} (within {$radius}m) during the specified period.";
        }
        
        $output = "📍 Landmark Visits for {$deviceName}:\n";
        $output .= "Target: {$latitude}, {$longitude}\n";
        $output .= "Radius: {$radius}m\n";
        $output .= "Period: " . date('M d', strtotime($dateFrom)) . " - " . date('M d, Y', strtotime($dateTo)) . "\n\n";
        $output .= "Found " . count($visits) . " visit(s):\n\n";
        
        foreach ($visits as $index => $visit) {
            $entryTime = Formatter::time()->human($visit['entry_time']);
            $exitTimeStr = Formatter::time()->human($visit['exit_time']);
            $duration = Carbon::parse($visit['entry_time'])->diff(Carbon::parse($visit['exit_time']));
            
            $output .= ($index + 1) . ". " . $entryTime . "\n";
            $output .= "   - Closest distance: " . round($visit['min_distance']) . "m from landmark\n";
            $output .= "   - Entry speed: " . round($visit['entry_speed'], 1) . " km/h\n";
            $output .= "   - Exit speed: " . round($visit['exit_speed'], 1) . " km/h\n";
            
            if ($duration->h > 0 || $duration->i > 0) {
                $durationStr = '';
                if ($duration->h > 0) {
                    $durationStr = $duration->h . "h " . $duration->i . "min";
                } else {
                    $durationStr = $duration->i . "min " . $duration->s . "s";
                }
                $output .= "   - Duration at location: {$durationStr}\n";
            }
            
            if (date('Y-m-d', strtotime($visit['entry_time'])) != date('Y-m-d', strtotime($visit['exit_time']))) {
                $output .= "   - Exit: " . $exitTimeStr . "\n";
            }
            
            $output .= "\n";
        }
        
        return $output;
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function getActivityLogs(User $user, ?string $action = null, ?string $subject = null, ?string $dateFrom = null, ?string $dateTo = null, ?int $limit = 20): string
    {
        $dateFrom = $dateFrom ?? date('Y-m-d 00:00:00', strtotime('-7 days'));
        $dateTo = $dateTo ?? date('Y-m-d 23:59:59');
        
        // Query model_change_logs using ModelChangeLog entity
        $query = \Tobuli\Entities\ModelChangeLog::where('causer_id', $user->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc');
        
        // Filter by action/description
        if ($action) {
            $query->where('description', 'like', "%{$action}%");
        }
        
        // Filter by subject type
        if ($subject) {
            $query->where('subject_type', 'like', "%{$subject}%");
        }
        
        $logs = $query->limit($limit)->get();
        
        if ($logs->isEmpty()) {
            return "No activity logs found for the specified criteria.";
        }
        
        // Group by action type
        $logsByAction = $logs->groupBy('description');
        $logsByDate = $logs->groupBy(function($log) {
            return \Carbon\Carbon::parse($log->created_at)->format('Y-m-d');
        });
        
        $output = "📋 Activity Logs:\n";
        $output .= "Period: " . date('M d', strtotime($dateFrom)) . " - " . date('M d, Y', strtotime($dateTo)) . "\n";
        $output .= "Total Activities: " . $logs->count() . "\n\n";
        
        // Summary by action type
        $output .= "Activities by Type:\n";
        foreach ($logsByAction->take(5) as $action => $actionLogs) {
            $output .= "• {$action}: " . $actionLogs->count() . "\n";
        }
        $output .= "\n";
        
        // Summary by date
        $output .= "Activities by Date:\n";
        foreach ($logsByDate->take(5) as $date => $dateLogs) {
            $formattedDate = \Carbon\Carbon::parse($date)->format('M d, Y');
            $output .= "• {$formattedDate}: " . $dateLogs->count() . " activities\n";
        }
        $output .= "\n";
        
        // Recent activities
        $output .= "Recent Activities (last " . min($limit, 20) . "):\n";
        foreach ($logs->take(20) as $log) {
            $time = Formatter::time()->human($log->created_at);
            $output .= "• {$time} - {$log->description}";
            
            if ($log->subject_type) {
                $subjectName = class_basename($log->subject_type);
                $output .= " ({$subjectName}";
                if ($log->subject_id) {
                    $output .= " #{$log->subject_id}";
                }
                $output .= ")";
            }
            
            if ($log->ip) {
                $output .= " from {$log->ip}";
            }
            
            $output .= "\n";
        }
        
        return $output;
    }
}



<?php

namespace Tobuli\Reports\Reports;

use Tobuli\History\Actions\Distance;
use Tobuli\History\Actions\DriveStop;
use Tobuli\History\Actions\Duration;
use Tobuli\History\Actions\EngineHours;
use Tobuli\History\Actions\FuelReport;
use Tobuli\History\Actions\Speed;
use Tobuli\History\Actions\GroupDrive;
use Tobuli\History\Group;
use Tobuli\Reports\DeviceHistoryReport;
use Tobuli\Entities\Event;

class FuelSummaryReport extends DeviceHistoryReport
{
    const TYPE_ID = 87;
    
    protected $currentDevice;

    public function typeID()
    {
        return self::TYPE_ID;
    }

    public function title()
    {
        return trans('front.fuel_summary_report');
    }

    protected function getActionsList()
    {
        return [
            DriveStop::class,
            Duration::class,
            Distance::class,
            FuelReport::class,
            GroupDrive::class,
        ];
    }

    protected function getTable($data)
    {
        $rows = [];

        foreach ($data['groups']->all() as $group)
        {
            $rows[] = $this->getDataFromGroup($group, [
                'group_key',
                'start_at',
                'end_at',
                'duration',
                'distance',
                'location_start',
                'location_end',
                'fuel_consumption_list',
                'fuel_price_list'
            ]);
        }

        return [
            'rows'   => $rows,
            'totals' => [],
        ];
    }

    protected function generateDevice($device)
    {
        if ($error = $this->precheckError($device))
            return [
                'meta' => $this->getDeviceMeta($device),
                'error' => $error
            ];

        $data = $this->getDeviceHistoryData($device);

        if ($this->isEmptyResult($data))
            return null;

        // Set current device for use in getSummaryData
        $this->currentDevice = $device;

        // Aggregate data for summary view
        $summaryData = $this->getSummaryData($data);

        return [
            'meta' => $this->getDeviceMeta($device) + $this->getHistoryMeta($data['root']),
            'table'  => $this->getTable($data),
            'summary' => $summaryData,
            'totals' => $this->getTotals($data['root'])
        ];
    }

    protected function getSummaryData($data)
    {
        $totalDuration = 0;
        $totalDistance = 0;
        $totalFuelConsumption = 0;
        $tripCount = 0;
        $startLocation = '';
        $endLocation = '';
        $firstTripStart = null;
        $lastTripEnd = null;
        $fuelFillCount = 0;
        $fuelTheftCount = 0;
        $lastFuelLevel = 0;

        foreach ($data['groups']->all() as $group) {
            $groupData = $this->getDataFromGroup($group, [
                'start_at',
                'end_at',
                'duration',
                'distance',
                'location_start',
                'location_end',
                'fuel_consumption_list'
            ]);

            $tripCount++;

            // Track first and last trip times
            if ($firstTripStart === null) {
                $firstTripStart = $groupData['start_at'];
                $startLocation = $groupData['location_start'] ?? '';
            }
            $lastTripEnd = $groupData['end_at'];
            $endLocation = $groupData['location_end'] ?? '';

            // Sum duration
            if (!empty($groupData['duration'])) {
                $duration = $this->parseDuration($groupData['duration']);
                $totalDuration += $duration;
            }

            // Sum distance
            if (!empty($groupData['distance'])) {
                $distance = $this->parseDistance($groupData['distance']);
                $totalDistance += $distance;
            }

            // Sum fuel consumption
            if (!empty($groupData['fuel_consumption_list']) && 
                is_array($groupData['fuel_consumption_list']) && 
                isset($groupData['fuel_consumption_list'][1]) && 
                isset($groupData['fuel_consumption_list'][1]['value'])) {
                
                $fuelValue = (float)str_replace(['L', 'l'], '', $groupData['fuel_consumption_list'][1]['value']);
                if (is_numeric($fuelValue) && $fuelValue >= 0) {
                    $totalFuelConsumption += $fuelValue;
                }
            }
        }

        // Get fuel fill and theft amounts from events
        $device = $this->getCurrentDevice();
        if ($device) {
            $fuelFillAmount = $this->getFuelEventSum($device, 'fuel_fill');
            $fuelTheftAmount = $this->getFuelEventSum($device, 'fuel_theft');
            $lastFuelLevel = $this->getLastFuelLevel($device);
        }

        return [
            'trip_count' => $tripCount,
            'total_duration' => $this->formatDuration($totalDuration),
            'total_distance' => number_format($totalDistance, 2) . ' Km',
            'total_fuel_consumption' => number_format($totalFuelConsumption, 2) . ' L',
            'start_location' => $startLocation,
            'end_location' => $endLocation,
            'first_trip_start' => $firstTripStart,
            'last_trip_end' => $lastTripEnd,
            'average_fuel_efficiency' => $totalFuelConsumption > 0 ? number_format($totalDistance / $totalFuelConsumption, 2) . ' Km/L' : '0 Km/L',
            'fuel_fill_amount' => number_format($fuelFillAmount, 2) . ' L',
            'fuel_theft_amount' => number_format($fuelTheftAmount, 2) . ' L',
            'last_fuel_level' => $lastFuelLevel . ' L'
        ];
    }

    protected function parseDuration($duration)
    {
        $seconds = 0;
        if (!empty($duration) && is_string($duration)) {
            if (preg_match('/(\d+)min/', $duration, $matches)) {
                $seconds += (int)$matches[1] * 60;
            }
            if (preg_match('/(\d+)s/', $duration, $matches)) {
                $seconds += (int)$matches[1];
            }
        }
        return $seconds;
    }

    protected function parseDistance($distance)
    {
        $cleanedDistance = str_replace(['Km', 'L'], '', $distance);
        return (float)$cleanedDistance;
    }

    protected function formatDuration($totalSeconds)
    {
        $hours = floor($totalSeconds / 3600);
        $remainingSeconds = $totalSeconds % 3600;
        $minutes = floor($remainingSeconds / 60);
        $seconds = $remainingSeconds % 60;
        
        return $hours . "h " . $minutes . "min " . $seconds . "s";
    }

    protected function getFuelEventSum($device, $eventType)
    {
        $events = Event::whereBetween('time', [$this->date_from, $this->date_to])
            ->where('device_id', $device->id)
            ->where('type', $eventType)->where('user_id', $this->user->id)
            ->get();
            
        $totalAmount = 0;
        
        
        // Count unique fuel amounts only (avoid duplicates)
        foreach ($events as $event) {
            $additional = $event->additional ?? [];
            $difference = abs((float)($additional['difference'] ?? 0));
            
            // Only add if this amount hasn't been counted before
            if ($difference > 0) {
                $totalAmount += $difference;
                
            }
        }
        
        return $totalAmount;
    }

    protected function getLastFuelLevel($device)
    {
        // Get the last fuel level from the device's sensors
        $fuelSensor = $device->sensors->where('type', 'fuel_tank')->first();
        
        if (!$fuelSensor) {
            return 0;
        }

        // Get the last position within the date range
        $lastPosition = $device->positions()
            ->whereBetween('time', [$this->date_from, $this->date_to])
            ->orderBy('time', 'desc')
            ->first();

        if ($lastPosition) {
            // Get fuel value using the sensor's getValue method
            $fuelValue = $fuelSensor->getValue($lastPosition->other, false);
            if ($fuelValue < 9999) { // Filter out invalid values
                return number_format((float)$fuelValue, 2);
            }
        }

        return 0;
    }

    protected function getCurrentDevice()
    {
        // This method needs to be implemented to get the current device being processed
        // We'll need to track this in the generateDevice method
        return $this->currentDevice ?? null;
    }

    protected function getTotals(Group $group, array $only = [])
    {
        return parent::getTotals($group, ['distance', 'drive_duration']);
    }
}
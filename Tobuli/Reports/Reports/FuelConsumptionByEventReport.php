<?php

namespace Tobuli\Reports\Reports;

use CustomFacades\Repositories\EventRepo;
use DateTime;
use Formatter;
use Tobuli\Reports\DeviceReport;

class FuelConsumptionByEventReport extends DeviceReport
{
    protected $offline_timeout;

    const TYPE_ID = 89;

    public function typeID()
    {
        return self::TYPE_ID;
    }

    public function title()
    {
        return trans('front.fuel_consumption_by_events');
    }

    protected function generate()
    {
        foreach ($this->devices as $device)
        {
            $this->items[] = $this->generateDevice($device);
        }
    }

    protected function generateDevice($device)
    {
        $events = EventRepo::getBetweenOnlyONOFF($this->user->id, $device->id, $this->date_from, $this->date_to);

        if (empty($events))
            return [
                'meta' => $this->getDeviceMeta($device),
                'error' => trans('front.nothing_found_request')
            ];

        $totals = [];
        if (isset($events[0]))
        $preTime = Formatter::time()->human($events[0]['time']);
        else 
        $preTime = $this->date_from;
        
        $totalRunningSeconds = 0;
        $fuelTotal = 0;
        foreach ($events as & $event) {
            $time = Formatter::time()->human($event['time']);
            $fuelstart = fuel_tank_value($event['device_id'],$preTime);
            $fuelend = fuel_tank_value($event['device_id'],$time);
            $fuelconsum = $fuelstart-$fuelend;
            if($fuelconsum < 0) {
               $emplty = 1;
            } else {
                $event['fuelstart'] = $fuelstart;
                $event['fuelend'] = $fuelend;
                $event['fuelconsum'] = $fuelconsum;
                $fuelTotal += (int) $fuelconsum;
                $eventTime = new DateTime($time);
                $previousTime = new DateTime($preTime);
                // Calculate running hours
                 $interval = $eventTime->diff($previousTime);
                $event['runninghours'] = $interval->format('%H:%I:%S');
                if($event['message']=='ignition Off')
                $totalRunningSeconds += ($interval->h * 3600) + ($interval->i * 60) + $interval->s;

    
            }
            
                $event['time'] = $time;
                $event['location'] = $this->getLocation((object)[
                    'latitude' => $event['latitude'],
                    'longitude' => $event['longitude']
                ]);

                if (empty($totals[$event['message']]))
                    $totals[$event['message']] = [
                        'title' => trans('front.total') . ' ' . $event['message'],
                        'value' => 0,
                    ];
                    
            $preTime = $time;
            $totals[$event['message']]['value']++;
        }
        $totals['Total Fuel Consumption']=[
                    'title'=>'Total Fuel Consumption',
                    'value'=>$fuelTotal,
                    ];
        $totalRunningHours = sprintf('%02d:%02d:%02d', 
        floor($totalRunningSeconds / 3600),
        floor(($totalRunningSeconds % 3600) / 60),
        $totalRunningSeconds % 60);
        $totals['Running Hours'] = [
        'title' => 'Running Hours',
        'value' => $totalRunningHours
        ];
        list($hours, $minutes, $seconds) = explode(":", $totalRunningHours);
        $totalRunningSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        $totalRunningHours = $totalRunningSeconds / 3600;
        $averageFuelConsumption = $fuelTotal / $totalRunningHours;
        $totals['Average Fuel Consumption'] = [
        'title' => 'Average Fuel Consumption',
        'value' => number_format($averageFuelConsumption, 2)
        ];
        
        return [
            'meta' => $this->getDeviceMeta($device),
            'table' => [
                'rows' => $events
            ],
            'totals' => $totals
        ];
    }
}
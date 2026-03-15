<?php

namespace Tobuli\Reports\Reports;

use Formatter;
use Illuminate\Support\Arr;
use Tobuli\Entities\Event;
use Tobuli\Reports\DeviceReport;

class FuelTheftsReport extends DeviceReport
{
    const TYPE_ID = 12;

    protected $disableFields = ['geofences', 'speed_limit', 'stops'];

    public function __construct()
    {
        parent::__construct();

        $this->formats[] = 'csv';
    }

    public function getInputParameters(): array
    {
        $inputParameters = [];

        if ($this->user->isManager()) {
            $inputParameters[] = \Field::select('subusers', trans('front.subusers'), 0)
                ->setOptions([0 => trans('global.no'), 1 => trans('global.yes')])
                ->setValidation('in:0,1');
        }

        return $inputParameters;
    }

    public function typeID()
    {
        return self::TYPE_ID;
    }

    public function title()
    {
        return trans('front.fuel_thefts');
    }

    protected function beforeGenerate()
    {
        parent::beforeGenerate();

        if (!$this->getSkipBlankResults())
            return;

        $query = $this->getDevicesQuery()->whereHas('events', function($q){
            $q->whereBetween('time', [$this->date_from, $this->date_to])
              ->where('type', Event::TYPE_FUEL_THEFT);

            $subusers = $this->parameters['subusers'] ?? false;

            if ($subusers && $this->user->isManager()) {
                $q->whereIn('user_id', function ($q) {
                    $q->select('users.id')
                        ->from('users')
                        ->where('users.id', $this->user->id)
                        ->orWhere('users.manager_id', $this->user->id);
                });
            } else {
                $q->where('user_id', $this->user->id);
            }
        });

        $this->setDevicesQuery($query);
    }

    protected function generateDevice($device)
    {
        $query = Event::with(['geofence'])
            ->whereBetween('time', [$this->date_from, $this->date_to])
            ->where('device_id', $device->id)
            ->where('type', Event::TYPE_FUEL_THEFT)
            ->where(function($query){
                $subusers = $this->parameters['subusers'] ?? false;

                if ($subusers && $this->user->isManager()) {
                    $query->userControllable($this->user);
                } else {
                    $query->userAccessible($this->user);
                }
            })
            ->orderBy('time', 'asc');

        $events = $query->get();

        if ($events->isEmpty())
            return null;

        $rows = [];

        foreach ($events as $event) {
            $additional = $event->additional ?? [];
            $difference = $additional['difference'] ?? 0;
            $sensorId = $additional['sensor_id'] ?? null;
            
            // Get sensor name if available
            $sensorName = 'Unknown';
            if ($sensorId && $device->sensors) {
                $sensor = $device->sensors->where('id', $sensorId)->first();
                if ($sensor) {
                    $sensorName = $sensor->name;
                }
            }

            // Get actual fuel levels from the device's sensor
            // The current fuel level is the sensor's current value
            // The previous level is calculated by adding the theft amount back
            $previousLevel = 0;
            $currentLevel = 0;
            
            if ($sensorId && $device->sensors) {
                $sensor = $device->sensors->where('id', $sensorId)->first();
                if ($sensor) {
                    // Get the current fuel level from the sensor (this represents the fuel level after theft)
                    $currentLevel = $sensor->value ?? 0;
                    // Calculate previous level by adding the theft amount back (fuel level before theft)
                    $previousLevel = $currentLevel + abs($difference);
                }
            }

            $rows[] = [
                'start_at' => Formatter::time()->human($event->time),
                'location' => $this->getLocation((object)[
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude
                ]),
                'fuel_level_previous' => Formatter::capacity()->human($previousLevel),
                'fuel_level_current' => Formatter::capacity()->human($currentLevel),
                'fuel_level_difference' => Formatter::capacity()->human(abs($difference)),
                'sensor_name' => $sensorName,
                'message' => $event->message,
                'detail' => $event->detail,
            ];
        }

        return [
            'meta' => $this->getDeviceMeta($device),
            'table' => [
                'rows' => $rows
            ],
        ];
    }

    protected function toCSVData($file)
    {
        foreach ($this->getItems() as $item) {
            $metas = Arr::pluck($item['meta'], 'value');

            if (empty($item['table']['rows']))
                continue;

            foreach ($item['table']['rows'] as $row) {
                $values = $metas;
                $values[] = $row['start_at'];
                $values[] = $row['fuel_level_previous'];
                $values[] = $row['fuel_level_current'];
                $values[] = $row['fuel_level_difference'];
                $values[] = $row['sensor_name'];
                $values[] = strip_tags($row['location']);

                fputcsv($file, $values);
            }
        }
    }

    public static function isEnabled(): bool
    {
        $user = getActingUser();

        return !$user || $user->perm('events', 'view');
    }
}
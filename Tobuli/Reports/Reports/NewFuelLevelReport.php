<?php

namespace Tobuli\Reports\Reports;


use Tobuli\History\Actions\Distance;
use Tobuli\History\Actions\Drivers;
use Tobuli\History\Actions\DriveStop;
use Tobuli\History\Actions\Duration;
use Tobuli\History\Actions\EngineHours;
use Tobuli\History\Actions\Fuel;
use Tobuli\History\Actions\GeofencesIn;
use Tobuli\History\Actions\GroupDrive;
use Tobuli\History\Actions\OdometersDiff;
use Tobuli\History\Actions\AppendFuelTanks;
use Tobuli\History\Actions\AppendFuelConsumptionLevelSensors;
use Tobuli\History\Actions\FuelReport;
use Tobuli\History\Group;
use Tobuli\Reports\DeviceHistoryReport;

class NewFuelLevelReport extends DeviceHistoryReport
{
    const TYPE_ID = 91;

    public function typeID()
    {
        return self::TYPE_ID;
    }

    public function title()
    {
        return trans('front.fuel_level_trip_report');
    }

    protected function getActionsList()
    {
        $list = [
            DriveStop::class,
            Duration::class,
            Distance::class,
            FuelReport::class,
            EngineHours::class,
            GroupDrive::class,
            AppendFuelTanks::class,
            AppendFuelConsumptionLevelSensors::class,
        ];

        return $list;
    }

    protected function getTable($data)
    {
        $rows = [];

        foreach ($data['groups']->all() as $group)
        {
            $rows[] = $this->getDataFromGroup($group, [
                'group_key',
                'status',
                'start_at',
                'end_at',
                'duration',
                'distance',
                'location_start',
                'location_end',
                'fuel_tank',
                'fuel_level_start_list',
                'fuel_level_end_list',
                'fuel_consumption_list'
            ]);
        }

        return [
            'rows'   => $rows,
            'totals' => [],
        ];
    }

    protected function getTotals(Group $group, array $only = [])
    {
        $totals = parent::getTotals($group, ['drive_distance', 'drive_duration', 'fuel_consumption']);
        $totals['distance'] = $totals['drive_distance'];

        return $totals;
    }
}
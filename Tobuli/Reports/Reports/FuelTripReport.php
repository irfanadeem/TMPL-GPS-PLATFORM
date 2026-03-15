<?php

namespace Tobuli\Reports\Reports;

use Tobuli\History\Actions\Distance;
use Tobuli\History\Actions\Drivers;
use Tobuli\History\Actions\DriveStop;
use Tobuli\History\Actions\Duration;
use Tobuli\History\Actions\EngineHours;
use Tobuli\History\Actions\Fuel;
use Tobuli\History\Actions\FuelReport;
use Tobuli\History\Actions\GeofencesIn;
use Tobuli\History\Actions\GroupDrive;
use Tobuli\History\Actions\GroupDriveStop;
use Tobuli\History\Actions\OdometersDiff;
use Tobuli\History\Actions\Speed;
use Tobuli\History\Group;
use Tobuli\Reports\DeviceHistoryReport;
use Tobuli\Reports\DeviceSensorDataReport;

class FuelTripReport extends DeviceHistoryReport
{
    const TYPE_ID = 88;

    public function typeID()
    {
        return self::TYPE_ID;
    }

    public function title()
    {
        return trans('front.fuel_trip_report');
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

    protected function getTotals(Group $group, array $only = [])
    {
        return parent::getTotals($group, ['distance', 'drive_duration']);
    }
}
<?php

namespace Tobuli\History\Actions;

use Formatter;
use Tobuli\History\Stats\StatConsumption;
use Tobuli\History\Stats\StatValue;
use Tobuli\History\Stats\StatValueFirst;

class FuelReport extends ActionStat
{
    protected $fuel_price;

    protected $total_by;

    protected $formatters = [];

    protected $lastFuelLevel = [];

    static public function required()
    {
        return [
            AppendFuelConsumptions::class
        ];
    }

    public function boot()
    {
        $device = $this->getDevice();
        $this->fuel_price = (float)($device->fuel_price) * ($device->fuel_measurement_id == 2 ? 0.264172053 : 1);

        $this->loadConsumptionGPS();
        $this->loadConsumptionHour();
        $this->loadConsumptionLevelSensors();
        $this->loadConsumptionSensors();

        if ($this->total_by) {
            $this->registerStat("fuel_consumption", (new StatConsumption())->setFormatUnit(Formatter::capacity()));

            if ($this->fuel_price) {
                $this->registerStat("fuel_price", (new StatConsumption()));
            }
        }
    }

    public function finalize()
    {
        $this->finalizeTankConsumption();
    }

    public function proccess($position)
    {
        $this->processFuelTank($position);
        $this->processConsumptions($position);
    }

    // Keep these as class properties
    //protected $lastFuelLevel = [];
protected $usedDiffs = [];

protected function processFuelTank($position)
{
    if (empty($position->fuel_tanks))
        return;

    foreach ($position->fuel_tanks as $key => $value)
    {
        // Skip processing if this fuel sensor ID doesn't have proper stats registered
        // This handles cases where fuel sensor IDs exist in data but aren't properly configured
        if (!$this->history->hasStat("fuel_level_start_{$key}") && 
            !$this->history->hasStat("fuel_level_end_{$key}")) {
            continue;
        }

        // Register start and end stats if they exist
        if ($this->history->hasStat("fuel_level_start_{$key}")) {
            $this->history->applyStat("fuel_level_start_{$key}", $value);
        }
        if ($this->history->hasStat("fuel_level_end_{$key}")) {
            $this->history->applyStat("fuel_level_end_{$key}", $value);
        }

        // Register tank consumption stat if not already registered
        if (!$this->history->hasStat("fuel_consumption_tank_{$key}")) {
            $this->registerStat(
                "fuel_consumption_tank_{$key}",
                (new StatConsumption())->setFormatUnit(Formatter::capacity())
            );
        }

        if (isset($this->lastFuelLevel[$key])) {
            $prev = $this->lastFuelLevel[$key];
            $curr = $value;

            // --- Detect refill (level increase > 10)
            if (($curr - $prev) > 5) {
                // Reset duplicate tracker for this tank
                $this->usedDiffs[$key] = [];
            }

            // --- Calculate consumption
            $diff = $prev - $curr;

            // Rule 1 & 2: Validate difference
            if ($diff < 0 || $diff > 1) {
                $diff = 0;
            }

            // Rule 3: Prevent duplicate differences (per tank)
            $hash = "{$prev}-{$curr}";
            if (isset($this->usedDiffs[$key][$hash])) {
                $diff = 0;
            }

            // Save valid consumption
            if ($diff >= 0) {
                $this->usedDiffs[$key][$hash] = true;

                // Only apply fuel consumption stat if it exists
                if ($this->history->hasStat("fuel_consumption_tank_{$key}") && $diff > 0) {
                    $this->history->applyStat("fuel_consumption_tank_{$key}", $diff);
                }

                if ($this->fuel_price) {
                    if (!$this->history->hasStat("fuel_price_tank_{$key}")) {
                        $this->registerStat("fuel_price_tank_{$key}", new StatConsumption());
                    }
                    if ($this->history->hasStat("fuel_price_tank_{$key}")) {
                        $this->history->applyStat("fuel_price_tank_{$key}", $diff * $this->fuel_price);
                    }
                }
            }
        }

        // Save current value for next comparison
        $this->lastFuelLevel[$key] = $value;
    }
}


    protected function processConsumptions($position)
    {
        if ( ! $position->consumptions)
            return;

        $consumption = null;

        foreach ($position->consumptions as $key => $value)
        {
            if ($formatter = $this->formatters[$key] ?? null) {
                $value = $formatter->reverse($value);
            }

            if ($this->history->hasStat("fuel_consumption_{$key}")) {
                $this->history->applyStat("fuel_consumption_{$key}", $value);
            }

            if ($this->fuel_price && $this->history->hasStat("fuel_price_{$key}")) {
                $this->history->applyStat("fuel_price_{$key}", $value * $this->fuel_price);
            }

            if (in_array($key, $this->total_by)) {
                $consumption += $value;
            }
        }

        if ( ! is_null($consumption)) {
            if ($this->history->hasStat("fuel_consumption")) {
                $this->history->applyStat("fuel_consumption", $consumption);
            }

            if ($this->fuel_price && $this->history->hasStat("fuel_price")) {
                $this->history->applyStat("fuel_price", $consumption * $this->fuel_price);
            }
        }
    }

    protected function loadConsumptionGPS()
    {
        if ($this->getDevice()->fuel_per_km <= 0)
            return;

        $this->total_by = ['gps'];

        $this->registerFuelConsumptionStat('GPS', 'gps');
        $this->registerFuelPriceStat('GPS', 'gps');
    }

    protected function loadConsumptionHour()
    {
        $device = $this->getDevice();

        if (!$device->fuel_per_h || $device->fuel_per_h <= 0 || $device->fuel_measurement_id != 4) {
            return;
        }

        $this->total_by = ['hour'];

        $this->registerFuelConsumptionStat('Hour', 'hour');
        $this->registerFuelPriceStat('Hours', 'hour');
    }

    protected function loadConsumptionSensors()
    {
        $sensors = $this->getDevice()->sensors->filter(function($sensor) {
            return in_array($sensor->type, ['fuel_consumption']);
        });

        if ($sensors->isEmpty())
            return;

        $this->total_by = [];

        foreach ($sensors as $sensor)
        {
            $this->total_by[] = $sensor->id;

            $name = $sensor->formatName();
            $formatter = (clone Formatter::capacity())->setUnit($sensor->unit_of_measurement);
            $this->formatters[$sensor->id] = $formatter;

            $this->registerFuelConsumptionStat($name, $sensor->id, $formatter);
            $this->registerFuelPriceStat($name, $sensor->id);
        }
    }

    protected function loadConsumptionLevelSensors()
    {
        $sensors = $this->getDevice()->sensors->filter(function($sensor) {
            return in_array($sensor->type, ['fuel_tank']);
        });

        if ($sensors->isEmpty())
            return;

        $this->total_by = [];

        foreach ($sensors as $sensor)
        {
            $this->total_by[] = $sensor->id;

            $name = $sensor->formatName();
            $formatter = (clone Formatter::capacity())->setUnit($sensor->unit_of_measurement);
            $this->formatters[$sensor->id] = $formatter;

            $this->registerFuelConsumptionStat($name, $sensor->id, $formatter);
            $this->registerFuelPriceStat($name, $sensor->id);


            $stat = (new StatValueFirst())->setFormatUnit($formatter);
            $stat->setName($name);
            $this->registerStat("fuel_level_start_{$sensor->id}", $stat);

            $stat = (new StatValue())->setFormatUnit($formatter);
            $stat->setName($name);
            $this->registerStat("fuel_level_end_{$sensor->id}", $stat);
        }
    }

    protected function registerFuelPriceStat($name, $key)
    {
        if (!$this->fuel_price) {
            return;
        }

        $stat = new StatConsumption();
        $stat->setName($name);
        $this->registerStat("fuel_price_{$key}", $stat);
    }

    protected function registerFuelConsumptionStat($name, $key, $formatter = null)
    {
        $stat = (new StatConsumption())->setFormatUnit($formatter ?? Formatter::capacity());
        $stat->setName($name);

        $this->registerStat("fuel_consumption_{$key}", $stat);
    }

    protected function finalizeTankConsumption()
    {
        $total = 0;

        foreach ($this->lastFuelLevel as $key => $level) {
            $statKey = "fuel_consumption_tank_{$key}";
            if ($this->history->hasStat($statKey)) {
                try {
                    $tankStat = $this->history->root->stats()->get($statKey);
                    if ($tankStat && $tankStat->value() >= 0) {
                        $total += $tankStat->value();
                    }
                } catch (\Exception $e) {
                    // Skip this tank if stat doesn't exist or can't be accessed
                    continue;
                }
            }
        }

        if ($total > 0) {
            if ($this->history->hasStat("fuel_consumption")) {
                $this->history->applyStat("fuel_consumption", $total);
            }

            if ($this->fuel_price && $this->history->hasStat("fuel_price")) {
                $this->history->applyStat("fuel_price", $total * $this->fuel_price);
            }
        }
    }
}
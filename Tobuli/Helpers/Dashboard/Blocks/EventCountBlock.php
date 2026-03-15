<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\CronJobCalculation;
use Tobuli\Helpers\Dashboard\Traits\HasPeriodOption;

class EventCountBlock extends Block
{
    use HasPeriodOption;

    protected function getName()
    {
        return 'event_count';
    }

    protected function getContent()
    {
        $option = $this->getConfig("options");
        $alerts = $this->getUserEventsWithCounts();
        $colorCodes = $this->getColorCodes();

        $alertsWithColors = $this->assignColorsToEvents($alerts, $colorCodes);

        return ['events' => $alertsWithColors, 'options' => $option];
    }

    private function getUserEventsWithCounts()
    {
        $option = $this->getConfig("options");
        // If no valid cache, fetch fresh data from the database
        $event_count_query = $this->user->events()
            ->selectRaw('events.alert_id, COUNT(DISTINCT events.id) as count')
            ->whereBetween(DB::raw('DATE(events.time)'), [$option['from_date'], $option['to_date']])
            ->groupBy('events.alert_id');
        if (!empty($option['department'])) {
            $event_count_query->leftJoin('user_device_pivot', 'user_device_pivot.device_id', '=', 'events.device_id')
                ->join('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
                ->where('device_groups.id', $option['department']);
        }

        // Main query to ensure all alerts are included
        $event_query = $this->user->alerts()
            ->leftJoinSub($event_count_query, 'event_counts', function ($join) {
                $join->on('alerts.id', '=', 'event_counts.alert_id');
            })
            ->select('alerts.name as message', 'alerts.id as alert_id', DB::raw('COALESCE(event_counts.count, 0) as count'));

        return $event_query->get();

    }

    private function getColorCodes()
    {
        return [
            "#d557087a",
            "#0d90bb40",
            "#40bb0d40",
            "#8a000066",
            "#e6ca0061",
            "#f51212b8",
            "rgba(18, 181, 245, 0.72)"
        ];
    }

    private function assignColorsToEvents($events, $colorCodes)
    {
        return $events->map(function ($event, $index) use ($colorCodes) {
            $event->color = $colorCodes[$index % count($colorCodes)];
            return $event;
        });
    }
}
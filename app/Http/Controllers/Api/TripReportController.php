<?php namespace App\Http\Controllers\Api;

use Illuminate\Support\Arr;
use Tobuli\Reports\ReportManager;
use Formatter;
use Validator;
use Tobuli\Entities\Device;
use Tobuli\Services\FractalTransformerService;

class TripReportController extends ApiController
{
    private $reportManager;

    public function __construct(FractalTransformerService $transformerService)
    {
        parent::__construct($transformerService);
        $this->reportManager = new ReportManager();
    }

    public function index()
    {
        $validator = Validator::make(request()->all(), [
            'device_ids' => 'required',
            'from_date'  => 'required|date',
            'to_date'    => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errors' => $validator->errors()], 422);
        }

        $this->reportManager->setUser($this->user);
        Formatter::byUser($this->user);

        $deviceIds = request('device_ids');
        $query = $this->user->devices();

        if ($deviceIds !== '*') {
            $deviceIds = is_string($deviceIds) ? explode(',', $deviceIds) : $deviceIds;
            $query->whereIn('devices.id', (array)$deviceIds);
        }

        $devicesQuery = $query;
        $allDeviceIds = (clone $devicesQuery)->pluck('devices.id')->all();

        if (empty($allDeviceIds)) {
            return response()->json(['status' => 0, 'message' => trans('front.nothing_found_request')], 404);
        }

        $from_date = request('from_date');
        $to_date = request('to_date');

        // If only date is provided, append time to cover the whole day
        if (strlen($from_date) == 10) $from_date .= ' 00:00:00';
        if (strlen($to_date) == 10) $to_date .= ' 23:59:59';

        $data = [
            'type'      => 1, // General Information Report
            'format'    => 'json',
            'date_from' => Formatter::time()->reverse($from_date),
            'date_to'   => Formatter::time()->reverse($to_date),
            'devices'   => $devicesQuery,
            'generate'  => 1,
            'view'      => 1,
            'user'      => $this->user
        ];

        try {
            $report = $this->reportManager->from($data);
            $result = $report->view();
            
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                $items = $decoded['items'] ?? [];
                
                $summaryList = [];
                foreach ($items as $item) {
                    $meta = $item['meta'] ?? [];
                    $totals = $item['totals'] ?? [];
                    
                    $summaryList[] = [
                        'device_id'       => $meta['device.id']['value'] ?? null,
                        'device_name'     => $meta['device.name']['value'] ?? '',
                        'total_distance'  => $totals['distance']['value'] ?? '0 Km',
                        'engine_hours'    => $totals['engine_hours']['value'] ?? '0s',
                        'idle_hours'      => $totals['engine_idle']['value'] ?? '0s',
                        'moving_duration' => $totals['drive_duration']['value'] ?? '0s',
                        'stop_duration'   => $totals['stop_duration']['value'] ?? '0s',
                        'start_time'      => $totals['start']['value'] ?? '-',
                        'end_time'        => $totals['end']['value'] ?? '-',
                    ];
                }

                // Sort by distance (optional but helpful for "top 5")
                usort($summaryList, function($a, $b) {
                    $distA = (float)str_replace([' Km', ' mi', ','], '', $a['total_distance']);
                    $distB = (float)str_replace([' Km', ' mi', ','], '', $b['total_distance']);
                    return $distB <=> $distA;
                });

                // Top 5
                $summaryList = array_slice($summaryList, 0, 5);

                return response()->json([
                    'status' => 1,
                    'data'   => $summaryList
                ]);
            }

            return response()->json(['status' => 1, 'data' => []]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

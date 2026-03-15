<?php namespace App\Http\Controllers\Api;

use Illuminate\Support\Arr;
use Tobuli\Reports\ReportManager;
use Formatter;
use Validator;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Services\FractalTransformerService;

class FuelReportController extends ApiController
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
            'device_id' => 'required',
            'type'      => 'required|in:fillings,thefts,summary,trip',
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errors' => $validator->errors()], 422);
        }

        $this->reportManager->setUser($this->user);
        Formatter::byUser($this->user);

        $typeMap = [
            'fillings' => 11, // FuelFillingsReport
            'thefts'   => 12, // FuelTheftsReport
            'summary'  => 87, // FuelSummaryReport
            'trip'     => 91, // NewFuelLevelReport
        ];

        $type = request('type');
        $typeId = $typeMap[$type];
        
        $deviceIds = request('device_id');
        $query = $this->user->devices();

        if ($deviceIds !== '*') {
            $deviceIds = is_string($deviceIds) ? explode(',', $deviceIds) : $deviceIds;
            $query->whereIn('devices.id', (array)$deviceIds);
        }

        // Only include devices with fuel sensors
        $query->whereHas('sensors', function($q) {
            $q->whereIn('type', ['fuel_tank', 'fuel_tank_calibration', 'fuel_consumption']);
        });

        if (!$query->exists()) {
            return response()->json(['status' => 0, 'message' => trans('front.nothing_found_request')], 404);
        }

        $from_date = request('from_date');
        $to_date = request('to_date');

        if (!$from_date) $from_date = date('Y-m-d H:i:s', strtotime('-1 day'));
        if (!$to_date) $to_date = date('Y-m-d H:i:s');

        // If only date is provided, append time to cover the whole day
        if (strlen($from_date) == 10) $from_date .= ' 00:00:00';
        if (strlen($to_date) == 10) $to_date .= ' 23:59:59';

        $data = [
            'type'      => $typeId,
            'format'    => 'json',
            'date_from' => Formatter::time()->reverse($from_date),
            'date_to'   => Formatter::time()->reverse($to_date),
            'devices'   => $query,
            'generate'  => 1,
            'view'      => 1,
            'user'      => $this->user
        ];

        try {
            $report = $this->reportManager->from($data);
            $result = $report->view();
            
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                
                if ($type === 'summary' && isset($decoded['items'])) {
                    $summaryItems = [];
                    foreach ($decoded['items'] as $item) {
                        $meta = $item['meta'] ?? [];
                        $summary = $item['summary'] ?? [];
                        
                        $summaryItems[] = [
                            'Asset'                  => $meta['device.name']['value'] ?? '',
                            'Start Date & Time'      => $summary['first_trip_start'] ?? '',
                            'End Date & Time'        => $summary['last_trip_end'] ?? '',
                            'Start Location'         => strip_tags($summary['start_location'] ?? ''),
                            'End Location'           => strip_tags($summary['end_location'] ?? ''),
                            'Total Duration'         => $summary['total_duration'] ?? '',
                            'Total Route length'     => $summary['total_distance'] ?? '',
                            'Fuel Fill'              => $summary['fuel_fill_amount'] ?? '',
                            'Fuel Theft'             => $summary['fuel_theft_amount'] ?? '',
                            'Fuel Level'             => $summary['last_fuel_level'] ?? '',
                            'Total Fuel Consumption' => $summary['total_fuel_consumption'] ?? '',
                        ];
                    }
                    return response()->json([
                        'status' => 1,
                        'data'   => $summaryItems
                    ]);
                }

                return response()->json([
                    'status' => 1,
                    'data'   => $decoded
                ]);
            }

            return response()->json([
                'status' => 1,
                'data'   => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

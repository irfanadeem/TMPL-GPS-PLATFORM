<?php

namespace Tobuli\Reports\Reports;

use Tobuli\Reports\DeviceSensorDataReport;
use Tobuli\Helpers\Formatter\Facades\Formatter;

class TemperatureHourlyReport extends DeviceSensorDataReport
{
    const TYPE_ID = 104;

    protected $disableFields = ['geofences', 'speed_limit', 'stops'];
    protected $formats = ['html', 'json', 'xls', 'xlsx', 'pdf', 'pdf_land'];

    public function typeID()
    {
        return self::TYPE_ID;
    }

    public function title()
    {
        return 'Cold Chain Temperature Report (Hourly)';
    }

    protected function getSensorTypes()
    {
        return ['temperature', 'humidity'];
    }

    public function getView()
    {
        // Use simplified Excel view for xls/xlsx formats
        if (in_array($this->format, ['xls', 'xlsx'])) {
            return 'front::Reports.partials.type_104_excel';
        }
        
        return parent::getView();
    }

    protected function getActionsList()
    {
        return [
            \Tobuli\History\Actions\AppendPosition::class,
        ];
    }

    protected function generateDevice($device)
    {
        $data = parent::generateDevice($device);

        if (!is_array($data) || !isset($data['sensors'])) {
            return $data;
        }

        $hourlyData = [];
        $sensorInfo = [];
        $globalSummary = [];

        foreach ($data['sensors'] as $sensor) {
            $sensorId = $sensor['id'];
            $sensorName = $sensor['name'];
            $sensorType = 'other';
            if ($device->getSensorByType('temperature') && $device->getSensorByType('temperature')->id == $sensorId) {
                $sensorType = 'temperature';
            } elseif ($device->getSensorByType('humidity') && $device->getSensorByType('humidity')->id == $sensorId) {
                $sensorType = 'humidity';
            } else {
                 // Fallback: check if name contains Temp or Humidity
                 if (stripos($sensorName, 'temp') !== false) $sensorType = 'temperature';
                 if (stripos($sensorName, 'hum') !== false) $sensorType = 'humidity';
            }

            $sensorInfo[$sensorId] = [
                'name' => $sensorName,
                'unit' => $sensor['unit'],
                'type' => $sensorType
            ];

            // Initialize summary
            $globalSummary[$sensorId] = [
                'min' => null,
                'min_time' => null,
                'max' => null,
                'max_time' => null
            ];

            foreach ($sensor['values'] as $value) {
                $timestamp = $value['t'];
                $val = $value['v'];
                
                // Convert timestamp to datetime string for Formatter
                // The timestamp is a Unix timestamp, but Formatter expects datetime strings
                $datetimeString = date('Y-m-d H:i:s', $timestamp);
                
                // Group by Date and Hour
                // Format: Y-m-d H
                $dateKey = Formatter::date()->convert($datetimeString, 'Y-m-d');
                $hourKey = Formatter::time()->convert($datetimeString, 'H'); // 00-23
                $key = $dateKey . ' ' . $hourKey;
                
                if (!isset($hourlyData[$key])) {
                    $hourlyData[$key] = [
                        'date' => $dateKey,
                        'hour' => $hourKey,
                        'sensors' => []
                    ];
                }

                if (!isset($hourlyData[$key]['sensors'][$sensorId])) {
                     $hourlyData[$key]['sensors'][$sensorId] = [
                        'min' => $val,
                        'min_time' => $timestamp,
                        'max' => $val,
                        'max_time' => $timestamp,
                        'sum' => $val,
                        'count' => 1,
                        'unit' => $sensor['unit']
                     ];
                } else {
                    $current = &$hourlyData[$key]['sensors'][$sensorId];
                    if ($val < $current['min']) {
                        $current['min'] = $val;
                        $current['min_time'] = $timestamp;
                    }
                    if ($val > $current['max']) {
                        $current['max'] = $val;
                        $current['max_time'] = $timestamp;
                    }
                    $current['sum'] += $val;
                    $current['count']++;
                }

                // Global Summary
                if (is_null($globalSummary[$sensorId]['min']) || $val < $globalSummary[$sensorId]['min']) {
                    $globalSummary[$sensorId]['min'] = $val;
                    $globalSummary[$sensorId]['min_time'] = $timestamp;
                }
                if (is_null($globalSummary[$sensorId]['max']) || $val > $globalSummary[$sensorId]['max']) {
                    $globalSummary[$sensorId]['max'] = $val;
                    $globalSummary[$sensorId]['max_time'] = $timestamp;
                }
            }
        }

        // Calculate averages
        foreach ($hourlyData as &$hourItem) {
            foreach ($hourItem['sensors'] as &$sItem) {
                $sItem['avg'] = $sItem['count'] > 0 ? round($sItem['sum'] / $sItem['count'], 2) : 0;
            }
        }
        unset($hourItem); // break reference
        unset($sItem);

        // Sort by time
        ksort($hourlyData);

        // Replace the data structure to return our processed table
        $data['hourly_items'] = $hourlyData;
        $data['sensor_info'] = $sensorInfo;
        $data['global_summary'] = $globalSummary;
        
        // Add table data for Excel/PDF export
        $data['table'] = $this->getTable([
            'hourly_items' => $hourlyData,
            'sensor_info' => $sensorInfo,
            'global_summary' => $globalSummary
        ]);
        
        unset($data['sensors']);

        return $data;
    }

    protected function getTable($data)
    {
        if (!isset($data['hourly_items']) || !isset($data['sensor_info'])) {
            return ['rows' => [], 'totals' => []];
        }

        $rows = [];
        $headers = ['Date', 'Hour'];
        
        // Build headers dynamically based on sensors
        foreach ($data['sensor_info'] as $sensorId => $info) {
            $headers[] = $info['name'] . ' - Average';
            $headers[] = $info['name'] . ' - Min';
            $headers[] = $info['name'] . ' - Min Time';
            $headers[] = $info['name'] . ' - Max';
            $headers[] = $info['name'] . ' - Max Time';
        }

        // Add header row
        $rows[] = $headers;

        // Add data rows
        foreach ($data['hourly_items'] as $key => $hourData) {
            $row = [
                $hourData['date'],
                date('h A', strtotime($hourData['hour'] . ':00'))
            ];

            foreach ($data['sensor_info'] as $sensorId => $info) {
                if (isset($hourData['sensors'][$sensorId])) {
                    $sData = $hourData['sensors'][$sensorId];
                    $row[] = $sData['avg'];
                    $row[] = $sData['min'];
                    $row[] = date('H:i:s', $sData['min_time']);
                    $row[] = $sData['max'];
                    $row[] = date('H:i:s', $sData['max_time']);
                } else {
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                }
            }

            $rows[] = $row;
        }

        // Add summary row
        if (isset($data['global_summary'])) {
            $summaryRow = ['Summary', ''];
            
            foreach ($data['sensor_info'] as $sensorId => $info) {
                if (isset($data['global_summary'][$sensorId])) {
                    $gSum = $data['global_summary'][$sensorId];
                    $summaryRow[] = '-'; // Average
                    $summaryRow[] = $gSum['min'];
                    $summaryRow[] = $gSum['min_time'] ? date('Y-m-d H:i:s', $gSum['min_time']) : '-';
                    $summaryRow[] = $gSum['max'];
                    $summaryRow[] = $gSum['max_time'] ? date('Y-m-d H:i:s', $gSum['max_time']) : '-';
                } else {
                    $summaryRow[] = '-';
                    $summaryRow[] = '-';
                    $summaryRow[] = '-';
                    $summaryRow[] = '-';
                    $summaryRow[] = '-';
                }
            }
            
            $rows[] = $summaryRow;
        }

        return [
            'rows' => $rows,
            'totals' => []
        ];
    }

    protected function afterGenerate()
    {
        // Skip parent's convertSensorDataTimestamps since we use a different structure
        // Our timestamps are already in the correct format in hourly_items
    }
}

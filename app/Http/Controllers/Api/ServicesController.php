<?php

namespace App\Http\Controllers\Api;

use App\Transformers\DeviceService\DeviceServiceFullTransformer;
use CustomFacades\ModalHelpers\ServiceModalHelper;
use Tobuli\Entities\Device;
use Tobuli\Entities\DeviceService;
use FractalTransformer;
use Illuminate\Http\Request;
use Validator;

class ServicesController extends ApiController
{
    /**
     * Get all services for a specific device or all devices.
     */
    public function index(Request $request)
    {
        $deviceIds = $this->user->devices()->pluck('devices.id')->toArray();
        $query = DeviceService::with('device')->whereIn('device_id', $deviceIds);

        if ($request->has('device_id')) {
            $query->where('device_id', $request->get('device_id'));
        }

        $services = $query->paginate($request->get('limit', 15));

        return response()->json(array_merge(
            ['status' => 1],
            FractalTransformer::paginate($services, DeviceServiceFullTransformer::class)->toArray()
        ));
    }

    /**
     * Get data required for creating a new service (odometer, engine hours, etc.)
     */
    public function create(Request $request)
    {
        $device_id = $request->get('device_id');
        
        if (empty($device_id)) {
            return response()->json(['status' => 0, 'message' => 'device_id is required'], 422);
        }
        
        return response()->json(array_merge(
            ['status' => 1],
            ServiceModalHelper::createData($device_id)
        ));
    }

    /**
     * Store a new service.
     */
    public function store(Request $request)
    {
        $device_id = $request->get('device_id');
        
        if (empty($device_id)) {
            return response()->json(['status' => 0, 'message' => 'device_id is required'], 422);
        }

        $result = ServiceModalHelper::create($device_id);

        return response()->json($result);
    }

    /**
     * Get data for editing an existing service.
     */
    public function edit(Request $request, $id = null)
    {
        $id = $id ?: $request->get('id') ?: $request->get('service_id');
        $service = DeviceService::find($id);

        if (!$service) {
            return response()->json(['status' => 0, 'message' => trans('front.service_not_found')], 404);
        }

        $this->checkException('devices', 'show', $service->device);

        $data = ServiceModalHelper::editData($id);

        return response()->json(array_merge(
            ['status' => 1],
            FractalTransformer::item($service, DeviceServiceFullTransformer::class)->toArray(),
            $data
        ));
    }

    /**
     * Update an existing service.
     */
    public function update(Request $request, $id = null)
    {
        $id = $id ?: $request->get('id') ?: $request->get('service_id');
        $service = DeviceService::find($id);

        if (!$service) {
            return response()->json(['status' => 0, 'message' => trans('front.service_not_found')], 404);
        }

        $this->checkException('devices', 'show', $service->device);

        $result = ServiceModalHelper::edit($id);

        return response()->json($result);
    }

    /**
     * Delete a service.
     */
    public function destroy(Request $request, $id = null)
    {
        $id = $id ?: $request->get('id') ?: $request->get('service_id');
        $service = DeviceService::find($id);

        if (!$service) {
            return response()->json(['status' => 0, 'message' => trans('front.service_not_found')], 404);
        }

        $this->checkException('devices', 'show', $service->device);

        $result = ServiceModalHelper::destroy($id);

        return response()->json($result);
    }
}

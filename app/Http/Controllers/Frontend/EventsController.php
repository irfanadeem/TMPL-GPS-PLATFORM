<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use CustomFacades\ModalHelpers\EventModalHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Tobuli\Entities\DeviceGroup;
use Formatter;

class EventsController extends Controller {

    public function index()
    {
        $data = $this->data;
        $data['user'] = $this->user;

        $events = EventModalHelper::lookup($data);

        return $this->api ? ['status' => 1, 'items' => $events] : view('front::Events.index')->with(['events' => $events]);
    }

    public function doDestroy() {
        return view('front::Events.destroy')->with([
            'id' => request()->get('id', null)
        ]);
    }

    public function destroy()
    {
        EventModalHelper::destroy(request()->get('id', null));

        return ['status' => 1];
    }
    public function eventsTable(Request $request) {
        $input = $request->all();
        
        $event_query = $this->user->events()->join('devices', 'events.device_id', '=', 'devices.id')
            ->join('alerts', 'events.alert_id', '=', 'alerts.id'); 
            if($input['department'] && !empty($input['department'])){
            $event_query->Join('user_device_pivot', 'user_device_pivot.device_id', '=', 'events.device_id')
            ->Join('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
                ->where('device_groups.id',$input['department']);
            }else{
            $event_query->leftJoin('user_device_pivot', 'user_device_pivot.device_id', '=', 'events.device_id')
            ->Join('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id');
            }
        Formatter::byUser($this->user);
        $startDate = Formatter::time()->reverse($input['dataFrom'] . ' 00:00:00');
        $endDate = Formatter::time()->reverse($input['dateTo'] . ' 23:59:59');

        $event_query->where('events.alert_id',$input['alert_id'])
            ->whereBetween('events.time', [$startDate, $endDate])
            ->selectRaw('devices.name as device_name,alerts.name as alert_name,alert_id,
            device_groups.title as group_name,events.time,events.message,events.type
            ,events.additional,events.latitude,events.longitude,events.device_id')->groupBy('events.id');
        $events = $event_query->get();

        foreach ($events as $event) {
            $event->time = Formatter::time()->human($event->time);
        }
        return View::make('admin::Events.custom_table')->with(compact('events'));
           
    }
       public function searchEvent(Request $request)
    {
    
        $filterData = $request->all();
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $alert_id = $request->input('alert_id');
        Formatter::byUser($this->user);
        $startDate = Formatter::time()->reverse($fromDate . ' 00:00:00');
        $endDate = Formatter::time()->reverse($toDate . ' 23:59:59');
        
        $condition = [
                ['time', '>=', $startDate],
                ['time', '<=', $endDate],
                ['alert_id', '=', $alert_id]
            ];
        $groups = DeviceGroup::where('user_id',$this->user->id)->get();
        $devices = $this->user->devicesWithGroups($filterData)->orderBy('group_name','asc')->get();
        $alerts = $this->user->alerts()->orderBy('name','asc')->get();
        $data = [];
        $i=0;
        foreach ($devices as $device) {
            $events = $device->events()->where('user_id',$this->user->id)->where($condition)->get();
            if($events) {
                foreach ($events as $event) {
                    if (isset($event) && $event != null) {
                        $data[$i]['status'] = $device->active;
                        //$data[$i]['address'] = $event->getAddress() ?? '';
                        $data[$i]['latitude'] = $event['latitude'] ?? '';
                        $data[$i]['longitude'] = $event['longitude'] ?? '';
                        $data[$i]['alert'] = $event->alert()->pluck('name')->first() ?? '';
                        $data[$i]['message'] = $event['message'] ?? '';
                        $data[$i]['name'] = $device->name;
                        $data[$i]['device'] = $device;
                        $data[$i]['type'] = $event->type;
                        $data[$i]['id'] = $event->id;
                        $data[$i]['time'] = $event->time
                        ? Formatter::time()->human($event->time)
                        : '';
                        $i++;
                    }
                }
            }
        }
        
        return view('front::Events.filter_index')->with(['data' => $data,'alerts'=>$alerts,'groups' => $groups]);
    }
        public function refill(Request $request)
        {
            $filterData = $request->all();
            $groups = DeviceGroup::where('user_id',$this->user->id)->get();
            $devices = $this->user->devicesWithGroups($filterData)->orderBy('group_name','asc')->get();
            $data = [];
            $i=0;
            $fromDate = $request->input('from');
            $toDate = $request->input('to');
            Formatter::byUser($this->user);
            $startDate = Formatter::time()->reverse($fromDate . ' 00:00:00');
            $endDate = Formatter::time()->reverse($toDate . ' 23:59:59');
            $condition = [
                    ['time', '>=', $startDate],
                    ['time', '<=', $endDate]
                ];
            foreach ($devices as $device) {
                $last_event = $device->last_event()->where('type', 'fuel_fill')->where('user_id',$this->user->id)->where($condition)->get();
                foreach ($last_event as $event) {
                    if (isset($event) && $event != null) {
                        $data[$i]['fuel_fill'] = $event->additional['difference'] ?? '';
                        $data[$i]['status'] = $device->active;
                        $data[$i]['latitude'] = $event['latitude'] ?? '';
                        $data[$i]['longitude'] = $event['longitude'] ?? '';
                        $data[$i]['name'] = $device->name;
                        $data[$i]['device'] = $device;
                        $data[$i]['id'] = $device->id;
                        $data[$i]['time'] = $event->time
                            ? Formatter::time()->human($event->time)
                            : '';
                        $i++;
                    }
                }
            }
            
            return view('front::Refill.index')->with(['data' => $data,'groups' => $groups]);
        }
        public function fuelTheft(Request $request)
        {
            $filterData = $request->all();
            $groups = DeviceGroup::where('user_id',$this->user->id)->get();
            $devices = $this->user->devicesWithGroups($filterData)->orderBy('group_name','asc')->get();
            $data = [];
            $i=0;
            $fromDate = $request->input('from');
            $toDate = $request->input('to');
            Formatter::byUser($this->user);
            $startDate = Formatter::time()->reverse($fromDate . ' 00:00:00');
            $endDate = Formatter::time()->reverse($toDate . ' 23:59:59');
            $condition = [
                    ['time', '>=', $startDate],
                    ['time', '<=', $endDate],
                    [DB::raw('CAST(REPLACE(REPLACE(additional, \'{"difference":\', \'\'), \'}\', \'\') AS SIGNED)'),'<',2999]
                ];
            foreach ($devices as $device) {
                $last_event = $device->last_event()->where('type', 'fuel_theft')->where('user_id',$this->user->id)->where($condition)->get();
                ///print_r($last_event);
                foreach ($last_event as $event) {
                    if (isset($event) && $event != null) {
                        $data[$i]['fuel_theft'] = $event->additional['difference'] ?? '';
                        $data[$i]['latitude'] = $event['latitude'] ?? '';
                        $data[$i]['longitude'] = $event['longitude'] ?? '';
                        $data[$i]['status'] = $device->active;
                        $data[$i]['name'] = $device->name;
                        $data[$i]['device'] = $device;
                        $data[$i]['id'] = $device->id;
                        $data[$i]['time'] = $event['time']
                            ? Formatter::time()->human($event['time'])
                            : '';
                        $i++;
                    }
                }
            }
    
            return view('front::fuelTheft.index')->with(['data' => $data,'groups' => $groups]);
        }
        public function fuelConsumption(Request $request)
        {
            $filterData = $request->all();
            $groups = DeviceGroup::where('user_id',$this->user->id)->get();
            $devices = $this->user->devicesWithGroups($filterData)->orderBy('group_name','asc')->get();
            
            $fromDate = $request->input('from');
            $toDate = $request->input('to');
            
            $deviceIds = $devices->pluck('id')->toArray();

            if (empty($deviceIds)) {
                return view('front::fuelConsum.index')->with(['data' => [], 'groups' => $groups]);
            }

            Formatter::byUser($this->user);
            $startDate = Formatter::time()->reverse($fromDate . ' 00:00:00');
            $endDate = Formatter::time()->reverse($toDate . ' 23:59:59');

            $calculations = \Tobuli\Entities\CronJobCalculation::query()
                ->whereBetween('job_time_from', [$startDate, $endDate])
                ->whereIn('device_id', $deviceIds)
                ->selectRaw("device_id, SUM(fuel_consumption) as total_consumption")
                ->groupBy('device_id')
                ->get()
                ->keyBy('device_id');

            $data = [];
            $i = 0;
            foreach ($devices as $device) {
                if ($calculations->has($device->id)) {
                    $data[$i]['fuel_consum_value'] = round($calculations->get($device->id)->total_consumption, 2);
                    $data[$i]['status'] = $device->active;
                    $data[$i]['name'] = $device->name;
                    $data[$i]['device'] = $device;
                    $data[$i]['id'] = $device->id;
                    $data[$i]['time'] = '';
                    $i++;
                }
            }

            return view('front::fuelConsum.index')->with(['data' => $data,'groups' => $groups]);
        }
        public function currentFuel(Request $request)
        {
            $filterData = $request->all();
            $groups = DeviceGroup::where('user_id',$this->user->id)->get();
            
            $fromDate = $request->get('from', Carbon::now()->format('Y-m-d'));
            $toDate = $request->get('to', Carbon::now()->format('Y-m-d'));

            $devices = $this->user->devicesWithGroups($filterData)->orderBy('group_name','asc')->get();
            $deviceIds = $devices->pluck('id')->toArray();

            Formatter::byUser($this->user);
            $startDate = Formatter::time()->reverse($fromDate . ' 00:00:00');
            $endDate = Formatter::time()->reverse($toDate . ' 23:59:59');

            // Get historical fuel snapshots from CronJobCalculation
            $fuelSnapshots = [];
            if (!empty($deviceIds)) {
                $fuelSnapshots = DB::table('cron_job_calculations')
                    ->select('device_id', 'max_fuel_level', 'job_time_from')
                    ->whereIn(DB::raw("(device_id, id)"), function($query) use ($startDate, $endDate, $deviceIds) {
                        $query->select('device_id', DB::raw('MAX(id)'))
                            ->from('cron_job_calculations')
                            ->whereBetween('job_time_from', [$startDate, $endDate])
                            ->whereIn('device_id', $deviceIds)
                            ->where('max_fuel_level', '>', 0)
                            ->where('max_fuel_level', '<', 2999)
                            ->groupBy('device_id');
                    })
                    ->get()
                    ->keyBy('device_id')
                    ->all();
            }

            $data = [];
            $i=0;
            foreach ($devices as $device) {
                $snapshot = $fuelSnapshots[$device->id] ?? null;
                $fuelValue = $snapshot ? $snapshot->max_fuel_level : 0;
                $time = $snapshot ? $snapshot->job_time_from : $device->time;

                // Fallback to live sensor value if no snapshot found for today/selected range
                if (!$snapshot) {
                    $sensor = $device->getFuelTankSensor('fuel_tank');
                    if ($sensor) {
                        $fuelValue = $sensor->value;
                    }
                }

                if ($fuelValue > 0) {
                    $data[$i]['fuel_tank_vlaue'] = $fuelValue;
                    $data[$i]['status'] = $device->active;
                    $data[$i]['name'] = $device->name;
                    $data[$i]['device'] = $device;
                    $data[$i]['id'] = $device->id;
                    $data[$i]['time'] = Formatter::time()->human($time);
                    $i++;
                }
            }
    
            return view('front::fuelTank.index')->with([
                'data' => $data,
                'groups' => $groups,
                'from' => $fromDate,
                'to' => $toDate
            ]);
        }
        public function fuelSummary(Request $request)
        {
            $filterData = $request->all();
            $groups = DeviceGroup::where('user_id',$this->user->id)->get();
            $fromDate = $request->input('from');
            $toDate = $request->input('to');
            
            // Get user's devices with optional group filter
            $devices = $this->user->devicesWithGroups($filterData)->get();
            $deviceIds = $devices->pluck('id')->toArray();

            if (empty($deviceIds)) {
                return view('front::fuelSummary.index')->with(['data' => [], 'groups' => $groups]);
            }

            Formatter::byUser($this->user);
            $startDate = Formatter::time()->reverse($fromDate . ' 00:00:00');
            $endDate = Formatter::time()->reverse($toDate . ' 23:59:59');

            // Get standard metrics from CronJobCalculation with leftJoin for groups
            $calculations = \Tobuli\Entities\CronJobCalculation::query()
                ->whereBetween('cron_job_calculations.job_time_from', [$startDate, $endDate])
                ->whereIn('cron_job_calculations.device_id', $deviceIds)
                ->join('user_device_pivot', 'user_device_pivot.device_id', '=', 'cron_job_calculations.device_id')
                ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
                ->where('user_device_pivot.user_id', $this->user->id)
                ->selectRaw("
                    COALESCE(device_groups.title, 'Unassigned') as group_name,
                    SUM(fuel_consumption) as total_fuel_consumption,
                    SUM(total_fuel_theft) as total_fuel_theft,
                    SUM(total_fuel_filled) as total_fuel_filled
                ")
                ->groupBy('group_name')
                ->get()
                ->keyBy('group_name');

            // Optimized snapshot calculation for Current Fuel Level (>0 AND <2999)
            $snapshotLevelQuery = DB::table('cron_job_calculations as cjc')
                ->join('user_device_pivot', 'user_device_pivot.device_id', '=', 'cjc.device_id')
                ->leftJoin('device_groups', 'user_device_pivot.group_id', '=', 'device_groups.id')
                ->selectRaw("
                    COALESCE(device_groups.title, 'Unassigned') as group_name,
                    SUM(cjc.max_fuel_level) as total_snapshot_level
                ")
                ->whereIn(DB::raw("(cjc.device_id, cjc.id)"), function($query) use ($startDate, $endDate, $deviceIds) {
                    $query->select('device_id', DB::raw('MAX(id)'))
                        ->from('cron_job_calculations')
                        ->whereBetween('job_time_from', [$startDate, $endDate])
                        ->whereIn('device_id', $deviceIds)
                        ->where('max_fuel_level', '>', 0)
                        ->where('max_fuel_level', '<', 2999)
                        ->groupBy('device_id');
                })
                ->where('user_device_pivot.user_id', $this->user->id)
                ->groupBy('group_name')
                ->get()
                ->pluck('total_snapshot_level', 'group_name')
                ->all();

            $data = [];
            foreach ($calculations as $groupName => $calc) {
                $data[$groupName] = [
                    'fuel_theft' => round($calc->total_fuel_theft, 2),
                    'fuel_fill' => round($calc->total_fuel_filled, 2),
                    'fuel_tank_value' => round($snapshotLevelQuery[$groupName] ?? 0, 2),
                    'fuel_con_value' => round($calc->total_fuel_consumption, 2)
                ];
            }

            return view('front::fuelSummary.index')->with(['data' => $data,'groups' => $groups]);
        }
}
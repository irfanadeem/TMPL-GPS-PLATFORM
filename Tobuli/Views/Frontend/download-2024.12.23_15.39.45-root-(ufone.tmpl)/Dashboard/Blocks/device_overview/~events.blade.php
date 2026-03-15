<div class="panel panel-transparent">
    <div class="panel-heading">
        <div class="panel-title">
            <div class="pull-left">
                <i class="icon events"></i> {{ trans('front.recent_events') }}
            </div>

            <div class="pull-right">
                <a class="link" href="{{ \Tobuli\Lookups\Tables\EventsLookupTable::route('index') }}" target="_blank">
                    {{ trans('global.view_details') }}
                </a>

                <div class="btn-group droparrow" data-position="fixed">
                    <i class="btn bnt-default icon filter"
                       data-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false"></i>

                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="options-dropdown">
                            {!! Form::open(['url' => route('dashboard.config_update'), 'method' => 'POST', 'class' => 'dashboard-config']) !!}
                            {!! Form::hidden('block', 'device_overview') !!}
                            <div class="radio">
                                {!! Form::radio("dashboard[blocks][device_overview][options][event_type]", 0, empty($event_type)) !!}
                                {!! Form::label(null, trans('front.none')) !!}
                            </div>
                            @foreach(\Tobuli\Entities\Event::getTypeTitles() as $type)
                                <div class="radio">
                                    {!! Form::radio("dashboard[blocks][device_overview][options][event_type]", $type['type'], $event_type == $type['type']) !!}
                                    {!! Form::label(null, ucfirst($type['title'])) !!}
                                </div>
                            @endforeach
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <div class="row">
                @if (count($events))
                    @php $messageCount = []; 
                         $alert_id = []; 

$colorPalette = [
    '#2C3E50', '#34495E', '#4B0082', '#6A0F8C', '#8E44AD',
    '#1ABC9C', '#16A085', '#27AE60', '#2980B9', '#2E86C1',
    '#C0392B', '#A93226', '#D35400', '#E67E22', '#F39C12',
    '#F1C40F', '#F39C12', '#E74C3C', '#9B59B6', '#8E44AD',
    '#2C3E50', '#7F8C8D', '#34495E', '#2E4053', '#1B2A6F',
    '#4A4A4A', '#3B3B3B', '#1C1C1C', '#3E3E3E', '#2E2E2E'
];


/*
                         $colorPalette = [
        '#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#33FFF2', '#FF8333', '#8333FF', '#33FF83', '#FF3381', '#33FFAA',
        '#FFD700', '#52BE80', '#FF4530', '#7CFC00', '#00CED1', '#9400D3', '#20B2AA', '#FFA07A', '#FF69B4', '#8A2BE2',
        '#CD5C5C', '#66CDAA', '#4682B4', '#D2691E', '#B0E0E6', '#FF6347', '#98FB98', '#FFDAB9', '#E9967A', '#F08080'
    ];  

*/
@endphp
                    @foreach ($events as $event)
                        @php $message = $event->name;
                         $alertId = $event->alert_id ?? '';
                    if (isset($messageCount[$message])) {
                            $messageCount[$message]++;
                        } else {
                            $messageCount[$message] = 1;
                        }
                        $alert_id[$message] = $alertId;
                    @endphp
                    @endforeach
                    @foreach ($messageCount as $message => $count)
                    @php
                        $alertIdForMessage = $alert_id[$message];
                        $colorIndex = ($alertIdForMessage % 50) - 1;
                        $color = isset($colorPalette[$colorIndex]) ? $colorPalette[$colorIndex] : '#E9967A';
                    @endphp
                        <div class="col-xs-6 col-sm-4 col-md-3" style="height: 100px;margin-bottom: 7px ">       
                            <a class="stat-box" style="background-color: {{ $color }}; height: 100%"
                               href="https://ufone.telematicsmaster.com/events_filter/filter?alert_id={{$alert_id[$message]}}&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
                                <div class="title" style="color: white;">{{ $message ?? '' }}</div>
                              	<div class="count" style="font-size: 25px; color: white;">{{ $count }}</div>
							  	<div class="link" style="color: white;">{{ trans('global.view_details') }}</div>

                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <a class="stat-box" style="background-color: {{ $status['color'] }}"
                           href="https://ufone.telematicsmaster.com/events_filter/filter?alert_id={{$alertId}}&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
                           	<div class="title" style="color: white;">{{ $message ?? '' }}</div>
							<div class="count" style="font-size: 25px; color: white;">{{ $count }}</div>
							<div class="link" style="color: white;">{{ trans('global.view_details') }}</div>

                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
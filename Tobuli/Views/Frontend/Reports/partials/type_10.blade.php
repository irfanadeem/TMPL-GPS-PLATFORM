@extends('Frontend.Reports.partials.layout')

@section('content')
    @foreach ($report->getItems() as $item)
        <div class="panel panel-default">
            @include('Frontend.Reports.partials.item_heading')

            @if (isset($item['error']))
                @include('Frontend.Reports.partials.item_empty')
            @else

            <div class="panel-body">
                @foreach($item['sensors'] as $sensor)
                    @include('Frontend.Reports.partials.item_sensor_data')
                @endforeach
            </div>

            @endif
        </div>
    @endforeach
@stop

@section('scripts')
    @include('Frontend.Reports.partials.chart_properties')
@stop
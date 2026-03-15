@extends('Frontend.Reports.partials.layout')

@section('content')

    <div class="panel panel-default">
        @include('Frontend.Reports.partials.custom_report_item_heading')
        @if ( ! empty($report->getItems()))
            <div class="panel-body no-padding">
                <table class="table table-striped table-speed">
                    <thead>
                    <tr>
                        <th>{{ trans('validation.attributes.date') }}</th>
                        <th>Device</th>
                        <th>{{ trans('front.duration') }}</th>
                        <th>{{ trans('front.position_a') }}</th>
                        <th>{{ trans('front.position_b') }}</th>
                        <th>{{ trans('front.route_length') }}</th>
                        <th>{{ trans('front.driver') }}</th>
                        <th>{{ trans('front.fuel_tank') }}</th>
                        
                        @if ( ! empty($report->getItems()[0]['table']['rows'][0]['fuel_consumption_list']))
                            @foreach($report->getItems()[0]['table']['rows'][0]['fuel_consumption_list'] as $row)
                                <th>{{ $row['title'] }}</th>
                            @endforeach
                        @endif
                        {{-- @if ( ! empty($report->getItems()[0]['table']['rows'][0]['fuel_price_list']))
                            @foreach($report->getItems()[0]['table']['rows'][0]['fuel_price_list'] as $row)
                                <th>{{ $row['title'] }}</th>
                            @endforeach
                        @endif--}}
                    </tr>
                    </thead>
                    @foreach ($report->getItems() as $item)
                            <?php $device_name='';?>
                        @if ( ! empty($item['meta']))
                            @foreach($item['meta'] as $meta)
                                    <?php $device_name=$meta['value'];?>
                            @endforeach
                        @endif
                        <tbody>
                        @foreach ($item['table']['rows'] as $row)
                            <tr>
                                <td>{{ $row['start_at'] }}</td>
                                <td>{{$device_name}}</td>
                                <td>{{ $row['duration'] }}</td>
                                <td>{!! $row['location_start'] !!}</td>
                                <td>{!! $row['location_end'] !!}</td>
                                <td>{{ $row['distance'] }}</td>
                                <td>{{ $row['drivers'] }}</td>
                                <td>{{ $row['drivers'] }}</td>
                                <td>{{ $row['fuel_tank'] }}</td>
                                

                                @if ( ! empty($row['fuel_consumption_list']))
                                    @foreach($row['fuel_consumption_list'] as $_row)
                                        <td>{{ $_row['value'] }}</td>
                                    @endforeach
                                @endif
                                @if ( ! empty($row['fuel_price_list']))
                                    @foreach($row['fuel_price_list'] as $_row)
                                        <td>{{ $_row['value'] }}</td>
                                    @endforeach
                                @endif

                            </tr>
                        @endforeach
                        </tbody>

                    @endforeach
                </table>
            </div>
        @endif

        {{--@include('Frontend.Reports.partials.item_total')--}}

    </div>
@stop
@extends('Frontend.Reports.partials.layout')

@section('content')

    @foreach ($report->getItems() as $item)
        <div class="panel panel-default">
            @include('Frontend.Reports.partials.item_heading')

            @if (isset($item['error']))
                @include('Frontend.Reports.partials.item_empty')
            @else

                @if ( ! empty($item['table']))
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-speed">
                            <thead>
                            <tr>
                                <th>{{ trans('front.time') }}</th>
                                <th>{{ trans('front.event') }}</th>
                                <th>{{ 'Running Hours' }}</th>
                                <th>{{ trans('front.position') }}</th>
                                <th>{{ 'fuel start' }}</th>
                                <th>{{ 'fuel end' }}</th>
                                <th>{{ 'fuel consumption' }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @php $fuelTotal = 0; @endphp
                            @foreach ($item['table']['rows'] as $row)
                            @php $fuelTotal += (int) $row['fuelconsum'] @endphp
                                <tr>
                                    <td>{{ $row['time'] }}</td>
                                    <td>{{ $row['message'] }}</td>
                                    @if($row['message'] === 'ignition Off')
                                    <td>{{ $row['runninghours'] }}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td>{!! $row['location'] !!}</td>
                                    <td>{!! $row['fuelstart'] !!}</td>
                                    <td>{!! $row['fuelend'] !!}</td>
                                    <td>{!! $row['fuelconsum'] !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if( ! empty($item['totals']))
                    <div class="panel-body">
                        <table class="table">
                            <tr>
                                <td class="col-sm-6">
                                    <table class="table">
                                        <tbody>
                                        @foreach($item['totals'] as $row)
                                            <tr>
                                                <th>{{ $row['title'] }}:</th>
                                                <td>{{ $row['value']}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </td>
                                <td class="col-sm-6">
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    @endforeach
@stop
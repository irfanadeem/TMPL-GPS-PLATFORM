<table class="table">
    <tbody>
    @foreach($events as $key => $event)
        <tr>
            <td class="text-left">{{ $event['title'] }}</td>
            <td class="text-right"><b><span class="latestEventTotalCountMeterial">{{ $event['count'] ?? 0 }}</span> {{ trans('front.times') }}</b></td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="row">
    @foreach($statuses as $status)
        <div class="col-xs-6 col-sm-4 col-md-2">
            <a class="stat-box" style="background-color: {{ $status['color'] }}" href="{{ $status['url'] }}" target="_blank">
                <div class="title" style="font-size: 18px;">{{ $status['label'] }}</div>
                <div class="count" style="font-size: 24px;">{{ $status['data'] }}</div>
                <div class="link"style="font-size: 14px;">{{ trans('global.view_details') }}</div>
            </a>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-sm-6">
        @include("Frontend.Dashboard.Blocks.device_overview.events")
    </div>
    <div class="col-sm-6">
        @include("Frontend.Dashboard.Blocks.device_overview.graph")
    </div>
</div>

<script type='text/javascript'>
    if ($('#dashboard').is(':visible'))
        setTimeout(function () {
            app.dashboard.loadBlockContent('device_overview', true);
        }, 10000);
</script>
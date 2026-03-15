<link href="https://fonts.cdnfonts.com/css/digital-7-mono" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<div id="dashboard_blocks" class="row" style="color: white">
    @if( ! empty($blocks))
        @foreach($blocks as $block => $html)
            {!! $html !!}
        @endforeach
    @else
        <p><b>{{ trans('front.nothing_found_request') }}</b></p>
    @endif
</div>
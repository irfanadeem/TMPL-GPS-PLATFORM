@extends('Admin.Layouts.default')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.css"/>
@section('javascript')
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/2.1.8/api/sum().js"></script>
@stop
@section('content')
    <div class="panel panel-default" id="table_tickets">
        <div class="panel-heading">
            <div class="panel-title">Fuel Thefts</div>
        </div>
        <div class="panel-body">
            @include('Frontend.fuelTheft.list')
        </div>
    </div>
@stop
@section('javascript')
    <script>
        tables.set_config('table_tickets', {
            url: '{{ route("events.fuelTheft") }}',
            do_destroy: {
                url: '{{ route("admin.objects.do_destroy") }}',
                modal: 'tickets_delete',
                method: 'GET'
            }
        });
    </script>
    @stop


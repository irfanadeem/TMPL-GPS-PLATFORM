@extends('Frontend.Layouts.default')
<style>
 .content{
             background-color: #1656a5;
         }
        .modal-full {
            min-width: 90%;
            margin: 0 auto;
            margin-top: 20px;
        }
        /* Bootstrap shadow */
        .shadow-container {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .btn-container {
            margin-bottom: 20px;
        }
          .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #1e2944;
            border-radius: 8px;
            overflow: hidden;
        }

        .dash-table thead tr {
            background-color: #1656a5;
            color: white;
            text-align: left;
            font-size: 14px;
        }

        .dash-table th, td {
            padding: 12px 16px;
        }

        .dash-table tbody tr {
            border-bottom: 1px solid #2a3b5f;
            color: white;
        }

        .dash-table tbody tr:last-child {
            border-bottom: none;
        }

        .dash-table tbody tr:hover {
            background-color: #2b3e65;
        }

        .dash-table tfoot tr {
            background-color: #fabf4e;
            color: black;
        }
    </style>

@section('header-menu-items')
    <li>
        <a href="{!! route('objects.index') !!}" role="button">
            <span class="icon map"></span>
            <span class="text">{!! trans('admin.map') !!}</span>
        </a>
    </li>
@stop

@section('content')
    <div id="dashboard">
        @include('Frontend.Events.custom_table')
    </div>
@stop

@section('scripts')
    @include('Frontend.Layouts.partials.app')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function () {
        var table = $('#myTable').DataTable();
    });
    </script>
@stop

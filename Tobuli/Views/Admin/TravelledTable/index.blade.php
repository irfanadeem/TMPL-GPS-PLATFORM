@extends('Admin.Layouts.mastertable')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script> 
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<style>
    .table-button {
    font-size: 11px !important;
   
    margin: 11px 3px !important;
}

        body{
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
        .top {
            display: flex !important;
            justify-content: space-between !important;
            flex-direction: column-reverse;
        }
        div.dt-buttons {
            float: right;
            display: flex;
            flex-direction: row-reverse;
        }
    </style>
@section('content')

<div id="EventsLookupTableContainer" class="container">
   <div class="shadow-container" style="color: white;background-color: #171821">
               <div class="btn-container">
            <h4 style="float: left; margin-right: 5px;" >{{ $type }}</h4>
        </div>
            <table id="myTable" class="display table dash-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Department</th>
                        <th>Vehicle</th>
                        <th>Engine Hours</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($countData as $index=> $row) 
                    <tr>
                        <td>{{ $index+1 }}</td>
                        <td>{{ $row['created_at'] }}</td>
                        <td>{{ $row['department'] }}</td>
                        <td>{{ $row['vehicle'] }}</td>
                        <td>{{ $row['engine_hours'] }}</td>
                        
                    </tr>
                    @endforeach
                
                </tbody>
            </table>
        </div>
</div>
@stop

@section('javascript')
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script>
    $(document).ready(function () {
        var logoUrl = "{{ Appearance::getAssetFileUrl('logo') }}";
        $('#myTable').DataTable({
            dom: '<"top"lfBr>t<"bottom"ip>',
            searching: true,
            paging: true,
            info: false,
            buttons: [
                {
                    extend: 'print',
                    text: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF"><path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
                    className: 'btn btn-primary table-button',
                    title: 'Total Hours',
                    customize: function (win) {
                        $(win.document.body)
                            .prepend(
                                `<div style="text-align: center; margin-bottom: 20px;">
                                <img src="${logoUrl}" style="width: 150px; height: auto;">
                             </div>`
                            );
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="#605DFF"><path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z"/></svg>',
                    className: 'btn btn-success table-button',
                    title: 'Total Hours'
                }
            ]
        });
    });
    
    </script>
  
@stop

<style>
    .dataTables_scrollBody {
    max-height: 400px;
    overflow-y: auto !important;
}
</style>
<div class="row">
    <div class="col-sm-10 col-md-12 col-lg-12">
   <div class="container" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
                                <div class="col-md-6" id="block_fuel_fence1">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                    <div class="pull-left">Fuel Theft Insight
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <table class="dash-table" id="dataTable3">
                                            <thead>
                                                <tr>
                                                <th class="sorting_disabled">
                                                Sr#
                                                </th><th class="sorting_disabled">
                                                Department
                                                </th>
                                                <th class="sorting_disabled">
                                                Vehicle
                                                </th>
                                                <th class="sorting_disabled">
                                                Date&Time
                                                </th>
                                                <th class="sorting_disabled">
                                                Liters
                                                </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($fuel_theft_rows as $index => $trow) 
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $trow->group_name??''}}</td>
                                                    <td>{{ $trow->device_name }}</td>
                                                    <td>{{ date('d-m-Y', strtotime($trow->date)) }}</td>
                                                    <td>{{ $trow->total_fuel_theft }}</td>
                                                    
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                  
                        <div class="col-md-6" id="block_fuel_fence2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                        <div class="pull-left">Geofence
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    <div>
                                        <table class="dash-table" id="dataTable4">
                                            <thead>
                                                <tr>
                                                <th class="sorting_disabled">
                                                Sr#
                                                </th><th class="sorting_disabled">
                                                Department
                                                </th>
                                                <th class="sorting_disabled">
                                                Vehicle
                                                </th>
                                                <th class="sorting_disabled">
                                                GeoFence IN
                                                </th>
                                                <th class="sorting_disabled">
                                                GeoFence OUT
                                                </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($zone_events as $index => $zrow)
                                                 <tr>
                                                 <td>{{ $index+1 }}</td>
                                                <td>{{ $zrow->group_name??'-'}}</td>
                                                    <td>{{ $zrow->device_name??'-' }}</td>
                                                    <td>{{ $zrow->zone_in_count??'-' }}</td>
                                                    <td>{{ $zrow->zone_out_count??'-' }}</td>
                                                    
                                                </tr>
                                                
                                            @endforeach
                                               
                                                
                                            </tbody>
                                        </table>   
                                    </div>
                            </div>
        </div>   
    </div>
   
</div>
<script>
var logoUrl = "{{ Appearance::getAssetFileUrl('logo') }}";
$('#dataTable3').DataTable({
    dom: '<"top"lBfr>t<"bottom"ip>',
     searching: false,
     paging: false,
     info: false,
     scrollY: '300px',
     scrollCollapse: true, 
     responsive: true,
     buttons: [
            {
                extend: 'print',
                 text: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF"><path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
                className: 'btn btn-primary table-button',
                title: 'Fuel Theft Insight',
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
                 title: 'Fuel Theft Insight'
            }
        ]
     });
     $('#dataTable4').DataTable({
    dom: '<"top"lBfr>t<"bottom"ip>',
     searching: false,
     paging: false,
     info: false,
     scrollY: '300px',
     scrollCollapse: true, 
     responsive: true,
     buttons: [
            {
                extend: 'print',
                 text: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF"><path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
                className: 'btn btn-primary table-button',
                title: 'Geofence',
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
                title: 'Geofence'
            }
        ]
     });
</script>


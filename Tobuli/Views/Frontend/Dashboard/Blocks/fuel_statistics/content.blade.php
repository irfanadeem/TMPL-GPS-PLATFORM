<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script> 
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<style>
    .table-button {
    font-size: 11px !important;
    color: white !important;
    margin: 11px 3px !important;
    background-color: white !important;
    }
    .dataTables_scrollBody {
        max-height: 400px;
        overflow-y: auto !important;
    }
            @media print {
    @page {
        size: A4;
        margin: 20mm;
    }
                #fuelChart-print{
                    display: none;
                }
    }
</style>
<div class="row">
    <div class="col-sm-10 col-md-12 col-lg-12">
        <div class="container"
             style="border-radius: 5px; padding: 16px; margin: 10px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">
                        <div class="pull-left">FUEL STATISTICS
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fuel Stats Block -->
            <div class="col-md-12">
                <div class="table-responsive">

                    <table class="dash-table" id="dataTable">
                        <thead>
                        <tr>
                            <th class="sorting_disabled">
                                No#
                            </th><th class="sorting_disabled">
                                Date
                            </th>
                            <th class="sorting_disabled">
                                Consumption (L)
                            </th>
                            <th class="sorting_disabled">
                                Theft (L)
                            </th>
                            <th class="sorting_disabled">
                                Refill (L)
                            </th>
                            <th class="sorting_disabled">
                                Level (L)
                            </th>

                        </tr>
                        </thead>
                        <tbody>
                        @php $total_fuel_consumption = 0;
                                            $total_fuel_theft = 0;
                                            $total_fuel_filled = 0;
                                            $max_fuel_level = 0;
                        @endphp

                        @foreach($rows as $index => $row)
                            @php $total_fuel_consumption += $row->total_fuel_consumption;
                                            $total_fuel_theft += $row->total_fuel_theft;
                                            $total_fuel_filled += $row->total_fuel_filled;
                                            if ($index == 0)
                                            $max_fuel_level = $row->max_fuel_level;

                            @endphp
                            <tr>
                            <td>{{ $index + 1 }}</td>
                                <td>{{date('d-m-Y', strtotime($row->date))}}</td>
                                <td>{{$row->total_fuel_consumption}}</td>
                                <td>{{$row->total_fuel_theft}}</td>
                                <td>{{$row->total_fuel_filled}}</td>
                                <td>{{$row->max_fuel_level}}</td>
                            </tr>
                        @endforeach

                        </tbody>
                        <tfoot>
                        <tr>
                        <th class="sorting_disabled">
                                No#
                            </th>
                            <th>
                                Total
                            </th>
                            <th class="sorting_disabled">
                                {{$total_fuel_consumption}}
                            </th>
                            <th class="sorting_disabled">
                                {{$total_fuel_theft}}
                            </th>
                            <th class="sorting_disabled">
                                {{$total_fuel_filled}}
                            </th>
                            <th class="sorting_disabled">
                              Current Fuel Level  {{$max_fuel_level}}
                            </th>

                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <!-- Fuel Chart Block -->
            <div class="col-md-12 chart-container" id="fuelChart-print">
                <canvas id="fuelMetricsChart"></canvas>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
    var logoUrl = "{{ Appearance::getAssetFileUrl('logo') }}";
    $('#dataTable').DataTable({
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
                 title: 'Fuel Statistics',
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
                title: 'Fuel Statistics'
            }
        ]
     });
        const rows = @json($rows);
// Extract data for the chart
        const dates = rows.map(row => row.date);
        const consumption = rows.map(row => row.total_fuel_consumption);
        const theft = rows.map(row => row.total_fuel_theft);
        const refill = rows.map(row => row.total_fuel_filled);
        /*const level = rows.map(row => row.max_fuel_level);*/
// Calculate the max value dynamically for all datasets
        const allData = [...consumption, ...theft, ...refill];
        const maxFuelData = Math.max(...allData);
        const minFuelData = Math.min(...allData);

// Create the chart
        const ctx = document.getElementById('fuelMetricsChart').getContext('2d');
        const fuelMetricsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates.reverse(),
                datasets: [
                    {
                        label: 'Consumption (L)',
                        data: consumption.reverse(),
                        borderColor: 'rgb(100, 181, 246)',  // Light blue
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: 'Theft (L)',
                        data: theft.reverse(),
                        borderColor: 'rgb(229, 115, 115)',  // Light red
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        fill: false,
                        borderDash: [5, 5],
                        tension: 0.4
                    },
                    {
                        label: 'Refill (L)',
                        data: refill.reverse(),
                        borderColor: 'rgb(129, 199, 132)',  // Light green
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4
                    }/*,
                    {
                        label: 'Level (L)',
                        data: level.reverse(),
                        borderColor: 'rgb(179, 157, 219)',  // Light purple
                        backgroundColor: 'transparent',
                        borderWidth: 1,
                        fill: false,
                        borderDash: [2, 2],
                        tension: 0.4
                    }*/
                ]
            },
            options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                labels: {
                    color: '#ffffff',
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12
                    }
                },
                onClick: function(e, legendItem, legend) {
                    const index = legendItem.datasetIndex;
                    const ci = legend.chart;
                    
                    // Toggle dataset visibility
                    if (ci.isDatasetVisible(index)) {
                        ci.hide(index);
                        legendItem.hidden = true;
                    } else {
                        ci.show(index);
                        legendItem.hidden = false;
                    }
                    
                    // Update dataset's hidden property
                    ci.data.datasets[index].hidden = !ci.isDatasetVisible(index);
                    
                    ci.update();
                }
            },
            title: {
                display: true,
                text: 'Fuel Matrix Over Time',
                color: '#ffffff',
                font: {
                    size: 22,
                    weight: 'bold'
                },
                padding: {
                    top: 10,
                    bottom: 10
                }
            },
        },
    },
    plugins: [
        {
            id: 'lineTotalLabels',
            afterDatasetsDraw(chart) {
                const {ctx, chartArea: {top}, scales: {x, y}} = chart;

                chart.data.datasets.forEach((dataset, i) => {
                    // Only draw labels if dataset is visible
                    if (!dataset.hidden) {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach((bar, index) => {
                            const value = dataset.data[index];
                            ctx.fillStyle = '#ffffff';
                            ctx.textAlign = 'center';
                            ctx.font = '12px Arial';
                            ctx.fillText(value, bar.x, bar.y - 10);
                        });
                    }
                });
            },
        }
    ]
});

// Add event listener for legend changes
fuelMetricsChart.options.plugins.legend.onClick = function(e, legendItem, legend) {
    const index = legendItem.datasetIndex;
    const ci = legend.chart;
    
    // Toggle dataset visibility
    if (ci.isDatasetVisible(index)) {
        ci.hide(index);
        ci.data.datasets[index].hidden = true;
    } else {
        ci.show(index);
        ci.data.datasets[index].hidden = false;
    }
    
    ci.update();
};
    
    });
</script>

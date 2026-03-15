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
        @media print {
    @page {
        size: A4;
        margin: 20mm;
    }
            .dt-buttons{
                display: none;  
            } 
            #travelChart-print{
                display: none !important;
            }
    }
</style>
<div class="row">
    <div class="col-sm-10 col-md-12 col-lg-12">
        <div class="container"
             style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">
                        <div class="pull-left">Travel Statistics
                        </div>
                        
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="dash-table" id="dataTable2">
                        <thead>
                        <tr>
                            <th class="sorting_disabled">
                                Sr#
                            </th><th class="sorting_disabled">
                                Vehicle
                            </th><th class="sorting_disabled">
                                Department
                            </th>
                            <th class="sorting_disabled">
                                Travelled KM
                            </th>

                        </tr>
                        </thead>
                        @php
                            $total_distance = 0;
                        @endphp
                        <tbody>
                        @foreach($travel_data as $index => $row)
                        @php $total_distance += $row->total_distance; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->device_name??'' }}</td>
                            <td>{{ $row->department??'' }}</td>
                            <td>{{ $row->total_distance??'' }}</td>
                        </tr>
                    @endforeach
                    <tbody>
                        <thead>
                        <tr>
                            <th colspan="2" class="sorting_disabled">
                                No#
                            </th><th class="sorting_disabled">
                                Total KM
                            </th>
                            <th class="sorting_disabled">
                                {{$total_distance}}
                            </th>
                        </tr>
                        </thead>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-8" style="height: 500px" id="travelChart-print">
                <canvas id="travelMetricsChart"></canvas>
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
    $('#dataTable2').DataTable({
    dom: '<"top"lBfr>t<"bottom"ip>',
     searching: false,
     paging: false,
     info: false,
     buttons: [
            {
                extend: 'print',
                 text: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="#605DFF"><path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
                className: 'btn btn-primary table-button',
                title: 'Travel Statistics',
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
                title: 'Travel Statistics'
            }
        ]
     });
    
        const travel_data = @json($travel_data);
        const travel_dates = travel_data.map(row => row.device_name);
        const travel_distance = travel_data.map(row => row.total_distance);

// Calculate the max and min values dynamically for the dataset
        const maxTravelDistance = Math.max(...travel_distance);
        const minTravelDistance = Math.min(...travel_distance);
// Create the chart
        const travel_ctx = document.getElementById('travelMetricsChart').getContext('2d');
const travelMetricsChart = new Chart(travel_ctx, {
    type: 'line',
    data: {
        labels: travel_dates.reverse(),
        datasets: [
            {
                label: 'Travel Km (L)',
                data: travel_distance.reverse(),
                borderColor: 'rgb(100, 181, 246)',  // Light blue
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'left',
                align: 'center',
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

                    if (ci.isDatasetVisible(index)) {
                        ci.hide(index);
                        legendItem.hidden = true;
                    } else {
                        ci.show(index);
                        legendItem.hidden = false;
                    }

                    ci.data.datasets[index].hidden = !ci.isDatasetVisible(index);
                    ci.update();
                }
            },
            title: {
                display: true,
                text: 'Travel KM Statistics',
                color: '#ffffff',
                font: {
                    size: 22,
                    weight: 'bold'
                },
                padding: {
                    top: 10,
                    bottom: 10
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)',
                    drawBorder: false
                },
                ticks: {
                    color: '#ffffff',
                    maxRotation: 45,
                    minRotation: 45
                }
            },
            y: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)',
                    drawBorder: false
                },
                ticks: {
                    color: '#ffffff',
                    padding: 10
                },
                
            }
        },
        layout: {
            padding: {
                left: 10,
                right: 30,
                top: 20,
                bottom: 10
            }
        },
        elements: {
            point: {
                radius: 0  // Hide points
            },
            line: {
                tension: 0.4  // Make lines smoother
            }
        }
    },
    plugins: [
        {
            id: 'lineTotalLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;

                chart.data.datasets.forEach((dataset, i) => {
                    if (!dataset.hidden) {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach((bar, index) => {
                            const value = dataset.data[index];
                            ctx.fillStyle = '#ffffff'; // White color for labels
                            ctx.textAlign = 'center';
                            ctx.font = '12px Arial';
                            ctx.fillText(value, bar.x, bar.y - 10); // Position above the point
                        });
                    }
                });
            }
        }
    ]
});

// Add event listener for legend click
travelMetricsChart.options.plugins.legend.onClick = function(e, legendItem, legend) {
    const index = legendItem.datasetIndex;
    const ci = legend.chart;

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


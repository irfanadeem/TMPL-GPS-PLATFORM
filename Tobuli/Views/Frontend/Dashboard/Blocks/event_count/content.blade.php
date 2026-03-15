<style>
    @media print {
    @page {
        size: A4;
        
        margin: 20mm;
    }
        #print-event{
            display: block !important;
        }
        #desktop-event{
            display: none !important;
        }
        
        #print-event {
        display: none;
        display: flex;
        flex-wrap: wrap;
        gap: 15px; /* Adjust spacing between items */
        justify-content: space-between; /* Adjust alignment */
    }

    #print-event > div {
        flex: 1 1 calc(25% - 10px); /* 3 columns per row */
        max-width: calc(25% - 10px);
        background: #333; /* Add background color for visibility */
        padding: 15px;
        text-align: center;
        border: 1px solid black;
        border-radius: 10px;
        margin: 5px;
    }

    /* Make it responsive */
    @media (max-width: 992px) {
        #print-event > div {
            flex: 1 1 calc(33.33% - 10px); /* 2 columns on medium screens */
            max-width: calc(33.33% - 10px);
        }
    }

    @media (max-width: 600px) {
        #print-event > div {
            flex: 1 1 100%; /* 1 column on small screens */
            max-width: 100%;
        }
    }
        #print-chart{
            display: none !important;
        }
    }
    
</style>
<div class="row">
    <div class="col-sm-10 col-md-12 col-lg-12">
        <div class="container"
             style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a; margin-top: 15px; padding-top: 5px; padding-bottom: 5px;">
            <!-- Events Display -->
            <div class="col-md-8">
                
<!--                For Desktop-->
                
                <div class="table-responsive" id="desktop-event">
                    @foreach($events as $key => $event)
                        <div class="col-xs-8 col-sm-2 col-md-3" style="border-radius: 10px;">
                            <a class="stat-box" target="_blank"
                            href="{{ route('events.customTable','dataFrom='.$options['from_date'].'&dateTo='.$options['to_date'].'&department='.$options['department'].'&alert_id='.$event->alert_id) }}"
                            style="background-color:{{$event->color??'#f51212b8'}}; border:none;">
                                <div class="title"
                                     style="font-weight: bold; color: rgb(254, 255, 255); font-size: 16px; margin-top: 25px;">
                                    {{ $event->message }}
                                </div>
                                <div class="dash-count"
                                     style="font-weight: bold; color: rgb(254, 255, 255); font-size: 30px;">
                                    {{ $event->count ?? 0 }}
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                
<!--                    For Print-->
                
                     <div class="table-responsive" style="display:none;" id="print-event">
                    @foreach($events as $key => $event)
                        <div class="col-xs-8 col-sm-2 col-md-3" style="border-radius: 10px;">
                                <div class="title"
                                     style="font-weight: bold; color: rgb(254, 255, 255); font-size: 16px; margin-top: 25px;">
                                    {{ $event->message }}
                                </div>
                                <div class="dash-count"
                                     style="font-weight: bold; color: rgb(254, 255, 255); font-size: 30px;">
                                    {{ $event->count ?? 0 }}
                                </div>
                        
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Chart Container -->
            <div class="col-sm-4 col-md-4" style="height: 370px;" id="print-chart">
                <div class="panel panel-transparent" style="height: 370px; border-radius: 10px;">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <div class="text-center">
                                <canvas id="eventMetricsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
    const events = @json($events);

    // Process data for the chart
    const chartData = {
        labels: events.map(event => event.message),
        datasets: [{
            data: events.map(event => event.count),
            backgroundColor: events.map(event => event.color || '#f51212b8'),
            borderColor: '#ffffff',
            borderWidth: 1
        }]
    };

    // Chart configuration
    const config = {
        type: 'pie',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#000',
                        font: {
                            size: 14,
                            family: 'Arial'
                        },
                        padding: 10
                    }
                },
                title: {
                    display: true,
                    text: 'Event Distribution',
                    color: '#000',
                    font: {
                        size: 20,
                        weight: 'bold',
                        family: 'Arial'
                    },
                    padding: {
                        top: 10,
                        bottom: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((acc, value) => acc + value, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${percentage}%`;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    top: 20,
                    bottom: 20,
                    left: 20,
                    right: 20
                }
            },
            elements: {
                arc: {
                    borderWidth: 2,
                    hoverOffset: 10 // Highlight effect on hover
                }
            },
            onClick: function (e, activeElements) {
                if (activeElements.length > 0) {
                    const chart = e.chart;
                    const datasetIndex = activeElements[0].datasetIndex;
                    const index = activeElements[0].index;
                    const label = chart.data.labels[index];
                    const value = chart.data.datasets[datasetIndex].data[index];
                    
                }
            }
        },
        plugins: [
            {
                id: 'pieTotalLabels',
                afterDatasetsDraw(chart) {
                    const { ctx, data } = chart;

                    chart.data.datasets.forEach((dataset) => {
                        const meta = chart.getDatasetMeta(0);
                        meta.data.forEach((slice, index) => {
                            const value = dataset.data[index];
                            const { x, y } = slice.tooltipPosition(); // Get the center position of the slice

                            ctx.fillStyle = '#000'; // Black color for labels
                            ctx.textAlign = 'center';
                            ctx.font = '12px Arial';
                            ctx.fillText(value, x, y); // Place value inside the slice
                        });
                    });
                }
            }
        ]
    };

    // Initialize the chart
    const ectx = document.getElementById('eventMetricsChart').getContext('2d');
    new Chart(ectx, config);
});

</script>


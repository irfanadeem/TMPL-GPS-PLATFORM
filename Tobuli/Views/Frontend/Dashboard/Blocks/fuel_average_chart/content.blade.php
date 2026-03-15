<style>
        @media print {
            @page {
                size: A4;
                margin: 20mm;
            }

            #fuelAverageChart {
                display: none;
            }

            .panel-title {
                display: none;
            }
        }
</style>
<div class="row" id="fuelAverageChart">
    <div class="col-sm-10 col-md-12 col-lg-12">
        <div class="container"
             style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color: #131f3a ;margin-top: 15px;padding-top: 5px;padding-bottom: 5px;">
            <div class="col-md-8 chart-container-fuel">
                <canvas id="fuelAverageMetricsChart"></canvas>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    $(document).ready(function () {
     const fuel_data = @json($fuel_average);

// Extract data for the chart
const device_name = fuel_data.map(row => row.device_name);
const per_fuel_average_data = fuel_data.map(row => Math.round(row.per_fuel_average * 100) / 100);
const maxFuelAverage = Math.max(...per_fuel_average_data);

// Create the chart
const fctx = document.getElementById('fuelAverageMetricsChart').getContext('2d');
const fuelAverageMetricsChart = new Chart(fctx, {
    type: 'bar',
    data: {
        labels: device_name.reverse(),
        datasets: [
            {
                label: 'Fuel Average (KM/L)',
                data: per_fuel_average_data.reverse(),
                borderColor: 'rgb(100, 181, 246)',  // Light blue
                backgroundColor: 'rgba(100, 181, 246, 0.5)',  // Light blue (semi-transparent)
                borderWidth: 2,
                fill: false,
                tension: 0.4,
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

                    // Toggle dataset visibility
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
                text: 'Fuel Average by Vehicle',
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
                min: 0,
                max: maxFuelAverage
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
                tension: 0.4  // Smoother lines
            }
        }
    },
    plugins: [
        {
            id: 'barTotalLabels',
            afterDatasetsDraw(chart) {
                const {ctx} = chart;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    if (!dataset.hidden) {  // Draw labels only if dataset is visible
                        meta.data.forEach((bar, index) => {
                            const value = dataset.data[index];
                            ctx.fillStyle = '#ffffff'; // White color for labels
                            ctx.textAlign = 'center';
                            ctx.font = '12px Arial';
                            ctx.fillText(value, bar.x, bar.y - 10); // Position above the bar
                        });
                    }
                });
            }
        }
    ]
});


    });
</script>

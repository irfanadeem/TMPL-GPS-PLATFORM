<div class="row">
    <div class="col-sm-10">
        <div id="fuel_count" style="width: 100%; height: 300px"></div>
    </div>
    <div class="col-sm-2">
        <div id="fuel_count_legends"></div>
        <div  style="padding-top: 10px;">
            <a href="/current_fuel/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
                <input type="button" style="background-color: #12d53d;color: black; margin-top: 5px; width: 125px;" value="Fuel Level"></a>
            <br>
            <a href="/devices_refill/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
                <input type="button" style="background-color: #f2d413;color: black; margin-top: 5px; width: 125px;" 
                    value="Fuel Refill"></a><br>
        <a href="/fuelTheft/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
            <input type="button" style="background-color: #f94040; color: black; margin-top: 5px; width: 125px;"
                     value="Fuel Theft"></a><br>
        
        <a href="/fuel_consumption/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
            <input type="button" style="background-color: #3f88f7;color: black; margin-top: 5px; width: 125px;" value="Fuel Consumption"></a><br>
        <a href="/fuel_summary/filter?from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
            <input type="button" style="background-color: #15f9f6;color: black; margin-top: 5px; width: 125px;" value="summary"></a><br>
              </div>

    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var keys = [];
        var dates = {!! $keys !!};
        for (i in dates) {
            keys.push([i, dates[i]]);
        }
        var dataset = [];
        var data = {!! $data !!};
        for (device in data) {
            dataset.push({
                label: device,
                data: data[device]
            });
        }

        function showTooltip(x, y, contents) {
            $('<div id="tooltip">' + contents + '</div>').css({
                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 5,
                border: '1px solid #ccc',
                padding: '2px',
                'background-color': '#eee',
                opacity: 0.90
            }).appendTo("body").fadeIn(200);
        }

        var plot = $.plot($("#fuel_count"), dataset, {
            yaxis: {
                font: {
                    size: 12,
                    color: "black",
                },
                tickFormatter: function formatter(x) {
                    return x.toString() + ' liters';
                }
            },
            xaxis: {
                ticks: keys,
                autoscaleMargin: .05,
                font: {
                    size: 12,
                    color: "black"
                }
            },
            series: {
                shadowSize: 1,
                bars: {
                    show: true,
                    barWidth: 0.06,
                    order: 1,
                    lineWidth: 0,
                    fill: true,
                    fillColor: { colors: [ { opacity: 1 }, { opacity: 0.5 } ] },
                      barLabels: {
                show: true,
                align: 'center',     // Aligns label at the center of the bar
                valign: 'above',      // Position label above the bar
                labelFormatter: function (label) {
                    return label + " liters"; // Optional formatting for label
                },
                font: {
                    size: 12,
                    weight: "bold",
                    color: "#000000"
                }
            }
                }
            },
            legend: {
                show: true,
                noColumns: 1,
                labelFormatter: function(label, series) {
                    return '<span>' + label + '</span>';
                },
                container: $('#fuel_count_legends'),
                labelBoxBorderColor: '#fff'
            },
            grid: {
                show: true,
                borderWidth: 0,
                borderColor: 'black',
                backgroundColor: '#fbfcfd',
                clickable: true
            },
            plugins: {
            labels: 
            {
                render: 'value',
                fontSize: 20,
            }
        },
        });
        $("<div id='tooltip'></div>").css({
            position: "absolute",
            display: "none",
            border: "1px solid #black",
            padding: "2px",
            backgroundColor: "black",
            color: "white",
            "font-size": "16px",
            opacity: 1,
            "z-index": "9999"
        }).appendTo("body");

        $("#fuel_count").bind("plotclick", function (event, pos, item) {
            if (item) {
                var x = item.datapoint[0],
                    y = item.datapoint[1],
                    count = item.datapoint[1]; // Assuming the count is the y value

                $("#tooltip").html(count + " liters")
                    .css({ top: item.pageY + 5, left: item.pageX + 5 })
                    .fadeIn(200);
            } else {
                $("#tooltip").hide();
            }
        });

        setTimeout(() => {
            var plotOffset = plot.getPlotOffset();
            var xTicks = plot.getAxes().xaxis.ticks;

            xTicks.forEach((tick, index) => {
                if (tick.v < plot.getAxes().xaxis.min || tick.v > plot.getAxes().xaxis.max) {
                    return; // Skip ticks outside the viewable range
                }

                // Find the x position of the tick
                var xPos = plot.pointOffset({x: tick.v, y: 0}).left;

                // Define colors for specific ticks
                let backgroundColor;
                switch (index) {
                    case 0:
                        backgroundColor = 'rgb(18 213 61 / 58%)';// Color for first tick
                        break;
                    case 1:
                        backgroundColor = 'rgba(249,216,4,0.55)'; // Color for second tick
                        break;
                    case 2:
                        backgroundColor = 'rgb(249 64 64 / 57%)'; // Color for third tick
                        break;
                    case 3:
                        backgroundColor = 'rgb(63 136 247 / 60%)'; // Color for fourth tick
                        break;
                    default:
                        backgroundColor = 'transparent'; // Default background for other ticks
                }

                // Create and style the background div for each tick
                $('<div class="tick-bg"></div>').css({
                    position: 'absolute',
                    left: xPos + plotOffset.left - 141, // Adjust for alignment
                    bottom: plotOffset.bottom - 23,    // Position behind the label
                    width: 150,
                    height: 20,
                    backgroundColor: backgroundColor,
                    zIndex: 1,
                    border: `1px solid black`
                }).appendTo("#fuel_count");
            });
        }, 100);
    });



</script>

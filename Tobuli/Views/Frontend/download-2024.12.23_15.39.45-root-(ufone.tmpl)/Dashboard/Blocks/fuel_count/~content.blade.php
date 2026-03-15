<div class="row">
    <div class="col-sm-10">
        <div id="fuel_count" style="width: 100%; height: 300px"></div>
    </div>
    <div class="col-sm-2">
        <div id="fuel_count_legends"></div>
        <div  style="padding-top: 10px;">
            <a href="/current_fuel/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
                <input type="button" style="background-color: #17c23d;color: black; margin-top: 5px; width: 125px;" value="Fuel Level"></a>
            <br>
            <a href="/devices_refill/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
                <input type="button" style="background-color: #dbbe12;color: black; margin-top: 5px; width: 125px;" 
                    value="Fuel Refill"></a><br>
        <a href="/fuelTheft/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
            <input type="button" style="background-color: #ed7c7c; color: black; margin-top: 5px; width: 125px;"
                     value="Fuel Theft"></a><br>
        
        <a href="/fuel_consumption/filter?_groups=&from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
            <input type="button" style="background-color: #82b0f6;color: black; margin-top: 5px; width: 125px;" value="Fuel Consumption"></a><br>
        <a href="/fuel_summary/filter?from={{now()->format('Y-m-d')}}&to={{now()->format('Y-m-d')}}" target="_blank">
            <input type="button" style="background-color: #10e0dd;color: black; margin-top: 5px; width: 125px;" value="summary"></a><br>
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
    });



</script>

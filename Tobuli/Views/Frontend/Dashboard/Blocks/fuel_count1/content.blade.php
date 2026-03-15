<div class="row">
    <div class="col-sm-10">
        <div id="fuel_count" style="width: 100%; height: 300px"></div>
    </div>
    <div class="col-sm-2">
        <div id="fuel_count_legends"></div>
        <div  style="padding-top: 10px;">
            <a href="https://ufone.telematicsmaster.com/lookup/devices_refill" target="_blank">
                <input type="button" style="background-color: #52BE80;color: black"
                    value="Refilled Detail"></a>
        <a href="https://ufone.telematicsmaster.com/lookup/devices_theft" target="_blank">
            <input type="button" style="background-color: #529cbe; color: black"
                     value="Theft Detail"></a></div>

    </div>

</div>
<script type="text/javascript">
    $(document).ready(function () {
        console.log('fuel_count');
        var keys = [];
        var dates = {!! $keys !!};
        for (i in dates) {
            keys.push([i, dates[i]]);
        }
        var dataset = [];
        var data = {!! $data !!};
        
        // Define colors for each group
        var colors = ['#5DADE2', '#52BE80', '#EC7063', '#F7DC6F', '#F39C12', '#8E44AD', '#E74C3C', '#1ABC9C'];
        var colorIndex = 0;
        
        for (device in data) {
            dataset.push({
                label: device,
                data: data[device],
                color: colors[colorIndex % colors.length]
            });
            colorIndex++;
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
                    barWidth: 0.2,
                    order: 1,
                    lineWidth: 0,
                    fill: true,
                    fillColor: { colors: [ { opacity: 1 }, { opacity: 0.8 } ] }
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
            }
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

<form action="{{route('events.fuelTheft')}}/filter?" method="get">
<div class="form-group" style="display:flex">
    <select name="_groups" class="form-control col-md-6">
        <option value="">Select Group</option>
        @foreach($groups as $group)
            <option value="{{$group['id']}}" {{isset($_GET['_groups']) && $_GET['_groups'] == $group['id'] ? 'selected' : ''}}>{{$group['title']}}</option>
        @endforeach
    </select>
    </div>
    <div class="row">
        <div class="form-group col-md-4">
            <label for="fromDate">From</label>
            <input type="date" id="fromDate" name="from" class="form-control" required
                   value="{{isset($_GET['from']) ? $_GET['from'] : ''}}">
        </div>

        <!-- Date To and Filter Button -->
        <div class="form-group col-md-4">
            <div class="row">
                <!-- Date To -->
                <div class="col-md-12">
                    <label for="toDate">To</label>
                    <input type="date" id="toDate" name="to" class="form-control" required
                           value="{{isset($_GET['to']) ? $_GET['to'] : ''}}">
                </div>

                <!-- Filter Button -->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" style="margin-top: 20px;border-radius: 5px " class="btn btn-primary btn-block">
                Filter
            </button>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <input type="button" style="margin-top: 20px;border-radius: 5px"
             class="btn btn-primary btn-block" id="total_value">
        </div>
    </div>

</form>
<div class="table-responsive">

    <table class="table table-list" id="ticketDataTable" data-toggle="multiCheckbox">
        <thead>
        <tr>

            <th>User</th>
            <th>Vehicle Registration</th>
            <th>Department/Group Name</th>
            <th>Date/Time</th>
            <th>Fuel (L)</th>
            <th>Location</th>
        </tr>
        </thead>
        <tbody>
        <?php //dd($data)?>
        @if (count($data))
            @foreach ($data as $row)
                @php
                    $currDateTime = $row['time'];
                @endphp
@if($row['fuel_theft'] <= '999')
                <tr>
                    <td>
                        {{$row['name']}}
                    </td>
                    <td>{{$row['device']['registration_number']}}</td>
                    <td>{{$row['device']['group_name']}}</td>
                    <td>{{$currDateTime}}</td>
                    <td>
                        {{$row['fuel_theft']}}
                    </td>
                    <td class="actions">
                        <a style="color: #00d400;" target="_blank" href=" https://www.google.com/maps/search/?api=1&query={{isset($row['latitude'])?$row['latitude']:null}},{{isset($row['longitude'])?$row['longitude']:null}}" class="btn icon"  {{--onclick="dialogWindow(event, {{isset($row['name'])?$row['name']:null }})"--}} >
                            <img style="height: 40px; background-repeat: no-repeat" src="/mapicon.png">
                        </a>
                    </td>
                </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="5">Data not found</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
<div class="nav-pagination">
   
</div>
<script>
    window.onload = function() {
        console.log("All resources finished loading!");
        DataTable.ext.errMode = 'none';
        var table = $('#ticketDataTable').DataTable({
        layout: {
            topStart: {
                buttons: ['excel', 'pdf', 'print']
            }
        }
    });
    var total = table.column(4).data().sum();
        $('#total_value').val("Total Fuel "+total);
    }
</script>
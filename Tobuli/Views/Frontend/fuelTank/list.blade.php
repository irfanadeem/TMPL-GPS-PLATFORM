<form action="{{route('events.current_fuel')}}/filter?" method="get">
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Group</label>
            <select name="_groups" class="form-control">
                <option value="">Select Group</option>
                @foreach($groups as $group)
                    <option value="{{$group['id']}}" {{isset($_GET['_groups']) && $_GET['_groups'] == $group['id'] ? 'selected' : ''}}>{{$group['title']}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>From</label>
            <input type="date" name="from" class="form-control" value="{{ $_GET['from'] ?? date('Y-m-d') }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>To</label>
            <input type="date" name="to" class="form-control" value="{{ $_GET['to'] ?? date('Y-m-d') }}">
        </div>
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <button type="submit" style="margin-top: 25px; border-radius: 5px" class="btn btn-primary btn-block">
            Filter
        </button>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <input type="button" style="margin-top: 25px; border-radius: 5px" class="btn btn-primary btn-block" id="total_value">
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
            <th>Fuel (L)</th>
            <th>Date/Time</th>
            <th>Location</th>
        </tr>
        </thead>
        <tbody>
        @if (count($data))
            @foreach ($data as $row)
                @if($row['fuel_tank_vlaue'] < 2999)
                <tr>
                    <td>
                        {{$row['name']}}
                    </td>
                    <td>{{$row['device']['registration_number']}}</td>
                    <td>{{$row['device']['group_name']}}</td>
                    <td>
                        {{(int)$row['fuel_tank_vlaue']}}
                    </td>
                    <td>{{$row['time']}}</td>
                    <td class="actions">
                        <a href="/devices/follow_map/{{isset($row['id'])?$row['id']:null}}" class="btn icon"  onclick="dialogWindow(event, '{{$row['name']}}')" >
                            <img style="height: 40px; background-repeat: no-repeat" src="/mapicon.png">
                        </a>
                    </td>
                </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="6">Data Not Found</td>
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
        
        // Sum values that are currently visible/filtered in the table
        var total = 0;
        table.rows({ search: 'applied' }).every(function() {
            var data = this.data();
            var fuel = parseFloat(data[3]); // Fuel is in the 4th column (index 3)
            if (!isNaN(fuel)) {
                total += fuel;
            }
        });
        
        $('#total_value').val("Total Fuel " + Math.round(total));
    }
</script>
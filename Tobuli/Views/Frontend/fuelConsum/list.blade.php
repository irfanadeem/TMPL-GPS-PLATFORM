<form action="{{ route('events.fuel_consumption') }}" method="get">
<div class="row">
    <div class="form-group col-md-4">
        <label for="_groups">Group</label>
        <select name="_groups" id="_groups" class="form-control">
            <option value="">Select Group</option>
            @foreach($groups as $group)
                <option value="{{$group['id']}}" {{isset($_GET['_groups']) && $_GET['_groups'] == $group['id'] ? 'selected' : ''}}>{{$group['title']}}</option>
            @endforeach
        </select>
    </div>
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
            <th>User</th>
            <th>Vehicle Registration</th>
            <th>Department/Group Name</th>
            <th>Fuel (L)</th>
            <th>Date From </th>
            <th>Date To </th>
            <th>Location</th>
        </tr>
        </thead>
        <tbody>
        <?php //dd($data)?>
        @if (count($data))
            @foreach ($data as $row)
    @if($row['fuel_consum_value'] <= '2999' && $row['fuel_consum_value'] > '0')
                <tr>
                    <td>{{$row['name']}}</td>
                    <td>{{$row['device']['registration_number']}}</td>
                    <td>{{$row['device']['group_name']}}</td>
                    <td>
                        {{$row['fuel_consum_value']}}
                    </td>
                    <td>{{isset($_GET['from']) ? $_GET['from'] : ''}}</td>
                    <td>{{isset($_GET['to']) ? $_GET['to'] : ''}}</td>

                    <td class="actions">
                        <a href="/devices/follow_map/{{isset($row['id'])?$row['id']:null}}" class="btn"  onclick="dialogWindow(event, {{isset($row['name'])?$row['name']:null }})" >
                            <img style="height: 40px; background-repeat: no-repeat" src="/mapicon.png">
                    </td>
                </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="5">Data Not Found</td>
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
            var table = new DataTable('#ticketDataTable', {
            layout: {
                topStart: {
                    buttons: ['excel', 'pdf', 'print']
                }
            }
        });
        var total = table.column(3).data().sum();
        $('#total_value').val("Total Fuel "+total);
        }
</script>
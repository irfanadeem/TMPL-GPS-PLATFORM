<form action="{{ route('events.fuel_summary') }}" method="get">
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
    <div class="form-group col-md-4">
        <label for="toDate">To</label>
        <input type="date" id="toDate" name="to" class="form-control" required
               value="{{isset($_GET['to']) ? $_GET['to'] : ''}}">
    </div>
</div>
    <div class="row">
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" style="margin-top: 20px;border-radius: 5px " class="btn btn-primary btn-block">
                Filter
            </button>
        </div>
    </div>
</form>
<div class="table-responsive">

    <table class="table table-list" id="ticketDataTable" data-toggle="multiCheckbox">
        <thead>
           <tr>
                <th>Group Name</th>
                <th>Fuel Theft</th>
                <th>Fuel Fill</th>
                <th>Fuel Level</th>
                <th>Fuel Consumption</th>
            </tr>
        </thead>
        <tbody>
        <?php //dd($data)?>
        @if (count($data))
             @foreach ($data as $group => $values)
                <tr>
                    <td>{{ $group }}</td>
                    <td>{{ $values['fuel_theft'] ?? 0 }}</td>
                    <td>{{ $values['fuel_fill'] ?? 0 }}</td>
                    <td>{{ $values['fuel_tank_value'] ?? 0 }}</td>
                    <td>{{ $values['fuel_con_value'] ?? 0 }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="5">Data Not Found</td>
            </tr>
        @endif
        </tbody>
         <tfoot>
            <tr>
                <th colspan="1" style="text-align:right">Total:</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
<div class="nav-pagination">
   
</div>
<script>
    window.onload = function() {
            console.log("All resources finished loading!");
            DataTable.ext.errMode = 'none';
            new DataTable('#ticketDataTable', {
            layout: {
                topStart: {
                    buttons: ['excel', 'pdf', 'print']
                }
            }, 
               drawCallback: function () {
            var api = this.api();
            
            // Loop over each column starting from index 1 (ignoring the first column for Group Name)
            api.columns([1, 2, 3, 4], { page: 'current' }).every(function (index) {
                let columnSum = this
                    .data()
                    .reduce(function (a, b) {
                        return a + (parseFloat(b) || 0);
                    }, 0);
                
                // Update the footer for each column with the calculated sum
                $(this.footer()).html(columnSum);
            });
        }
        });
    }
</script>
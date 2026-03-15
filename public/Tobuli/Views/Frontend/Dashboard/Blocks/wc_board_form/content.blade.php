<style>
    select.form-control {
    display: block !important;
    }
    @media print {
    @page {
        size: A4;
        margin: 20mm;
    }
    #container1{
        display:none !important;
    }
    #state-print{
    display:block !important;
}
#state-desktop{
 display:none !important;
}
#statPrintContainer {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        margin-right:170px !important;
        width: 120% !important;
    }

    .stat-print-box {
        display: inline-block !important;
        width: 40% !important;
        text-align: center;
        background: black !important;  Ensure visibility 
        color: white !important;
        border: 1px solid black !important;
        padding: 10px !important;
        margin: 5px !important;
    }
        

    }
#state-print{
    display:none;
}
</style>
<div class="container"  style="border-radius: 5px; padding: 1px; max-width: 100%; background-color: #131f3a;">

<div class="container"   style="border-radius: 5px; padding: 1px; margin: 1px auto; max-width: 100%; background-color:rgb(69, 66, 241);margin-top: 5px; 
    padding-bottom: 10px;
">

                        <!-- From Date -->
        <div class="col-md-12" id="container1">
                    {!! Form::open(['url'=> route('dashboard.config_update_all_block'), 'method' => 'POST',  'id' => 'dashboardConfigForm']) !!}
                    {!! Form::hidden('block', $block) !!}
                    <div class="col-md-2" style="float: left;">
                        <label for="from_date">From:</label>
                        <input type="date" class="form-control"
                               name="dashboard[blocks][wc_board_form][options][from_date]" id="from_date"
                               value="{{$options['from_date']??\Carbon\Carbon::today()->format('Y-m-d')}}">
                    </div>

                    <div class="col-md-2" style="float: left;">
                        <label for="to_date">To:</label>
                        <input type="date" class="form-control"
                               name="dashboard[blocks][wc_board_form][options][to_date]" id="to_date"
                               value="{{$options['to_date']??\Carbon\Carbon::today()->format('Y-m-d')}}">
                    </div>

                    <div class="col-md-2" style="float: left;">
                        <label for="default_unit_of_altitude" class="control-label">Department</label>
                        <select class="form-control" name="dashboard[blocks][wc_board_form][options][department]"
                                id="default_unit_of_altitude">
                            <option value="">All</option>
                            @foreach($groups as $groupRow) 
                                <option value="{{ $groupRow['id'] }}" 
                                    {{ isset($options['department']) && $options['department'] == $groupRow['id'] ? 'selected' : '' }}>
                                    {{ $groupRow['title'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 mt-4 shadow rounded"
                                style="margin-top: 20px; background-color: #131f3a; border: none;">Submit
                        </button>
                        {!! Form::close() !!}

                            <span><a href="{{ route('admin.mastertable.view','dateFrom='.$options['from_date'].'&dateTo='.$options['to_date'].'&department='.$options['department']) }}" class="btn btn-primary w-100 mt-4 shadow rounded" style="margin-top: 21px;border-radius: 5px;background-color: #131f3a;border:none;" role="button" target="_blank">Analytics</a></span>
                    </div>
                        <!-- Print Button -->
                        <div class="col-md-3" style= "margin-top: 12px; float: right; width: 200px; padding-right: 5px;padding-left: 5px;";>
                        <button class="btn w-100 mt-4 shadow rounded tooltip-btn" onclick="printPDF()" style="background-color:transparent" data-tooltip="Download">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="30" height="30" fill= "#ffffff">
                            <path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z" />
                            </svg>
                        </button>
                        <button class="btn w-100 mt-4 shadow rounded tooltip-btn" style="background-color:transparent" onclick="printPage()" data-tooltip="Print">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30" fill="#ffffff">
                            <path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                            </svg>
                        </button>
                        <button id="btn-email" class="btn w-100 mt-4 shadow rounded tooltip-btn" style="background-color:transparent" data-tooltip="Email">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30" fill="#ffffff">
                            <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                            </svg>
                        </button>
                    </div>
                    

        </div>
    </div>
       

     <!-- Engine Stats Block -->
     <div class="container" id="state-desktop" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color:rgb(69, 66, 241);margin-top: 5px;">
                    <div class="col-md-8">
                        <div class="table-responsive">
                                           
                                        
                                                
                                <div class="col-md-3">
                                    <a class="stat-box" style="background-color:rgb(65, 21, 51);margin-top:10px;" href="{{ route('admin.travelled.view','formDate?dataFrom='.$options['from_date'].'&dateTo='.$options['to_date'].'&department='.$options['department'].'&type=total_moving_time') }}" target="_blank">
                                    <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Engine Hours</div>
                                    <div class="dash-count ">{{$rows[0]?->total_moving_time}}</div>
                                    </a>
                                </div>

                                <div class="col-md-3">
                                    <a class="stat-box" style="background-color:rgb(19, 58, 53);margin-top:10px;" href="{{ route('admin.travelled.view','formDate?dataFrom='.$options['from_date'].'&dateTo='.$options['to_date'].'&department='.$options['department'].'&type=total_stop_time') }}" target="_blank">
                                    <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Stop Hours</div>
                                    <div class="dash-count">{{$rows[0]?->total_stop_time}}</div>
                                    </a>
                                </div>

                                <div class="col-md-3">
                                    <a class="stat-box" style="background-color:rgb(58, 57, 19);margin-top:10px;" href="{{ route('admin.travelled.view','formDate?dataFrom='.$options['from_date'].'&dateTo='.$options['to_date'].'&department='.$options['department'].'&type=total_idle_time') }}" target="_blank">
                                    <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Idle Hours</div>
                                    <div class="dash-count">{{$rows[0]?->total_idle_time}}</div>
                                    </a>
                                </div>
                    
                        </div> 
                    </div>        
                        
        </div>

        <!-- Engine box for print -->
          <div class="container" id="state-print" style="border-radius: 5px; padding: 5px; margin: 5px auto; max-width: 100%; background-color:rgb(69, 66, 241);margin-top: 5px;">
                    <div class="col-md-8">
                        <div class="table-responsive" id="statPrintContainer">           
                                <div class="col-md-3 stat-print-box">
                                    <div class="title" style="font-weight: bold; font-size:13px !important;">Total Engine Hours</div>
                                    <div class="dash-count" style="font-size:18px !important;">{{$rows[0]?->total_moving_time}}</div>
                                </div>

                                <div class="col-md-3 stat-print-box">
                                    <div class="title" style="font-weight: bold; font-size:13px !important;">Total Stop Hours</div>
                                    <div class="dash-count" style="font-size:18px !important;">{{$rows[0]?->total_stop_time}}</div>
                                </div>

                                <div class="col-md-3 stat-print-box">
                                    <div class="title" style="font-weight: bold; font-size:13px !important;">Total Idle Hours</div>
                                    <div class="dash-count" style="font-size:18px !important;">{{$rows[0]?->total_idle_time}}</div>
                                </div>
                    
                        </div> 
                    </div>        
                        
        </div>
    </div>
   
</div>
<script type="text/javascript">
$(document).ready(function() {
        $('#dashboardConfigForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            var form = $(this);
            var actionUrl = form.attr('action');
            var formData = form.serialize(); // Serialize form data for POST

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel CSRF token for security
                },
                success: function(response) {
                    $('#responseMessage').text('Form submitted successfully!').css('color', 'green');
                    initializeDashboardBlocks();
                },
                error: function(xhr, status, error) {
                    $('#responseMessage').text('Error submitting form.').css('color', 'red');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>

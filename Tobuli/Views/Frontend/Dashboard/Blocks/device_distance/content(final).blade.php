

<style>
    label{
        color: white;
    }
    .btn{
        border-radius: 5px;
    }
    .stat-box{
        text-decoration: none !important;
        cursor: pointer !important;
        border-radius: 10px !important;
    }
    .stat-box:hover{
        background-color: #FD7C0D !important;
    }
    .form-control{
        border-radius: 4px;
    }
    .dropdown-toggle{
         border-radius: 4px !important;
    }

</style>
<div class="container" style= "max-width: 100%;">
    <!-- Department & Date Range Selection Block -->
 <div class="container" style=" border-radius: 5px; padding: 20px; margin: 10px auto; max-width: 100%; background-color: #4f4f4f;">
    <div class="row d-flex align-items-center gap-3" style="border: radius 6px;">
        <!-- From Date -->
        <div class="col-md-2 col-xs-12" style="float: left;">
            <label for="from_date">From:</label>
            <input type="date" class="form-control" name="from_date" id="from_date" value="2024-12-14">
        </div>

        <!-- To Date -->
        <div class="col-md-2 col-xs-12" style="float: left;">
            <label for="to_date">To:</label>
            <input type="date" class="form-control" name="to_date" id="to_date" value="2024-12-14">
        </div>

        <!-- Department Dropdown -->
        <div class="col-xs-12 col-sm-4" style="float: left;">
            <label for="default_unit_of_altitude" class="control-label">Department</label>
            <div class="btn-group bootstrap-select form-control" style="width: 100%;">
                <button type="button" class="btn dropdown-toggle btn-default" data-toggle="dropdown" role="button" data-id="default_unit_of_altitude" title="Meter" aria-expanded="false">
                    <span class="icon unit-altitude pull-left"></span>
                    <span class="filter-option pull-left">All</span>&nbsp;<span class="bs-caret">
                        <span class="caret"></span></span>
                </button>
                <div class="dropdown-menu open" role="combobox" style="max-height: 137.75px; overflow: hidden; min-height: 0px;">
                    <ul class="dropdown-menu inner" role="listbox" aria-expanded="false" style="max-height: 135.75px; overflow-y: auto; min-height: 0px;">
                        <li data-original-index="0" class="selected"><a tabindex="0" class="" data-tokens="null" role="option" aria-disabled="false" aria-selected="true">
                            <span class="check-mark"></span><span class="text">MPO</span></a></li>
                        <li data-original-index="1"><a tabindex="0" class="" data-tokens="null" role="option" aria-disabled="false" aria-selected="false"><span class="check-mark"></span>
                            <span class="text">Sanitation</span></a></li>
                    </ul>
                    <div class="bs-pagination"></div>
                </div>
            </div>
        </div>
            <button type="submit" class="btn btn-primary w-100 mt-4 shadow rounded" style="margin-top: 20px;">Submit</button>
            <span><a href="https://103.83.91.81/mastertable.html#" class="btn btn-primary w-100 mt-4 shadow rounded" style="margin-top: 21px;border-radius: 5px;" role="button">Analytics</a></span>


        <!-- Submit Button -->
        <div class="col-md-2 col-xs-2" style="float: right;margin-top: 21px;width:18%">
            <button class="btn w-100 mt-4 shadow rounded" style="background-color:transparent"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="30" height="30" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M0 448c0 35.3 28.7 64 64 64l160 0 0-128c0-17.7 14.3-32 32-32l128 0 0-288c0-35.3-28.7-64-64-64L64 0C28.7 0 0 28.7 0 64L0 448zM171.3 75.3l-96 96c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l96-96c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zm96 32l-160 160c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l160-160c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6zM384 384l-128 0 0 128L384 384z"/></svg></button>
            <button class="btn w-100 mt-4 shadow rounded" style="background-color:transparent"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg></button>
            <button  id="btn-print" class="btn w-100 mt-4" style="background-color:transparent"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg></button>
        </div>
    </div>
</div>
                    
    <!-- Event Tiles Block -->
    <div class="container" style="border-radius: 5px; padding: 16px; margin: 10px auto; max-width: 100%; background-color: #4f4f4f;">
                    <div class="col-md-8">

                                    <div class="table-responsive">
                                        <div class="col-xs-8 col-sm-2 col-md-3" style="border-radius:10px;">
                                            <a class="stat-box" style="background: linear-gradient(167deg, rgba(23, 24, 33, 1) 0%, rgba(43, 33, 31, 0.9836309523809523) 47%, rgba(49, 36, 30, 1) 53%, rgba(108, 61, 25, 1) 76%, rgb(87 40 0 / 87%) 100%);border: none;" href="https://103.83.91.81/fueltheft.html#" target="_blank">
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M32 64C32 28.7 60.7 0 96 0L256 0c35.3 0 64 28.7 64 64l0 192 8 0c48.6 0 88 39.4 88 88l0 32c0 13.3 10.7 24 24 24s24-10.7 24-24l0-154c-27.6-7.1-48-32.2-48-62l0-64L384 64c-8.8-8.8-8.8-23.2 0-32s23.2-8.8 32 0l77.3 77.3c12 12 18.7 28.3 18.7 45.3l0 13.5 0 24 0 32 0 152c0 39.8-32.2 72-72 72s-72-32.2-72-72l0-32c0-22.1-17.9-40-40-40l-8 0 0 144c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 512c-17.7 0-32-14.3-32-32s14.3-32 32-32L32 64zM96 80l0 96c0 8.8 7.2 16 16 16l128 0c8.8 0 16-7.2 16-16l0-96c0-8.8-7.2-16-16-16L112 64c-8.8 0-16 7.2-16 16z"/></svg></div>
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FUEL THEFT</div>
                                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">10</div>
                                                
                                            </a>
                                        </div>
                                            <div class="col-xs-8 col-sm-2 col-md-3">
                                            <a class="stat-box" style="background: linear-gradient(167deg, rgba(23, 24, 33, 1) 0%, rgba(43, 33, 31, 0.9836309523809523) 47%, rgba(49, 36, 30, 1) 53%, rgba(108, 61, 25, 1) 76%, rgb(87 40 0 / 87%) 100%);border: none;" href="https://103.83.91.81/fstemper.html#"  target="_blank">
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="50" height="50" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M160 64c-26.5 0-48 21.5-48 48l0 164.5c0 17.3-7.1 31.9-15.3 42.5C86.2 332.6 80 349.5 80 368c0 44.2 35.8 80 80 80s80-35.8 80-80c0-18.5-6.2-35.4-16.7-48.9c-8.2-10.6-15.3-25.2-15.3-42.5L208 112c0-26.5-21.5-48-48-48zM48 112C48 50.2 98.1 0 160 0s112 50.1 112 112l0 164.4c0 .1 .1 .3 .2 .6c.2 .6 .8 1.6 1.7 2.8c18.9 24.4 30.1 55 30.1 88.1c0 79.5-64.5 144-144 144S16 447.5 16 368c0-33.2 11.2-63.8 30.1-88.1c.9-1.2 1.5-2.2 1.7-2.8c.1-.3 .2-.5 .2-.6L48 112zM208 368c0 26.5-21.5 48-48 48s-48-21.5-48-48c0-20.9 13.4-38.7 32-45.3L144 144c0-8.8 7.2-16 16-16s16 7.2 16 16l0 178.7c18.6 6.6 32 24.4 32 45.3z"/></svg></i></div>
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FS TEMPER</div>
                                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">02</div>
                                            
                                            </a>
                                        </div>
                                        <div class="col-xs-8 col-sm-2 col-md-3">
                                            <a class="stat-box" style="background: linear-gradient(167deg, rgba(23, 24, 33, 1) 0%, rgba(43, 33, 31, 0.9836309523809523) 47%, rgba(49, 36, 30, 1) 53%, rgba(108, 61, 25, 1) 76%, rgb(87 40 0 / 87%) 100%);border: none;" href="https://103.83.91.81/fuelrefill.html#"  target="_blank">
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm320 96c0-15.9-5.8-30.4-15.3-41.6l76.6-147.4c6.1-11.8 1.5-26.3-10.2-32.4s-26.2-1.5-32.4 10.2L262.1 288.3c-2-.2-4-.3-6.1-.3c-35.3 0-64 28.7-64 64s28.7 64 64 64s64-28.7 64-64z"/></svg></div>
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FUEL REFILL</div>
                                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">20</div>
                                                
                                            </a>
                                        </div>
                                        <div class="col-xs-8 col-sm-2 col-md-3">
                                            <a class="stat-box" style="background: linear-gradient(167deg, rgba(23, 24, 33, 1) 0%, rgba(43, 33, 31, 0.9836309523809523) 47%, rgba(49, 36, 30, 1) 53%, rgba(108, 61, 25, 1) 76%, rgb(87 40 0 / 87%) 100%);border: none;" href="https://103.83.91.81/accon.html#" target="_blank">
                                            <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="50" height="50" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 64C86 64 0 150 0 256S86 448 192 448l192 0c106 0 192-86 192-192s-86-192-192-192L192 64zm192 96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"/></svg></div>
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">IGNITION ON</div>
                                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">70</div>
                                            
                                            </a>
                                        </div>
                                        <div class="col-xs-8 col-sm-2 col-md-3">
                                            <a class="stat-box" style="background: linear-gradient(167deg, rgba(23, 24, 33, 1) 0%, rgba(43, 33, 31, 0.9836309523809523) 47%, rgba(49, 36, 30, 1) 53%, rgba(108, 61, 25, 1) 76%, rgb(87 40 0 / 87%) 100%);border: none;" href="https://103.83.91.81/accoff.html#" target="_blank">
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="50" height="50" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M384 128c70.7 0 128 57.3 128 128s-57.3 128-128 128l-192 0c-70.7 0-128-57.3-128-128s57.3-128 128-128l192 0zM576 256c0-106-86-192-192-192L192 64C86 64 0 150 0 256S86 448 192 448l192 0c106 0 192-86 192-192zM192 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"/></svg></div>
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">IGNITION OFF</div>
                                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">70</div>
                                            
                                            </a>
                                        </div>
                                        <div class="col-xs-8 col-sm-2 col-md-3">
                                            <a class="stat-box" style="background: linear-gradient(167deg, rgba(23, 24, 33, 1) 0%, rgba(43, 33, 31, 0.9836309523809523) 47%, rgba(49, 36, 30, 1) 53%, rgba(108, 61, 25, 1) 76%, rgb(87 40 0 / 87%) 100%);border: none;" href="https://103.83.91.81/fenceout.html#" target="_blank">
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="50" height="50" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M565.6 36.2C572.1 40.7 576 48.1 576 56l0 336c0 10-6.2 18.9-15.5 22.4l-168 64c-5.2 2-10.9 2.1-16.1 .3L192.5 417.5l-160 61c-7.4 2.8-15.7 1.8-22.2-2.7S0 463.9 0 456L0 120c0-10 6.1-18.9 15.5-22.4l168-64c5.2-2 10.9-2.1 16.1-.3L383.5 94.5l160-61c7.4-2.8 15.7-1.8 22.2 2.7zM48 136.5l0 284.6 120-45.7 0-284.6L48 136.5zM360 422.7l0-285.4-144-48 0 285.4 144 48zm48-1.5l120-45.7 0-284.6L408 136.5l0 284.6z"/></svg></div>
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">FENCE OUT</div>
                                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">10</div>
                                                
                                            </a>
                                        </div>
                                        <div class="col-xs-8 col-sm-2 col-md-3">
                                            <a class="stat-box" style="background: linear-gradient(167deg, rgba(23, 24, 33, 1) 0%, rgba(43, 33, 31, 0.9836309523809523) 47%, rgba(49, 36, 30, 1) 53%, rgba(108, 61, 25, 1) 76%, rgb(87 40 0 / 87%) 100%);border: none;" href="https://103.83.91.81/powercut.html#" target="_blank">
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:35px;"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="white"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24l0 112c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-112c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg></div>
                                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255);font-size:16px;margin-top: 25px;">POWER CUT</div>
                                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255);font-size:30px;">5</div>
                                                
                                            </a>
                                        </div>
                        </div>        
                    </div>
    
                    <div class="col-sm-4 col-md-4" style="height: 370px;">
                        <div class="panel panel-transparent" style="height: 370px;">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <div class="text-center">
                                    
                                        <img src="https://103.83.91.81/eventschart-removebg-preview.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    </div>

                    
    <!-- Engine Stats Block -->  
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; background-color: #252932;">
                <div class="col-md-4">
                    <div>
                        <a class="stat-box" style="background-color: rgb(207, 207, 47);" href="https://103.83.91.81/dashenginestats.html#" target="_blank">
                            <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Engine Hours</div>
                            <div class="count" style="font-weight: bold; color: rgb(254, 255, 255);">3455</div>
                        </a>
                    </div>

                    <div>
                        <a class="stat-box" style="background-color: rgb(250, 190, 78);" href="https://103.83.91.81/enginestop.html#" target="_blank">
                            <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Stop Hours</div>
                            <div class="count" style="font-weight: bold; color: rgb(254, 255, 255);">6547</div>
                        </a>
                    </div>

                    <div>
                        <a class="stat-box" style="background-color: rgb(104, 99, 125);" href="https://103.83.91.81/engineidle.html#" target="_blank">
                            <div class="title" style="font-weight: bold; color: rgb(254, 255, 255);">Total Idle Hours</div>
                            <div class="count" style="font-weight: bold; color: rgb(254, 255, 255);">234</div>
                        </a>
                    </div>
                </div>       
                <!-- Fuel Stats Block -->  
                <div class="col-md-8">
                    <div class="table-responsive">
                            <table class="table table-list table-bordered" style= "background-color: floralwhite;">
                                <thead>
                                    <tr style="background-color: rgb(250, 190, 78); font-size: 13px; color: black;">
                                        <th class="sorting_disabled" >
                                        Date
                                        </th>
                                        <th class="sorting_disabled">
                                        Consumption (L)
                                        </th>
                                        <th class="sorting_disabled">
                                        Theft (L)
                                        </th>
                                        <th class="sorting_disabled">
                                        Refill (L)
                                        </th>
                                        <th class="sorting_disabled">
                                        Level (L)
                                        </th>
                                    
                                    </tr>
                                </thead>
                                    <tbody>
                                            <tr>
                                                <td>01-12-2024</td>
                                                <td>560</td>
                                                <td>90</td>
                                                <td>300</td>
                                                <td>450</td>
                                                
                                            </tr>
                                            <tr>
                                                <td>02-12-2024</td>
                                                <td>340</td>
                                                <td>60</td>
                                                <td>400</td>
                                                <td>650</td>
                                                
                                            </tr>
                                            <tr>
                                                <td>03-12-2024</td>
                                                <td>600</td>
                                                <td>110</td>
                                                <td>200</td>
                                                <td>750</td>
                                            
                                            </tr>
                                            <tr>
                                                <td>04-12-2024</td>
                                                <td>560</td>
                                                <td>90</td>
                                                <td>300</td>
                                                <td>450</td>
                                                
                                            </tr>
                                            <tr>
                                                <td>05-12-2024</td>
                                                <td>560</td>
                                                <td>90</td>
                                                <td>300</td>
                                                <td>450</td>
                                                
                                            </tr>
                                            <tr>
                                                <td>06-12-2024</td>
                                                <td>560</td>
                                                <td>90</td>
                                                <td>300</td>
                                                <td>450</td>
                                            
                                            </tr>
                                                            
                                    </tbody>
                                    <thead>
                                    <tr style="background-color: rgb(250, 190, 78); font-size: 13px; color: black;">
                                        <th>
                                        Total
                                        </th>
                                        <th class="sorting_disabled">
                                        3180
                                        </th>
                                        <th class="sorting_disabled">
                                        530
                                        </th>
                                        <th class="sorting_disabled">
                                        1800
                                        </th>
                                        <th class="sorting_disabled">
                                        3200
                                        </th>
                                    
                                    </tr>
                                </thead>
                            </table>
                        
                        
                    </div>   
                </div>
        </div>         
                   
<!-- Fuel Chart Block -->            
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; background-color: gray;">
                  <div class="col-md-12">
                <div>
                    <img src="https://103.83.91.81/fuelchart.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">
                    <p class="mt-2">Fuel consumption statistics displayed in a graphical format.</p>
                </div>
            </div>
        </div>

<!-- Travel Stats Block --> 
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; background-color: #252932;">
                      <div class="table-responsive">
                    <table class="table table-list table-bordered" style= "background-color: floralwhite;">
                        <thead>
                        <tr style="background-color: rgb(250, 190, 78); font-size: 13px; color: black;">
                                <th class="sorting_disabled">
                                Date
                                </th>
                                <th class="sorting_disabled">
                                Travelled KM
                                </th>
                                
                            </tr>
                        </thead>
                            <tbody>
                                    <tr>
                                        <td>01-12-2024</td>
                                        <td>560</td>
                                       
                                    </tr>
                                    <tr>
                                        <td>02-12-2024</td>
                                        <td>340</td>
                                       
                                    </tr>
                                    <tr>
                                        <td>03-12-2024</td>
                                        <td>600</td>
                                        
                                    </tr>
                                    <tr>
                                        <td>04-12-2024</td>
                                        <td>560</td>
                                        
                                    </tr>
                                    <tr>
                                        <td>05-12-2024</td>
                                        <td>560</td>
                                       
                                    </tr>
                                    <thead>
                                    <tr style="background-color: rgb(250, 190, 78); font-size: 13px; color: black;">
                                <th class="sorting_disabled">
                                Total KM
                                </th>
                                <th class="sorting_disabled">
                                2620
                                </th>
                            </tr>
                        </thead>
                                                    
                            </tbody>
                    </table>
                </div>
        </div>

 <!-- Travel Chart Block -->            
    <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; background-color: #252932;">
    <div class="col-md-12">
                <div>
                    <img src="https://103.83.91.81/travelchart.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">
                    <p class="mt-2">Travel statistics displayed in a graphical format.</p>
                </div>
            </div>
        
    </div>

       

 <!-- Test Block --> 

    <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; background-color: #252932;">
        <div class="col-md-4">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <div class="pull-left">Fuel Theft Insights</div>
                        </div>
                    </div>
                <div class="panel-body dashboard-content" style="position: relative;">
                    <table class="table" style="color:white;">
                    <thead>
                                    <tr>
                                    <th class="sorting_disabled">
                                        Department
                                        </th>
                                        <th class="sorting_disabled">
                                        Vehicle
                                        </th>
                                        <th class="sorting_disabled">
                                        Date&Time
                                        </th>
                                        <th class="sorting_disabled">
                                        Liters
                                        </th>
                                        <th class="sorting_disabled">
                                        Location
                                        </th>
                                    </tr>
                                </thead>
                        <tbody>
                        <tr>
                            <td>MPO</td>
                            <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                            <td>MPO</td>
                            <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                            <td>Sanitation</td>
                            <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        <tr>
                        <td>MPO</td>
                        <td>GF:068 (FS)</td>
                            <td>01-12-2024</td>
                            <td>50</td>
                            <td>MAP</td>
                        </tr>
                        </tbody>
                    </table>  
                    
                    
        </div>

    </div>


    <!-- 2nd window -->
    <div class="col-md-4">
        <div class="panel panel-default">
        <div class="panel-heading">
                    <div class="panel-title">
                        <div class="pull-left">GeoFence</div>
                    </div>
                </div>
        </div>
            <div class="panel-body dashboard-content" style="position: relative;">
                <table class="table" style="color:white;">
                    <thead>
                                        <tr>
                                        <th class="sorting_disabled">
                                        Department
                                        </th>
                                        <th class="sorting_disabled">
                                        Device
                                        </th>
                                        <th class="sorting_disabled">
                                        GeoFence IN
                                        </th>
                                        <th class="sorting_disabled">
                                        GeoFence OUT
                                        </th>
                                    </tr>
                    </thead>
                            <tbody>
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                        </tr>
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                        </tr>
                                      
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                        </tr>
                                       
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                       
                                        </tr>
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                        
                                        </tr>
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                        </tr>
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                        </tr>
                                        <tr>
                                        <td>MPO</td>
                                            <td>GF:068 (FS)</td>
                                            <td>10</td>
                                            <td>10</td>
                                        </tr>
                        
                            </tbody>
                </table>
        </div>
    </div>

    <!-- FUEL EFFECIENCY CHART -->
    <div class="col-md-4">    
        <img src="https://103.83.91.81/top10EC.png" alt="Fuel Effeciency Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">
    </div>
</div>

<!-- END FUEL EFFECIENCY CHART -->
    
<script>
        // Get context for the chart
        const ctx = document.getElementById('pieChart').getContext('2d');

        // Create the chart
        const pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Ignition ON', 'Ignition OFF', 'Fuel Refill', 'Fuel Theft', 'FenceOut', 'PowerCut', 'FS Temper'],
                datasets: [{
                    label: 'Event Distribution',
                    data: [27.5, 27.5, 15.7, 7.8, 5.9, 3.9, 11.8], // Percentages
                    backgroundColor: [
                        '#FFD700', // Yellow
                        '#9ACD32', // Yellow-Green
                        '#87CEEB', // Sky Blue
                        '#FF6347', // Red
                        '#FFB6C1', // Pink
                        '#FFA500', // Orange
                        '#98FB98'  // Light Green
                    ],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top', // Position of the legend
                        labels: {
                            color: '#000', // Legend text color
                        }
                    },
                    title: {
                        display: true,
                        text: 'Event Distribution'
                    }
                }
            }
        });
    </script>

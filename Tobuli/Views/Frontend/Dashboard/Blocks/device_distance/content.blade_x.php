
<!-- <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 1px; margin: 1px auto; max-width: 100%;">-->
    <!-- Department & Date Range Selection Block -->
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; background-color: gray;">
            <div class="row d-flex align-items-center gap-3">
                <!-- From Date -->
                <div class="col-md-2 col-xs-12">
                    <label for="from_date">From:</label>
                    <input type="date" class="form-control" name="from_date" id="from_date" value="2024-12-14">
                </div>

                <!-- To Date -->
                <div class="col-md-2 col-xs-12">
                    <label for="to_date">To:</label>
                    <input type="date" class="form-control" name="to_date" id="to_date" value="2024-12-14">
                </div>

                <!-- Department Dropdown -->
                <div class="form-group">
            <label for="default_unit_of_altitude" class="col-xs-12 col-sm-4 control-label&quot;">Department</label>
            <div class="col-xs-12 col-sm-8">
                <div class="btn-group bootstrap-select form-control">
                    <button type="button" class="btn dropdown-toggle btn-default" data-toggle="dropdown" role="button" data-id="default_unit_of_altitude" title="Meter" aria-expanded="false">
                        <span class="icon unit-altitude pull-left"></span>
                        <span class="filter-option pull-left">All</span>&nbsp;<span class="bs-caret">
                            <span class="caret"></span></span>
                    </button>
                    <div class="dropdown-menu open" role="combobox" style="max-height: 137.75px; overflow: hidden; min-height: 0px;">
                        <ul class="dropdown-menu inner" role="listbox" aria-expanded="false" style="max-height: 135.75px; overflow-y: auto; min-height: 0px;">
                            <li data-original-index="0" class="selected"><a tabindex="0" class="" data-tokens="null" role="option" aria-disabled="false" aria-selected="true">
                                <span class="  check-mark"></span><span class="text">MPO</span></a></li>
                                <li data-original-index="1"><a tabindex="0" class="" data-tokens="null" role="option" aria-disabled="false" aria-selected="false"><span class="  check-mark"></span>
                                <span class="text">Sanitation</span></a></li></ul><div class="bs-pagination"></div></div><select class="form-control" data-icon="icon unit-altitude" id="default_unit_of_altitude" name="default_unit_of_altitude" tabindex="-98"><option value="mt" selected="selected">MPO</option><option value="ft">Sanitation</option></select></div>
            </div>
        </div>
                <!-- Submit Button -->
                <div class="col-md-2 col-xs-2">
                    <button type="submit" class="btn btn-primary w-100 mt-4">Submit</button>
                    <span><a href="https://103.83.91.81/mastertable.html#" class="btn btn-primary w-100 mt-4" role="button">Analytics</a></span>
                </div>
            </div>
        </div>
                    
    <!-- Event Tiles Block -->
    <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; background-color: gray;">            
        <div class="col-sm-6">
            <div class="panel panel-transparent">
            <div class="panel-body" style="height: 370px; width: 100%; overflow-y: auto;">

                    <div class="table-responsive">
                        <div class="col-xs-8 col-sm-2 col-md-4">
                            <a class="stat-box" style="background-color:rgb(248, 53, 92)" href="https://103.83.91.81/fueltheft.html#" target="_blank">
                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">FUEL THEFT</div>
                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">10</div>
                                
                            </a>
                        </div>
                            <div class="col-xs-8 col-sm-2 col-md-4">
                            <a class="stat-box" style="background-color: #5DADE2" href="https://103.83.91.81/fstemper.html#"  target="_blank">
                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">FS TEMPER</div>
                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">2</div>
                            
                            </a>
                        </div>
                        <div class="col-xs-8 col-sm-2 col-md-4">
                            <a class="stat-box" style="background-color:rgb(91, 105, 238)" href="https://103.83.91.81/fuelrefill.html#"  target="_blank">
                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">FUEL REFILL</div>
                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">20</div>
                                
                            </a>
                        </div>
                        <div class="col-xs-8 col-sm-2 col-md-4">
                            <a class="stat-box" style="background-color: #EC7063" href="https://103.83.91.81/accon.html#" target="_blank">
                            
                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">Ignition ON</div>
                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">70</div>
                            
                            </a>
                        </div>
                        <div class="col-xs-8 col-sm-2 col-md-4">
                            <a class="stat-box" style="background-color: #D7DBDD" href="https://103.83.91.81/accoff.html#" target="_blank">
                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">Ignition OFF</div>
                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">70</div>
                            
                            </a>
                        </div>
                        <div class="col-xs-8 col-sm-2 col-md-4">
                            <a class="stat-box" style="background-color: #F7DC6F" href="https://103.83.91.81/fenceout.html#" target="_blank">
                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">FENCE OUT</div>
                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">10</div>
                                
                            </a>
                        </div>
                        <div class="col-xs-8 col-sm-2 col-md-4">
                            <a class="stat-box" style="background-color:rgb(247, 111, 170)" href="https://103.83.91.81/powercut.html#" target="_blank">
                                <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">POWER CUT</div>
                                <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">5</div>
                                
                            </a>
                        </div>
                        
                </div>
            </div>
        </div>        
    </div>
    
                    <div class="col-sm-4 col-md-6">
                        <div class="panel panel-transparent">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <div class="text-center">
                                        <img src="https://103.83.91.81/eventschart.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 70%; border-radius: 5px;">       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    </div>

                    
        <!-- Engine Stats Block -->  
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; 
    background-color: gray;"> 
    <div class="row d-flex align-items-center gap-3">         
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <a class="stat-box" style="background-color:rgb(207, 207, 47)" href="https://103.83.91.81/dashenginestats.html# target="_blank">
                            <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">Total Engine Hours</div>
                            <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">3455</div>
                        
                        </a>
                    </div>
                    
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <a class="stat-box" style="background-color:rgb(250, 190, 78)" href="https://103.83.91.81/enginestop.html# target="_blank">
                            <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">Total Stop Hours</div>
                            <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">6547</div>
                        
                        </a>
                    </div>
                    
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <a class="stat-box" style="background-color:rgb(104, 99, 125)" href="https://103.83.91.81/engineidle.html# target="_blank">
                            <div class="title" style="font-weight: bold; color:rgb(254, 255, 255)">Total Idle Hours</div>
                            <div class="count" style="font-weight: bold; color:rgb(254, 255, 255)">234</div>
                        
                        </a>
                    </div>

        </div>   
</div>             
        <!-- Fuel Stats Block -->  
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; 
    background-color: gray;"> 
                <div class="table-responsive;">
                    <table class="table table-list table-bordered" style="background-color:floralwhite;">
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
                  
                   
        <!-- Fuel Chart Block -->            
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; 
    background-color: gray;">
            <div class="col-md-12">
                <div>
                    <img src="https://103.83.91.81/fuelchart.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">
                    <p class="mt-2">Fuel consumption statistics displayed in a graphical format.</p>
                </div>
            </div>
        </div>

        <!-- Travel Stats Block --> 
        <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; 
    background-color: gray;">
                <div class="table-responsive">
                    <table class="table table-list table-bordered">
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
    <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%; 
    background-color: gray;">
            <div class="col-md-12">
                <div>
                    <img src="https://103.83.91.81/travelchart.png" alt="Fuel Consumption Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">
                    <p class="mt-2">Travel statistics displayed in a graphical format.</p>
                </div>
            </div>
        
    </div>

       

     <!-- Test Block --> 

<div class="dashboard-block col-sm-6 col-md-4 col-lg-3" id="block_latest_events" style="background-color: #FABE4E; font-size: 13px; color: black; font-weight: bold;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                <div class="pull-left">Fuel Theft Insights</div>
            </div>
        </div>
        <div class="panel-body dashboard-content" style="position: relative;">
            <table class="table">
            <thead>
                            <tr>
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
                    <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                <tr>
                <td>GF:068 (FS)</td>
                    <td>01-12-2024</td>
                    <td>50</td>
                    <td>MAP</td>
                </tr>
                </tbody>
            </table>
            
        </div>
    </div>
</div>

<!-- 2nd window -->
<div class="dashboard-block col-sm-6 col-md-4 col-lg-3" id="block_device_status_counts" style="background-color: #FABE4E; font-size: 13px; color: black; font-weight: bold;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                <div class="pull-left">Devices</div>
            </div>
        </div>
        <div class="panel-body dashboard-content" style="position: relative;">
            <table class="table">
            <thead>
                                    <tr>
                                        <th class="sorting_disabled">
                                        Statistics
                                        </th>
                                        <th class="sorting_disabled">
                                        Device
                                        </th>
                                        <th class="sorting_disabled">
                                        Value
                                        </th>
                                        <th class="sorting_disabled">
                                        Unit
                                        </th>
                                    </tr>
                                </thead>
                                    <tbody>
                                        <tr>
                                            <td>HIGHEST STOP DURATION</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>Hours</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST ENGINE HOURS</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>Hours</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST IDLE DURATION</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>Hours</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST FS TEMPER ALERT</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>NOS</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST POWER CUT ALERTS</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>NOS</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST DISTANCE TRAVELLED</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>KM</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST ZONE IN/OUT ALERT</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>NOS</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST FUEL Consumption</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>Litre</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST FUEL THEFT</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>Litre</td>
                                        </tr>
                                        <tr>
                                            <td>HIGHEST FUEL REFILL</td>
                                            <td>GF:068 (FS)</td>
                                            <td>50</td>
                                            <td>Litre</td>
                                        </tr>
                        
                                    </tbody>
            </table>
        </div>
    </div>
</div>

<!-- FUEL EFFECIENCY CHART -->
 <div class="container" style="border: 2px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px auto; max-width: 100%;">
            <div class="row d-flex align-items-center gap-3">
            <div class="col-md-6 col-xs-2">
            <div>
                    <img src="https://103.83.91.81/top10EC.png" alt="Fuel Effeciency Chart" class="img-fluid" style="max-width: 100%; border-radius: 5px;">
                </div>

           </div>
            </div>
</div>
<!-- END FUEL EFFECIENCY CHART -->
    </div>

    </div>


<!-- </div>-->


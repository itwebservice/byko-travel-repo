<?php
$login_id = $_SESSION['login_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$emp_id = $_SESSION['emp_id'];

//**Enquiries
$assigned_enq_count = mysqli_num_rows(mysqlQuery("select enquiry_id from enquiry_master where assigned_emp_id='$emp_id' and status!='Disabled' and financial_year_id='$financial_year_id'"));

$converted_count = 0;
$closed_count = 0;
$followup_count = 0;
$infollowup_count=0;

$sq_enquiry = mysqlQuery("select * from enquiry_master where status!='Disabled' and assigned_emp_id='$emp_id' and financial_year_id='$financial_year_id'");
while($row_enq = mysqli_fetch_assoc($sq_enquiry)){
  $sq_enquiry_entry = mysqli_fetch_assoc(mysqlQuery("select followup_status from enquiry_master_entries where entry_id=(select max(entry_id) as entry_id from enquiry_master_entries where enquiry_id='$row_enq[enquiry_id]')"));
  if($sq_enquiry_entry['followup_status']=="Dropped"){
    $closed_count++;
  }
  if($sq_enquiry_entry['followup_status']=="Converted"){
    $converted_count++;
  }
  if($sq_enquiry_entry['followup_status']=="Active"){
    $followup_count++;
  }
  if($sq_enquiry_entry['followup_status']=="In-Followup"){
    $infollowup_count++;
  }
}
?>
<div class="app_panel"> 
<div class="dashboard_panel panel-body">
      <div class="dashboard_widget_panel dashboard_widget_panel_first main_block mg_bt_25">
        
          <!-- Enquiry widgets -->
          <div class="dashboard_enqury_widget_panel main_block mg_bt_25">
            <div class="row">
              <div class="col-sm-3 col-xs-6">
                <div class="single_enquiry_widget main_block blue_enquiry_widget mg_bt_10_sm_xs" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                  <div class="col-xs-4 text-left">
                    <span class="dashboard-card-icon">
                      <i class="fa fa-cubes"></i>
                    </span>
                  </div>
                  <div class="col-xs-8 text-right">
                    <span class="single_enquiry_widget_amount dashboard-counter" data-max="<?php echo $assigned_enq_count; ?>"></span>
                  </div>
                  <div class="col-sm-12 single_enquiry_widget_amount">
                    Total Enquiries
                  </div>
                </div>
              </div>
              <div class="col-sm-3 col-xs-6">
                <div class="single_enquiry_widget main_block yellow_enquiry_widget mg_bt_10_sm_xs" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="col-xs-4 text-left">
                        <span class="dashboard-card-icon">
                          <i class="fa fa-folder-o"></i>
                        </span>
                      </div>
                      <div class="col-xs-8 text-right">
                        <span class="single_enquiry_widget_amount dashboard-counter" data-max="<?php echo $followup_count; ?>"></span>
                      </div>
                      <div class="col-sm-12 single_enquiry_widget_amount">
                        Active
                      </div>
                    </div>
                    <div class="col-sm-6" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                      <div class="col-xs-4 text-left">
                        <span class="dashboard-card-icon">
                          <i class="fa fa-folder-open-o"></i>
                        </span>
                      </div>
                      <div class="col-xs-8 text-right">
                        <span class="single_enquiry_widget_amount dashboard-counter" data-max="<?php echo $infollowup_count; ?>"></span>
                      </div>
                      <div class="col-sm-12 single_enquiry_widget_amount" style="padding-left:0px; padding-right:0px;">
                        In-Followup
                      </div>

                    </div>
                  </div>
                </div>
                <!-- <div class="single_enquiry_widget main_block gray_enquiry_widget mg_bt_10_sm_xs"  >
                </div> -->
              </div>
              <div class="col-sm-3 col-xs-6">
                <div class="single_enquiry_widget main_block green_enquiry_widget" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                  <div class="col-xs-4 text-left">
                    <span class="dashboard-card-icon">
                      <i class="fa fa-check-square-o"></i>
                    </span>
                  </div>
                  <div class="col-xs-8 text-right">
                    <span class="single_enquiry_widget_amount dashboard-counter" data-max="<?php echo $converted_count; ?>"></span>
                  </div>
                  <div class="col-sm-12 single_enquiry_widget_amount">
                    Converted
                  </div>
                </div>
              </div>
              <div class="col-sm-3 col-xs-6">
                <div class="single_enquiry_widget main_block red_enquiry_widget" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                  <div class="col-xs-4 text-left">
                    <span class="dashboard-card-icon">
                      <i class="fa fa-trash-o" aria-hidden="true"></i>
                    </span>
                  </div>
                  <div class="col-xs-8 text-right">
                    <span class="single_enquiry_widget_amount dashboard-counter" data-max="<?php echo $closed_count; ?>"></span>
                  </div>
                  <div class="col-sm-12 single_enquiry_widget_amount">
                    Dropped Enquiries
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Enquiry widgets End -->
            <div class="row">
              <?php
              $target = ($sq_emp['target']!='')?$sq_emp['target']:'0';
              $total_tour_fee = 0; $total_forex_cost = 0; $total_visa_cost = 0;
              $total_train_cost = 0;  $total_ticket_cost = 0;
              $total_pass_cost = 0;   $total_misc_cost = 0;
              $total_hotel_cost = 0;  $total_car_cost = 0;
              $total_exc_cost = 0;    $total_bus_cost = 0;

              $a_date = date('Y-m-d');
              $first_day_this_month= date("Y-m-1", strtotime($a_date));
              $last_day_this_month =  date("Y-m-t", strtotime($a_date));
              
              $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$emp_id'"));
              $cur_date= date('Y/m/d H:i');
              $search_form = date('Y-m-01 H:i',strtotime($cur_date));
              $search_to =  date('Y-m-t H:i',strtotime($cur_date));
              
              // Completed Target Group           
              $sq_group_bookings = mysqlQuery("select * from tourwise_traveler_details where tour_group_status!='Cancel' and emp_id = '$emp_id' and financial_year_id='$financial_year_id' and (DATE(form_date) between '$first_day_this_month' and '$last_day_this_month') and delete_status='0'");
              while($row_group_bookings = mysqli_fetch_assoc($sq_group_bookings)){

                $pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_group_bookings[traveler_group_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_group_bookings[traveler_group_id]' and status='Cancel'"));
                if($pass_count!=$cancelpass_count){
                  $total_tour_fee = $total_tour_fee + $row_group_bookings['net_total'];
                }
              }
              // Package
              $sq_package_booking = mysqlQuery("select * from package_tour_booking_master where emp_id ='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0' and (booking_date between '$first_day_this_month' and '$last_day_this_month')");
              while($row_package_booking = mysqli_fetch_assoc($sq_package_booking)){
                $pass_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_package_booking[booking_id]'"));
			          $cancle_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_package_booking[booking_id]' and status='Cancel'"));
                if($pass_count!=$cancle_count){
                  $total_tour_fee = $total_tour_fee + $row_package_booking['net_total'] ;
                }
              }
              // Bus
              $sq_bus = mysqlQuery("select * from bus_booking_master where emp_id='$emp_id' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month'  and financial_year_id='$financial_year_id' and delete_status='0'"); 
              while($sq_total_amount = mysqli_fetch_assoc($sq_bus)){

                $pass_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$sq_total_amount[booking_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$sq_total_amount[booking_id]' and status='Cancel'"));
                if( $pass_count!=$cancelpass_count){
                  $total_bus_cost = $total_bus_cost + $sq_total_amount['net_total'];
                } 
              }
              // Activity
              $sq_exc=mysqlQuery("select * from excursion_master where emp_id='$emp_id' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month'  and financial_year_id='$financial_year_id' and delete_status='0'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_exc)){
                $pass_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$sq_total_amount[exc_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$sq_total_amount[exc_id]' and status='Cancel'"));
                if($pass_count!=$cancelpass_count){
                  $total_exc_cost = $total_exc_cost + $sq_total_amount['exc_total_cost'];
                }
              }
              // Car rental
              $sq_car = mysqlQuery("select * from car_rental_booking where emp_id='$emp_id' and delete_status='0' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month' and financial_year_id='$financial_year_id' and status!='Cancel'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_car)){
                $total_car_cost = $total_car_cost + $sq_total_amount['total_fees'];
              }
              // Hotel
              $sq_hotel = mysqlQuery("select * from hotel_booking_master where emp_id='$emp_id' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month'  and financial_year_id='$financial_year_id' and delete_status='0'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_hotel )){
                $pass_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$sq_total_amount[booking_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$sq_total_amount[booking_id]' and status='Cancel'"));
                if($pass_count!=$cancelpass_count){
                  $total_hotel_cost = $total_hotel_cost + $sq_total_amount['total_fee'];
                }
              }
              // Miscellaneous
              $sq_misc = mysqlQuery("select * from miscellaneous_master where emp_id='$emp_id' and delete_status='0' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month' and financial_year_id='$financial_year_id'");
              while( $sq_total_amount = mysqli_fetch_assoc($sq_misc)){
                $pass_count = mysqli_num_rows(mysqlQuery("select * from miscellaneous_master_entries where misc_id='$sq_total_amount[misc_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from miscellaneous_master_entries where misc_id='$sq_total_amount[misc_id]' and status='Cancel'"));
                if($pass_count!=$cancelpass_count){
                  $total_misc_cost = $total_misc_cost + $sq_total_amount['misc_total_cost'];
                }
              }
              // Passport
              $sq_pass = mysqlQuery("select * from passport_master where emp_id='$emp_id' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month'  and financial_year_id='$financial_year_id'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_pass)){
                $pass_count = mysqli_num_rows(mysqlQuery("select * from passport_master_entries where passport_id='$sq_total_amount[passport_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from passport_master_entries where passport_id='$sq_total_amount[passport_id]' and status='Cancel'"));
                if($pass_count!=$cancelpass_count){
                  $total_pass_cost = $total_pass_cost + $sq_total_amount['passport_total_cost'];
                }
              }
              //Flight
              $sq_ticket = mysqlQuery("select * from ticket_master where emp_id='$emp_id' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month' and delete_status='0' and financial_year_id='$financial_year_id'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_ticket)){

                if($sq_total_amount['cancel_type'] != 1 || $sq_total_amount['cancel_type'] != 2 || $sq_total_amount['cancel_type'] != 3){

                  $cancel_estimate_data = json_decode($sq_total_amount['cancel_estimate']);
                  $cancel_estimate = (!isset($cancel_estimate_data)) ? 0 : $cancel_estimate_data[0]->ticket_total_cost;
                  $balance_amount = ($sq_total_amount['ticket_total_cost'] - floatval($cancel_estimate));
                  $total_ticket_cost = $total_ticket_cost + $balance_amount;
                }else{
                  $total_ticket_cost = $total_ticket_cost + $sq_total_amount['ticket_total_cost'];
                }
              }
              // Train
              $sq_train = mysqlQuery("select * from train_ticket_master where emp_id='$emp_id' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month'  and financial_year_id='$financial_year_id' and delete_status='0'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_train)){
                $pass_count = mysqli_num_rows(mysqlQuery("select * from train_ticket_master_entries where train_ticket_id='$sq_total_amount[train_ticket_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from train_ticket_master_entries where train_ticket_id='$sq_total_amount[train_ticket_id]' and status='Cancel'"));
                if($pass_count!=$cancelpass_count){
                  $total_train_cost = $total_train_cost + $sq_total_amount['net_total'];
                }
              }
              // Visa
              $sq_visa = mysqlQuery("select * from visa_master where emp_id='$emp_id' and delete_status='0' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month' and financial_year_id='$financial_year_id'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_visa)){
                $pass_count = mysqli_num_rows(mysqlQuery("select * from  visa_master_entries where visa_id='$sq_total_amount[visa_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from  visa_master_entries where visa_id='$sq_total_amount[visa_id]' and status='Cancel'"));
                if($pass_count!=$cancelpass_count){
                  $total_visa_cost = $total_visa_cost + $sq_total_amount['visa_total_cost'];
                }
              }
              // Forex
              $sq_forex = mysqlQuery("select * from forex_booking_master where emp_id='$emp_id' and DATE(created_at)<='$last_day_this_month' and DATE(created_at)>='$first_day_this_month'  and financial_year_id='$financial_year_id'");
              while($sq_total_amount = mysqli_fetch_assoc($sq_forex)){
                $total_forex_cost = $total_forex_cost + $sq_total_amount['net_total'];
              }
              $completed_amount = $total_forex_cost + $total_visa_cost + $total_train_cost + $total_ticket_cost + $total_pass_cost+ $total_misc_cost + $total_hotel_cost + $total_car_cost + $total_exc_cost + $total_bus_cost + $total_tour_fee;
              ?>
              <div class="col-sm-3"></div>
              <div class="col-md-6">
                <div class="dashboard_widget main_block">
                  <div class="dashboard_widget_title_panel np main_block widget_purp_title">
                    <div class="dashboard_widget_icon">
                      <i class="fa fa-star-half-o" aria-hidden="true"></i>
                    </div>
                    <div class="dashboard_widget_title_text">
                      <h3 class="">achievements</h3>
                      <p>Total Achievements Summary</p>
                    </div>
                  </div>
                  <div class="dashboard_widget_conetent_panel main_block">
                    <div class="col-sm-6" style="border-right: 1px solid #e6e4e5">
                      <div class="dashboard_widget_single_conetent">
                        <span class="dashboard_widget_conetent_amount"><?php echo number_format($target,2); ?></span>
                        <span class="dashboard_widget_conetent_text widget_blue_text">Target</span>
                      </div>
                    </div>
                    <div class="col-sm-6" style="border-right: 1px solid #e6e4e5">
                      <div class="dashboard_widget_single_conetent">
                        <span class="dashboard_widget_conetent_amount"><?php echo number_format($completed_amount,2); ?></span>
                        <span class="dashboard_widget_conetent_text widget_green_text">Completed</span>
                      </div>
                    </div>
                  </div>  
                </div>
              </div>
      </div>
    </div>


      <div id="payment_summary_html"></div>
      <!-- dashboard_tab -->
      <div id="id_proof2"></div>
          <div class="row">
            <div class="col-md-12">
              <div class="dashboard_tab text-center main_block">

                <!-- Nav tabs -->
                <ul class="nav nav-tabs responsive" role="tablist">
                  <li role="presentation" class="active"><a href="#enquiry_tab" aria-controls="enquiry_tab" role="tab" data-toggle="tab">Followups</a>
                  <li role="presentation" ><a href="#oncoming_tab" aria-controls="oncoming_tab" role="tab" data-toggle="tab">Tour Summary</a></li>
                  <li role="presentation" ><a href="#itinerary_tab" aria-controls="itinerary_tab" role="tab" data-toggle="tab">Tour Itinerary</a></li>
                  <li role="presentation"><a href="#task_tab" aria-controls="task_tab" role="tab" data-toggle="tab">Task</a></li>
						      <li role="presentation"><a href="#reminder_tab" aria-controls="reminder_tab" role="tab" data-toggle="tab">Reminders</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content responsive main_block">
                  
                    <!-- reminders tab  -->
                    <div role="tabpanel" class="tab-pane" id="reminder_tab">
                      <div class="row">
                        <div class="col-md-10 col-sm-6 mg_bt_10"></div>
                        <div class="col-md-2 col-sm-6 mg_bt_10">
                          <select id="reminder_option" name="reminder_option" title="Reminder Type" onchange="get_reminders(this.value);">
                            <option value="Payment">Payment</option>
                            <option value="Common">Common</option>
                          </select>
                        </div>
                      </div>
                      <div id='reminders_data'></div>
                    </div>
                    <!-- reminders summary End -->
                    <!-- itinerary tab  -->
                    <div role="tabpanel" class="tab-pane" id="itinerary_tab">
                      <div class="row">
                        <div class="col-md-9 col-sm-6 mg_bt_10"></div>
                        <div class="col-md-2 col-sm-6 mg_bt_10">
                          <input type="text" id="itinerary_from_date_filter" name="itinerary_from_date_filter" class="form-control" placeholder="*Date" title="Date" value="<?= date('d-m-Y') ?>">
                        </div>
                        <div class="col-md-1 text-left col-sm-6 mg_bt_10">
                          <button class="btn btn-excel btn-sm" onclick="itinerary_reflect()" data-toggle="tooltip" title="" data-original-title="Proceed"><i class="fa fa-arrow-right"></i></button>
                        </div>
                      </div>
                      <div id='itinerary_data'></div>
                    </div>
                    <!-- itinerary summary End -->
                    <!-- Ongoing  -->
                    <div role="tabpanel" class="tab-pane" id="oncoming_tab">
                      <div class="row">
                        <div class="col-md-7 col-sm-6 mg_bt_10"></div>
                          <div class="col-md-2 col-sm-6 mg_bt_10">
                            <input type="text" id="tfrom_date_filter" name="tfrom_date_filter" placeholder="Travel From Date" title="Travel From Date" onchange="get_to_date(this.id,'tto_date_filter')">
                          </div>
                          <div class="col-md-2 col-sm-6 mg_bt_10">
                            <input type="text" id="tto_date_filter" name="tto_date_filter" placeholder="Travel To Date" title="Travel To Date" onchange="validate_validDate('tfrom_date_filter','tto_date_filter')">
                          </div>
                          <div class="col-md-1 text-left col-sm-6 mg_bt_10">
                            <button class="btn btn-excel btn-sm" onclick="ongoing_tours_reflect()" data-toggle="tooltip" title="" data-original-title="Proceed"><i class="fa fa-arrow-right"></i></button>
                          </div>
                        </div>
                      <div id='ongoing_tours_data'></div>
                    </div>
				        	  <!-- Ongoing Tours summary End -->

                    <!-- Upcoming  -->
                    <div role="tabpanel" class="tab-pane" id="upcoming_tab">
                      <div id='upcoming_tours_data'></div>
			            	</div>
				        	  <!-- Upcoming Tours summary End -->

                    <!-- Enquiry & Followup summary -->
                    <div role="tabpanel" class="tab-pane active" id="enquiry_tab">
                          <div class="row text-left">
                            <div class="col-md-7"></div>
                            <div class="col-md-2 col-sm-6 mg_bt_10">
                              <input type="text" id="followup_from_date_filter" name="followup_from_date_filter" placeholder="Followup From D/T" title="Followup From D/T" onchange="get_to_datetime(this.id,'followup_to_date_filter')">
                            </div>
                            <div class="col-md-2 col-sm-6 mg_bt_10">
                              <input type="text" id="followup_to_date_filter" name="followup_to_date_filter" placeholder="Followup To D/T" title="Followup To D/T" onchange="validate_validDatetime('followup_from_date_filter','followup_to_date_filter')">
                            </div>
                            <div class="col-md-1 text-left col-sm-6 mg_bt_10">
                              <button class="btn btn-excel btn-sm" id="followup_reflect1" onclick="followup_reflect()" data-toggle="tooltip" title="" data-original-title="Proceed"><i class="fa fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <div id='followup_data'></div>
                        <div id="history_data"></div>
                  </div>
            <!-- Enquiry & Followup summary End -->

                      <!-- Weekly Task -->
                  <div role="tabpanel" class="tab-pane" id="task_tab">
                    <?php
                    $assigned_task_count = mysqli_num_rows(mysqlQuery("select task_id from tasks_master where emp_id='$emp_id' and task_status!='Disabled'"));
                    $can_task_count = mysqli_num_rows(mysqlQuery("select task_id from tasks_master where emp_id='$emp_id' and task_status='Cancelled'"));
                    $completed_task_count = mysqli_num_rows(mysqlQuery("select task_id from tasks_master where emp_id='$emp_id' and task_status='Completed'"));
                    ?>
                    <div class="dashboard_table dashboard_table_panel main_block">
                      <div class="row text-left">
                          <div class="col-md-12">
                            <div class="dashboard_table_heading main_block">
                              <div class="col-md-12 no-pad">
                                <h3>Allocated Tasks</h3>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="dashboard_table_body main_block">
                              <div class="col-sm-9 no-pad table_verflow table_verflow_two"> 
                                <div class="table-responsive no-marg-sm">
                                  <table class="table table-hover" style="margin: 0 !important;border: 0;">
                                    <thead>
                                      <tr class="table-heading-row">
                                        <th>Task_Name</th>
                                        <th>Task_Type</th>
                                        <th>ID/ENQ_NO.</th>
                                        <th>Assign_Date</th>
                                        <th>Due_Date&Time</th>
                                        <th>Status</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sq_task = mysqlQuery("select * from tasks_master where emp_id='$emp_id' and (task_status='Created' or task_status='Incomplete') order by task_id");
                                    while($row_task = mysqli_fetch_assoc($sq_task)){ 
                                        if($row_task['task_status'] == 'Created'){
                                          $bg='warning';
                                        }
                                        elseif($row_task['task_status'] == 'Incomplete' ){
                                          $bg='danger';
                                        }
                                        if($row_task['task_type'] == 'Package Tour'){

                                          $sq_booking = mysqli_fetch_assoc(mysqlQuery("select booking_date,booking_id from package_tour_booking_master where booking_id='$row_task[task_type_field_id]'"));
                                          $date = $sq_booking['booking_date'];
                                          $yr = explode("-", $date);
                                          $year =$yr[0];
                                          $booking_id = get_package_booking_id($sq_booking['booking_id'],$year);
                                        }
                                        else if($row_task['task_type'] == 'Group Tour'){

                                          $sq_booking = mysqli_fetch_assoc(mysqlQuery("select form_date,id from tourwise_traveler_details where id='$row_task[task_type_field_id]'"));
                                          $sq_tour_group = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where group_id='$row_task[task_type_field_id]'"));
                                          $sq_tour = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id='$sq_tour_group[tour_id]'"));
                                          $date = $sq_booking['form_date'];
                                          $yr = explode("-", $date);
                                          $year = $yr[0];
                                          $booking_id = $sq_tour['tour_name'].'('.date('d-m-Y', strtotime($sq_tour_group['from_date'])).' to '.date('d-m-Y', strtotime($sq_tour_group['to_date'])).')';
                                        }
                                        else if($row_task['task_type'] == 'Enquiry'){

                                          $sq_booking = mysqli_fetch_assoc(mysqlQuery("select enquiry_date,enquiry_id from enquiry_master where enquiry_id='$row_task[task_type_field_id]'"));
                                          $date = $sq_booking['enquiry_date'];
                                          $yr = explode("-", $date);
                                          $year =$yr[0];
                                          $booking_id = get_enquiry_id($sq_booking['enquiry_id'],$year);
                                        }
                                        else{
                                          $booking_id = 'NA';
                                        }
                                    ?>
                                        <tr class="odd">
                                          <td><?php echo $row_task['task_name']; ?></td>
                                          <td><?php echo $row_task['task_type']; ?></td>
                                          <td><?php echo $booking_id; ?></td>
                                          <td><?php echo get_date_user($row_task['created_at']); ?></td>
                                          <td><?php echo get_datetime_user($row_task['due_date']); ?></td>
                                          <td><span class="<?= $bg ?>"><?php echo $row_task['task_status']; ?></span></td>
                                        </tr>
                                      <?php } ?>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                              <div class="col-sm-3 no-pad">
                                <div class="table_side_widget_panel main_block">
                                  <div class="table_side_widget_content main_block">
                                    <div class="col-xs-12" style="border-bottom: 1px solid hsla(180, 100%, 30%, 0.25)">
                                      <div class="table_side_widget">
                                        <div class="table_side_widget_amount"><?= $assigned_task_count ?></div>
                                        <div class="table_side_widget_text widget_blue_text">Total Task</div>
                                      </div>
                                    </div>
                                    <div class="col-xs-6" style="border-bottom: 1px solid hsla(180, 100%, 30%, 0.25)">
                                      <div class="table_side_widget">
                                        <div class="table_side_widget_amount"><?= $completed_task_count ?></div>
                                        <div class="table_side_widget_text widget_green_text">Task Completed</div>
                                      </div>
                                    </div>
                                    <div class="col-xs-6" style="border-bottom: 1px solid hsla(180, 100%, 30%, 0.25)">
                                      <div class="table_side_widget">
                                        <div class="table_side_widget_amount"><?= $can_task_count ?></div>
                                        <div class="table_side_widget_text widget_red_text">Task Cancelled</div>
                                      </div>
                                    </div>
                                    <div class="col-xs-12">
                                      <div class="table_side_widget">
                                        <div class="table_side_widget_amount"><?= $assigned_task_count-$completed_task_count-$can_task_count ?></div>
                                        <div class="table_side_widget_text widget_yellow_text">Task Pending</div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                      </div>
                    </div> 
                  </div>
                <!-- Weekly Task  End-->
                <!-- Monthly Incentive -->
                  <div role="tabpanel" class="tab-pane" id="incentive_tab">
                      <div class="dashboard_table dashboard_table_panel main_block">
                        <div class="row text-left">
                          <div class="col-md-12">
                            <div class="dashboard_table_heading main_block">
                              <div class="col-md-8 no-pad">
                                <h3 style="cursor: pointer;" onclick="window.open('<?= BASE_URL ?>view/booker_incentive/booker_incentive.php', 'My Window');">Incentive/Commission</h3>
                              </div>
                              <div class="col-md-2 col-xs-12 no-pad-sm mg_bt_10_sm_xs">
                                  <input type="text" id="from_date" name="from_date" class="form-control" placeholder="From Date" title="From Date" onchange="booking_list_reflect()">
                              </div>
                              <div class="col-md-2 col-xs-12 no-pad-sm">
                                  <input type="text" id="to_date" name="to_date" class="form-control" placeholder="To Date" title="To Date" onchange="booking_list_reflect()">
                              </div>
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="dashboard_table_body main_block">
                              <div class="col-md-12 no-pad  table_verflow"> 
                                  <div id="div_booker_incentive_reflect">
                                  </div>                     
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                  </div>
                  <!-- Incentive End --> 
                </div>
              </div>
            </div>
          </div>
      </div>
  </div>
<script type="text/javascript">
	$('#tfrom_date_filter,#tto_date_filter,#itinerary_from_date_filter').datetimepicker({ format: 'd-m-Y', timepicker:false });
	$('#followup_from_date_filter, #followup_to_date_filter').datetimepicker({format:'d-m-Y H:i' });
  $('#from_date, #to_date').datetimepicker({ timepicker:false, format:'d-m-Y' });
  
	itinerary_reflect();
	function itinerary_reflect() {
		var from_date = $('#itinerary_from_date_filter').val();
		$.post('itinerary/index.php', { date: from_date }, function(data) {
			$('#itinerary_data').html(data);
		});
	}
function send_sms(id,tour_type,emp_id,contact_no, name){
	
	var base_url = $('#base_url').val();
	var draft = "Dear "+name+",We hope that you are enjoying your trip. It will be a great source of input from you, if you can share your tour feedback with us, so that we can serve you even better.Thank you."
	$('#send_btn').button('loading');
    $.ajax({
		type:'post',
		url: base_url+'controller/dashboard_sms_send.php',
		data:{ draft : draft,enquiry_id : id,mobile_no : contact_no},
		success: function(message){
			msg_alert("Feedback sent successfully");
			$('#send_btn').button('reset'); 
		}
    });
    web_whatsapp_open(contact_no,name);
}
function checklist_update(count,booking_id,tour_type){
	$('#checklist-'+count).button('loading');
	$.post('sales/update_checklist.php', { booking_id:booking_id,tour_type:tour_type}, function(data){
    $('#checklist-'+count).button('reset');
    $('#id_proof2').html(data);
	});
}

function web_whatsapp_open(mobile_no, name) {

  var app_name = $('#app_name').val();
  var app_contact_no = $('#app_contact_no').val();
  var link = 'https://web.whatsapp.com/send?phone=' + mobile_no + '&text=Dear%20' + encodeURI(name) + ',%0aWe%20hope%20that%20you%20are%20enjoying%20your%20trip.%20It%20will%20be%20a%20great%20source%20of%20input%20from%20you,%20if%20you%20can%20share%20your%20tour%20feedback%20with%20us,%20so%20that%20we%20can%20serve%20you%20even%20better.%0aThank%20you,%0a'+app_name+' ';
  link += encodeURIComponent(app_contact_no);
  window.open(link);
}

function whatsapp_wishes(number, name) {

  var app_name = $('#app_name').val();
  var app_contact_no = $('#app_contact_no').val();
  var msg = encodeURI("Dear " + name + ",\nMay this trip turns out to be a wonderful treat for you and may you create beautiful memories throughout this trip to cherish forever. Wish you a very happy and safe journey!!\nThank you,\n"+app_name+" ");
  msg += encodeURIComponent(app_contact_no);
  window.open('https://web.whatsapp.com/send?phone=' + number + '&text=' + msg);
}
	function followup_reflect(){
		var from_date = $('#followup_from_date_filter').val();
		var to_date = $('#followup_to_date_filter').val();
		$.post('sales/followup_list_reflect.php', { from_date : from_date,to_date:to_date }, function(data){
			$('#followup_data').html(data);
		});
	}
  followup_reflect();
  function booking_list_reflect()
  {
    var from_date = $('#from_date').val();
    var to_date = $('#to_date').val();
    $.post('sales/incentive_list_reflect.php', { from_date : from_date, to_date : to_date }, function(data){
      $('#div_booker_incentive_reflect').html(data);
    });
  }
  booking_list_reflect();
	function display_history(enquiry_id)
	{
    $('#history-'+enquiry_id).button('loading');
		$.post('admin/followup_history.php', { enquiry_id : enquiry_id }, function(data){
		$('#history_data').html(data);
    $('#history-'+enquiry_id).button('reset');
		});
  }
  function Followup_update(enquiry_id)
  {
    $('#followup-'+enquiry_id).button('loading');
    $.post('admin/followup_update.php', { enquiry_id : enquiry_id }, function(data){
      $('#history_data').html(data);
      $('#followup-'+enquiry_id).button('reset');
    });
    
  }
function followup_type_reflect(followup_status){
	$.post('admin/followup_type_reflect.php', {followup_status : followup_status}, function(data){
		$('#followup_type').html(data);
	}); 
}
ongoing_tours_reflect();
function ongoing_tours_reflect(){
		var from_date = $('#tfrom_date_filter').val();
		var to_date = $('#tto_date_filter').val();
		$.post('../dashboard/tour_summary.php', {from_date: from_date, to_date: to_date }, function(data) {
			$('#ongoing_tours_data').html(data);
		});
}
	function view_payment_summary(count, booking_id, tour_type){
		
		$('#payment-' + count).prop('disabled',true);
		$('#payment-' + count).button('loading');
		$.post('../dashboard//view_payment_smmary.php', {
			count: count,
			booking_id: booking_id,
			tour_type: tour_type
		}, function(data) {
			$('#payment-' + count).prop('disabled',false);
			$('#payment-' + count).button('reset');
			$('#payment_summary_html').html(data);
		});
	}
	function get_reminders(type){

		if(type == 'Payment'){
			var url ='payment_index.php';
		}else{
			var url ='common_index.php';
		}
		$.post('../dashboard/reminders/'+url, { }, function(data) {
			$('#reminders_data').html(data);
		});
	}
	get_reminders('Payment');
</script>
<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>
<script type="text/javascript">
    (function($) {
        fakewaffle.responsiveTabs(['xs', 'sm']);
    })(jQuery);
  </script>
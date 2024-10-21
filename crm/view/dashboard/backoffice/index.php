<?php
$login_id = $_SESSION['login_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
//**Enquiries
$assigned_enq_count = mysqli_num_rows(mysqlQuery("select enquiry_id from enquiry_master where assigned_emp_id='$emp_id' and status!='Disabled' and financial_year_id='$financial_year_id'"));

$converted_count = 0;
$closed_count = 0;
$infollowup_count = 0;
$followup_count = 0;

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

	<div class="dashboard_enqury_widget_panel main_block mg_bt_25">
            <div class="row">
                <div class="col-sm-3 col-xs-6" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                  <div class="single_enquiry_widget main_block blue_enquiry_widget mg_bt_10_sm_xs">
                    <div class="col-xs-3 text-left">
                      <i class="fa fa-cubes"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                      <span class="single_enquiry_widget_amount dashboard-counter" data-max=<?php echo $assigned_enq_count; ?>></span>
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
                </div>
                <div class="col-sm-3 col-xs-6" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                  <div class="single_enquiry_widget main_block green_enquiry_widget">
                    <div class="col-xs-3 text-left">
                      <i class="fa fa-check-square-o"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                      <span class="single_enquiry_widget_amount dashboard-counter" data-max=<?php echo $converted_count; ?>></span>
                    </div>
                    <div class="col-sm-12 single_enquiry_widget_amount">
                      Converted
                    </div>
                  </div>
                </div>
                <div class="col-sm-3 col-xs-6" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
                  <div class="single_enquiry_widget main_block red_enquiry_widget">
                    <div class="col-xs-3 text-left">
                      <i class="fa fa-trash-o"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                      <span class="single_enquiry_widget_amount dashboard-counter" data-max=<?php echo $closed_count; ?>></span>
                    </div>
                    <div class="col-sm-12 single_enquiry_widget_amount">
                      Dropped Enquiries
                    </div>
                  </div>
                </div>
            </div>
    </div>

    <div id="payment_summary_html"></div>
    <div class="row">
      <div class="col-md-12">
      <div id="id_proof2"></div>
        <div class="dashboard_tab text-center main_block">

          <!-- Nav tabs -->
          <ul class="nav nav-tabs responsive" role="tablist">
            <li role="presentation"  class="active"><a href="#week_fol_tab" aria-controls="week_fol_tab" role="tab" data-toggle="tab">Followups</a></li>
            <li role="presentation"><a href="#oncoming_tab" aria-controls="oncoming_tab" role="tab" data-toggle="tab">Tour Summary</a></li>
            <li role="presentation" ><a href="#itinerary_tab" aria-controls="itinerary_tab" role="tab" data-toggle="tab">Tour Itinerary</a></li>
            <li role="presentation"><a href="#week_task_tab" aria-controls="week_task_tab" role="tab" data-toggle="tab">Tasks</a></li>
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

            <!-- Weekly Followups -->
            <div role="tabpanel" class="tab-pane active" id="week_fol_tab">
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
            <!-- Weekly Followups end -->
            <!-- Weekly Task -->
            <div role="tabpanel" class="tab-pane" id="week_task_tab">
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
                                  <th>ID/Enq_No.</th>
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
            <!-- Weekly Task end -->
          </div>
        </div>
      </div>
    </div>
</div>
</div>

<script type="text/javascript">
  $('#followup_from_date_filter, #followup_to_date_filter').datetimepicker({format:'d-m-Y H:i' });
	$('#tfrom_date_filter,#tto_date_filter,#itinerary_from_date_filter').datetimepicker({ format: 'd-m-Y', timepicker:false });
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
  function checklist_update(count,booking_id,tour_type){
	$('#checklist-'+count).button('loading');
	$.post('backoffice/update_checklist.php', { booking_id:booking_id,tour_type:tour_type}, function(data){
		$('#id_proof2').html(data);
    $('#checklist-'+count).button('reset');
	});
}
	function display_history(enquiry_id)
	{
		$.post('admin/followup_history.php', { enquiry_id : enquiry_id }, function(data){
		$('#history_data').html(data);
		});
  }
  function Followup_update(enquiry_id)
{
  $.post('admin/followup_update.php', { enquiry_id : enquiry_id }, function(data){
    $('#history_data').html(data);
  });
}
function followup_type_reflect(followup_status){
	$.post('admin/followup_type_reflect.php', {followup_status : followup_status}, function(data){
		$('#followup_type').html(data);
	}); 
}
	followup_reflect();
	function followup_reflect(){
		var from_date = $('#followup_from_date_filter').val();
		var to_date = $('#followup_to_date_filter').val();
		$.post('backoffice/followup_list_reflect.php', { from_date : from_date,to_date:to_date }, function(data){
			$('#followup_data').html(data);
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
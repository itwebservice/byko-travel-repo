<?php
$login_id = $_SESSION['login_id'];
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];

$q = "select branch_status from branch_assign where link='package_booking/booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';

$q = "select branch_status from branch_assign where link='booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status2 = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';

$q = "select branch_status from branch_assign where link='attractions_offers_enquiry/enquiry/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status1 = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
//**Enquiries
$q1 = "select enquiry_id from enquiry_master where financial_year_id='$financial_year_id' and status!='Disabled'";
if ($branch_status1 == 'yes') {
  if ($role == 'Branch Admin' || $role == 'Accountant' || $role == 'Hr' || $role == 'Hr' || $role_id > '7') {
    $q1 .= " and branch_admin_id = '$branch_admin_id'";
  } elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
    $q1 .= " and assigned_emp_id='$emp_id' and branch_admin_id = '$branch_admin_id'";
  }
} elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
  $q1 .= " and assigned_emp_id='$emp_id'";
}
$assigned_enq_count = mysqli_num_rows(mysqlQuery($q1));
$converted_count = 0;
$closed_count = 0;
$followup_count = 0;
$infollowup_count = 0;
$q2 = "select enquiry_id from enquiry_master where financial_year_id='$financial_year_id' and status!='Disabled'";
if ($branch_status1 == 'yes') {
  if ($role == 'Branch Admin' || $role == 'Accountant' || $role == 'Hr' || $role == 'Hr' || $role_id > '7') {
    $q2 .= " and branch_admin_id = '$branch_admin_id'";
  } elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
    $q2 .= " and assigned_emp_id='$emp_id' and branch_admin_id = '$branch_admin_id'";
  }
} elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
  $q2 .= " and assigned_emp_id='$emp_id'";
}
$sq_enquiry = mysqlQuery($q2);
while ($row_enq = mysqli_fetch_assoc($sq_enquiry)) {
  $sq_enquiry_entry = mysqli_fetch_assoc(mysqlQuery("select followup_status from enquiry_master_entries where entry_id=(select max(entry_id) as entry_id from enquiry_master_entries where enquiry_id='$row_enq[enquiry_id]')"));
  if ($sq_enquiry_entry['followup_status'] == "Dropped") {
    $closed_count++;
  }
  if ($sq_enquiry_entry['followup_status'] == "Converted") {
    $converted_count++;
  }
  if ($sq_enquiry_entry['followup_status'] == "Active") {
    $followup_count++;
  }
  if ($sq_enquiry_entry['followup_status'] == "In-Followup") {
    $infollowup_count++;
  }
}


?>
<div class="app_panel">
  <div class="dashboard_panel panel-body">
    <input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status1 ?>">
    <div class="dashboard_enqury_widget_panel main_block mg_bt_25">
      <div class="row">
        <div class="col-sm-3 col-xs-6" onclick="window.open('<?= BASE_URL ?>view/attractions_offers_enquiry/enquiry/index.php', 'My Window');">
          <div class="single_enquiry_widget main_block blue_enquiry_widget mg_bt_10_sm_xs">
            <div class="col-xs-4 text-left">
              <i class="fa fa-cubes"></i>
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


    <div id="history_data"></div>
    <div id="id_proof2"></div>
    <div id="payment_summary_html"></div>
    <!-- dashboard_tab -->

    <div class="row">
      <div class="col-md-12">
        <div class="dashboard_tab text-center main_block">

          <!-- Nav tabs -->
          <ul class="nav nav-tabs responsive" role="tablist">
            <li role="presentation" class="active"><a href="#enquiry_tab" aria-controls="enquiry_tab" role="tab" data-toggle="tab">Followups</a></li>
            <li role="presentation"><a href="#oncoming_tab" aria-controls="oncoming_tab" role="tab" data-toggle="tab">Tour Summary</a></li>
            <li role="presentation" ><a href="#itinerary_tab" aria-controls="itinerary_tab" role="tab" data-toggle="tab">Tour Itinerary</a></li>
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
            <!-- Ongoing FIT Tours -->
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
            <!-- Ongoing FIT Tour summary End -->

            <!-- Upcoming FIT Tours -->
            <div role="tabpanel" class="tab-pane" id="upcoming_tab">
              <div id='upcoming_tours_data'></div>
            </div>
            <!-- Upcoming FIT Tour summary End -->
            <!--  FIT Summary -->
            <div role="tabpanel" class="tab-pane" id="fit_tab">
              <?php
              $bg = '';
              $query = mysqli_fetch_assoc(mysqlQuery("select max(booking_id) as booking_id from package_tour_booking_master"));
              $sq_package = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$query[booking_id]' and delete_status='0'"));
              $sq_entry = mysqlQuery("select * from package_travelers_details where booking_id='$query[booking_id]'");
              ?>
              <div class="dashboard_table dashboard_table_panel main_block mg_bt_25">
                <div class="row text-left">
                  <div class="">
                    <div class="dashboard_table_heading main_block">
                      <div class="col-md-2">
                        <h3>Package Tours</h3>
                      </div>
                      <div class="col-md-3 col-sm-4 col-md-push-7">
                        <select style="border-color: #009898; width: 100%;" id="package_booking_id" onchange="package_list_reflect(this.id)">
                          <?php
                          $query = "select * from package_tour_booking_master where 1 and financial_year_id='$financial_year_id' and delete_status='0'";
                          $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='package_booking/booking/index.php'"));
                          $branch_status = $sq_branch['branch_status'];
                          if ($branch_status == 'yes') {
                            if ($role == 'Branch Admin' || $role == 'Accountant' || $role == 'Hr' || $role_id > '7') {
                              $query .= " and branch_admin_id = '$branch_admin_id'";
                            } elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
                              $query .= " and emp_id='$emp_id' and branch_admin_id = '$branch_admin_id'";
                            }
                          } elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
                            $query .= " and emp_id='$emp_id'";
                          }
                          $query .= " order by booking_id desc";
                          $sq_booking = mysqlQuery($query);
                          while ($row_booking = mysqli_fetch_assoc($sq_booking)) {
                            $date = $row_booking['booking_date'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
                            if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                          ?>
                              <option value="<?php echo $row_booking['booking_id'] ?>"><?php echo get_package_booking_id($row_booking['booking_id'], $year) . "-" . " " . $sq_customer['company_name']; ?></option>
                            <?php } else { ?>
                              <option value="<?php echo $row_booking['booking_id'] ?>"><?php echo get_package_booking_id($row_booking['booking_id'], $year) . "-" . " " . $sq_customer['first_name'] . " " . $sq_customer['last_name']; ?></option>
                          <?php
                            }
                          } ?>
                        </select>
                      </div>
                      <div id="package_div_list">
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <!--  FIT Summary End -->
            <!--  GIT Summary -->
            <div role="tabpanel" class="tab-pane" id="git_tab">
              <?php
              $bg = '';
              $query = mysqli_fetch_assoc(mysqlQuery("select max(id) as booking_id from tourwise_traveler_details"));
              $sq_package = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$query[booking_id]' and delete_status='0'"));
              $sq_tour_name = mysqli_fetch_assoc(mysqlQuery("select  * from tour_master where tour_id = '$sq_package[tour_id]'"));
              $sq_traveler_personal_info = mysqli_fetch_assoc(mysqlQuery("select * from traveler_personal_info where tourwise_traveler_id='$query[booking_id]'"));
              ?>
              <div class="dashboard_table dashboard_table_panel main_block mg_bt_25">
                <div class="row text-left">
                  <div class="">
                    <div class="dashboard_table_heading main_block">
                      <div class="col-md-2">
                        <h3>Group Tours</h3>
                      </div>
                      <div class="col-md-3 col-sm-4 col-md-push-7">
                        <select style="border-color: #009898; width: 100%;" id="group_booking_id" onchange="group_list_reflect(this.id)">
                          <?php
                          $query = "select * from tourwise_traveler_details where 1 and financial_year_id='$financial_year_id' and delete_status='0'";
                          $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='booking/index.php'"));
                          $branch_status = $sq_branch['branch_status'];
                          if ($branch_status2 == 'yes') {
                            if ($role == 'Branch Admin' || $role == 'Accountant' || $role == 'Hr' || $role_id > '7') {
                              $query .= " and branch_admin_id = '$branch_admin_id'";
                            } elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
                              $query .= " and emp_id='$emp_id' and branch_admin_id = '$branch_admin_id'";
                            }
                          } elseif ($role != 'Admin' && $role != 'Branch Admin' && $role != 'Hr' && $role_id != '7' && $role_id < '7') {
                            $query .= " and emp_id='$emp_id'";
                          }
                          $query .= " order by id desc";
                          $sq_booking = mysqlQuery($query);
                          while ($row_booking = mysqli_fetch_assoc($sq_booking)) {

                            $date = $row_booking['form_date'];
                            $yr = explode("-", $date);
                            $year = $yr[0];

                            $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
                            if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                          ?>
                              <option value="<?php echo $row_booking['id'] ?>"><?php echo get_group_booking_id($row_booking['id'], $year) . "-" . " " . $sq_customer['company_name']; ?></option>
                            <?php } else { ?>

                              <option value="<?= $row_booking['id'] ?>"><?= get_group_booking_id($row_booking['id'], $year) ?> : <?= $sq_customer['first_name'] . ' ' . $sq_customer['last_name'] ?></option>
                          <?php
                            }
                          } ?>
                        </select>
                      </div>

                      <div id="group_div_list">
                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--  GIT Summary End -->
            <!-- Enquiry & Followup summary -->
            <div role="tabpanel" class="tab-pane active" id="enquiry_tab">
                <div class="row text-left">
                  <div class="col-md-7">    </div>
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
            </div>
          </div>
        </div>
        <!-- Enquiry & Followup summary end -->
      </div>
    </div>
  </div>
</div>
</div>
</div>
<script type="text/javascript">
	$('#tfrom_date_filter,#tto_date_filter,#itinerary_from_date_filter').datetimepicker({ format: 'd-m-Y', timepicker:false });
  $('#followup_from_date_filter, #followup_to_date_filter').datetimepicker({
    format: 'd-m-Y H:i'
  });
  $('#group_booking_id,#package_booking_id').select2();

	itinerary_reflect();
	function itinerary_reflect() {
		var from_date = $('#itinerary_from_date_filter').val();
		$.post('itinerary/index.php', { date: from_date }, function(data) {
			$('#itinerary_data').html(data);
		});
	}
  function send_sms(id, tour_type, emp_id, contact_no, name) {
    var base_url = $('#base_url').val();
    var draft = "Dear " + name + ",We hope that you are enjoying your trip. It will be a great source of input from you, if you can share your tour feedback with us, so that we can serve you even better.Thank you."
    $('#send_btn').button('loading');
    $.ajax({
      type: 'post',
      url: base_url + 'controller/dashboard_sms_send.php',
      data: {
        draft: draft,
        enquiry_id: id,
        mobile_no: contact_no
      },
      success: function(message) {
        msg_alert("Feedback sent successfully");
        $('#send_btn').button('reset');
      }
    });
    web_whatsapp_open(contact_no, name);
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

  function checklist_update(count, booking_id, tour_type, aemp_id) {
    $('#checklist-' + count).button('loading');
    $.post('branch_admin/update_checklist.php', {
      booking_id: booking_id,
      tour_type: tour_type,
      aemp_id: aemp_id
    }, function(data) {
      $('#id_proof2').html(data);
      $('#checklist-' + count).button('reset');
    });
  }

  function package_list_reflect() {
    var booking_id = $('#package_booking_id').val();
    $.post('branch_admin/package_list_reflect.php', {
      booking_id: booking_id
    }, function(data) {
      $('#package_div_list').html(data);
    });
  }
  package_list_reflect();

  function group_list_reflect() {
    var booking_id = $('#group_booking_id').val();
    $.post('branch_admin/group_list_reflect.php', {
      booking_id: booking_id
    }, function(data) {
      $('#group_div_list').html(data);
    });
  }
  group_list_reflect();

  function display_history(enquiry_id, count) {

    $('#history-' + count).button('loading');
    $.post('branch_admin/followup_history.php', {
      enquiry_id: enquiry_id
    }, function(data) {
      $('#history_data').html(data);
      $('#history-' + count).button('reset');
    });
  }

  function followup_type_reflect(followup_status) {
    $.post('admin/followup_type_reflect.php', {
      followup_status: followup_status
    }, function(data) {
      $('#followup_type').html(data);
    });
  }
  followup_reflect();

  function followup_reflect() {
    var from_date = $('#followup_from_date_filter').val();
    var to_date = $('#followup_to_date_filter').val();
    $.post('branch_admin/followup_list_reflect.php', {
      from_date: from_date,
      to_date: to_date
    }, function(data) {
      $('#followup_data').html(data);
    });
  }
  ongoing_tours_reflect();

  function ongoing_tours_reflect() {
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

  function Followup_update(enquiry_id, count) {
    $('#followup-' + count).button('loading');
    $.post('admin/followup_update.php', {
      enquiry_id: enquiry_id
    }, function(data) {
      $('#history_data').html(data);
      $('#followup-' + count).button('reset');
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
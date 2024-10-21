<?php
include '../../../../model/model.php';
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];

$flight_details = json_decode($_POST['flight_details']);
$branch_status = isset($_POST['branch_status']) ? $_POST['branch_status'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';
$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
$count = isset($_POST['count']) ? $_POST['count'] : '';
$pass_entry_id = isset($_POST['entry_id']) ? $_POST['entry_id'] : '';

$type_of_tour = (isset($flight_details[0])) ? $flight_details[0]->type_of_tour : '';
$departure_datetime_arr = (isset($flight_details[0])) ? $flight_details[0]->departure_datetime_arr : [];
$loop_items = (isset($flight_details[0]->departure_datetime_arr)) ? sizeof($flight_details[0]->departure_datetime_arr) : '1';
$button_name = ($type == 'update') ? 'Update' : 'Save';
$status = '';
if($pass_entry_id!=''){
	$sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$pass_entry_id'"));
	$status = $sq_pass['status'];
}
?>
<div class="modal fade" id="flight_details_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document" style="min-width: 90%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Add Flight Ticket Details for <?= $first_name.' '.$last_name ?></h4>
			</div>
			<div class="modal-body">
                <form id="frm_flight_details">
					<input type="hidden" id="count" name="count" value="<?= $count ?>" />
					<input type="hidden" id="type" name="type" value="<?= $type ?>" />
					<?php
					if($count=='1' || $count=='1u'){
						?>
						<div class="row mg_bt_20">
							<div class="col-md-3">
								<select name="quotation_id" id="quotation_id" style="width:100%" onchange="get_quotation_details(this)" class="form-control">
								<option value="">Select Quotation</option>
								<?php
								if($role == 'Admin'){
									$query = "SELECT * FROM `flight_quotation_master` where status='1' order by quotation_id desc";
								}
								else{
									if($branch_status=='yes'){
										if($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
										$query = "select * from flight_quotation_master where status='1' and branch_admin_id='$branch_admin_id' order by quotation_id desc";
										}
										elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
										$query = "select * from flight_quotation_master where status='1' and emp_id='$emp_id' and branch_admin_id='$branch_admin_id' order by quotation_id desc";
										}
									}
									elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
									$query = "select * from flight_quotation_master where status='1' and emp_id='$emp_id' order by quotation_id desc";
									}  
									elseif($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
										$query = "select * from flight_quotation_master where status='1' order by quotation_id desc";
									}
								}  
								$sq_enq = mysqlQuery($query);   
								while($row_enq = mysqli_fetch_assoc($sq_enq)){
									$quotation_date = $row_enq['quotation_date'];
									$yr = explode("-", $quotation_date);
									$year =$yr[0];
									?>
									<option value="<?= $row_enq['quotation_id'] ?>"><?= get_quotation_id($row_enq['quotation_id'],$year ).": ".$row_enq['customer_name'] ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
					<?php } ?>
                    <div class="row mg_bt_20">
                        <div class="col-md-8 col-sm-12 col-xs-12">
							<strong>Type Of Trip :</strong>&nbsp;&nbsp;&nbsp;
							<?php $chk = ($type_of_tour=="One Way") ? "checked" : "" ?>
							<input type="radio" name="type_of_tour" id="type_of_tour-one_way" value="One Way" <?= $chk ?>>&nbsp;&nbsp;<label for="type_of_tour-one_way">One Way</label>
							&nbsp;&nbsp;&nbsp;
							
							<?php $chk = ($type_of_tour=="Round Trip") ? "checked" : "" ?>
							<input type="radio" name="type_of_tour" id="type_of_tour-round_trip" value="Round Trip" <?= $chk ?>>&nbsp;&nbsp;<label for="type_of_tour-round_trip">Round Trip</label>
							&nbsp;&nbsp;&nbsp;

							<?php $chk = ($type_of_tour=="Multi City") ? "checked" : "" ?>
							<input type="radio" name="type_of_tour" id="type_of_tour-multi_city" value="Multi City" <?= $chk ?>>&nbsp;&nbsp;<label for="type_of_tour-multi_city">Multi City</label>
							&nbsp;&nbsp;&nbsp;
        					<button	button type="button" class="btn btn-excel btn-sm" title="Add Airport/Airline" onclick="airport_airline_save_modal()"><i class="fa fa-plus"></i></button>
                        </div>
						<?php
						if($status == ''){ ?>
							<div class="col-md-4 col-sm-12 col-xs-12 text-right">
								<button type="button" class="btn btn-info btn-sm ico_left" onclick="addDyn('div_dynamic_ticket_info'); event_airport_s();copy_values()"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Section</button>
							</div>
						<?php } ?>
                    </div>
                    <div class="dynform-wrap" id="div_dynamic_ticket_info" data-counter="<?= $loop_items ?>">
						<?php
						for($i = 0; $i < $loop_items; $i++){
							$sq_trip_entries_count = $i+1;
							$entry_id = isset($flight_details[0]) ? $flight_details[0]->trip_entry_id_arr[$i] : 0;
							$css = '';
							if($type == 'update'){
								$button_name = 'Update';
							}
							$date = isset($departure_datetime_arr[$i]) ? $departure_datetime_arr[$i] : date('d-m-Y H:i');
							$sq_entry = mysqli_fetch_assoc(mysqlQuery("select * from ticket_trip_entries where entry_id='$entry_id'"));
							$bg = (isset($sq_entry['status']) && $sq_entry['status'] == 'Cancel') ? 'background-color:#f2dede!important;' : '';
							$disbaled = (isset($sq_entry['status']) && $sq_entry['status'] == 'Cancel') ? 'disabled' : '';
							$css =  'style="margin-top :0px !important;'.$bg.'"';
							$check_status = (isset($flight_details[0]) && $flight_details[0]->trip_data_check_arr[$i]) ? 'checked' : '';
							if($type == 'save') { $check_status = 'checked'; }
							if(isset($flight_details[0])){
								$arrival_datetime = $flight_details[0]->arrival_datetime_arr[$i];
								$departure_city = $flight_details[0]->departure_city_arr[$i];
								$from_city = $flight_details[0]->from_city_id_arr[$i];
								$dep_terminal = $flight_details[0]->departure_terminal_arr[$i];
								$to_city = $flight_details[0]->to_city_id_arr[$i];
								$arrival_city = $flight_details[0]->arrival_city_arr[$i];
								$arr_terminal = $flight_details[0]->arrival_terminal_arr[$i];
								$airline_name = $flight_details[0]->airlines_name_arr[$i];
								$classa = $flight_details[0]->class_arr[$i];
								$flight_duration = $flight_details[0]->flight_duration_arr[$i];
								$layover_time = $flight_details[0]->layover_time_arr[$i];
								$sub_category = $flight_details[0]->sub_category_arr[$i];
								$flight_no = $flight_details[0]->flight_no_arr[$i];
								$airlin_pnr = $flight_details[0]->airlin_pnr_arr[$i];
								$no_of_pieces = $flight_details[0]->no_of_pieces_arr[$i];
								$special_note = $flight_details[0]->special_note_arr[$i];
								$aircraft_type = $flight_details[0]->aircraft_type_arr[$i];
								$operating_carrier = $flight_details[0]->operating_carrier_arr[$i];
								$ticket_status = $flight_details[0]->ticket_status_arr[$i];
								$frequent_flyer = $flight_details[0]->frequent_flyer_arr[$i];
								$basic_fare = floatval($flight_details[0]->basic_fare_arr[$i]);
								$refund_type = $flight_details[0]->refund_type_arr[$i];
								$trip_entry_id = $flight_details[0]->trip_entry_id_arr[$i];
							}else{
								$arrival_datetime = '';
								$departure_city = '';
								$from_city = '';
								$dep_terminal = '';
								$to_city = '';
								$arrival_city = '';
								$arr_terminal = '';
								$airline_name = '';
								$classa = '';
								$flight_duration = '';
								$layover_time = '';
								$sub_category = '';
								$flight_no = '';
								$airlin_pnr = '';
								$no_of_pieces = '';
								$special_note = '';
								$aircraft_type = '';
								$operating_carrier = '';
								$ticket_status = '';
								$frequent_flyer = '';
								$basic_fare = '';
								$refund_type = '';
								$trip_entry_id = '';
							}
							?>
							<input type="checkbox" class="form-control css-checkbox trip_data_check" id="chk_tickett<?= $sq_trip_entries_count?>" <?= $check_status.' '.$disbaled ?>>
							<?php //} ?>
							<div class="dynform-item" for="chk_tickett<?= $sq_trip_entries_count ?>" <?=$css?>>
								<div class="row">
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="departure_datetime-<?= $sq_trip_entries_count ?>" name="departure_datetime" class="app_datetimepicker departure_datetime" placeholder="*Departure Date-Time" title="Departure Date-Time" value="<?php echo $date ?>" data-dyn-valid="required">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="arrival_datetime-<?= $sq_trip_entries_count ?>" name="arrival_datetime" class="app_datetimepicker arrival_datetime" placeholder="*Arrival Date-Time" onchange="validate_validDatetimeFlight(this.id)" title="Arrival Date-Time" value="<?php echo $arrival_datetime; ?>" data-dyn-valid="required">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<?php
										$city_id = $from_city;
										$sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$city_id'"));
										?>
										<input id="airpf-<?= $sq_trip_entries_count ?>" name="airpf" title="Enter Departure Airport" data-toggle="tooltip" class="form-control autocomplete airpf" placeholder="*Enter Departure Airport" data-dyn-valid="required" value="<?php echo isset($sq_city['city_name']) ? $sq_city['city_name']." - ".$departure_city : ''; ?>">
										<input type="hidden" name="from_city" id="from_city-<?= $sq_trip_entries_count ?>" data-dyn-valid="required" value="<?= $from_city ?>"/>
										<input type="hidden" name="departure_city" id="departure_city-<?= $sq_trip_entries_count ?>" data-dyn-valid="required" value="<?= $departure_city ?>">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="dterm-<?= $sq_trip_entries_count ?>" name="dterm" onchange="validate_specialChar(this.id)" placeholder="Departure Terminal" title="Departure Terminal" data-dyn-valid="" value="<?= $dep_terminal ?>">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10 ">
										<?php
										$city_id = $to_city;
										$sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$city_id'"));
										?>
										<input id="airpt-<?= $sq_trip_entries_count ?>" name="airpt" class="form-control autocomplete airpt" title="Enter Arrival Airport" data-toggle="tooltip" placeholder="*Enter Arrival Airport" data-dyn-valid="required" value="<?php echo isset($sq_city['city_name']) ? $sq_city['city_name']." - ".$arrival_city : ''; ?>" >
										<input type="hidden" name="to_city" id="to_city-<?= $sq_trip_entries_count ?>" data-dyn-valid="required" value="<?= $to_city ?>"/>
										<input type="hidden" name="arrival_city" id="arrival_city-<?= $sq_trip_entries_count ?>" data-dyn-valid="required" value="<?php echo $arrival_city; ?>">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="aterm-<?= $sq_trip_entries_count ?>" name="aterm" onchange="validate_specialChar(this.id)" placeholder="Arrival Terminal" title="Arrival Terminal" data-dyn-valid="" value="<?= $arr_terminal ?>">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<select id="airlines_name-<?= $sq_trip_entries_count ?>" name="airlines_name" title="Airlines Name" style="width:100%" data-dyn-valid="required" class="airlines_names app_select">
										<?php if($airline_name!=''){?><option value="<?= $airline_name ?>"><?= $airline_name ?></option><?php } ?>
											<option value="">*Airline Name</option>
										<?php $sq_airline = mysqlQuery("SELECT airline_name,airline_code FROM airline_master WHERE active_flag!='Inactive' ORDER BY airline_name ASC");
											while($row_airline = mysqli_fetch_assoc($sq_airline)){
											?>
											<option value="<?= $row_airline['airline_name'].' ('.$row_airline['airline_code'].')' ?>"><?= $row_airline['airline_name'].' ('.$row_airline['airline_code'].')' ?></option>
										<?php
										}
										?>
									</select>
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<select name="class" id="class-<?= $sq_trip_entries_count ?>" title="Class" data-dyn-valid="required" class="flight_class">
										<?php if($classa!=''){?><option value="<?= $classa ?>"><?= $classa ?></option><?php } ?>
                            				<?php get_flight_class_dropdown(); ?>
										</select>
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="flight_duration-<?= $sq_trip_entries_count ?>" name="flight_duration" placeholder="Flight Duration" title="Flight Duration" value="<?= $flight_duration ?>" >
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="layover_time-<?= $sq_trip_entries_count ?>" name="layover_time" placeholder="Layover Time" title="Layover Time" value="<?= $layover_time ?>" >
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="sub_category-<?= $sq_trip_entries_count ?>" name="sub_category"  placeholder="Sub Category" title="Sub Category" value="<?= $sub_category ?>" >
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="flight_no-<?= $sq_trip_entries_count ?>" style="text-transform: uppercase;" name="flight_no" onchange="validate_specialChar(this.id)" placeholder="Flight No" title="Flight No" data-dyn-valid="" value="<?= $flight_no ?>">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="airlin_pnr-<?= $sq_trip_entries_count ?>" style="text-transform: uppercase;" onchange=" validate_specialChar(this.id)" name="airlin_pnr" placeholder="GDS PNR" title="GDS PNR" data-dyn-valid="" value="<?= $airlin_pnr ?>">
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 hidden mg_bt_10">
										<input type="hidden" id="cancel_status-<?= $sq_trip_entries_count ?>" name="cancel_status" value="<?= $sq_entry['status'] ?>" data-dyn-valid="" >
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="no_of_pieces-<?= $sq_trip_entries_count ?>" name="no_of_pieces"  placeholder="No of pieces" title="No of pieces" value="<?= $no_of_pieces ?>" >
									</div>
									<div class="col-md-3 col-sm-12 col-xs-12 mg_bt_10">
										<textarea name="special_note" id="special_note-<?= $sq_trip_entries_count ?>" onchange="validate_address(this.id)" rows="1" placeholder="Special Note" title="Special Note" data-dyn-valid=""><?= $special_note ?></textarea>
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="aircraft_type-<?= $sq_trip_entries_count ?>" name="aircraft_type"  placeholder="Aircraft Type" title="Aircraft Type" value="<?= $aircraft_type ?>" >
									</div>
								</div>	
								<div class="row">
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="operating_carrier-<?= $sq_trip_entries_count ?>" name="operating_carrier" placeholder="Operated By" title="Operated By" value="<?= $operating_carrier ?>" >
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="text" id="frequent_flyer-<?= $sq_trip_entries_count ?>" name="frequent_flyer" placeholder="Frequent Flyer" title="Frequent Flyer" value="<?= $frequent_flyer ?>" >
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<select name="ticket_status" id="ticket_status-<?= $sq_trip_entries_count ?>" title="Status of ticket" >
											<?php if($ticket_status!=''){?><option value="<?= $ticket_status ?>"><?= $flight_details[0]->ticket_status_arr[$i] ?></option><?php } ?>
											<option value="">Status of ticket</option>
											<option value="Hold">Hold</option>
											<option value="Confirmed">Confirmed</option>
										</select>
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<input type="number" id="basic_fare-<?= $sq_trip_entries_count ?>" name="basic_fare" placeholder="Basic Fare" title="Basic Fare" value="<?= $basic_fare ?>" >
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
										<select name="refund_type" id="refund_type-<?= $sq_trip_entries_count ?>" title="Refund Type" data-dyn-valid="required" >
										<?php if($refund_type=''){?><option value="<?= $refund_type ?>"><?= $refund_type ?></option><?php } ?>
											<option value="">Refund Type</option>
											<option value="Refundable">Refundable</option>
											<option value="Non Refundable">Non Refundable</option>
										</select>
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10 hidden">
										<input type="hidden" name="entry_id" id="entry_id-<?= $sq_trip_entries_count ?>"
										value="<?= $trip_entry_id ?>"/>
									</div>
									<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10 hidden">
										<input type="text" id="cabin_baggage-<?= $sq_trip_entries_count ?>" name="cabin_baggage" onchange="validate_specialChar(this.id)" placeholder="Cabin Baggage" title="Cabin Baggage" data-dyn-valid="">
									</div>

								</div>
							</div>
							<script>
							$('#departure_datetime-<?= $sq_trip_entries_count ?>, #arrival_datetime-<?= $sq_trip_entries_count ?>').datetimepicker({ format:'d-m-Y H:i' });
							$('#airlines_name-<?= $sq_trip_entries_count ?>,#plane_from_location-<?= $sq_trip_entries_count ?>,#plane_to_location-<?= $sq_trip_entries_count ?>').select2();
							</script>
						<?php } ?>
                    </div>
                    <div class="row text-center mg_tp_20">
                        <div class="col-xs-12">
                        	<button id="update_btn" class="btn btn-sm btn-success" id="btn_ticket_details"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;<?= $button_name ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
$('#flight_details_modal').modal('show');
$('#quotation_id').select2();
$('#frm_flight_details').validate({
	submitHandler:function(form, e){
		$('#update_btn').prop('disabled',true);
		e.preventDefault();
		var base_url = $('#base_url').val();
		var count = $('#count').val();
		var type = $('#type').val();
		var type_of_tour = $('input[name="type_of_tour"]:checked').val();
		var msg = "Type of trip is required"; 
		var airpf = $('.airpf');
		var airpt = $('.airpt');
		var departure_datetime = $('.departure_datetime');
		var arrival_datetime = $('.arrival_datetime');
		var airlines_name = $('.airlines_names');
		var trip_data_check = $('.trip_data_check');
		var flight_class = $('.flight_class');

		var err_msg = "";
		var airpf_flag    = false;
		var airpt_flag    = false;
		var dd_flag       = false;
		var ad_flag       = false;
		var date_mismatch = false;
		var airline_flag  = false;
		var class_flag  = false;
		var status = '';
		for(let i = 0; i < airpf.length ; i++){
			if(!trip_data_check[i]){
				status = true;
			}else{
				status = trip_data_check[i].checked;
			}
			if(status){

				if(airpf[i].value == "")	airpf_flag = true;
				if(airpt[i].value == "")	airpt_flag = true;
				if(departure_datetime[i].value == "")	dd_flag = true;
				if(arrival_datetime[i].value == "")	ad_flag = true;
				if(airlines_name[i].value == "") airline_flag = true;
				if(flight_class[i].value == "") class_flag = true;
				if(departure_datetime[i].value != "" && arrival_datetime[i].value != "" && Date.parse(departure_datetime[i].value) >= Date.parse(arrival_datetime[i].value)){
					date_mismatch = true;
				}
			}
			if(airpf_flag){
				err_msg += "-Please Enter Departure Airport at section "+(i+1)+"<br>";
			}
			if(airpt_flag){
				err_msg += "-Please Enter Arrival Airport at section "+(i+1)+"<br>";
			}
			if(dd_flag){
				err_msg += "-Please Enter Departure Date and Time at section "+(i+1)+"<br>";
			}
			if(ad_flag){
				err_msg += "-Please Enter Arrival Date and Time at section "+(i+1)+"<br>";
			}
			if(date_mismatch){
				err_msg += "-Please Enter Valid Departure and Arrival Date and Time at section "+(i+1)+"<br>";
			}
			if(airline_flag){
				err_msg += "-Please Select Airline Name at section "+(i+1)+"<br>";
			}
			if(class_flag){
				err_msg += "-Please Select Class at section "+(i+1)+"<br>";
			}
		}
		if(airpf_flag || airpt_flag || dd_flag || ad_flag || airline_flag || date_mismatch){
			error_msg_alert(err_msg);
			$('#update_btn').prop('disabled',false);
			return false;
		}
		if(type_of_tour == undefined){
			error_msg_alert(msg);
			$('#update_btn').prop('disabled',false);
			return false;
		}
		var airlin_pnr_arr = getDynFields('airlin_pnr');
		$('#update_btn').button('loading');
		$.ajax({
			type: 'post',
			url: base_url+'controller/visa_passport_ticket/ticket/ticket_pnr_check.php',
			data:{ airlin_pnr_arr : airlin_pnr_arr,type:'save',entry_id:'' },
			success: function(result){
				if(result==''){
					$('a[href="#tab3"]').tab('show');
				}
				else{
					var msg = result.split('--');
					error_msg_alert(msg[1]);
					$('#update_btn').prop('disabled',false);
					$('#update_btn').button('reset');
					return false;
				}
			}
		});

        var departure_datetime_arr = getDynFields('departure_datetime');
		var arrival_datetime_arr = getDynFields('arrival_datetime');
		var airlines_name_arr = getDynFields('airlines_name');
		var class_arr = getDynFields('class');
		var sub_category_arr = getDynFields('sub_category');
		var flight_no_arr = getDynFields('flight_no');
		var airlin_pnr_arr = getDynFields('airlin_pnr');
        var from_city_id_arr = getDynFields('from_city');
		var departure_city_arr = getDynFields('departure_city');
		var departure_terminal_arr = getDynFields('dterm');
        var to_city_id_arr = getDynFields('to_city');
		var arrival_city_arr = getDynFields('arrival_city');
		var arrival_terminal_arr = getDynFields('aterm');
		var luggage_arr = getDynFields('cabin_baggage');
		var no_of_pieces_arr = getDynFields('no_of_pieces');
		var special_note_arr = getDynFields('special_note');
		var aircraft_type_arr = getDynFields('aircraft_type');
		var operating_carrier_arr = getDynFields('operating_carrier');
		var frequent_flyer_arr = getDynFields('frequent_flyer');
		var ticket_status_arr = getDynFields('ticket_status');
		var basic_fare_arr = getDynFields('basic_fare');
		var flight_duration_arr = getDynFields('flight_duration');
		var layover_time_arr = getDynFields('layover_time');
		var refund_type_arr = getDynFields('refund_type');
		var trip_entry_id_arr = getDynFields('entry_id');
		var cancel_status_arr = getDynFields('cancel_status');

		var trip_data_check_arr = [];
		var valid_count = 0;
		for(let i = 0; i < departure_datetime_arr.length ; i++){
			var status = '';
			if(!trip_data_check[i]){
				status = true;
			}else{
				status = trip_data_check[i].checked;
			}
			if(status)
			valid_count++;

			trip_data_check_arr.push(status);
		}
		if(valid_count == 0){
			error_msg_alert('Atleast one flight details required!');
			$('#update_btn').prop('disabled',false);
			$('#update_btn').button('reset');
			return false;
		}
		var dynamic_section_arr = [];
		dynamic_section_arr.push({
			'type_of_tour' : type_of_tour,
			'departure_datetime_arr' : departure_datetime_arr,
			'arrival_datetime_arr' : arrival_datetime_arr,
			'airlines_name_arr' : airlines_name_arr,
			'class_arr' : class_arr,
			'sub_category_arr' : sub_category_arr,
			'flight_no_arr' : flight_no_arr,
			'airlin_pnr_arr' : airlin_pnr_arr,
			'from_city_id_arr' : from_city_id_arr,
			'departure_city_arr' : departure_city_arr,
			'departure_terminal_arr' : departure_terminal_arr,
			'to_city_id_arr' : to_city_id_arr,
			'arrival_city_arr' : arrival_city_arr,
			'arrival_terminal_arr' : arrival_terminal_arr,
			'luggage_arr' : luggage_arr,
			'no_of_pieces_arr' : no_of_pieces_arr,
			'special_note_arr' : special_note_arr,
			'aircraft_type_arr' : aircraft_type_arr,
			'operating_carrier_arr' : operating_carrier_arr,
			'frequent_flyer_arr' : frequent_flyer_arr,
			'ticket_status_arr' : ticket_status_arr,
			'basic_fare_arr' : basic_fare_arr,
			'flight_duration_arr' : flight_duration_arr,
			'layover_time_arr' : layover_time_arr,
			'refund_type_arr' : refund_type_arr,
			'trip_entry_id_arr':trip_entry_id_arr,
			'trip_data_check_arr':trip_data_check_arr,
			'cancel_status_arr':cancel_status_arr
		});
		dynamic_section_arr = JSON.stringify(dynamic_section_arr);
		$('#flight_details'+count).html(dynamic_section_arr);
		if(type == 'update'){ var msg = 'Flight ticket details updated!'; }else{ var msg = 'Flight ticket details saved!'; }
		success_msg_alert(msg);
		$('#flight_details_modal').modal('hide');

        var table = document.getElementById("tbl_dynamic_ticket_master");
        var rowCount = table.rows.length;
        for(var i=0; i<rowCount; i++){
			var row = table.rows[i];
			var trip_details = $('#flight_details'+(i+1)).html();
			if(trip_details == ''||trip_details==null){
				$('#flight_details'+(i+1)).html(dynamic_section_arr);
			}
		}
	}
});

function event_airport_s(count = 2){
	if(count == 1)	{id1 = "airpf-1"; id2 = "airpt-1"}
	else{
		id1 = "airpf-"+$('#div_dynamic_ticket_info').attr('data-counter');
		id2 = "airpt-"+$('#div_dynamic_ticket_info').attr('data-counter');
	}
	ids = [{"dep" : id1}, {"arr" : id2}];
	airport_load_main_sale(ids);
}
for(var num_airp = 1; num_airp <= parseInt($('#div_dynamic_ticket_info').attr('data-counter')); num_airp++)
event_airport_s(num_airp);

function copy_values(){
	var count = $('#div_dynamic_ticket_info').attr('data-counter');
	var currentdate = new Date(); 
	var day = currentdate.getDate();
	var month = currentdate.getMonth() + 1;
	if (day < 10) {
		day = '0' + day;
	}
	if (month < 10) {
		month = '0' + month;
	}
	var datetime = day + "-"
                + month + "-" 
                + currentdate.getFullYear() + " "  
                + currentdate.getHours() + ":"  
                + currentdate.getMinutes();
	$('#departure_datetime-'+count).val(datetime);
	$('#luggage-'+count).val($('#luggage-1').val());
	$('#airpf-'+count).val($('#airpt-1').val());
	$('#from_city-'+count).val($('#to_city-1').val());
	$('#departure_city-'+count).val($('#arrival_city-1').val());
	$('#airpt-'+count).val($('#airpf-1').val());
	$('#to_city-'+count).val($('#from_city-1').val());
	$('#arrival_city-'+count).val($('#departure_city-1').val());
	// $('#cancel_status-'+count).val($('#cancel_status-1').val());
	$('#cancel_status-'+count).val();
}
function addSection(id){
	if($('#div_dynamic_ticket_info').attr('data-counter') == 1){
		addDyn('div_dynamic_ticket_info');
		if(id == 'type_of_tour-round_trip'){
			copy_values();
		}
		event_airport_s();
	}
}
function airport_load_main_sale(ids){

	var base_url = $('#base_url').val();
	ids.forEach(function (id){
		var object_id = Object.values(id)[0];
		$("#"+object_id).autocomplete({
			source: function(request, response){
				$.ajax({
					method:'get',
					url : base_url+'view/visa_passport_ticket/ticket/home/airport_list.php',
					dataType : 'json',
					data : {request : request.term},
					success : function(data){
						response(data);
					}
				});
			},
			select: function (event, ui) {
				var substr_id =  object_id.substr(6);
				if(Object.keys(id)[0] == 'dep'){
					$('#from_city-'+substr_id).val(ui.item.city_id);
					$('#departure_city-'+substr_id).val(ui.item.value.split(" - ")[1]);
				}
				else{
					$('#to_city-'+substr_id).val(ui.item.city_id);
					$('#arrival_city-'+substr_id).val(ui.item.value.split(" - ")[1]);
				}
			},
			open: function(event, ui) {
				$(this).autocomplete("widget").css({
					"width": document.getElementById(object_id).offsetWidth
				});
			},
			minLength: 2,
			change: function(event,ui){
				var substr_id =  object_id.substr(6);
				if(!ui.item) {
					$(this).val('');
					$('#from_city-'+substr_id).val("");
					$('#departure_city-'+substr_id).val("");
					error_msg_alert('Please select Airport from the list!!');
					$(this).css('border','1px solid red;');
					return;
				}
				if($('#'+ids[0].dep).val() == $("#"+ids[1].arr).val()){
					$(this).val('');
					$(this).css('border','1px solid red;');
					$('#from_city-'+substr_id).val("");
					$('#departure_city-'+substr_id).val("");
					error_msg_alert('Same Arrival and Boarding Airport Not Allowed!!');
				}
			}
		}).data("ui-autocomplete")._renderItem = function(ul, item) {
				return $("<li disabled>")
				.append("<a>" + item.value.split(" -")[1] + "<br><b>" + item.value.split(" -")[0] + "<b></a>")
				.appendTo(ul);
			}
	});	
}
</script>
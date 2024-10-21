<?php
include '../../../../model/model.php';
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='visa_passport_ticket/visa/index.php'"));
$branch_status = $sq['branch_status'];
?>
<input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>">
<input type="hidden" id="financial_year_id" name="financial_year_id" value="<?= $financial_year_id ?>">

<input type="hidden" id="unique_timestamp" name="unique_timestamp" value="<?= md5(time()) ?>">
<input type="hidden" id="hotel_sc" name="hotel_sc">
<input type="hidden" id="hotel_markup" name="hotel_markup">
<input type="hidden" id="hotel_taxes" name="hotel_taxes">
<input type="hidden" id="hotel_markup_taxes" name="hotel_markup_taxes">
<input type="hidden" id="hotel_tds" name="hotel_tds">

<div class="modal fade" id="visa_save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document" style="min-width: 90%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">New Visa Booking</h4>
			</div>
			<div class="modal-body">

				<form id="frm_visa_save" name="frm_visa_save">

					<div class="panel panel-default panel-body fieldset">
						<legend>Customer Details</legend>

						<div class="row">
							<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_sm_xs">
								<select class="form-control" name="customer_id" id="customer_id" class="customer_dropdown" title="Customer Name" style="width:100%" onchange="customer_info_load();get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','false','service_charge','basic');">
									<?php get_new_customer_dropdown($role, $branch_admin_id, $branch_status); ?>
								</select>
							</div>
							<div id="cust_details">
								<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_sm_xs">
									<input class="form-control" type="text" id="mobile_no" name="mobile_no" title="Mobile Number" placeholder="Mobile No" title="Mobile No" readonly>
								</div>
								<div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_sm_xs">
									<input class="form-control" type="text" id="email_id" name="email_id" title="Email Id" placeholder="Email ID" title="Email ID" readonly>
								</div>
								<div class="col-md-3 col-sm-4 col-xs-12">
									<input class="form-control hidden" type="text" id="company_name" name="company_name" title="Company Name" placeholder="Company Name" title="Company Name" readonly>
								</div>
								<div class="col-md-3 col-sm-4 col-xs-12">
									<input class="form-control hidden" type="text" id="credit_amount" name="credit_amount" placeholder="Credit Note Balance" title="Credit Note Balance" readonly>
								</div>
							</div>
						</div>
						<div class="row mg_tp_10">
							<div class="col-md-4">
								<input id="copy_details1" name="copy_details1" type="checkbox" onClick="copy_details();">
								&nbsp;&nbsp;<label for="copy_details1">Passenger Details same as above</label>
							</div>
						</div>
					</div>
							<div id="new_cust_div"></div>

					<div class="panel panel-default panel-body fieldset mg_tp_30">
						<legend>Passenger Details</legend>

						<div class="row mg_bt_10">
							<div class="col-xs-12 text-right text_center_xs">
                                <button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_dynamic_visa')"><i class="fa fa-plus"></i></button>
                                <button type="button" class="btn btn-pdf btn-sm" title="Delete Row" onclick="deleteRow('tbl_dynamic_visa');alltable_visa_cost('tbl_dynamic_visa');"><i class="fa fa-trash"></i></button>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="table-responsive">
									<?php $offset = ""; ?>
									<table id="tbl_dynamic_visa" name="tbl_dynamic_visa" class="table table-bordered no-marg pd_bt_51" style="width:1685px">
										<?php include_once('visa_member_tbl.php'); ?>
									</table>
								</div>
							</div>
						</div>


					</div>

					<div class="panel panel-default panel-body fieldset mg_tp_30">
						<legend>Costing Details</legend>

						<div class="row mg_bt_10">
							<div class="col-md-3 col-sm-6 col-xs-12">
								<small id="basic_show" style="color:red">&nbsp;</small>
								<input class="form-control" type="number" id="visa_issue_amount" name="visa_issue_amount" placeholder="*Basic Amount" title="Basic Amount" onchange="validate_balance(this.id);get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','true','basic','basic');">
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small id="service_show" style="color:red">&nbsp;</small>
								<input class="form-control" type="text" name="service_charge" id="service_charge" placeholder="Service Charge" title="Service Charge" onchange="validate_balance(this.id);get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','true','service_charge','basic');">
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<select title="Tax Apply On" id="tax_apply_on" name="tax_apply_on" class="form-control" onchange="get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','false','service_charge','basic');">
									<option value="">*Tax Apply On</option>
									<option value="1">Basic Amount</option>
									<option value="2">Service Charge</option>
									<option value="3">Total</option>
								</select>
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<select title="Select Tax" id="tax_value" name="tax_value" class="form-control" onchange="get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','false','service_charge','basic');">
									<option value="">*Select Tax</option>
									<?php get_tax_dropdown('Income') ?>
								</select>
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input class="form-control" type="text" id="service_tax_subtotal" name="service_tax_subtotal" placeholder="Tax Amount" title="Tax Amount" readonly>
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small id="markup_show" style="color:red">&nbsp;</small>
								<input class="form-control" type="text" id="markup" name="markup" placeholder="Markup Amount" title="Markup Amount" onchange="get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','false','markup','basic');validate_balance(this.id)">
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<select title="Select Markup Tax" id="markup_tax_value" name="markup_tax_value" class="form-control" onchange="get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','false','service_charge','basic');">
									<option value="">*Select Markup Tax</option>
									<?php get_tax_dropdown('Income') ?>
								</select>
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input class="form-control" type="text" id="service_tax_markup" name="service_tax_markup" placeholder="Tax on Markup" title="Tax on Markup" readonly>
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input class="form-control" type="text" name="roundoff" id="roundoff" class="text-right" placeholder="Round Off" title="RoundOff" readonly>
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input class="form-control amount_feild_highlight text-right" type="text" name="visa_total_cost" id="visa_total_cost" placeholder="Net Total" title="Net Total" readonly>
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input class="form-control" type="text" name="booking_date" id="booking_date" value="<?= date('d-m-Y') ?>" placeholder="*Booking Date" title="*Booking Date" onchange="get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','false','service_charge','basic' );">
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input class="form-control" type="text" name="due_date" id="due_date" placeholder="Due Date" title="Due Date" value="<?= date('d-m-Y') ?>">
							</div>
							<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<select class="form-control" name="currency_code" id="vcurrency_code" title="Currency" style="width:100%" data-toggle="tooltip" required>
									<?php
									$sq_app_setting = mysqli_fetch_assoc(mysqlQuery("select currency from app_settings"));
									if($sq_app_setting['currency']!='0'){

										$sq_currencyd = mysqli_fetch_assoc(mysqlQuery("SELECT `id`,`currency_code` FROM `currency_name_master` WHERE id=" . $sq_app_setting['currency']));
										?>
										<option value="<?= $sq_currencyd['id'] ?>"><?= $sq_currencyd['currency_code'] ?></option>
									<?php } ?>
									<?php
									$sq_currency = mysqlQuery("select * from currency_name_master order by currency_code");
									while($row_currency = mysqli_fetch_assoc($sq_currency)){
									?>
									<option value="<?= $row_currency['id'] ?>"><?= $row_currency['currency_code'] ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

					</div>

					<div class="panel panel-default panel-body fieldset mg_tp_30">
						<legend>Advance Details</legend>

						<div class="row">
							<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
								<input class="form-control" type="text" id="payment_date" name="payment_date" placeholder="*Date" title="Date" value="<?= date('d-m-Y') ?>" onchange="check_valid_date(this.id)">
							</div>
							<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
								<select class="form-control" name="payment_mode" id="payment_mode" title="Mode" onchange="payment_master_toggles(this.id, 'bank_name', 'transaction_id', 'bank_id');get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','true','service_charge','basic');get_identifier_block('identifier','payment_mode','credit_card_details','credit_charges');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges')">
									<?php get_payment_mode_dropdown(); ?>
								</select>
							</div>
							<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
								<input class="form-control" type="text" id="payment_amount" name="payment_amount" placeholder="*Amount" title="Amount" onchange="validate_balance(this.id);get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges');payment_amount_validate(this.id,'payment_mode','transaction_id','bank_name','bank_id')">
							</div>
						</div>

						<div class="row mg_bt_10">
							<div class="col-md-4 col-sm-6 col-xs-12">
								<input class="hidden form-control" type="text" id="credit_charges" name="credit_charges" title="Credit card charges" disabled>
							</div>
							<div class="col-md-4 col-sm-6 col-xs-12">
								<select class="hidden form-control" id="identifier" onchange="get_credit_card_data('identifier','payment_mode','credit_card_details')" title="Identifier(4 digit)" required>
									<option value=''>*Select Identifier</option>
								</select>
							</div>
							<div class="col-md-4 col-sm-6 col-xs-12">
								<input class="hidden form-control" type="text" id="credit_card_details" name="credit_card_details" title="Credit card details" disabled>
							</div>
						</div>
						<div class="row mg_bt_10">
							<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
								<input class="form-control bank_suggest" type="text" id="bank_name" name="bank_name" title="Bank Name" placeholder="Bank Name" readonly>
							</div>
							<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
								<input class="form-control" type="number" id="transaction_id" name="transaction_id" onchange="validate_specialChar(this.id)" placeholder="Cheque No/ID" title="Cheque No/ID" readonly>
							</div>
							<div class="col-md-4 col-sm-6 col-xs-12">
								<select class="form-control" name="bank_id" id="bank_id" title="Select Bank" disabled>
									<?php get_bank_dropdown(); ?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-9 col-sm-9">
								<span class="note" style="color: red;line-height: 35px;" data-original-title="" title=""><?= $txn_feild_note ?></span>
							</div>
						</div>
					</div>

					<div class="row text-center">
						<div class="col-md-12">
							<button id="btn_visa_master_save" class="btn btn-sm btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
						</div>
					</div>

				</form>


			</div>
		</div>
	</div>
</div>

<script>
	$('#visa_save_modal').modal('show');
	$('#vcurrency_code').select2();
	$('#expiry_date1,#due_date').datetimepicker({
		timepicker: false,
		format: 'd-m-Y',
	});
	$('#booking_date,#payment_date').datetimepicker({
		timepicker: false,
		format: 'd-m-Y',
	});
	var date = new Date();
	var yest = date.setDate(date.getDate() - 1);
	var tom = date.setDate(date.getDate() + 1);
	$('#birth_date1').datetimepicker({
		timepicker: false,
		maxDate: yest,
		format: 'd-m-Y'
	});
	$('#issue_date1').datetimepicker({
		timepicker: false,
		format: 'd-m-Y',
		maxDate: yest
	});
	$('#appointment1,#expiry_date1').datetimepicker({
		timepicker: false,
		minDate: tom,
		format: 'd-m-Y'
	});
	$('#customer_id').select2();

	function total_fun() {

		var service_tax = $('#service_tax').val();
		var service_tax_subtotal = $('#service_tax_subtotal').val();
		var sub_total = $('#visa_issue_amount').val();
		var service_charge = $('#service_charge').val();
		var markup = $('#markup').val();
		var service_tax_markup = $('#service_tax_markup').val();

		if (sub_total == "") {
			sub_total = 0;
		}
		if (service_charge == "") {
			service_charge = 0;
		}
		if (markup == "") {
			markup = 0;
		}

		var service_tax_amount = 0;
		if (parseFloat(service_tax_subtotal) !== 0.00 && (service_tax_subtotal) !== '') {

			var service_tax_subtotal1 = service_tax_subtotal.split(",");
			for (var i = 0; i < service_tax_subtotal1.length; i++) {
				var service_tax = service_tax_subtotal1[i].split(':');
				service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
			}
		}

		var markupservice_tax_amount = 0;
		if (parseFloat(service_tax_markup) !== 0.00 && (service_tax_markup) !== "") {
			var service_tax_markup1 = service_tax_markup.split(",");
			for (var i = 0; i < service_tax_markup1.length; i++) {
				var service_tax = service_tax_markup1[i].split(':');
				markupservice_tax_amount = parseFloat(markupservice_tax_amount) + parseFloat(service_tax[2]);
			}
		}

		sub_total = ($('#basic_show').html() == '&nbsp;') ? sub_total : parseFloat($('#basic_show').text().split(' : ')[1]);
		service_charge = ($('#service_show').html() == '&nbsp;') ? service_charge : parseFloat($('#service_show').text().split(' : ')[1]);
		markup = ($('#markup_show').html() == '&nbsp;') ? markup : parseFloat($('#markup_show').text().split(' : ')[1]);

		var total_amount = parseFloat(sub_total) + parseFloat(service_tax_amount) + parseFloat(markupservice_tax_amount) + parseFloat(service_charge) + parseFloat(markup);
		var total = total_amount.toFixed(2);
		var roundoff = Math.round(total) - total;
		$('#roundoff').val(roundoff.toFixed(2));
		$('#visa_total_cost').val(parseFloat(total) + parseFloat(roundoff));
	}

	$(function() {
		$('#frm_visa_save').validate({
			rules: {
				customer_id: {
					required: true
				},
				visa_issue_amount: {
					required: true,
					number: true
				},
				service_charge: {
					required: true,
					number: true
				},
				visa_total_cost: {
					required: true,
					number: true
				},
				booking_date: {
					required: true
				},
				payment_date: {
					required: true
				},
				payment_amount: {
					required: true,
					number: true
				},
				payment_mode: {
					required: true
				},
				country_code: {
					required: true
				},
				bank_id: {
					required: function() {
						if ($('#payment_mode').val() != "Cash") {
							return true;
						} else {
							return false;
						}
					}
				},
				tax_apply_on : { required:true},
				tax_value : { required:true},
				markup_tax_value : { required:true}
			},
			submitHandler: function(form) {
				$('#btn_visa_master_save').prop('disabled', true);
				var base_url = $('#base_url').val();
				var customer_id = $('#customer_id').val();
				var cust_first_name = $('#cust_first_name').val();
				var cust_middle_name = $('#cust_middle_name').val();
				var cust_last_name = $('#cust_last_name').val();
				var gender = $('#cust_gender').val();
				var cust_birth_date = $('#cust_birth_date').val();
				var age = $('#cust_age').val();
				var contact_no = $('#cust_contact_no').val();
				var email_id = $('#cust_email_id').val();
				var address = $('#cust_address1').val();
				var address2 = $('#cust_address2').val();
				var city = $('#city').val();
				var service_tax_no = $('#cust_service_tax_no').val();
				var landline_no = $('#cust_landline_no').val();
				var alt_email_id = $('#cust_alt_email_id').val();
				var company_name = $('#corpo_company_name').val();
				var cust_type = $('#cust_type').val();
				var country_code = $('#country_code').val();
				var state = $('#cust_state').val();
				var active_flag = 'Active';
				var branch_admin_id = $('#branch_admin_id1').val();
				var financial_year_id = $('#financial_year_id').val();
				var credit_charges = $('#credit_charges').val();
				var credit_card_details = $('#credit_card_details').val();
				var currency_code = $('#vcurrency_code').val();

				var credit_amount = $('#credit_amount').val();
				var emp_id = $('#emp_id').val();
				var visa_issue_amount = $('#visa_issue_amount').val();
				var service_charge = $('#service_charge').val();
				var service_tax_subtotal = $('#service_tax_subtotal').val();
				var roundoff = $('#roundoff').val();
				var visa_total_cost = $('#visa_total_cost').val();
				var due_date = $('#due_date').val();
				var booking_date = $('#booking_date').val();

				var payment_date = $('#payment_date').val();
				var payment_amount = $('#payment_amount').val();
				var payment_mode = $('#payment_mode').val();
				var bank_name = $('#bank_name').val();
				var transaction_id = $('#transaction_id').val();
				var bank_id = $('#bank_id').val();
				var markup = $('#markup').val();
				var service_tax_markup = $('#service_tax_markup').val();

				if(payment_mode=="Advance"){
					error_msg_alert("Please select another payment mode.");
					$('#btn_visa_master_save').prop('disabled',false);
					return false;
				}

				if (payment_mode == "Credit Note" && credit_amount != '') {
					if (parseFloat(payment_amount) > parseFloat(credit_amount)) {
						error_msg_alert('Credit Note Balance is not available');
						$('#btn_visa_master_save').prop('disabled', false);
						return false;
					}
				} else if (payment_mode == "Credit Note" && credit_amount == '') {
					error_msg_alert("Credit Note Balance is not available");
					$('#btn_visa_master_save').prop('disabled', false);
					return false;
				}
				if (parseFloat(payment_amount) > parseFloat(visa_total_cost)) {
					error_msg_alert("Payment amount cannot be greater than selling amount.");
					$('#btn_visa_master_save').prop('disabled', false);
					return false;
				}
				var first_name_arr = new Array();
				var middle_name_arr = new Array();
				var last_name_arr = new Array();
				var birth_date_arr = new Array();
				var adolescence_arr = new Array();
				var visa_country_name_arr = new Array();
				var visa_type_arr = new Array();
				var passport_id_arr = new Array();
				var issue_date_arr = new Array();
				var expiry_date_arr = new Array();
				var nationality_arr = new Array();
				//var received_documents_arr = new Array();
				var appointment_date_arr = new Array();

				var table = document.getElementById("tbl_dynamic_visa");
				var rowCount = table.rows.length;
				var checked_count = 0;
				for (var i = 0; i < rowCount; i++) {
					var row = table.rows[i];
					if (row.cells[0].childNodes[0].checked) {
						checked_count++;
					}
				}
				if (checked_count == 0) {
					error_msg_alert("Atleast one passenger details is required!");
					$('#btn_visa_master_save').prop('disabled', false);
					return false;
				}

				for (var i = 0; i < rowCount; i++) {
					var row = table.rows[i];
					if (row.cells[0].childNodes[0].checked) {
						var first_name = row.cells[2].childNodes[0].value;
						var middle_name = row.cells[3].childNodes[0].value;
						var last_name = row.cells[4].childNodes[0].value;
						var birth_date = row.cells[5].childNodes[0].value;
						var adolescence = row.cells[6].childNodes[0].value;
						var visa_country_name = row.cells[7].childNodes[0].value;
						var visa_type = row.cells[8].childNodes[0].value;
						var passport_id = row.cells[9].childNodes[0].value;
						var issue_date = row.cells[10].childNodes[0].value;
						var expiry_date = row.cells[11].childNodes[0].value;
						var nationality = row.cells[12].childNodes[0].value;
						var appointment = row.cells[13].childNodes[0].value;
						var msg = "";

						if (first_name == "") {
							msg += "First name is required in row:" + (i + 1) + "<br>";
						}

						if (visa_country_name == "") {
							msg += "Visa Country name is required in row:" + (i + 1) + "<br>";
						}
						if (visa_type == "") {
							msg += "Visa Type is required in row:" + (i + 1) + "<br>";
						}
						if (nationality == "") {
							msg += "Nationality is required in row:" + (i + 1) + "<br>";
						}
						if (msg != "") {
							error_msg_alert(msg);
							$('#btn_visa_master_save').prop('disabled', false);
							return false;
						}

						first_name_arr.push(first_name);
						middle_name_arr.push(middle_name);
						last_name_arr.push(last_name);
						birth_date_arr.push(birth_date);
						adolescence_arr.push(adolescence);
						visa_country_name_arr.push(visa_country_name);
						visa_type_arr.push(visa_type);
						passport_id_arr.push(passport_id);
						issue_date_arr.push(issue_date);
						expiry_date_arr.push(expiry_date);
						nationality_arr.push(nationality);
						appointment_date_arr.push(appointment);
					}
				}
				var hotel_sc = $('#hotel_sc').val();
				var hotel_markup = $('#hotel_markup').val();
				var hotel_taxes = $('#hotel_taxes').val();
				var hotel_markup_taxes = $('#hotel_markup_taxes').val();
				var hotel_tds = $('#hotel_tds').val();
				var tax_apply_on = $('#tax_apply_on').val();
				var tax_value = $('#tax_value').val();
				var markup_tax_value = $('#markup_tax_value').val();

				
				var reflections = [];
				reflections.push({
					'hotel_sc': hotel_sc,
					'hotel_markup': hotel_markup,
					'hotel_taxes': hotel_taxes,
					'hotel_markup_taxes': hotel_markup_taxes,
					'hotel_tds': hotel_tds,
					'tax_apply_on':tax_apply_on,
					'tax_value':tax_value,
					'markup_tax_value':markup_tax_value
				});
				var bsmValues = [];
				bsmValues.push({
					"basic": $('#basic_show').find('span').text(),
					"service": $('#service_show').find('span').text(),
					"markup": $('#markup_show').find('span').text()
				});

				$('#btn_visa_master_save').button('loading');
				//Validation for booking and payment date in login financial year
				$.post(base_url + 'view/load_data/finance_date_validation.php', {
					check_date: booking_date
				}, function(data) {
						if (data !== 'valid') {
							error_msg_alert("The Booking date does not match between selected Financial year.");
							$('#btn_visa_master_save').prop('disabled', false);
							$('#btn_visa_master_save').button('reset');
							return false;
						} else {
							var payment_date = $('#payment_date').val();
							$.post(base_url + 'view/load_data/finance_date_validation.php', {
								check_date: payment_date
							}, function(data) {
								if (data !== 'valid') {
									error_msg_alert("The Payment date does not match between selected Financial year.");
									$('#btn_visa_master_save').prop('disabled', false);
									$('#btn_visa_master_save').button('reset');
									return false;
								} else {
									//New Customer save
									if (customer_id == '0') {
										$.ajax({
											type: 'post',
											url: base_url + 'controller/customer_master/customer_save.php',
											data: {
												first_name: cust_first_name,
												middle_name: cust_middle_name,
												last_name: cust_last_name,
												gender: gender,
												birth_date: cust_birth_date,
												age: age,
												contact_no: contact_no,
												email_id: email_id,
												address: address,
												address2: address2,
												city: city,
												active_flag: active_flag,
												service_tax_no: service_tax_no,
												landline_no: landline_no,
												alt_email_id: alt_email_id,
												company_name: company_name,
												cust_type: cust_type,
												state: state,
												branch_admin_id: branch_admin_id,
												country_code: country_code
											},
											success: function(result) {
												var error_arr = result.split('--');
												if (error_arr[0] == 'error') {
													error_msg_alert(error_arr[1]);
													$('#btn_visa_master_save').button('reset');
													$('#btn_visa_master_save').prop('disabled', false);
													return false;
												} else {
													saveData();
												}
											}
										});
									}
									else{
										saveData();
									}

									function saveData() {

										$.ajax({
											type: 'post',
											url: base_url + 'controller/visa_passport_ticket/visa/visa_master_save.php',
											data: {
												emp_id: emp_id,
												customer_id: customer_id,
												visa_issue_amount: visa_issue_amount,
												service_charge: service_charge,
												service_tax_subtotal: service_tax_subtotal,
												visa_total_cost: visa_total_cost,
												payment_date: payment_date,
												payment_amount: payment_amount,
												payment_mode: payment_mode,
												bank_name: bank_name,
												transaction_id: transaction_id,
												first_name_arr: first_name_arr,
												middle_name_arr: middle_name_arr,
												last_name_arr: last_name_arr,
												birth_date_arr: birth_date_arr,
												adolescence_arr: adolescence_arr,
												visa_country_name_arr: visa_country_name_arr,
												visa_type_arr: visa_type_arr,
												passport_id_arr: passport_id_arr,
												issue_date_arr: issue_date_arr,
												expiry_date_arr: expiry_date_arr,
												nationality_arr: nationality_arr,
												//received_documents_arr: received_documents_arr,
												appointment_date_arr: appointment_date_arr,
												bank_id: bank_id,
												due_date: due_date,
												balance_date: booking_date,
												branch_admin_id: branch_admin_id,
												financial_year_id: financial_year_id,
												markup: markup,
												service_tax_markup: service_tax_markup,
												reflections: reflections,
												bsmValues: bsmValues,
												roundoff: roundoff,
												credit_charges: credit_charges,
												credit_card_details: credit_card_details,
												currency_code:currency_code
											},
											success: function(result) {
												var msg = result.split('-');

												if (msg[0] == 'error') {
													$('#btn_visa_master_save').prop('disabled', false);
													msg_alert(result);
													$('#btn_visa_master_save').button('reset');
												} else {
													var msg1 = result.split('-');
													$('#btn_visa_master_save').prop('disabled', false);
													$('#btn_visa_master_save').button('reset');
													msg_alert(msg1[0]);
													reset_form('frm_visa_save');
													$('#visa_save_modal').modal('hide');
													visa_customer_list_reflect();
													window.open(base_url+'view/vendor/dashboard/estimate/estimate_save_modal.php?type=Visa&amount='+visa_issue_amount+'&booking_id='+msg1[1]);
													setTimeout(() => {
														if ($('#whatsapp_switch').val() == "on") whatsapp_send(emp_id, customer_id, booking_date, base_url, country_code+contact_no,email_id);
													}, 1000);
												}
											}
										});
									}
								}
							});
						}
					});
			}
		});

	});

	function business_rule_load() {
		get_auto_values('booking_date', 'visa_issue_amount', 'payment_mode', 'service_charge', 'markup', 'save', 'true', 'service_charge', 'basic');
	}

	function reflect_cost(id) {

		var offset = id.substr(9);
		var visa_type = $('#visa_type' + offset).val(),
			visa_country = $('#visa_country_name' + offset).val();
		$.post('<?= BASE_URL ?>view/visa_passport_ticket/visa/home/visa_cost_reflect.php', {
			visa_type: visa_type,
			visa_country: visa_country
		}, function(data) {
			var json_data = JSON.parse(data);
			var amount = isNaN(parseFloat(json_data.amount)) ? 0.00 : parseFloat(json_data.amount);
			var service_charge = isNaN(parseFloat(json_data.service)) ? 0.00 : parseFloat(json_data.service);
			$('#visa_cost' + offset).val(amount+'-'+service_charge);
			alltable_visa_cost('tbl_dynamic_visa');
		});
	}
	function alltable_visa_cost(id){
		var table = document.getElementById(id);
		$('#visa_issue_amount').val(0);
		$('#markup').val(0);
		var rowCount = table.rows.length;
		var total_amt = 0, total_markup = 0;
		for(var i=0; i<rowCount; i++){
			var row = table.rows[i];
			if(row.cells[0].childNodes[0].checked == true){
				var amt1 = row.cells[14].childNodes[0].value;
				var amt = amt1.split('-');
				total_amt += parseFloat(amt[0]);
				total_markup += parseFloat(amt[1]);
			}
		}
		if(isNaN(total_amt)) total_amt = 0;
		if(isNaN(total_markup)) total_markup = 0;
		$('#visa_issue_amount').val(total_amt);$('#visa_issue_amount').trigger('change');
		$('#markup').val(total_markup);$('#markup').trigger('change');
	}

	$('#visa_save_modal').on('hidden.bs.modal', function() {
		reset_form('frm_visa_save');
	})
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<?php
$booking_id = $_POST['booking_id'];

$sq_est_info = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$booking_id'"));

$query1 = "SELECT * from package_payment_master where booking_id='$booking_id'";

$sq_tour_paid_amount = 0;
$tour_pending_cancel = 0;

$sq_package_tour_payment = mysqlQuery($query1);
while ($row_package_tour_payment = mysqli_fetch_assoc($sq_package_tour_payment)) {
	if ($row_package_tour_payment['clearance_status'] == "Pending" || $row_package_tour_payment['clearance_status'] == "Cancelled") {
		$tour_pending_cancel = $tour_pending_cancel + $row_package_tour_payment['amount'];
	}
	$sq_tour_paid_amount = $sq_tour_paid_amount + $row_package_tour_payment['amount'];
}

$query = "SELECT * from package_payment_master where booking_id='$booking_id' AND payment_for='Travelling'";

$sq_paid_amount = 0;
$pending_cancel = 0;

$sq_package_payment = mysqlQuery($query);
while ($row_package_payment = mysqli_fetch_assoc($sq_package_payment)) {

	if ($row_package_payment['clearance_status'] == "Pending" || $row_package_payment['clearance_status'] == "Cancelled") {
		$pending_cancel = $pending_cancel + $row_package_payment['amount'];
	}
	$sq_paid_amount = $sq_paid_amount + $row_package_payment['amount'];
}

$sq_pck_info = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id' and delete_status='0'"));

$paid_amount = ($sq_paid_amount - $pending_cancel) + ($sq_tour_paid_amount - $tour_pending_cancel);
?>
<hr>
<input type="hidden" id="total_sale" name="total_sale" value="<?= $sq_pck_info['net_total']  ?>">
<input type="hidden" id="total_paid" name="total_paid" value="<?= $paid_amount ?>">

<div class="row">
	<div class="col-sm-6 col-sm-offset-3 col-xs-12">
		<div class="widget_parent-bg-img bg-green">
			<div class="widget_parent">
				<div class="stat_content main_block">
					<span class="main_block content_span">
						<span class=" stat_content-tilte pull-left">Total Paid</span>
						<span class="stat_content-amount pull-right"><?php echo number_format(($sq_tour_paid_amount - $tour_pending_cancel), 2); ?></span>
					</span>
				</div>
			</div>
		</div>

	</div>
</div>
<hr>
<?php
$sq_c_info = mysqli_num_rows(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$booking_id'"));
$sq_p_info = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$booking_id'"));

$e_cancel_amount = ($sq_c_info > 0) ? $sq_p_info['cancel_amount'] : '';
$e_tax_amount = ($sq_c_info > 0) ? $sq_p_info['tax_amount'] : '';
$e_cancel_amount_exc = ($sq_c_info > 0) ? $sq_p_info['cancel_amount_exc'] : '';
$e_total_refund_amount = ($sq_c_info > 0) ? $sq_p_info['total_refund_amount'] : '';
$pass_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id'"));
$cancle_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id' and status='Cancel'"));
?>
<form id="frm_refund" class="mg_bt_30">
	<div class="row">
		<div class="col-md-12 text-center mt-5 mb-5" style="margin-bottom: 20px;">
			<h4>Refund Estimate</h4>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<input type="text" name="cancel_amount" id="cancel_amount" class="text-right" placeholder="*Cancel amount(Tax Incl)" title="Cancel amount(Tax Incl)" onchange="validate_balance(this.id);calculate_total_refund()" value="<?= $e_cancel_amount ?>">
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<select title="Select Tax" id="tax_value" name="tax_value" class="form-control" onchange="calculate_total_refund();">
			<?php
			if($sq_c_info == '0'){ ?>
				<option value="">*Select Tax</option>
				<?php get_tax_dropdown('Income') ?>
			<?php }else{
				?>
				<option value="<?= $sq_p_info['tax_value'] ?>"><?= $sq_p_info['tax_value'] ?></option>
			<?php } ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<input type="text" title="Tax Subtotal" id="tour_service_tax_subtotal" name="tour_service_tax_subtotal" value="<?= $e_tax_amount ?>" readonly>
			<input type="hidden" id="ledger_posting" />
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<input type="text" name="cancel_amount_exc" id="cancel_amount_exc" class="text-right" placeholder="*Cancellation Charges" title="Cancellation Charges" onchange="validate_balance(this.id);calculate_total_refund()" value="<?= $e_cancel_amount_exc ?>">
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_tp_10 mg_bt_10_xs">
			<input type="text" name="total_refund_amount" id="total_refund_amount" class="amount_feild_highlight text-right" placeholder="Total Refund" title="Total Refund" readonly value="<?= $e_total_refund_amount ?>">
		</div>
	</div>
	<?php if ($pass_count != $cancle_count || $sq_c_info == '0') { ?>
		<div class="row mg_tp_20">
			<div class="col-md-12 text-center">
				<button id="btn_refund_save" class="btn btn-sm btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
			</div>
		</div>
	<?php } ?>

</form>

<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>

<script>
	function calculate_total_refund() {
		var total_refund_amount = 0;
		var applied_taxes = '';
		var ledger_posting = '';
		var cancel_amount = $('#cancel_amount').val();
		var total_sale = $('#total_sale').val();
		var total_paid = $('#total_paid').val();
		var tax_value = $('#tax_value').val();

		if (cancel_amount == "") {
			cancel_amount = 0;
		}
		if (total_paid == "") {
			total_paid = 0;
		}

		if (parseFloat(cancel_amount) > parseFloat(total_sale)) {
			error_msg_alert("Cancel amount can not be greater than Sale amount");
		}
		if(tax_value!=""){

			var service_tax_subtotal1 = tax_value.split("+");
			for(var i=0;i<service_tax_subtotal1.length;i++){
				var service_tax_string = service_tax_subtotal1[i].split(':');
				if(parseInt(service_tax_string.length) > 0){
					var service_tax_string1 = service_tax_string[1] && service_tax_string[1].split('%');
					service_tax_string1[0] = service_tax_string1[0] && service_tax_string1[0].replace('(','');
					service_tax = service_tax_string1[0];
				}

				service_tax_string[2] = service_tax_string[2].replace('(','');
				service_tax_string[2] = service_tax_string[2].replace(')','');
				service_tax_amount = (( parseFloat(cancel_amount) * parseFloat(service_tax) ) / 100).toFixed(2);
				if(applied_taxes==''){
					applied_taxes = service_tax_string[0] +':'+ service_tax_string[1] + ':' + service_tax_amount;
					ledger_posting = service_tax_string[2];
				}else{
					applied_taxes += ', ' + service_tax_string[0] +':'+ service_tax_string[1] + ':' + service_tax_amount;
					ledger_posting += ', ' + service_tax_string[2];
				}
			}
		}
		$('#tour_service_tax_subtotal').val(applied_taxes);
		var service_tax_subtotal = $('#tour_service_tax_subtotal').val();
		if (service_tax_subtotal == "") {
			service_tax_subtotal = '';
		}
		var service_tax_amount = 0;
		if (parseFloat(service_tax_subtotal) !== 0.0 && service_tax_subtotal !== '') {
			var service_tax_subtotal1 = service_tax_subtotal.split(',');
			for (var i = 0; i < service_tax_subtotal1.length; i++) {
				var service_tax = service_tax_subtotal1[i].split(':');
				service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
			}
		}
		var total_refund_amount = parseFloat(total_paid) - parseFloat(cancel_amount);
		var cancel_amount_exc = parseFloat(cancel_amount) - parseFloat(service_tax_amount);
		if (parseFloat(total_refund_amount) < 0) {
			total_refund_amount = 0;
		}
		$('#cancel_amount_exc').val(cancel_amount_exc);
		$('#ledger_posting').val(ledger_posting);
		$('#total_refund_amount').val(total_refund_amount.toFixed(2));
	}

	$('#frm_refund').validate({
		rules: {
			cancel_amount: {
				required: true
			},
			tax_value: {
				required: true
			},
			refund_amount: {
				required: true,
				number: true
			},
			total_refund_amount: {
				required: true,
				number: true
			},
		},
		submitHandler: function(form) {

			$('#btn_refund_save').prop('disabled', true);
			var booking_id = $('#booking_id').val();
			var cancel_amount = $('#cancel_amount').val();
			var total_refund_amount = $('#total_refund_amount').val();
			var total_sale = $('#total_sale').val();
			var total_paid = $('#total_paid').val();
			var tax_value = $('#tax_value').val();
			var tour_service_tax_subtotal = $('#tour_service_tax_subtotal').val();
			var cancel_amount_exc = $('#cancel_amount_exc').val();
			var ledger_posting = $('#ledger_posting').val();

			if (parseFloat(cancel_amount) > parseFloat(total_sale)) {
				error_msg_alert("Cancel amount can not be greater than Sale amount");
				$('#btn_refund_save').prop('disabled', false);
				return false;
			}

			$('#btn_refund_save').button('loading');
			$('#vi_confirm_box').vi_confirm_box({

				callback: function(data1) {

					if (data1 == "yes") {
						$.ajax({
							type: 'post',
							url: base_url() + 'controller/package_tour/cancel_and_refund/booking_refund_estimate.php',
							data: {
								booking_id: booking_id,
								cancel_amount: cancel_amount,
								total_refund_amount: total_refund_amount,
								tax_value:tax_value,
								tour_service_tax_subtotal:tour_service_tax_subtotal,
								cancel_amount_exc:cancel_amount_exc,
								ledger_posting:ledger_posting
							},
							success: function(result) {
								msg_alert(result);
								$('#btn_refund_save').prop('disabled', false);
								cancel_booking_reflect();
							}

						});

					} else {
						$('#btn_refund_save').button('reset');
					}
				}
			});
		}

	});
</script>
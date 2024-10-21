<?php
$sq_tour_paid_amount=0;
$tour_pending_cancel = 0;
$sq_est_info = mysqli_fetch_assoc(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$tourwise_id'"));

$sq_group_tour_payment = mysqlQuery("SELECT * from payment_master where tourwise_traveler_id='$tourwise_id' ");	
while($row_group_tour_payment = mysqli_fetch_assoc($sq_group_tour_payment)){

	if($row_group_tour_payment['clearance_status']=="Pending" || $row_group_tour_payment['clearance_status']=="Cancelled"){ 
		$tour_pending_cancel = $tour_pending_cancel + $row_group_tour_payment['amount'];
	}
	$sq_tour_paid_amount = $sq_tour_paid_amount + $row_group_tour_payment['amount'];
}

$sq_paid_amount=0;
$pending_cancel = 0;
$sq_group_info = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$tourwise_id' and delete_status='0'"));
$paid_amount =  ($sq_tour_paid_amount - $tour_pending_cancel );
?>
<input type="hidden" id="total_sale" name="total_sale" value="<?= $sq_group_info['net_total'] ?>">	        
<input type="hidden" id="total_paid" name="total_paid" value="<?= $paid_amount ?>">	

<div class="row mg_tp_20">
	<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-12">
		<div class="widget_parent-bg-img bg-green">
			<div class="widget_parent">
				<div class="stat_content main_block">
				<span class="main_block content_span">
					<span class="stat_content-tilte pull-left">Total Paid</span>
					<span class="stat_content-amount pull-right"><?php echo number_format(($sq_tour_paid_amount - $tour_pending_cancel ) ,2);?></span>
				</span>
				</div>
			</div>
		</div>
	</div>
</div>
<hr>
<?php
$sq_tour_info = mysqli_fetch_assoc(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$tourwise_id'"));
$sq_tour_count = mysqli_num_rows(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$tourwise_id'"));
if($sq_tour_count > 0){
	$ecancel_amount = $sq_tour_info['cancel_amount'];
	$etax_amount = $sq_tour_info['tax_amount'];
	$ecancel_amount_exc = $sq_tour_info['cancel_amount_exc'];
	$etotal_refund_amount = $sq_tour_info['total_refund_amount'];
}else{
	$ecancel_amount = '';
	$etax_amount = '';
	$ecancel_amount_exc = '';
	$etotal_refund_amount = '';
}
?>
<form id="frm_refund">
<div class="row">
		<div class="col-md-12 text-center mt-5 mb-5" style="margin-bottom: 20px;">
			<h4>Refund Estimate</h4>
		</div>
	</div>
	<div class="row text-center">
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<input type="text" name="cancel_amount" id="cancel_amount" class="text-right" placeholder="*Cancel amount(Tax Incl)" title="Cancel amount(Tax Incl)" onchange="validate_balance(this.id);calculate_total_refund()" value="<?= $ecancel_amount ?>">
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<select title="Select Tax" id="tax_value" name="tax_value" class="form-control" onchange="calculate_total_refund();">
			<?php
			if($sq_tour_count =='0'){ ?>
				<option value="">*Select Tax</option>
				<?php get_tax_dropdown('Income') ?>
			<?php }else{
				?>
				<option value="<?= $sq_tour_info['tax_value'] ?>"><?= $sq_tour_info['tax_value'] ?></option>
			<?php } ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<input type="text" title="Tax Subtotal" class="form-control text-right" id="tour_service_tax_subtotal" name="tour_service_tax_subtotal" value="<?= $etax_amount ?>" readonly>
			<input type="hidden" id="ledger_posting" />
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<input type="text" name="cancel_amount_exc" id="cancel_amount_exc" class="form-control text-right" placeholder="*Cancellation Charges" title="Cancellation Charges" onchange="validate_balance(this.id);calculate_total_refund()" value="<?= $ecancel_amount_exc ?>">
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_tp_10 mg_bt_10_xs">
			<input type="text" name="total_refund_amount" id="total_refund_amount" class="amount_feild_highlight text-right" placeholder="Total Refund" title="Total Refund" readonly value="<?= $etotal_refund_amount ?>">
		</div>
	</div>
	<?php
	if($sq_tour_count =='0'){ ?>
		<div class="row mg_tp_20">
			<div class="col-md-6 col-md-offset-3 text-center">
				<button id="btn_refund_save" class="btn btn-sm btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
			</div>
		</div>
	<?php } ?>
</form>
<hr>
<script>
function calculate_total_refund()
{
	var total_refund_amount = 0;
    var applied_taxes = '';
    var ledger_posting = '';
	var cancel_amount = $('#cancel_amount').val();
	var total_sale = $('#total_sale').val();
	var total_paid = $('#total_paid').val();
	var tax_value = $('#tax_value').val();

	if(cancel_amount==""){ cancel_amount = 0; }
	if(total_paid==""){ total_paid = 0; }

	if(parseFloat(cancel_amount) > parseFloat(total_sale)) { error_msg_alert("Cancel amount can not be greater than Sale amount"); }
	var total_refund_amount = parseFloat(total_paid) - parseFloat(cancel_amount);

	if(parseFloat(total_refund_amount) < 0){ 
		total_refund_amount = 0;
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
    
    var cancel_amount_exc = parseFloat(cancel_amount) - parseFloat(service_tax_amount);
    $('#cancel_amount_exc').val(cancel_amount_exc);
    $('#ledger_posting').val(ledger_posting);
	$('#total_refund_amount').val(total_refund_amount.toFixed(2));
}
$('#frm_refund').validate({
	rules:{
		cancel_amount :{ required : true, number : true },
		total_refund_amount :{ required : true, number : true },	
		tax_value: { required: true }
	},
	submitHandler:function(form){

		$('#btn_refund_save').prop('disabled',true);
		var tourwise_id = $('#txt_tourwise_traveler_id').val();
		var cancel_amount = $('#cancel_amount').val();
		var total_refund_amount = $('#total_refund_amount').val();
		var total_sale = $('#total_sale').val();
		var total_paid = $('#total_paid').val();
        var tax_value = $('#tax_value').val();
        var tour_service_tax_subtotal = $('#tour_service_tax_subtotal').val();
        var cancel_amount_exc = $('#cancel_amount_exc').val();
        var ledger_posting = $('#ledger_posting').val();

		if(parseFloat(cancel_amount) > parseFloat(total_sale)) { error_msg_alert("Cancel amount can not be greater than Sale amount");
		$('#btn_refund_save').prop('disabled',false); return false; }

		$('#vi_confirm_box').vi_confirm_box({
			message: 'Are you sure?',
			callback: function(data1) {
				if (data1 == "yes") {
					$('#btn_refund_save').button('loading');
					$.ajax({
						type:'post',
						url: base_url()+'controller/group_tour/tour_cancelation_and_refund/booking_tour_refund_estimate.php',
						data: { tourwise_id : tourwise_id,cancel_amount : cancel_amount, total_refund_amount : total_refund_amount,tax_value:tax_value,tour_service_tax_subtotal:tour_service_tax_subtotal,cancel_amount_exc:cancel_amount_exc,ledger_posting:ledger_posting },
						success:function(result){
							msg_alert(result);
							reset_form('frm_refund');
							$('#btn_refund_save').prop('disabled',false);
							$('#btn_refund_save').button('reset');
							refund_cancelled_tour_group_reflect();
						}
					});
				}else{
					$('#btn_refund_save').prop('disabled',false);
					$('#btn_refund_save').button('reset');

				}
			}
		});
	}
});
</script>
<script src="<?= BASE_URL?>/view/tour_cancelation_and_refund/js/refund_cancelled_tour.js"></script>
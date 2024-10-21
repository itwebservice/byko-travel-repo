<?php
include "../../../../../model/model.php";

$ticket_id = $_POST['ticket_id'];
$sales_return = $_POST['sales_return'];

$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$ticket_id' and delete_status='0'"));
$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from ticket_payment_master where ticket_id='$ticket_id' AND clearance_status!='Pending' AND clearance_status!='Cancelled'"));
$service_tax_amount = 0;
if($sq_ticket_info['service_tax_subtotal'] !== 0.00 && ($sq_ticket_info['service_tax_subtotal']) !== ''){
	$service_tax_subtotal1 = explode(',',$sq_ticket_info['service_tax_subtotal']);
	for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
		$service_tax = explode(':',$service_tax_subtotal1[$i]);
		$service_tax_amount = $service_tax_amount + $service_tax[2];
	}
}
$markupservice_tax_amount = 0;
if($sq_ticket_info['service_tax_markup'] !== 0.00 && $sq_ticket_info['service_tax_markup'] !== ""){
	$service_tax_markup1 = explode(',',$sq_ticket_info['service_tax_markup']);
	for($i=0;$i<sizeof($service_tax_markup1);$i++){
		$service_tax = explode(':',$service_tax_markup1[$i]);
		$markupservice_tax_amount = $markupservice_tax_amount+ $service_tax[2];
	}
}
?>
<input type="hidden" id="total_sale" name="total_sale" value="<?= $sq_ticket_info['ticket_total_cost']?>">	        
<input type="hidden" id="total_paid" name="total_paid" value="<?= $sq_payment_info['sum']?>">	  
<input type="hidden" id="sales_return_value" name="sales_return_value" value="<?= $sales_return ?>">	  
<input type="hidden" id="ticket_id" name="ticket_id" value="<?= $ticket_id ?>">	  
<div class="row mg_tp_20">
	<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10_xs">
        <div class="widget_parent-bg-img bg-img-red">
            <div class="widget_parent">
                <div class="stat_content main_block">
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Basic Amount</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="obasic_cost"><?= $sq_ticket_info['basic_cost'] ?></span>
                    </span>
                    <span class="main_block content_span" data-original-title="" title="">                    
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">YQ Tax</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="oyq_tax"><?= $sq_ticket_info['yq_tax'] ?></span>
                    </span>
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Other Taxes</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="oother_taxes"><?= $sq_ticket_info['other_taxes'] ?></span>
                    </span>  
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Discount</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="odiscount"><?= $sq_ticket_info['basic_cost_discount'] ?></span>
                    </span>                      
                </div> 
            </div>
        </div>
    </div>
	<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10_xs">
        <div class="widget_parent-bg-img bg-img-purp">
            <div class="widget_parent">
                <div class="stat_content main_block">  
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Service Charge</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="oservice_charge"><?= $sq_ticket_info['service_charge'] ?></span>
                    </span>  
                    <span class="main_block content_span" data-original-title="" title="">                    
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Tax Amount</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="oservice_tax_subtotal"><?= $service_tax_amount ?></span>
                    </span>      
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Markup</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="omarkup"><?= $sq_ticket_info['markup'] ?></span>
                    </span>         
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Markup Tax</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="oservice_tax_markup"><?= $markupservice_tax_amount ?></span>
                    </span>          
                </div> 
            </div>
        </div>
    </div>
	<div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10_xs">
        <div class="widget_parent-bg-img bg-img-green">
            <div class="widget_parent">     
                <div class="stat_content main_block">            
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">TDS</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="otds"><?= $sq_ticket_info['tds'] ?></span>
                    </span>         
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Roundoff</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="oroundoff"><?= number_format($sq_ticket_info['roundoff'], 2) ?></span>
                    </span>             
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Net Total</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title="" id="oticket_total_cost"><?= $sq_ticket_info['ticket_total_cost'] ?></span>
                    </span>  
                    <span class="main_block content_span" data-original-title="" title="">
                        <span class="stat_content-tilte pull-left" data-original-title="" title="">Paid Amount</span>
                        <span class="stat_content-amount pull-right" data-original-title="" title=""><?= ($sq_payment_info['sum'] == "") ?  "0.00" : $sq_payment_info['sum'] ?></span>
                    </span>                 
                </div> 
            </div>
        </div>
    </div>
</div>

<?php
if($sq_ticket_info['cancel_type'] == 0){ ?>
    <div class="row">
        <div class="col-md-12 col-sm-10  col-xs-12 mg_tp_10">
            <input type="checkbox" id="check_all" name="check_all" onClick="select_all_check(this.id,'traveler_names')">&nbsp;&nbsp;&nbsp;<span style="text-transform: initial;">Check All</span>
        </div>
    </div>
<?php } ?>
<div class="row mg_bt_30">
	<div class="col-xs-12 no-pad"> <div class="table-responsive">
		<table class="table table-bordered table-hover mg_bt_0">
            <?php
            if($sales_return == 1 || $sales_return == 2){
                ?>
                <thead>
                    <tr class="table-heading-row">
                        <th>S_No.</th>
                        <th>Passenger_Name</th>
                        <th>Cancel</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $count = 0;
                $disabled_count= 0;
                $sq_ticket_entries = mysqlQuery("select * from ticket_master_entries where ticket_id='$ticket_id'");
                while($row_entry = mysqli_fetch_assoc($sq_ticket_entries)){

                    if($row_entry['status']=="Cancel"){
                        $bg = "danger"; 
                        $checked = "checked disabled";
                        ++$disabled_count;
                    }
                    else{
                        $bg = ""; 
                        $checked = "";
                    }
                    ?>
                    <tr class="<?= $bg ?>">
                        <td><?= ++$count ?></td>
                        <td><?= $row_entry['first_name'].' '.$row_entry['last_name'] ?></td>
                        <td>
                            <input type="checkbox" id="chk_entry_id_<?= $count ?>" class="traveler_names" name="chk_entry_id" <?= $checked ?> value="<?= $row_entry['entry_id'] ?>">
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            <?php }
            else{
                ?>
                <thead>
                    <tr class="table-heading-row">
                        <th>S_No.</th>
                        <th>Passenger_Name</th>
                        <th>Sector(From_To)</th>
                        <th>Cancel</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $count = 0;
                $disabled_count= 0;
                $sq_ticket_entries = mysqlQuery("select * from ticket_trip_entries where ticket_id='$ticket_id'");
                while($row_entry = mysqli_fetch_assoc($sq_ticket_entries)){

                    if($row_entry['status']=="Cancel"){
                        $bg = "danger"; 
                        $checked = "checked disabled";
                        ++$disabled_count;
                    }
                    else{
                        $bg = "";
                        $checked = "";
                    }
                    $sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$row_entry[passenger_id]'"));
                    ?>
                    <tr class="<?= $bg ?>">
                        <td><?= ++$count ?></td>
                        <td><?= $sq_pass['first_name'].' '.$sq_pass['last_name'] ?></td>
				        <td><?= $row_entry['departure_city'].' -- '.$row_entry['arrival_city'] ?></td>
                        <td>
                            <input type="checkbox" id="chk_entry_id_<?= $count ?>" class="traveler_names" name="chk_entry_id" <?= $checked ?> value="<?= $row_entry['entry_id'] ?>">
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            <?php } ?>
		</table>
        <input type="hidden" id="pass_count" name="pass_count" value="<?= $count ?>">
        <input type="hidden" id="disabled_count" name="disabled_count" value="<?= $disabled_count ?>">
		<?php
		if($sq_ticket_info['cancel_type'] == 0){ ?>
            <div class="panel panel-default panel-body mg_tp_20 text-center">
                <button class="btn btn-danger btn-sm ico_left" id="cancel_booking" onclick="cancel_booking()"><i class="fa fa-times"></i>&nbsp;&nbsp;Cancel Booking</button>
            </div>
        <?php } ?>
		</div> </div> 

</div>
<?php
$sq_cancel_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$ticket_id' and status='Cancel'"));
$sq_tripcancel_count = mysqli_num_rows(mysqlQuery("select * from ticket_trip_entries where ticket_id='$ticket_id' and status='Cancel'"));
if($sq_cancel_count>0||$sq_tripcancel_count>0){
	if($sq_ticket_info['cancel_amount'] == "0.00"){
		$refund_amount = $sq_payment_info['sum'];
	}else{
		$refund_amount = $sq_ticket_info['total_refund_amount'];
	}
$cancel_estimate = (isset($sq_ticket_info['cancel_estimate']) && $sq_ticket_info['cancel_estimate']!='') ? json_decode($sq_ticket_info['cancel_estimate']) : [];
$markup = ''; $service_tax_markup = ''; $roundoff = ''; $ticket_total_cost = '';
$basic_cost = ''; $other_taxes = ''; $yq_tax = ''; $service_charge = ''; $discount = ''; $tds = ''; $service_tax_subtotal = '';
if(sizeof($cancel_estimate) >0){
    $basic_cost = floatval($cancel_estimate[0]->basic_cost);
    $other_taxes = floatval($cancel_estimate[0]->other_taxes);
    $yq_tax = floatval($cancel_estimate[0]->yq_tax);
    $service_charge = floatval($cancel_estimate[0]->service_charge);
    $discount = floatval($cancel_estimate[0]->discount);
    $service_tax_subtotal = floatval($cancel_estimate[0]->service_tax_subtotal);
    $tds = floatval($cancel_estimate[0]->tds);
    $markup = floatval($cancel_estimate[0]->markup);
    $service_tax_markup = floatval($cancel_estimate[0]->service_tax_markup);
    $roundoff = floatval($cancel_estimate[0]->roundoff);
    $ticket_total_cost = floatval($cancel_estimate[0]->ticket_total_cost);
}
?>
<legend class="text-center">Cancellation Estimation</legend>
<form id="frm_refund">

    <div class="row mg_tp_20">
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small id="basic_show">Basic Amount</small>
            <input type="number" id="basic_cost" name="basic_cost" placeholder="Basic Amount" title="Basic Amount"  onchange="calculate_total_amount(this.id);" value="<?= floatval($basic_cost) ?>">
        </div>
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small>YQ Tax</small>
            <input type="number" id="yq_tax" name="yq_tax" placeholder="YQ Tax" title="YQ Tax" onchange="calculate_total_amount(this.id);" value="<?= floatval($yq_tax) ?>">
        </div>
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small>Other Taxes</small>
            <input type="number" id="other_taxes" name="other_taxes" placeholder="Other Taxes" title="Other Taxes" onchange="calculate_total_amount(this.id);" value="<?= floatval($other_taxes) ?>" >
        </div>
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small id="discount_show">Discount</small>
            <input type="number" id="discount" name="discount" placeholder="Discount" title="Discount" onchange="calculate_total_amount(this.id);" value="<?= floatval($discount) ?>" >
        </div>
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small id="service_show">Service Charge</small>
            <input type="number" id="service_charge" name="service_charge" placeholder="Service Charge" title="Service Charge" onchange="calculate_total_amount(this.id);" value="<?= floatval($service_charge) ?>" >
        </div>
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small>Tax Amount</small>
            <input type="number" id="service_tax_subtotal" name="service_tax_subtotal" placeholder="Tax Amount" title="Tax Amount" onchange="calculate_total_amount(this.id);" value="<?= floatval($service_tax_subtotal) ?>">
        </div>
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small id="markup_show">Markup</small>
            <input type="text" id="markup" name="markup" placeholder="Markup" title="Markup" onchange="calculate_total_amount(this.id);" value="<?= $markup ?>" >
        </div>
        <div class="col-sm-3 col-xs-12 mg_bt_10">
            <small>Tax on Markup</small>
            <input type="text" id="service_tax_markup" name="service_tax_markup" placeholder="Tax on Markup" title="Tax on Markup" onchange="calculate_total_amount(this.id);" value="<?= $service_tax_markup ?>">
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 mg_bt_10">
            <small>TDS</small>
            <input type="number" id="tds" name="tds" placeholder="TDS" title="TDS" onchange="calculate_total_amount(this.id);validate_balance(this.id)" value="<?= floatval($tds) ?>">
        </div>		
        <div class="col-md-3 col-sm-3 col-xs-12 mg_bt_10">
            <small>Roundoff</small>
            <input type="text" name="roundoff" id="roundoff" class="text-right" placeholder="Round Off" title="RoundOff" value="<?= $roundoff ?>" readonly>
        </div>	
        <div class="col-md-3 col-sm-3 col-xs-12 mg_bt_10_sm_xs">
            <small>Net Total</small>
            <input type="text" name="ticket_total_cost" id="ticket_total_cost" class="amount_feild_highlight text-right" placeholder="Net Total" title="Net Total" readonly value="<?= $ticket_total_cost ?>">
        </div>
    </div>
	<div class="row mg_tp_30 mg_bt_10">
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
			<input type="text" name="cancel_amount" id="cancel_amount" class="text-right" placeholder="*Cancel amount(Tax Incl)" title="Cancel amount(Tax Incl)" onchange="validate_balance(this.id);calculate_total_refund()" value="<?= $sq_ticket_info['cancel_amount'] ?>">
		</div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
            <select title="Select Tax" id="tax_value" name="tax_value" class="form-control" onchange="calculate_total_refund();">
            <?php
            if($sq_ticket_info['cancel_flag'] == 0){ ?>
                <option value="">*Select Tax</option>
                <?php get_tax_dropdown('Income') ?>
            <?php }else{
                ?>
                <option value="<?= $sq_ticket_info['tax_value'] ?>"><?= $sq_ticket_info['tax_value'] ?></option>
            <?php } ?>
            </select>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
            <input type="text" title="Tax Subtotal" id="tour_service_tax_subtotal" name="tour_service_tax_subtotal" value="<?= $sq_ticket_info['tax_amount'] ?>" readonly>
            <input type="hidden" id="ledger_posting" />
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
            <input type="text" name="cancel_amount_exc" id="cancel_amount_exc" class="text-right" placeholder="*Cancellation Charges" title="Cancellation Charges" onchange="validate_balance(this.id);calculate_total_refund()" value="<?= $sq_ticket_info['cancel_amount_exc'] ?>">
        </div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs mg_tp_10">
			<input type="text" name="total_refund_amount" id="total_refund_amount" class="amount_feild_highlight text-right" placeholder="Total Refund" title="Total Refund" readonly value="<?= $refund_amount ?>">
		</div>
	</div>
	<?php if($sq_ticket_info['cancel_flag'] == 0){ ?>
	<div class="row mg_tp_20">
        <div class="col-md-12 text-center">
            <button id="btn_refund_save" class="btn btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
        </div>
	</div>
	<?php } ?>
</form>
<?php } ?>

<script>
function calculate_total_amount(id){

    var basic_cost = $('#basic_cost').val(); 
    var yq_tax = $('#yq_tax').val();	
    var other_taxes = $('#other_taxes').val();	
    var discount = $('#discount').val(); 
    var service_charge = $('#service_charge').val();
    var service_tax_subtotal = $('#service_tax_subtotal').val();
    var markup = $('#markup').val();
    var service_tax_markup = $('#service_tax_markup').val();
    var tds = $('#tds').val();

    // For validations
    var obasic_cost = $('#obasic_cost').html(); 
    var oyq_tax = $('#oyq_tax').html();		
    var oother_taxes = $('#oother_taxes').html(); 
    var odiscount = $('#odiscount').html();
    var oservice_charge = $('#oservice_charge').html();
    var oservice_tax_subtotal = $('#oservice_tax_subtotal').html();
    var omarkup = $('#omarkup').html();
    var oservice_tax_markup = $('#oservice_tax_markup').html();
    var otds = $('#otds').html();

    if(basic_cost==""){ basic_cost = 0; }
    if(markup==""){ markup = 0; }
    if(discount==""){ discount = 0; }
    if(yq_tax==""){ yq_tax = 0; }
    if(other_taxes==""){ other_taxes = 0; }
    if(service_charge==""){ service_charge = 0; }
    if(service_tax_subtotal == '') { service_tax_subtotal = 0; }
    if(tds==""){ tds = 0; }
    if(service_tax_markup==""){ service_tax_markup = 0; }

    if(parseFloat(basic_cost) > parseFloat(obasic_cost)){
        error_msg_alert("Basic amount should not be greater than invoice basic amount!");
        return false;
    }
    if(parseFloat(yq_tax) > parseFloat(oyq_tax)){
        error_msg_alert("YQ tax should not be greater than invoice YQ tax!");
        return false;
    }
    if(parseFloat(other_taxes) > parseFloat(oother_taxes)){
        error_msg_alert("Other tax should not be greater than invoice other tax!");
        return false;
    }
    if(parseFloat(discount) > parseFloat(odiscount)){
        error_msg_alert("Discount should not be greater than invoice discount!");
        return false;
    }
    if(parseFloat(service_charge) > parseFloat(oservice_charge)){
        error_msg_alert("Service charge should not be greater than invoice service charge!");
        return false;
    }
    if(parseFloat(service_tax_subtotal) > parseFloat(oservice_tax_subtotal)){
        error_msg_alert("Tax amount should not be greater than invoice tax amount!");
        return false;
    }
    if(parseFloat(markup) > parseFloat(omarkup)){
        error_msg_alert("Markup should not be greater than invoice markup!");
        return false;
    }
    if(parseFloat(service_tax_markup) > parseFloat(oservice_tax_markup)){
        error_msg_alert("Markup tax should not be greater than invoice markup tax!");
        return false;
    }
    if(parseFloat(tds) > parseFloat(otds)){
        error_msg_alert("TDS should not be greater than invoice TDS!");
        return false;
    }
    var ticket_total_cost = parseFloat(basic_cost) + parseFloat(yq_tax) + parseFloat(other_taxes) + parseFloat(markup) + parseFloat(service_tax_markup) - parseFloat(discount) + parseFloat(service_charge) + parseFloat(service_tax_subtotal) - parseFloat(tds);

    ticket_total_cost = ticket_total_cost.toFixed(2);
    var roundoff = Math.round(ticket_total_cost)-ticket_total_cost;
    $('#roundoff').val(roundoff.toFixed(2));
    $('#ticket_total_cost').val(parseFloat(ticket_total_cost) + parseFloat(roundoff));
    $('#ticket_total_cost').trigger('change');
    
    return true;
}

function cancel_booking(){

	var entry_id_arr = new Array();
	$('input[name="chk_entry_id"]:checked').each(function(){
		entry_id_arr.push($(this).val());
	});

    var sales_return_value = $('#sales_return_value').val();
    var ticket_id = $('#ticket_id').val();
    //Validaion to select complete tour cancellation 
    var pass_count = $('#pass_count').val();
    var disabled_count = $('#disabled_count').val();
    var len = $('input[name="chk_entry_id"]:checked').length;
    if(sales_return_value == 1){
        if(len!=pass_count){
            error_msg_alert('Please select all passenger for cancellation.');
            return false;
        }
    }
    if((sales_return_value == 2 || sales_return_value == 3) && len == 0){
        error_msg_alert('Please select atleast one passenger for cancellation.');
        return false;
    }
    if(sales_return_value == 2 && pass_count == len){
        error_msg_alert('Please select Sales Return type as "Full".');
        return false;
    }
    if(sales_return_value == 3 && pass_count == len){
        error_msg_alert('Please select Sales Return type as "Full".');
        return false;
    }
    if(pass_count == disabled_count){
        error_msg_alert('All the Passengers have been already cancelled');
        return false;
    }
    else{
        $('#vi_confirm_box').vi_confirm_box({
        message: 'Are you sure?',
        callback: function(data1){
            if(data1=="yes"){

                var base_url = $('#base_url').val();
                $('#cancel_booking').button('loading');
                $.ajax({
                    type: 'post',
                    url: base_url+'controller/visa_passport_ticket/ticket/cancel/cancel_booking.php',
                    data:{ entry_id_arr : entry_id_arr,sales_return_value:sales_return_value,ticket_id:ticket_id },
                    success: function(result){
                        msg_alert(result);
                        $('#cancel_booking').button('reset');
                        content_reflect();
                    }
                });
            }
        }
    	});
    }
}


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
    var total_refund_amount = parseFloat(total_paid) - parseFloat(cancel_amount);
	
	if(parseFloat(total_refund_amount) < 0){ 
		total_refund_amount = 0;
	}
	$('#cancel_amount_exc').val(cancel_amount_exc);
	$('#ledger_posting').val(ledger_posting);
	$('#total_refund_amount').val(total_refund_amount.toFixed(2));
}

$(function(){
    $('#frm_refund').validate({
        rules:{
            ticket_id : { required: true },
            cancel_amount : { required : true, number : true },
			tax_value: { required: true }
        },
        submitHandler:function(form){

            var result = calculate_total_amount('basic_cost');
            if(result){
                var ticket_id = $('#ticket_id').val();
                var cancel_amount = $('#cancel_amount').val();
                var total_refund_amount = $('#total_refund_amount').val();
                var total_sale = $('#total_sale').val();
                var total_paid = $('#total_paid').val();
                var tax_value = $('#tax_value').val();
                var tour_service_tax_subtotal = $('#tour_service_tax_subtotal').val();
                var cancel_amount_exc = $('#cancel_amount_exc').val();
                var ledger_posting = $('#ledger_posting').val();

                if(parseFloat(cancel_amount) > parseFloat(total_sale)) { error_msg_alert("Cancel amount can not be greater than Sale amount"); return false; }
                
                var estimate_arr = [];
                var obasic_cost = $('#basic_cost').val(); 
                var oyq_tax = $('#yq_tax').val();		
                var oother_taxes = $('#other_taxes').val(); 
                var odiscount = $('#discount').val();
                var oservice_charge = $('#service_charge').val();
                var oservice_tax_subtotal = $('#service_tax_subtotal').val();
                var omarkup = $('#markup').val();
                var oservice_tax_markup = $('#service_tax_markup').val();
                var otds = $('#tds').val();
                var oroundoff = $('#roundoff').val();
                var oticket_total_cost = $('#ticket_total_cost').val();
                estimate_arr.push({
                    'basic_cost':obasic_cost,
                    'yq_tax':oyq_tax,
                    'other_taxes':oother_taxes,
                    'discount':odiscount,
                    'service_charge':oservice_charge,
                    'service_tax_subtotal':oservice_tax_subtotal,
                    'markup':omarkup,
                    'service_tax_markup':oservice_tax_markup,
                    'tds':otds,
                    'roundoff':oroundoff,
                    'ticket_total_cost':oticket_total_cost
                });
                
                var base_url = $('#base_url').val();

                $('#vi_confirm_box').vi_confirm_box({
                message: 'Estimation can not modify after save. Are you sure?',
                callback: function(data1){

                    if(data1=="yes"){

                        $('#btn_refund_save').button('loading');
                        $.ajax({
                            type:'post',
                            url: base_url+'controller/visa_passport_ticket/ticket/cancel/refund_estimate_update.php',
                            data:{ ticket_id : ticket_id,cancel_amount : cancel_amount, total_refund_amount : total_refund_amount,estimate_arr:estimate_arr,tax_value:tax_value,tour_service_tax_subtotal:tour_service_tax_subtotal,cancel_amount_exc:cancel_amount_exc,ledger_posting:ledger_posting},
                            success:function(result){
                            msg_alert(result);
                            content_reflect();
                            $('#btn_refund_save').button('reset');
                            },
                            error:function(result){
                            console.log(result.responseText);
                            }
                        });
                    }
                }
                });
            }
        }
    });

});
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<?php
include '../../../../model/model.php';
$role = $_SESSION['role'];
$emp_id = $_SESSION['emp_id'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='hotels/booking/index.php'"));
$branch_status = $sq['branch_status'];
$sq_tcs = mysqli_fetch_assoc(mysqlQuery("select * from tcs_master where entry_id='3'"));
$tcs_readonly = ($sq_tcs['calc'] == '0') ? 'readonly' : '';
?>
<div id="markup_confirm"></div>
<div class="modal fade" id="booking_save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static"
    data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="width: 95% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">New Hotel Booking</h4>
            </div>
            <div class="modal-body">
                <form id="frm_hotel_booking_save">
                    <input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>">
                    <input type="hidden" id="unique_timestamp" name="unique_timestamp" value="<?= md5(time()) ?>">
                    <input type="hidden" id="hotel_sc" name="hotel_sc">
                    <input type="hidden" id="hotel_markup" name="hotel_markup">
                    <input type="hidden" id="hotel_taxes" name="hotel_taxes">
                    <input type="hidden" id="hotel_markup_taxes" name="hotel_markup_taxes">
                    <input type="hidden" id="hotel_tds" name="hotel_tds">
                    <input type="hidden" id="tcs" name="tcs" value="<?= $sq_tcs['tax_amount'] ?>">
                    <input type="hidden" id="tcs_apply" name="tcs_apply" value="<?= $sq_tcs['apply'] ?>">
                    <input type="hidden" id="tcs_calc" name="tcs_calc" value="<?= $sq_tcs['calc'] ?>">

                    <div class="panel panel-default panel-body app_panel_style feildset-panel">
                        <legend>Quotation Details</legend>
                        <div class="row">
                            <div class="col-md-4">

                                <select name="quotation_id" id="quotation_id" style="width:100%"
                                    onchange="get_options(this);" class="form-control text-center">
                                    <option value="">Select Quotation</option>
                                    <?php
                                    $query = "select * from hotel_quotation_master where 1 and status='1' ";
                                    if ($role == 'Sales' || $role == 'Backoffice') {
                                        $query .= " and emp_id='$emp_id'";
                                    }
                                    if ($branch_status == 'yes' && $role != 'Admin') {
                                        $query .= " and branch_admin_id = '$branch_admin_id'";
                                    }
                                    if ($branch_status == 'yes' && $role == 'Branch Admin') {
                                        $query .= " and branch_admin_id='$branch_admin_id'";
                                    }

                                    $query .= " order by quotation_id desc";
                                    $sq_quotation = mysqlQuery($query);
                                    while ($row_quotation = mysqli_fetch_assoc($sq_quotation)) {
                                        $enqDetails = json_decode($row_quotation['enquiry_details']);
                                        $quotation_date = $row_quotation['quotation_date'];
                                        $yr = explode("-", $quotation_date);
                                        $year = $yr[0];
                                    ?>
                                    <option value="<?= $row_quotation['quotation_id'] ?>">
                                        <?= get_quotation_id($row_quotation['quotation_id'], $year) . ': ' . $enqDetails->customer_name ?>
                                    </option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" id="hotel_options" style="width:100%" onchange="get_quotation_details()">
                                    <option value="">Select Option</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default panel-body app_panel_style feildset-panel">
                        <legend>Customer Details</legend>

                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <select name="customer_id" id="customer_id" class="customer_dropdown" style="width:100%"
                                    title="Customer Name"
                                    onchange="customer_info_load();get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount',true);">
                                    <?php get_new_customer_dropdown($role, $branch_admin_id, $branch_status); ?>
                                </select>
                            </div>
                            <div id="cust_details">
                                <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                    <input type="text" id="mobile_no" name="mobile_no" title="Mobile Number"
                                        placeholder="Mobile No" title="Mobile No" readonly>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                    <input type="text" id="email_id" name="email_id" title="Email Id"
                                        placeholder="Email ID" title="Email ID" readonly>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                    <input type="text" id="company_name" class="hidden" name="company_name"
                                        title="Company Name" placeholder="Company Name" title="Company Name" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 col-xs-12 mg_bt_10">
                                <input type="text" id="pass_name" name="pass_name" placeholder="Guest Name"
                                    title="Guest Name">
                            </div>
                            <div class="col-sm-3 col-xs-12 mg_bt_10">
                                <input type="number" id="adults" name="adults" placeholder="Adult(s)" title="Adult(s)"
                                    onchange="validate_balance(this.id);get_hotel_cost();get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount',true);">
                            </div>
                            <div class="col-sm-3 col-xs-12 mg_bt_10">
                                <input type="number" id="childrens" name="childrens" placeholder="Child Without Bed(s)"
                                    title="Child Without Bed(s)" onchange="validate_balance(this.id);get_hotel_cost();get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount',true)">
                            </div>
                            <div class="col-sm-3 col-xs-12 mg_bt_10">
                                <input type="number" id="children_with" name="children_with" placeholder="Child With Bed(s)"
                                    title="Child With Bed(s)" onchange="validate_balance(this.id);get_hotel_cost();get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount',true)">
                            </div>
                            <div class="col-sm-3 col-xs-12 mg_bt_10">
                                <input type="number" id="infants" name="infants" placeholder="Infant(s)" title="Infant(s)"
                                    onchange="validate_balance(this.id)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_sm_xs">
                                <input type="text" id="credit_amount" class="hidden" name="credit_amount" placeholder="Credit Note Balance" title="Credit Note Balance" readonly>
                            </div>
                        </div>
                    </div>
                    <div id="new_cust_div"></div>
                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">
                        <legend>Hotel Details</legend>
                        <div class="row mg_bt_10">
                            <div class="col-md-6 text-left">
                                <button type="button" class="btn btn-excel btn-sm" title="Add Hotel" onclick="hotel_save_modal()"><i class="fa fa-plus"></i></button> 
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_hotel_booking')"><i class="fa fa-plus"></i></button>
                                <button type="button" class="btn btn-pdf btn-sm" title="Delete Row" onclick="deleteRow('tbl_hotel_booking');"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="table-responsive" id="hotel_booking_wrap">
                                    <?php $prefix = ""; ?>
                                    <table id="tbl_hotel_booking" class="table table-bordered table-hover table-striped no-marg pd_bt_51" style="width:1685px;">
                                        <?php include_once('hotel_booking_dynamic_tbl.php'); ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">
                        <legend>Costing Details</legend>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <small id="basic_show" style="color:red">&nbsp;</small>
                                <input type="text" id="sub_total" name="sub_total" placeholder="*Basic Amount"
                                    title="Basic Amount"
                                    onchange="get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','basic','discount');validate_balance(this.id)">
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12 ">
                                <small id="service_show" style="color:red">&nbsp;</small>
                                <input type="text" id="service_charge" name="service_charge"
                                    placeholder="Service Charge" title="Service Charge"
                                    onchange="total_fun();validate_balance(this.id);get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount')">
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <select title="Tax Apply On" id="tax_apply_on" name="tax_apply_on" class="form-control" onchange="get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','basic','discount');">
                                    <option value="">*Tax Apply On</option>
                                    <option value="1">Basic Amount</option>
                                    <option value="2">Service Charge</option>
                                    <option value="3">Total</option>
                                </select>
                            </div> 
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <select title="Select Tax" id="tax_value" name="tax_value" class="form-control" onchange="get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','basic','discount');">
                                    <option value="">*Select Tax</option>
                                    <?php get_tax_dropdown('Income') ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" id="service_tax_subtotal" name="service_tax_subtotal"
                                    placeholder="Tax Amount" title="Tax Amount" readonly>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small id="markup_show" style="color:red">&nbsp;</small>
                                <input type="text" id="markup" name="markup" placeholder="Markup Amount"
                                    title="Markup Amount"
                                    onchange="total_fun();get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','markup','discount');validate_balance(this.id)">
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <select title="Select Markup Tax" id="markup_tax_value" name="markup_tax_value" class="form-control" onchange="get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','basic','discount');">
                                    <option value="">*Select Markup Tax</option>
                                    <?php get_tax_dropdown('Income') ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" id="service_tax_markup" name="service_tax_markup"
                                    placeholder="Tax on Markup" title="Tax on Markup" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small id="discount_show" style="color:red">&nbsp;</small>
                                <input type="text" id="discount" name="discount" placeholder="Discount "
                                    title="Discount" value='0'
                                    onchange="total_fun();get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','discount','discount');validate_balance(this.id)">
                            </div>
                             <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <label for="tcs_tax">TCS</label>
                                <select title="Select Tax" id="tcs_tax" name="tcs_tax" class="form-control">
                                    <option value="">*Select Tax</option>
									<option value="5">5% TCS</option>
									<option value="20">20% TCS</option>
                                </select>
                            </div>
                            
                             <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <label for="tds">TCS</label>
                                <input type="number" name="tcs1" id="tcs1" readonly class="text-right"
                                    placeholder="TCS" title="TCS" value="0.00">
                                    
                            </div>
                            
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <label for="tds">TDS</label>
                                <input type="number" name="tds" id="tds" readonly class="text-right"
                                    placeholder="TDS" title="TDS" value="0.00">
                                    
                            </div>
                                
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" name="roundoff" id="roundoff" placeholder="Round Off"
                                    title="RoundOff" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" name="total_fee" id="total_fee"
                                    class="amount_feild_highlight text-right" placeholder="Net Total" title="Net Total"
                                    readonly>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" name="due_date" id="due_date" placeholder="Due Date" title="Due Date"
                                    value="<?= date('d-m-Y') ?>">
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" name="booking_date" id="booking_date" placeholder="Booking Date"
                                    value="<?= date('d-m-Y') ?>" title="Booking Date"
                                    onchange="check_valid_date(this.id);get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount');">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10 mg_tp_10" id="currency_div">
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">
                        <legend>Advance Details</legend>
                        <div class="row">
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <input type="text" id="payment_date" name="payment_date" class="form-control"
                                    placeholder="Date" title="Date" value="<?= date('d-m-Y') ?>"
                                    onchange="check_valid_date(this.id)">
                            </div>

                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <select name="payment_mode" id="payment_mode" class="form-control" title="Mode"
                                    onchange="get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount');payment_master_toggles(this.id, 'bank_name', 'transaction_id', 'bank_id');get_identifier_block('identifier','payment_mode','credit_card_details','credit_charges');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges')">
                                    <?php echo get_payment_mode_dropdown(); ?>
                                </select>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <input type="text" id="payment_amount" name="payment_amount" class="form-control"
                                    placeholder="*Amount" title="Amount"
                                    onchange="payment_amount_validate(this.id,'payment_mode','transaction_id','bank_name','bank_id');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges');validate_balance(this.id)">
                            </div>
                        </div>
                        <div class="row mg_bt_10">
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <input class="hidden" type="text" id="credit_charges" name="credit_charges"
                                    title="Credit card charges" disabled>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <select class="hidden" id="identifier"
                                    onchange="get_credit_card_data('identifier','payment_mode','credit_card_details')"
                                    title="Identifier(4 digit)" required>
                                    <option value=''>Select Identifier</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <input class="hidden" type="text" id="credit_card_details" name="credit_card_details"
                                    title="Credit card details" disabled>
                            </div>
                        </div>
                        <div class="row mg_bt_10">
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <input type="text" id="bank_name" name="bank_name" class="form-control bank_suggest"
                                    placeholder="Bank Name" title="Bank Name" disabled>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <input type="number" id="transaction_id" name="transaction_id"
                                    onchange="validate_specialChar(this.id)" class="form-control"
                                    placeholder="Cheque No/ID" title="Cheque No/ID" disabled>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <select name="bank_id" id="bank_id" title="Select Bank" disabled>
                                    <?php get_bank_dropdown(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-9 col-sm-9">
                                <span style="color: red;line-height: 35px;" data-original-title="" title=""
                                    class="note"><?= $txn_feild_note ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center  mg_bt_20">
                        <div class="col-xs-12">
                            <button id="btn_hotel_booking" class="btn btn-sm btn-success"><i
                                    class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$('#booking_save_modal').modal('show');
$('#customer_id,#quotation_id').select2();
$.get('../booking/inc/get_currency_dropdown.php', {quotation_id:''}, function (data) {
    $('#currency_div').html(data);
});
city_lzloading('.city_id');
$('#payment_date,#due_date,#booking_date').datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});
$('#check_in1, #check_out1').datetimepicker({
    format: 'd-m-Y H:i'
});

//Get Hotel Cost
function get_hotel_cost() {

    var hotel_id_arr = [];
    var room_cat_arr = [];
    var check_in_arr = [];
    var check_out_arr = [];
    var total_nights_arr = [];
    var total_rooms_arr = [];
    var extra_bed_arr = [];
    var meal_plan_arr = [];
    var checked_arr = [];
    var package_id_arr = [];
    var child_without_bed = $('#childrens').val();
    var child_with_bed = $('#children_with').val();
    var adult_count = $('#adults').val();

    adult_count = (adult_count == '') ? 0 : adult_count;
    child_without_bed = (child_without_bed == '') ? 0 : child_without_bed;
    child_with_bed = (child_with_bed == '') ? 0 : child_with_bed;

    var table = document.getElementById("tbl_hotel_booking");
    var rowCount = table.rows.length;
    for(var i=0; i<rowCount; i++){

        var row = table.rows[i];
        var hotel_id = row.cells[4].childNodes[0].value;
        var room_category = row.cells[10].childNodes[0].value;
        var check_in = row.cells[5].childNodes[0].value;
        var check_out = row.cells[6].childNodes[0].value;
        var total_nights = row.cells[7].childNodes[0].value;
        var total_rooms = row.cells[8].childNodes[0].value;
        var extra_bed = row.cells[12].childNodes[0].value;
        var meal_plan = row.cells[13].childNodes[0].value;
        
        total_nights = (total_nights == '') ? 0 : total_nights;
        total_rooms = (total_rooms == '') ? 0 : total_rooms;
        extra_bed = (extra_bed == '') ? 0 : extra_bed;
        var package_id = 0;
        
        hotel_id_arr.push(hotel_id);
        room_cat_arr.push(room_category);
        check_in_arr.push(check_in);
        check_out_arr.push(check_out);
        total_nights_arr.push(total_nights);
        total_rooms_arr.push(total_rooms);
        extra_bed_arr.push(extra_bed);
        meal_plan_arr.push(meal_plan);
        package_id_arr.push(package_id);
        checked_arr.push(row.cells[0].childNodes[0].checked);
    }
    var base_url = $('#base_url').val();
    $.ajax({
        type:'post',
        url: base_url+'view/package_booking/quotation/home/hotel/get_hotel_cost.php',
        data:{ hotel_id_arr : hotel_id_arr,check_in_arr : check_in_arr,check_out_arr:check_out_arr,room_cat_arr:room_cat_arr,total_nights_arr:total_nights_arr,total_rooms_arr:total_rooms_arr,extra_bed_arr:extra_bed_arr,child_with_bed:child_with_bed,child_without_bed:child_without_bed,adult_count:adult_count,package_id_arr:package_id_arr,checked_arr:checked_arr ,meal_plan_arr:meal_plan_arr},
		success:function(result){
			var hotel_arr = JSON.parse(result);
            var hotel_cost = 0;
			for(var i=0; i<hotel_arr.length; i++){

				var row = table.rows[i];
				if(row.cells[0].childNodes[0].checked){
					hotel_cost = parseFloat(hotel_cost) + parseFloat(hotel_arr[i]['hotel_cost']);
                }
            }
            $('#sub_total').val(hotel_cost);
            // total_fun();
            get_auto_values('booking_date','sub_total','payment_mode','service_charge','markup','save','true','service_charge','discount');
        }
    });
}

function total_fun() {

    var base_url = $('#base_url').val();
    var service_tax = $('#service_tax').val();
    var service_tax_subtotal = $('#service_tax_subtotal').val();
    var sub_total = $('#sub_total').val();
    var service_charge = $('#service_charge').val();
    var markup = $('#markup').val();
    var service_tax_markup = $('#service_tax_markup').val();
    var discount = $('#discount').val();
    var tds = $('#tds').val();

    //TCS Tax impl
    var tcs_apply = $('#tcs_apply').val();
    var tcs_calc = $('#tcs_calc').val();
    var tcs = $('#tcs').val();

    if (sub_total == "") {
        sub_total = 0;
    }
    if (service_charge == "") {
        service_charge = 0;
    }
    if (markup == "") {
        markup = 0;
    }
    if (discount == "") {
        discount = 0;
    }
    if (tds == "") {
        tds = 0;
    }
    if (parseFloat(discount) > (parseFloat(service_charge) + parseFloat(markup))) {
        error_msg_alert("Discount can't be greater than service charge + markup !");
        return false;
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
    service_charge = ($('#service_show').html() == '&nbsp;') ? service_charge : parseFloat($('#service_show').text()
        .split(' : ')[1]);
    markup = ($('#markup_show').html() == '&nbsp;') ? markup : parseFloat($('#markup_show').text().split(' : ')[1]);
    discount = ($('#discount_show').html() == '&nbsp;') ? discount : parseFloat($('#discount_show').text().split(' : ')[
        1]);

    var total_amount = parseFloat(sub_total) + parseFloat(service_tax_amount) + parseFloat(markupservice_tax_amount) +
        parseFloat(service_charge) + parseFloat(markup) - parseFloat(tds) - parseFloat(discount);
    var total = total_amount.toFixed(2);

    var hotel_id_arr = [];
    var tour_type_arr = [];
    var table = document.getElementById("tbl_hotel_booking");
    var rowCount = table.rows.length;
    for (var i = 0; i < rowCount; i++) 
    {
        var row = table.rows[i];
        if (row.cells[0].childNodes[0].checked) {
            var tour_type = row.cells[2].childNodes[0].value;
            var hotel_id = row.cells[4].childNodes[0].value;
            hotel_id_arr.push(hotel_id);
            tour_type_arr.push(tour_type);
        }
    }
    if (tour_type_arr.includes("International") && parseInt(tcs_apply) == 1) 
    {
        if (parseInt(tcs_calc) == 0) {
            var net_total = parseFloat(total);
            var tsc_tax = parseFloat(net_total) * (parseFloat(tcs) / 100);
           
            //document.getElementById("tcs_tax").readOnly = true;
        } else {
            var tsc_tax = $('#tcs_tax').val();
            if (tsc_tax == '') {
                tsc_tax = 0;
            }
            //document.getElementById("tcs_tax").readOnly = false;
        }
    } else if (!tour_type_arr.includes("International") || parseInt(tcs_apply) == 0) {
       
    }
    customTcsTax();
    var totalTcs=$("#tcs1").val();
    total = parseFloat(total)+parseFloat(totalTcs);
    var roundoff = Math.round(total) - total;

    $('#roundoff').val(roundoff.toFixed(2));
    $('#total_fee').val(parseFloat(total) + parseFloat(roundoff));
}

$(document).on("change","#tcs_tax",function() {
    customTcsTax();
});





function customTcsTax()
{
    var tcs_tax=$("#tcs_tax").val();
    var service_tax_markup = $('#service_tax_markup').val();
    var markup = $('#markup').val();
    if(tcs_tax!=='')
    {
       var  subtotal=$("#sub_total").val();
       var  service_charge=$("#service_charge").val();
       var discount=$('#discount').val() || 0;
       
       var txt_actual_tour_cost1=$("#total_fee").val();
       var  service_tax_amount=0;
       var  tax_subtotal=$("#service_tax_subtotal").val();
       var service_tax_subtotal1 = tax_subtotal.split(',');
	   for (var i = 0; i < service_tax_subtotal1.length; i++) {
		    var service_tax = service_tax_subtotal1[i].split(':');
		    service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
	   }

       var markupservice_tax_amount = 0;
    if (parseFloat(service_tax_markup) !== 0.00 && (service_tax_markup) !== "") {
        var service_tax_markup1 = service_tax_markup.split(",");
        for (var i = 0; i < service_tax_markup1.length; i++) {
            var service_tax = service_tax_markup1[i].split(':');
            markupservice_tax_amount = parseFloat(markupservice_tax_amount) + parseFloat(service_tax[2]);
        }
    } 
	   var service_charge_amt=parseFloat(service_charge)-parseFloat(discount);
	   
       var tdsamount=parseFloat(parseFloat(service_tax_amount)+parseFloat(service_charge_amt)+parseFloat(subtotal)+parseFloat(markup)+parseFloat(markupservice_tax_amount))*parseFloat(tcs_tax)/100;
       var totalTcs=$("#tcs1").val();
       if(totalTcs=='')
       {
        totalTcs=0;   
       }
       $("#tcs1").val(tdsamount.toFixed(2));

       txt_actual_tour_cost1=parseFloat(txt_actual_tour_cost1)-parseFloat(totalTcs);
       var txt_actual_tour_cost1total=parseFloat(tdsamount)+parseFloat(txt_actual_tour_cost1);
       $("#total_fee").val(txt_actual_tour_cost1total.toFixed(2));
    }
    else
    {
        var totalTcs=$("#tcs1").val();
        //$("#tds").val(0.00);
        $("#tcs1").val(0.00);
        var txt_actual_tour_cost1=$("#total_fee").val();
        var txt_actual_tour_cost1total=parseFloat(txt_actual_tour_cost1)-parseFloat(totalTcs);
        $("#total_fee").val(txt_actual_tour_cost1total.toFixed(2));
    }    

}

function business_rule_load() {
    get_auto_values('booking_date', 'sub_total', 'payment_mode', 'service_charge', 'markup', 'save', 'true',
        'service_charge', 'discount', true);
}
$(function() {
    $('#frm_hotel_booking_save').validate({
        rules: {
            customer_id: {
                required: true
            },
            sub_total: {
                required: true,
                number: true
            },
            service_charge: {
                required: true,
                number: true
            },
            total_fee: {
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

            $('#btn_hotel_booking').prop('disabled', true);
            var unique_timestamp = $('#unique_timestamp').val();
            var base_url = $('#base_url').val();
            var customer_id = $('#customer_id').val();
            var first_name = $('#cust_first_name').val();
            var cust_middle_name = $('#cust_middle_name').val();
            var cust_last_name = $('#cust_last_name').val();
            var gender = $('#cust_gender').val();
            var birth_date = $('#cust_birth_date').val();
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
            var state = $('#cust_state').val();
            var country_code = $('#country_code').val();
            var credit_charges = $('#credit_charges').val();
            var credit_card_details = $('#credit_card_details').val();
            var active_flag = 'Active';
            var branch_admin_id = $('#branch_admin_id1').val();
            var quotation_id = $('#quotation_id').val();
            var hotel_options = $('#hotel_options').val();
            var emp_id = $('#emp_id').val();
            var pass_name = $('#pass_name').val();
            var adults = $('#adults').val();
            var childrens = $('#childrens').val();
            var child_with_bed = $('#children_with').val();
            var infants = $('#infants').val();
            var sub_total = $('#sub_total').val();
            var service_charge = $('#service_charge').val();
            var service_tax_subtotal = $('#service_tax_subtotal').val();
            var markup = $('#markup').val();
            var service_tax_markup = $('#service_tax_markup').val();
            var discount = $('#discount').val();
            var tds = $('#tds').val();
            var total_fee = $('#total_fee').val();
            var roundoff = $('#roundoff').val();
            var payment_date = $('#payment_date').val();
            var payment_amount = $('#payment_amount').val();
            var payment_mode = $('#payment_mode').val();
            var bank_name = $('#bank_name').val();
            var transaction_id = $('#transaction_id').val();
            var bank_id = $('#bank_id').val();
            var due_date = $('#due_date').val();
            var booking_date = $('#booking_date').val();
            var credit_amount = $('#credit_amount').val();
            var currency_code = $('#hcurrency_code').val();

            var city_id_arr = [];
            var hotel_id_arr = [];
            var check_in_arr = [];
            var check_out_arr = [];
            var no_of_nights_arr = [];
            var rooms_arr = [];
            var room_type_arr = [];
            var category_arr = [];
            var accomodation_type_arr = [];
            var extra_beds_arr = [];
            var meal_plan_arr = [];
            var conf_no_arr = [];
            var tour_type_arr = [];

            if (parseFloat(discount) > (parseFloat(service_charge) + parseFloat(markup))) {
                error_msg_alert("Discount can't be greater than service charge + markup !");
                $('#btn_hotel_booking').prop('disabled', false);
                return false;
            }

            if (payment_mode == "Advance") {
                error_msg_alert("Please select another payment mode.");
                $('#btn_hotel_booking').prop('disabled', false);
                return false;
            }
            if (payment_mode == 'Credit Note' && credit_amount != '') {
                if (parseFloat(payment_amount) > parseFloat(credit_amount)) {
                    error_msg_alert('Credit Note Balance is not available');
                    $('#btn_hotel_booking').prop('disabled', false);
                    return false;
                }
            } else if (payment_mode == "Credit Note" && credit_amount == '') {
                error_msg_alert("Credit Note Balance is not available");
                $('#btn_hotel_booking').prop('disabled', false);
                return false;
            }
            if (parseFloat(payment_amount) > parseFloat(total_fee)) {
                error_msg_alert("Payment amount cannot be greater than selling amount.");
                $('#btn_hotel_booking').prop('disabled', false);
                return false;
            }

            var table = document.getElementById("tbl_hotel_booking");
            var rowCount = table.rows.length;
            var checked_count = 0;
            for (var i = 0; i < rowCount; i++) {
                var row = table.rows[i];
                if (row.cells[0].childNodes[0].checked) {
                    checked_count++;
                }
            }
            if (checked_count == 0) {
                error_msg_alert("Atleast one Hotel details is required!");
                $('#btn_hotel_booking').prop('disabled', false);
                return false;
            }
            for (var i = 0; i < rowCount; i++) {

                var row = table.rows[i];
                if (row.cells[0].childNodes[0].checked) {

                    var tour_type = row.cells[2].childNodes[0].value;
                    var city_id = row.cells[3].childNodes[0].value;
                    var hotel_id = row.cells[4].childNodes[0].value;
                    var check_in = row.cells[5].childNodes[0].value;
                    var check_out = row.cells[6].childNodes[0].value;
                    var no_of_nights = row.cells[7].childNodes[0].value;
                    var rooms = row.cells[8].childNodes[0].value;
                    var room_type = row.cells[9].childNodes[0].value;
                    var category = row.cells[10].childNodes[0].value;
                    var accomodation_type = row.cells[11].childNodes[0].value;
                    var extra_beds = row.cells[12].childNodes[0].value;
                    var meal_plan = row.cells[13].childNodes[0].value;
                    var conf_no = row.cells[14].childNodes[0].value;

                    var msg = "";
                    if (city_id == "") {
                        msg += "City is required in row:" + (i + 1) + '<br>';
                    }
                    if (hotel_id == "") {
                        msg += "Hotel is required in row:" + (i + 1) + '<br>';
                    }
                    if (check_in == "") {
                        msg += "Check-In is required in row:" + (i + 1) + '<br>';
                    }
                    if (check_out == "") {
                        msg += "Check-Out is required in row:" + (i + 1) + '<br>';
                    }
                    if (rooms == "") {
                        msg += "No of Rooms is required in row:" + (i + 1) + '<br>';
                    }
                    if (category == "") {
                        msg += "Room category is required in row:" + (i + 1) + '<br>';
                    }
                    if (no_of_nights == "") {
                        msg += "No of Nights is required in row:" + (i + 1) + '<br>';
                    }

                    if (msg != "") {
                        error_msg_alert(msg);
                        $('#btn_hotel_booking').prop('disabled', false);
                        return false;
                    }

                    tour_type_arr.push(tour_type);
                    city_id_arr.push(city_id);
                    hotel_id_arr.push(hotel_id);
                    check_in_arr.push(check_in);
                    check_out_arr.push(check_out);
                    no_of_nights_arr.push(no_of_nights);
                    rooms_arr.push(rooms);
                    room_type_arr.push(room_type);
                    category_arr.push(category);
                    accomodation_type_arr.push(accomodation_type);
                    extra_beds_arr.push(extra_beds);
                    meal_plan_arr.push(meal_plan);
                    conf_no_arr.push(conf_no);
                }
            }

            var hotel_sc = $('#hotel_sc').val();
            var hotel_markup = $('#hotel_markup').val();
            var hotel_taxes = $('#hotel_taxes').val();
            var hotel_markup_taxes = $('#hotel_markup_taxes').val();
            var hotel_tds = $('#hotel_tds').val();
            var tcs_per = $('#tcs_tax').val();
            var tcs_tax = $('#tcs1').val();
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
                "markup": $('#markup_show').find('span').text(),
                "discount": $('#discount_show').find('span').text()
            });
            //Validation for booking and payment date in login financial year
            var check_date1 = $('#booking_date').val();

            $('#btn_hotel_booking').button('loading');
            $.post(base_url + 'view/load_data/finance_date_validation.php', {
                check_date: check_date1
            }, function(data) {
                if (data !== 'valid') {
                    error_msg_alert(
                        "The Booking date does not match between selected Financial year."
                    );
                    $('#btn_hotel_booking').button('reset');
                    $('#btn_hotel_booking').prop('disabled', false);
                    return false;
                } else {

                    var payment_date = $('#payment_date').val();
                    $.post(base_url + 'view/load_data/finance_date_validation.php', {
                        check_date: payment_date
                    }, function(data) {
                        if (data !== 'valid') {
                            error_msg_alert(
                                "The Payment date does not match between selected Financial year."
                            );
                            $('#btn_hotel_booking').button('reset');
                            $('#btn_hotel_booking').prop('disabled', false);
                            return false;
                        } else {

                            //New Customer save
                            if (customer_id == '0') {
                                $.ajax({
                                    type: 'post',
                                    url: base_url +
                                        'controller/customer_master/customer_save.php',
                                    data: {
                                        first_name: first_name,
                                        middle_name: cust_middle_name,
                                        last_name: cust_last_name,
                                        gender: gender,
                                        birth_date: birth_date,
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
                                        country_code: country_code,
                                        quotation_id: quotation_id
                                    },
                                    success: function(result) {
                                        var error_arr = result.split(
                                            '--');
                                        if (error_arr[0] == 'error') {
                                            error_msg_alert(error_arr[
                                                1]);
                                            $('#btn_hotel_booking')
                                                .button('reset');
                                            $('#btn_hotel_booking')
                                                .prop('disabled',
                                                    false);
                                            return false;
                                        } else {
                                            saveData();
                                        }
                                    }
                                });
                            } else {
                                saveData();
                            }
                        }
                    });
                }
            });

            function saveData() {
                $.post(base_url + 'view/load_data/finance_date_validation.php', {
                    check_date: check_date1
                }, function(data) {
                    if (data !== 'valid') {
                        error_msg_alert(
                            "The Booking date does not match between selected Financial year."
                        );
                        $('#btn_hotel_booking').button('reset');
                        $('#btn_hotel_booking').prop('disabled', false);
                        return false;
                    } else {
                        var payment_date = $('#payment_date').val();
                        $.post(base_url + 'view/load_data/finance_date_validation.php', {
                            check_date: payment_date
                        }, function(data) {
                            if (data !== 'valid') {
                                error_msg_alert(
                                    "The Payment date does not match between selected Financial year."
                                );
                                $('#btn_hotel_booking').button('reset');
                                $('#btn_hotel_booking').prop('disabled', false);
                                return false;
                            } else {
                                $.ajax({
                                    type: 'post',
                                    url: base_url +
                                        'controller/hotel/booking/booking_save.php',
                                    data: {
                                        emp_id: emp_id,
                                        unique_timestamp: unique_timestamp,
                                        customer_id: customer_id,
                                        pass_name: pass_name,
                                        adults: adults,
                                        childrens: childrens,child_with_bed:child_with_bed,
                                        infants: infants,
                                        sub_total: sub_total,
                                        service_charge: service_charge,
                                        service_tax_subtotal: service_tax_subtotal,
                                        markup: markup,
                                        discount: discount,
                                        tds: tds,
                                        total_fee: total_fee,
                                        payment_date: payment_date,
                                        payment_amount: payment_amount,
                                        payment_mode: payment_mode,
                                        bank_name: bank_name,
                                        transaction_id: transaction_id,
                                        bank_id: bank_id,
                                        tour_type_arr:tour_type_arr,
                                        city_id_arr: city_id_arr,
                                        hotel_id_arr: hotel_id_arr,
                                        check_in_arr: check_in_arr,
                                        check_out_arr: check_out_arr,
                                        no_of_nights_arr: no_of_nights_arr,
                                        rooms_arr: rooms_arr,
                                        room_type_arr: room_type_arr,
                                        category_arr: category_arr,
                                        accomodation_type_arr: accomodation_type_arr,
                                        extra_beds_arr: extra_beds_arr,
                                        meal_plan_arr: meal_plan_arr,
                                        conf_no_arr: conf_no_arr,
                                        due_date: due_date,
                                        booking_date: booking_date,
                                        branch_admin_id: branch_admin_id,
                                        reflections: reflections,
                                        service_tax_markup: service_tax_markup,
                                        bsmValues: bsmValues,
                                        roundoff: roundoff,
                                        credit_charges: credit_charges,
                                        credit_card_details: credit_card_details,
                                        currency_code: currency_code,
                                        tcs_tax: tcs_tax,
                                        tcs_per: tcs_per,
                                        quotation_id: quotation_id,hotel_options:hotel_options
                                    },
                                    success: function(result) {
                                        var msg = result.split('--');
                                        if (msg[0] == 'error') {
                                            error_msg_alert(msg[1]);
                                            $('#booking_save_modal').modal('hide');
                                            $('#btn_hotel_booking').button('reset');
                                            $('#btn_hotel_booking').prop('disabled',false);
                                            return false;
                                        } else {
                                            var msg1 = result.split('-');
                                            $('#btn_hotel_booking').prop('disabled',false);
                                            $('#booking_save_modal').modal('hide');
                                            $('#btn_hotel_booking').button('reset');
                                            reset_form('frm_hotel_booking_save');
                                            success_msg_alert(msg1[0]);
                                            window.open(base_url + 'view/vendor/dashboard/estimate/estimate_save_modal.php?type=Hotel&amount='+ sub_total +'&booking_id=' +msg1[1]);
                                            setTimeout(() => {
                                                if ($('#whatsapp_switch').val() == "on")
                                                    whatsapp_send(
                                                        emp_id,
                                                        customer_id,
                                                        booking_date,
                                                        base_url,
                                                        country_code +
                                                        contact_no,
                                                        email_id
                                                    );
                                            }, 1000);
                                            booking_list_reflect();
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            }
        }
    });
});

function whatsapp_send(emp_id, customer_id, booking_date, base_url, contact_no, email_id) {
    $.post(base_url + 'controller/hotel/booking/whatsapp_send.php', {
        emp_id: emp_id,
        customer_id: customer_id,
        booking_date: booking_date,
        contact_no: contact_no,
        email_id: email_id
    }, function(data) {
        console.log(data)
        window.open(data);
    });
}

$('#booking_save_modal').on('hidden.bs.modal', function() {
    reset_form('frm_hotel_booking_save');
})
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<?php

include "../../../../model/model.php";
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_POST['branch_status'];
$booking_id = $_POST['booking_id'];

$sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id='$booking_id' and delete_status='0'"));
$reflections = json_decode($sq_booking['reflections']);
if($reflections[0]->tax_apply_on == '1') { 
    $tax_apply_on = 'Basic Amount';
}
else if($reflections[0]->tax_apply_on == '2') { 
    $tax_apply_on = 'Service Charge';
}
else if($reflections[0]->tax_apply_on == '3') { 
    $tax_apply_on = 'Total';
}else{
    $tax_apply_on = '';
}
?>

<form id="frm_update">

    <div class="modal fade" id="update_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static"
        data-keyboard="false">

        <div class="modal-dialog modal-lg" role="document" style="width: 96% !important;">

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>

                    <h4 class="modal-title" id="myModalLabel">Update Bus Booking</h4>

                </div>

                <div class="modal-body">
                    <input type="hidden" id="booking_id" name="booking_id" value="<?= $booking_id ?>">
                    <input type="hidden" id="hotel_sc" name="hotel_sc" value="<?php echo $reflections[0]->hotel_sc ?>">
                    <input type="hidden" id="hotel_markup" name="hotel_markup" value="<?php echo $reflections[0]->hotel_markup ?>">
                    <input type="hidden" id="hotel_taxes" name="hotel_taxes" value="<?php echo $reflections[0]->hotel_taxes ?>">
                    <input type="hidden" id="hotel_markup_taxes" name="hotel_markup_taxes" value="<?php echo $reflections[0]->hotel_markup_taxes ?>">
                    <input type="hidden" id="hotel_tds" name="hotel_tds" value="<?php echo $reflections[0]->hotel_tds ?>">
                    <input type="hidden" id="tax_apply_on" name="tax_apply_on" value="<?php echo $tax_apply_on ?>">
                    <input type="hidden" id="atax_apply_on" name="atax_apply_on" value="<?php echo $reflections[0]->tax_apply_on ?>">
                    <input type="hidden" id="tax_value1" name="tax_value1" value="<?php echo $reflections[0]->tax_value ?>">
                    <input type="hidden" id="markup_tax_value1" name="markup_tax_value1" value="<?php echo $reflections[0]->markup_tax_value ?>">

                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_10">

                        <legend>Customer Details</legend>

                        <div class="row">

                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">

                                <select name="customer_id1" id="customer_id1" class="customer_dropdown"
                                    title="Customer Name" style="width:100%" onchange="customer_info_load('1')"
                                    disabled>

                                    <?php

                                    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));

                                    if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                    ?>
                                    <option value="<?= $sq_customer['customer_id'] ?>">
                                        <?= $sq_customer['company_name'] ?></option>
                                    <?php } else { ?>
                                    <option value="<?= $sq_customer['customer_id'] ?>">
                                        <?= $sq_customer['first_name'] . ' ' . $sq_customer['last_name'] ?></option>
                                    <?php } ?>

                                    <?php get_customer_dropdown($role, $branch_admin_id, $branch_status); ?>

                                </select>

                            </div>

                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">

                                <input type="text" id="mobile_no1" name="mobile_no" placeholder="Mobile No"
                                    title="Mobile No" disabled value="<?= $sq_customer['contact_no'] ?>">

                            </div>

                            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_sm_xs">

                                <input type="text" id="email_id1" name="email_id1" placeholder="Email ID"
                                    title="Email ID" disabled value="<?= $sq_customer['email_id'] ?>">

                            </div>

                            <div class="col-md-3 col-sm-6 col-xs-12">

                                <input type="text" id="company_name1" class="hidden" name="company_name"
                                    title="Company Name" placeholder="Company Name" title="Company Name" readonly>

                            </div>

                        </div>



                        <script>
                        customer_info_load('1');
                        </script>

                    </div>



                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">

                        <legend>Booking Details</legend>



                        <div class="row mg_bt_10">

                            <div class="col-xs-12 text-right">

                            <button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_dynamic_bus_booking')"><i class="fa fa-plus"></i></button>

                            </div>

                        </div>



                        <div class="row">

                            <div class="col-xs-12">

                                <div class="table-responsive">

                                    <table id="tbl_dynamic_bus_booking" name="tbl_dynamic_bus_booking"
                                        class="table table-bordered no-marg" style="width:1250px">

                                        <?php $update_form = true; ?>

                                        <?php include_once('bus_booking_tbl.php'); ?>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $basic_cost = $sq_booking['basic_cost'];
                    $service_charge = $sq_booking['service_charge'];
                    $markup = $sq_booking['markup'];

                    $bsmValues = json_decode($sq_booking['bsm_values']);
                    $service_tax_amount = 0;
                    if ($sq_booking['service_tax_subtotal'] !== 0.00 && ($sq_booking['service_tax_subtotal']) !== '') {
                        $service_tax_subtotal1 = explode(',', $sq_booking['service_tax_subtotal']);
                        for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
                            $service_tax = explode(':', $service_tax_subtotal1[$i]);
                            $service_tax_amount = $service_tax_amount + $service_tax[2];
                        }
                    }
                    $markupservice_tax_amount = 0;
                    if ($sq_booking['markup_tax'] !== 0.00 && $sq_booking['markup_tax'] !== "") {
                        $service_tax_markup1 = explode(',', $sq_booking['markup_tax']);
                        for ($i = 0; $i < sizeof($service_tax_markup1); $i++) {
                            $service_tax = explode(':', $service_tax_markup1[$i]);
                            $markupservice_tax_amount = $markupservice_tax_amount + $service_tax[2];
                        }
                    }
                    foreach ($bsmValues[0] as $key => $value) {
                        switch ($key) {
                            case 'basic':
                                $basic_cost = ($value != "") ? $basic_cost + $service_tax_amount : $basic_cost;
                                $inclusive_b = $value;
                                break;
                            case 'service':
                                $service_charge = ($value != "") ? $service_charge + $service_tax_amount : $service_charge;
                                $inclusive_s = $value;
                                break;
                            case 'markup':
                                $markup = ($value != "") ? $markup + $markupservice_tax_amount : $markup;
                                $inclusive_m = $value;
                                break;
                        }
                    }
                    ?>

                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">
                        <legend>Costing Details</legend>
                        <div class="row mg_bt_10">

                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <small id="basic_show"
                                    style="color:#000000"><?= ($inclusive_b == '') ? '&nbsp;' : 'Inclusive Amount : <span>' . $inclusive_b ?></span></small>
                                <input type="text" id="basic_cost" name="basic_cost" placeholder="Basic Amount" title="Basic Amount"
                                    onchange="get_auto_values('balance_date1','basic_cost','payment_mode','service_charge','markup','update','true','basic','basic',false);calculate_total_amount();validate_balance(this.id)"
                                    value="<?= $basic_cost ?>">
                            </div>

                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <small id="service_show"
                                    style="color:#000000"><?= ($inclusive_s == '') ? '&nbsp;' : 'Inclusive Amount : <span>' . $inclusive_s ?></span></small>
                                <input type="text" id="service_charge" name="service_charge"
                                    placeholder="Service Charge" title="Service Charge"
                                    onchange="calculate_total_amount();validate_balance(this.id);get_auto_values('balance_date1','basic_cost','payment_mode','service_charge','markup','update','true','service_charge');"
                                    value="<?= $service_charge ?>">
                            </div>

                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" id="service_tax_subtotal" name="service_tax_subtotal"
                                    placeholder="Tax Amount" title="Tax Amount" readonly
                                    value="<?= $sq_booking['service_tax_subtotal'] ?>">
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <small id="markup_show"
                                    style="color:#000000"><?= ($inclusive_m == '') ? '&nbsp;' : 'Inclusive Amount : <span>' . $inclusive_m ?></span></small>
                                <input type="text" id="markup" name="markup" placeholder="Markup Amount" title="Markup Amount"
                                    onchange="get_auto_values('balance_date1','basic_cost','payment_mode','service_charge','markup','update','true','markup','discount1');calculate_total_amount('');validate_balance(this.id)"
                                    value="<?= $markup ?>">
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" id="service_tax_markup" name="service_tax_markup"
                                    placeholder="Tax on Markup" title="Tax on Markup"
                                    value="<?= $sq_booking['markup_tax'] ?>" readonly>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" name="roundoff" id="roundoff" class="text-right"
                                    placeholder="Round Off" title="RoundOff" value="<?= $sq_booking['roundoff'] ?> "
                                    readonly>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                                <small>&nbsp;</small>
                                <input type="text" id="net_total" class="amount_feild_highlight text-right"
                                    name="net_total" placeholder="Net Total" title="Net Total" readonly
                                    value="<?= $sq_booking['net_total'] ?>">
                                    <input type="hidden" id="old_total" value="<?= $sq_booking['net_total'] ?>"/>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10_xs">
                                <small>&nbsp;</small>
                                <input type="text" name="balance_date1" id="balance_date1"
                                    value="<?= get_date_user($sq_booking['created_at']) ?>" placeholder="Booking Date"
                                    title="Booking Date"
                                    onchange="check_valid_date(this.id);get_auto_values('balance_date1','basic_cost','payment_mode','service_charge','markup','update','true','service_charge','discount1',false);">
                            </div>

                        </div>
                    </div>



                    <div class="row text-center">

                        <div class="col-md-12">

                            <button id="btn_update" class="btn btn-sm btn-success"><i
                                    class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>

                        </div>

                    </div>



                </div>

            </div>

        </div>

    </div>



</form>



<script>
$('#update_modal').modal('show');

$('#customer_id').select2();

$('#payment_date,#balance_date1').datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});

$('#journey_date').datetimepicker({
    format: 'd-m-Y H:i'
});



$('#frm_update').validate({

    rules: {

        customer_id: {
            required: true
        },
        basic_cost: {
            required: true
        },
        service_charge: {
            required: true
        },
        net_total: {
            required: true
        },
        balance_date1: {
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
        bank_name: {
            required: function() {
                if ($('#payment_mode').val() != "Cash" && $('#payment_amount').val() != '0') {
                    return true;
                } else {
                    return false;
                }
            }
        },

        transaction_id: {
            required: function() {
                if ($('#payment_mode').val() != "Cash") {
                    return true;
                } else {
                    return false;
                }
            }
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

    },

    submitHandler: function() {



        $('#btn_update').prop('disabled', true);
        var booking_id = $('#booking_id').val();

        var customer_id = $('#customer_id1').val();



        var basic_cost = $('#basic_cost').val();

        var service_charge = $('#service_charge').val();

        var taxation_type = $('#taxation_type').val();

        var taxation_id = $('#taxation_id').val();

        var service_tax = $('#service_tax').val();

        var service_tax_subtotal = $('#service_tax_subtotal').val();

        var net_total = $('#net_total').val();
        var balance_date1 = $('#balance_date1').val();
        var markup = $('#markup').val();
        var service_tax_markup = $('#service_tax_markup').val();
        var hotel_sc = $('#hotel_sc').val();
        var hotel_markup = $('#hotel_markup').val();
        var hotel_taxes = $('#hotel_taxes').val();
        var hotel_markup_taxes = $('#hotel_markup_taxes').val();
        var hotel_tds = $('#hotel_tds').val();
        var roundoff = $('#roundoff').val();
        var old_total = $('#old_total').val();
        var tax_apply_on = $('#atax_apply_on').val();
        var tax_value = $('#tax_value1').val();
        var markup_tax_value = $('#markup_tax_value1').val();

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
            "discount": $('#discount_show1').find('span').text()
        });
        if (parseFloat(taxation_id) == "0") {
            error_msg_alert("Please select Tax Percentage");
            $('#btn_update').prop('disabled', false);
            return false;
        }

        var company_name_arr = new Array();
        var bus_type_arr = new Array();
        var bus_type_new_arr = new Array();
        var pnr_no_arr = new Array();
        var origin_arr = new Array();
        var destination_arr = new Array();
        var date_of_journey_arr = new Array();
        var reporting_time_arr = new Array();
        var boarding_point_access_arr = new Array();
        var entry_id_arr = new Array();
        var e_checkbox_arr = [];

        var msg = "";
        var table = document.getElementById("tbl_dynamic_bus_booking");
        var rowCount = table.rows.length;
        var checked_count = 0;
        for (var i = 0; i < rowCount; i++) {
            var row = table.rows[i];
            if (row.cells[0].childNodes[0].checked) {
                checked_count++;
            }
        }
        if (checked_count == 0) {
            error_msg_alert("Atleast one bus details is required!");
            $('#btn_update').prop('disabled', false);
            return false;
        }

        for (var i = 0; i < rowCount; i++) {
            var row = table.rows[i];
                var company_name = row.cells[2].childNodes[0].value;
                var bus_type = row.cells[3].childNodes[0].value;
                var bus_type_new = row.cells[4].childNodes[0].value;
                var pnr_no = row.cells[5].childNodes[0].value;
                var origin = row.cells[6].childNodes[0].value;
                var destination = row.cells[7].childNodes[0].value;
                var date_of_journey = row.cells[8].childNodes[0].value;
                var reporting_time = row.cells[9].childNodes[0].value;
                var boarding_point_access = row.cells[10].childNodes[0].value;

                if (row.cells[0].childNodes[0].checked) {

                    if (company_name == '') {
                        error_msg_alert("Enter Bus Operator name at row " + (i + 1));
                        $('#btn_update').prop('disabled', false);
                        return false;
                    }
                    if (origin == '') {
                        error_msg_alert("Enter Source at row " + (i + 1));
                        $('#btn_update').prop('disabled', false);
                        return false;
                    }
                    if (destination == '') {
                        error_msg_alert("Enter Destination at row " + (i + 1));
                        $('#btn_update').prop('disabled', false);
                        return false;
                    }
                }

                if (row.cells[11]) {

                    entry_id = row.cells[11].childNodes[0].value;

                } else {

                    entry_id = "";

                }

                company_name_arr.push(company_name);

                bus_type_arr.push(bus_type);

                bus_type_new_arr.push(bus_type_new);

                pnr_no_arr.push(pnr_no);

                origin_arr.push(origin);

                destination_arr.push(destination);

                date_of_journey_arr.push(date_of_journey);

                reporting_time_arr.push(reporting_time);

                boarding_point_access_arr.push(boarding_point_access);

                entry_id_arr.push(entry_id);
                e_checkbox_arr.push(row.cells[0].childNodes[0].checked);

        }
        if (msg != "") {

            error_msg_alert(msg);
            $('#btn_update').prop('disabled', false);
            return false;

        }


        //Validation for booking and payment date in login financial year
        var base_url = $('#base_url').val();
        var check_date1 = $('#balance_date1').val();
        $.post(base_url + 'view/load_data/finance_date_validation.php', {
            check_date: check_date1
        }, function(data) {
            if (data !== 'valid') {
                error_msg_alert("The Booking date does not match between selected Financial year.");
                $('#btn_update').prop('disabled', false);
                return false;
            } else {
                $('#btn_update').button('loading');
                $.ajax({

                    type: 'post',
                    url: base_url + 'controller/bus_booking/booking/booking_update.php',
                    data: {
                        booking_id: booking_id,
                        customer_id: customer_id,
                        basic_cost: basic_cost,
                        service_charge: service_charge,
                        taxation_type: taxation_type,
                        taxation_id: taxation_id,
                        service_tax: service_tax,
                        service_tax_subtotal: service_tax_subtotal,
                        net_total: net_total,
                        company_name_arr: company_name_arr,
                        bus_type_arr: bus_type_arr,
                        bus_type_new_arr: bus_type_new_arr,
                        pnr_no_arr: pnr_no_arr,
                        origin_arr: origin_arr,
                        destination_arr: destination_arr,
                        date_of_journey_arr: date_of_journey_arr,
                        reporting_time_arr: reporting_time_arr,
                        boarding_point_access_arr: boarding_point_access_arr,
                        entry_id_arr: entry_id_arr,e_checkbox_arr:e_checkbox_arr,
                        balance_date1: balance_date1,
                        reflections: reflections,
                        service_tax_markup: service_tax_markup,
                        markup: markup,
                        bsmValues: bsmValues,
                        roundoff: roundoff,old_total:old_total
                    },
                    success: function(result) {
                        $('#btn_update').button('reset');

                        msg_alert(result);
                        $('#btn_update').prop('disabled', false);

                        $('#update_modal').modal('hide');

                        list_reflect();

                    }
                });
            }
        });



    }

});
</script>

<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
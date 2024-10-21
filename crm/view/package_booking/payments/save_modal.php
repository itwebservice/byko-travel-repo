<?php
include "../../../model/model.php";
$branch_admin_id = $_SESSION['branch_admin_id'];
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_status = $_POST['branch_status'];
?>
<input type="hidden" id="emp_id" name="emp_id" value="<?php echo $emp_id; ?>">
<input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>">
<div class="modal fade" id="save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static"
    data-keyboard="false">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>

                <h4 class="modal-title" id="myModalLabel">New Receipt</h4>

            </div>

            <div class="modal-body">



                <form id="frm_payment_save">



                    <div class="row">

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                            <select id="booking_id" style="width: 100%" name="booking_id" title="Booking ID"
                                onchange="get_outstanding('package',this.id);">

                                <option value="">*Select Booking ID</option>
                                <?php
                                $query = "select * from package_tour_booking_master where 1 and delete_status='0' ";
                                include "../../../model/app_settings/branchwise_filteration.php";
                                $query .= " order by booking_id desc";
                                $sq_booking = mysqlQuery($query);
                                while ($row_booking = mysqli_fetch_assoc($sq_booking)) {

                                    $pass_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_booking[booking_id]'"));
                                    $cancle_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_booking[booking_id]' and status='Cancel'"));
                                    $status = '';
                                    if ($pass_count == $cancle_count) {
                                        $status = '(Cancelled)';
                                        $sq_payment_total = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum from package_payment_master where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
                                        $paid_amount = $sq_payment_total['sum'];

                                        $sq_esti = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_booking[booking_id]'"));

                                        $canc_amount = $sq_esti['cancel_amount'];
                                        $balance = ($paid_amount > $canc_amount) ? 0 : floatval($canc_amount) - floatval($paid_amount);
                                        if ($balance <= 0) continue;
                                    }
                                    $date = $row_booking['booking_date'];
                                    $yr = explode("-", $date);
                                    $year = $yr[0];
                                    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
                                    if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                        $cust_name =  $sq_customer['company_name'];
                                    }else{
                                        $cust_name = $sq_customer['first_name'] . " " . $sq_customer['last_name'];   
                                    }
                                    ?>
                                    <option value="<?php echo $row_booking['booking_id'] ?>">
                                        <?php echo get_package_booking_id($row_booking['booking_id'], $year) . "-" . " " . $cust_name . ' ' . $status; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>

                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                            <input type="text" id="txt_payment_date" name="txt_payment_date" placeholder="*Date"
                                title="Date" required value="<?= date('d-m-Y') ?>"
                                onchange="check_valid_date(this.id)" />

                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                            <select id="cmb_payment_mode" required
                                onchange="payment_installment_enable_disable_fields();get_identifier_block('identifier','cmb_payment_mode','credit_card_details','credit_charges');get_credit_card_charges('identifier','cmb_payment_mode','txt_amount','credit_card_details','credit_charges')"
                                title="Mode">

                                <?php get_payment_mode_dropdown() ?>

                            </select>

                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                            <input type="text" id="txt_amount" name="txt_amount" placeholder="*Amount" title="Amount"
                                onchange="validate_balance(this.id);payment_amount_validate(this.id,'cmb_payment_mode','txt_transaction_id','txt_bank_name','bank_id');get_credit_card_charges('identifier','cmb_payment_mode','txt_amount','credit_card_details','credit_charges');" />

                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                            <input class="hidden form-control" type="text" id="credit_charges" name="credit_charges"
                                title="Credit card charges" disabled>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                            <select class="hidden" id="identifier"
                                onchange="get_credit_card_data('identifier','cmb_payment_mode','credit_card_details')"
                                title="Identifier(4 digit)" required>
                                <option value=''>Select Identifier</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                            <input class="hidden form-control" type="text" id="credit_card_details"
                                name="credit_card_details" title="Credit card details" disabled>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                            <input class="form-control bank_suggest" type="text" id="txt_bank_name" name="txt_bank_name"
                                placeholder="Bank Name" title="Bank Name" disabled />

                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                            <input type="number" id="txt_transaction_id" onchange="validate_specialChar(this.id);"
                                name="txt_transaction_id" placeholder="Cheque No / ID" title="Cheque No / ID"
                                disabled />

                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                            <select name="bank_id" id="bank_id" title="Creditor Bank" disabled>

                                <?php get_bank_dropdown(); ?>

                            </select>

                        </div>
                    </div>
                    <div class="row">
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-3">
                            <input type="text" id="outstanding" name="outstanding" class="form-control"
                                placeholder="Outstanding" title="Outstanding" readonly />
                            <input type="hidden" id="canc_status" name="canc_status" class="form-control" />
                        </div>
                        <div class="col-md-9 col-sm-9">
                            <span style="color: red;line-height: 35px;" data-original-title="" title=""
                                class="note"><?= $txn_feild_note ?></span>
                        </div>
                    </div>



                    <div class="row text-center mg_tp_20">
                        <div class="col-xs-12">

                            <button class="btn btn-sm btn-success" id="btn_payment_installment"
                                name="btn_payment_installment"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>

                        </div>
                    </div>



                </form>







            </div>

        </div>

    </div>

</div>
<!-- <script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script> -->

<script type="text/javascript">
$('#save_modal').modal('show');



$("#txt_payment_date").datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});

$('#booking_id').select2();



$('#frm_payment_save').validate({

    rules: {

        booking_id: {
            required: true
        },

        txt_payment_date: {
            required: true
        },

        txt_amount: {
            required: true
        },

        cmb_payment_mode: {
            required: true
        },

        bank_id: {
            required: function() {
                if ($('#cmb_payment_mode').val() != "Cash") {
                    return true;
                } else {
                    return false;
                }
            }
        },

    },

    submitHandler: function(form) {

        $('#btn_payment_installment').prop('disabled', true);

        var booking_id = $('#booking_id').val();
        var payment_date = $("#txt_payment_date").val();
        var payment_mode = $("#cmb_payment_mode").val();
        var payment_amount = $("#txt_amount").val();
        var bank_name = $("#txt_bank_name").val();
        var transaction_id = $("#txt_transaction_id").val();
        var payment_for = $("#cmb_payment_of_type").val();
        var p_travel_type = $("#cmb_travel_of_type").val();
        var credit_charges = $('#credit_charges').val();
        var credit_card_details = $('#credit_card_details').val();
        var outstanding = $('#outstanding').val();
        var canc_status = $('#canc_status').val();

        var bank_id = $('#bank_id').val();
        var emp_id = $("#emp_id").val();
        var branch_admin_id = $('#branch_admin_id1').val();

        if (payment_mode == "Credit Card" && credit_card_details == '') {
            error_msg_alert("Please select identifier.");
            $('#btn_payment_installment').prop('disabled', false);
            return false;
        }
        if (payment_mode == "Credit Note" || payment_mode == "Advance") {
            error_msg_alert("Please select another payment mode.");
            $('#btn_payment_installment').prop('disabled', false);
            return false;
        }
        if (parseFloat(payment_amount) > parseFloat(outstanding)) {
            error_msg_alert("Payment amount cannot be greater than outstanding amount.");
            $('#btn_payment_installment').prop('disabled', false);
            return false;
        }

        //Validation for booking and payment date in login financial year
        var base_url = $('#base_url').val();
        var check_date1 = $('#txt_payment_date').val();
        $.post(base_url + 'view/load_data/finance_date_validation.php', {
            check_date: check_date1
        }, function(data) {
            if (data !== 'valid') {
                error_msg_alert("The Payment date does not match between selected Financial year.");
                $('#btn_payment_installment').prop('disabled', false);
                return false;
            } else {
                $('#btn_payment_installment').button('loading');

                $.post(
                    base_url +
                    "controller/package_tour/payment/package_tour_payment_master_save_c.php", {
                        booking_id: booking_id,
                        payment_date: payment_date,
                        payment_mode: payment_mode,
                        payment_amount: payment_amount,
                        bank_name: bank_name,
                        transaction_id: transaction_id,
                        payment_for: payment_for,
                        p_travel_type: p_travel_type,
                        bank_id: bank_id,
                        branch_admin_id: branch_admin_id,
                        emp_id: emp_id,
                        credit_charges: credit_charges,
                        credit_card_details: credit_card_details,
                        canc_status: canc_status
                    },
                    function(data) {
                        msg_alert(data);
                        list_reflect();
                        $('#btn_payment_installment').button('reset');
                        $('#btn_payment_installment').prop('disabled', false);
                        $('#save_modal').modal('hide');
                        if ($('#whatsapp_switch').val() == "on") whatsapp_send_r(booking_id,
                            payment_amount, base_url)
                    });
            }
        });
    }
});
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
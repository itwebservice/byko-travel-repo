<?php
include "../../../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_POST['branch_status']
?>
<input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>" >
<div class="modal fade" id="save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">

    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">New Receipt</h4>
      </div>
      <div class="modal-body">

        <form id="frm_save">

          <div class="row mg_bt_10">
            <div class="col-md-3">
              <select name="booking_id" id="booking_id" style="width:100%" title="Booking ID" onchange="get_outstanding('bus',this.id);">
                <option value="">*Select Booking ID</option>
                  <?php
                  $query = "select * from bus_booking_master where 1 and delete_status='0'";
                  include "../../../../model/app_settings/branchwise_filteration.php";
                  $query .= " order by booking_id desc";
                  $sq_booking = mysqlQuery($query);
                  while($row_booking = mysqli_fetch_assoc($sq_booking)){
                    $status = '';
                    $date = $row_booking['created_at'];
                    $yr = explode("-", $date);
                    $year = $yr[0];
                    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
                    $pass_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$row_booking[booking_id]'"));
                    $cancel_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$row_booking[booking_id]' and status='Cancel'"));
                    if($pass_count == $cancel_count){
                      $status = '(Cancelled)';
                      $sq_payment_total = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from bus_booking_payment_master where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
                      $paid_amount = $sq_payment_total['sum'];
                      $canc_amount = $row_booking['cancel_amount'];
                      $balance = ($paid_amount > $canc_amount) ? 0 : floatval($canc_amount) - floatval($paid_amount);
                      if($balance <= 0) continue;
                    }
                    if($sq_customer['type']=='Corporate'||$sq_customer['type']=='B2B'){ ?>
                      <option value="<?php echo $row_booking['booking_id'] ?>"><?php echo get_bus_booking_id($row_booking['booking_id'],$year)."-"." ".$sq_customer['company_name'].' '.$status; ?></option>
                    <?php } 
                    else{ ?>
                      <option value="<?php echo $row_booking['booking_id'] ?>"><?php echo get_bus_booking_id($row_booking['booking_id'],$year)."-"." ".$sq_customer['first_name']." ".$sq_customer['last_name'].' '.$status; ?></option>
                      <?php
                    }
                  } ?>
              </select>
          </div>
          <div class="col-md-3">
            <input type="text" id="payment_date" name="payment_date" placeholder="*Date" title="Date" value="<?= date('d-m-Y')?>" onchange="check_valid_date(this.id)">
          </div>
          <div class="col-md-3">
            <select name="payment_mode" id="payment_mode" title="Mode" onchange="payment_master_toggles(this.id, 'bank_name', 'transaction_id', 'bank_id');get_identifier_block('identifier','payment_mode','credit_card_details','credit_charges');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges')">
                <?php get_payment_mode_dropdown(); ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" id="payment_amount" name="payment_amount" placeholder="*Amount" title="Amount" onchange="validate_balance(this.id);payment_amount_validate(this.id,'payment_mode','transaction_id','bank_name','bank_id');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges');">
          </div>
        </div>
        <div class="row mg_bt_10">
          <div class="col-md-3 col-sm-6 col-xs-12">
            <input class="hidden" type="text" id="credit_charges" name="credit_charges" title="Credit card charges" disabled>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <select class="hidden" id="identifier" onchange="get_credit_card_data('identifier','payment_mode','credit_card_details')" title="Identifier(4 digit)" required
            ><option value=''>Select Identifier</option></select>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <input class="hidden" type="text" id="credit_card_details" name="credit_card_details" title="Credit card details" disabled>
          </div>
        </div>
        <div class="row mg_bt_10">
          <div class="col-md-3">
            <input type="text" id="bank_name" name="bank_name" class="form-control bank_suggest" placeholder="Bank Name" title="Bank Name" disabled>
          </div>
          <div class="col-md-3">
            <input type="number" id="transaction_id" name="transaction_id" onchange="validate_specialChar(this.id)" placeholder="Cheque No/ID" title="Cheque No/ID" disabled>
          </div>
          <div class="col-md-3">
            <select name="bank_id" id="bank_id" title="Select Bank" disabled>
              <?php get_bank_dropdown(); ?>
            </select>
          </div>
        </div>
        <div class="row mg_bt_10">
          <div class="col-md-3 col-sm-3">
            <input type="text" id="outstanding" name="outstanding" class="form-control" placeholder="Outstanding" title="Outstanding" readonly/>
            <input type="hidden" id="canc_status" name="canc_status" class="form-control"/>
          </div>
        </div>
        <div class="row mg_tp_20">
          <div class="col-md-9 col-sm-9">
            <span style="color: red;line-height: 35px;" data-original-title="" title="" class="note"><?= $txn_feild_note ?></span>
          </div>
        </div>

        <div class="row text-center mg_tp_20">
            <div class="col-md-12">
              <button id="btn_save" class="btn btn-sm btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
            </div>
        </div>

      </form>

    </div>
  </div>
</div>

</div>

<script>
$('#save_modal').modal('show');
$('#booking_id').select2();
$('#payment_date').datetimepicker({ timepicker:false, format:'d-m-Y' });

$(function(){

$('#frm_save').validate({

  rules:{
    booking_id : { required : true },
    payment_date : { required : true },
    payment_amount : { required : true, number: true },
    payment_mode : { required : true },  
    bank_id : { required : function(){  if($('#payment_mode').val()!="Cash"){ return true; }else{ return false; }  }  },     
  },

  submitHandler:function(form){

    $('#btn_save').prop('disabled',true);
    var booking_id = $('#booking_id').val();
    var payment_date = $('#payment_date').val();
    var payment_amount = $('#payment_amount').val();
    var payment_mode = $('#payment_mode').val();
    var bank_name = $('#bank_name').val();
    var transaction_id = $('#transaction_id').val();
    var bank_id = $('#bank_id').val();
    var branch_admin_id = $('#branch_admin_id1').val();
    var credit_charges = $('#credit_charges').val();
    var credit_card_details = $('#credit_card_details').val();
    var outstanding = $('#outstanding').val();
    var canc_status = $('#canc_status').val();

    if(payment_mode=="Credit Note" || payment_mode=="Advance"){
      error_msg_alert("Please select another payment mode.");
      $('#btn_save').prop('disabled',false);
        return false;
    }
    if(parseFloat(payment_amount)>parseFloat(outstanding)){
      error_msg_alert("Payment amount cannot be greater than outstanding amount.");
      $('#btn_save').prop('disabled',false);
      return false;
    }
    //Validation for booking and payment date in login financial year
		var base_url = $('#base_url').val();
    var check_date1 = $('#payment_date').val();
    $.post(base_url+'view/load_data/finance_date_validation.php', { check_date: check_date1 }, function(data){
      if(data !== 'valid'){
        error_msg_alert("The Payment date does not match between selected Financial year.");
        $('#btn_save').prop('disabled',false);
        return false;
      }else{
        $('#btn_save').button('loading');
        $.ajax({
            type: 'post',
            url: base_url+'controller/bus_booking/payment/payment_save.php',
            data:{ booking_id : booking_id, payment_date : payment_date, payment_amount : payment_amount, payment_mode : payment_mode, bank_name : bank_name, transaction_id : transaction_id, bank_id : bank_id, branch_admin_id : branch_admin_id,credit_charges:credit_charges,credit_card_details:credit_card_details,canc_status:canc_status  },

            success: function(result){
              $('#btn_save').button('reset');
              var msg = result.split('--');
              if(msg[0]=='error'){
                $('#btn_save').prop('disabled',false);
                msg_alert(result);
              }
              else{
                msg_alert(result);
                $('#btn_save').prop('disabled',false);
                reset_form('frm_save');
                $('#save_modal').modal('hide');  
                list_reflect();
              }
            }
          });
          setTimeout(() => {
            if($('#whatsapp_switch').val() == "on") whatsapp_send_r(booking_id, payment_amount, base_url);
          }, 1000);
        }
      });
    }
  });
});
</script>

<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
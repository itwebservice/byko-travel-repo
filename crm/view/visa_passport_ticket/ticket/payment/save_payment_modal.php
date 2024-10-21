<?php
include "../../../../model/model.php";
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='visa_passport_ticket/ticket/index.php'"));
$branch_status = $sq['branch_status'];
?>
<input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>" >

<div class="modal fade" id="ticket_payment_save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">New Receipt</h4>
      </div>
      <div class="modal-body">

        <form id="frm_ticket_payment_save">
          <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
              <select name="ticket_id" id="ticket_id" style="width:100%" title="Booking ID" onchange="get_outstanding('flight',this.id);">
                <option value="">*Select Booking ID</option>
                <?php
                $query = "select * from ticket_master where 1 and delete_status='0'";
                include "../../../../model/app_settings/branchwise_filteration.php";
                $query .= " order by ticket_id desc";
                $sq_ticket = mysqlQuery($query);
                while($row_ticket = mysqli_fetch_assoc($sq_ticket)){
                  
                  $status = '';
                  $sq_entries = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id ='$row_ticket[ticket_id]'"));
                  $sq_entries_cancel = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id ='$row_ticket[ticket_id]' and status='Cancel'"));
                  if($row_ticket['cancel_type'] == 1){
                    $status = '(Cancelled)';
                    $sq_payment_total = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from ticket_payment_master where ticket_id='$row_ticket[ticket_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
                    $paid_amount = $sq_payment_total['sum'];
                    $canc_amount = $row_ticket['cancel_amount'];
                    $cancel_estimate = json_decode($row_ticket['cancel_estimate']);
                    $balance = (($row_ticket['ticket_total_cost'] - floatval($cancel_estimate[0]->ticket_total_cost)) + $canc_amount) - $paid_amount;
                    $balance = ($balance <= 0 ) ? 0 : $balance;
                    if($balance <= 0) continue;
                  }
                  $date = $row_ticket['created_at'];
                  $yr = explode("-", $date);
                  $year = $yr[0];
                  $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
                    if($sq_customer['type'] == 'Corporate' || $sq_customer['type']=='B2B'){
                        $cust_name = $sq_customer['company_name'];
                    }else{
                        $cust_name = $sq_customer['first_name'].' '.$sq_customer['last_name'];
                    }
                    ?>
                    <option value="<?= $row_ticket['ticket_id'] ?>"><?= get_ticket_booking_id($row_ticket['ticket_id'],$year).' : '.$cust_name.' '.$status ?></option>
                    <?php
                }
                ?>
              </select>
            </div>     
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
              <input type="text" id="payment_date" name="payment_date" class="form-control" placeholder="*Date" title="Date" value="<?= date('d-m-Y')?>" onchange="check_valid_date(this.id)">
            </div>  
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
              <select name="payment_mode" id="payment_mode" class="form-control" title="Mode" onchange="payment_master_toggles(this.id, 'bank_name', 'transaction_id', 'bank_id');;get_identifier_block('identifier','payment_mode','credit_card_details','credit_charges');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges')">
                  <?php get_payment_mode_dropdown(); ?>
              </select>
            </div>      
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
              <input type="text" id="payment_amount" name="payment_amount" class="form-control" placeholder="*Amount" title="Amount" onchange="validate_balance(this.id);payment_amount_validate(this.id,'payment_mode','transaction_id','bank_name','bank_id');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges');">
            </div>
          </div>
          <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
              <input class="hidden form-control" type="text" id="credit_charges" name="credit_charges" title="Credit card charges" disabled>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
              <select class="hidden form-control" id="identifier" onchange="get_credit_card_data('identifier','payment_mode','credit_card_details')" title="Identifier(4 digit)" required
              ><option value=''>Select Identifier</option></select>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
              <input class="hidden form-control" type="text" id="credit_card_details" name="credit_card_details" title="Credit card details" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
              <input type="text" id="bank_name" name="bank_name" class="form-control bank_suggest" placeholder="Bank Name" title="Bank Name" disabled>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
              <input type="number" id="transaction_id" name="transaction_id" onchange="validate_specialChar(this.id)" class="form-control" placeholder="Cheque No/ID" title="Cheque No/ID" disabled>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
              <select name="bank_id" id="bank_id" title="Select Bank" class="form-control" disabled>
                <?php get_bank_dropdown(); ?>
              </select>
            </div>
          </div>
          <div class="row mg_tp_10 mg_bt_10">
            <div class="col-md-3 col-sm-3">
              <input type="text" id="outstanding" name="outstanding" class="form-control" placeholder="Outstanding" title="Outstanding" readonly/>
              <input type="hidden" id="canc_status" name="canc_status" class="form-control"/>
            </div>
          </div>
          <div class="row">
            <div class="col-md-9 col-sm-9">
              <span style="color: red;line-height: 35px;" data-original-title="" title="" class="note"><?= $txn_feild_note ?></span>
            </div>
          </div>

          <div class="row text-center mg_tp_10">
              <div class="col-xs-12">
                <button id="btn_save_patment" class="btn btn-sm btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
              </div>
          </div>
          </div>

        </form>
        
      </div>     
    </div>
  </div>
</div>

<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
$('#ticket_payment_save_modal').modal('show');
$('#payment_date').datetimepicker({ timepicker:false, format:'d-m-Y' });
$('#ticket_id').select2();

$(function(){
$('#frm_ticket_payment_save').validate({
  rules:{
    ticket_id : { required : true },
    payment_date : { required : true },
    payment_amount : { required : true, number: true },
    payment_mode : { required : true }, 
    bank_id : { required : function(){  if($('#payment_mode').val()!="Cash"){ return true; }else{ return false; }  }  },     
  },
  submitHandler:function(form){
    $('#btn_save_patment').prop('disabled', true);
    var ticket_id = $('#ticket_id').val();
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

    //Validation for booking and payment date in login financial year
		var base_url = $('#base_url').val();
    var check_date1 = $('#payment_date').val();


    if(payment_mode=="Credit Note"||payment_mode=="Advance"){
        error_msg_alert("Please select another payment mode.");
        $('#btn_save_patment').prop('disabled',false);
        return false;
    }
    if(parseFloat(payment_amount)>parseFloat(outstanding)){
      error_msg_alert("Payment amount cannot be greater than outstanding amount.");
      $('#btn_save_patment').prop('disabled',false);
      return false;
    }


    $.post(base_url+'view/load_data/finance_date_validation.php', { check_date: check_date1 }, function(data){
      if(data !== 'valid'){
        error_msg_alert("The Payment date does not match between selected Financial year.");
        $('#btn_save_patment').prop('disabled', false);
        return false;
      } else{

        $('#btn_save_patment').button('loading');  

        var base_url = $('#base_url').val();
        
          $.ajax({
            type: 'post',
            url: base_url+'controller/visa_passport_ticket/ticket/ticket_master_payment_save.php',
            data:{ ticket_id : ticket_id, payment_date : payment_date, payment_amount : payment_amount, payment_mode : payment_mode, bank_name : bank_name, transaction_id : transaction_id, bank_id : bank_id, branch_admin_id : branch_admin_id,credit_charges:credit_charges,credit_card_details:credit_card_details,canc_status:canc_status },
            success: function(result){
              $('#btn_save_patment').button('reset');
              $('#btn_save_patment').prop('disabled', false);
              var msg = result.split('-');
              if(msg[0]=='error'){
                error_msg_alert(result);
              }
              else{
                reset_form('frm_ticket_payment_save');
                $('#ticket_payment_save_modal').modal('hide');  
                success_msg_alert(result);
                ticket_payment_list_reflect();
              }
              if($('#whatsapp_switch').val() == "on") whatsapp_send_r(ticket_id, payment_amount, base_url); 
            }
          });
      }
    });
  }
});

});
</script>
<?php
$role= $_SESSION['role'];
$emp_id= $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$q = "select * from branch_assign where link='car_rental/booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
$role_id = $_SESSION['role_id'];
?>
<input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>" >
<input type="hidden" id="emp_id" name="emp_id" value="<?= $emp_id ?>" >
<form id="frm_payment_save">
<div class="modal fade" id="payment_save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">New Receipt</h4>
      </div>
      <div class="modal-body">

        <div class="row">
          <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
              <select name="booking_id" id="booking_id" style="width:100%" title="Booking ID" onchange="get_outstanding('car',this.id);">
                  <option value="">*Select Booking ID</option>
                  <?php
                  $query = "select * from car_rental_booking where 1 and delete_status='0' ";
                  include "../../../model/app_settings/branchwise_filteration.php";
                  $query .= " order by booking_id desc";
                  $sq_booking = mysqlQuery($query);
                  while($row_booking = mysqli_fetch_assoc($sq_booking)){

                    $date = $row_booking['created_at'];
                    $yr = explode("-", $date);
                    $year = $yr[0];
                    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));

                    $status = '';
                    if($row_booking['status'] == 'Cancel'){
                      $status = '(Cancelled)';
                      $sq_payment_total = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from car_rental_payment where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
                      $paid_amount = $sq_payment_total['sum'];
                      $canc_amount=$row_booking['cancel_amount'];
                      $balance = ($paid_amount > $canc_amount) ? 0 : floatval($canc_amount) - floatval($paid_amount);
                      if($balance <= 0) continue;
                    }
                    if($sq_customer['type']=='Corporate'||$sq_customer['type']=='B2B'){ ?>
                        <option value="<?= $row_booking['booking_id'] ?>"><?= get_car_rental_booking_id($row_booking['booking_id'],$year)." : ".$sq_customer['company_name'].' '.$status ?></option>
                    <?php }
                    else{ ?>
                        <option value="<?= $row_booking['booking_id'] ?>"><?= get_car_rental_booking_id($row_booking['booking_id'],$year)." : ".$sq_customer['first_name'].' '.$sq_customer['last_name'].' '.$status ?></option>
                    <?php }
                  } ?>
              </select>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" id="payment_date" name="payment_date" class="form-control" placeholder="Date" title="Date" value="<?= date('d-m-Y')?>" onchange="check_valid_date(this.id)">
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <select id="payment_mode" name="payment_mode" class="form-control" required title="Mode" onchange="payment_master_toggles(this.id, 'bank_name', 'transaction_id', 'bank_id');get_identifier_block('identifier','payment_mode','credit_card_details','credit_charges');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges')">
                <?php get_payment_mode_dropdown(); ?>
            </select>  
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" id="payment_amount" class="form-control" name="payment_amount" placeholder="*Amount" title="Amount" onchange="validate_balance(this.id);payment_amount_validate(this.id,'payment_mode','transaction_id','bank_name','bank_id');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges');">
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
          
          <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" id="bank_name" name="bank_name" class="form-control bank_suggest" placeholder="Bank Name" title="Bank Name" disabled/>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="number" id="transaction_id" onchange="validate_specialChar(this.id)" name="transaction_id" class="form-control" placeholder="Cheque No / ID" title="Cheque No / ID" disabled />
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <select name="bank_id" id="bank_id" class="form-control" title="Select Bank" disabled>
              <?php get_bank_dropdown(); ?>
            </select>
          </div>
          </div>
          
          <div class="row mg_tp_10">
          
          <div class="col-md-3 col-sm-3">
            <input type="text" id="outstanding" name="outstanding" class="form-control" placeholder="Outstanding" title="Outstanding" readonly/>
            <input type="hidden" id="canc_status" name="canc_status" class="form-control"/>
          </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-9">
            <span style="color: red;line-height: 35px;" data-original-title="" title="" class="note"><?= $txn_feild_note ?></span>
        </div>
        </div>

        <div class="row text-center mg_tp_20">
          <div class="col-xs-12">
              <button id="btn_payment_save" class="btn btn-sm btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
          </div>
        </div>

      </div>     
    </div>
  </div>
</div>
</form>
<script>
$('#payment_date').datetimepicker({ timepicker:false, format:'d-m-Y' });
$('#booking_id').select2();
$(function(){
  $('#frm_payment_save').validate({
      rules:{
        booking_id : { required: true },
        payment_amount : { required: true, number:true },
        payment_date : { required: true },
        payment_mode : { required : true },
        bank_id : { required : function(){  if($('#payment_mode').val()!="Cash"){ return true; }else{ return false; }  }  },
      },
      submitHandler:function(form){
        $('#btn_payment_save').prop('disabled',true);
        var booking_id = $('#booking_id').val();
        var payment_amount = $('#payment_amount').val();
        var payment_date = $('#payment_date').val();
        var payment_mode = $('#payment_mode').val();
        var bank_name = $('#bank_name').val();
        var transaction_id = $('#transaction_id').val();
        var bank_id = $('#bank_id').val();
        var branch_admin_id = $('#branch_admin_id1').val();
        var emp_id = $('#emp_id').val();
        var credit_charges = $('#credit_charges').val();
        var credit_card_details = $('#credit_card_details').val();
        var base_url = $('#base_url').val();
        var outstanding = $('#outstanding').val();
        var canc_status = $('#canc_status').val();
        if(payment_mode=="Credit Note"||payment_mode=="Advance"){
          error_msg_alert("Please select another payment mode.");
          $('#btn_payment_save').prop('disabled',false);
          return false;
        }
        if(parseFloat(payment_amount)>parseFloat(outstanding)){
          error_msg_alert("Payment amount cannot be greater than outstanding amount.");
          $('#btn_payment_save').prop('disabled',false);
          return false;
        }
        //Validation for booking and payment date in login financial year
        var check_date1 = $('#payment_date').val();
        $.post(base_url+'view/load_data/finance_date_validation.php', { check_date: check_date1 }, function(data){
          if(data !== 'valid'){
            error_msg_alert("The Payment date does not match between selected Financial year.");
            $('#btn_payment_save').prop('disabled',false);
            return false;
          } else{
              $('#btn_payment_save').button('loading');
              
              $.ajax({
                type:'post',
                url: base_url+'controller/car_rental/payment/payment_save.php',
                data:{ booking_id : booking_id, payment_amount : payment_amount, payment_date : payment_date, payment_mode : payment_mode, bank_name : bank_name, transaction_id : transaction_id, bank_id : bank_id , branch_admin_id : branch_admin_id, emp_id : emp_id,credit_charges:credit_charges,credit_card_details:credit_card_details,canc_status:canc_status},
                success:function(result){
                  $('#btn_payment_save').prop('disabled',false);
                  $('#btn_payment_save').button('reset');
                  msg_alert(result);
                  reset_form('frm_payment_save');
                  $('#payment_save_modal').modal('hide');  
                  payment_list_reflect();
                },
                error:function(result){
                  $('#btn_payment_save').prop('disabled',false);
                  console.log(result.responseText);
                }
              });
              if($('#whatsapp_switch').val() == "on") whatsapp_send_r(booking_id, payment_amount, base_url);
          }
        })
      }
  });
});
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
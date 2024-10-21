<?php
include_once('../../../../model/model.php');
include_once('../../inc/vendor_generic_functions.php');
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$role = $_SESSION['role'];
$branch_status = $_POST['branch_status']; 
?>
<form id="frm_vendor_payment_save1">
<input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>" >
<input type="hidden" id="emp_id" name="emp_id" value="<?= $emp_id ?>" >
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >
<div class="modal fade" id="v_payment_save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document" style="width:80%; margin-top:20px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Payment Save</h4>
      </div>
      <div class="modal-body">

            <div class="panel panel-default panel-body app_panel_style mg_tp_20 feildset-panel">
            <legend>Select Purchase</legend>
            <div class="row">
              <div class="col-md-5 col-sm-6 col-xs-12">
                <select id="estimate_id" class="form-control" name="estimate_id" style="width:100%" title="Supplier Estimate ID" onchange="get_payment_outstanding(this.id);" required>
                      <option value="">*Supplier Estimate ID</option>
                      <?php
                      $sq_estimate = mysqlQuery("select * from vendor_estimate where delete_status='0' order by estimate_id desc");
                      while($row_estimate = mysqli_fetch_assoc($sq_estimate)){

                        $balance_amount = 0;
                        $sq_supplier_p = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as payment_amount from vendor_payment_master where estimate_id='$row_estimate[estimate_id]' and clearance_status!='Pending' AND clearance_status!='Cancelled'"));
                        $total_paid = $sq_supplier_p['payment_amount'];
                        $cancel_est = $row_estimate['cancel_amount'];
                        if($row_estimate['purchase_return'] == '1'){
                          $status = '(Cancelled)';
                          if($total_paid > 0){
                            if($cancel_est >0){
                              if($total_paid > $cancel_est){
                                $balance_amount = 0;
                              }else{
                                $balance_amount = $cancel_est - $total_paid;
                              }
                            }else{
                              $balance_amount = 0;
                            }
                          }
                          else{
                            $balance_amount = $cancel_est;
                          }
                        }else if($row_estimate['purchase_return'] == '2'){
                          $status = '(Cancelled)';
                          $cancel_estimate = (json_decode($row_estimate['cancel_estimate'])[0] === null ) ? 0 : json_decode($row_estimate['cancel_estimate'])[0]->net_total;
                          $balance_amount = (($row_estimate['net_total'] - floatval($cancel_estimate)) + $cancel_est) - $total_paid;
                        }
                        else{
                          $status = '';
                          $balance_amount = $row_estimate['net_total'] - $total_paid;
                        }
                      if($balance_amount > 0){
                        $vendor_type_val = get_vendor_name($row_estimate['vendor_type'], $row_estimate['vendor_type_id']);
                        $estimate_type_val = get_estimate_type_name($row_estimate['estimate_type'], $row_estimate['estimate_type_id']);
                        $date = $row_estimate['purchase_date'];
                        $yr = explode("-", $date);
                        $year = $yr[0];
                        ?>
                        <option value="<?= $row_estimate['estimate_id'] ?>"><?= get_vendor_estimate_id($row_estimate['estimate_id'],$year)." : ".$vendor_type_val."(".$row_estimate['vendor_type'].") : ".$estimate_type_val.' '.$status ?></option>
                        <?php
                        }
                      }
                      ?>
                  </select>
              </div>
              <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                <input type="text" class="form-control" id="outstanding" name="outstanding" title="Outstanding" placeholder="Outstanding" readonly/>
                <input type="hidden" id="canc_status" name="canc_status" class="form-control"/>
              </div>
            </div>
            </div>
            <div id="div_payment_for"></div>
            <div class="panel panel-default panel-body app_panel_style mg_tp_20 feildset-panel">
            <legend>Payment Particulars</legend>
          
              <div class="row mg_bt_20">
                <div class="col-md-4">
                  <input type="text" id="payment_date" name="payment_date" class="form-control" placeholder="Date" title="Payment Date" value="<?= date('d-m-Y')?>" onchange="check_valid_date(this.id)">
                </div>  
                <div class="col-md-4">
                  <input type="text" id="payment_amount" name="payment_amount" class="form-control" placeholder="*Amount" title="Payment Amount" onchange="number_validate(this.id);">
                </div>             
                <div class="col-md-4">
                  <select class="form-control" name="payment_mode" id="payment_mode" title="*Payment Mode" onchange="payment_master_toggles(this.id, 'bank_name', 'transaction_id', 'bank_id')">
                  <option value="">*Payment Mode</option>
                    <option value="Cash">Cash</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="NEFT">NEFT</option>
                    <option value="RTGS">RTGS</option>
                    <option value="IMPS">IMPS</option>
                    <option value="DD">DD</option>
                    <option value="Online">Online</option>
                    <option value="Debit Note">Debit Note</option>
                    <option value="Advance">Advance</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              <div class="row mg_bt_10">
                <div class="col-md-4">
                  <input type="text" id="bank_name" name="bank_name" class="form-control bank_suggest" placeholder="Bank Name" title="Bank Name" disabled>
                </div>
                <div class="col-md-4">
                  <input type="number" id="transaction_id" name="transaction_id" class="form-control" placeholder="Cheque No/ID" title="Cheque No/ID" disabled>
                </div>
                <div class="col-md-4">
                  <select class="form-control" name="bank_id" id="bank_id" title="Debitor Bank" disabled>
                    <?php get_bank_dropdown('Debitor Bank'); ?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="div-upload pull-left" id="div_upload_button">
                      <div id="payment_evidence_upload" class="upload-button1"><span>Payment Evidence</span></div>
                      <span id="payment_evidence_status" ></span>
                      <ul id="files" ></ul>
                      <input type="hidden" id="payment_evidence_url" name="payment_evidence_url">
                  </div>
                </div>
                <div class="col-md-9 col-sm-9 no-pad mg_bt_20">
                  <span style="color: red;line-height: 35px;" data-original-title="" title="" class="note">Please make sure Date, Amount, Mode, Debitor bank entered properly.</span>
                </div>
              </div>

            </div>

            <div class="row text-center mg_tp_20">
                <div class="col-md-12">
                  <button class="btn btn-success" id="payment_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
                </div>
            </div>    


      </div>      
    </div>
  </div>
</div>
</form>

<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
$('#v_payment_save_modal').modal('show');
$('#payment_date').datetimepicker({timepicker:false, format:'d-m-Y'});

payment_evidence_upload();
function payment_evidence_upload(offset='')
{
    var btnUpload=$('#payment_evidence_upload'+offset);
    var status=$('#payment_evidence_status'+offset);
    new AjaxUpload(btnUpload, {
      action: 'payment/upload_payment_evidence.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){

        var id_proof_url = $("#payment_evidence_url"+offset).val();
        if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){ 
                    // extension is not allowed 
          status.text('Only JPG, PNG files are allowed');
          //return false;
        }
        status.text('Uploading...');
      },
      onComplete: function(file, response){
        //On completion clear the status
        status.text('');
        //Add uploaded file to list
        if(response==="error"){          
          alert("File is not uploaded.");           
          //$('<li></li>').appendTo('#files').html('<img src="./uploads/'+file+'" alt="" /><br />'+file).addClass('success');
        } else{
          ///$('<li></li>').appendTo('#files').text(file).addClass('error');
          $("#payment_evidence_url"+offset).val(response);
		      $(btnUpload).find('span').text('Uploaded');
          msg_alert('File uploaded!');
        }
      }
    });

}
$(function(){
  $('#frm_vendor_payment_save1').validate({
      rules:{              
        vendor_type: { required: true },
        payment_amount : { required: true, number:true },
        payment_date : { required: true },
        payment_mode : { required : true },
        bank_id : { required : function(){  if($('#payment_mode').val()!="Cash"){ return true; }else{ return false; }  }  },     
      },
      submitHandler:function(form){
        $('#payment_save').prop('disabled',true);
        var base_url = $('#base_url').val();
        var estimate_id = $('#estimate_id').val();
        var payment_amount = $('#payment_amount').val();
        var payment_date = $('#payment_date').val();
        var payment_mode = $('#payment_mode').val();
        var bank_name = $('#bank_name').val();
        var transaction_id = $('#transaction_id').val();              
        var bank_id = $('#bank_id').val();
        var payment_evidence_url = $('#payment_evidence_url').val();
        var branch_admin_id = $('#branch_admin_id1').val();
        var emp_id = $('#emp_id').val();
        var outstanding = $('#outstanding').val();
        var canc_status = $('#canc_status').val();
        
        var advance_amount = $('#advance_amount').val();
        var advance_nullify = $('#advance_nullify').val();
        var debit_note_amount =  $('#debit_note_amount').val();

        // Jquery check undefined value
        if(typeof advance_nullify === "undefined") { advance_nullify = '0'; }
        if(typeof advance_amount === "undefined") { advance_amount = '0'; }
        if(typeof debit_note_amount === 'undefined') { debit_note_amount = '0'; }
        if(advance_nullify == "") { advance_nullify = '0'; }

        if(payment_mode=='Credit Card'){
          $('#payment_save').prop('disabled',false);
          error_msg_alert("Please select another payment mode!");
          return false; 
        }
        if(parseFloat(outstanding) < parseFloat(payment_amount)){ 
          $('#payment_save').prop('disabled',false);
          error_msg_alert("Payment amount cannot be greater than outstanding amount.");
          return false; 
        }

        //Amount Validations
        if(parseFloat(advance_nullify) > 0 || parseFloat(advance_nullify) != '' || parseFloat(advance_nullify) != 0){

          if(parseFloat(payment_amount)>0){
            $('#payment_save').prop('disabled',false);
            error_msg_alert("Please release payment either from Advances or Payment Particulars!");
            return false; 
          }
          if(payment_mode!='Advance'){
            $('#payment_save').prop('disabled',false);
            error_msg_alert("Please select payment mode as Advance!");
            return false; 
          }
        }
        if(payment_mode=='Advance'){
          
          if(parseFloat(advance_nullify) <= 0 || parseFloat(advance_nullify) == ''){
            $('#payment_save').prop('disabled',false);
            error_msg_alert("Please select another payment mode!");
            return false; 
          }
          if(parseFloat(advance_amount) < parseFloat(payment_amount)){ 
            $('#payment_save').prop('disabled',false);
            error_msg_alert("Payment amount to be nullify should not be more than Advance");
            return false; 
          }
          if(parseFloat(advance_amount) < parseFloat(advance_nullify)){
            error_msg_alert("Amount to be nullify should not be more than Advance amount");
            $('#payment_save').prop('disabled', false);
            return false; 
          }
        }
        if (payment_mode == "Debit Note" && debit_note_amount == '0') {
          error_msg_alert("Debit Note Balance is not available");
          $('#payment_save').prop('disabled', false);
          return false;
        }
        else if(payment_mode == 'Debit Note' && debit_note_amount != '0') {
          if (parseFloat(payment_amount) > parseFloat(debit_note_amount)) {
            error_msg_alert('Debit Note Balance is not available');
            $('#payment_save').prop('disabled', false);
            return false;
          }
        }

        if(parseFloat(advance_nullify)<'0'){ 
          $('#payment_save').prop('disabled',false);
          error_msg_alert("Amount to be nullify should be greater than 0"); return false; 
        };

        var total_payment_amount = parseFloat(payment_amount) + parseFloat(advance_nullify);

          $.post(base_url+'view/load_data/finance_date_validation.php', { check_date: payment_date }, function(data){
          if(data !== 'valid'){
            $('#payment_save').prop('disabled',false);
            error_msg_alert("The Payment date does not match between selected Financial year.");
            $('#payment_save').prop('disabled', false);
            return false;
          }
          else{
            $('#payment_save').button('loading');
            $.ajax({
              type: 'post',
              url: base_url+'controller/vendor/dashboard/multiple_invoice_payment/payment_save.php',
              data:{ payment_amount : payment_amount, payment_date : payment_date, payment_mode : payment_mode, bank_name : bank_name, transaction_id : transaction_id, bank_id : bank_id, payment_evidence_url :payment_evidence_url, branch_admin_id : branch_admin_id , emp_id : emp_id,advance_nullify : advance_nullify,total_payment_amount:total_payment_amount,estimate_id:estimate_id,canc_status:canc_status },
              success: function(result){
              $('#payment_save').button('reset');
              $('#payment_save').prop('disabled',false);
              var msg = result.split('-');
              if(msg[0]=='error'){
                error_msg_alert(result);
              }
              else{
                msg_alert(result);
                $('#v_payment_save_modal').modal('hide'); 
                reset_form('frm_vendor_payment_save1'); 
                payment_list_reflect();
              }
              }
            });
          }
        });
      }
  });
});
</script>
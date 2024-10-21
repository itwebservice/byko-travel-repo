<?php 
include_once('../../../model/model.php');

$expense_id = $_POST['expense_id'];
$q_expense = mysqli_fetch_assoc(mysqlQuery("select * from other_expense_master where expense_id='$expense_id'"));
?>
<form id="frm_expense_update">
<input type="hidden" name="expense_id" value="<?= $expense_id ?>" id="expense_id"/>
<div class="modal fade" id="expense_update_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Update Expense</h4>
      </div>
      <div class="modal-body">
      	<input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>" >
      	<input type="hidden" id="emp_id" name="emp_id" value="<?= $emp_id ?>" >
			<div class="panel panel-default panel-body app_panel_style feildset-panel">
			<legend>*Expense For</legend>				
				<div class="row">
          <?php if($q_expense['expense_type_id'] != '0'){
              $sq_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$q_expense[expense_type_id]'")); ?>
          <div class="col-md-3">
            <select name="expense_type2" id="expense_type2" class="form-control" title="Expense Type" style="width:100%" disabled>
              <option value="<?= $sq_ledger['ledger_id'] ?>"><?= $sq_ledger['ledger_name'] ?></option>
            </select>
          </div>
          <?php } ?> 
          <?php if($q_expense['supplier_id'] != '0'){ ?>
					<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
						<select name="supplier_type2" id="supplier_type2" title="Supplier Name" class="form-control" style="width:100%" disabled>
							<?php 
							$sq_supp = mysqli_fetch_assoc(mysqlQuery("select * from other_vendors where vendor_id='$q_expense[supplier_id]'"));
							?>
							<option value="<?= $sq_supp['vendor_id'] ?>"><?= $sq_supp['vendor_name'] ?></option>
						</select>
					</div> <?php } ?>
				</div>
			</div><div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">
          <legend>Payment Details</legend>
          <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
              <input type="text" id="sub_total1" name="sub_total1" value="<?= $q_expense['amount'] ?>" placeholder="*Amount" title="Amount" class="form-control" onchange="validate_balance(this.id);total_fun_update();">
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                <input type="text" id="service_tax_subtotals1" name="service_tax_subtotals1" placeholder="Tax" title="Tax" onchange="validate_balance(this.id);total_fun_update();" value="<?= $q_expense['tax_refl'] ?>" readonly>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10 hidden">
                <input type="text" id="service_tax_subtotal1" name="service_tax_subtotal1" placeholder="Tax Amount" title="Tax Amount" onchange="validate_balance(this.id);total_fun_update();" value="<?= $q_expense['service_tax_subtotal'] ?>" readonly>
                <input type="hidden" id="old_tax" name="old_tax" value="<?= $q_expense['service_tax_subtotal'] ?>">
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10 hidden">
              <input type="hidden" id="ledger_ids1" name="ledger_ids1" value="<?= $q_expense['ledgers'] ?>">
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <input type="text" id="tds1" name="tds1" placeholder="TDS" title="TDS" value="<?= $q_expense['tds'] ?>" class="form-control" onchange="validate_balance(this.id);total_fun_update();">
            </div> 
          </div>
          <div class="row mg_bt_10">           
            <div class="col-md-4 col-sm-6 col-xs-12">
                <input type="text" name="total_fee1" id="total_fee1" class="amount_feild_highlight text-right form-control" placeholder="*Net Total" title="Net Total" value="<?= $q_expense['total_fee'] ?>" readonly>
                <input type="hidden" name="old_total" id="old_total" value="<?= $q_expense['total_fee'] ?>">
            </div>                        
            <div class="col-md-4 col-sm-6 col-xs-12">
              <input type="text" name="due_date1" id="due_date1" placeholder="Due Date" title="Due Date" value="<?= get_date_user($q_expense['due_date']) ?>" class="form-control">
            </div>              
            <div class="col-md-4 col-sm-6 col-xs-12">
              <input type="text" name="booking_date1" id="booking_date1" placeholder="Booking Date" value="<?= get_date_user($q_expense['expense_date']) ?>" class="form-control" title="Booking Date" onchange="check_valid_date(this.id)">
            </div>             
          </div>
          <div class="row mg_bt_10">   
            <div class="col-md-4 col-sm-6 col-xs-12">
                <input type="text" name="invoice_no1" id="invoice_no1" placeholder="Invoice No" value="<?= $q_expense['invoice_no'] ?>" class="form-control" title="Invoice No">
            </div>  
            <div class="col-xs-4 mg_bt_10_sm_xs">     
                    <div class="div-upload">
                      <div id="id_upload_btn1" class="upload-button1"><span>Upload Invoice</span></div>
                      <span id="id_proof_status" ></span>
                      <ul id="files" ></ul>
                      <input type="hidden" id="id_upload_url1" name="id_upload_url1" value="<?= $q_expense['invoice_url'] ?>">
                    </div> 
            </div>	
          </div>
          <div class="row">
            <div class="col-xs-12 text-center">
              <button class="btn btn-sm btn-success" id="btn_update_expense"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>
            </div>
          </div>
	    </div>      
    </div>
  </div>
</div>
</form>

<script>
$('#expense_update_modal').modal('show');
$('#expense_type2,#supplier_type2').select2();
$('#payment_date1,#due_date1,#booking_date1').datetimepicker({ timepicker:false, format:'d-m-Y' });


function total_fun_update()
{ 
    var sub_total = $('#sub_total1').val();   
    var tds = $('#tds1').val();

    if(sub_total==""){ sub_total = 0; }
    if(tds==""){ tds = 0; }

    var service_tax = 0;
    var service_tax_amount = 0;
    var applied_taxes = '';
    var ledger_posting = '';
    var tax_value = $('#service_tax_subtotals1').val();

    if(tax_value!=""){
      var service_tax_subtotal1 = tax_value.split(",");
      for(var i=0;i<service_tax_subtotal1.length;i++){
        var service_tax_string = service_tax_subtotal1[i].split(':');
        if(parseInt(service_tax_string.length) > 0){
          var service_tax_string1 = service_tax_string[1] && service_tax_string[1].split('%');
          service_tax_string1[0] = service_tax_string1[0] && service_tax_string1[0].replace('(','');
          service_tax = service_tax_string1[0];
        }

        service_tax_string[2] = service_tax_string[2].replace('(','');
        service_tax_string[2] = service_tax_string[2].replace(')','');
        service_tax_amount = (( parseFloat(sub_total) * parseFloat(service_tax) ) / 100).toFixed(2);
        if(applied_taxes==''){
          applied_taxes = service_tax_string[0] +':'+ service_tax_string[1] + ':' + service_tax_amount;
          ledger_posting = service_tax_string[2];
        }else{
          applied_taxes += ', ' + service_tax_string[0] +':'+ service_tax_string[1] + ':' + service_tax_amount;
          ledger_posting += ', ' + service_tax_string[2];
        }
      }
      $('#service_tax_subtotals1').val(applied_taxes);
      $('#ledger_ids').val(ledger_posting);
    }else{
      $('#service_tax_subtotals1').val('');
      $('#ledger_ids1').val('');
    }
    console.log(applied_taxes);
    var service_tax_subtotal = $('#service_tax_subtotals1').val();   
    if(service_tax_subtotal==""){ service_tax_subtotal = 0; }
		var service_tax_amount = 0;
		if (parseFloat(service_tax_subtotal) !== 0.00 && (service_tax_subtotal) !== '') {

			var service_tax_subtotal1 = service_tax_subtotal.split(",");
			for (var i = 0; i < service_tax_subtotal1.length; i++) {
				var service_tax = service_tax_subtotal1[i].split(':');
				service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
			}
		}
    $('#service_tax_subtotal1').val(service_tax_amount);

    var total_amount = parseFloat(sub_total) + parseFloat(service_tax_amount) - parseFloat(tds);
    var total=total_amount.toFixed(2);
    $('#total_fee1').val(total);
}

function upload_hotel_pic_attch()
{
    var btnUpload=$('#id_upload_btn1');
    $(btnUpload).find('span').text('Upload Invoice');
    
    new AjaxUpload(btnUpload, {
      action: 'booking/upload_invoice_proof.php',
      name: 'uploadfile',
      onSubmit: function(file, ext)
      {  
        if (! (ext && /^(jpg|png|jpeg|pdf)$/.test(ext))){ 
          error_msg_alert('Only JPG, PNG, pdf files are allowed');
          return false;
        }
        $(btnUpload).find('span').text('Uploading...');
      },
      onComplete: function(file, response){
        if(response==="error"){          
          error_msg_alert("File is not uploaded.");           
          $(btnUpload).find('span').text('Upload');
        }else
        { 
          $(btnUpload).find('span').text('Uploaded');
          $("#id_upload_url1").val(response);
        }
      }
    });
}
upload_hotel_pic_attch();
$(function(){
	$('#frm_expense_update').validate({
		rules:{	
        expense_type: { required: true  },
        sub_total1:{ required : true, number: true },
		},
		submitHandler:function(form){

        $('#btn_update_expense').prop('disabled',true);
        var base_url = $('#base_url').val();
        var expense_id = $('#expense_id').val();
        var expense_type = $('#expense_type2').val();
        var supplier_type = $('#supplier_type2').val();
        var sub_total = $('#sub_total1').val();
        var service_tax_subtotal = $('#service_tax_subtotal1').val();
        var service_tax_subtotals = $('#service_tax_subtotals1').val();
        var old_tax = $('#old_tax').val();
        var old_total = $('#old_total').val();
        var ledger_ids = $('#ledger_ids1').val();
        if(parseInt(old_tax) === 0 && (parseFloat(service_tax_subtotal) !== 0)){
          error_msg_alert('Tax amount can not update from 0');
              $('#btn_update_expense').prop('disabled',false); return false;
        }
        if(parseFloat(service_tax_subtotal) !== 0 && ledger_ids.length < 1){
          error_msg_alert('Please select ledger for posting!');
              $('#btn_update_expense').prop('disabled',false); return false;
        }
        ledger_ids = ledger_ids.toString();
				var tds = $('#tds1').val();
				var net_total = $('#total_fee1').val();
				var due_date = $('#due_date1').val();
				var booking_date = $('#booking_date1').val();
				var invoice_no = $('#invoice_no1').val();
				var id_upload_url = $('#id_upload_url1').val();
		    var taxation_id = $('#taxation_id1').val();
        
        //Validation for booking and payment date in login financial year
        var check_date1 = $('#booking_date1').val();
        $.post(base_url+'view/load_data/finance_date_validation.php', { check_date: check_date1 }, function(data){
          if(data !== 'valid'){
            error_msg_alert("The Booking date does not match between selected Financial year.");
              $('#btn_update_expense').prop('disabled',false);
            return false;
          }else{
				      $('#btn_update_expense').button('loading');
              $('#btn_update_expense').prop('disabled',true);
	            $.ajax({
	              type:'post',
	              url: base_url+'controller/other_expense/expense_booking_update.php',
	              data:{ expense_id : expense_id,expense_type : expense_type, supplier_type : supplier_type, sub_total : sub_total,ledger_ids : ledger_ids, service_tax_subtotal : service_tax_subtotal,service_tax_subtotals:service_tax_subtotals, tds : tds, net_total : net_total, due_date : due_date, booking_date : booking_date, invoice_no : invoice_no, id_upload_url : id_upload_url,old_total:old_total },
	              success:function(result){
	              	$('#btn_update_expense').button('reset');
	                msg_alert(result);	                
	                $('#expense_update_modal').modal('hide');
	                $('#expense_update_modal').on('hidden.bs.modal', function(){
	                	expense_dashboard_content_reflect();
                    $('#btn_update_expense').prop('disabled',false);
	                });
	              }
              });
            }
          });
        }
      });
});
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
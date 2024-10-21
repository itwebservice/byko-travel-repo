<?php
include_once("../../../../model/model.php");
$entry_id =$_POST['entry_id'];
$sq_tax = mysqli_fetch_assoc(mysqlQuery("select * from tax_master where entry_id='$entry_id'"));
?>
<input type="hidden" id="modal_type" name="modal_type">
<div class="modal fade" id="taxes_update_modal" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Update Tax</h4>
      </div>
      <div class="modal-body">

          <div class="row">
            <input type="hidden" id="entry_id" value="<?= $sq_tax['entry_id'] ?>"/>
            <div class="col-md-3"> 
              <select title="*Reflection" id="reflt1" class="form-control">
                <option value="<?= $sq_tax['reflection'] ?>"><?= $sq_tax['reflection'] ?></option>
                <option value="">*Reflection</option>
                <option value="Income">Income</option>
                <option value="Expense">Expense</option>
              </select>
            </div>
            <div class="col-md-3">
              <input type="text" placeholder="*Tax Name-1" title="Tax Name-1" id="namet1"  class="form-control" value="<?= $sq_tax['name1'] ?>" />
            </div>
            <div class="col-md-3">
              <input type="number" placeholder="*Tax Amount-1(%)" min="0" title="Tax Amount-1(%)" id="amountt1" class="form-control" onchange="toggle_rate_validation(this.id)" value="<?= $sq_tax['amount1'] ?>"/>
            </div> 
            <div class="col-md-3">
              <select title="*Select Ledger-1" id="ledgert1" style="width:100%" class="form-control app_select2">
                <?php
                $sq = mysqli_fetch_assoc(mysqlQuery("select ledger_name,ledger_id from ledger_master where ledger_id='$sq_tax[ledger1]'"));
                ?>
                <option value="<?= $sq['ledger_id'] ?>"><?= $sq['ledger_name'] ?></option>
                <option value="">*Select Ledger-1</option>
                <?php
                $sq = mysqlQuery("select * from ledger_master where group_sub_id in('99','106') order by ledger_name");
                while($row = mysqli_fetch_assoc($sq)){ ?>
                  <option value="<?= $row['ledger_id'] ?>"><?= $row['ledger_name'] ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="row mg_tp_20">
            <div class="col-md-3">
              <input type="text" placeholder="Tax Name-2" title="Tax Name-2" id="namet2"  class="form-control" value="<?= $sq_tax['name2'] ?>" />
            </div>
            <div class="col-md-3">
              <input type="number" placeholder="Tax Amount-2(%)" min="0" title="Tax Amount-2(%)" id="amountt2" class="form-control" onchange="toggle_rate_validation(this.id)" value="<?= $sq_tax['amount2'] ?>"/>
            </div> 
            <div class="col-md-3">
              <select title="Select Ledger-2" id="ledgert2" style="width:100%" class="form-control app_select2">
                <?php
                if($sq_tax['ledger2'] != 0){
                $sq = mysqli_fetch_assoc(mysqlQuery("select ledger_name,ledger_id from ledger_master where ledger_id='$sq_tax[ledger2]'"));
                  ?>
                  <option value="<?= $sq['ledger_id'] ?>"><?= $sq['ledger_name'] ?></option>
                <?php } ?>
                <option value="">Select Ledger-2</option>
                <?php
                $sq = mysqlQuery("select * from ledger_master where group_sub_id in('99','106') order by ledger_name");
                while($row = mysqli_fetch_assoc($sq)){ ?>
                  <option value="<?= $row['ledger_id'] ?>"><?= $row['ledger_name'] ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-3"> 
              <select title="*Status" id="status" class="form-control">
                <option value="<?= $sq_tax['status'] ?>"><?= $sq_tax['status'] ?></option>
                <?php
                if($sq_tax['status'] != 'Active') { ?><option value="Active">Active</option> <?php }
                if($sq_tax['status'] != 'Inactive') { ?><option value="Inactive">Inactive</option> <?php } ?>
              </select>
            </div>
          </div>
      
        <div class="row mg_tp_20">
          <div class="col-md-12 text-center">
            <button class="btn btn-sm btn-success" onclick="taxes_master_update()" id="btn_taxes_update"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>
          </div>
        </div>
      </div>      
    </div>
  </div>
</div>

<script>
$('#taxes_update_modal').modal('show');
$('#ledgert1,#ledgert2').select2();
function taxes_master_update(){

  var base_url = $('#base_url').val();
  var entry_id = $('#entry_id').val();
  var reflection = $('#reflt1').val();
  var tax_name1 = $('#namet1').val();
  var tax_amount1 = $('#amountt1').val();
  var ledger1 = $('#ledgert1').val();
  var tax_name2 = $('#namet2').val();
  var tax_amount2 = $('#amountt2').val();
  var ledger2 = $('#ledgert2').val();
  var status = $('#status').val();
  
  if(reflection ==""){
    error_msg_alert("Select reflection");
    return false;
  }
  // Tax-1
  if(tax_name1==""){
    error_msg_alert("Enter tax name-1");
    return false;
  }
  if(tax_amount1==""){
    error_msg_alert("Enter tax amount-1");
    return false;
  }
  if(ledger1==""){
    error_msg_alert("Select ledger-1");
    return false;
  }
  if(parseFloat(tax_amount1) < 0){
    error_msg_alert("Tax amount-1 should not be less than 0");
    return false;
  }
    // Tax-2
    if(tax_name2!=""){
      if(tax_amount2==""){
        error_msg_alert("Enter tax amount-2");
        return false;
      }
      if(ledger2==""){
        error_msg_alert("Select ledger-2");
        return false;
      }
      if(parseFloat(tax_amount2) < 0){
        error_msg_alert("Tax amount-1 should not be less than 0");
        return false;
      }
    }
    // Tax-2
    if(tax_amount2!="" && tax_amount2!=0){
      if(tax_name2==""){
        error_msg_alert("Enter tax name-2");
        return false;
      }
      if(ledger2==""){
        error_msg_alert("Select ledger-2");
        return false;
      }
      if(parseFloat(tax_amount2) < 0){
        error_msg_alert("Tax amount-1 should not be less than 0");
        return false;
      }
    }
    // Tax-2
    if(ledger2!=""){
      if(tax_name2==""){
        error_msg_alert("Enter tax name-2");
        return false;
      }
      if(tax_amount2==""){
        error_msg_alert("Enter Tax amount-2");
        return false;
      }
      if(parseFloat(tax_amount2) < 0){
        error_msg_alert("Tax amount-1 should not be less than 0");
        return false;
      }
    }

  $('#btn_taxes_update').button('loading');
  $.post( 
        base_url+"controller/business_rules/taxes/update.php",
        { entry_id:entry_id,reflection : reflection, tax_name1 : tax_name1,tax_amount1:tax_amount1,ledger1:ledger1,tax_name2:tax_name2,tax_amount2:tax_amount2,ledger2:ledger2,status:status },
        function(data) {
          var msg = data.split('--');
          if(msg[0].replace(/\s/g, '') === "error"){
            error_msg_alert(msg[1]);
            $('#btn_taxes_update').button('reset');
            return false;
          }
          else{
              success_msg_alert(data);
              $('#btn_taxes_update').button('reset');
              $('#taxes_update_modal').modal('hide');
              update_cache();
              list_reflect();
          }
  });
}
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
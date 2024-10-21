<?php
include_once("../../../../model/model.php");
?>
<input type="hidden" id="modal_type" name="modal_type">
<div class="modal fade" id="taxes_save_modal" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add New Tax</h4>
      </div>
      <div class="modal-body">
        
        <div class="row mg_bt_10"> <div class="col-md-12 text-right">
            <button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_taxes')"><i class="fa fa-plus"></i></button>
            <button type="button" class="btn btn-pdf btn-sm" title="Delete Row" onclick="deleteRow('tbl_taxes')"><i class="fa fa-trash"></i></button>
        </div></div>

        <div class="row"> <div class="col-md-12"> <div class="table-responsive">
        
          <table id="tbl_taxes" name="tbl_taxes" class="table border_0 table-hover no-marg pd_bt_51"  cellspacing="0">
              <tr>
                  <td><input id="chk_tax1" type="checkbox" checked></td>
                  <td><input maxlength="15" value="1" type="text" name="username" placeholder="Sr. No." class="form-control" disabled /></td>
                  <td><select title="*Reflection" id="reflt1" class="form-control" style="width:150px!important;">
                      <option value="">*Reflection</option>
                      <option value="Income">Income</option>
                      <option value="Expense">Expense</option>
                    </select></td>
                  <td><input type="text" placeholder="*Tax Name-1" title="Tax Name-1" id="namet1"  class="form-control" style="width:160px!important;" /></td>
                  <td><input type="number" placeholder="*Tax Amount-1(%)" min="0" title="Tax Amount-1(%)" id="amountt1" class="form-control" onchange="toggle_rate_validation(this.id)" style="width:170px!important;" /></td>
                  <td><select title="*Select Ledger-1" id="ledgert1"  class="form-control app_select2" style="width:200px!important;">
                      <option value="">*Select Ledger-1</option>
                      <?php
                      $sq = mysqlQuery("select * from ledger_master where group_sub_id in('99','106') order by ledger_name");
                      ?>
                      <?php while($row = mysqli_fetch_assoc($sq)){ ?>
                        <option value="<?= $row['ledger_id'] ?>"><?= $row['ledger_name'] ?></option>
                      <?php } ?>
                    </select>
                  </td>
                  <td><input type="text" placeholder="Tax Name-2" title="Tax Name-2" id="namet2"  class="form-control" style="width:160px!important;" /></td>
                  <td><input type="number" placeholder="Tax Amount-2(%)" min="0" title="Tax Amount-2(%)" id="amountt2" class="form-control" onchange="toggle_rate_validation(this.id)" style="width:170px!important;" /></td>
                  <td><select title="Select Ledger-2" id="ledgert2" class="form-control app_select2" style="width:200px!important;">
                      <option value="">Select Ledger-2</option>
                      <?php
                      $sq = mysqlQuery("select * from ledger_master where group_sub_id in('99','106') order by ledger_name");
                      ?>
                      <?php while($row = mysqli_fetch_assoc($sq)){ ?>
                        <option value="<?= $row['ledger_id'] ?>"><?= $row['ledger_name'] ?></option>
                      <?php } ?>
                    </select></td>
              </tr>                                
          </table>  

        </div> </div> </div>
      
        <div class="row mg_tp_20">
          <div class="col-md-12 text-center">
            <button class="btn btn-sm btn-success" onclick="taxes_master_save()" id="btn_taxes_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
          </div>
        </div>


      </div>      
    </div>
  </div>
</div>

<script>
$('#taxes_save_modal').modal('show');
$('#ledgert1,#ledgert2').select2();
function taxes_master_save()
{
  var base_url = $('#base_url').val();
  var reflection_array = new Array();
  var tax_name1_array = new Array();
  var tax_amount1_array = new Array();
  var ledger1_array = new Array();
  var tax_name2_array = new Array();
  var tax_amount2_array = new Array();
  var ledger2_array = new Array();

  var table = document.getElementById("tbl_taxes");
  var rowCount = table.rows.length;
  for(var i=0; i<rowCount; i++){

    var row = table.rows[i];
    if(row.cells[0].childNodes[0].checked){

      var reflection = row.cells[2].childNodes[0].value;
      var tax_name1 = row.cells[3].childNodes[0].value;
      var tax_amount1 = row.cells[4].childNodes[0].value;
      var ledger1 = row.cells[5].childNodes[0].value;
      var tax_name2 = row.cells[6].childNodes[0].value;
      var tax_amount2 = row.cells[7].childNodes[0].value;
      var ledger2 = row.cells[8].childNodes[0].value;
      
      if(reflection ==""){
        error_msg_alert("Select reflection in row"+(i+1));
        return false;
      }
      // Tax-1
      if(tax_name1==""){
        error_msg_alert("Enter tax name-1 in row"+(i+1));
        return false;
      }
      if(tax_amount1==""){
        error_msg_alert("Enter tax amount-1 in row"+(i+1));
        return false;
      }
      if(ledger1==""){
        error_msg_alert("Select ledger-1 in row"+(i+1));
        return false;
      }
      if(parseFloat(tax_amount1) < 0){
        error_msg_alert("Tax amount-1 should not be less than 0 in row"+(i+1));
        return false;
      }
      // Tax-2
      if(tax_name2!=""){
        if(tax_amount2==""){
          error_msg_alert("Enter tax amount-2 in row"+(i+1));
          return false;
        }
        if(ledger2==""){
          error_msg_alert("Select ledger-2 in row"+(i+1));
          return false;
        }
        if(parseFloat(tax_amount2) < 0){
          error_msg_alert("Tax amount-1 should not be less than 0 in row"+(i+1));
          return false;
        }
      }
      // Tax-2
      if(tax_amount2!="" && tax_amount2!=0){
        if(tax_name2==""){
          error_msg_alert("Enter tax name-2 in row"+(i+1));
          return false;
        }
        if(ledger2==""){
          error_msg_alert("Select ledger-2 in row"+(i+1));
          return false;
        }
        if(parseFloat(tax_amount2) < 0){
          error_msg_alert("Tax amount-1 should not be less than 0 in row"+(i+1));
          return false;
        }
      }
      // Tax-2
      if(ledger2!=""){
        if(tax_name2==""){
          error_msg_alert("Enter tax name-2 in row"+(i+1));
          return false;
        }
        if(tax_amount2==""){
          error_msg_alert("Enter Tax amount-2 in row"+(i+1));
          return false;
        }
        if(parseFloat(tax_amount2) < 0){
          error_msg_alert("Tax amount-1 should not be less than 0 in row"+(i+1));
          return false;
        }
      }
      reflection_array.push(reflection);
      tax_name1_array.push(tax_name1);
      tax_amount1_array.push(tax_amount1);
      ledger1_array.push(ledger1);
      tax_name2_array.push(tax_name2);
      tax_amount2_array.push(tax_amount2);
      ledger2_array.push(ledger2);
    }
  }

  if(reflection_array.length==0){
    error_msg_alert("Select rows to save taxes!");
    return false;
  }  

  $('#btn_taxes_save').button('loading');
  $.post( 
        base_url+"controller/business_rules/taxes/save.php",
        { reflection_array : reflection_array, tax_name1_array : tax_name1_array,tax_amount1_array:tax_amount1_array,ledger1_array:ledger1_array,tax_name2_array:tax_name2_array,tax_amount2_array:tax_amount2_array,ledger2_array:ledger2_array },
        function(data) {
          var msg = data.split('--');
          if(msg[0].replace(/\s/g, '') === "error"){
            error_msg_alert(msg[1]);
            $('#btn_taxes_save').button('reset');
            return false;
          }
          else{
              success_msg_alert(data);
              $('#btn_taxes_save').button('reset');
              $('#taxes_save_modal').modal('hide');
              update_cache();
              list_reflect();
          }
  });
}
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
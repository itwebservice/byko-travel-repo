<?php 
include "../../../model/model.php";
$entity_id = $_POST['entity_id'];

$query = "select * from checklist_entities where entity_id = '$entity_id'";
$sq_checklist = mysqli_fetch_assoc(mysqlQuery($query));
?>
<div class="modal fade" id="entity_update_modal" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Update Checklist</h4>
      </div>
      <div class="modal-body">
        
        <form id="frm_entity_update">
        <div class="row">
          <input type="hidden" name="entity_id" id="entity_id1" value="<?php echo $sq_checklist['entity_id'];?>">
              
          <div class="col-sm-4 mg_bt_30">
            <select name="entity_for" class="form-control" id="entity_for1" data-toggle="tooltip" title="Select Service" disabled="" >
              <?php
              $entity_for = ($sq_checklist['entity_for'] == 'Excursion Booking')?'Activity Booking':$sq_checklist['entity_for'];
              ?>
              <option value="<?php echo $sq_checklist['entity_for'];?>"><?php echo $entity_for; ?></option>
              <option value="">Select Service</option>
              <option value="Package Tour">Package Tour</option>
              <option value="Group Tour">Group Tour</option>
              <option value="Hotel Booking">Hotel Booking</option>
              <option value="Flight Booking">Flight Booking</option>
              <option value="Visa Booking">Visa Booking</option>
              <option value="Car Rental Booking">Car Rental Booking</option>
              <option value="Excursion Booking">Activity Booking</option>
              <option value="Train Booking">Train Booking</option>
              <option value="Bus Booking">Bus Booking</option>
              <option value="Miscellaneous Booking">Miscellaneous Booking</option>
            </select>
          </div>
          <?php
            if($sq_checklist['entity_for']=='Package Tour'||$sq_checklist['entity_for']=='Group Tour'){ 
              $class = '';
            }else{
              $class = 'hidden';
            }
          ?>
           <div class="col-sm-4 mg_bt_30 <?= $class ?>">
            <?php if($sq_checklist['destination_name']!=''){  ?>
           
              <select id="dest_name_s"  name="dest_name_s" title="Select Destination" class="form-control"  style="width:100%" disabled> 
                  <?php
                    $sq_query1 = mysqli_fetch_assoc(mysqlQuery("select * from destination_master where dest_id = '$sq_checklist[destination_name]'"));
                    ?>

                    <option value="<?= $sq_checklist['destination_name'] ?>"><?= $sq_query1['dest_name']; ?></option>
                      <option value="">*Destination</option>
                      <?php 
                      $sq_query = mysqlQuery("select * from destination_master where status != 'Inactive'"); 
                      while($row_dest = mysqli_fetch_assoc($sq_query)){ ?>
                          <option value="<?php echo $row_dest['dest_id']; ?>"><?php echo $row_dest['dest_name']; ?></option>
                  <?php } ?>
                </select>
            <?php } ?>
          </div>
          
         
        </div>
        
        <div class="row mg_bt_10">
        <div class="col-md-4 text-right"></div>
          <div class="col-md-4 text-center"><h4>Checklist Entries<h4></div>
          <div class="col-md-4 text-right">
            <button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_dynamic_tour_name_update')"><i class="fa fa-plus"></i></button>
          </div> </div>

          <div class="row"> <div class="col-md-12"> 
            <table id="tbl_dynamic_tour_name_update" name="tbl_dynamic_tour_name_update" class="table table-bordered table-hover no-marg"  cellspacing="0">
              <?php
              $count=0;
              $sql_query=mysqlQuery("select * from to_do_entries where entity_id = '$entity_id'");
              while($row_query=mysqli_fetch_assoc($sql_query)){
                $count++;
              ?>
              <tr>
                  <td class="col-md-1"><input id="chk_tour_group1<?= $count ?>" type="checkbox" class="form-control" checked ></td>
                  <td class="col-md-1"><input maxlength="15" value="<?= $count ?>" type="text" name="username" placeholder="Sr. No." class="form-control" disabled /></td>
                  <td class="col-md-10"><input type="text" placeholder="*Checklist Name" onchange="validate_specialChar(this.id)" id="entity_name<?= $count ?>"  name="entity_name" title="Checklist Name" class="form-control" value="<?php echo $row_query['entity_name'];?>" /></td>
                  <td><input type="hidden" id="entry_id<?= $count ?>"  name="entry_id" class="form-control" value="<?php echo $row_query['id'];?>" /></td>
              </tr> 
              <?php } ?>                               
            </table>  

          </div> </div>
          <div class="row text-center mg_tp_20">
          <div class="col-md-12">
            <button class="btn btn-sm btn-success" id="update_button"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>
          </div>
        </div>
        </form>
      </div>      
    </div>
  </div>
</div>

<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>
<script>
$('#entity_update_modal').modal('show');

 function feild_reflect(){
    var entity_for = $('#entity_for').val();
    var base_url = $('#base_url').val();
    $.post(base_url+'view/checklist/entities/tour_load.php', { entity_for : entity_for }, function(data){
   $('#div_reflect_tour').html(data);
     });
    }


$(function(){
  $('#frm_entity_update').validate({
    rules:{
      
    },
    submitHandler:function(form){
      
      $('#update_button').prop('disabled',true);
      var entity_id = $('#entity_id1').val();
      var base_url =$('#base_url').val();
      var checked_arr = new Array();
      var entity_name_arr = new Array();
      var entry_id_arr = new Array();

      var table = document.getElementById("tbl_dynamic_tour_name_update");
      var rowCount = table.rows.length;
      for(var i=0; i<rowCount; i++)
      {
        var row = table.rows[i];
      
          var checked = row.cells[0].childNodes[0].checked;
          var entity_name = row.cells[2].childNodes[0].value;
          var entry_id = row.cells[3].childNodes[0].value;
          
          if(row.cells[0].childNodes[0].checked){
            if(entity_name == ''){ error_msg_alert("Enter Checklist Name in Row "+(i+1));
            $('#update_button').prop('disabled',false); return false; }
          }
          entity_name_arr.push(entity_name);
          entry_id_arr.push(entry_id);
          checked_arr.push(checked);
       
      }
      
      $('#update_button').button('loading');
      $.ajax({
        type:'post',
        url:base_url+'controller/checklist/entities/entity_update.php',
        data:{entity_name_arr : entity_name_arr,entity_id : entity_id,entry_id_arr : entry_id_arr,checked_arr:checked_arr},
        success:function(result){
          var msg = result.split('--');
          if(msg[0] == 'error'){
            error_msg_alert(msg[1]); 
            $('#update_button').prop('disabled',false);
            $('#save_checklist').button('reset');
            return false;
          }else{
            success_msg_alert(msg[0]);
            $('#update_button').button('reset');
            $('#update_button').prop('disabled',false);
            $('#entity_update_modal').modal('hide');
            entities_list_reflect();
          }
        }
      });
    }
  });
});
</script>
<?php
include "../../../model/model.php";
?>
<div class="row text-right mg_tp_20"> <div class="col-md-12">
   <button class="btn btn-info btn-sm ico_left" onclick="state_save()" id="state_save_modal_btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;State/Country</button>
</div> </div>

<div id="div_list_content" class="loader_parent">
    <div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
        <table id="state_table" class="table table-hover" style="margin: 20px 0 !important;">         
        </table>
    </div></div></div>
</div>
<div id="div_state_list_update_modal"></div>
<script>
var columns = [
          { title: "State/Country Id" },
          { title: "State/Country Name" },
          { title: "Status" },
          { title: "Actions", className:"text-center" }
      ];
function list_reflect(){
  $('#div_list_content').append('<div class="loader"></div>');
  $.post('states/list_reflect.php', {}, function(data){
     setTimeout(() => {
      pagination_load(data,columns,true, false, 20, 'state_table');
      $('.loader').remove();
     }, 1000);
   });
}list_reflect();

function state_master_update_modal(id)
{
	$('#state_update-'+id).button('loading');
  $('#state_update-'+id).prop('disabled',true);
  $('#div_state_list_update_modal').load('states/update_modal.php', { id : id }).hide().fadeIn(500);
	$('#state_update-'+id).button('reset');
  $('#state_update-'+id).prop('disabled',false);
}

function state_save() {
	$('#state_save_modal_btn').button('loading');
  $('#state_save_modal_btn').prop('disabled',true);
	$.post('states/save_modal.php', {}, function(data){
		$('#div_state_list_update_modal').html(data);
    $('#state_save_modal_btn').button('reset');
    $('#state_save_modal_btn').prop('disabled',false);
	});
}
</script>
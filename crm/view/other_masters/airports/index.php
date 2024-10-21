<?php include "../../../model/model.php";?>
<div class="row text-right mg_tp_20 mg_bt_10">
	<div class="col-md-12">
		<button class="btn btn-info btn-sm ico_left" onclick="save_modal()" id="btn_save_modal"><i class="fa fa-plus"></i>&nbsp;&nbsp;Airport</button>
	</div>
</div>
<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="text-left col-md-3 col-sm-6">
			<select id="airport_status" name="airport_status" title="Select Status" class="form-control" onchange="list_reflect()" style="width:100%"> 
				<option value="Active">Active</option>
				<option value="Inactive">Inactive</option>
			</select>
		</div>
	</div>
</div>

<div id="div_list">
	<div id="div_list_content" class="main_block loader_parent">
		<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
			<table id="airport_table" class="table table-hover" style="margin: 20px 0 !important;">
			</table>
		</div></div></div>
	</div>
</div>
	<div id="div_modal"></div>
<script>
// var action = 'inactive'; //Check if current action is going on or not. If not then inactive otherwise active
var columns = [
	{ title: "Airport_Id" },
	{ title: "City" },
	{ title: "Airport" },
	{ title: "Code" },
	{ title: "Status" },
	{ title: "Actions" }
];
function list_reflect(){
	$('#div_list_content').append('<div class="loader"></div>');
	var status = $('#airport_status').val();
	$.post('airports/list_reflect.php', { status : status }, function(data){
	setTimeout(() => {
    	pagination_load(data,columns,true, false, 20, 'airport_table');
		$('.loader').remove();
    }, 1000);
	});
}list_reflect();

function save_modal(){
	$('#btn_save_modal').button('loading');
	$.post('airports/save_modal.php', {}, function(data){
		$('#btn_save_modal').button('reset');
		$('#div_modal').html(data);
	});
}

function update_modal(airport_id){
	$('#airport_update-'+airport_id).button('loading');
	$('#airport_update-'+airport_id).prop('disabled',true);
	$.post('airports/update_modal.php', { airport_id : airport_id }, function(data){
		$('#div_modal').html(data);
		$('#airport_update-'+airport_id).button('reset');
		$('#airport_update-'+airport_id).prop('disabled',false);
	});
}
</script>
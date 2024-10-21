<?php
include "../../../model/model.php";
?>
<div class="row text-right mg_tp_20">
	<div class="col-md-12">
		<button class="btn btn-info btn-sm ico_left" onclick="save_modal()" id="btn_save_modal"><i class="fa fa-plus"></i>&nbsp;&nbsp;Reference</button>
	</div>
</div>

<div id="div_modal"></div>
<div id="div_list"></div>
<script>
function save_modal()
{
	$('#btn_save_modal').button('loading');
	$.post('references/save_modal.php', {}, function(data){
		$('#btn_save_modal').button('reset');
		$('#div_modal').html(data);
	});
}
function list_reflect()
{
	$.post('references/list_reflect.php', {}, function(data){
		$('#div_list').html(data);
	});
}
list_reflect();
function update_modal(reference_id)
{
	$('#ref_update-'+reference_id).button('loading');
	$('#ref_update-'+reference_id).prop('disabled',true);
	$.post('references/update_modal.php', { reference_id : reference_id }, function(data){
		$('#div_modal').html(data);
		$('#ref_update-'+reference_id).button('reset');
		$('#ref_update-'+reference_id).prop('disabled',false);
	});
}
</script>
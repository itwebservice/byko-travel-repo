<?php
include "../../model/model.php";
/*======******Header******=======*/
require_once('../layouts/admin_header.php');
?>
<?= begin_panel('Settings','') ?>
<div id="top"></div>
<div class="row text-center mg_bt_20">
	<div class="col-md-12">
		<label for="rd_system" class="app_dual_button active">
	        <input type="radio" id="rd_system" name="rd_app" checked onchange="content_reflect()">
	        &nbsp;&nbsp;System settings
	    </label>
	    <label for="rd_tax" class="app_dual_button">
	        <input type="radio" id="rd_tax" name="rd_app" onchange="content_reflect()">
	        &nbsp;&nbsp;Tax settings
	    </label>
	</div>   
</div>

<div id="div_settings"></div>

<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>

<script>
function content_reflect()
{
	var base_url= $('#base_url').val();
	var id = $('input[name="rd_app"]:checked').attr('id');
	if(id=="rd_system"){
		$.post(base_url+'view/settings/system/index.php', {}, function(data){
			$('#div_settings').html(data);
		});
	}
	if(id=="rd_tax"){
		$.post(base_url+'view/settings/business_rules/index.php', {}, function(data){
			$('#div_settings').html(data);
		});
	}
}
content_reflect();
</script>
<?= end_panel() ?>
<?php
/*======******Footer******=======*/
require_once('../layouts/admin_footer.php'); 
?>
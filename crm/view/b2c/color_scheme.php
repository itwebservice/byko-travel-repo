<?php
include '../../model/model.php';
$query = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM `b2c_color_scheme` where 1"));
?>
<form id="section_color">
    <legend>Define Color Scheme</legend>
    <div class="row mg_bt_20">
        <div class="col-md-3">
            Text Primary Color
        </div>
        <div class="col-md-9">
            <a class="btn btn-info btn-sm ico_left" data-toggle="tooltip" data-placement="bottom" title="Setting"
                href="javascript:void(0)" id="pr_btn" onclick="color_scheme_save_modal('text_primary_color','pr_btn')"><i
                    class="fa fa-cog"></i><span class="">&nbsp;&nbsp;Change</span></a>
        </div>
    </div>
    <div class="row mg_bt_20">
        <div class="col-md-3">
            Text Secondary Color
        </div>
        <div class="col-md-9">
            <a class="btn btn-info btn-sm ico_left" data-toggle="tooltip" data-placement="bottom" title="Setting"
                href="javascript:void(0)" id="se_btn" onclick="color_scheme_save_modal('text_secondary_color','se_btn')"><i
                    class="fa fa-cog"></i><span class="">&nbsp;&nbsp;Change</span></a>
        </div>
    </div>
    <div class="row mg_bt_20">
        <div class="col-md-3">
            Button Color
        </div>
        <div class="col-md-9">
            <a class="btn btn-info btn-sm ico_left" data-toggle="tooltip" data-placement="bottom" title="Setting"
                href="javascript:void(0)" id="btn_clr" onclick="color_scheme_save_modal('button_color','btn_clr')"><i
                    class="fa fa-cog"></i><span class="">&nbsp;&nbsp;Change</span></a>
        </div>
    </div>
    <div id="color_modal"></div>
</form>
<script>
function color_scheme_save_modal(color_element,btn_id) {

	$('#'+btn_id).prop('disabled',true);
	$('#'+btn_id).button('loading');
    var base_url = $('#base_url').val();
    $.post('color_scheme_modal.php', {
        color_element: color_element
    }, function(data) {
        $('#color_modal').html(data);
        $('#'+btn_id).prop('disabled',false);
        $('#'+btn_id).button('reset');
    });
}
</script>
<?php
include "../../../model/model.php";
$sq_settings = mysqli_fetch_assoc(mysqlQuery("select * from app_settings"));
$sq_settings_g = mysqli_fetch_assoc(mysqlQuery("select * from generic_count_master"));

if($sq_settings['quot_format'] == 2){
	$quot_format = 'Landscape Standard';
}else if($sq_settings['quot_format'] == 3){
	$quot_format = 'Landscape Creative';
}else if($sq_settings['quot_format'] == 4){
	$quot_format = 'Portrait Creative';
}else if($sq_settings['quot_format'] == 5){
	$quot_format = 'Portrait Advanced';
}else if($sq_settings['quot_format'] == 6){
	$quot_format = 'Landscape Advanced';
}else{
	$quot_format = 'Portrait Standard';
}
?>

<form id="app_format_info" class="mg_tp_30">
	<div class="row mg_tp_30">
		<div class="col-md-6">
			<div class="panel panel-default panel-body app_panel_style feildset-panel ">
			<legend>Color Setting</legend>
				<div class="col-md-10 col-md-offset-2">
					<button class="btn btn-info btn-sm ico_left" data-toggle="tooltip" data-placement="bottom" title="Setting"  id="theme_color_scheme_save_modal_btn" onclick="theme_color_scheme_save_modal();btnDisableEnable(this.id)"><i class="fa fa-cog"></i><span class="">&nbsp;&nbsp;Change</span></button>
				</div>
				</div>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default panel-body app_panel_style feildset-panel ">
			<legend>Invoice Setting</legend>
				<div class="col-md-8 ">
					
					<select name="invoice_format_list" id="invoice_format_list" title="Invoice Format List">
					<?php if($sq_settings_g['invoice_format'] == '4') {?>
					<option value="4">Creative</option>
					<?php }else{ ?>
						<option value="<?= $sq_settings_g['invoice_format'] ?>"><?= $sq_settings_g['invoice_format'] ?></option>
					<?php } ?>
						<option value="Standard">Standard</option>
						<option value="Regular">Regular</option>
						<option value="Advance">Advance</option>
					</select>
					<!-- <small>Note : Bydefault Standard Format is used.</small> -->
				</div>
				<div class="col-md-4">
					<button type="button" data-toggle="tooltip" class="btn btn-excel" title="Bydefault Standard Format is used."><i class="fa fa-question-circle"></i></button>
					<button class="btn btn-info btn-sm ico_left" data-toggle="tooltip" data-placement="bottom" title="View"  id="display_modal_invoive_btn" onclick="display_modal_invoive();btnDisableEnable(this.id)"><i class="fa fa-eye"></i><span class="">&nbsp;&nbsp;View</span></button>
				</div>
			</div>
	</div>
	</div>
	<!-- Quotatio nformat settings -->
	<div class="row mg_tp_30">
		<div class="col-md-12 mg_tp_30">
			<div class="panel panel-default panel-body app_panel_style feildset-panel ">
			<legend>Quotation Format & Image Setting</legend>
				<div class="col-md-3">
					<select name="format_list" id="format_list" title="Quotation Format List" onchange="display_images(this.id);">
					<?php if($sq_settings['quot_format'] != 0){?>
						<option value="<?= $sq_settings['quot_format'] ?>"><?= $quot_format ?></option>
					<?php }
					else{ ?>
						<option value="4">Portrait Creative</option>
					<?php } ?>
						<option value="1">Portrait Standard</option>
						<option value="4">Portrait Creative</option>
						<option value="5">Portrait Advanced</option>
						<option value="2">Landscape Standard</option>
						<option value="3">Landscape Creative</option>
						<option value="6">Landscape Advanced</option>
					</select>
				</div>
				<div class="col-md-3">
				<select style="width:100%;" name="" id="destination_format_filter" title="Destination" onchange="display_images('format_list');">

					<?= get_destinations_option($sq_settings['format_dest_id']) ?>
				</select>
				<?php 
				if(!empty($sq_settings['format_dest_id']))
				{
					?>
					<script>
						$('#destination_format_filter').trigger('change');
					</script>
					<?php 
				}
				?>
				</div>
				<div class="col-md-6 no-pad">
						<div class="col-md-6 text-left">
							<button type="button" data-toggle="tooltip" class="btn btn-excel" title="Bydefault Portrait Standard Format is used."><i class="fa fa-question-circle"></i></button>
							<a class="btn btn-info btn-sm ico_left" data-toggle="tooltip" data-placement="bottom" title="View" href="javascript:void(0)" onclick="display_modal('format_list')"><i class="fa fa-eye"></i><span class="">&nbsp;&nbsp;View</span></a>
							<button type="button" class="btn btn-info btn-sm ico_left" data-toggle="tooltip" data-placement="bottom" title="Upload"  onclick="upload_modal()"><i class="fa fa-upload"></i><span class="">&nbsp;&nbsp;Upload</span></button>
						</div>
						<div class="col-md-6 text-right">
							<button class="btn btn-sm btn-success" id="format_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
						</div>
				</div>
				<div class="col-md-12">
					<div class="panel panel-default panel-body mg_tp_20 main_block">
						<div class="row"> 
							<div class="col-md-12 no-pad">
									<div id="div_list" class="loader_parent"></div>
									<div id="upload_modal_div"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</form>
<div id="invoice_format_image" class="main_block"></div>

<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
<script type="text/javascript">
	$('#destination_format_filter').select2();
	
function display_modal_invoive(){
	$('#display_modal_invoive_btn').button('loading');
	$('#display_modal_invoive_btn').prop('disabled',true);
	var base_url = $('#base_url').val();
	$.post(base_url+'view/app_settings/basic_info/view/index.php', {}, function(data){
        $('#invoice_format_image').html(data);
		$('#display_modal_invoive_btn').button('reset');
		$('#display_modal_invoive_btn').prop('disabled',false);
    });

}
function display_modal(format_list){

	var format = $('#'+format_list).val();
	var base_url = $('#base_url').val();

	if(format == 2){
		window.open('https://itourscloud.com/quotation_format_images/quot_pdf/Landscape-Standard-Pdf', '_blank'); return false;
	}
	else if(format == 3){
		window.open('https://itourscloud.com/quotation_format_images/quot_pdf/Landscape-Creative-Pdf', '_blank'); return false;
	}
	else if(format == 4){
		window.open('https://itourscloud.com/quotation_format_images/quot_pdf/Portrait-Creative-Pdf', '_blank'); return false;
	}
	else if(format == 5){
		window.open('https://itourscloud.com/quotation_format_images/quot_pdf/Portrait-Advanced-Pdf', '_blank'); return false;
	}
	else if(format == 6){
		window.open('https://itourscloud.com/quotation_format_images/quot_pdf/Landscape-Advanced-Pdf', '_blank'); return false;
	}
	else{
		window.open('https://itourscloud.com/quotation_format_images/quot_pdf/Portiat-Standard-Pdf', '_blank'); return false;
	}
}

function display_images(format_list){
var format = $('#'+format_list).val();
var destination = $('#destination_format_filter').val();
var base_url = $('#base_url').val();
	$.post(base_url+'view/app_settings/app_format/display_images.php', {format:format,destination:destination}, function(data){
        $('#div_list').html(data);
  });
}

function upload_modal(){

var base_url = $('#base_url').val();
	$.post(base_url+'view/app_settings/app_format/upload_img.php', {}, function(data){
        $('#upload_modal_div').html(data);
  });
}
display_images('format_list');

$(function(){
	$('#app_format_info').validate({
		rules:{
				app_version : {  },
				app_email_id : { email: true },
		},

		submitHandler:function(form){
		 	
		  var base_url = $('#base_url').val();
		 	var invoice_format_list = $('#invoice_format_list').val();
			var quot_format = $('#format_list').val();
			var dest_id = $('#destination_format_filter').val();
			var img_arr1 = (function() {  var a = ''; $("input[name='image_check']:checked").each(function() { a += this.value+','; });  return a; })();

			var gallary_arr1 = img_arr1.split(",");
            var length = gallary_arr1.length-1;
			if(length == 0 || length >1){
				error_msg_alert("Please select at least one image for Quotation cover!");
				return false;
			}
			var image = gallary_arr1[0];
			$('#format_save').button('loading');
			$('#vi_confirm_box').vi_confirm_box({
			    callback: function(data1){
			        if(data1=="yes"){
			          $.ajax({
			          	type:'post',
			          	url:base_url+'controller/app_settings/setting/app_format_info_save.php',
			          	data:{ invoice_format_list : invoice_format_list,quot_format : quot_format,image : image,dest_id:dest_id},
			          	success:function(result){
			          	// msg_popup_reload(result);
			          	success_msg_alert(result);
								  $('#format_save').button('reset');
			          	}
			          });
			        }
						else{
							$('#format_save').button('reset');
						}
						}
			});
			return false;
		}
	});
});
</script>
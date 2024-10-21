<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-3 col-sm-4 mg_bt_10_sm_xs">
			<input type="text" id="exp_date"  name="exp_date" class="form-control" placeholder="Expiry Date" title="Expiry Date">
		</div>
		
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
			<button class="btn btn-sm btn-info ico_right" onclick="report_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
		</div>
	</div>
</div>
	

<div id="div_list" class="main_block mg_tp_20">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="tariff_expiry_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<div id="tariff_expiry_display"></div>

<script>
$("#exp_date").datetimepicker({ timepicker:false, format:'d-m-Y' });        
function report_reflect(){
	
	var exp_date = $('#exp_date').val();
	var column = [
	{ title : "S_No"},
	{ title : "Supplier Type"},
	{ title : "Hotel/Transfer/Activity"},
	{ title : "Actions"} ];
	$.post('report_reflect/tariff_expiry/get_report.php', { exp_date : exp_date}, function(data){
		pagination_load(data, column, true, false, 20, 'tariff_expiry_report');
	});
}
report_reflect();


function view_details_modal(btn_id,tariff_data)
{
	$('#'+btn_id).prop('disabled',true);
	$('#'+btn_id).button('loading');
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/tariff_expiry/view_expire_tariff_modal.php', { btn_id : btn_id, tariff_data : tariff_data}, function(data){
		$('#tariff_expiry_display').html(data);
		$('#'+btn_id).button('reset');
		$('#'+btn_id).prop('disabled',false);
	});
}
</script>
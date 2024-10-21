
<div class="col-md-12 mg_bt_10 text-right">
	<button class="btn btn-excel btn-sm" onclick="exportToExcel('b2b_agent_report')" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
</div>
<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-3 col-sm-4 mg_bt_10_sm_xs">
			<input type="text" id="b2b_from_date" onchange="get_to_date(this.id,'b2b_to_date');" name="from_date" class="form-control" placeholder="*From Date" title="From Date">
		</div>
		<div class="col-md-3 col-sm-4 mg_bt_10_sm_xs">
			<input type="text" id="b2b_to_date" name="to_date" onchange="validate_validDate('b2b_from_date','b2b_to_date');" class="form-control" placeholder="*To Date" title="To Date">
		</div>	
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
			<button class="btn btn-sm btn-info ico_right" onclick="report_reflect(true)">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
		</div>
		
	</div>
</div>

<div id="div_list" class="main_block mg_tp_20">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="b2b_agent_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<div id="other_booking_display">

</div>
<script>
$( "#b2b_from_date, #b2b_to_date" ).datetimepicker({ timepicker:false, format:'d-m-Y' });        

var column = [
	{ title : "SR_No."},
	{ title : "Company Name"},
	{ title : "Contact Person"},
	{ title : "Total Quotations"},
	{ title : "Total Sales"},
	{ title : "Actions",class : "no-export"},
];
function report_reflect(data){
		
	var fromdate = $('#b2b_from_date').val();
	var todate = $('#b2b_to_date').val();
	$.post('report_reflect/b2b_agent/get_report.php', { fromdate : fromdate ,todate : todate }, function(data){
		pagination_load(data, column, true, false, 20, 'b2b_agent_report');
	});
}
report_reflect(false);

function view_data_modal(customer_id)
{
	$('#view_data_btn-'+customer_id).button('loading');
	var fromdate = $('#b2b_from_date').val();
	var todate = $('#b2b_to_date').val();
	var base_url = $('#base_url').val();
	$.post('report_reflect/b2b_agent/view_enquiry_modal.php', { customer_id : customer_id, fromdate : fromdate ,todate : todate}, function(data){
		$('#other_booking_display').html(data);
		$('#view_data_btn-'+customer_id).button('reset');
	});
}
</script>
<?php
include "../../../../../model/model.php";
?>
<div class="col-md-12 text-right mg_bt_10">
		<button class="btn btn-excel btn-sm" onclick="excel_report1()" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
	</div>

<div class="app_panel_content Filter-panel">
		<div class="row">
			<div class="col-md-3">
				<input class="form-control" type="text" name="from_date_filter" id="from_date_filter" placeholder="From Date" title="From Date" onchange="get_to_date(this.id,'to_date_filter')">
			</div>
			<div class="col-md-3">
				<input class="form-control" type="text" name="to_date_filter" id="to_date_filter" placeholder="To Date" title="To Date" onchange="validate_validDate('from_date_filter','to_date_filter')">
			</div>	
			<div class="col-md-3">
				<button class="btn btn-info ico_right" onclick="report_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
			</div>
		</div>
	</div>
	

<div id="div_list" class="main_block">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="deleted_entries" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<div id="other_hotel_display">

</div>
<script>
$( "#from_date, #to_date" ).datetimepicker({ timepicker:false, format:'d-m-Y' });
$('#from_date_filter, #to_date_filter').datetimepicker({ timepicker:false, format:'d-m-Y' });

function report_reflect(){
	
	var column = [
		{ title : "Sr.No"},
		{ title : "Transaction_Date/Time"},
		{ title : "Transaction_Type"},
		{ title : "Service_name"},
		{ title : "Transaction_ID"},
		{ title : "Name(Customer/Supplier/Bank/Ledger)"},
		{ title : "Amount"},
	];
	var from_date = $('#from_date_filter').val();
	var to_date = $('#to_date_filter').val();
	$.post('report_reflect/deleted_entries/get_report.php', { from_date : from_date, to_date : to_date }, function(data){
		pagination_load(data, column, true, false, 20, 'deleted_entries');
	});
}
report_reflect();


function excel_report1(){
	var from_date = $('#from_date_filter').val();
	var to_date = $('#to_date_filter').val();
	window.location = 'report_reflect/deleted_entries/export_excel.php?from_date='+from_date+'&to_date='+to_date;
}
</script>


<div class="col-md-12 text-right">
	<button class="btn btn-excel btn-sm" onclick="exportToExcel('missing_followup_report')" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
</div>

<div id="div_list" class="main_block mg_tp_20">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="missing_followup_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<div id="other_enquiry_display">

</div>
<script>
$( "#from_date, #to_date" ).datetimepicker({ timepicker:false, format:'d-m-Y' });        

var column = [
	{ title : "SR_No."},
	{ title : "Enquiry_No."},
	{ title : "Enquiry_Date"},
	{ title : "Customer Name"},
	{ title : "Tour Type"},
	{ title : "Followup_Date/Time"},
	{ title : "Allocate_to"},
	{ title : "Actions",class : "no-export"},
];
function report_reflect(data){
		$.post('report_reflect/missing_followups/get_report.php', {}, function(data){
		pagination_load(data, column, true, false, 20, 'missing_followup_report');
	});
}
report_reflect(false);

function view_followup_modal(enquiry_id)
{
	var base_url = $('#base_url').val();
	$.post('report_reflect/missing_followups/view_enquiry_modal.php', { enquiry_id : enquiry_id}, function(data){
		$('#other_enquiry_display').html(data);
	});
}
</script>
<?php
include "../../../model/model.php";
$selectedDate = !empty($_POST['date']) ? get_date_db($_POST['date']) : null;
?>
<input type="hidden" id="selectedDate" value="<?= $selectedDate ?>"/>
<div id="div_list" class="main_block mg_tp_20">
	<div class="dashboard_table dashboard_table_panel main_block">
	<div class="row text-left mg_tp_10">
		<div class="col-md-12">
			<div class="col-md-12 no-pad table_verflow"> 
				<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">
					<table class="table table-hover" style="margin: 20px 0 !important;width: 100%;" id="itinerary_report">    
					</table>
				</div></div></div>
			</div>
	</div></div></div>
	<div id="other_des_wise_display">
</div>

<script>
function report_reflect(){
	
	var fromdate = $('#selectedDate').val();
	var column = [
	{ title : "S_No"},
	{ title : "Booking Id"},
	{ title : "Customer Name"},
	{ title : "Special Attraction"},
	{ title : "Day Wise Program"},
	{ title : "Overnight Stay"},
	{ title : "Meal Plan"}];
	$.post('itinerary/list_reflect.php', { date : fromdate}, function(data){
		pagination_load(data, column, true, true, 20, 'itinerary_report');
	});
}
report_reflect();
</script>
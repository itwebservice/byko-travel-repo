<?php
include "../../../../../model/model.php";
$branch_admin_id = $_SESSION['branch_admin_id'];
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$q = "select * from branch_assign where link='package_booking/booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >
<div class="app_panel_content Filter-panel mg_bt_10">
	<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
		<select id="booking_id_filter3" name="booking_id_filter" style="width:100%" title="Booking ID" class="form-control" onchange="ticket_reflect()"> 
			<?php get_package_booking_dropdown($role, $branch_admin_id, $branch_status,$emp_id); ?>
		</select>
	</div>
</div>
<div id="div_list" class="main_block mg_tp_20">
<div class="row"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="gtc_tour_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
	$('#booking_id_filter3').select2();
</script>
<script>
	var column = [
	{ title: "S_No." },
	{ title: "Tour_name" },
	{ title: "Tour_date" },
	{ title: "booking_id" },
	{ title: "train_ticket"},
	{ title: "flight_ticket"},
	{ title: "cruise_ticket"}
];
	function ticket_reflect(){
		var booking_id = $('#booking_id_filter3').val();
		var branch_status = $('#branch_status').val();
		$.post('reports_content/package_tour/booking_tickets/booking_ticket_report_filter.php', {booking_id : booking_id,branch_status:branch_status}, function(data){
			pagination_load(data, column, true, false, 20, 'gtc_tour_report',true);
	});
	}
	ticket_reflect();
</script>
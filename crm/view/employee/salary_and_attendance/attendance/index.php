<?php
include "../../../../model/model.php";
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$q = "select branch_status from branch_assign where link='employee/salary_and_attendance/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >

<div class="app_panel_content Filter-panel">
	
	<div class="row text-center">
		<div class="col-md-2 col-sm-6 mg_bt_10_xs col-md-offset-4">
			<input type="text" id="attendence_date" name="attendence_date" placeholder="*Select Date" onchange="emp_attendance_reflect()" title="Select Date" value="<?= date('d-m-Y') ?>" >
		</div>
	</div>
</div>

<div id="div_reflect" class="main_block"></div>

<script>
$('#attendence_date').datetimepicker({ timepicker:false, format:'d-m-Y', maxDate:'d-m-Y' });

emp_attendance_reflect();
function emp_attendance_reflect(){
	var attendence_date = $('#attendence_date').val();
	var branch_status = $('#branch_status').val();
		if(attendence_date==""){
			error_msg_alert("Please select a Date!!!");
		}
		var today = new Date();
		today = today.getTime();

		var from_parts = attendence_date.split(' ');
		var parts = from_parts[0].split('-');
		var date = new Date();
		var new_month = parseInt(parts[1]) - 1;
		date.setFullYear(parts[2]);
		date.setDate(parts[0]);
		date.setMonth(new_month);
		var from_date_ms = date.getTime();

		if (today != from_date_ms && today < from_date_ms) {
			error_msg_alert('Future date is not allowed.');
			return false;
		}
		$.post('attendance/employee_attendance_reflect.php',{ attendence_date : attendence_date, branch_status: branch_status },function(data){
			$('#div_reflect').html(data);
		});
		 
}

</script>
 
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
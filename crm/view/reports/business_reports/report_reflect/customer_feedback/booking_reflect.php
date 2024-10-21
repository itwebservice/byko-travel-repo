<?php
include_once('../../../../../model/model.php');
$booking_type = $_POST['booking_type']; ?>
<option value="">Booking ID</option>
<?php
if($booking_type=="Group Booking"){ 
$query = "select * from tourwise_traveler_details where delete_status='0' and tour_group_status!='Cancel'";  

	$sq_booking = mysqlQuery($query); 
	while($row_booking = mysqli_fetch_assoc($sq_booking)){

		$pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_booking[traveler_group_id]'"));
		$cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and status='Cancel'"));
		if($pass_count!=$cancelpass_count){
			$sq_tour_group_name = mysqlQuery("select from_date,to_date from tour_groups where group_id='$row_booking[tour_group_id]'");
			$row_tour_group_name = mysqli_fetch_assoc($sq_tour_group_name);
			$tour_group_from = date("d-m-Y", strtotime($row_tour_group_name['from_date']));
			$tour_group_to = date("d-m-Y", strtotime($row_tour_group_name['to_date']));
			$date = $row_booking['form_date'];
			$yr = explode("-", $date);
			$year =$yr[0];
			$booking_id = get_group_booking_id($row_booking['id'],$year);
			?>
			<option value="<?php echo $row_booking['id']; ?>"><?php echo $booking_id.' ('.$tour_group_from." To ".$tour_group_to.')' ?></option>;
			<?php
		}
	} 
}
elseif($booking_type=="Package Booking"){

	$query = "select * from package_tour_booking_master where delete_status='0' ";
	$sq_booking = mysqlQuery($query); 
	while($row_booking = mysqli_fetch_assoc($sq_booking)){
			
		$pass_count= mysqli_num_rows(mysqlQuery("select booking_id from package_travelers_details where booking_id='$row_booking[booking_id]'"));
		$cancle_count= mysqli_num_rows(mysqlQuery("select booking_id from package_travelers_details where booking_id='$row_booking[booking_id]' and status='Cancel'"));

		if($pass_count!=$cancle_count){
			$date = $row_booking['booking_date'];
			$yr = explode("-", $date);
			$year = $yr[0];        
			$booking_id = get_package_booking_id($row_booking['booking_id'],$year);
			?>
			<option value="<?php echo $row_booking['booking_id']; ?>"><?php echo $booking_id.' ('.date('d-m-Y', strtotime($row_booking['tour_from_date'])).' To '. date('d-m-Y', strtotime($row_booking['tour_to_date'])).')' ?></option>; 
			<?php
		}
	} 
} ?>
<script>
$('#booking_id').select2();
</script>
<?php include "../../../model/model.php"; ?>
<?php include_once "../../../classes/tour_booked_seats.php"; ?>
<?php
	
	$tour_id = $_GET['tour_id'];
	$tour_group_id = $_GET['tour_group_id'];
	$total_seats = 0;
	$sq = mysqlQuery("select capacity from tour_groups where tour_id='$tour_id' and group_id='$tour_group_id' ");
	if($row = mysqli_fetch_assoc($sq))
	{
		$total_seats = $row['capacity'];
	}	
	
	$seats_booked = $bk_seats->booked_seats($tour_id, $tour_group_id);
	$b2c_seats_booked = $bk_seats->b2c_booked_seats($tour_id, $tour_group_id);
	$b2b_seats_booked = $bk_seats->b2b_booked_seats($tour_id, $tour_group_id);
	$total_seats = ($total_seats > 0) ? $total_seats : 0;
	$b2c_seats_booked = ($b2c_seats_booked > 0) ? $b2c_seats_booked : 0;
	$b2b_seats_booked = ($b2b_seats_booked > 0) ? $b2b_seats_booked : 0;

	$available_seats = $total_seats - $seats_booked - $b2c_seats_booked - $b2b_seats_booked;
?>


<div class="danger_bg seat_availability main_block_xs text_center_sm_xs mg_bt_10_sm_xs ">
	Total Seats : <?php echo $total_seats ?>	
</div>	
<div class="info_bg seat_availability main_block_xs text_center_sm_xs">
	Available Seats : <?php echo $available_seats ?>
</div>

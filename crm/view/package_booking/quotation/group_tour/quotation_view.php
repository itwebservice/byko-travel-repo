<?php
include "../../../../model/model.php";
/*======******Header******=======*/
include_once('../../../layouts/fullwidth_app_header.php'); 

$quotation_id = $_GET['quotation_id'];
$role = $_SESSION['role'];

$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_quotation_master where quotation_id='$quotation_id'"));

$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where id='$sq_quotation[login_id]'"));
$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_login[emp_id]'"));

if($sq_emp_info['first_name']==''){
	$emp_name = 'Admin';
}
else{
	$emp_name = $sq_emp_info['first_name'].' '.$sq_emp_info['last_name'];
}

$quotation_date = $sq_quotation['quotation_date'];
$yr = explode("-", $quotation_date);
$year =$yr[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Online Booking</title>	

	<?php admin_header_scripts(); ?>

</head>
<input type="hidden" id="base_url" name="base_url" value="<?= BASE_URL ?>">

<?= begin_panel('Quotation View') ?>
<div class="container">


<div class="main_block mg_tp_30"></div>
<h3 class="editor_title">Enquiry Details</h3>
<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Destination Name</label> : <?= $sq_quotation['tour_name'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>From Date</label> : <?= date('d/m/Y', strtotime($sq_quotation['from_date'])) ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>To Date</label> : <?= date('d/m/Y', strtotime($sq_quotation['to_date'])) ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Total Days</label> : <?= $sq_quotation['total_days'] ?> </div>
	</div>
	<div class="row">
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Customer Name</label> : <?= $sq_quotation['customer_name'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Mobile Number</label> : <?= $sq_quotation['mobile_number'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Adults</label> : <?= $sq_quotation['total_adult'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Child Without Bed</label> : <?= $sq_quotation['children_without_bed'] ?> </div>
	</div>
	<div class="row">
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Child With Bed</label> : <?= $sq_quotation['children_with_bed'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Infants</label> : <?= $sq_quotation['total_infant'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Single Person</label> : <?= $sq_quotation['single_person'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Quotation Date</label> : <?= date('d/m/Y', strtotime($sq_quotation['quotation_date'])) ?> </div>
	</div>
	<div class="row">
		<div class="col-md-3 mg_bt_10_xs" style="border-right: 1px solid #ddd;"> <div class="highlighted_cost"><label>Quotation Cost</label> : <?= $sq_quotation['quotation_cost'] ?> </div></div>
		<div class="col-md-3" style="border-right: 1px solid #ddd;"> <div class="highlighted_cost"><label>Quotation ID</label> : <?= get_quotation_id($quotation_id,$year) ?> </div></div>
		<div class="col-md-3" style="border-right: 1px solid #ddd;"> <div class="highlighted_cost"><label>Created By</label> : <?= $emp_name ?> </div></div>
	</div>
</div>
<?php 
$sq_t_count = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_quotation_train_entries where quotation_id='$quotation_id'"));
if($sq_t_count != '0'){
?>
<div class="main_block mg_tp_30"></div>
<h3 class="editor_title main_block">Train Details</h3>
<table class="table table-bordered">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Location_From</th>
			<th>Location_To</th>
			<th>Class</th>
			<th>Departure_Date_Time</th>
			<th>Arrival_Date_Time</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		$sq_train = mysqlQuery("select * from group_tour_quotation_train_entries where quotation_id='$quotation_id'");
		while($row_train = mysqli_fetch_assoc($sq_train))
		{
			?>
			<tr>
				<td><?= ++$count ?></td>
				<td><?= $row_train['from_location'] ?></td>
				<td><?= $row_train['to_location'] ?></td>
				<td><?= $row_train['class'] ?></td>
				<td><?= get_datetime_user($row_train['departure_date']) ?></td>
				<td><?= get_datetime_user($row_train['arrival_date']) ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php } ?>

<?php 
$sq_h_count = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_hotel_entries where tour_id='$sq_quotation[tour_group_id]'"));
if($sq_h_count != '0'){
?>
<div class="main_block mg_tp_30"></div>
<h3 class="editor_title main_block">Hotel Details</h3>
<table class="table table-bordered">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>City Name</th>
			<th>Hotel Name</th>
			<th>Hotel Category</th>
			<th>Total Nights</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		$sq_hotel = mysqlQuery("select * from group_tour_hotel_entries where tour_id='$sq_quotation[tour_group_id]'");
		while($row_hotel = mysqli_fetch_assoc($sq_hotel))
		{
			?>
			<tr>
				<td><?= ++$count ?></td>
				<td><?php
				$city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id = ".$row_hotel['city_id']));
				echo $city['city_name'] ?></td>
				<td><?php
				$hotel = mysqli_fetch_assoc(mysqlQuery("select hotel_name from hotel_master where hotel_id = ".$row_hotel['hotel_id']));
				echo $hotel['hotel_name'] ?></td>
				<td><?= $row_hotel['hotel_type'] ?></td>
				<td><?= $row_hotel['total_nights'] ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php } ?>





<?php 
$sq_f_count = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_quotation_plane_entries where quotation_id='$quotation_id'"));
if($sq_f_count != '0'){
?>
<div class="main_block mg_tp_30"></div>
<h3 class="editor_title main_block">Flight Details</h3>
<table class="table table-bordered">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Sector_From</th>
			<th>Sector_To</th>
			<th>Airline</th>
			<th>Class</th>
			<th>Departure_Date_Time</th>
			<th>Arrival_Date_Time</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		$sq_train = mysqlQuery("select * from group_tour_quotation_plane_entries where quotation_id='$quotation_id'");
		while($row_train = mysqli_fetch_assoc($sq_train))
		{
			$sq_airline = mysqli_fetch_assoc(mysqlQuery("select * from airline_master where airline_id='$row_train[airline_name]'"));
			?>
			<tr>
				<td><?= ++$count ?></td>
				<td><?= $row_train['from_location'] ?></td>
				<td><?= $row_train['to_location'] ?></td>
				<td><?= $sq_airline['airline_name'].' ('.$sq_airline['airline_code'].')' ?></td>
				<td><?= $row_train['class'] ?></td>
				<td><?= get_datetime_user($row_train['dapart_time']) ?></td>
				<td><?= get_datetime_user($row_train['arraval_time']) ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php } ?>


<?php 
$sq_c_count = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_quotation_cruise_entries where quotation_id='$quotation_id'"));
if($sq_c_count != '0'){
?>
<div class="main_block mg_tp_30"></div>
<h3 class="editor_title main_block">Cruise Details</h3>
<table class="table table-bordered">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Departure_Date_Time</th>
			<th>Arrival_Date_Time</th>
			<th>Route</th>
			<th>Cabin</th>
			<th>Sharing</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		$sq_cruise = mysqlQuery("select * from group_tour_quotation_cruise_entries where quotation_id='$quotation_id'");
		while($row_cruise = mysqli_fetch_assoc($sq_cruise))
		{
			?>
			<tr>
				<td><?= ++$count ?></td>
				<td><?= get_datetime_user($row_cruise['dept_datetime']) ?></td>
				<td><?= get_datetime_user($row_cruise['arrival_datetime']) ?></td>
				<td><?= $row_cruise['route'] ?></td>
				<td><?= $row_cruise['cabin'] ?></td>
				<td><?= $row_cruise['sharing'] ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php } ?>

<div class="main_block mg_tp_30"></div>
<h3 class="editor_title main_block">Itinerary Details</h3>
<table class="table table-bordered no-marg">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th style="min-width:200px ;">Date</th>
			<th>Special_Attraction</th>
			<th>Day-wise_Program</th>
			<th>Overnight_Stay</th>
			<th>Meal_Plan</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		$i = 0;
        $dates = (array) get_dates_for_tour_itineary($quotation_id); 
		$sq_package_program = mysqlQuery("select * from group_tour_program where tour_id ='$sq_quotation[tour_group_id]'");
		while($row_itinarary = mysqli_fetch_assoc($sq_package_program)){
			
			$date_format = isset($dates[$i]) ? $dates[$i] : 'NA';;
			$i++;
			?>
			<tr>
				<td><?= ++$count ?></td>
				<td><?php echo $date_format; ?></td>
				<td><?= $row_itinarary['attraction'] ?></td>
				<td><pre class="real_text"><?= $row_itinarary['day_wise_program'] ?></pre></td>
				<td><?= $row_itinarary['stay'] ?></td>
				<td><?= ($row_itinarary['meal_plan']!='')?$row_itinarary['meal_plan']:'NA' ?></td>
			</tr>
			<?php
		}	
		?>
	</tbody>
</table>

<div class="main_block mg_tp_30"></div>
<h3 class="editor_title main_block">Costing Details</h3>
<table class="table table-bordered">
	<thead>
		<tr class="table-heading-row">
			<th>Adult</th>
			<th>Child With Bed</th>
			<th>Child Without Bed</th>
			<th>Infant</th>
			<th>Single Person</th>
			<th>Total_Tour </th>
			<th>Service_Charge</th>
			<th>Tax</th>
			<th>Quotation_cost</th>
		</tr>
	</thead>
	
	<tbody>
			<tr>
				<td><?= $sq_quotation['adult_cost'] ?></td>
				<td><?= $sq_quotation['with_bed_cost'] ?></td>
				<td><?= $sq_quotation['children_cost'] ?></td>
				<td><?= $sq_quotation['infant_cost'] ?></td>
				<td><?= $sq_quotation['single_person_cost'] ?></td>
				<td><?= $sq_quotation['tour_cost'] ?></td>
				<td><?= $sq_quotation['service_charge'] ?></td>
				<td><?= $sq_quotation['service_tax_subtotal'] ?></td>
				<td><?= $sq_quotation['quotation_cost'] ?></td>
			</tr>
	</tbody>
</table>

<div class="row mg_bt_10">
	<div class="col-md-12 mg_tp_30">
		<h3 class="editor_title main_block">Inclusions</h3>
		<div class="panel panel-default panel-body app_panel_style main_block">
			<?= $sq_quotation['incl'] ?>
		</div>
	</div>
</div>

<div class="row mg_bt_10">
	<div class="col-md-12 mg_tp_30">
		<h3 class="editor_title main_block">Exclusions</h3>
		<div class="panel panel-default panel-body app_panel_style main_block">
			<?= $sq_quotation['excl'] ?>
		</div>
	</div>
</div>

<?php 
$sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Group Quotation' and active_flag ='Active'"));
if (isset($sq_terms_cond['terms_and_conditions'])) { ?>
<div class="row mg_bt_10">
	<div class="col-md-12 mg_tp_30">
		<h3 class="editor_title main_block">Terms & Conditions</h3>
		<div class="panel panel-default panel-body app_panel_style main_block">
			<?= $sq_terms_cond['terms_and_conditions'] ?>
		</div>
	</div>
</div>
<?php } ?>
</div>
<?= end_panel() ?>

<?php
/*======******Footer******=======*/
include_once('../../../layouts/fullwidth_app_footer.php');
?>
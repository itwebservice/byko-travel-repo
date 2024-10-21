<?php
include "../../model/model.php";
/*======******Header******=======*/
require_once('../layouts/fullwidth_app_header.php');

$quotation_id = $_GET['quotation_id'];
$role = $_SESSION['role'];
$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from hotel_quotation_master where quotation_id='$quotation_id'"));

$enquiryDetails = json_decode($sq_quotation['enquiry_details'], true);
$hotelDetails = json_decode($sq_quotation['hotel_details'], true);
$costDetails = json_decode($sq_quotation['costing_details'], true);

$quotation_date = $sq_quotation['quotation_date'];
$yr = explode("-", $quotation_date);
$year =$yr[0];

$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where id='$sq_quotation[login_id]'"));
$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_login[emp_id]'"));

if($sq_emp_info['first_name']==''){
	$emp_name = 'Admin';
}
else{
	$emp_name = $sq_emp_info['first_name'].' '.$sq_emp_info['last_name'];
}
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
<h3 class="editor_title main_block">Enquiry Details</h3>
<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Customer Name</label> : <?= $enquiryDetails['customer_name'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Email Id</label> : <?= $enquiryDetails['email_id'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>WhatsApp Number</label> : <?= $enquiryDetails['country_code'].$enquiryDetails['whatsapp_no'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Total Adult(s)</label> : <?= $enquiryDetails['total_adult'] ?> </div>
	</div>
    <div class="row">
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Child Without Bed</label> : <?= $enquiryDetails['children_without_bed'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Child With Bed</label> : <?= $enquiryDetails['children_with_bed'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Total Infant(s)</label> : <?= $enquiryDetails['total_infant'] ?> </div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Total Guest(s)</label> : <?= $enquiryDetails['total_members'] ?> </div>
	</div>
    <div class="row">
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label>Quotation Date</label> : <?= date('d/m/Y', strtotime($sq_quotation['quotation_date'])) ?> </div>
		<div class="col-md-3" style="border-right: 1px solid #ddd;"> <div class="highlighted_cost"><label>Created By</label> : <?= $emp_name ?> </div></div>
		<div class="col-md-3 mg_bt_10" style="border-right: 1px solid #ddd;"> <label class="highlighted_cost">Quotation ID : <?= get_quotation_id($sq_quotation['quotation_id'],$year) ?> </label></div>
	</div>
	<div class="row mg_tp_20">
		<div class="col-md-12 mg_bt_10"><legend>Hotel Requirements :</legend> <?= $sq_quotation['hotel_req'] ?></div>
	</div>
	
</div>

<div class="main_block mg_tp_30"></div>
<div class="row mg_tp_30">
<div class="col-md-12">
<h3 class="editor_title">Hotel Details</h3>
	<div class="table-responsive">
	<table class="table table-hover table-bordered no-marg">

	<tbody>
		<?php 
		foreach($hotelDetails as $values){
			?>
			<thead>
			<tr class="table-heading-row">
				<td colspan="12"><?= 'Option-'.$values['option'] ?></td>
			</tr>
			<tr class="table-heading-row">
				<th>S_No.</th>
				<th>Tour_Type</th>
				<th>City_Name</th>
				<th>Hotel_Name</th>
				<th>R_Category</th>
				<th>Meal_plan</th>
				<th>Check_In</th>
				<th>Check_Out</th>
				<th>Category</th>
				<th>NIGHTS</th>
				<th>Rooms</th>
				<th>Extra_Bed</th>
			</tr>
			</thead>
			<?php
			$data = $values['data'];
			for($i = 0;$i<sizeof($data);$i++){
				$cityName = mysqli_fetch_assoc(mysqlQuery("SELECT `city_name` FROM `city_master` WHERE `city_id`=".$data[$i]['city_id']));
				$hotelName = mysqli_fetch_assoc(mysqlQuery("SELECT `hotel_name` FROM `hotel_master` WHERE `hotel_id`=".$data[$i]['hotel_id']));
				?>
				<tr>
					<td><?= $i+1 ?></td>
					<td><?= $data[$i]['tour_type'] ?></td>
					<td><?= $cityName['city_name'] ?></td>
					<td><?= $hotelName['hotel_name'] ?></pre></td>
					<td><?= $data[$i]['hotel_cat'] ?></td>
					<td><?= $data[$i]['meal_plan'] ?></td>
					<td><?= $data[$i]['checkin'] ?></td>
					<td><?= $data[$i]['checkout'] ?></td>
					<td><?= $data[$i]['hotel_type'] ?></td>
					<td><?= $data[$i]['hotel_stay_days'] ?></td>
					<td><?= $data[$i]['total_rooms'] ?></td>
					<td><?= $data[$i]['extra_bed'] ?></td>
				</tr>
				<?php
			}
		}	
		?>
	</tbody>
</table>
	</div>
	</div>
	</div>


	<div class="main_block mg_tp_30"></div>
<div class="row mg_tp_30">
<div class="col-md-12">
<h3 class="editor_title">Costing Details</h3>
	<div class="table-responsive">
	<table class="table table-hover table-bordered no-marg">
		<?php
		foreach($costDetails as $values){
			$data = $values['costing']; ?>
			<thead>
			<tr class="table-heading-row">
				<td colspan="6"><?= 'Option-'.$values['option'] ?></td>
			</tr>
			<tr class="table-heading-row">
				<th>Basic_Cost</th>
				<th>Service_Charge</th>
				<th>Tax_Amount</th>
				<th>Markup_Amount</th>
				<th>Markup_Tax</th>
				<th>Quotation_Cost</th>
			</tr>
			</thead>
			<tbody>
				<tr>
					<td><?= number_format(floatval($data['hotel_cost']),2) ?></td>
					<td><?= number_format(floatval($data['service_charge']),2) ?></pre></td>
					<td><?= ($data['tax_amount'] == '') ? 0.00 : $data['tax_amount']  ?></td>
					<td><?= number_format(floatval($data['markup_cost']),2) ?></td>
					<td><?= ($data['markup_tax'] == '') ? 0.00 : $data['markup_tax'] ?></td>
					<td><?= number_format(floatval($data['total_amount']),2) ?></td>
				</tr>
			</tbody>
			<?php
		} ?>
	</table>
	</div>
</div>
</div>



</div>
<?= end_panel() ?>

<?php
/*======******Footer******=======*/
include_once('../layouts/fullwidth_app_footer.php');
?>
<?php
include "../../../../../../model/model.php";
$tour_id = $_POST['tour_id'];
$tour_group_id = $_POST['tour_group_id'];

$total_sale = 0;
$total_purchase = 0;
$array_s = array();
$temp_arr = array();
//Sale
$q1 = mysqlQuery("select * from tourwise_traveler_details where tour_id='$tour_id' and tour_group_id ='$tour_group_id' and delete_status='0' ");
while($tourwise_details = mysqli_fetch_assoc($q1)){

	$pass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from  travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]'"));
	$cancelpass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]' and status='Cancel'"));

	if($pass_count != $cancelpass_count){

		$sale_amount = $tourwise_details['net_total'];
		$service_tax_amount = 0;
		if($tourwise_details['service_tax'] !== 0.00 && ($tourwise_details['service_tax']) !== ''){
			$service_tax_subtotal1 = explode(',',$tourwise_details['service_tax']);
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = explode(':',$service_tax_subtotal1[$i]);
				$service_tax_amount +=  $service_tax[2];
			}
		}
		$sale_amount -= $service_tax_amount;
		$total_sale += $sale_amount;
	}
}

// Purchase
$sq_purchase = mysqlQuery("select * from vendor_estimate where estimate_type='Group Tour' and estimate_type_id ='$tour_group_id' and status!='Cancel' and delete_status='0'");
while($row_purchase = mysqli_fetch_assoc($sq_purchase)){
	//Service Tax 
	$service_tax_amount = 0;
	if($row_purchase['service_tax_subtotal'] !== 0.00 && ($row_purchase['service_tax_subtotal']) !== ''){
		$service_tax_subtotal1 = explode(',',$row_purchase['service_tax_subtotal']);
		for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
			$service_tax = explode(':',$service_tax_subtotal1[$i]);
			$service_tax_amount +=  $service_tax[2];
		}
	}
	if($row_purchase['purchase_return'] == 0){
		$total_purchase += $row_purchase['net_total'];
	}
	else if($row_purchase['purchase_return'] == 2){
		$cancel_estimate = json_decode($row_purchase['cancel_estimate']);
		$p_purchase = ($row_purchase['net_total'] - floatval($cancel_estimate[0]->net_total) - floatval($cancel_estimate[0]->service_tax_subtotal));
		$total_purchase += $p_purchase;
	}
	$total_purchase -= $service_tax_amount;
}

//Other Expense
$sq_other_purchase = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as amount_total from group_tour_estimate_expense where tour_id='$tour_id' and tour_group_id ='$tour_group_id'"));
$total_purchase += $sq_other_purchase['amount_total'];

//Revenue & Expenses
$result = $total_sale - $total_purchase;

if($total_sale > $total_purchase){
	$var = 'Total Profit';
}else{
	$var = 'Total Loss';
}
$profit_loss = $total_sale - $total_purchase;

$count = 1;
$q1 = mysqlQuery("select * from tourwise_traveler_details where tour_id='$tour_id' and tour_group_id ='$tour_group_id' and delete_status='0' ");
while($tourwise_details = mysqli_fetch_assoc($q1)){

	$pass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]'"));
	$cancelpass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]' and status='Cancel'"));
	$sq_emp = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from emp_master where emp_id='$tourwise_details[emp_id]'"));
	$emp_name = $sq_emp['first_name'].' '.$sq_emp['last_name'];
	if($pass_count != $cancelpass_count && $tourwise_details['tour_group_status'] != 'Cancel'){
		$bg = '';
	}else{
		$bg = 'danger';
	}
	$sale_amount = $tourwise_details['net_total'];
	$service_tax_amount = 0;
	if($tourwise_details['service_tax'] !== 0.00 && ($tourwise_details['service_tax']) !== ''){
		$service_tax_subtotal1 = explode(',',$tourwise_details['service_tax']);
		for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
			$service_tax = explode(':',$service_tax_subtotal1[$i]);
			$service_tax_amount +=  $service_tax[2];
		}
	}
	$sale_amount -= $service_tax_amount;

	$date = $tourwise_details['form_date'];
	$yr = explode("-", $date);
	$year =$yr[0];

	$temp_arr = array( "data" => array(

		(int)($count++),
		get_group_booking_id($tourwise_details['id'],$year),
		get_date_user($tourwise_details['form_date']),
		number_format($sale_amount,2),
		'<button class="btn btn-info btn-sm" onclick="view_purchase_modal('. $tour_id .','.$tour_group_id.','.$tourwise_details['id'].')" data-toggle="tooltip" title="View Details" id="supplierv_btn-'.$tourwise_details['id'].$tour_id.$tour_group_id.'"><i class="fa fa-eye"></i></button>',
		'<button class="btn btn-info btn-sm" id="suppliere_btn-'.$tourwise_details['id']. $tour_id.$tour_group_id.'" onclick="other_expnse_modal('. $tour_id .','.$tour_group_id .','.$tourwise_details['id'].')" data-toggle="tooltip" title="Add Other expense amount"><i class="fa fa-plus"></i></button>',
		$emp_name
	), "bg" =>$bg);
	array_push($array_s,$temp_arr);
}
echo json_encode($array_s);
?>
	
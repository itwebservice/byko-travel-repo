<?php 
include "../../../../model/model.php";
$customer_id = $_POST['customer_id'];
$booking_id = $_POST['booking_id'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$array_s = array();
$temp_arr = array();
$query = "select * from b2c_sale where 1 ";
if($customer_id!=""){
	$query .= " and customer_id='$customer_id'";
}
if($booking_id!=""){
	$query .= " and booking_id='$booking_id'";
}
if($from_date!="" && $to_date!=""){
	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));
	$query .= " and created_at between '$from_date' and '$to_date'";
}
// $query .= " order by booking_id desc";
$count = 0;
$total_balance=0;
$total_refund=0;		
$cancel_total =0;
$sale_total = 0;
$paid_total = 0;
$balance_total = 0;
$f_net_total = 0;
$f_cancel_amount = 0;
$f_total_cost = 0;
$f_paid_amount = 0;
$f_balance_amount = 0;
$sq_exc = mysqlQuery($query);
while($row_booking = mysqli_fetch_assoc($sq_exc)){

	$bg = (($row_booking['status']=='Cancel')) ? "danger" : '';
	
	$date = $row_booking['created_at'];
	$yr = explode("-", $date);
	$year = $yr[0];
	$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
	$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer_info['contact_no'], $secret_key);
	$email_id = $encrypt_decrypt->fnDecrypt($sq_customer_info['email_id'], $secret_key);

	$customer_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
	$emp_name='Admin';

	$sq_paid_amount = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from b2c_payment_master where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
	$paid_amount = $sq_paid_amount['sum'];
	$credit_card_charges = $sq_paid_amount['sumc'];

	$costing_data = json_decode($row_booking['costing_data']);
	$enq_data = json_decode($row_booking['enq_data']);
	$total_cost = $costing_data[0]->total_cost;
	$net_total = $costing_data[0]->net_total;

	$total_tax = $costing_data[0]->total_tax;
	$taxes = explode(',',$total_tax);
	$tax_amount = 0;
	for($i=0; $i<sizeof($taxes);$i++){
		$single_tax = explode(':',$taxes[$i]);
		$service_tax_amount = isset($single_tax[1]) ? $single_tax[1] : 0;
		$tax_amount += floatval($service_tax_amount);
	}
	$coupon_amount = $costing_data[0]->coupon_amount;
	$coupon_amount = ($coupon_amount!='')?$coupon_amount:0;

	$cancel_amount = $row_booking['cancel_amount'];
	$total_cost1 = $net_total - $cancel_amount;

	if ($row_booking['status'] == 'Cancel') {
		if ($cancel_amount <= $paid_amount) {
			$balance_amount = 0;
		} else {
			$balance_amount =  $cancel_amount - $paid_amount;
		}
	} else {
		$cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
		$balance_amount = $net_total - $paid_amount;
	}
	
	/////// Purchase ////////
	$total_purchase = 0;
	$purchase_amt = 0;
	$i = 0;
	$p_due_date = '';
	$sq_purchase_count = mysqli_num_rows(mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='B2C' and estimate_type_id='$row_booking[booking_id]' and delete_status='0'"));
	if ($sq_purchase_count == 0) {
		$p_due_date = 'NA';
	}
	$sq_purchase = mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='B2C' and estimate_type_id='$row_booking[booking_id]' and delete_status='0'");
	while ($row_purchase = mysqli_fetch_assoc($sq_purchase)) {	
		if($row_purchase['purchase_return'] == 0){
			$total_purchase += $row_purchase['net_total'];
		}
		else if($row_purchase['purchase_return'] == 2){
			$cancel_estimate = (json_decode($row_purchase['cancel_estimate'])[0] === null ) ? 0 : json_decode($row_purchase['cancel_estimate'])[0]->net_total;

			$p_purchase = ($row_purchase['net_total'] - floatval($cancel_estimate));
			$total_purchase += $p_purchase;
		}
	}

	$f_net_total += $net_total;
	$f_cancel_amount += $cancel_amount;
	$f_total_cost += $total_cost1;
	$f_paid_amount += $paid_amount;
	$f_balance_amount += $balance_amount;

	$temp_arr = array( "data" => array(
	(int)(++$count),
	get_b2c_booking_id($row_booking['booking_id'],$year),
	$customer_name,
	$contact_no,
	$email_id,
	$row_booking['service'],
	get_date_user($row_booking['created_at']),
	'<button class="btn btn-info btn-sm" id="packagev_btn-'. $row_booking['booking_id'] .'" onclick="exc_view_modal('. $row_booking['booking_id'] .')" data-toggle="tooltip" title="View Details"><i class="fa fa-eye" aria-hidden="true"></i></button>',
	number_format(floatval($total_cost),2),
	number_format(floatval($tax_amount),2),
	number_format(floatval($coupon_amount),2),
	number_format(floatval($sq_paid_amount['sumc']),2),
	number_format(floatval($net_total),2),
	number_format(floatval($cancel_amount), 2),
	number_format(floatval($total_cost1), 2),
	number_format(floatval($paid_amount), 2),
	'<button class="btn btn-info btn-sm" id="paymentv_btn-'. $row_booking['booking_id'] .'" onclick="payment_view_modal('.$row_booking['booking_id'] .')"  data-toggle="tooltip" title="View Details"><i class="fa fa-eye" aria-hidden="true"></i></button>',
	number_format(floatval($balance_amount), 2),
	number_format(floatval($total_purchase),2),
	'<button class="btn btn-info btn-sm" id="supplierv_btn-'. $row_booking['booking_id'] .'" onclick="supplier_view_modal('. $row_booking['booking_id'] .')" data-toggle="tooltip" title="View Details"><i class="fa fa-eye" aria-hidden="true"></i></button>',
	$emp_name,
	), "bg" =>$bg);
	array_push($array_s,$temp_arr);
}
$footer_data = array("footer_data" => array(
	'total_footers' => 6,
	'foot0' => "",
	'col0' =>10,
	'class0' =>"",
	
	'foot1' => "TOTAL CANCEL : ".number_format($f_cancel_amount,2),
	'col1' => 2,
	'class1' =>"danger text-right",

	'foot2' => "TOTAL SALE :".number_format($f_total_cost,2),
	'col2' => 2,
	'class2' =>"info text-right",

	'foot3' => "TOTAL PAID : ".number_format($f_paid_amount,2),
	'col3' => 2,
	'class3' =>"success text-right",

	'foot4' => "TOTAL BALANCE : ".number_format($f_balance_amount,2),
	'col4' => 2,
	'class4' =>"warning text-right",

	'foot5' => "",
	'col5' => 6,
	'class5' => "",

	'foot6' => "",
	'col6' => 6,
	'class6' => ""
	)
);
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>
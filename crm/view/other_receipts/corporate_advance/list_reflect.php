<?php
include "../../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];

$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$cust_id = $_POST['cust_id'];
$branch_status = $_POST['branch_status'];
$financial_year_id = $_SESSION['financial_year_id'];

$query = "select * from corporate_advance_master where payment_amount!='0' and delete_status='0' ";
if($from_date!="" && $to_date!=""){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);

	$query .= " and payment_date between '$from_date' and '$to_date'";
}
if($cust_id!=""){
	$query .= " and cust_id='$cust_id' ";
}
if($financial_year_id!=""){
	$query .=" and financial_year_id='$financial_year_id'";
}
if($branch_status=='yes' && $role!='Admin'){
	$query .= " and branch_admin_id = '$branch_admin_id'";
}
$query .= " order by advance_id desc";
$array_s = array();
$temp_arr = array();
$footer_data = array();
$count = 0;
$bg;
$sq_pending_amount=0;
$sq_cancel_amount=0;
$sq_paid_amount=0;
$Total_payment=0;
$sq_income = mysqlQuery($query);
while($row_income = mysqli_fetch_assoc($sq_income)){

	$yr1 = explode("-", $row_income['payment_date']);
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_income[cust_id]'"));
	if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
		$customer_name = $sq_cust['company_name'];
	}else{
		$customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
	}
	$sq_paid_amount = $sq_paid_amount + $row_income['payment_amount'];
	if($row_income['clearance_status']=="Pending"){
		$bg = 'warning';
		$sq_pending_amount = $sq_pending_amount + $row_income['payment_amount'];
	}
	else if($row_income['clearance_status']=="Cancelled"){
		$bg = 'danger';
		$sq_cancel_amount = $sq_cancel_amount + $row_income['payment_amount'];
	}
	else{
		$bg = '';
	}
	// PDF
	$payment_id_name = "";
	$payment_id = get_custadv_payment_id($row_income['advance_id'],$yr1[0]);
	$receipt_date = get_date_user($row_income['payment_date']);
	$booking_id = 'NA';
	$customer_id = $row_income['cust_id'];
	$booking_name = 'Customer Advance';
	$travel_date = 'NA';
	$payment_amount = $row_income['payment_amount'];
	$payment_mode1 = $row_income['payment_mode'];
	$transaction_id = $row_income['transaction_id'];
	$payment_date = $receipt_date;
	$bank_name = $row_income['bank_name'];
	$receipt_type = 'Customer Advance';

	$url1 = BASE_URL."model/app_settings/print_html/receipt_html/receipt_body_html.php?payment_id_name=$payment_id_name&payment_id=$payment_id&receipt_date=$receipt_date&booking_id=$booking_id&customer_id=$customer_id&booking_name=$booking_name&travel_date=$travel_date&payment_amount=$payment_amount&transaction_id=$transaction_id&payment_date=$payment_date&bank_name=$bank_name&confirm_by=&receipt_type=$receipt_type&payment_mode=$payment_mode1&branch_status=$branch_status&outstanding=0&table_name=corporate_advance_master&customer_field=cust_id&in_customer_id=$customer_id&currency_code=$currency&status=";

	$temp_arr = array( "data" => array(
		(int)(++$count),
		$customer_name,
		get_date_user($row_income['payment_date']),
		$row_income['payment_mode'],
		$row_income['particular'],
		$row_income['payment_amount'],
		'<a onclick="loadOtherPage(\''. $url1 .'\')" data-toggle="tooltip" class="btn btn-info btn-sm" title="Download Receipt"><i class="fa fa-print"></i></a>'.
		'<button data-toggle="tooltip" title="Update Details" class="btn btn-info btn-sm '.$active_inactive_flag.'" onclick="update_income_modal('.$row_income['advance_id'] .')" id="edit-'.$row_income['advance_id'] .'"><i class="fa fa-pencil-square-o"></i></button>'.
		'<button class="'.$delete_flag.' btn btn-danger btn-sm" onclick="delete_entry('.$row_income['advance_id'].')" title="Delete Entry"><i class="fa fa-trash"></i></button>'),"bg"=>$bg);
		array_push($array_s,$temp_arr); 
	}
	$footer_data = array("footer_data" => array(
		'total_footers' => 4,
		'foot0' => "Total Amount: ".number_format((($sq_paid_amount=="") ? 0 : $sq_paid_amount), 2),
		'col0' => 2,
		'class0' => "info",
		'foot1' => "Pending Clearance : ".number_format((($sq_pending_amount=="") ? 0 : $sq_pending_amount), 2),
		'col1' => 2,
		'class1' => "warning",
		'foot2' =>  "Cancelled : ".number_format((($sq_cancel_amount=="") ? 0 : $sq_cancel_amount), 2),
		'col2' => 1,
		'class2' => "danger",
		'foot3' => "Total Paid : ".number_format(($sq_paid_amount - $sq_pending_amount - $sq_cancel_amount), 2),
		'col3' => 2,
		'class3' => "success"
		)
	);
array_push($array_s, $footer_data);	
echo json_encode($array_s);	
?>

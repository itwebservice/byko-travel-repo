<?php
include "../../../../model/model.php";

$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$train_ticket_id = $_POST['train_ticket_id'];
$payment_mode = $_POST['payment_mode'];
$financial_year_id = $_SESSION['financial_year_id'];
$payment_from_date = $_POST['payment_from_date'];
$payment_to_date = $_POST['payment_to_date'];
$cust_type = isset($_POST['cust_type']) ? $_POST['cust_type'] : '';
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_POST['branch_status'];

$query = "select * from train_ticket_payment_master where 1 and payment_amount!='0'";		
if($financial_year_id!=""){
	$query .= " and financial_year_id='$financial_year_id'";
}
if($train_ticket_id!=""){
	$query .= " and train_ticket_id='$train_ticket_id'";
}
if($payment_mode!=""){
	$query .= " and payment_mode='$payment_mode'";
}
if($customer_id!=""){
	$query .= " and train_ticket_id in (select train_ticket_id from train_ticket_master where customer_id='$customer_id')";
}
if($payment_from_date!='' && $payment_to_date!=''){
	$payment_from_date = get_date_db($payment_from_date);
	$payment_to_date = get_date_db($payment_to_date);
	$query .=" and payment_date between '$payment_from_date' and '$payment_to_date'";
}
if($cust_type != ""){
	$query .= " and train_ticket_id in (select train_ticket_id from train_ticket_master where customer_id in ( select customer_id from customer_master where type='$cust_type' ))";
}
if($company_name != ""){
	$query .= " and train_ticket_id in (select train_ticket_id from train_ticket_master where customer_id in ( select customer_id from customer_master where company_name='$company_name' ))";
}
if($role == "B2b"){
	$query .= " and train_ticket_id in (select train_ticket_id from train_ticket_master where emp_id ='$emp_id')";
}
if($branch_status=='yes'){
	if($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
		$query .= " and branch_admin_id = '$branch_admin_id'";
	}
	elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
		
		if($role == 'Backoffice' && $show_entries_switch == 'Yes'){
			$query .=" and branch_admin_id = '$branch_admin_id'";
		}else{
			$query .= " and train_ticket_id in (select train_ticket_id from train_ticket_master where emp_id ='$emp_id')  and branch_admin_id = '$branch_admin_id'";			
		}
	}
}
elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
	if($role == 'Backoffice' && $show_entries_switch == 'Yes'){
		$query .=" and branch_admin_id = '$branch_admin_id'";
	}else{
		$query .= " and train_ticket_id in (select train_ticket_id from train_ticket_master where emp_id ='$emp_id')  and branch_admin_id = '$branch_admin_id'";			
	}
}

$count = 0;

$sq_pending_amount=0;
$sq_cancel_amount=0;
$sq_paid_amount=0;
$Total_payment=0;
$array_s = array();
$temp_arr = array();
$footer_data = array();
$sq_train_ticket_payment = mysqlQuery($query);
while($row_train_ticket_payment = mysqli_fetch_assoc($sq_train_ticket_payment)){

	$count++;
	$sq_train_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$row_train_ticket_payment[train_ticket_id]'"));
	$total_sale = $sq_train_ticket_info['net_total'];
	$sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from train_ticket_payment_master where clearance_status!='Cancelled' and train_ticket_id='$row_train_ticket_payment[train_ticket_id]'"));
	$total_pay_amt = $sq_pay['sum'];
	$outstanding =  $total_sale - $total_pay_amt;

	$date = $sq_train_ticket_info['created_at'];
	$yr = explode("-", $date);
	$year = $yr[0];
	$date1 = $row_train_ticket_payment['payment_date'];
	$yr1 = explode("-", $date1);
	$year1 = $yr1[0];

	$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_train_ticket_info[customer_id]'"));
	if($sq_customer_info['type']=='Corporate'||$sq_customer_info['type'] == 'B2B'){
		$customer_name = $sq_customer_info['company_name'];
	}else{
		$customer_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
	}

	$sq_depa_date = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master_trip_entries where train_ticket_id='$row_train_ticket_payment[train_ticket_id]'"));
	
	$bg="";

	if($row_train_ticket_payment['clearance_status']=="Pending"){ 
		$bg='warning';
		$sq_pending_amount = $sq_pending_amount + $row_train_ticket_payment['payment_amount'] + $row_train_ticket_payment['credit_charges'];
	}
	else if($row_train_ticket_payment['clearance_status']=="Cancelled"){ 
		$bg='danger';
		$sq_cancel_amount = $sq_cancel_amount + $row_train_ticket_payment['payment_amount'] + $row_train_ticket_payment['credit_charges'];
	}
	else if($row_train_ticket_payment['clearance_status']=="Cleared"){ 
		$bg='success';
	}
	else if($row_train_ticket_payment['clearance_status']==""){ 
		$bg='';
	}
	$sq_paid_amount = $sq_paid_amount + $row_train_ticket_payment['payment_amount'] + $row_train_ticket_payment['credit_charges'];
	
	$payment_id_name = "Train Ticket Payment ID";
	$payment_id = get_train_ticket_booking_payment_id($row_train_ticket_payment['payment_id'],$year1);
	$receipt_date = date('d-m-Y');
	$booking_id = get_train_ticket_booking_id($row_train_ticket_payment['train_ticket_id'],$year);
	$customer_id = $sq_train_ticket_info['customer_id'];
	$booking_name = "Train Ticket Booking";
	$travel_date = date('d-m-Y',strtotime($sq_depa_date['travel_datetime']));;
	$payment_amount = $row_train_ticket_payment['payment_amount'] + $row_train_ticket_payment['credit_charges'];
	$payment_mode1 = $row_train_ticket_payment['payment_mode'];
	$transaction_id = $row_train_ticket_payment['transaction_id'];
	$payment_date = date('d-m-Y',strtotime($row_train_ticket_payment['payment_date']));
	$bank_name = $row_train_ticket_payment['bank_name'];
	$receipt_type ="Train Ticket Receipt";
	
	$url1 = BASE_URL."model/app_settings/print_html/receipt_html/receipt_body_html.php?payment_id_name=$payment_id_name&payment_id=$payment_id&receipt_date=$receipt_date&booking_id=$booking_id&customer_id=$customer_id&booking_name=$booking_name&travel_date=$travel_date&payment_amount=$payment_amount&transaction_id=$transaction_id&payment_date=$payment_date&bank_name=$bank_name&confirm_by=&receipt_type=$receipt_type&payment_mode=$payment_mode1&branch_status=$branch_status&outstanding=$outstanding&table_name=train_ticket_payment_master&customer_field=train_ticket_id&in_customer_id=$row_train_ticket_payment[train_ticket_id]&status=$row_train_ticket_payment[status]";
	$checshow = "";
	if($row_train_ticket_payment['payment_mode']=="Cash" || $row_train_ticket_payment['payment_mode']=="Cheque"){
		$checshow = "<input type=\"checkbox\" id=\"chk_train_ticket_payment".$count."\" name=\"chk_train_ticket_payment\" value=". $row_train_ticket_payment['payment_id'].">";
	}
	$payshow = "";
	if($payment_mode=="Cheque"){
		$payshow = '<input type="text" id="branch_name_'.$count.'" name="branch_name_d" class="form-control" placeholder="Branch Name" style="width:120px">';
	}
	if($row_train_ticket_payment['payment_mode'] == 'Credit Note' || ($row_train_ticket_payment['payment_mode'] == 'Credit Card' && $row_train_ticket_payment['clearance_status']=="Cleared")){
		$edit_btn = '';
		$delete_btn = '';
	}else{
		$edit_btn = '<button class="btn btn-info btn-sm" data-toggle="tooltip"  onclick="train_ticket_payment_update_modal('.$row_train_ticket_payment['payment_id'].')" id="updater_btn-'. $row_train_ticket_payment['payment_id'] .'" title="Update Details"><i class="fa fa-pencil-square-o"></i></button>';
		$delete_btn = '<button class="'.$delete_flag.' btn btn-danger btn-sm" onclick="delete_entry('.$row_train_ticket_payment['payment_id'].')" title="Delete Entry"><i class="fa fa-trash"></i></button>';
	}
	if ($row_train_ticket_payment['clearance_status']=="Cancelled"){
		$edit_btn = '';
	}

	$temp_arr = array( "data" => array(
		(int)($count),
		$checshow,
		$payment_id,
		$booking_id,
		$customer_name,
		date('d/m/Y', strtotime($row_train_ticket_payment['payment_date'])),
		$row_train_ticket_payment['payment_mode'],
		$payshow,
		number_format($row_train_ticket_payment['payment_amount'] + $row_train_ticket_payment['credit_charges'],2),
		'<a onclick="loadOtherPage(\''. $url1 .'\')" data-toggle="tooltip" class="btn btn-info btn-sm" title="Download Receipt"><i class="fa fa-print"></i></a>
		'.$edit_btn.$delete_btn
		), "bg" =>$bg );
		array_push($array_s,$temp_arr); 
}

$footer_data = array("footer_data" => array(
	'total_footers' => 4,
	'foot0' => "Paid Amount: ".number_format($sq_paid_amount, 2),
	'col0' => 3,
	'class0' => "",
	'foot1' => "Pending Clearance : ".number_format($sq_pending_amount, 2),
	'col1' => 2,
	'class1' => "warning",
	'foot2' =>  "Cancelled Amount : ".number_format($sq_cancel_amount, 2),
	'col2' => 2,
	'class2' => "danger",
	'foot3' => "Total Payment : ".number_format(($sq_paid_amount - $sq_pending_amount - $sq_cancel_amount), 2),
	'col3' => 2,
	'class3' => "success",
	)
);
array_push($array_s, $footer_data);	
echo json_encode($array_s);	
?>
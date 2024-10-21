<?php
include "../../model/model.php";
global $modify_entries_switch;
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_POST['financial_year_id'];
$emp_id = $_SESSION['emp_id'];

$branch_status = $_POST['branch_status'];
$tour_id = $_POST['tour_id'];
$group_id = $_POST['group_id'];
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$booking_id = $_POST['booking_id'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$cust_type = isset($_POST['cust_type']) ? $_POST['cust_type'] : '';
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';

$query = "select * from tourwise_traveler_details where financial_year_id='$financial_year_id' and delete_status='0'";
if($tour_id!=""){
$query .=" and tour_id='$tour_id'";
}
if($group_id!=""){
$query .=" and tour_group_id='$group_id'";
}

if($customer_id!=""){
$query .=" and customer_id='$customer_id'";
}

if($booking_id!=""){
$query .=" and id='$booking_id'";
}

if($from_date!="" && $to_date!=""){
$from_date = get_date_db($from_date);
$to_date = get_date_db($to_date);
$query .= " and date(form_date) between '$from_date' and '$to_date'";
}		

if($cust_type != ""){
$query .= " and customer_id in (select customer_id from customer_master where type = '$cust_type')";
}

if($company_name != ""){
$query .= " and customer_id in (select customer_id from customer_master where company_name = '$company_name')";
}

if($role == "B2b"){
$query .= " and emp_id='$emp_id' ";
}
include "../../model/app_settings/branchwise_filteration.php";

$query .= " order by id desc";
$sq_booking = mysqlQuery($query);
$array_s = array();
$temp_arr = array();
$count = 1;
$footer_amount = 0;
$footer_cancel = 0;
$footer_total = 0;
while($row_booking = mysqli_fetch_assoc($sq_booking)){

	$sq_emp =  mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_booking[emp_id]'"));
	$emp_name = ($row_booking['emp_id'] != 0) ? $sq_emp['first_name'].' '.$sq_emp['last_name'] : 'Admin';
	$pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_booking[traveler_group_id]'"));
	$cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and status='Cancel'"));
	
	$date = $row_booking['form_date'];
	$yr = explode("-", $date);
	$year = $yr[0];

	$sq_tour = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id='$row_booking[tour_id]'"));
	$sq_group = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where group_id='$row_booking[tour_group_id]'"));

	$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
	$sq_est_info = mysqli_fetch_assoc(mysqlQuery("select * from refund_traveler_estimate where tourwise_traveler_id='$row_booking[id]'"));

	$sq_train = mysqli_num_rows(mysqlQuery("select * from train_master where tourwise_traveler_id='$row_booking[id]'"));
	$sq_plane = mysqli_num_rows(mysqlQuery("select * from plane_master where tourwise_traveler_id='$row_booking[id]'"));
	$sq_visa = $row_booking['visa_amount'];

	$sq_insurance = $row_booking['insuarance_amount'];

	$total_paid = 0;
	$sq_paid_amount = mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum,sum(`credit_charges`) as sumc from payment_master where tourwise_traveler_id='$row_booking[id]'and clearance_status!='Cancelled' and clearance_status!='Pending'"));
	$total_paid = $sq_paid_amount['sum'];  
	$credit_card_charges = $sq_paid_amount['sumc'];
	
	$total_paid = ($total_paid == '') ? '0' : $total_paid ;
	$average = 1;

	if($sq_train > 0){ $average++; }
	if($sq_plane > 0){ $average++; }
	if($sq_visa !=0 && $sq_visa!=""){ $average++; }
	if($sq_insurance !=0 && $sq_insurance!=""){ $average++; }

	$tour = $sq_tour['tour_name'];
	$group = get_date_user($sq_group['from_date']).' to '.get_date_user($sq_group['to_date']);

	if($row_booking['tour_group_status'] == 'Cancel'){
		//Group Tour cancel
		$cancel_tour_count2=mysqli_num_rows(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row_booking[id]'"));
		if($cancel_tour_count2 >= '1'){
			$cancel_tour=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row_booking[id]'"));
			$cancel_tour_amount = $cancel_tour['cancel_amount'];
		}
		else{ 
			$cancel_tour_amount = 0;
		}
	}
	else{
		// Group booking cancel
		$cancel_esti_count1=mysqli_num_rows(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row_booking[id]'"));
		if($cancel_esti_count1 >= '1'){
			$cancel_esti1=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id = '$row_booking[id]'"));
			$cancel_tour_amount = $cancel_esti1['cancel_amount'];
		}
		else{ $cancel_tour_amount = 0; }
	}

	$update_btn = '
		<form style="display:inline-block" action="booking_update/booking_update.php" id="frm_booking_'.$count.'" method="POST">
			<input type="hidden" id="booking_id" style="display:inline-block" name="booking_id" value="'.$row_booking['id'].'">
			<input type="hidden" id="branch_status" name="branch_status" style="display:inline-block" value="'.$branch_status .'" >
			<button data-toggle="tooltip" class="btn btn-info btn-sm" style="display:inline-block" title="Update Details" id="edit-'.$row_booking['id'].'"><i class="fa fa-pencil-square-o"></i></button>
		</form>';
	$to_date = $sq_group['to_date'];
	$today = date('Y-m-d');
	if($to_date < $today && $modify_entries_switch == 'No' && $role != 'Admin' && $role != 'Branch Admin'){
		$update_btn = '';
	}
	$delete_btn = '<button class="'.$delete_flag.' btn btn-danger btn-sm" onclick="delete_entry('.$row_booking['id'].')" title="Delete Entry"><i class="fa fa-trash"></i></button>';
	// Booking Form
	$b_url = BASE_URL."model/app_settings/print_html/booking_form_html/group_tour.php?booking_id=$row_booking[id]&branch_status=$branch_status&year=$year&credit_card_charges=$credit_card_charges";
	$conf_btn = '<a onclick="loadOtherPage(\''. $b_url .'\')" data-toggle="tooltip" class="btn btn-info btn-sm" title="Download Confirmation Form"><i class="fa fa-print"></i></a>';
	
	$bg = "";
	if($row_booking['tour_group_status']=="Cancel"){
		$bg = "danger";
		$update_btn = '';
		$delete_btn = '';
		$conf_btn = '';
	}
	else{
		if($pass_count==$cancelpass_count){
			$bg = "danger";
			$update_btn = '';
			$delete_btn = '';
			$conf_btn = '';
		}
	}
	
	$invoice_no = get_group_booking_id($row_booking['id'],$year);
	$invoice_date = date('d-m-Y',strtotime($row_booking['form_date']));
	$customer_id = $row_booking['customer_id'];
	$service_name = "Group Invoice";

	//Net amount
	$net_total = 0;
	$net_total  = $row_booking['net_total'] + $credit_card_charges;

	$taxation_type = $row_booking['taxation_type'];

	//basic amount
	$train_expense = $row_booking['train_expense'];
	$plane_expense = $row_booking['plane_expense'];
	$cruise_expense = $row_booking['cruise_expense'];
	$visa_amount = $row_booking['visa_amount'];
	$insuarance_amount = $row_booking['insuarance_amount'];
	$tour_subtotal = $row_booking['tour_fee_subtotal_1'];
	$basic_cost = $train_expense +$plane_expense +$cruise_expense +$visa_amount +$insuarance_amount +$tour_subtotal;

	//Service charge	
	$train_service_charge = $row_booking['train_service_charge'];
	$plane_service_charge = $row_booking['plane_service_charge'];
	$cruise_service_charge = $row_booking['cruise_service_charge'];
	$visa_service_charge = $row_booking['visa_service_charge'];
	$insuarance_service_charge = $row_booking['insuarance_service_charge'];
	$service_charge = $train_service_charge +$plane_service_charge +$cruise_service_charge +$visa_service_charge +$insuarance_service_charge;

	//service tax
	$train_service_tax = $row_booking['train_service_tax'];
	$plane_service_tax = $row_booking['plane_service_tax'];
	$cruise_service_tax = $row_booking['cruise_service_tax'];
	$visa_service_tax = $row_booking['visa_service_tax'];
	$insuarance_service_tax = $row_booking['insuarance_service_tax'];
	$tour_service_tax = $row_booking['service_tax_per'];
	
	//service tax subtotal	
	$train_service_tax_subtotal = $row_booking['train_service_tax_subtotal'];
	$plane_service_tax_subtotal = $row_booking['plane_service_tax_subtotal'];
	$cruise_service_tax_subtotal = $row_booking['cruise_service_tax_subtotal'];
	$visa_service_tax_subtotal = $row_booking['visa_service_tax_subtotal'];
	$insuarance_service_tax_subtotal = $row_booking['insuarance_service_tax_subtotal'];
	$tour_service_tax_subtotal = $row_booking['service_tax'];
	$service_tax_subtotal = floatval($train_service_tax_subtotal) + floatval($plane_service_tax_subtotal) +floatval($cruise_service_tax_subtotal) +floatval($visa_service_tax_subtotal) +floatval($insuarance_service_tax_subtotal)+floatval($tour_service_tax_subtotal);	
	
	$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Group Tour'"));   
	$sac_code = $sq_sac['hsn_sac_code'];
	$tour_date = get_date_user($sq_group['from_date']);
	$tour_to_date = get_date_user($sq_group['to_date']);
	$booking_id = $row_booking['id'];
	
	$adults = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and adolescence='Adult'"));
	$childw = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and adolescence='Child With Bed'"));
	$childwo = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and adolescence='Child Without Bed'"));
	$child = intval($childw) + intval($childwo);
	$infants = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and adolescence='Infant'"));
	//Flights
	$sq_f_count = mysqli_num_rows(mysqlQuery("select * from plane_master where tourwise_traveler_id='$row_booking[id]'")); 
	$flights = '';
	$count = 1;
    if($sq_f_count != '0'){
		$sq_entry = mysqlQuery("select * from plane_master where tourwise_traveler_id='$row_booking[id]'");
		while($row_entry = mysqli_fetch_assoc($sq_entry)){
	
			$seperator = ($sq_f_count != $count) ? '/ ' : '';
			$flights .= 'From '.$row_entry['from_location'].' To '.$row_entry['to_location'].$seperator;
			$count++;
		}
	}
	//Train
	$sq_f_count = mysqli_num_rows(mysqlQuery("select * from train_master where tourwise_traveler_id='$row_booking[id]'")); 
	$trains = '';
	$count = 1;
    if($sq_f_count != '0'){
		$sq_entry = mysqlQuery("select * from train_master where tourwise_traveler_id='$row_booking[id]'");
		while($row_entry = mysqli_fetch_assoc($sq_entry)){
			$seperator = ($sq_f_count != $count) ? '/ ' : '';
			$trains .= 'From '.$row_entry['from_location'].' To '.$row_entry['to_location'].$seperator;
			$count++;
		}
	}
	//Cruise
	$sq_f_count = mysqli_num_rows(mysqlQuery("select * from group_cruise_master where booking_id='$row_booking[id]'")); 
	$cruises = '';
    if($sq_f_count != '0'){
		$count = 0;
		$sq_entry = mysqlQuery("select * from group_cruise_master where booking_id='$row_booking[id]'");
		while($row_entry = mysqli_fetch_assoc($sq_entry)){
			$count++;
			$cruises .= 'Cabin '.$row_entry['cabin'].', Route '.$row_entry['route'];
			$cruises .= ($count<$sq_f_count)?' / ':'';
		}
	}

	if($app_invoice_format == 4)			
	$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/git_fit_tax_invoice.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=$taxation_type&train_expense=$train_expense&plane_expense=$plane_expense&cruise_expense=$cruise_expense&visa_amount=$visa_amount&insuarance_amount=$insuarance_amount&tour_subtotal=$tour_subtotal&train_service_charge=$train_service_charge&plane_service_charge=$plane_service_charge&cruise_service_charge=$cruise_service_charge&visa_service_charge=$visa_service_charge&insuarance_service_charge=$insuarance_service_charge&train_service_tax=$train_service_tax&plane_service_tax=$plane_service_tax&cruise_service_tax=$cruise_service_tax&visa_service_tax=$visa_service_tax&insuarance_service_tax=$insuarance_service_tax&tour_service_tax=$tour_service_tax&train_service_tax_subtotal=$train_service_tax_subtotal&plane_service_tax_subtotal=$plane_service_tax_subtotal&cruise_service_tax_subtotal=$cruise_service_tax_subtotal&visa_service_tax_subtotal=$visa_service_tax_subtotal&insuarance_service_tax_subtotal=$insuarance_service_tax_subtotal&tour_service_tax_subtotal=$tour_service_tax_subtotal&total_paid=$total_paid&net_total=$net_total&sac_code=$sac_code&branch_status=$branch_status&pass_count=$pass_count&tour_date=$tour_date&tour_name=$tour&booking_id=$booking_id&credit_card_charges=$credit_card_charges";
	else
	$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/git_fit_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=$taxation_type&train_expense=$train_expense&plane_expense=$plane_expense&cruise_expense=$cruise_expense&visa_amount=$visa_amount&insuarance_amount=$insuarance_amount&tour_subtotal=$tour_subtotal&train_service_charge=$train_service_charge&plane_service_charge=$plane_service_charge&cruise_service_charge=$cruise_service_charge&visa_service_charge=$visa_service_charge&insuarance_service_charge=$insuarance_service_charge&train_service_tax=$train_service_tax&plane_service_tax=$plane_service_tax&cruise_service_tax=$cruise_service_tax&visa_service_tax=$visa_service_tax&insuarance_service_tax=$insuarance_service_tax&tour_service_tax=$tour_service_tax&train_service_tax_subtotal=$train_service_tax_subtotal&plane_service_tax_subtotal=$plane_service_tax_subtotal&cruise_service_tax_subtotal=$cruise_service_tax_subtotal&visa_service_tax_subtotal=$visa_service_tax_subtotal&insuarance_service_tax_subtotal=$insuarance_service_tax_subtotal&tour_service_tax_subtotal=$tour_service_tax_subtotal&total_paid=$total_paid&net_total=$net_total&sac_code=$sac_code&branch_status=$branch_status&tour_name=$tour&booking_id=$booking_id&credit_card_charges=$credit_card_charges&tcs_tax=$row_booking[tcs_tax]&tcs_per=$row_booking[tcs_per]&tour_date=$tour_date&tour_to_date=$tour_to_date&child=$child&adults=$adults&infants=$infants&flights=$flights&trains=$trains&cruises=$cruises&canc_amount=$cancel_tour_amount&bg=$bg";


	// currency conversion
	$currency_amount1 = currency_conversion($currency,$row_booking['currency_code'],$net_total - $cancel_tour_amount);
	if($row_booking['currency_code'] !='0' && $currency != $row_booking['currency_code']){
		$currency_amount = ' ('.$currency_amount1.')';
	}else{
		$currency_amount = '';
	}
	$footer_amount += $net_total;
	$footer_cancel += $cancel_tour_amount;
	$footer_total += $net_total - $cancel_tour_amount;

	$temp_arr = array( "data" => array(
		$row_booking['invoice_pr_id'],
		get_group_booking_id($row_booking['id'],$year),
		($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') ? $sq_customer['company_name'] : $sq_customer['first_name'].' '.$sq_customer['last_name'],
		$tour,
		number_format(floatval($row_booking['net_total']) + floatval($credit_card_charges),2),
		number_format($cancel_tour_amount,2),
		number_format(floatval($row_booking['net_total']) + floatval($credit_card_charges) - floatval($cancel_tour_amount),2).$currency_amount,
		$emp_name,
		get_date_user($date),
		$conf_btn.
		'<a onclick="loadOtherPage(\''.$url1 .'\')" class="btn btn-info btn-sm" data-toggle="tooltip" title="Download Invoice"><i class="fa fa-print"></i></a>'.$update_btn.'
		<button data-toggle="tooltip" class="btn btn-info btn-sm" style="display:inline-block" onclick="display_modal(\''.$row_booking['id'] .'\')" title="View Details" id="viewb-'.$row_booking['id'].'"><i class="fa fa-eye"></i></button>'.$delete_btn), "bg" => $bg
		);
	array_push($array_s,$temp_arr); 
	$count++;

}
$footer_data = array("footer_data" => array(
	'total_footers' => 5,
	'foot0' => "Total",
	'col0' => 4,
	'class0' => "text-right",
	'foot1' => number_format($footer_amount, 2),
	'col1' => 1, 
	'class1' => "info",
	'foot2' =>  number_format($footer_cancel, 2),
	'col2' => 1,
	'class2' => "danger",
	'foot3' => number_format($footer_total, 2),
	'col3' => 1,
	'class3' => "success",
	'foot4' => '',
	'col4' => 3,
	'class4' => "",
	)
);
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>


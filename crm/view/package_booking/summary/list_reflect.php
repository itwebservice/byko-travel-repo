<?php
include "../../../model/model.php";
global $currency;
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_POST['branch_status'];
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$booking_id = $_POST['booking_id'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$cust_type = $_POST['cust_type'];
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';
$booker_id = $_POST['booker_id'];
$branch_id = $_POST['branch_id'];
$array_s = array();
$temp_arr = array();

$query = "select * from package_tour_booking_master where 1 and delete_status='0' ";
if($customer_id!=""){
	$query .= " and customer_id='$customer_id'";
}
if($booking_id!=""){
	$query .= " and booking_id='$booking_id'";
}
if($from_date!="" && $to_date!=""){
	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));
	$query .= " and booking_date between '$from_date' and '$to_date'";
}
if($cust_type != ""){
	$query .= " and customer_id in (select customer_id from customer_master where type = '$cust_type')";
}
if($company_name != ""){
	$query .= " and customer_id in (select customer_id from customer_master where company_name = '$company_name')";
}
if($booker_id!=""){
	$query .= " and emp_id='$booker_id'";
}
if($branch_id!=""){
	$query .= " and emp_id in(select emp_id from emp_master where branch_id = '$branch_id')";
}
include "../../../model/app_settings/branchwise_filteration.php";
$count = 0;
$total_balance=0;
$total_refund=0;	
$cancel_total =0;
$sale_total = 0;
$paid_total = 0;
$balance_total = 0;

$sq_package = mysqlQuery($query);
while($row_package = mysqli_fetch_assoc($sq_package)){

	$date = $row_package['booking_date'];
	$yr = explode("-", $date);
	$year =$yr[0];
	$pass_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_package[booking_id]'"));
	$cancle_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_package[booking_id]' and status='Cancel'"));
	if($pass_count==$cancle_count){
			$bg="danger";
	}else{
			$bg="#fff";
	}
	
	$tour_name = $row_package['tour_name'];
	$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$row_package[emp_id]'"));
	if($sq_emp['first_name'] == '') { $emp_name='Admin';}
	else{ $emp_name = $sq_emp['first_name'].' '.$sq_emp['last_name']; }

	$sq_branch = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$sq_emp[branch_id]'"));
	$branch_name = $sq_branch['branch_name']==''?'NA':$sq_branch['branch_name'];
	$sq_total_member = mysqli_num_rows(mysqlQuery("select booking_id from package_travelers_details where booking_id = '$row_package[booking_id]'"));
	$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_package[customer_id]'"));
	$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer_info['contact_no'], $secret_key);
	$email_id = $encrypt_decrypt->fnDecrypt($sq_customer_info['email_id'], $secret_key);
	if($sq_customer_info['type'] == 'Corporate'||$sq_customer_info['type'] == 'B2B'){
		$customer_name = $sq_customer_info['company_name'];
	}else{
		$customer_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
	}

	$total_paid = 0;
	$sq_paid_amount = mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum,sum(credit_charges) as sumc from  package_payment_master where booking_id='$row_package[booking_id]' and clearance_status!='Pending' and  clearance_status!='Cancelled'"));
	$credit_card_charges = $sq_paid_amount['sumc'];
	$total_paid =  $sq_paid_amount['sum']; 
	if($total_paid == ''){ $total_paid = 0; }
	
	//sale amount
	$tour_fee = $row_package['net_total'];

	//cancel amount
	$q1 = "SELECT * from package_refund_traveler_estimate where booking_id='$row_package[booking_id]'";
	$row_esti_count = mysqli_fetch_assoc(mysqlQuery($q1));
	$row_esti= mysqli_fetch_assoc(mysqlQuery($q1));
	$tour_esti= ($row_esti_count > 0) ? $row_esti['cancel_amount'] : 0;

	//total amount
	$total_amount = $tour_fee - $tour_esti;

	//balance
	if($pass_count == $cancle_count){
		if($total_paid > 0){
			if($tour_esti >0){
				if($total_paid > $tour_esti){
					$total_balance = 0;
				}else{
					$total_balance = $tour_esti - $total_paid;
				}
			}else{
				$total_balance = 0;
			}
		}
		else{
			$total_balance = $tour_esti;
		}
	}
	else{
		$total_balance = $total_amount - $total_paid;
	}

	//Footer
	$cancel_total = $cancel_total + $tour_esti;
	$sale_total = $sale_total + $total_amount;
	$paid_total = $paid_total + $sq_paid_amount['sum'];
	$balance_total = $balance_total + $total_balance;
	/////// Purchase ////////
	$total_purchase = 0;
	$purchase_amt = 0;
	$i=0;
	$p_due_date = '';
	$sq_purchase_count = mysqli_num_rows(mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='Package Tour' and estimate_type_id='$row_package[booking_id]' and delete_status='0'"));
	if($sq_purchase_count == 0){  $p_due_date = 'NA'; }
	$sq_purchase = mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='Package Tour' and estimate_type_id='$row_package[booking_id]' and delete_status='0'");
	while($row_purchase = mysqli_fetch_assoc($sq_purchase)){			
		if($row_purchase['purchase_return'] == 0){
			$total_purchase += $row_purchase['net_total'];
		}
		else if($row_purchase['purchase_return'] == 2){
			$cancel_estimate = json_decode($row_purchase['cancel_estimate']);
			$p_purchase = ($row_purchase['net_total'] - floatval($cancel_estimate[0]->net_total));
			$total_purchase += $p_purchase;
		}
	}
	$q1 = "select * from vendor_estimate where status!='Cancel' and estimate_type='Package Tour' and estimate_type_id='$row_package[booking_id]' and delete_status='0'";
	$sq_purchase1 = mysqli_fetch_assoc(mysqlQuery($q1));		
	$sq_purchase_count = mysqli_num_rows(mysqlQuery($q1));		
	$vendor_type = ($sq_purchase_count > 0) ? $sq_purchase1['vendor_type'] : '';
	$vendor_type_id = ($sq_purchase_count > 0) ? $sq_purchase1['vendor_type_id'] : '';
	$vendor_name = get_vendor_name_report($vendor_type, $vendor_type_id);
	if($vendor_name == ''){ $vendor_name1 = 'NA';  }
	else{ $vendor_name1 = $vendor_name; }

	//Service Tax and Markup Tax
	$service_tax_amount = 0;
	if($row_package['tour_service_tax_subtotal'] !== 0.00 && ($row_package['tour_service_tax_subtotal']) !== ''){
		$service_tax_subtotal1 = explode(',',$row_package['tour_service_tax_subtotal']);
		for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
			$service_tax = explode(':',$service_tax_subtotal1[$i]);
			$service_tax_amount +=  $service_tax[2];
		}
	}
	/////// Incetive ////////
	$sq_incentive = mysqli_fetch_assoc(mysqlQuery("select * from booker_sales_incentive where booking_id='$row_package[booking_id]' and service_type='Package Tour'"));
	$incentive_amount = isset($sq_incentive['incentive_amount']) ? $sq_incentive['incentive_amount'] : 0;
	
	//////////Invoice//////////////
	$invoice_no = get_package_booking_id($row_package['booking_id'],$year);
	$invoice_date = date('d-m-Y',strtotime($row_package['booking_date']));
	$customer_id = $row_package['customer_id'];
	$quotation_id = $row_package['quotation_id'];
	$service_name = "Package Invoice";			
	
	$cust_user_name = '';
	$sq_quo = mysqli_fetch_assoc(mysqlQuery("select user_id from package_tour_quotation_master where quotation_id='$quotation_id'"));
	if($sq_quo['user_id'] != 0){ 
		$row_user = mysqli_fetch_assoc(mysqlQuery("Select name from customer_users where user_id ='$sq_quo[user_id]'"));
		$cust_user_name = ' ('.$row_user['name'].')';
	}
	//**Service Tax
	$taxation_type = $row_package['taxation_type'];
	
	//basic amount
	$train_expense = $row_package['train_expense'];
	$plane_expense = $row_package['plane_expense'];
	$cruise_expense = $row_package['cruise_expense'];
	$visa_amount = $row_package['visa_amount'];
	$insuarance_amount = $row_package['insuarance_amount'];
	$tour_subtotal = $row_package['total_hotel_expense'] - $tour_esti;
	$basic_cost = $train_expense +$plane_expense +$cruise_expense +$visa_amount +$insuarance_amount +$tour_subtotal;

	//Service charge	
	$train_service_charge = $row_package['train_service_charge'];
	$plane_service_charge = $row_package['plane_service_charge'];
	$cruise_service_charge = $row_package['cruise_service_charge'];
	$visa_service_charge = $row_package['visa_service_charge'];
	$insuarance_service_charge = $row_package['insuarance_service_charge'];
	$service_charge = $train_service_charge +$plane_service_charge +$cruise_service_charge +$visa_service_charge +$insuarance_service_charge +$tour_subtotal;

	//service tax
	$train_service_tax = $row_package['train_service_tax'];
	$plane_service_tax = $row_package['plane_service_tax'];
	$cruise_service_tax = $row_package['cruise_service_tax'];
	$visa_service_tax = $row_package['visa_service_tax'];
	$insuarance_service_tax = $row_package['insuarance_service_tax'];
	$tour_service_tax = $row_package['tour_service_tax'];
	
	//service tax subtotal	
	$train_service_tax_subtotal = $row_package['train_service_tax_subtotal'];
	$plane_service_tax_subtotal = $row_package['plane_service_tax_subtotal'];
	$cruise_service_tax_subtotal = $row_package['cruise_service_tax_subtotal'];
	$visa_service_tax_subtotal = $row_package['visa_service_tax_subtotal'];
	$insuarance_service_tax_subtotal = $row_package['insuarance_service_tax_subtotal'];
	$tour_service_tax_subtotal = $row_package['tour_service_tax_subtotal'];
	$service_tax_subtotal = floatval($train_service_tax_subtotal) + floatval($plane_service_tax_subtotal) + floatval($cruise_service_tax_subtotal) + floatval($visa_service_tax_subtotal) + floatval($insuarance_service_tax_subtotal)+ floatval($tour_service_tax_subtotal);

	// Net amount
	$net_amount = 0;
	$tour_total_amount= ($row_package['actual_tour_expense']!="") ? $row_package['actual_tour_expense']: 0;
	$net_amount  =  $tour_total_amount + $row_package['total_travel_expense'] - $tour_esti;
	
	$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Package Tour'"));   
	$sac_code = $sq_sac['hsn_sac_code'];
	$tour_date = get_date_user($row_package['tour_from_date']);
	$destination_city = $row_package['tour_name'];

	if($app_invoice_format == 4)			
	$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/git_fit_tax_invoice.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=$taxation_type&train_expense=$train_expense&plane_expense=$plane_expense&cruise_expense=$cruise_expense&visa_amount=$visa_amount&insuarance_amount=$insuarance_amount&tour_subtotal=$tour_subtotal&train_service_charge=$train_service_charge&plane_service_charge=$plane_service_charge&cruise_service_charge=$cruise_service_charge&visa_service_charge=$visa_service_charge&insuarance_service_charge=$insuarance_service_charge&train_service_tax=$train_service_tax&plane_service_tax=$plane_service_tax&cruise_service_tax=$cruise_service_tax&visa_service_tax=$visa_service_tax&insuarance_service_tax=$insuarance_service_tax&tour_service_tax=$tour_service_tax&train_service_tax_subtotal=$train_service_tax_subtotal&plane_service_tax_subtotal=$plane_service_tax_subtotal&cruise_service_tax_subtotal=$cruise_service_tax_subtotal&visa_service_tax_subtotal=$visa_service_tax_subtotal&insuarance_service_tax_subtotal=$insuarance_service_tax_subtotal&tour_service_tax_subtotal=$tour_service_tax_subtotal&total_paid=$total_paid&net_amount=$net_amount&sac_code=$sac_code&branch_status=$branch_status&pass_count=$pass_count&tour_date=$tour_date&destination_city=$destination_city&booking_id=$row_package[booking_id]&credit_card_charges=$credit_card_charges";
	else
	$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/git_fit_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&quotation_id=$quotation_id&service_name=$service_name&taxation_type=$taxation_type&train_expense=$train_expense&plane_expense=$plane_expense&cruise_expense=$cruise_expense&visa_amount=$visa_amount&insuarance_amount=$insuarance_amount&tour_subtotal=$tour_subtotal&train_service_charge=$train_service_charge&plane_service_charge=$plane_service_charge&cruise_service_charge=$cruise_service_charge&visa_service_charge=$visa_service_charge&insuarance_service_charge=$insuarance_service_charge&train_service_tax=$train_service_tax&plane_service_tax=$plane_service_tax&cruise_service_tax=$cruise_service_tax&visa_service_tax=$visa_service_tax&insuarance_service_tax=$insuarance_service_tax&tour_service_tax=$tour_service_tax&train_service_tax_subtotal=$train_service_tax_subtotal&plane_service_tax_subtotal=$plane_service_tax_subtotal&cruise_service_tax_subtotal=$cruise_service_tax_subtotal&visa_service_tax_subtotal=$visa_service_tax_subtotal&insuarance_service_tax_subtotal=$insuarance_service_tax_subtotal&tour_service_tax_subtotal=$tour_service_tax_subtotal&total_paid=$total_paid&net_amount=$net_amount&sac_code=$sac_code&branch_status=$branch_status&tour_name=$tour_name&booking_id=$row_package[booking_id]&credit_card_charges=$credit_card_charges&tcs_tax=$row_package[tcs_tax]";

	// Booking Form
	$b_url = BASE_URL."model/app_settings/print_html/booking_form_html/package_tour.php?booking_id=$row_package[booking_id]&quotation_id=$quotation_id&branch_status=$branch_status&year=$year&credit_card_charges=$credit_card_charges";
	$discount_in = ($row_package['discount_in'] == 'Percentage') ? '%' : '';
	$discount_in = ($row_package['discount'] != 0) ? '('.$row_package['discount'].$discount_in.' Off)' : '';

	$balance_amount = $row_package['net_total'] + $credit_card_charges - $tour_esti;
	// currency conversion
	$currency_amount1 = currency_conversion($currency,$row_package['currency_code'],$balance_amount);
	if($row_package['currency_code'] !='0' && $currency != $row_package['currency_code']){
		$currency_amount = ' ('.$currency_amount1.')';
	}else{
		$currency_amount = '';
	}
	$temp_arr = array( "data" => array(
		
		(int)(++$count),
		get_package_booking_id($row_package['booking_id'],$year),
		$customer_name.$cust_user_name,
		$contact_no,
		$email_id,
		$sq_total_member,
		get_date_user($row_package['booking_date']),
		'<button class="btn btn-info btn-sm" id="packagev_btn-'. $row_package['booking_id'] .'" onclick="package_view_modal('. $row_package['booking_id'] .')" data-toggle="tooltip" title="View Details" id="view-'.$row_package['booking_id'] .'"><i class="fa fa-eye" aria-hidden="true"></i></button>',
		$row_package['tour_name'],
		get_date_user($row_package['tour_from_date']).' To '.get_date_user($row_package['tour_to_date']),
		number_format($row_package['basic_amount'],2),
		number_format($row_package['service_charge'],2).$discount_in,
		number_format($service_tax_amount,2),
		number_format($row_package['tcs_per'],2),
		number_format($row_package['tds'],2),
		number_format(floatval($credit_card_charges),2),
		number_format($tour_fee,2),
		number_format(floatval($tour_esti),2),
		number_format($total_amount,2).$currency_amount,
		number_format($total_paid,2),
		'<button class="btn btn-info btn-sm" id="paymentv_btn-'. $row_package['booking_id'] .'" onclick="payment_view_modal('.$row_package['booking_id'] .')"  data-toggle="tooltip" title="View Details" id="pview-'.$row_package['booking_id'] .'"><i class="fa fa-eye" aria-hidden="true"></i></button>',
		number_format(floatval($total_balance), 2),
		($row_package['due_date']=='1970-01-01')?get_date_user($row_package['booking_date']):get_date_user($row_package['due_date']),
		number_format(floatval($total_purchase),2),
		'<button class="btn btn-info btn-sm" id="supplierv_btn-'. $row_package['booking_id'] .'" onclick="supplier_view_modal('. $row_package['booking_id'] .')" data-toggle="tooltip" title="View Details" id="sview-'.$row_package['booking_id'] .'"><i class="fa fa-eye" aria-hidden="true"></i></button>',
		$branch_name,
		$emp_name,
		number_format(floatval($incentive_amount),2)
	), "bg" =>$bg);
	array_push($array_s,$temp_arr);
	
}
$footer_data = array("footer_data" => array(
	'total_footers' => 6,
	'foot0' => "",
	'col0' =>12,
	'class0' =>"",
	
	'foot1' => "TOTAL CANCEL : ".number_format($cancel_total,2),
	'col1' => 2,
	'class1' =>"danger text-right",

	'foot2' => "TOTAL SALE : ".number_format($sale_total,2),
	'col2' => 2,
	'class2' =>"info text-right",

	'foot3' => "TOTAL PAID : ".number_format($paid_total,2),
	'col3' => 3,
	'class3' =>"success text-right",

	'foot4' => "TOTAL BALANCE : ".number_format($balance_total,2),
	'col4' => 3,
	'class4' =>"warning text-right",

	'foot5' => "",
	'col5' =>11,
	'class5' =>""

	)
);
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>
	
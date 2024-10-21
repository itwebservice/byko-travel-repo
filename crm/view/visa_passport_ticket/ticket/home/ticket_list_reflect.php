<?php
include "../../../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_POST['financial_year_id'];
$branch_status = isset($_POST['branch_status']) ? $_POST['branch_status'] : '';
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$ticket_id = $_POST['ticket_id_filter'];
$cust_type = isset($_POST['cust_type']) ? $_POST['cust_type'] : '';
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';
$array_s = array();
$temp_arr = array();
$footer_data = array();

$query = "select * from ticket_master where financial_year_id='$financial_year_id' and delete_status='0'";
if($customer_id!=""){
	$query .= " and customer_id='$customer_id'";
}
if($ticket_id!="")
{
	$query .= " and ticket_id='$ticket_id'";
}
if($from_date!="" && $to_date!=""){
	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));
	$query .= " and created_at between '$from_date' and '$to_date'";
}		
if($cust_type != ""){
	$query .= " and customer_id in (select customer_id from customer_master where type = '$cust_type')";
}
if($company_name != ""){
	$query .= " and customer_id in (select customer_id from customer_master where company_name = '$company_name')";
}
if($role == "B2b"){
	$query .= " and emp_id='$emp_id'";
}
include "../../../../model/app_settings/branchwise_filteration.php";
$query .= " order by ticket_id desc ";
$count = 0;
$total_sale = 0;
$total_cancelation_amount = 0;
$total_balance = 0;
$sq_ticket = mysqlQuery($query);
while($row_ticket = mysqli_fetch_assoc($sq_ticket)){

	$sq_emp =  mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_ticket[emp_id]'"));
	$emp_name = ($row_ticket['emp_id'] != 0) ? $sq_emp['first_name'].' '.$sq_emp['last_name'] : 'Admin';
	$pass_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_ticket[ticket_id]'"));
	$cancel_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_ticket[ticket_id]' and status='Cancel'"));

	if($pass_count==$cancel_count){
		$bg="danger";
		$update_btn = '';
		$delete_btn = '';
	}
	else {
		$bg="";
		$update_btn = '
		<form method="POST" style="display:inline-block" data-toggle="tooltip" action="home/update/index.php">
			<input type="hidden" id="ticket_id" name="ticket_id" value="'. $row_ticket['ticket_id'] .'">
			<input type="hidden" id="branch_status" name="branch_status" value="'. $branch_status .'">
			<button data-toggle="tooltip" style="display:inline-block" class="btn btn-info btn-sm" title="Update Details"><i class="fa fa-pencil-square-o"></i></button>
		</form>';
		$delete_btn = '<button class="'.$delete_flag.' btn btn-danger btn-sm" onclick="delete_entry('.$row_ticket['ticket_id'].')" title="Delete Entry"><i class="fa fa-trash"></i></button>';
	}
	$cancel_type = $row_ticket['cancel_type'];
	if($cancel_type == 2 || $cancel_type == 3){
		$bg="warning";
		$delete_btn = '';
	} 
	$date = $row_ticket['created_at'];
	$yr = explode("-", $date);
	$year = $yr[0];
	$customer_name = '';
	$guest_name = '';
	$sq_customer_count = mysqli_num_rows(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
	if($sq_customer_count > 0){
		
		$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
		$sq_flight = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from ticket_master_entries where ticket_id= '$row_ticket[ticket_id]' "));
		$guest_name = (($sq_customer_info['type']=='Corporate' || $sq_customer_info['type'] == 'B2B') && isset($sq_flight['first_name'])) ? '('.$sq_flight['first_name'].' '.$sq_flight['last_name'].')' : '';
		if($sq_customer_info['type'] == 'Corporate'||$sq_customer_info['type'] == 'B2B'){
			$customer_name = $sq_customer_info['company_name'];
		}else{
			$customer_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
		}
	}

	$sq_paid_amount = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from ticket_payment_master where ticket_id='$row_ticket[ticket_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
	$credit_card_charges = $sq_paid_amount['sumc'];
		
	$paid_amount = $sq_paid_amount['sum'] + $credit_card_charges;
	$paid_amount = ($paid_amount == '')?0:$paid_amount;
	$sale_amount = $row_ticket['ticket_total_cost'] + $credit_card_charges;

	$cancel_amt = $row_ticket['cancel_amount'];
	if($cancel_amt==""){ $cancel_amt = 0;}
	
	$total_sale = $total_sale + $row_ticket['ticket_total_cost']+ $credit_card_charges;
	$total_cancelation_amount = $total_cancelation_amount + $cancel_amt;
	$total_balance = $total_balance + $sale_amount - $cancel_amt;
	$bal_amount = 0;
	if($row_ticket['cancel_type'] == '1'){
		if($paid_amount > 0){
			if($cancel_amt >0){
				if($paid_amount > $cancel_amt){
					$bal_amount = 0;
				}else{
					$bal_amount = $cancel_amt - $paid_amount + $credit_card_charges;
				}
			}else{
				$bal_amount = 0;
			}
		}
		else{
			$bal_amount = $cancel_amt;
		}
	}else if($row_ticket['cancel_type'] == '2'||$row_ticket['cancel_type'] == '3'){
		$cancel_estimate_data = json_decode($row_ticket['cancel_estimate']);
		$cancel_estimate = (!isset($cancel_estimate_data)) ? 0 : $cancel_estimate_data[0]->ticket_total_cost;
		$bal_amount = (($sale_amount - floatval($cancel_estimate)) + $cancel_amt) - $paid_amount;
	}
	else{
		$bal_amount = $sale_amount - $paid_amount;
	}

	$ticket_id = $row_ticket['ticket_id'];
	$invoice_no = get_ticket_booking_id($ticket_id,$year);
	$invoice_date = date('d-m-Y',strtotime($row_ticket['created_at']));
	$customer_id = $row_ticket['customer_id'];
	$service_name = "Flight Invoice";			
	//**Service tax
	$service_charge = $row_ticket['service_charge'];
	$service_tax = $row_ticket['service_tax_subtotal'];
	//Other taxes
	$other_tax = $row_ticket['other_taxes'];
	$yq_tax = $row_ticket['yq_tax'];
	//**Basic Cost
	$basic_cost1 = $row_ticket['basic_cost'] + $other_tax + $yq_tax;
	$basic_cost2 = $row_ticket['basic_cost'];

	$roundoff = $row_ticket['roundoff'];
	$bsmValues = $row_ticket['bsm_values'];
	$bsmValues = http_build_query(array('bsmValues' => $bsmValues));
	$tds = $row_ticket['tds'];
	$discount = $row_ticket['basic_cost_discount'];
	$markup = $row_ticket['markup'];
	$markup_tax = $row_ticket['service_tax_markup'];

	$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Flight'"));   
	$sac_code = $sq_sac['hsn_sac_code'];
	$net_amount = $row_ticket['ticket_total_cost'];
	$net_amount1 =  $row_ticket['basic_cost'] + $service_charge + $markup - $discount + $tds; 

	if($app_invoice_format == 4)
		$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/tax_invoice_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&service_charge=$service_charge&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&ticket_id=$ticket_id&total_paid=$paid_amount&balance_amount=$bal_amount&sac_code=$sac_code&branch_status=$branch_status&pass_count=$pass_count&$bsmValues&roundoff=$roundoff&tds=$tds&discount=$discount&markup=$markup&markup_tax=$markup_tax&net_amount1=$net_amount1&credit_card_charges=$credit_card_charges";
	else
		$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/flight_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost2&service_charge=$service_charge&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&ticket_id=$ticket_id&total_paid=$paid_amount&balance_amount=$bal_amount&sac_code=$sac_code&branch_status=$branch_status&credit_card_charges=$credit_card_charges&canc_amount=$cancel_amt&bg=$bg&cancel_type=$cancel_type";

	$btn_eticket = ($cancel_type != '1') ? '<a style="display:inline-block" onclick="loadOtherPage(\''. BASE_URL."model/app_settings/print_html/booking_form_html/flightTicket.php?ticket_id=$ticket_id&invoice_date=$invoice_date&branch_status=$branch_status" .'\')" class="btn btn-info btn-sm" title="Download E_Ticket"><i class="fa fa-print"></i></a>' : '';

	$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer_info['contact_no'], $secret_key);

	$temp_arr = array( "data" => array(
		$row_ticket['invoice_pr_id'],
		get_ticket_booking_id($row_ticket['ticket_id'],$year),
		$customer_name.$guest_name,
		$contact_no,
		number_format($row_ticket['ticket_total_cost'] + $credit_card_charges,2),
		$cancel_amt,
		number_format(($row_ticket['ticket_total_cost'] - $cancel_amt + $credit_card_charges), 2),
		$emp_name,
		$invoice_date,
		$btn_eticket.
		'<a style="display:inline-block" onclick="loadOtherPage(\''. $url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>
		'.$update_btn.'
		<button data-toggle="tooltip" style="display:inline-block" class="btn btn-info btn-sm" onclick="ticket_display_modal('.$row_ticket['ticket_id'] .')" id="display_ticket-'.$row_ticket['ticket_id'] .'" title="View Details"><i class="fa fa-eye" aria-hidden="true"></i></button>'.$delete_btn
		), "bg" =>$bg );
		array_push($array_s,$temp_arr);
	$count++;
}
$footer_data = array("footer_data" => array(
	'total_footers' => 4,
	'foot0' => "Total",
	'col0' => 4,
	'class0' => "text-right",
	'foot1' => number_format($total_sale, 2),
	'col1' => 1,
	'class1' => "info",
	'foot2' =>  number_format($total_cancelation_amount, 2),
	'col2' => 1,
	'class2' => "danger",
	'foot3' => number_format($total_balance, 2),
	'col3' => 1,
	'class3' => "success",
));
array_push($array_s, $footer_data);	
echo json_encode($array_s);	
?>	
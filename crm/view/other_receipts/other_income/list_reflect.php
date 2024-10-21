<?php
include "../../../model/model.php";

$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$income_type_id = $_POST['income_type_id'];
$financial_year_id = $_POST['financial_year_id'];

$query = "select * from other_income_master where 1 and delete_status='0'";
if($from_date!="" && $to_date!=""){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and receipt_date between '$from_date' and '$to_date'";
}
if($income_type_id!=""){
	$query .= " and income_type_id='$income_type_id' ";
}
if($financial_year_id!=""){
	$query .=" and financial_year_id='$financial_year_id'";
}
$query .=" order by income_id desc";
$array_s = array();
$temp_arr = array();
$footer_data = array();
$count = 0;
$bg;
$paid_amount = 0;
$sq_pending_amount = 0;
$sq_cancel_amount = 0;
$sq_income = mysqlQuery($query);
while($row_income = mysqli_fetch_assoc($sq_income)){

	$sq_income_type_info = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$row_income[income_type_id]'"));
	$sq_paid = mysqli_fetch_assoc(mysqlQuery("select * from other_income_payment_master where income_type_id='$row_income[income_id]'"));
	$paid_amount += $sq_paid['payment_amount'];

	$sq_paid_actual = mysqli_fetch_assoc(mysqlQuery("select payment_amount as sum from other_income_payment_master where income_type_id='$row_income[income_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));

	$year1 = explode("-", $sq_paid['payment_date']);
	$yr1 = $year1[0];
	$bg = '';
	$update_btn = '<button class="btn btn-info btn-sm" data-toggle="tooltip" title="Update Details" onclick="update_income_modal('. $sq_paid['payment_id'] .')" id="updateo_btn-'. $sq_paid['payment_id'] .'"><i class="fa fa-pencil-square-o"></i></button>';
	if($sq_paid['clearance_status']=="Pending"){ 
		$bg='warning';
		$sq_pending_amount = $sq_pending_amount + $sq_paid['payment_amount'];
		;
		$update_btn = '';
	}
	else if($sq_paid['clearance_status']=="Cancelled"){ 
		$bg='danger';
		$sq_cancel_amount = $sq_cancel_amount + $sq_paid['payment_amount'];
		;
		$update_btn = '';
	}
	//Receipt
	$payment_id_name = "Hotel Payment ID";
	$payment_id = get_other_income_payment_id($sq_paid['payment_id'],$yr1);
	$receipt_date = date('d-m-Y');
	$booking_id = $row_income['receipt_from'];
	$customer_id = 0;
	$booking_name = $sq_income_type_info['ledger_name'].'('.$row_income['particular'].')';
	$travel_date = 'NA';
	$payment_amount = ($sq_paid_actual['sum'] == '') ? 0 : $sq_paid_actual['sum'];
	$payment_mode1 = $sq_paid['payment_mode'];
	$transaction_id = $sq_paid['transaction_id'];
	$payment_date = date('d-m-Y',strtotime($sq_paid['payment_date']));
	$receipt_date = date('d-m-Y',strtotime($row_income['receipt_date']));
	$bank_name = $sq_paid['bank_name'];
	$receipt_type ="Other Income";
	$sq = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='other_receipts/index.php'"));
	$branch_status = $sq['branch_status'];
	$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Other Income'"));   
	$sac_code = $sq_sac['hsn_sac_code'];

	if(floatval($payment_amount) > 0){
		$url1 = BASE_URL."model/app_settings/print_html/receipt_html/receipt_body_html.php?payment_id_name=$payment_id_name&payment_id=$payment_id&receipt_date=$receipt_date&booking_id=$booking_id&customer_id=$customer_id&booking_name=$booking_name&travel_date=$travel_date&payment_amount=$payment_amount&transaction_id=$transaction_id&payment_date=$payment_date&receipt_date=$receipt_date&bank_name=$bank_name&confirm_by=&receipt_type=$receipt_type&payment_mode=$payment_mode1&branch_status=$branch_status&table_name=other_income_payment_master&customer_field=income_type_id&in_customer_id=$sq_paid[income_type_id]";
		$receipt_pdf = '<a onclick="loadOtherPage(\''. $url1 .'\')" data-toggle="tooltip" class="btn btn-info btn-sm" title="Download Receipt"><i class="fa fa-print"></i></a>';
	}else{
		$receipt_pdf = '';
	}

	//Invoice
	$invoice_no = $payment_id;
	$income_id = $payment_id;
	$customer_id = 0;
	$service_name = $sq_income_type_info['ledger_name'];
	$basic_cost = $row_income['amount'];
	$tds = $row_income['tds'];
	$net_amount = $row_income['total_fee'];
	$service_tax = $row_income['service_tax_subtotal'];
	$balance_amount = floatval($net_amount) - floatval($payment_amount);

	$invoice_url = BASE_URL."model/app_settings/print_html/invoice_html/body/other_income_body_html.php?invoice_no=$invoice_no&invoice_date=$payment_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&service_tax=$service_tax&net_amount=$net_amount&tds=$tds&total_paid=$payment_amount&balance_amount=$balance_amount&branch_status=$branch_status&income_id=$income_id&currency_code=$currency&sac_code=$sac_code";

	$temp_arr = array( "data" => array(
		(int)(++$count),
		$payment_id,
		$sq_income_type_info['ledger_name'],
		$row_income['receipt_from'],
		get_date_user($row_income['receipt_date']),
		($sq_paid['payment_mode']=='')?'NA':$sq_paid['payment_mode'],
		$row_income['particular'],
		$sq_paid['payment_amount'] ,
		'<a data-toggle="tooltip" onclick="loadOtherPage(\'' .$invoice_url .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>'.$receipt_pdf.$update_btn.'
		<button class="btn btn-info btn-sm" data-toggle="tooltip" onclick="entry_display_modal('. $row_income['income_id'] .')" title="View Details" id="viewo_btn-'. $row_income['income_id'] .'"><i class="fa fa-eye"></i></button>
		<button class="'.$delete_flag.' btn btn-danger btn-sm" onclick="delete_entry('.$row_income['income_id'].')" title="Delete Entry"><i class="fa fa-trash"></i></button>'
		), "bg" =>$bg);

	array_push($array_s,$temp_arr);
}

$footer_data = array("footer_data" => array(
	'total_footers' => 4,
	'foot0' => "Paid Amount: ".number_format($paid_amount, 2),
	'col0' => 2,
	'class0' => "info",
	'foot1' => "Pending Clearance : ".number_format($sq_pending_amount, 2),
	'col1' => 2,
	'class1' => "warning",
	'foot2' =>  "Cancelled: ".number_format($sq_cancel_amount, 2),
	'col2' => 2,
	'class2' => "danger",
	'foot3' => "Total Payment : ".number_format(($paid_amount - $sq_pending_amount - $sq_cancel_amount), 2),
	'col3' => 2,
	'class3' => "success",
	)
);
array_push($array_s, $footer_data);

echo json_encode($array_s);
?>
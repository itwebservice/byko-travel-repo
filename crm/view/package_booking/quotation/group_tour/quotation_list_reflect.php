<?php
include "../../../../model/model.php";
global $app_quot_format,$currency,$modify_entries_switch;

$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$booking_type = $_POST['booking_type'];
$tour_name = $_POST['tour_name'];
$quotation_id = isset($_POST['quotation_id']) ? $_POST['quotation_id'] : '';
$branch_status = isset($_POST['branch_status']) ? $_POST['branch_status'] : '';
$financial_year_id = isset($_POST['financial_year_id']) ? $_POST['financial_year_id'] : '';
$branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

if($status != ''){

	$query = "select * from group_tour_quotation_master where status='$status'";
}else{

	$query = "select * from group_tour_quotation_master where status='1' ";
}
if($financial_year_id!=""){
	$query .=" and financial_year_id='$financial_year_id'";
}

if($from_date!='' && $to_date!=""){

	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));

	$query .= " and quotation_date between '$from_date' and '$to_date' "; 
}
if($booking_type!=''){
	$query .= " and booking_type='$booking_type'";
}
if($tour_name!=''){
	$query .= " and tour_name='$tour_name'";
}
if($quotation_id!=''){
	$query .= " and quotation_id='$quotation_id'";

}
include "../../../../model/app_settings/branchwise_filteration.php";
if($branch_id!=""){
	$query .= " and branch_admin_id = '$branch_id'";
}
$query .=" order by quotation_id desc ";

	$count = 0;
	$quotation_cost = 0;
	$array_s = array();
	$temp_arr = array();
	$sq_quotation = mysqlQuery($query);
	while($row_quotation = mysqli_fetch_assoc($sq_quotation)){

		if($row_quotation['status'] == '0') {
			$bg = 'danger';
		}else if($row_quotation['clone'] == 'yes'){
			$bg = 'warning';
		} else{
			$bg = '';
		}
		$sq_emp =  mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_quotation[emp_id]'"));
		$emp_name = ($row_quotation['emp_id'] != 0) ? $sq_emp['first_name'].' '.$sq_emp['last_name'] : 'Admin';
		
		$quotation_date = $row_quotation['quotation_date'];
		$yr = explode("-", $quotation_date);
		$year =$yr[0];
		//Proforma Invoice
		$for = 'Group Tour'; 
		$invoice_no = get_quotation_id($row_quotation['quotation_id'],$year);
		$invoice_date = get_date_user($row_quotation['created_at']);
		$customer_id = $row_quotation['customer_name'];
		$customer_email = $row_quotation['email_id'];
		$service_name = "Proforma Invoice";

		$basic_cost = $row_quotation['tour_cost'] + $row_quotation['service_charge'];
		$service_tax =  $row_quotation['service_tax_subtotal'];
		$travel_cost = 0;
		$net_amount = $row_quotation['quotation_cost'];

		//Currency conversion
		$currency_amount1 = currency_conversion($currency,$row_quotation['currency_code'],$net_amount);
		if($row_quotation['currency_code'] !='0' && $currency != $row_quotation['currency_code']){
			$currency_amount = ' ('.$currency_amount1.')';
		}else{
			$currency_amount = '';
		}
		$p_url = BASE_URL."model/app_settings/print_html/invoice_html/body/proforma_invoice_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&customer_email=$customer_email&service_name=$service_name&basic_cost=$basic_cost&service_tax=$service_tax&net_amount=$net_amount&travel_cost=$travel_cost&for=$for&currency=$row_quotation[currency_code]";
							
		$quotation_id = $row_quotation['quotation_id'];
		if($app_quot_format == 2){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_2/git_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 3){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_3/git_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 4){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_4/git_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 5){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_5/git_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 6){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_6/git_quotation_html.php?quotation_id=$quotation_id";
		}
		else{
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_1/git_quotation_html.php?quotation_id=$quotation_id";
		} 
		$whatsapp_tooltip_change = ($whatsapp_switch == "on") ? 'Email and What\'sApp Quotation to Customer' : "Email Quotation to Customer";
		
		$copy_btn = ($row_quotation['status'] == '1') ? '<button class="btn btn-warning btn-sm" data-toggle="tooltip" onclick="quotation_clone('.$row_quotation['quotation_id'].')" title="Create copy of this quotation"><i class="fa fa-files-o"></i></button>': '';
		$pdf_btn = ($row_quotation['status'] == '1') ? '<a onclick="loadOtherPage(\''.$url1.'\')" data-toggle="tooltip" class="btn btn-info btn-sm" title="Download Quotation PDF"><i class="fa fa-print"></i></a>' : '';
		$mail_btn = ($row_quotation['status'] == '1') ? '<a href="javascript:void(0)" id="btn_email_'.$count.'" class="btn btn-info btn-sm" onclick="quotation_email_send(this.id, '.$row_quotation['quotation_id'].')" title="'.$whatsapp_tooltip_change.'"><i class="fa fa-envelope-o"></i></a>' : '';
		$mail_btn_b = ($row_quotation['status'] == '1') ? '<a href="javascript:void(0)" title="Email Quotation to Backoffice" id="email_backoffice_btn-'.$row_quotation['quotation_id'].'" class="btn btn-info btn-sm" onclick="quotation_email_send_backoffice_modal('.$row_quotation['quotation_id'].')"><i class="fa fa-paper-plane-o"></i></a>' : '';
		
		$update_btn = '<button class="btn btn-info btn-sm" data-toggle="tooltip" onclick="update_modal(\''.$row_quotation['quotation_id'].'\')" id="editq-\''.$row_quotation['quotation_id'].'\'" title="Update Details"><i class="fa fa-pencil-square-o"></i></button>';
		$to_date = $row_quotation['to_date'];
		$today = date('Y-m-d');
		if($to_date < $today && $modify_entries_switch == 'No' && $role != 'Admin' && $role != 'Branch Admin'){
			$update_btn = '';
		}
		$temp_arr = array( "data" => array(
			(int)(++$count),
			get_quotation_id($row_quotation['quotation_id'],$year),
			$row_quotation['tour_name'],
			$row_quotation['customer_name'],
			get_date_user($row_quotation['quotation_date']),
			number_format($row_quotation['quotation_cost'],2).$currency_amount,
			$emp_name,
			$pdf_btn.$mail_btn
			.$update_btn.$copy_btn.$mail_btn_b.'
			<a href="quotation_view.php?quotation_id='.$row_quotation['quotation_id'].'" target="_BLANK" class="btn btn-info btn-sm" title="View Details"><i class="fa fa-eye"></i></a>',

		), "bg" =>$bg);
		array_push($array_s,$temp_arr); 
		}
echo json_encode($array_s);
?>
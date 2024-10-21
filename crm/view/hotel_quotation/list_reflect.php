<?php
include "../../model/model.php";
global $app_quot_format,$whatsapp_switch,$currency;
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$quotation_id = $_POST['quotation_id'];
$status = $_POST['status'];
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_status = $_POST['branch_status'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_POST['financial_year_id'];

if($status != ''){

	$query = "select * from hotel_quotation_master where status='$status'";
}else{

	$query = "select * from hotel_quotation_master where status='1' ";
}
if($financial_year_id!=""){
	$query .=" and financial_year_id='$financial_year_id'";
}

if($from_date!='' && $to_date!=""){

	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));
	$query .= " and quotation_date between '$from_date' and '$to_date' "; 
}
if($quotation_id!=''){
	$query .= " and quotation_id='$quotation_id'";
}
include "../../model/app_settings/branchwise_filteration.php";
$query .=" order by quotation_id desc ";

$count = 0;
$array_s = array();
	$temp_arr = array();
	$quotation_cost = 0;
	$sq_quotation = mysqlQuery($query);
	while($row_quotation = mysqli_fetch_assoc($sq_quotation)){

		if($row_quotation['status'] == '0') {
			$bg = 'danger';
		}else if($row_quotation['clone'] == 1){
			$bg = 'warning';
		} else{
			$bg = '';
		}
		$sq_emp =  mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_quotation[emp_id]'"));
		$emp_name = ($row_quotation['emp_id'] != 0) ? $sq_emp['first_name'].' '.$sq_emp['last_name'] : 'Admin';
		$quotation_date = $row_quotation['quotation_date'];
		$yr = explode("-", $quotation_date);
		$year =$yr[0];

		$quotation_id = $row_quotation['quotation_id'];
		if($app_quot_format == 2){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_2/hotel_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 3){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_3/hotel_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 4){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_4/hotel_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 5){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_5/hotel_quotation_html.php?quotation_id=$quotation_id";
		}
		else if($app_quot_format == 6){
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_6/hotel_quotation_html.php?quotation_id=$quotation_id";
		}
		else{
			$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_1/hotel_quotation_html.php?quotation_id=$quotation_id";
		}
		$whatsapp_show = "";
		if($whatsapp_switch == "on"){
			$whatsapp_show = '<button class="btn btn-info btn-sm" onclick="quotation_whatsapp('.$row_quotation['quotation_id'].')" title="What\'sApp Quotation to customer" data-toggle="tooltip"><i class="fa fa-whatsapp"></i></button>';
		}
		$enq_details = json_decode($row_quotation['enquiry_details'], true);
		$cost_details = json_decode($row_quotation['costing_details'], true);
		
		$copy_btn = ($row_quotation['status'] == '1') ? '<button class="btn btn-warning btn-sm" onclick="quotation_clone('.$row_quotation['quotation_id'].')" title="Create Copy of this Quotation" data-toggle="tooltip"><i class="fa fa-files-o"></i></button>' : '';

		$pdf_btn = ($row_quotation['status'] == '1') ? '<a data-toggle="tooltip" onclick="loadOtherPage(\''.$url1.'\')" class="btn btn-info btn-sm" title="Download Quotation PDF"><i class="fa fa-print"></i></a> <button class="btn btn-info btn-sm" onclick="send_mail(\''.trim($enq_details['email_id']).'\',\''.$row_quotation['quotation_id'].'\')" id="email-'.$row_quotation['quotation_id'].'" title="Email Quotation to Customer" data-toggle="tooltip"><i class="fa fa-envelope-o"></i></button>' : '';

		$whatsapp_show = ($row_quotation['status'] == '1') ? '<button class="btn btn-info btn-sm" onclick="quotation_whatsapp('.$row_quotation['quotation_id'].')" id="whatsapp-'.$row_quotation['quotation_id'].'" title="What\'sApp Quotation to customer" data-toggle="tooltip"><i class="fa fa-whatsapp"></i></button>' : '';

		$temp_arr = array( "data" => array(
			(int)(++$count),
			get_quotation_id($row_quotation['quotation_id'],$year),
			get_date_user($row_quotation['quotation_date']),
			$enq_details['customer_name'],
			$emp_name,
			$pdf_btn.'<form  style="display:inline-block" action="update/index.php" id="frm_booking_'.$count.'" method="POST">
			<input  style="display:inline-block" type="hidden" id="quotation_id" name="quotation_id" value="'.$row_quotation['quotation_id'].'">
			<button data-toggle="tooltip"  style="display:inline-block" class="btn btn-info btn-sm" id="edit-'.$row_quotation['quotation_id'].'" title="Update Details"><i class="fa fa-pencil-square-o"></i></button>
			</form>'.$copy_btn.$whatsapp_show.'<form  style="display:inline-block" action="quotation_view.php" target="_blank" id="frm_booking_view_'.$count.'" method="GET">
				<input style="display:inline-block" type="hidden" id="quotation_id" name="quotation_id" value="'.$row_quotation['quotation_id'].'">
				<button data-toggle="tooltip"  style="display:inline-block" class="btn btn-info btn-sm" title="View Details" id="view-'.$row_quotation['quotation_id'].'"><i class="fa fa-eye"></i></button>
			</form>',
		), "bg" =>$bg);
		array_push($array_s,$temp_arr); 
}
echo json_encode($array_s);
?>
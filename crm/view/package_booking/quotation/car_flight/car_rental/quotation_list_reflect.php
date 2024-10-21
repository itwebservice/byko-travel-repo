<?php
include "../../../../../model/model.php";
global $app_quot_format;
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

	$query = "select * from car_rental_quotation_master where status='$status'";
}else{

	$query = "select * from car_rental_quotation_master where status='1' ";
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
include "../../../../../model/app_settings/branchwise_filteration.php";
$query .=" order by quotation_id desc ";
$count = 0;
$quotation_cost = 0;
$array_s = array();
$temp_arr = array();
$sq_quotation = mysqlQuery($query);
while($row_quotation = mysqli_fetch_assoc($sq_quotation)){

	$sq_emp =  mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_quotation[emp_id]'"));
	$emp_name = ($row_quotation['emp_id'] != 0) ? $sq_emp['first_name'].' '.$sq_emp['last_name'] : 'Admin';

	$quotation_date = $row_quotation['quotation_date'];
	$yr = explode("-", $quotation_date);
	$year =$yr[0];
	$sq_cost =  mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id = '$row_quotation[quotation_id]'"));
	$quotation_id = $row_quotation['quotation_id'];
	if($app_quot_format == 2){
		$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_2/car_rental_quotation_html.php?quotation_id=$quotation_id";
	}
	else if($app_quot_format == 3){
		$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_3/car_rental_quotation_html.php?quotation_id=$quotation_id";
	}
	else if($app_quot_format == 4){
		$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_4/car_rental_quotation_html.php?quotation_id=$quotation_id";
	}
	else if($app_quot_format == 5){
		$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_5/car_rental_quotation_html.php?quotation_id=$quotation_id";
	}
	else if($app_quot_format == 6){
		$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_6/car_rental_quotation_html.php?quotation_id=$quotation_id";
	}
	else{
		$url1 = BASE_URL."model/app_settings/print_html/quotation_html/quotation_html_1/car_rental_quotation_html.php?quotation_id=$quotation_id";
	}
	$whatsapp_show = "";
	if($whatsapp_switch == "on"){
		$whatsapp_show = '<button class="btn btn-info btn-sm" onclick="quotation_whatsapp('.$row_quotation['quotation_id'].')" title="What\'sApp Quotation to customer" data-toggle="tooltip"><i class="fa fa-whatsapp"></i></button>';
	}
	if($row_quotation['status'] == '0') {
		$bg = 'danger';
		$pdf_show = '';
		$whatsapp_show = '';
	}else{
		$bg = '';
		$pdf_show = '<a onclick="loadOtherPage(\''.$url1.'\')" data-toggle="tooltip" class="btn btn-info btn-sm" title="Download Quotation PDF"><i class="fa fa-print"></i></a>';
		$whatsapp_show = '<button class="btn btn-info btn-sm" onclick="quotation_whatsapp('.$row_quotation['quotation_id'].')" title="What\'sApp Quotation to customer" data-toggle="tooltip"><i class="fa fa-whatsapp"></i></button>';
	}
	$temp_arr = array( "data" => array(
		(int)(++$count),
		get_quotation_id($row_quotation['quotation_id'],$year),
		$row_quotation['customer_name'],
		get_date_user($row_quotation['quotation_date']),
		$row_quotation['total_tour_cost'],
		$emp_name,
		$pdf_show.$whatsapp_show.'
		
		<button class="btn btn-info btn-sm" onclick="update_modal('.$row_quotation['quotation_id'].')" id="edit-'.$row_quotation['quotation_id'] .'" title="Update Details" data-toggle="tooltip"><i class="fa fa-pencil-square-o"></i></button>

		<a data-toggle="tooltip" href="quotation_view.php?quotation_id='.$row_quotation['quotation_id'].'" target="_BLANK" class="btn btn-info btn-sm" title="View Details"><i class="fa fa-eye"></i></a>'
	), "bg" =>$bg);
	array_push($array_s,$temp_arr); 
}
echo json_encode($array_s);			
?>
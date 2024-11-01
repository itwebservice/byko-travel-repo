<?php include "../../../model/model.php";
global $app_quot_format,$whatsapp_switch,$currency;

$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$customer_id = $_POST['customer_id'];
$q = "select branch_status from branch_assign where link='b2b_customer/quotation/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';

$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];

$query = "select * from b2b_quotations where 1 and status=''";
if($from_date!='' && $to_date!=""){
	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));
	$query .= " and created_at between '$from_date' and '$to_date' "; 
}
if($customer_id!=''){
	$query .= " and register_id in(select register_id from b2b_registration where customer_id = '$customer_id')";
}
if($branch_status=='yes'){
	if($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
		$query .= " and branch_admin_id = '$branch_admin_id'";
	}
}
$query .=" order by quotation_id desc ";

$count = 1;
$array_s = array();
	$temp_arr = array();
	$quotation_cost = 0;
	$sq_quotation = mysqlQuery($query);
	while($row_quotation = mysqli_fetch_assoc($sq_quotation)){

		$sq_customer =  mysqli_fetch_assoc(mysqlQuery("select company_name from b2b_registration where register_id = '$row_quotation[register_id]'"));

		$cart_list_arr = $row_quotation['cart_list_arr'];
		$pdf_data_array = json_decode($row_quotation['pdf_data_array']);
		$cust_name = $pdf_data_array[0]->cust_name;
		
		$markup_in = $pdf_data_array[0]->markup_in;
		$markup_amount = $pdf_data_array[0]->markup_amount;
		$tax_in = $pdf_data_array[0]->tax_in;
		$tax_amount = $pdf_data_array[0]->tax_amount;
		$grand_total = $pdf_data_array[0]->grand_total;
		if($markup_in == 'Percentage'){
			$markup = $grand_total*($markup_amount/100);
		}
		else{
			$markup = $markup_amount;
		}
		$grand_total += $markup;
		if($tax_in == 'Percentage'){
			$tax_amt = ($grand_total*($tax_amount/100));
		}
		else{
			$tax_amt = $tax_amount;
		}
		$quotation_cost = $grand_total + $tax_amt;
		
		$pdf_data_array = json_encode($pdf_data_array);
		$currency = $row_quotation['currency'];
		$url1 = BASE_URL.'model/app_settings/print_html/quotation_html/quotation_html_2/b2b_quotation_html.php?pdf_data_array='.urlencode($pdf_data_array).'&cart_list_arr='.urlencode($cart_list_arr).'&flag_value=true'.'&quotation_currency='.$currency.'&created_at='.get_date_user($row_quotation['created_at']);
		
		$sq_curr = mysqli_fetch_assoc(mysqlQuery("select currency_code from currency_name_master where id='$currency'"));
		if($row_quotation['agent_flag'] == '0'){
			$sq_agent = mysqli_fetch_assoc(mysqlQuery("select full_name from b2b_users where id='$row_quotation[user_id]'"));
			$cust_name1 = ' ('.$sq_agent['full_name'].')';
		}else{
			$cust_name1 = '';
		}
		$temp_arr = array(
			$count++,
			get_date_user($row_quotation['created_at']),
			$sq_customer['company_name'].$cust_name1,
			$cust_name,
			$sq_curr['currency_code'].' '.number_format($quotation_cost,2),
			'<a data-toggle="tooltip" onclick="loadOtherPage(\''.$url1.'\')" class="btn btn-info btn-sm" title="Download Quotation PDF"><i class="fa fa-print"></i></a>
			<button style="display:inline-block" class="btn btn-danger btn-sm" onclick="delete_quotation('.$row_quotation['quotation_id'] .')" title="Delete Quotation" data-toggle="tooltip"><i class="fa fa-trash" aria-hidden="true"></i></button>',
		);
		array_push($array_s,$temp_arr); 
}
echo json_encode($array_s);
?>
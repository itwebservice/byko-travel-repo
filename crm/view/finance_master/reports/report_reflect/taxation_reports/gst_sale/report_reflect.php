<?php
include "../../../../../../model/model.php";
include_once('../gst_sale/sale_generic_functions.php');

$branch_status = $_POST['branch_status'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];

$array_s = array();
$temp_arr = array();
$tax_total = 0;
$markup_tax_total = 0;
$count = 1;
$sq_setting = mysqli_fetch_assoc(mysqlQuery("select * from app_settings where setting_id='1'"));
$sq_supply = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_setting[state_id]'"));

//GIT Booking
$query = "select * from tourwise_traveler_details where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and DATE(form_date) between '$from_date' and '$to_date' ";
}
$query .= " order by id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as booking_count from travelers_details where traveler_group_id ='$row_query[traveler_group_id]'"));

	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as cancel_count from travelers_details where traveler_group_id ='$row_query[traveler_group_id]' and status ='Cancel'"));
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
	if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
		$cust_name = $sq_cust['company_name'];
	}else{
		$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
	}

	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'] && $row_query['tour_group_status']!="Cancel")
	{
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		$hsn_code = get_service_info('Group Tour');

		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if($row_query['service_tax'] !== 0.00 && ($row_query['service_tax']) !== ''){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;
		
		$yr = explode("-",$row_query['form_date']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Group Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_group_booking_id($row_query['id'],$yr[0]) ,
			get_date_user($row_query['form_date']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			number_format($row_query['net_total'],2) ,
			number_format($row_query['net_total']-$row_query['roundoff']-$service_tax_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'] || $row_query['tour_group_status']=="Cancel")
	{
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		$hsn_code = get_service_info('Group Tour');

		$sq_tour_c = mysqli_num_rows(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$row_query[id]'"));
		if($sq_tour_c != 0)
			$sq_tour_info = mysqli_fetch_assoc(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$row_query[id]'"));
		else
			$sq_tour_info = mysqli_fetch_assoc(mysqlQuery("select * from refund_traveler_estimate where tourwise_traveler_id='$row_query[id]'"));
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($sq_tour_info['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$sq_tour_info['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;
		
		$yr = explode("-",$row_query['form_date']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Group Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_group_booking_id($row_query['id'],$yr[0]) ,
			get_date_user($row_query['form_date']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			number_format($row_query['net_total'],2) ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
}
//FIT Booking
$query = "select * from package_tour_booking_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and booking_date between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as booking_count from package_travelers_details where booking_id ='$row_query[booking_id]'"));
	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as cancel_count from package_travelers_details where booking_id ='$row_query[booking_id]' and status ='Cancel'"));

	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
    	$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
    	$hsn_code = get_service_info('Package Tour');
		$tax_per = 0;
		$service_tax_amount = 0; //Service tax
		$tax_name = 'NA';
		if(isset($row_query['tour_service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['tour_service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		//Taxable amount
		$taxable_amount = ($tax_per != 0) ? ($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['booking_date']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Package Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_package_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['booking_date']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			number_format($row_query['net_total'],2) ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$sq_cancel_bookingc = mysqli_num_rows(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_query[booking_id]'"));
		if($sq_cancel_bookingc > 0){

			$sq_cancel_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_query[booking_id]'"));
			$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
			$hsn_code = get_service_info('Package Tour');
			$tax_per = 0;
			$service_tax_amount = 0; //Service tax
			$tax_name = 'NA';
			if(isset($sq_cancel_booking['tax_amount'])){
				$service_tax_subtotal1 = explode(',',$sq_cancel_booking['tax_amount']);
				$tax_name = '';
				for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
					$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
					$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
					$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
					$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
					$service_tax_amount += $gst_amount;
					$tax_name .= $gst_amount0 . $gst_amount1.' ';
					$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
				}
			}
			//Markup Tax
			$markup_tax_amount = 0;
			$markup_tax_name = 'NA';
			$markup = 'NA';
			//Taxable amount
			$taxable_amount = ($tax_per != 0) ? ($service_tax_amount / $tax_per) * 100:0;
			$tax_total += $service_tax_amount;
			$markup_tax_total += $markup_tax_amount;

			$yr = explode("-",$row_query['booking_date']);
			$temp_arr = array( "data" => array(
				(int)($count++),
				"Package Booking",
				$hsn_code ,
				$cust_name,
				($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
				($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
				get_package_booking_id($row_query['booking_id'],$yr[0]) ,
				get_date_user($row_query['booking_date']),
				($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
				($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
				number_format($row_query['net_total'],2) ,
				number_format($taxable_amount,2) ,
				$tax_name,
				number_format($service_tax_amount,2),
				$markup,
				$markup_tax_name,
				number_format($markup_tax_amount,2),
				"0.00",
				"0.00",
				"",
				""
			), "bg" =>'danger');
			array_push($array_s,$temp_arr);
		}
	}
}
//Visa Booking
$query = "select * from visa_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by visa_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from visa_master_entries where visa_id ='$row_query[visa_id]'"));
	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from visa_master_entries where visa_id ='$row_query[visa_id]' and status ='Cancel'"));
	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		
		$hsn_code = get_service_info('Visa');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		if($row_query['markup_tax'] !== 0.00 && ($row_query['markup_tax']) !== ''){
			$markup_tax_subtotal1 = explode(',',$row_query['markup_tax']);
			$markup_tax_name = '';
			for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
				$markup_tax = explode(':',$markup_tax_subtotal1[$i]);
				$markup_tax_amount +=  $markup_tax[2];
				$markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
			}
		}
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Visa Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_visa_booking_id($row_query['visa_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['visa_total_cost'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		
		$hsn_code = get_service_info('Visa');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Visa Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_visa_booking_id($row_query['visa_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['visa_total_cost'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
}
//Bus Booking
$query = "select * from bus_booking_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from bus_booking_entries where booking_id ='$row_query[booking_id]'"));

	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from bus_booking_entries where booking_id ='$row_query[booking_id]' and status ='Cancel'"));
	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Bus');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));

		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		if($row_query['markup_tax'] !== 0.00 && ($row_query['markup_tax']) !== ''){
			$markup_tax_subtotal1 = explode(',',$row_query['markup_tax']);
			$markup_tax_name = '';
			for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
				$markup_tax = explode(':',$markup_tax_subtotal1[$i]);
				$markup_tax_amount +=  $markup_tax[2];
				$markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
			}
		}
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;
		
		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Bus Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_bus_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['net_total'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Bus');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));

		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;
		
		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Bus Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_bus_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['net_total'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
}
//Activity Booking
$query = "select * from excursion_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by exc_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
 	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from excursion_master_entries where exc_id ='$row_query[exc_id]'"));
 	//Cancelled count
 	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from excursion_master_entries where exc_id ='$row_query[exc_id]' and status ='Cancel'"));
 	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
	{
    	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
    	if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
    		$cust_name = $sq_cust['company_name'];
    	}else{
    		$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
    	}
    	$hsn_code = get_service_info('Excursion');
    	$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		if(isset($row_query['service_tax_markup'])){
			$markup_tax_subtotal1 = explode(',',$row_query['service_tax_markup']);
			$markup_tax_name = '';
			for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
				$markup_tax = explode(':',$markup_tax_subtotal1[$i]);
				$markup_tax_amount +=  $markup_tax[2];
				$markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
			}
		}
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Activity Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_exc_booking_id($row_query['exc_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['exc_total_cost'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Excursion');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Activity Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_exc_booking_id($row_query['exc_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['exc_total_cost'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
}

//Hotel Booking
$query = "select * from hotel_booking_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}	
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from hotel_booking_entries where booking_id ='$row_query[booking_id]'"));

	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from hotel_booking_entries where booking_id ='$row_query[booking_id]' and status ='Cancel'"));
	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
	{
    	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
    	if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
    		$cust_name = $sq_cust['company_name'];
    	}else{
    		$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
    	}
    	$hsn_code = get_service_info('Hotel / Accommodation');
    	$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		if($row_query['markup_tax'] !== 0.00 && ($row_query['markup_tax']) !== ''){
			$markup_tax_subtotal1 = explode(',',$row_query['markup_tax']);
			$markup_tax_name = '';
			for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
				$markup_tax = explode(':',$markup_tax_subtotal1[$i]);
				$markup_tax_amount +=  $markup_tax[2];
				$markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
			}
		}
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Hotel Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_hotel_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['total_fee'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count']){

	   	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Hotel / Accommodation');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Hotel Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_hotel_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['total_fee'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
}

//Car Rental Booking
$query = "select * from car_rental_booking where delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	if($row_query['status'] != 'Cancel'){
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Car Rental');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'] )){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = ($row_query['markup_cost'] == '' || $row_query['markup_cost'] == '0') ? 'NA' : number_format($row_query['markup_cost'],2);
		if(isset($row_query['markup_cost_subtotal'])){
			$markup_tax_subtotal1 = explode(',',$row_query['markup_cost_subtotal']);
			$markup_tax_name = '';
			for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
				$markup_tax = explode(':',$markup_tax_subtotal1[$i]);
				$markup_tax_amount +=  $markup_tax[2];
				$markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
			}
		}
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Car Rental Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_car_rental_booking_id($row_query['booking_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['total_fees'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($row_query['status'] == 'Cancel'){
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Car Rental');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Car Rental Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_car_rental_booking_id($row_query['booking_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['total_fees'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
} 
//Flight Booking
$query = "select * from ticket_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by ticket_id desc";

$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	$cancel_type = $row_query['cancel_type'];
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from ticket_master_entries where ticket_id ='$row_query[ticket_id]'"));
	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from ticket_master_entries where ticket_id ='$row_query[ticket_id]' and status ='Cancel'"));
	if($cancel_type == 2 || $cancel_type == 3 || $cancel_type == 0)
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Flight');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));

		if($cancel_type == 2 || $cancel_type == 3){
			$bg="warning";
		} else{
			$bg = '';
		}
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		if(isset($row_query['service_tax_markup'])){
			$markup_tax_subtotal1 = explode(',',$row_query['service_tax_markup']);
			$markup_tax_name = '';
			for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
				$markup_tax = explode(':',$markup_tax_subtotal1[$i]);
				$markup_tax_amount +=  $markup_tax[2];
				$markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
			}
		}
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Ticket Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_ticket_booking_id($row_query['ticket_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['ticket_total_cost'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>$bg);
		array_push($array_s,$temp_arr);
	}
    if($row_query['cancel_flag'] == 1 && $cancel_type != 0)
    {
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
		  $cust_name = $sq_cust['company_name'];
		}else{
		  $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Flight');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		$bg="";
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;
		$yr = explode("-",$row_query['created_at']);
		if($cancel_type == 2 || $cancel_type == 3){
			$bg="warning";
		} else if($cancel_type == 1){
			$bg = 'danger';
		}
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Ticket Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_ticket_booking_id($row_query['ticket_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['ticket_total_cost'] ,
			number_format($row_query['cancel_amount'],2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>$bg);
		array_push($array_s,$temp_arr);
	}
} 
//Train Booking
$query = "select * from train_ticket_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by train_ticket_id desc";
$bg = '';
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from train_ticket_master_entries where train_ticket_id ='$row_query[train_ticket_id]'"));

	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from train_ticket_master_entries where train_ticket_id ='$row_query[train_ticket_id]' and status ='Cancel'"));
	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Train');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));

		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Train Ticket Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_train_ticket_booking_id($row_query['train_ticket_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['net_total'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Train');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));

		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Train Ticket Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_train_ticket_booking_id($row_query['train_ticket_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['net_total'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
} 
//Miscellaneous Booking
$query = "select * from miscellaneous_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
$from_date = get_date_db($from_date);
$to_date = get_date_db($to_date);
$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by misc_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	//Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from miscellaneous_master_entries where misc_id ='$row_query[misc_id]'"));

	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from miscellaneous_master_entries where misc_id ='$row_query[misc_id]' and status ='Cancel'"));
	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Miscellaneous');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['service_tax_subtotal'])){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = (!isset($row_query['markup']) ) ? 'NA' : number_format($row_query['markup'],2);
		if(isset($row_query['service_tax_markup'])){
			$markup_tax_subtotal1 = explode(',',$row_query['service_tax_markup']);
			$markup_tax_name = '';
			for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
				$markup_tax = explode(':',$markup_tax_subtotal1[$i]);
				$markup_tax_amount +=  $markup_tax[2];
				$markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
			}
		}
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Miscellaneous Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_misc_booking_id($row_query['misc_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['misc_total_cost'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	} 
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$hsn_code = get_service_info('Miscellaneous');
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if(isset($row_query['tax_amount'])){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = 'NA';
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"Miscellaneous Booking" ,
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_misc_booking_id($row_query['misc_id'],$yr[0])  ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$row_query['misc_total_cost'] ,
			number_format($taxable_amount,2) ,
			$tax_name,
			number_format($service_tax_amount,2),
			$markup,
			$markup_tax_name,
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	} 
}
//B2C Booking
$query = "select * from b2c_sale where 1";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	if($row_query['status'] != 'Cancel')
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		if($row_query['service'] == 'Holiday'){
			$service = 'Package Tour';
		}else{
			$service = $row_query['service'];
		}
		$hsn_code = get_service_info($service);
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$costing_data = json_decode($row_query['costing_data']);

		$total_cost = $costing_data[0]->total_cost;
		$total_tax = $costing_data[0]->total_tax;
		$net_total = $costing_data[0]->net_total;
		$taxes = explode(',',$total_tax);
		$tax_amount = 0;
		$tax_string = '';
		for($i=0; $i<sizeof($taxes);$i++){
			$single_tax = explode(':',$taxes[$i]);
			$tax_amount += floatval($single_tax[1]);
			$temp_tax = explode(' ',$single_tax[1]);
			$tax_string .= $single_tax[0].$temp_tax[1];
		}
		$grand_total = $costing_data[0]->grand_total;
		$coupon_amount = $costing_data[0]->coupon_amount;
		$coupon_amount = ($coupon_amount!='')?$coupon_amount:0;
		$net_total = $costing_data[0]->net_total;
		$markup_tax_amount = 0;

		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"B2C Booking (".$row_query['service'].')',
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_b2c_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$net_total ,
			number_format($total_cost,2) ,
			$tax_string,
			number_format($tax_amount,2),
			'NA',
			'NA',
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'');
		array_push($array_s,$temp_arr);
	}
	if($row_query['status'] == 'Cancel')
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		if($row_query['service'] == 'Holiday'){
			$service = 'Package Tour';
		}else{
			$service = $row_query['service'];
		}
		$hsn_code = get_service_info($service);
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$costing_data = json_decode($row_query['costing_data']);

		$total_cost = $row_query['cancel_amount'];
		$net_total = $costing_data[0]->net_total;
		//Service tax
		$tax_per = 0;
		$tax_amount = 0;
		$tax_name = 'NA';
		if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		$markup_tax_amount = 0;

		$tax_total += $tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);
		$temp_arr = array( "data" => array(
			(int)($count++),
			"B2C Booking (".$row_query['service'].')',
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_b2c_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			$net_total ,
			number_format($total_cost,2) ,
			$tax_name,
			number_format($tax_amount,2),
			'NA',
			'NA',
			number_format($markup_tax_amount,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
}

//Get default currency rate
global $currency;
$sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
$to_currency_rate = $sq_to['currency_rate'];
//B2B Booking
$query = "select * from b2b_booking_master where 1";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and DATE(created_at) between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	if($row_query['status'] != 'Cancel')
	{
		$yr = explode("-",$row_query['created_at']);
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$cart_checkout_data = json_decode($row_query['cart_checkout_data']);
		$traveller_details = ($row_query['traveller_details']!='' && $row_query['traveller_details']!='null')?json_decode($row_query['traveller_details']):[];

		$hotel_list_arr = array();
		$transfer_list_arr = array();
		$activity_list_arr = array();
		$tours_list_arr = array();
		$ferry_list_arr = array();
		$group_list_arr = array();

		for($i=0;$i<sizeof($cart_checkout_data);$i++){
			if($cart_checkout_data[$i]->service->name == 'Hotel'){
				array_push($hotel_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Transfer'){
				array_push($transfer_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Activity'){
				array_push($activity_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
				array_push($tours_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Ferry'){
				array_push($ferry_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Group Tours'){
				array_push($group_list_arr,$cart_checkout_data[$i]);
			}
		}

		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		// Hotel
		if(sizeof($hotel_list_arr)>0){

			for($i=0;$i<sizeof($hotel_list_arr);$i++){
				$hsn_code = get_service_info('Hotel / Accommodation');
				$tax_arr = explode(',',$hotel_list_arr[$i]->service->hotel_arr->tax);
				for($j=0;$j<sizeof($hotel_list_arr[$i]->service->item_arr);$j++){
					
					$room_types = explode('-',$hotel_list_arr[$i]->service->item_arr[$j]);
					$room_no = $room_types[0];
					$room_cost = $room_types[2];
					$h_currency_id = $room_types[3];
					$tax_name = '';
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].')';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
					$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
			$tax_total += $tax_amount1;
				$temp_arr = array( "data" => array(
					(int)($count++),
					"B2B Booking (Hotel)",
					$hsn_code ,
					$cust_name,
					($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
					($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
					get_b2b_booking_id($row_query['booking_id'],$yr[0]) ,
					get_date_user($row_query['created_at']),
					($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
					($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
					number_format($total_amount1,2),
					number_format($room_cost1,2) ,
					$tax_name,
					number_format($tax_amount1,2),
					'NA',
					'NA',
					number_format(0,2),
					"0.00",
					"0.00",
					"",
					""
				), "bg" =>'');
				array_push($array_s,$temp_arr);
		}
		// Activity
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($activity_list_arr)>0){

			$hsn_code = get_service_info('Excursion');
			for($i=0;$i<sizeof($activity_list_arr);$i++){

				$tax_amount = 0;	
				$tax_arr = explode(',',$activity_list_arr[$i]->service->service_arr[0]->taxation);
				$transfer_types = explode('-',$activity_list_arr[$i]->service->service_arr[0]->transfer_type);
				$transfer = $transfer_types[0];
				$room_cost = $transfer_types[1];
				$h_currency_id = $transfer_types[2];
				$tax_name = '';
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] === "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							$tax_name .= $tax_arr2[0] .' ('.$tax_arr2[1].')';
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
				$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
			$tax_total += $tax_amount1;
			$temp_arr = array( "data" => array(
				(int)($count++),
				"B2B Booking (Activity)",
				$hsn_code ,
				$cust_name,
				($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
				($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
				get_b2b_booking_id($row_query['booking_id'],$yr[0]) ,
				get_date_user($row_query['created_at']),
				($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
				($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
				number_format($total_amount1,2) ,
				number_format($room_cost1,2) ,
				$tax_name,
				number_format($tax_amount1,2),
				'NA',
				'NA',
				number_format(0,2),
				"0.00",
				"0.00",
				"",
				""
			), "bg" =>'');
			array_push($array_s,$temp_arr);
		}
		// Transfer
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($transfer_list_arr)>0){

			$hsn_code = get_service_info('Car Rental');
			for($i=0;$i<sizeof($transfer_list_arr);$i++){

				$services = ($transfer_list_arr[$i]->service!='') ? $transfer_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){
					$tax_name = '';
					$tax_arr = explode(',',$services->service_arr[$j]->taxation);
					$transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
					$room_cost = $transfer_cost[0];
					$h_currency_id = $transfer_cost[1];
					$tax_amount = 0;
					
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
					$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
			$tax_total += $tax_amount1;
			$temp_arr = array( "data" => array(
				(int)($count++),
				"B2B Booking (Transfer)",
				$hsn_code ,
				$cust_name,
				($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
				($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
				get_b2b_booking_id($row_query['booking_id'],$yr[0]) ,
				get_date_user($row_query['created_at']),
				($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
				($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
				number_format($total_amount1,2) ,
				number_format($room_cost1,2) ,
				$tax_name,
				number_format($tax_amount1,2),
				'NA',
				'NA',
				number_format(0,2),
				"0.00",
				"0.00",
				"",
				""
			), "bg" =>'');
			array_push($array_s,$temp_arr);
		}
		// Combo Tour
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($tours_list_arr)>0){

			$hsn_code = get_service_info('Package Tour');
			for($i=0;$i<sizeof($tours_list_arr);$i++){
				$tax_name = '';
				$tax_amount = 0;
				$tax_arr = explode(',',$tours_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$tours_list_arr[$i]->service->service_arr[0]->package_type);
				$room_cost = $package_item[1];
				$h_currency_id = $package_item[2];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
				$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
			$tax_total += $tax_amount1;
			$temp_arr = array( "data" => array(
				(int)($count++),
				"B2B Booking (Holiday)",
				$hsn_code ,
				$cust_name,
				($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
				($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
				get_b2b_booking_id($row_query['booking_id'],$yr[0]) ,
				get_date_user($row_query['created_at']),
				($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
				($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
				number_format($total_amount1,2) ,
				number_format($room_cost1,2) ,
				$tax_name,
				number_format($tax_amount1,2),
				'NA',
				'NA',
				number_format(0,2),
				"0.00",
				"0.00",
				"",
				""
			), "bg" =>'');
			array_push($array_s,$temp_arr);
		}
		// Ferry
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($ferry_list_arr)>0){

			$hsn_code = get_service_info('Cruise/Ferry');
			for($i=0;$i<sizeof($ferry_list_arr);$i++){

				$tax_amount = 0;
				$tax_arr = explode(',',$ferry_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$ferry_list_arr[$i]->service->service_arr[0]->total_cost);
				$room_cost = $package_item[0];
				$h_currency_id = $package_item[1];
				$tax_name = '';
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
						$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
						}else{
						$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
				$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
			$tax_total += $tax_amount1;
			$temp_arr = array( "data" => array(
				(int)($count++),
				"B2B Booking (Ferry)",
				$hsn_code ,
				$cust_name,
				($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
				($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
				get_b2b_booking_id($row_query['booking_id'],$yr[0]) ,
				get_date_user($row_query['created_at']),
				($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
				($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
				number_format($total_amount1,2) ,
				number_format($room_cost1,2) ,
				$tax_name,
				number_format($tax_amount1,2),
				'NA',
				'NA',
				number_format(0,2),
				"0.00",
				"0.00",
				"",
				""
			), "bg" =>'');
			array_push($array_s,$temp_arr);
		}
		// Group Tour
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($group_list_arr)>0){

			$hsn_code = get_service_info('Group Tour');
			for($i=0;$i<sizeof($group_list_arr);$i++){

				$services = isset($group_list_arr[$i]->service) ? $group_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){

					$tax_arr = explode(',',$group_list_arr[$i]->service->service_arr[$j]->taxation);
					$room_cost = $group_list_arr[$i]->service->service_arr[$j]->total_cost;
					$h_currency_id = $group_list_arr[$i]->service->service_arr[$j]->currency_id;
					$tax_name = '';
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + $tax_arr2[1];
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
					$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
			$tax_total += $tax_amount1;
			$temp_arr = array( "data" => array(
				(int)($count++),
				"B2B Booking (Group Tour)",
				$hsn_code ,
				$cust_name,
				($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
				($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
				get_b2b_booking_id($row_query['booking_id'],$yr[0]) ,
				get_date_user($row_query['created_at']),
				($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
				($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
				number_format($total_amount1,2) ,
				number_format($room_cost1,2) ,
				$tax_name,
				number_format($tax_amount1,2),
				'NA',
				'NA',
				number_format(0,2),
				"0.00",
				"0.00",
				"",
				""
			), "bg" =>'');
			array_push($array_s,$temp_arr);
		}
	}
	if($row_query['status'] == 'Cancel')
	{
		$yr = explode("-",$row_query['created_at']);
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$cart_checkout_data = json_decode($row_query['cart_checkout_data']);
		$traveller_details = ($row_query['traveller_details']!='' && $row_query['traveller_details']!='null')?json_decode($row_query['traveller_details']):[];

		//Service tax
		$tax_per = 0;
		$cancel_tax_amount = 0;
		$tax_name = 'NA';
		if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = isset($service_tax_subtotal1[$i]) ? explode(':',$service_tax_subtotal1[$i]) : [];
				$gst_amount0 = isset($service_tax[0]) ? $service_tax[0] : 0;
				$gst_amount1 = isset($service_tax[1]) ? $service_tax[1] : 0;
				$gst_amount = isset($service_tax[2]) ? $service_tax[2] : 0;
				$service_tax_amount += $gst_amount;
				$tax_name .= $gst_amount0 . $gst_amount1.' ';
				$tax_per += floatval(str_replace( array('(',')', '%'),'', $gst_amount1));
			}
		}
		$cancel_amount = $row_query['cancel_amount'];
		$markup_tax_amount = 0;
		$tax_total += $cancel_tax_amount;

		$hotel_list_arr = array();
		$transfer_list_arr = array();
		$activity_list_arr = array();
		$tours_list_arr = array();
		$ferry_list_arr = array();
		$group_list_arr = array();
		for($i=0;$i<sizeof($cart_checkout_data);$i++){
			if($cart_checkout_data[$i]->service->name == 'Hotel'){
				array_push($hotel_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Transfer'){
				array_push($transfer_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Activity'){
				array_push($activity_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
				array_push($tours_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Group Tours'){
				array_push($group_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Ferry'){
				array_push($ferry_list_arr,$cart_checkout_data[$i]);
			}
		}
		$total_amount1 = 0;
		// Hotel
		if(sizeof($hotel_list_arr)>0){

			for($i=0;$i<sizeof($hotel_list_arr);$i++){
				$hsn_code = get_service_info('Hotel / Accommodation');
				$tax_arr = explode(',',$hotel_list_arr[$i]->service->hotel_arr->tax);
				for($j=0;$j<sizeof($hotel_list_arr[$i]->service->item_arr);$j++){
					
					$room_types = explode('-',$hotel_list_arr[$i]->service->item_arr[$j]);
					$room_no = $room_types[0];
					$room_cost = $room_types[2];
					$h_currency_id = $room_types[3];
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
		}
		// Activity
		if(sizeof($activity_list_arr)>0){

			$hsn_code = get_service_info('Excursion');
			for($i=0;$i<sizeof($activity_list_arr);$i++){

				$tax_amount = 0;	
				$tax_arr = explode(',',$activity_list_arr[$i]->service->service_arr[0]->taxation);
				$transfer_types = explode('-',$activity_list_arr[$i]->service->service_arr[0]->transfer_type);
				$transfer = $transfer_types[0];
				$room_cost = $transfer_types[1];
				$h_currency_id = $transfer_types[2];
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] === "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
		}
		// Transfer
		if(sizeof($transfer_list_arr)>0){

			$hsn_code = get_service_info('Car Rental');
			for($i=0;$i<sizeof($transfer_list_arr);$i++){

				$services = ($transfer_list_arr[$i]->service!='') ? $transfer_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){
					$tax_arr = explode(',',$services->service_arr[$j]->taxation);
					$transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
					$room_cost = $transfer_cost[0];
					$h_currency_id = $transfer_cost[1];
					$tax_amount = 0;
					
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
		}
		// Combo Tour
		if(sizeof($tours_list_arr)>0){

			$hsn_code = get_service_info('Package Tour');
			for($i=0;$i<sizeof($tours_list_arr);$i++){
				$tax_amount = 0;
				$tax_arr = explode(',',$tours_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$tours_list_arr[$i]->service->service_arr[0]->package_type);
				$room_cost = $package_item[1];
				$h_currency_id = $package_item[2];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
		}
		// Ferry
		if(sizeof($ferry_list_arr)>0){

			$hsn_code = get_service_info('Cruise/Ferry');
			for($i=0;$i<sizeof($ferry_list_arr);$i++){

				$tax_amount = 0;
				$tax_arr = explode(',',$ferry_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$ferry_list_arr[$i]->service->service_arr[0]->total_cost);
				$room_cost = $package_item[0];
				$h_currency_id = $package_item[1];
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
						$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						}else{
						$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
		}
		//Group tour
		if(sizeof($group_list_arr)>0){

			$hsn_code = get_service_info('Group Tour');
			for($i=0;$i<sizeof($group_list_arr);$i++){

				$services = isset($group_list_arr[$i]->service) ? $group_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){

					$tax_arr = explode(',',$group_list_arr[$i]->service->service_arr[$j]->taxation);
					$room_cost = $group_list_arr[$i]->service->service_arr[$j]->total_cost;
					$h_currency_id = $group_list_arr[$i]->service->service_arr[$j]->currency_id;
					$tax_name = '';
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + $tax_arr2[1];
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
					$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
		}

		$temp_arr = array( "data" => array(
			(int)($count++),
			"B2B Booking",
			$hsn_code ,
			$cust_name,
			($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'] ,
			($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'] ,
			get_b2b_booking_id($row_query['booking_id'],$yr[0]) ,
			get_date_user($row_query['created_at']),
			($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered',
			($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'] ,
			number_format($total_amount1,2),
			number_format($cancel_amount,2) ,
			$tax_name,
			number_format($cancel_tax_amount,2),
			'NA',
			'NA',
			number_format(0,2),
			"0.00",
			"0.00",
			"",
			""
		), "bg" =>'danger');
		array_push($array_s,$temp_arr);
	}
}
//Income Booking
$query = "select * from other_income_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
$from_date = get_date_db($from_date);
$to_date = get_date_db($to_date);
$query .= " and receipt_date between '$from_date' and '$to_date' ";
}
$query .= " order by income_id desc";

$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	$taxable_amount = $row_query['amount'];
	$sq_income_type_info = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$row_query[income_type_id]'"));
	$hsn_code = 'NA';
	
	//Service tax
	$tax_per = 0;
	$service_tax_amount = 0;
	$tax_name = 'NA';
	//Markup Tax
	$markup_tax_amount = 0;
	$markup_tax_name = 'NA';
	$markup = number_format(0,2);
	//Taxable amount
	$tax_total += $row_query['service_tax_subtotal'];
	$yr = explode("-",$row_query['receipt_date']);
	$temp_arr = array( "data" => array(
		(int)($count++),
		$sq_income_type_info['ledger_name'] ,
		$hsn_code ,
		$row_query['receipt_from'],
		'NA' ,
		($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'],
		get_other_income_payment_id($row_query['income_id'],$yr[0])  ,
		get_date_user($row_query['receipt_date']),
		'Unregistered',
		'NA',
		$row_query['total_fee'] ,
		number_format($taxable_amount,2) ,
		'NA',
		number_format($row_query['service_tax_subtotal'],2),
		$markup,
		$markup_tax_name,
		number_format($markup_tax_amount,2),
		"0.00",
		"0.00",
		"",
		""
	), "bg" =>'');
	array_push($array_s,$temp_arr);
}
					
$footer_data = array("footer_data" => array(
	'total_footers' => 4,
	
	'foot0' => 'Total TAX :'.number_format($tax_total,2),
	'col0' => 14,
	'class0' =>"info text-right",

	'foot1' => '',
	'col1' => 1,
	'class1' =>"info text-left",

	'foot2' => 'Total Markup TAX :'.number_format($markup_tax_total,2),
	'col2' => 2,
	'class2' =>"info text-left",

	'foot3' => '',
	'col3' => 12,
	'class3' =>"info text-left"
	)
);
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>
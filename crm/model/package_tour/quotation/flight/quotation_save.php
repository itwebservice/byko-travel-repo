<?php 
class quotation_save{

public function quotation_master_save()
{
	$enquiry_id = $_POST['enquiry_id'];
	$login_id = $_POST['login_id'];
	$emp_id = $_POST['emp_id'];
	$customer_name = $_POST['customer_name'];
    $email_id = $_POST['email_id'];
    $mobile_no = $_POST['mobile_no'];
	$country_code = $_POST['country_code'];
	$quotation_date =  $_POST['quotation_date'];
	$subtotal =  $_POST['subtotal'];
	$markup_cost =  $_POST['markup_cost'];
	$markup_cost_subtotal =  $_POST['markup_cost_subtotal'];
	$service_tax =  $_POST['service_tax'];
	$service_charge =  $_POST['service_charge'];
	$total_tour_cost =  $_POST['total_tour_cost'];
	$branch_admin_id = $_POST['branch_admin_id'];
	$financial_year_id = $_POST['financial_year_id'];

	$roundoff = $_POST['roundoff'];

	$bsmValues = json_decode(json_encode($_POST['bsmValues']));
	foreach($bsmValues[0] as $key => $value){
		switch($key){
			case 'basic' : $subtotal = ($value != "") ? $value : $subtotal;break;
			case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
			case 'markup' : $markup_cost = ($value != "") ? $value : $markup_cost;break;
		}
	}

	//Plane
	$from_city_id_arr = $_POST['from_city_id_arr'];
	$to_city_id_arr = $_POST['to_city_id_arr'];
    $from_sector_arr = $_POST['from_sector_arr'];
    $to_sector_arr = $_POST['to_sector_arr'];
    $airline_name_arr = $_POST['airline_name_arr'];
    $plane_class_arr = $_POST['plane_class_arr'];
    $arraval_arr = $_POST['arraval_arr'];
	$dapart_arr = $_POST['dapart_arr'];
	$total_adult_arr = $_POST['total_adult_arr'];
	$total_child_arr = $_POST['total_child_arr'];
	$total_infant_arr = $_POST['total_infant_arr'];
	
	$enquiry_content = isset($_POST['enquiry_content']) ? json_encode($_POST['enquiry_content']) : '';
	$quotation_date = get_date_db($quotation_date);
	$created_at = date('Y-m-d');

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(quotation_id) as max from flight_quotation_master"));
	$quotation_id = $sq_max['max']+1;
	$bsmValues = json_encode($bsmValues);
	$whatsapp_no = $country_code.$mobile_no;
	$sq_quotation = mysqlQuery("INSERT INTO flight_quotation_master ( quotation_id, enquiry_id, login_id, branch_admin_id,financial_year_id, emp_id,customer_name,  email_id, mobile_no,country_code,whatsapp_no,subtotal,markup_cost,markup_cost_subtotal,service_tax,service_charge,quotation_cost,quotation_date,bsm_values,roundoff,created_at,status) VALUES ('$quotation_id','$enquiry_id','$login_id', '$branch_admin_id','$financial_year_id', '$emp_id', '$customer_name','$email_id','$whatsapp_no','$country_code','$mobile_no','$subtotal','$markup_cost','$markup_cost_subtotal','$service_tax','$service_charge','$total_tour_cost','$quotation_date','$bsmValues','$roundoff','$created_at','1')");

	if($sq_quotation){
		////////////Enquiry Save///////////
		if($enquiry_id == 0){
			$sq_max_id = mysqli_fetch_assoc(mysqlQuery("select max(enquiry_id) as max from enquiry_master"));
			$enquiry_id1 = $sq_max_id['max']+1;
			$sq_enquiry = mysqlQuery("insert into enquiry_master (enquiry_id, login_id,branch_admin_id,financial_year_id, enquiry_type,enquiry, name, mobile_no, landline_no, email_id,location, assigned_emp_id, enquiry_specification, enquiry_date, followup_date, reference_id, enquiry_content,country_code ) values ('$enquiry_id1', '$login_id', '$branch_admin_id','$financial_year_id', 'Flight Ticket','Strong', '$customer_name', '$mobile_no', '$mobile_no', '$email_id','', '$emp_id','', '$quotation_date', '$quotation_date', '', '$enquiry_content','$country_code')");
			if($sq_enquiry){
				$sq_quot_update = mysqlQuery("update flight_quotation_master set enquiry_id='$enquiry_id1' where quotation_id='$quotation_id'");
			}
			$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from enquiry_master_entries"));
			$entry_id = $sq_max['max'] + 1;
			$sq_followup = mysqlQuery("insert into enquiry_master_entries(entry_id, enquiry_id, followup_reply,  followup_status,  followup_type, followup_date, followup_stage, created_at) values('$entry_id', '$enquiry_id1', '', 'Active','', '$quotation_date','Strong', '$quotation_date')");
			$sq_entryid = mysqlQuery("update enquiry_master set entry_id='$entry_id' where enquiry_id='$enquiry_id1'");
		}
		
		$this->plane_entries_save($quotation_id, $from_city_id_arr, $from_sector_arr, $to_city_id_arr, $to_sector_arr, $plane_class_arr,$airline_name_arr, $dapart_arr, $arraval_arr,$total_adult_arr,$total_child_arr , $total_infant_arr);
		echo "Quotation has been successfully saved.";
		exit;
	}
	else{
		echo "error--Quotation not saved!";
		exit;
	}

}

public function plane_entries_save($quotation_id, $from_city_id_arr, $from_sector_arr, $to_city_id_arr, $to_sector_arr, $plane_class_arr,$airline_name_arr,$dapart_arr, $arraval_arr ,$total_adult_arr,$total_child_arr , $total_infant_arr)
{
	for($i=0; $i<sizeof($from_city_id_arr); $i++){

		$arraval_arr1 = get_datetime_db($arraval_arr[$i]);
		$dapart_arr1 = get_datetime_db($dapart_arr[$i]);

		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(id) as max from flight_quotation_plane_entries"));
		$id = $sq_max['max']+1;
		$from_location = array_slice(explode(' - ', $from_sector_arr[$i]), 1);
        $from_location = implode(' - ',$from_location);
        $to_location = array_slice(explode(' - ', $to_sector_arr[$i]), 1);
        $to_location = implode(' - ',$to_location);
		$sq_plane = mysqlQuery("insert into flight_quotation_plane_entries ( id, quotation_id, from_city, from_location, to_city, to_location,airline_name, class, total_adult,total_child , total_infant,arraval_time, dapart_time) values ( '$id', '$quotation_id', '$from_city_id_arr[$i]', '$from_location', '$to_city_id_arr[$i]', '$to_location','$airline_name_arr[$i]', '$plane_class_arr[$i]', '$total_adult_arr[$i]','$total_child_arr[$i]' , '$total_infant_arr[$i]','$arraval_arr1', '$dapart_arr1')");
		if(!$sq_plane){
			echo "error--Plane information not saved!";
			exit;
		}
	}

}
public function quotation_whatsapp(){
	$quotation_id = $_POST['quotation_id'];

	// $currency = "Rs.";
	global $app_name,$app_contact_no,$currency;
	
	$all_message = "";
	$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from flight_quotation_master where quotation_id='$quotation_id'"));
	
	$mobile_no = mysqli_fetch_assoc(mysqlQuery("SELECT landline_no FROM `enquiry_master` WHERE enquiry_id = ".$sq_quotation['enquiry_id']));
	$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where id='$sq_quotation[login_id]'"));
	$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_login[emp_id]'"));

	if($sq_login['emp_id'] == 0){
		$contact = $app_contact_no;
	}
	else{
		$contact = $sq_emp_info['mobile_no'];
	}
	$sq_sector = mysqlQuery("SELECT * FROM `flight_quotation_plane_entries` WHERE `quotation_id` = $quotation_id");
	$sector_string = ''; 
	while($row = mysqli_fetch_assoc($sq_sector)){
		$sector_string .= "
*Sector From* : ".$row['from_location']."
*Sector To* : ".$row['to_location']."
";
	}
	

	$quotation_cost = currency_conversion($currency,$currency,$sq_quotation['quotation_cost']);

	$whatsapp_msg = rawurlencode('Dear '.$sq_quotation['customer_name'].',
Hope you are doing great. This is flight quotation details as per your request. We look forward to having you onboard with us.'.$sector_string.'
*Quotation Cost* : '.$quotation_cost.'

Please contact for more details : '.$app_name.' '.$contact.'
Thank you.');
	$all_message .=$whatsapp_msg;
	$link = 'https://web.whatsapp.com/send?phone='.$mobile_no['landline_no'].'&text='.$all_message;
	echo $link;
}

}
?>
 
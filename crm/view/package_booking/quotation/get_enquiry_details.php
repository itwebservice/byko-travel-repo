<?php
include "../../../model/model.php";
$enquiry_id = $_POST['enquiry_id'];

$sq_enq = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master where enquiry_id='$enquiry_id'"));
$enquiry_content_arr1 = isset($sq_enq['enquiry_content']) ? json_decode($sq_enq['enquiry_content'], true) : [];

if($enquiry_content_arr1 !== null){
	foreach($enquiry_content_arr1 as $enquiry_content_arr2)
	{
		if($enquiry_content_arr2['name']=="tour_name"){ $sq_enq1['tour_name'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="budget"){ $sq_enq1['budget'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="total_members"){ $sq_enq1['total_members'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="total_adult"){ $sq_enq1['total_adult'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="total_children"){ $sq_enq1['total_children'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="total_infant"){ $sq_enq1['total_infant'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="total_single_person"){ $sq_enq1['total_single_person'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="children_without_bed"){ $sq_enq1['children_without_bed'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="children_with_bed"){ $sq_enq1['children_with_bed'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="travel_from_date"){ $formatted = date('d-m-Y', strtotime($enquiry_content_arr2['value']));
			$sq_enq1['travel_from_date'] = $formatted; }
		if($enquiry_content_arr2['name']=="travel_to_date"){ $formatted1 = date('d-m-Y', strtotime($enquiry_content_arr2['value']));
			$sq_enq1['travel_to_date'] = $formatted1; }
		if($enquiry_content_arr2['name']=="landline_no"){ $sq_enq1['landline_no'] = $enquiry_content_arr2['value']; }
	}
}
$sq_enq1['name'] = $sq_enq['name'];
$sq_enq1['user_id'] = $sq_enq['user_id'];
if($sq_enq['user_id'] != 0){
	$sq_user = mysqli_fetch_assoc(mysqlQuery("select name from customer_users where user_id='$sq_enq[user_id]'"));
	$sq_enq1['user_name'] = $sq_user['name'];
}
$sq_enq1['email_id'] = $sq_enq['email_id'];
$sq_enq1['landline_no'] = $sq_enq['landline_no'];
$sq_enq1['country_code'] = $sq_enq['country_code'];
echo json_encode($sq_enq1);
exit;
?>
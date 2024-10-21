<?php
include "../../../../../model/model.php";

$enquiry_id = $_POST['enquiry_id'];

$sq_enq = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master where enquiry_id='$enquiry_id'"));
$enquiry_content_arr1 = isset($sq_enq['enquiry_content']) ? json_decode($sq_enq['enquiry_content'], true) : [];
if($enquiry_content_arr1 !== null){
	foreach($enquiry_content_arr1 as $enquiry_content_arr2){
		if($enquiry_content_arr2['name']=="total_pax"){ $sq_enq['total_pax'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="days_of_traveling"){ $sq_enq['days_of_traveling'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="traveling_date"){ $sq_enq['traveling_date'] = ($enquiry_content_arr2['value']!='') ? get_datetime_user($enquiry_content_arr2['value']) : ''; }
		if($enquiry_content_arr2['name']=="vehicle_type"){ $sq_enq['vehicle_type'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="travel_type"){ $sq_enq['travel_type'] = $enquiry_content_arr2['value']; }
		if($enquiry_content_arr2['name']=="places_to_visit"){ $sq_enq['places_to_visit'] = $enquiry_content_arr2['value']; }
	}
}

echo json_encode($sq_enq);
exit;
?>
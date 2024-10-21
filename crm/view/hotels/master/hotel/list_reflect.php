<?php
include "../../../../model/model.php";

$active_flag = !empty($_POST['active_flag']) ? $_POST['active_flag'] : null;
$city_id = !empty($_POST['city_id']) ? $_POST['city_id'] : null ;
$array_s = array();
$temp_arr = array();
$query = "select * from hotel_master where 1 ";
if($active_flag!=""){
	$query .=" and active_flag='$active_flag' ";
}
if($city_id!=""){
	$query .=" and city_id='$city_id' ";
}
$count = 0;
$sq_hotel = mysqlQuery($query);
while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
	$sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_hotel[city_id]'"));
	$bg = ($row_hotel['active_flag']=="Inactive") ? "danger" : "";
	$mobile_no = $encrypt_decrypt->fnDecrypt($row_hotel['mobile_no'], $secret_key);
	$temp_arr = array("data" =>array(
		(int)(++$count), 
		!empty($row_hotel['hotel_name']) ? ucfirst($row_hotel['hotel_name']) : null ,
		!empty($sq_city['city_name']) ? ucfirst($sq_city['city_name']) : null ,
		!empty($mobile_no) ? $mobile_no : null,
		!empty($row_hotel['contact_person_name']) ? $row_hotel['contact_person_name'] : null ,
		'<button class="btn btn-info btn-sm" id="update_btn-'.$row_hotel['hotel_id'] .'" onclick="update_modal('.$row_hotel['hotel_id'] .')" data-toggle="tooltip" title="Update Details"><i class="fa fa-pencil-square-o"></i></button><button class="btn btn-info btn-sm" id="view_btn-'.$row_hotel['hotel_id'] .'" onclick="view_modal('.$row_hotel['hotel_id'].')" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>
		'), "bg" => $bg
	);
	array_push($array_s,$temp_arr); 
}
echo json_encode($array_s);

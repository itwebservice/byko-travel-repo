<?php 
include "../../../model/model.php";
$status = $_POST['status'];

$array_s = array();
$temp_arr = array();
$count = 0;

if($status != ''){
	$query = "select * from b2b_transfer_master where 1 and status='$status'";
}else{
	
	$query = "select * from b2b_transfer_master where 1 and status='Active'";
}

$sq_vehicle = mysqlQuery($query);
while($row_vehicle = mysqli_fetch_assoc($sq_vehicle))
{
	$bg = ($row_vehicle['status']=="Inactive") ? "danger" : "";
	$temp_arr = array ("data"=>array(
		(int)(++$count),
		$row_vehicle['vehicle_type'],
		$row_vehicle['vehicle_name'],
		$row_vehicle['seating_capacity'],
		'<button class="btn btn-info btn-sm" data-toggle="tooltip" id="update_btn-'.$row_vehicle['entry_id'] .'" onclick="edit_modal('.$row_vehicle['entry_id'] .')" title="Update Details"><i class="fa fa-pencil-square-o"></i></button>'),"bg" => $bg
	);
	array_push($array_s,$temp_arr); 
}
echo json_encode($array_s);
?>
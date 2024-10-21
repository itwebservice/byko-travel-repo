<?php
include "../../../../../model/model.php";
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$array_s = array();
$temp_arr = array();
$query = "SELECT * FROM `deleted_entries_master` where 1 ";
if($from_date != '' && $to_date != ''){

	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and DATE(deleted_at) between '$from_date' and '$to_date'";
}
$query .= " ORDER BY `entry_id` DESC";

$sq_query1 = mysqlQuery($query);
while($sq_query = mysqli_fetch_assoc($sq_query1)){

    $bg = '';
    $temp_arr = array( "data" => array(
        $sq_query['entry_id'],
        get_datetime_user($sq_query['deleted_at']),
        $sq_query['trans_type'],
        $sq_query['module_name'],
        $sq_query['long_id'],
        $sq_query['guest_name'],
        number_format($sq_query['amount'],2)
    ), "bg" =>$bg);
    array_push($array_s, $temp_arr);
}
echo json_encode($array_s);

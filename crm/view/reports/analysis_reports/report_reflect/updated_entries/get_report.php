<?php
include "../../../../../model/model.php";
$financial_year_id = $_POST['financial_year_id'];
$array_s = array();
$query = "SELECT * FROM `updated_entries_log` where 1 ";
if($financial_year_id != ''){
	$query .= " and financial_year_id = '$financial_year_id'";
}
$query .= " ORDER BY `entry_id` DESC";

$sq_query1 = mysqlQuery($query);
while($sq_query = mysqli_fetch_assoc($sq_query1)){

    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from emp_master where emp_id='$sq_query[emp_id]'"));
    $temp_arr = array( "data" => array(
        $sq_query['entry_id'],
        $sq_query['service'],
        $sq_query['trans_id'],
        get_datetime_user($sq_query['updated_at']),
        $sq_emp['first_name'].' '.$sq_emp['last_name'],
        $sq_query['old_amount'].' => <b>'.$sq_query['new_amount'].'</b>'
    ), "bg" =>'');
    array_push($array_s, $temp_arr);
}
echo json_encode($array_s);

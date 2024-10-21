<?php
include_once("../../../model/model.php");
$array_s = array();
$temp_arr = array();
$footer_data = array();
$register_id = isset($_SESSION['register_id']) ? $_SESSION['register_id'] : '';
global $encrypt_decrypt,$secret_key;

$query = "select * from b2b_users where register_id = '$register_id' order by id desc";

$count = 1;
$quotation_cost = 0;
$sq_quotation = mysqlQuery($query);
while($row_user = mysqli_fetch_assoc($sq_quotation)){

    $bg = ($row_user['status']!='Inactive') ? '' : 'table-danger';
    $user_id = $row_user['id'];
    $username = $encrypt_decrypt->fnDecrypt($row_user['username'], $secret_key);
    $password = $encrypt_decrypt->fnDecrypt($row_user['password'], $secret_key);
	$temp_arr = array("data" => array (
        $count++,
        $row_user['full_name'],
        $row_user['email_id'],
        $row_user['mobile_no'],
        get_date_user($row_user['created_at']),
        '<button class="btn btn-info btn-sm" onclick="update_user('.$user_id.')" title="Edit Details"><i class="fa fa-pencil"></i></button>'), "bg" => $bg
	);
array_push($array_s,$temp_arr); 
}
$footer_data = array("footer_data" => array());
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>
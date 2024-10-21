<?php
include '../../model/model.php';
$register_id = $_POST['register_id'];
$user_id = $_SESSION['user_id'];
$agent_flag = $_SESSION['agent_flag'];
$sq_reg = mysqli_fetch_assoc(mysqlQuery("select cart_data from b2b_login where user_id='$register_id' and agent_flag='$agent_flag'"));
echo ($sq_reg['cart_data']);
?>
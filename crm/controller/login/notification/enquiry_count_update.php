<?php
include "../../../model/model.php";  
$emp_id=$_SESSION['emp_id'];
$sq_emp = mysqlQuery("update emp_master set notification_count='0' where emp_id='$emp_id'");
?>
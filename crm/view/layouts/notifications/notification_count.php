<?php
include_once("../../../model/model.php");
$emp_id = $_SESSION['emp_id'];
$row_emp = mysqli_fetch_assoc(mysqlQuery("select notification_count from emp_master where emp_id='$emp_id'"));
$class = ($row_emp['notification_count'] <= 0) ? 'hidden' : '';
?>
<mark class="notify text-center <?= $class ?>" id="notification_count"><?= $row_emp['notification_count'] ?></mark>

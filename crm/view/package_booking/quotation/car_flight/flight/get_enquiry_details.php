<?php
include "../../../../../model/model.php";

$enquiry_id = $_GET['enquiry_id'];
$sq_enq = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master where enquiry_id='$enquiry_id'"));

$enquiry_content_arr1 = isset($sq_enq['enquiry_content']) ? json_decode($sq_enq['enquiry_content'], true) : [];
echo json_encode($sq_enq);
exit;
?>
<?php
include '../../../model/model.php';
$estimate_type = $_POST['estimate_type'];
$estimate_type_id = isset($_POST['estimate_type_id']) ? $_POST['estimate_type_id'] : '';
$sq_p = '';
$sq = mysqlQuery("select vendor_type from vendor_estimate where status!='Cancel' and estimate_type_id = '$estimate_type_id' and estimate_type = '$estimate_type' and delete_status='0'");
while($row = mysqli_fetch_assoc($sq)){
    $sq_p .= $row['vendor_type'].',';
}
$string = substr($sq_p, 0, -1);
echo $string;
?>
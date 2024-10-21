<?php
include "../../../../../model/model.php";

$travel_type = $_POST['travel_type'];
$vehicle_name = $_POST['vehicle_name'];

$sq_enq = mysqli_fetch_assoc(mysqlQuery("select seating_capacity from b2b_transfer_master where vehicle_name='$vehicle_name'"));
$capacity = isset($sq_enq['seating_capacity']) ? $sq_enq['seating_capacity'] : 0;

echo $capacity;
exit;
?>
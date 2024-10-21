<?php
include "../../model/model.php";

$booking_id = $_POST['booking_id'];
$table_name = $_POST['table_name'];
$col_name = $_POST['col_name'];

$sq_booking = mysqli_fetch_assoc(mysqlQuery("select cancel_type from $table_name where $col_name='$booking_id'"));
if($sq_booking['cancel_type'] == 1){
    $cancel_type = 'Full';
}else if($sq_booking['cancel_type'] == 2){
    $cancel_type = 'Passenger wise';
}else if($sq_booking['cancel_type'] == 3){
    $cancel_type = 'Sector wise';
}else{
    $cancel_type = 0;
}
if($sq_booking['cancel_type'] == 1||$sq_booking['cancel_type'] == 2||$sq_booking['cancel_type'] == 3)
echo '<option value='."'$sq_booking[cancel_type] '".'>'."$cancel_type".'</option>';
else
echo 0;
?>
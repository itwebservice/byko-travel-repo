<?php
include "../../../../model/model.php";


$booking_id = $_POST['booking_id'];
$net_total_arr = $_POST['net_total_arr'];
$basic_amount_arr = array();
// $emp_id = $_POST['emp_id'];
$financial_year_id = $_SESSION['financial_year_id'];
// $purchase = isset($_POST['purchase']) ? $_POST['purchase'] : '';
$estimate_type = $_POST['estimate_type'];

if($estimate_type=='Package Tour'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['total_travel_expense'] + $sq_total_amount['actual_tour_expense'];
    $booking_date = $sq_total_amount['booking_date'];
}
if($estimate_type=='Bus'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['net_total'];
    $booking_date = $sq_total_amount['created_at'];
}
if($estimate_type=='Activity'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master where exc_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['exc_total_cost'];
    $booking_date = $sq_total_amount['created_at'];

}
if($estimate_type=='Car Rental'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from car_rental_booking where booking_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['total_fees'];
    $booking_date = $sq_total_amount['created_at'];
}
if($estimate_type=='Group Tour'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['total_travel_expense'] + $sq_total_amount['total_tour_fee'];
    $booking_date = $sq_total_amount['from_date'];

}
if($estimate_type=='Hotel'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from hotel_booking_master where booking_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['total_fee'];
    $booking_date = $sq_total_amount['created_at'];

}
if($estimate_type=='Miscellaneous'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from miscellaneous_master where misc_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['misc_total_cost'];
    $booking_date = $sq_total_amount['created_at'];

}
if($estimate_type=='Flight'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['ticket_total_cost'];
    $booking_date = $sq_total_amount['created_at'];

}
if($estimate_type=='Train'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['net_total'];
    $booking_date = $sq_total_amount['created_at'];

}
if($estimate_type=='Visa'){
    $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select * from visa_master where visa_id='$booking_id' and delete_status='0'"));
    $total_exp_amount = $sq_total_amount['visa_total_cost'];
    $booking_date = $sq_total_amount['created_at'];

}
$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_total_amount[emp_id]'"));
$incentive = $sq_emp['incentive_per'];


for($i=0;$i<sizeof($net_total_arr);$i++){
    $total_amount= $total_exp_amount-$net_total_arr[$i];
    $basic_amount = ($total_amount*($incentive/100));
    $emp_id = $sq_total_amount['emp_id'];
    $arr = array(
        'basic_amount' => $basic_amount,
        'emp_id' => $emp_id,
        'booking_date' => $booking_date
         );
    
array_push($basic_amount_arr,$arr);
}



echo json_encode($basic_amount_arr);
exit;
?>
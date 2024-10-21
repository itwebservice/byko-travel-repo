<?php
include "../../../model/model.php";
$estimate_type = $_POST['estimate_type'];
$estimate_type_id = $_POST['estimate_type_id'];

if($estimate_type=="Package Tour"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select basic_amount from package_tour_booking_master where booking_id='$estimate_type_id' "));
	echo $sq_booking['basic_amount'];
}
else if($estimate_type=="Car Rental"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select basic_amount from car_rental_booking where booking_id='$estimate_type_id' "));
	echo $sq_booking['basic_amount'];
}
else if($estimate_type=="Visa"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select visa_issue_amount from visa_master where visa_id='$estimate_type_id' "));
	echo $sq_booking['visa_issue_amount'];
}
else if($estimate_type=="Flight"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select basic_cost from ticket_master where ticket_id='$estimate_type_id' "));
	echo $sq_booking['basic_cost'];
}
else if($estimate_type=="Train"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select basic_fair from train_ticket_master where train_ticket_id='$estimate_type_id' "));
	echo $sq_booking['basic_fair'];
}
else if($estimate_type=="Hotel"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select sub_total from hotel_booking_master where booking_id='$estimate_type_id' "));
	echo $sq_booking['sub_total'];
}
else if($estimate_type=="Bus"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select basic_cost from bus_booking_master where booking_id='$estimate_type_id' "));
	echo $sq_booking['basic_cost'];
}
else if($estimate_type=="Activity"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select exc_issue_amount from excursion_master where exc_id='$estimate_type_id' "));
	echo $sq_booking['exc_issue_amount'];
}
else if($estimate_type=="Miscellaneous"){
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select misc_issue_amount from miscellaneous_master where misc_id='$estimate_type_id' "));
	echo $sq_booking['misc_issue_amount'];
}else{
	echo 0;
}
?>        
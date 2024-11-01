<?php
include_once('../model.php');
$due_date=date('Y-m-d');
global $secret_key,$encrypt_decrypt,$model;
$sq_hotel = mysqli_num_rows(mysqlQuery("select * from hotel_booking_master where due_date='$due_date' and delete_status='0'"));
if($sq_hotel>0){

	$sq_hotel_details = mysqlQuery("select * from hotel_booking_master where due_date='$due_date' and delete_status='0'");
	while($row_hotel = mysqli_fetch_assoc($sq_hotel_details)){

		$hotel_id = $row_hotel['booking_id'];
		$date = $row_hotel['created_at'];
		$yr = explode("-", $date);
		$year = $yr[0];
		$booking_id = get_hotel_booking_id($hotel_id,$year);
		$hotel_total_cost = $row_hotel['total_fee'];
		$tour_name = 'NA';
		$customer_id = $row_hotel['customer_id'];

		$row_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
		$email_id = $encrypt_decrypt->fnDecrypt($row_cust['email_id'], $secret_key);
		$customer_name =  ($row_cust['type'] == 'Corporate'||$row_cust['type'] == 'B2B') ? $row_cust['company_name'] : $row_cust['first_name'].' '.$row_cust['last_name'];
		
		$row_paid = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from hotel_booking_payment where booking_id='$hotel_id' and (clearance_status='Cleared' or clearance_status='')"));
		$paid_amount = $row_paid['sum'];
		$cancel_amount = $row_hotel['cancel_amount'];

		$pass_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$hotel_id'"));
		$cancel_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$hotel_id' and status='Cancel'"));
		if($pass_count == $cancel_count){
			if($paid_amount > 0){
				if($cancel_amount >0){
					if($paid_amount > $cancel_amount){
						$balance_amount = 0;
					}else{
						$balance_amount = $cancel_amount - $paid_amount;
					}
				}else{
					$balance_amount = 0;
				}
			}
			else{
				$balance_amount = $cancel_amount;
			}
		}
		else{
			$balance_amount = $hotel_total_cost - $paid_amount;
		}
		$balance_amount = ($balance_amount < 0) ? 0 : number_format($balance_amount,2);
		if($balance_amount>0){
			$sq_count = mysqli_num_rows(mysqlQuery("SELECT * from remainder_status where remainder_name = 'hotel_payment_pending_remainder' and date='$due_date' and status='Done'"));
			if($sq_count==0){
				$subject = 'Hotel Booking Payment Reminder !';
				$model->generic_payment_remainder_mail('85',$customer_name,$paid_amount,$balance_amount, $tour_name, $booking_id, $customer_id, $email_id,$subject,$hotel_total_cost,$due_date);
			}
		}
	}
}
$row=mysqlQuery("SELECT max(id) as max from remainder_status");
$value=mysqli_fetch_assoc($row);
$max=$value['max']+1;
$sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','hotel_payment_pending_remainder','$due_date','Done')");
?>
<?php
include_once('../model.php');
$due_date=date('Y-m-d');
global $secret_key,$encrypt_decrypt;
$sq_car_rental= mysqli_num_rows(mysqlQuery("select * from car_rental_booking where due_date='$due_date' and status!='Cancel' and delete_status='0'"));

if($sq_car_rental>0){

	$sq_car_rental_details = mysqlQuery("select * from car_rental_booking where due_date='$due_date' and status!='Cancel' and delete_status='0'");
	while($row_car=mysqli_fetch_assoc($sq_car_rental_details)) {

		$booking_id = $row_car['booking_id'];
		$total_cost = $row_car['total_fees'];
		$tour_name = 'NA';
		$customer_id = $row_car['customer_id'];
		$date = $row_car['created_at'];
		$yr = explode("-", $date);
		$year = $yr[0];
		$car_id = get_car_rental_booking_id($booking_id, $year);

		$row_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
		$email_id = $encrypt_decrypt->fnDecrypt($row_cust['email_id'], $secret_key);
		$customer_name =  ($row_cust['type'] == 'Corporate'||$row_cust['type'] == 'B2B') ? $row_cust['company_name'] : $row_cust['first_name'].' '.$row_cust['last_name'];

		$row_paid = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from car_rental_payment where booking_id='$booking_id' and (clearance_status='Cleared' or clearance_status='')"));	
		$paid_amount = $row_paid['sum'];
		$cancel_amount = $row_car['cancel_amount'];
		if($row_car['status'] == 'Cancel'){
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
			$balance_amount = $total_cost - $paid_amount;
		}
		$balance_amount = ($balance_amount < 0) ? 0 : number_format($balance_amount,2);
		if($balance_amount>0){

			$sq_count = mysqli_num_rows(mysqlQuery("SELECT * from  remainder_status where remainder_name = 'car_payment_pending_remainder' and date='$due_date' and status='Done'"));
			if($sq_count==0)
			{
				$subject = 'Car Rental Payment Reminder !';
				global $model;	
				$model->generic_payment_remainder_mail('87',$customer_name,$paid_amount,$balance_amount, $tour_name, $car_id, $customer_id, $email_id, $subject,$total_cost,$due_date);
			}
		}
	}
}
$row=mysqlQuery("SELECT max(id) as max from remainder_status");
$value=mysqli_fetch_assoc($row);
$max=$value['max']+1;
$sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','car_payment_pending_remainder','$due_date','Done')");
?>
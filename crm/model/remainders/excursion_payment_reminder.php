<?php
include_once('../model.php');
global $secret_key,$encrypt_decrypt;
$due_date=date('Y-m-d');
$sq_air = mysqli_num_rows(mysqlQuery("select * from excursion_master where due_date='$due_date' and delete_status='0'"));
if($sq_air>0){

	$sq_air_details = mysqlQuery("select * from excursion_master where due_date='$due_date' and delete_status='0'");
	while($row_air = mysqli_fetch_assoc($sq_air_details)){

		$air_id = $row_air['exc_id'];
		$date = $row_air['created_at'];
		$yr = explode("-", $date);
		$year = $yr[0];
		$exc_id = get_exc_booking_id($air_id,$year);
		$exc_total_cost = $row_air['exc_total_cost'];
		$tour_name = 'NA';
		$customer_id = $row_air['customer_id'];
		$cancel_amount = $row_air['cancel_amount'];

		$row_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
		$email_id = $encrypt_decrypt->fnDecrypt($row_cust['email_id'], $secret_key);
		$customer_name =  ($row_cust['type'] == 'Corporate'||$row_cust['type'] == 'B2B') ? $row_cust['company_name'] : $row_cust['first_name'].' '.$row_cust['last_name'];

		$row_paid = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from exc_payment_master where exc_id='$air_id' and (clearance_status='Cleared' or clearance_status='')"));
		$paid_amount = $row_paid['sum'];
		$pass_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$air_id'"));
		$cancel_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$air_id' and status='Cancel'"));

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
			$balance_amount = $exc_total_cost - $paid_amount;
		}
		$balance_amount = ($balance_amount < 0) ? 0 : number_format($balance_amount,2);
		if($balance_amount>0){
			$sq_count = mysqli_num_rows(mysqlQuery("SELECT * from  remainder_status where remainder_name = 'excursion_payment_pending_remainder' and date='$due_date' and status='Done'"));
			if($sq_count==0){
				global $model;
				$subject = 'Excursion Payment Reminder !';
				$model->generic_payment_remainder_mail('89',$customer_name,$paid_amount,$balance_amount, $tour_name, $exc_id, $customer_id, $email_id,$subject,$exc_total_cost,$due_date);
			}
		}
	}
}

$row = mysqlQuery("SELECT max(id) as max from remainder_status");
$value = mysqli_fetch_assoc($row);
$max = $value['max']+1;
$sq_check_status = mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','excursion_payment_pending_remainder','$due_date','Done')");
?>
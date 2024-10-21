<?php
include_once('../model.php');
$due_date = date('Y-m-d');
$sq_visa = mysqli_num_rows(mysqlQuery("select * from visa_master where due_date='$due_date' and delete_status='0'"));
global $secret_key,$encrypt_decrypt;

if($sq_visa>0){

	$sq_visa_details = mysqlQuery("select * from visa_master where due_date='$due_date' and delete_status='0'");
	while ($row_visa = mysqli_fetch_assoc($sq_visa_details)) {

		$visa_id = $row_visa['visa_id'];
		$date = $row_visa['created_at'];
		$yr = explode("-", $date);
		$year = $yr[0];
		$booking_id = get_visa_booking_id($visa_id,$year);
		$visa_total_cost = $row_visa['visa_total_cost'];
		$customer_id = $row_visa['customer_id'];
		$cancel_amount= $row_visa['cancel_amount'];
		$tour_name = 'NA';

		$row_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
		$email_id = $encrypt_decrypt->fnDecrypt($row_cust['email_id'], $secret_key);
		$customer_name =  ($row_cust['type'] == 'Corporate'||$row_cust['type'] == 'B2B') ? $row_cust['company_name'] : $row_cust['first_name'].' '.$row_cust['last_name'];

		$pass_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$row_visa[visa_id]'"));
		$cancel_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$row_visa[visa_id]' and status='Cancel'"));

		$row_total = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from visa_payment_master where visa_id='$visa_id' and (clearance_status='Cleared' or clearance_status='')"));
		$paid_amount = $row_total['sum'];
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
			$balance_amount = $visa_total_cost - $paid_amount;
		}
		$balance_amount = ($balance_amount < 0) ? 0 : number_format($balance_amount,2);
		if($balance_amount>0){

			$sq_count = mysqli_num_rows(mysqlQuery("SELECT * from  remainder_status where remainder_name = 'visa_payment_pending_remainder' and date='$due_date' and status='Done'"));
			if($sq_count==0)
			{
				global $model;
				$subject = 'Visa Payment Reminder !';
				$model->generic_payment_remainder_mail('82',$customer_name,$paid_amount,$balance_amount, $tour_name, $booking_id, $customer_id, $email_id,$subject,$visa_total_cost,$due_date );
			}
		}
	}
}
$row=mysqlQuery("SELECT max(id) as max from remainder_status");
$value=mysqli_fetch_assoc($row);
$max=$value['max']+1;
$sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','visa_payment_pending_remainder','$due_date','Done')");
?>
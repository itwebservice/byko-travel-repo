<?php
include_once('../model.php');
$due_date=date('Y-m-d');
$sq_tour = mysqli_num_rows(mysqlQuery("select * from tourwise_traveler_details where balance_due_date='$due_date' and tour_group_status!='cancel' and delete_status='0'"));
if($sq_tour>0){
	$sq_tour_details = mysqlQuery("select * from tourwise_traveler_details where balance_due_date='$due_date' and tour_group_status!='cancel' and delete_status='0'");
	while($row_tour = mysqli_fetch_assoc($sq_tour_details)){

		$booking_id = $row_tour['id'];
		$date = $row_tour['form_date'];
		$yr = explode("-", $date);
		$year = $yr[0];
		$booking_id1 = get_group_booking_id($booking_id,$year);
		$total_amount =  $row_tour['net_total'];
		$tour_id = $row_tour['tour_id'];
		$customer_id = $row_tour['customer_id'];

		$sq_tour_name = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id='$tour_id'"));
		$tour_name = $sq_tour_name['tour_name'];

		$row_total_paid = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum from payment_master where tourwise_traveler_id='$booking_id' and (clearance_status='Cleared' or clearance_status='')"));
		$paid_amount = $row_total_paid['sum'];
		$pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_tour[traveler_group_id]'"));
		$cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_tour[traveler_group_id]' and status='Cancel'"));    

		if($row_tour['tour_group_status'] == 'Cancel'){
			//Group Tour cancel
			$cancel_tour_count2=mysqli_num_rows(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row_tour[id]'"));
			if($cancel_tour_count2 >= '1'){
				$cancel_tour=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row_tour[id]'"));
				$cancel_amount = $cancel_tour['cancel_amount'];
			}
			else{ $cancel_amount = 0; }
		}
		else{
			// Group booking cancel
			$cancel_esti_count1=mysqli_num_rows(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row_tour[id]'"));
			if($pass_count==$cancelpass_count){
				$cancel_esti1=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row_tour[id]'"));
				$cancel_amount = $cancel_esti1['cancel_amount'];
			}
			else{ $cancel_amount = 0; }
		}
		$cancel_amount = ($cancel_amount == '')?'0':$cancel_amount;
		if($row_tour['tour_group_status'] == 'Cancel'){
			if($cancel_amount > $paid_amount){
				$balance_amount = $cancel_amount - $paid_amount;
			}
			else{
				$balance_amount = 0;
			}
		}else{
			if($pass_count==$cancelpass_count){
				if($cancel_amount > $paid_amount){
					$balance_amount = $cancel_amount - $paid_amount;
				}
				else{
					$balance_amount = 0;
				}
			}
			else{
				$balance_amount = $total_amount - $paid_amount;
			}
		}
		$balance_amount = ($balance_amount < 0) ? 0 : number_format($balance_amount,2);
		$sq_cust = mysqlQuery("select * from traveler_personal_info where tourwise_traveler_id='$booking_id'");
		while($row_cust = mysqli_fetch_assoc($sq_cust)){

			$email_id = $row_cust['email_id'];
			$t_id = $row_cust['tourwise_traveler_id'];
			$c_id = mysqli_fetch_assoc(mysqlQuery("select customer_id from tourwise_traveler_details where id='$booking_id'"));
			$name = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$c_id[customer_id]'"));  
			if($name['type'] == 'Corporate'||$name['type'] == 'B2B'){
				$customer_name = $name['company_name'];
			}else{
				$customer_name = $name['first_name'].' '.$name['last_name'];
			}
			$payment_id = get_group_booking_payment_id($payment_id);

			if($balance_amount>0){
				$sq_count = mysqli_num_rows(mysqlQuery("SELECT * from  remainder_status where remainder_name = 'git_payment_pending_remainder' and date='$due_date' and status='Done'"));
				if($sq_count==0){
					$subject = 'Group Tour Payment Reminder ! (Booking ID : '.$booking_id.' ).';
					global $model;
					$model->generic_payment_remainder_mail('80',$customer_name,$paid_amount,$balance_amount, $tour_name, $booking_id1, $customer_id, $email_id ,$subject,$total_amount,$due_date);
				}
			}
		}
	}
}
$row=mysqlQuery("SELECT max(id) as max from remainder_status");
$value=mysqli_fetch_assoc($row);
$max=$value['max']+1;
$sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','git_payment_pending_remainder','$due_date','Done')");
?>
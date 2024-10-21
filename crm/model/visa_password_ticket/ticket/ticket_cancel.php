<?php 
class ticket_cancel{

public function ticket_cancel_save()
{
	$entry_id_arr = $_POST['entry_id_arr'];
	$sales_return_value = $_POST['sales_return_value'];
	$ticket_id = $_POST['ticket_id'];

	if($sales_return_value == 1 || $sales_return_value == '2'){
		for($i=0; $i<sizeof($entry_id_arr); $i++){
			$sq_cancel = mysqlQuery("update ticket_master_entries set status='Cancel' where entry_id='$entry_id_arr[$i]'");
			$sq_ticket = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$entry_id_arr[$i]'"));
			$sq_cancel = mysqlQuery("update ticket_trip_entries set status='Cancel' where passenger_id='$sq_ticket[entry_id]'");
			if(!$sq_cancel){
				echo "error--Sorry, Cancellation not done!";
				exit;
			}
		}
	}
	if($sales_return_value == 3){
		
		for($i=0; $i<sizeof($entry_id_arr); $i++){
			$sq_cancel = mysqlQuery("update ticket_trip_entries set status='Cancel' where entry_id='$entry_id_arr[$i]'");
			if(!$sq_cancel){
				echo "error--Sorry, Cancellation not done!";
				exit;
			}
		}
	}
	mysqlQuery("UPDATE `ticket_master` SET `cancel_type`='$sales_return_value' WHERE `ticket_id`='$ticket_id'");

	//Cancelation notification mail send
	$this->cancel_mail_send($entry_id_arr,$sales_return_value);

	//Cancelation notification sms send
	// $this->cancelation_message_send($entry_id_arr);

	echo "Flight ticket booking has been successfully cancelled.";
}


public function cancel_mail_send($entry_id_arr,$sales_return_value)
{
	global $app_name,$encrypt_decrypt,$secret_key;
	if($sales_return_value == 1 || $sales_return_value == '2'){
		$sq_entry = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$entry_id_arr[0]'"));
	}
	else if($sales_return_value == 3){
		$sq_entry = mysqli_fetch_assoc(mysqlQuery("select * from ticket_trip_entries where entry_id='$entry_id_arr[0]'"));
	}
	$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$sq_entry[ticket_id]' and delete_status='0'"));
	$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_ticket_info[customer_id]'"));
	if($sq_customer['type'] == 'Corporate'||$sq_customer['type'] == 'B2B'){
		$cust_name = $sq_customer['company_name'];
	}else{
		$cust_name = $sq_customer['first_name'].' '.$sq_customer['last_name'];
	}
	$email = $encrypt_decrypt->fnDecrypt($sq_customer['email_id'], $secret_key);
	$date = $sq_ticket_info['created_at'];
    $yr = explode("-", $date);
    $year =$yr[0];

	$content1 = '';
	if($sales_return_value == 1 || $sales_return_value == '2'){
		$col_name = 'Passenger Name';
		for($i=0; $i<sizeof($entry_id_arr); $i++)
		{
			$sq_entry = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$entry_id_arr[$i]'"));
			$content1 .= '
			<tr>
				<td style="text-align:left;border: 1px solid #888888;">'.($i+1).'</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_entry['first_name'].' '.$sq_entry['last_name'].'</td>   
			</tr>';
		}
	}
	if($sales_return_value == 3){
		$col_name = 'From-To Sector';
		for($i=0; $i<sizeof($entry_id_arr); $i++)
		{
			$sq_entry = mysqli_fetch_assoc(mysqlQuery("select * from ticket_trip_entries where entry_id='$entry_id_arr[$i]'"));
			$content1 .= '
			<tr>
				<td style="text-align:left;border: 1px solid #888888;">'.($i+1).'</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_entry['departure_city'].' -- '.$sq_entry['arrival_city'].'</td>   
			</tr>';
		}
	}

	$content = '	                    
	<tr>
    	<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
    		<tr>
				<th style="border: 1px solid #888888;text-align: left;background: #ddd;color: #888888;">Sr.No</th>
				<th style="border: 1px solid #888888;text-align: left;background: #ddd;color: #888888;">'.$col_name.'</th>
    		</tr>
    	'.$content1.'
	</table>
	</tr> 
	';
	$subject = 'Flight Cancellation Confirmation ( '.get_ticket_booking_id($sq_entry['ticket_id'],$year).' )';
	global $model;
	$model->app_email_send('32',$cust_name,$email, $content, $subject);
}


// public function cancelation_message_send($entry_id_arr)
// {
// 	global $secret_key,$encrypt_decrypt;
// 	$sq_entry = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$entry_id_arr[0]'"));
// 	$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$sq_entry[ticket_id]' and delete_status='0'"));
// 	$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_ticket_info[customer_id]'"));
// 	$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);
// 	$message = 'We are accepting your cancellation request for Flight Ticket booking.';
//   	global $model;
//   	$model->send_message($contact_no, $message);
// }


}
?>
<?php
include_once('../model.php');
$today = date('Y-m-d');
$sq_reminder_count = mysqli_num_rows(mysqlQuery("select * from followup_and_birthday_reminder where reminder_date='$today'"));	
if($sq_reminder_count == 0){
	begin_t();

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(reminder_id) as max from followup_and_birthday_reminder"));
	$reminder_id = $sq_max['max'] + 1;
	$sq_reminder = mysqlQuery("insert into followup_and_birthday_reminder (reminder_id, reminder_date) values ('$reminder_id', '$today')");

	if(!$sq_reminder){
		rollback_t();
	}
	else{
		commit_t();
		mail_send();
	}
}

function mail_send(){

	$today = date('Y-m-d');
	$month = date('m');
	$day = date('d');
	$followup_count = 0;
	global $encrypt_decrypt,$secret_key;

	$sq_emp = mysqlQuery("select * from emp_master where active_flag='Active'");
	while($row_emp = mysqli_fetch_assoc($sq_emp)){

		$enquirty_count = mysqli_num_rows(mysqlQuery("select * from enquiry_master where status!='Disabled' and assigned_emp_id='$row_emp[emp_id]'"));
		if($enquirty_count > 0){
			$content1 = '';
			$sq_enquiry = mysqlQuery("select * from enquiry_master where status!='Disabled' and assigned_emp_id='$row_emp[emp_id]'");
			while($row_enq = mysqli_fetch_assoc($sq_enquiry)){

				$sq_enquiry_entry = mysqli_num_rows(mysqlQuery("select * from enquiry_master where status!='Disabled' and assigned_emp_id='$row_emp[emp_id]' and enquiry_id in(select enquiry_id from enquiry_master_entries where followup_status in ('Active','In-Followup') and DATE(followup_date) = '$today')"));
				if($sq_enquiry_entry > 0){
					$followup_count++;
				}
			}
		}
	}
	if($followup_count>0){

		$sq_emp = mysqlQuery("select * from emp_master where active_flag='Active'");
		while($row_emp = mysqli_fetch_assoc($sq_emp)){

			$sq_enquiry_count = mysqli_num_rows(mysqlQuery("select * from enquiry_master where status!='Disabled' and assigned_emp_id='$row_emp[emp_id]' and enquiry_id in(select enquiry_id from enquiry_master_entries where followup_status in ('Active','In-Followup') and DATE(followup_date) = '$today')"));
			if($sq_enquiry_count > 0){

				// $row_enq = mysqlQuery("select * from enquiry_master where status!='Disabled' and assigned_emp_id='$row_emp[emp_id]'");
				$content1 = '
				<tr>
					<td>
					<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
						<tr>
							<td style="text-align:left;border: 1px solid #888888;">Sr. No</td>
							<td style="text-align:left;border: 1px solid #888888;">Enquiry ID</td>
							<td style="text-align:left;border: 1px solid #888888;">Customer Name</td>
							<td style="text-align:left;border: 1px solid #888888;">Mobile No</td>
							<td style="text-align:left;border: 1px solid #888888;">Email ID</td>
							<td style="text-align:left;border: 1px solid #888888;">Tour Type</td>
							<td style="text-align:left;border: 1px solid #888888;">Tour Name</td>
							<td style="text-align:left;border: 1px solid #888888;">Followup Time</td>
						</tr>';
						$count = 0;
						$sq_enquiry = mysqlQuery("select * from enquiry_master where status!='Disabled' and assigned_emp_id='$row_emp[emp_id]'");
						while($row_enq = mysqli_fetch_assoc($sq_enquiry)){

							$sq_enquiry_entry = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master_entries where entry_id=(select max(entry_id) as entry_id from enquiry_master_entries where enquiry_id='$row_enq[enquiry_id]')"));

							$enquiry_content = $row_enq['enquiry_content'];
							$enquiry_id = $row_enq['enquiry_id'];
							$date = $row_enq['enquiry_date'];
							$yr = explode("-", $date);
							$year = $yr[0];

							$enquiry_content_arr1 = json_decode($enquiry_content, true);
							if($row_enq['enquiry_type'] =="Group Booking" || $row_enq['enquiry_type'] =="Package Booking"){
								foreach($enquiry_content_arr1 as $enquiry_content_arr2){
									if($enquiry_content_arr2['name']=="tour_name"){
										$tour_name = $enquiry_content_arr2['value'];
									}
								}
							}else{
								$tour_name = 'NA';
							}
							if(($sq_enquiry_entry['followup_status']=="Active" || $sq_enquiry_entry['followup_status']=="In-Followup") && date('Y-m-d', strtotime($sq_enquiry_entry['followup_date'])) == $today){

								$followup_date = explode(':',(explode(' ',$sq_enquiry_entry['followup_date'])[1]));
								$count++;
								$content1 .= '<tr>
									<td style="text-align:left;border: 1px solid #888888;">'.$count.'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.get_enquiry_id($enquiry_id,$year).'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.$row_enq['name'].'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.$row_enq['mobile_no'].'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.$row_enq['email_id'].'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.$row_enq['enquiry_type'].'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.$tour_name.'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.$followup_date[0].':'.$followup_date[1].'</td></tr>';
							}
						}
					$content1 .= '
						</table>
					</td>
				</tr>';
				$username = $row_emp['username'];
				$password = $row_emp['password'];
				$username = $encrypt_decrypt->fnDecrypt($username, $secret_key);
				$password = $encrypt_decrypt->fnDecrypt($password, $secret_key);
				$content1 .= mail_login_box($username, $password, BASE_URL);

				$subject = 'Todays Leads Follow-up Reminders : '.date('d-m-Y');
				global $model, $app_email_id;
				$model->app_email_send('69',"Team",$row_emp['email_id'], $content1,$subject,'1');
			}
		}
	}

	// $content2 = '';
	// $sq_birthday_count = mysqli_num_rows(mysqlQuery("select birth_date from customer_master where DAYOFMONTH(birth_date)='$day' and MONTH(birth_date)='$month' "));

	// if($sq_birthday_count>0){

	// 	$content2 .= '
	// 	<tr>
	// 		<td>
	// 			<table style="background: #fff; color: #22262e; font-size: 13px;width:100%; margin-bottom:20px;">
	// 				<tr>
	// 					<th colspan="4">Todays Birthdays</th>
	// 				</tr>
	// 				<tr>
	// 					<th style=" padding-left: 10px;border: 1px solid #c1c1c1;text-align: left;font-weight: 500;background: #ddd;font-size: 14px;color: #22262E">Sr. No</th>
	// 					<th style=" padding-left: 10px;border: 1px solid #c1c1c1;text-align: left;font-weight: 500;background: #ddd;font-size: 14px;color: #22262E">Customer</th>
	// 					<th style=" padding-left: 10px;border: 1px solid #c1c1c1;text-align: left;font-weight: 500;background: #ddd;font-size: 14px;color: #22262E">Birth Date</th>
	// 					<th style=" padding-left: 10px;border: 1px solid #c1c1c1;text-align: left;font-weight: 500;background: #ddd;font-size: 14px;color: #22262E">Mobile No</th>
	// 				</tr>';
	// 	$count = 0;
	// 	$sq_customer = mysqlQuery("select * from customer_master where DAYOFMONTH(birth_date)='$day' and MONTH(birth_date)='$month' ");
	// 	while($row_customer = mysqli_fetch_assoc($sq_customer)){
			
	// 		$count++;
	// 		$content2 .='
	// 			<tr>
	// 				<td style="color: #777;font-size: 14px;text-align: left;padding-left: 10px;font-weight: 500;">'.$count.'</td>
	// 				<td style="color: #777;font-size: 14px;text-align: left;padding-left: 10px;font-weight: 500;">'.$row_customer['first_name'].' '.$row_customer['last_name'].'</td>
	// 				<td style="color: #777;font-size: 14px;text-align: left;padding-left: 10px;font-weight: 500;">'.get_date_user($row_customer['birth_date']).'</td>
	// 				<td style="color: #777;font-size: 14px;text-align: left;padding-left: 10px;font-weight: 500;">'.$row_customer['contact_no'].'</td>
	// 			</tr>';
	// 	}

	// 	$content2 .= '</table>
	// 			</td>
	// 		</tr>';
	// }
	// $content = '<tr>
	// 	<td>
	// 		<table style="padding:0 30px; width:100%">
	// 			'.$content1.'
	// 		</table>
	// 	</td>
	// </tr>';

	// if($followup_count>0 || $sq_birthday_count>0){

	// 	$subject = 'Todays Leads Follow-up Reminders : '.date('d-m-Y');
	// 	global $model, $app_email_id;
	// 	$model->app_email_send('69',"Team",$app_email_id, $content,$subject,'1');
	// }
}
?>
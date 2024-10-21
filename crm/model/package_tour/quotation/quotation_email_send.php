<?php
// include_once('../../model.php');
class quotation_email_send
{
	public function quotation_email(){

		$quotation_id_arr = $_POST['quotation_id_arr'];
		global $currency,$app_cancel_pdf,$model,$quot_note,$theme_color;
		$i = 0;

		if($app_cancel_pdf == ''){	$url =  BASE_URL.'view/package_booking/quotation/cancellaion_policy_msg.php'; }

		else{
			$url = explode('uploads', $app_cancel_pdf);
			$url = BASE_URL.'uploads'.$url[1];
		}
		$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$quotation_id_arr[0]'"));
		$sq_cost =  mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id = '$quotation_id_arr[0]'"));

		for($i=0;$i<sizeof($quotation_id_arr);$i++)
		{
			$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$quotation_id_arr[$i]'"));
			$sq_cost =  mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id = '$quotation_id_arr[$i]'"));
			$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_quotation[emp_id]'"));

			if($sq_emp_info['first_name']==''){
				$emp_name = 'Admin';
			}
			else{
				$emp_name = $sq_emp_info['first_name'].' '.$sq_emp_info['last_name'];
			}		
			
			$quotation_cost = $sq_cost['total_tour_cost'] + $sq_quotation['train_cost'] + $sq_quotation['flight_cost'] + $sq_quotation['cruise_cost'] + $sq_quotation['visa_cost'] + $sq_quotation['guide_cost'] + $sq_quotation['misc_cost'];
			////////////////Currency conversion ////////////
			$currency_amount1 = currency_conversion($currency,$sq_quotation['currency_code'],$quotation_cost);
			
			$sq_tours_package = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id = '$sq_quotation[package_id]'"));
			
			$quotation_no = base64_encode($quotation_id_arr[$i]);
			
			$content = '   
			
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Package Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_tours_package['package_name'].'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Travel Date</td>   <td style="text-align:left;border: 1px solid #888888;">'.get_date_user($sq_quotation['from_date']) . ' To ' . get_date_user($sq_quotation['to_date']).'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Duration</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_quotation['total_days'].'N/'.($sq_quotation['total_days']+1).'D'.'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Quotation Cost</td><td style="text-align:left;border: 1px solid #888888;">'.$currency_amount1.'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">View Quotation</td><td style="text-align:left;border: 1px solid #888888;width:30%"><a style="color: '.$theme_color.';text-decoration: none;" href="'.BASE_URL.'model/package_tour/quotation/single_quotation.php?quotation='.$quotation_no.'">View</a></td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Created By</td>   <td style="text-align:left;border: 1px solid #888888;">'.$emp_name.'</td></tr>
				</table>
			</tr>	';
		}
		$content .= '
		<tr>
			<table style="width:100%;margin-top:20px">
				<tr>
					<td style="padding-left: 10px;border-bottom: 1px solid #eee;"><span style="font-weight: 600; color: '.$theme_color.'">'.$quot_note.'</span></td>
				</tr>
			</table>	
		<tr>';

		$subject = 'New Quotation : ('.$sq_tours_package['package_name'].' )';
		$model->app_email_send('8',$sq_quotation['customer_name'],$sq_quotation['email_id'], $content,$subject,'1');

		echo "Quotation successfully sent.";
		exit;
	}

	public function quotation_email_body(){

		$quotation_id_arr = $_POST['quotation_id_arr'];
		global $currency,$bank_name_setting,$bank_branch_name,$acc_name,$bank_acc_no,$bank_swift_code,$bank_account_name,$app_cancel_pdf,$model,$quot_note,$tcs_note,$theme_color;
		$i = 0;

		if($app_cancel_pdf == ''){	$url =  BASE_URL.'view/package_booking/quotation/cancellaion_policy_msg.php'; }

		else{

			$url = explode('uploads', $app_cancel_pdf);

			$url = BASE_URL.'uploads'.$url[1];

		}
		$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$quotation_id_arr[0]'"));
		$sq_cost =  mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id = '$quotation_id_arr[0]'"));

		for($i=0;$i<sizeof($quotation_id_arr);$i++)
		{
			$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$quotation_id_arr[$i]'"));
			$tcs_note_show = ($sq_quotation['booking_type'] != 'Domestic') ? $tcs_note : '';
			$sq_cost =  mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id = '$quotation_id_arr[$i]'"));
			$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where id='$sq_quotation[login_id]'"));
			$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_quotation[emp_id]'"));

			if($sq_emp_info['first_name']==''){
				$emp_name = 'Admin';
			}
			else{
				$emp_name = $sq_emp_info['first_name'].' '.$sq_emp_info['last_name'];
			}		
			
			$quotation_cost = $sq_cost['total_tour_cost'] + $sq_quotation['train_cost'] + $sq_quotation['flight_cost'] + $sq_quotation['cruise_cost'] + $sq_quotation['visa_cost'] + $sq_quotation['guide_cost'] + $sq_quotation['misc_cost'];
			////////////////Currency conversion ////////////
			$currency_amount1 = currency_conversion($currency,$sq_quotation['currency_code'],$quotation_cost);
			
			$sq_tours_package = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id = '$sq_quotation[package_id]'"));
			
			$sq_cost =  mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id = '$quotation_id_arr[$i]'"));
			$sq_tours_package = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id = '$sq_quotation[package_id]'"));
			

			$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where id='$sq_quotation[login_id]'"));
			$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_login[emp_id]'"));
			
			if($sq_emp_info['first_name']==''){
				$emp_name = 'Admin';
			}
			else{
				$emp_name = $sq_emp_info['first_name'].' '.$sq_emp_info['last_name'];
			}

			$quotation_id=$quotation_id_arr[$i];
			$sq_package_program = mysqlQuery("select * from  package_quotation_program where quotation_id='$quotation_id_arr[$i]'");

			$sq_trans_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_transport_entries2 where quotation_id='$quotation_id_arr[$i]'"));
			$sq_hotel = mysqlQuery("select * from package_tour_quotation_transport_entries2 where quotation_id='$quotation_id_arr[$i]'");

			$sq_hotel_count1 = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_hotel_entries where quotation_id='$quotation_id_arr[$i]'"));

			$sq_train_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_train_entries where quotation_id='$quotation_id_arr[$i]'"));
			$sq_train = mysqlQuery("select * from package_tour_quotation_train_entries where quotation_id='$quotation_id_arr[$i]'");
			
			$sq_plane_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_plane_entries where quotation_id='$quotation_id_arr[$i]'"));
  			$sq_plane = mysqlQuery("select * from package_tour_quotation_plane_entries where quotation_id='$quotation_id_arr[$i]'");

			$sq_cruise_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_cruise_entries where quotation_id='$quotation_id_arr[$i]'"));
			$sq_train_cruise = mysqlQuery("select * from package_tour_quotation_cruise_entries where quotation_id='$quotation_id_arr[$i]'");
			$sq_ex_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_excursion_entries where quotation_id='$quotation_id_arr[$i]'"));
			$sq_ex = mysqlQuery("select * from package_tour_quotation_excursion_entries where quotation_id='$quotation_id_arr[$i]'");
			$sq_query = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Package Quotation' and active_flag='Active'"));
			$sq_tours_package = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id = '$sq_quotation[package_id]'"));
			////////////////Currency conversion ////////////
			$currency_amount1 = currency_conversion($currency,$sq_quotation['currency_code'],$quotation_cost);
			$quotation_date = $sq_quotation['quotation_date'];
			$yr = explode("-", $quotation_date);
			$year = $yr[0];

			$content = '   
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
					background: #009898;">PACKAGE TOUR DETAILS</td></tr>
				</table>
			</tr>
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Package Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_tours_package['package_name'].'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Duration</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_quotation['total_days'].'N/'.($sq_quotation['total_days']+1).'D'.'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Travel Date</td> <td style="text-align:left;border: 1px solid #888888;">'.date('d-m-Y', strtotime($sq_quotation['from_date'])).' To '.date('d-m-Y', strtotime($sq_quotation['to_date'])).'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Quotation ID</td>   <td style="text-align:left;border: 1px solid #888888;">'.get_quotation_id($sq_quotation['quotation_id'],$year).'</td></tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Created By</td>   <td style="text-align:left;border: 1px solid #888888;">'.$emp_name.'</td></tr>
				</table>
			</tr>	
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
					background: #009898;">GUEST DETAILS</td></tr>
				</table>
			</tr>
			
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<tr>
						<td style="text-align:left;border: 1px solid #888888;width:30%">Adult(s)</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_quotation['total_adult'].'</td>
						<td style="text-align:left;border: 1px solid #888888;width:30%">Child With Bed</td><td style="text-align:left;border: 1px solid #888888;">'.$sq_quotation['children_with_bed'].'</td>
					</tr>
					<tr>
						<td style="text-align:left;border: 1px solid #888888;width:30%">Child Without Bed</td> <td style="text-align:left;border: 1px solid #888888;">'.$sq_quotation['children_without_bed'].'</td>
						<td style="text-align:left;border: 1px solid #888888;width:30%">Infant(s) </td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_quotation['total_infant'].'</td>
					</tr>
					<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Total</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_quotation['total_passangers'].'</td></tr>
				</table>
			</tr>';
			$content .= '
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
					background: #009898;">TOUR ITINERARY</td></tr>
				</table>
			</tr>';
			
			$count = 0;
			$i = 0;
			
			while($row_itinarary = mysqli_fetch_assoc($sq_package_program)){

				$dates =(array) get_dates_for_package_itineary($row_itinarary["quotation_id"]);
				$date_format = isset($dates[$i]) ? $dates[$i] : 'NA';

				$sq_day_image = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_images where quotation_id='$row_itinarary[quotation_id]' and package_id='$sq_quotation[package_id]'"));
				$day_url1 = explode(',',$sq_day_image['image_url']);
				$daywise_image = 'http://itourscloud.com/quotation_format_images/dummy-image.jpg';
				for($count1 = 0; $count1<sizeof($day_url1);$count1++){
					$day_url2 = explode('=',$day_url1[$count1]);
					if($day_url2[0]==$sq_quotation['package_id'] && $day_url2[1]==$row_itinarary['day_count']){
						$daywise_image = $day_url2[2];
					}
				}
					
				$count++;
				$content .= '<tr>
					<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
						<tr>
							<td style="text-align:left;border: 1px solid #888888;width:20%"><b>Day : </b> '.$count.' ('.$date_format.')'.'</td> 
							<td style="text-align:left;border: 1px solid #888888;width:60%"><b>Attraction : </b>'.$row_itinarary['attraction'].'</td>
						</tr>
						
						<tr>
							<td style="text-align:left;border: 1px solid #888888;width:20%"><img src="'.$daywise_image .'" class="img-responsive" style="width:200px;height:200px"></td>
							<td style="text-align:left;border: 1px solid #888888;width:60%">'.$row_itinarary['day_wise_program'].'</td> 
						</tr>
						
						<tr>
							<td style="text-align:left;border: 1px solid #888888;width:20%"><b>Overnight stay : </b>'.$row_itinarary['stay'].'</td>
							<td style="text-align:left;border: 1px solid #888888;width:60%"><b>Meal Plan : </b>'.$row_itinarary['meal_plan'].'</td>
						</tr>
					</table>
				</tr>
			';
			}
			
			if($sq_hotel_count1>0){
				$content .= '   
				
				<tr>
					<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
						<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
						background: #009898;">ACCOMMODATION DETAILS</td></tr>
					</table>
				</tr>';
				$sq_package_type = mysqlQuery("select DISTINCT(package_type) from package_tour_quotation_hotel_entries where quotation_id='$quotation_id' order by package_type");
			
				while($row_hotel1 = mysqli_fetch_assoc($sq_package_type)){
				$content .= '
				<tr>
					<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<thead>
						<tr class="table-heading-row">
							<th colspan="6" style="text-align:left;border: 1px solid #888888;width:30%">Package Type - '.$row_hotel1['package_type'].'</th>
						</tr>
						<tr class="table-heading-row">
							<th style="text-align:left;border: 1px solid #888888;width:30%">City</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Hotel Name</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Hotel Category</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Check-In</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Check-Out</th>
						</tr>
					</thead>
					<tbody> 
					';
					$sq_package_type1 = mysqlQuery("select * from package_tour_quotation_hotel_entries where quotation_id='$quotation_id' and package_type='$row_hotel1[package_type]'");
					while($row_hotels = mysqli_fetch_assoc($sq_package_type1)){

						$hotel_name = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$row_hotels[hotel_name]'"));
						$city_name = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_hotels[city_name]'"));
					$content .='	
						<tr>
							<td style="text-align:left;border: 1px solid #888888;width:30%">'.$city_name['city_name'].'</td>
							<td style="text-align:left;border: 1px solid #888888;">'.$hotel_name['hotel_name'].'</td>
							<td style="text-align:left;border: 1px solid #888888;width:30%">'.$row_hotels['hotel_type'].'</td>   
							<td style="text-align:left;border: 1px solid #888888;">'.get_date_user($row_hotels['check_in']).'</td>
							<td style="text-align:left;border: 1px solid #888888;">'.get_date_user($row_hotels['check_out']).'</td>
						</tr>';
					}
					$content .='
						<tbody> 
					</table>
				</tr>';
				}
			}
			
			if($sq_trans_count>0){
			$content .= '   
			
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
					background: #009898;">TRANSPORT DETAILS</td></tr>
				</table>
			</tr>
			<tr>
				<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
				<thead>
					<tr class="table-heading-row">
						<th style="text-align:left;border: 1px solid #888888;width:30%">Vehicle</th>
						<th style="text-align:left;border: 1px solid #888888;width:30%">Start Date</th>
						<th style="text-align:left;border: 1px solid #888888;width:30%">End Date</th>
						<th style="text-align:left;border: 1px solid #888888;width:30%">Pickup Location</th>
						<th style="text-align:left;border: 1px solid #888888;width:30%">Drop Location</th>
						<th style="text-align:left;border: 1px solid #888888;width:30%">Service Duration</th>
						<th style="text-align:left;border: 1px solid #888888;width:30%">Total Vehicles</th>
					</tr>
				</thead>
				<tbody> 
				';
				while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
              
					$transport_name = mysqli_fetch_assoc(mysqlQuery("select * from b2b_transfer_master where entry_id='$row_hotel[vehicle_name]'"));
					// Pickup
					if($row_hotel['pickup_type'] == 'city'){
					  $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_hotel[pickup]'"));
					  $pickup = $row['city_name'];
					}
					else if($row_hotel['pickup_type'] == 'hotel'){
					  $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_hotel[pickup]'"));
					  $pickup = $row['hotel_name'];
					}
					else{
					  $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_hotel[pickup]'"));
					  $airport_nam = clean($row['airport_name']);
					  $airport_code = clean($row['airport_code']);
					  $pickup = $airport_nam." (".$airport_code.")";
					}
					//Drop-off
					if($row_hotel['drop_type'] == 'city'){
					  $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_hotel[drop]'"));
					  $drop = $row['city_name'];
					}
					else if($row_hotel['drop_type'] == 'hotel'){
					  $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_hotel[drop]'"));
					  $drop = $row['hotel_name'];
					}
					else{
					  $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_hotel[drop]'"));
					  $airport_nam = clean($row['airport_name']);
					  $airport_code = clean($row['airport_code']);
					  $drop = $airport_nam." (".$airport_code.")";
					}
				$content .='	
					<tr>
						<td style="text-align:left;border: 1px solid #888888;width:30%">'.$transport_name['vehicle_name'].'</td>
						<td style="text-align:left;border: 1px solid #888888;">'.get_date_user($row_hotel['start_date']).'</td>
						<td style="text-align:left;border: 1px solid #888888;">'.get_date_user($row_hotel['end_date']).'</td>
						<td style="text-align:left;border: 1px solid #888888;width:30%">'.$pickup.'</td>   <td style="text-align:left;border: 1px solid #888888;">'.$drop.'</td>
						<td style="text-align:left;border: 1px solid #888888;">'.$row_hotel['service_duration'].'</td>
						<td style="text-align:left;border: 1px solid #888888;">'.$row_hotel['vehicle_count'].'</td>
					</tr>';
				}
				$content .='
					<tbody> 
				</table>
			</tr>';
				
			}
			if($sq_plane_count>0){
				$content .= '   
				
				<tr>
					<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
						<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
						background: #009898;">FLIGHT DETAILS</td></tr>
					</table>
				</tr>
				<tr>
					<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
					<thead>
						<tr class="table-heading-row">
							<th style="text-align:left;border: 1px solid #888888;width:30%">From Sector</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">To Sector</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Airline</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Class</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Departure</th>
							<th style="text-align:left;border: 1px solid #888888;width:30%">Arrival</th>
						</tr>
					</thead>
					<tbody> 
					';
					while($row_plane = mysqli_fetch_assoc($sq_plane)){

						$sq_airline = mysqli_fetch_assoc(mysqlQuery("select * from airline_master where airline_id='$row_plane[airline_name]'"));
						$airline = ($row_plane['airline_name'] != '') ? $sq_airline['airline_name'].' ('.$sq_airline['airline_code'].')' : 'NA';
						$class = ($row_plane['class']!='')?$row_plane['class']:'NA';
						
					$content .='	
						<tr>
							<td style="text-align:left;border: 1px solid #888888;width:30%">'.$row_plane['from_location'].'</td>
							<td style="text-align:left;border: 1px solid #888888;">'.$row_plane['to_location'].'</td>
							<td style="text-align:left;border: 1px solid #888888;width:30%">'.$airline.'</td> 
							<td style="text-align:left;border: 1px solid #888888;width:30%">'.$class.'</td>   
							<td style="text-align:left;border: 1px solid #888888;">'.date('d-m-Y H:i', strtotime($row_plane['dapart_time'])).'</td>
							<td style="text-align:left;border: 1px solid #888888;">'.date('d-m-Y H:i', strtotime($row_plane['arraval_time'])).'</td>
						</tr>';
					}
					$content .='
						<tbody> 
					</table>
				</tr>';
					
				}
				if($sq_train_count>0){
					$content .= '   
					<tr>
						<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
							<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
							background: #009898;">TRAIN DETAILS</td></tr>
						</table>
					</tr>
					<tr>
						<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
						<thead>
							<tr class="table-heading-row">
								<th style="text-align:left;border: 1px solid #888888;width:30%">From Location</th>
								<th style="text-align:left;border: 1px solid #888888;width:30%">To Location</th>
								<th style="text-align:left;border: 1px solid #888888;width:30%">Class Type</th>
								<th style="text-align:left;border: 1px solid #888888;width:30%">Departure</th>
								<th style="text-align:left;border: 1px solid #888888;width:30%">Arrival</th>
							</tr>
						</thead>
						<tbody> 
						';
						while($row_train = mysqli_fetch_assoc($sq_train)){
							
						$class = ($row_train['class']!='')?$row_train['class']:'NA';
							$content .='
								<tr>
									<td style="text-align:left;border: 1px solid #888888;width:30%">'.$row_train['from_location'].'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.$row_train['to_location'].'</td>
									<td style="text-align:left;border: 1px solid #888888;width:30%">'.$class.'</td>   
									<td style="text-align:left;border: 1px solid #888888;">'.date('d-m-Y H:i', strtotime($row_train['departure_date'])).'</td>
									<td style="text-align:left;border: 1px solid #888888;">'.date('d-m-Y H:i', strtotime($row_train['arrival_date'])).'</td>
								</tr>';
							}
							$content .='
								<tbody> 
							</table>
						</tr>';
					}
						
						if($sq_cruise_count>0){
							$content .= '   
							
							<tr>
								<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
									<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
									background: #009898;">CRUISE DETAILS</td></tr>
								</table>
							</tr>
							<tr>
								<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
								<thead>
									<tr class="table-heading-row">
										<th style="text-align:left;border: 1px solid #888888;width:30%">Departure D/T</th>
										<th style="text-align:left;border: 1px solid #888888;width:30%">Arrival D/T</th>
										<th style="text-align:left;border: 1px solid #888888;width:30%">Route</th>
										<th style="text-align:left;border: 1px solid #888888;width:30%">Cabin</th>
										<th style="text-align:left;border: 1px solid #888888;width:30%">Sharing</th>
									</tr>
								</thead>
								<tbody> 
								';
								while($row_train = mysqli_fetch_assoc($sq_train_cruise)){ 
																		
								$content .='	
									<tr>
										<td style="text-align:left;border: 1px solid #888888;width:30%">'.get_datetime_user($row_train['dept_datetime']).'</td>
										<td style="text-align:left;border: 1px solid #888888;">'.get_datetime_user($row_train['arrival_datetime']).'</td>
										<td style="text-align:left;border: 1px solid #888888;width:30%">'.$row_train['route'].'</td> 
										<td style="text-align:left;border: 1px solid #888888;width:30%">'.$row_train['cabin'].'</td>   
										<td style="text-align:left;border: 1px solid #888888;">'.$row_train['sharing'].'</td>
									</tr>';
								}
								$content .='
									<tbody> 
								</table>
							</tr>';
								
							}
							
							if($sq_ex_count>0){
								$content .= '   
								
								<tr>
									<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
										<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
										background: #009898;">ACTIVITY DETAILS</td></tr>
									</table>
								</tr>
								<tr>
									<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
									<thead>
										<tr class="table-heading-row">
											<th style="text-align:left;border: 1px solid #888888;width:30%">Activity Date</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">City Name</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Activity Name</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Transfer option</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Adult</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">CWB</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">CWOB</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Infant</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Vehicle</th>
										</tr>
									</thead>
									<tbody> 
									';
									while($row_ex = mysqli_fetch_assoc($sq_ex)){ 
										$sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_ex[city_name]'"));
              							$sq_ex_name = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master_tariff where entry_id='$row_ex[excursion_name]'"));
										
										$content .='	
											<tr>
												<td style="text-align:left;border: 1px solid #888888;width:30%">'.get_datetime_user($row_ex['exc_date']).'</td>
												<td style="text-align:left;border: 1px solid #888888;">'.$sq_city['city_name'].'</td>
												<td style="text-align:left;border: 1px solid #888888;width:30%">'.$sq_ex_name['excursion_name'].'</td> 
												<td style="text-align:left;border: 1px solid #888888;">'.$row_ex['transfer_option'].'</td>
												<td style="text-align:left;border: 1px solid #888888;">'.$row_ex['adult'].'</td>
												<td style="text-align:left;border: 1px solid #888888;">'.$row_ex['chwb'].'</td>
												<td style="text-align:left;border: 1px solid #888888;">'.$row_ex['chwob'].'</td>
												<td style="text-align:left;border: 1px solid #888888;">'.$row_ex['infant'].'</td>
												<td style="text-align:left;border: 1px solid #888888;">'.$row_ex['vehicles'].'</td>
											</tr>';
										}
										$content .='
										<tbody> 
									</table>
								</tr>';
								}
					if(isset($sq_query['terms_and_conditions'])){
						$content .= '<tr>
								<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
									<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
									background: #009898;">TERMS AND CONDITIONS</td></tr>
								</table>
							</tr>
							<tr>
								<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
									<tr>
										<td style="text-align:left;border: 1px solid #888888;width:100%"><pre>'. $sq_query['terms_and_conditions'].'</pre></td></tr>
								</table>
							</tr>';
					}
					$content .= '<tr>
						<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
							<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
							background: #009898;">INCLUSIONS</td></tr>
						</table>
					</tr>
					<tr>
						<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
							<tr>
								<td style="text-align:left;border: 1px solid #888888;width:100%"><pre>'. $sq_quotation['inclusions'].'</pre></td></tr>
						</table>
					</tr>';
					$content .= '<tr>
						<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
							<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
							background: #009898;">EXCLUSIONS</td></tr>
						</table>
					</tr>
					<tr>
						<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
							<tr>
								<td style="text-align:left;border: 1px solid #888888;width:100%"><pre>'. $sq_quotation['exclusions'].'</pre></td></tr>
						</table>
					</tr>';
					if($sq_tours_package['note'] != ''){
						$content .= '<tr>
							<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
								<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
								background: #009898;">NOTE</td></tr>
							</table>
						</tr>
						<tr>
							<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
								<tr>
									<td style="text-align:left;border: 1px solid #888888;width:100%"><pre>'. $sq_tours_package['note'].'</pre></td></tr>
							</table>
						</tr>';
					}
					if($sq_quotation['other_desc'] != ''){
						$content .= '<tr>
							<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
								<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
								background: #009898;">MISCELLANEOUS DESCRIPTION</td></tr>
							</table>
						</tr>
						<tr>
							<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
								<tr>
									<td style="text-align:left;border: 1px solid #888888;width:100%"><pre>'. $sq_quotation['other_desc'].'</pre></td></tr>
							</table>
						</tr>';
					}

					$content .= '
						<tr>
							<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
								<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
								background: #009898;">BANK DETAILS</td></tr>
							</table>
						</tr>
						<tr>
							<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
							<thead>
								<tr class="table-heading-row">
									<th style="text-align:left;border: 1px solid #888888;width:30%">Bank Name</th>
									<th style="text-align:left;border: 1px solid #888888;width:30%">Branch</th>
									<th style="text-align:left;border: 1px solid #888888;width:30%">A/C Type</th>
									<th style="text-align:left;border: 1px solid #888888;width:30%">Bank Account Name</th>
									<th style="text-align:left;border: 1px solid #888888;width:30%">A/C No</th>
									
									<th style="text-align:left;border: 1px solid #888888;width:30%">SWIFT Code</th>
								</tr>
							</thead>
							<tbody> 
							';
							$branch_admin_id = $_SESSION['branch_admin_id'];
							global $bank_name_setting,$bank_branch_name,$acc_name,$bank_acc_no,$bank_account_name,$bank_swift_code;
							if ($branch_admin_id != 0) {
								$sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
								$sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
							} else {
								$sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='1' and active_flag='Active'"));
								$sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='1' and active_flag='Active'"));
							}
							$bank_name = ($sq_bank_count>0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting;
							$branch_name = ($sq_bank_count>0 || $sq_bank_branch['branch_name'] != '') ? $sq_bank_branch['branch_name'] : $bank_branch_name;
							$acc_type = ($sq_bank_count>0 || $sq_bank_branch['account_type'] != '') ? $sq_bank_branch['account_type'] : $acc_name;
							$acc_no = ($sq_bank_count>0 || $sq_bank_branch['account_no'] != '') ? $sq_bank_branch['account_no'] : $bank_acc_no;
							$acc_name1 = ($sq_bank_count>0 || $sq_bank_branch['account_name'] != '') ? $sq_bank_branch['account_name'] : $bank_account_name;
							$swift_code = ($sq_bank_count>0 || $sq_bank_branch['swift_code'] != '') ? strtoupper($sq_bank_branch['swift_code']) :  strtoupper($bank_swift_code);
								$content .='	
									<tr>
										<td style="text-align:left;border: 1px solid #888888;width:30%">'.$bank_name.'</td>
										<td style="text-align:left;border: 1px solid #888888;">'.$branch_name .'</td>
										<td style="text-align:left;border: 1px solid #888888;width:30%">'.$acc_type.'</td> 
										<td style="text-align:left;border: 1px solid #888888;">'.$acc_name1.'</td>
										<td style="text-align:left;border: 1px solid #888888;">'.$acc_no.'</td>
										<td style="text-align:left;border: 1px solid #888888;">'.$swift_code.'</td>
									</tr>';
									if(check_qr()){
									
									$content .= '<tr> 
									<td style="text-align:left;width:30%" colspan=2>QR Code</td>
									<td style="text-align:left;width:30%" colspan=4>'.get_qr('general').' </td>
									
									</tr>';
									}
								$content .='
								<tbody> 
							</table>
						</tr>';
							
						$content .='
						<tr>
							<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
								<tr><td style="text-align:center;border: 1px solid #888888;width:1000%;color: #fff;
								background: #009898;">COSTING DETAILS</td></tr>
							</table>
						</tr>';
						if($sq_quotation['costing_type'] == 2){
			
							$sq_costing1 = mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id='$quotation_id' order by package_type");
							while($sq_costing = mysqli_fetch_assoc($sq_costing1)){
			
								$service_charge = $sq_costing['service_charge'];
								$discount_in = $sq_costing['discount_in'];
								$discount = $sq_costing['discount'];
								if($discount_in == 'Percentage'){
								  $act_discount = floatval($service_charge) * floatval($discount) / 100;
								}else{
								  $act_discount = ($service_charge != 0) ? $discount : 0;
								}
								$service_charge = $service_charge - floatval($act_discount);
								$total_pax = floatval($sq_quotation['total_adult']) + floatval($sq_quotation['children_with_bed']) + floatval($sq_quotation['children_without_bed']) + floatval($sq_quotation['total_infant']);
								$per_service_charge = floatval($service_charge) / floatval($total_pax);
								$o_per_service_charge = floatval($sq_costing['service_charge']) / floatval($total_pax);
					
								$adult_cost = ($sq_quotation['total_adult'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['adult_cost'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
								$child_with = ($sq_quotation['children_with_bed'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['child_with'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
								$child_without = ($sq_quotation['children_without_bed'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['child_without'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
								$infant_cost = ($sq_quotation['total_infant'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['infant_cost'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
					
								// Without currency
								$adult_costw = ($sq_quotation['total_adult'] != '0') ? (floatval($sq_costing['adult_cost'] + floatval($per_service_charge)) * intval($sq_quotation['total_adult'])) : 0;
								$child_withw = ($sq_quotation['children_with_bed'] != '0') ? (floatval($sq_costing['child_with'] + floatval($per_service_charge)) * intval($sq_quotation['children_with_bed'])) : 0;
								$child_withoutw = ($sq_quotation['children_without_bed'] != '0') ? (floatval($sq_costing['child_without'] + floatval($per_service_charge)) * intval($sq_quotation['children_without_bed'])) : 0;
								$infant_costw = ($sq_quotation['total_infant'] != '0') ? (floatval($sq_costing['infant_cost'] + floatval($per_service_charge)) * intval($sq_quotation['total_infant'])) : 0;
								$o_adult_costw = ($sq_quotation['total_adult'] != '0') ? (floatval($sq_costing['adult_cost'] + floatval($o_per_service_charge)) * intval($sq_quotation['total_adult'])) : 0;
								$o_child_withw = ($sq_quotation['children_with_bed'] != '0') ? (floatval($sq_costing['child_with'] + floatval($o_per_service_charge)) * intval($sq_quotation['children_with_bed'])) : 0;
								$o_child_withoutw = ($sq_quotation['children_without_bed'] != '0') ? (floatval($sq_costing['child_without'] + floatval($o_per_service_charge)) * intval($sq_quotation['children_without_bed'])) : 0;
								$o_infant_costw = ($sq_quotation['total_infant'] != '0') ? (floatval($sq_costing['infant_cost'] + floatval($o_per_service_charge)) * intval($sq_quotation['total_infant'])) : 0;
					
								$service_tax_amount = 0;
								$tax_show = '';
								$bsmValues = json_decode($sq_costing['bsmValues']);
								$name = '';
								if ($sq_costing['service_tax_subtotal'] !== 0.00 && ($sq_costing['service_tax_subtotal']) !== '') {
									$service_tax_subtotal1 = explode(',', $sq_costing['service_tax_subtotal']);
									for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
										$service_tax = explode(':', $service_tax_subtotal1[$i]);
										$service_tax_amount = floatval($service_tax_amount) + floatval($service_tax[2]);
										$name .= $service_tax[0] . $service_tax[1] . ', ';
									}
								}

								if($bsmValues[0]->tcsvalue != ''){ 
									$tcsvalue = $bsmValues[0]->tcsvalue;
									
									}else{
										$tcsvalue='0';
									}
									$tcs_amount_show  = currency_conversion($currency, $sq_quotation['currency_code'], $tcsvalue);


								$service_tax_amount_show = currency_conversion($currency, $sq_quotation['currency_code'], $service_tax_amount);
					
								$total_child = floatval($sq_quotation['children_with_bed']) + floatval($sq_quotation['children_without_bed']);
					
								$quotation_cost = floatval($adult_costw) + floatval($child_withw) + floatval($child_withoutw) + floatval($infant_costw);
								$o_quotation_cost = floatval($o_adult_costw) + floatval($o_child_withw) + floatval($o_child_withoutw) + floatval($o_infant_costw);
					
								$other_cost = $service_tax_amount + $sq_quotation['visa_cost'] + $sq_quotation['guide_cost'] + $sq_quotation['misc_cost'];
								$travel_cost = ($sq_plane_count > 0) ? $sq_quotation['flight_ccost'] + $sq_quotation['flight_icost'] + $sq_quotation['flight_acost'] : 0;
								$travel_cost += ($sq_train_count > 0) ? $sq_quotation['train_ccost'] + $sq_quotation['train_icost'] + $sq_quotation['train_acost'] : 0;
								$travel_cost += ($sq_cruise_count > 0) ?  $sq_quotation['cruise_acost'] + $sq_quotation['cruise_icost'] + $sq_quotation['cruise_ccost'] : 0;
					
								$quotation_cost = floatval($quotation_cost) + floatval($travel_cost) + floatval($other_cost)+floatval($tcsvalue);
								$quotation_cost = ceil($quotation_cost);
								$currency_amount1 = currency_conversion($currency, $sq_quotation['currency_code'], $quotation_cost);
								$o_quotation_cost = floatval($o_quotation_cost) + floatval($travel_cost) + floatval($other_cost);
								$act_tour_cost_camount = ($discount!=0) ? currency_conversion($currency, $sq_quotation['currency_code'], $o_quotation_cost+floatval($tcsvalue)) : '';
			
							$content .= '
							<tr>
								<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
									<tr>
										<td colspan="4" style="text-align:left;border: 1px solid #888888;width:30%">Package Type: '.$sq_costing['package_type'] . ' (' . $currency_amount1 . ' <s>'.$act_tour_cost_camount.'</s>)'.'</td>
									</tr>
									<tr>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Adult Cost</td>  <td style="text-align:left;border: 1px solid #888888;">'.$adult_cost.'</td>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Child with Bed Cost </td>   <td style="text-align:left;border: 1px solid #888888;">'.$child_with.'</td>
									</tr>
									<tr>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Child w/o Bed Cost</td><td style="text-align:left;border: 1px solid #888888;">'.$child_without.'</td>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Infant Cost</td> <td style="text-align:left;border: 1px solid #888888;">'.$infant_cost.'</td>
									</tr>
									<tr>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Tax</td><td style="text-align:left;border: 1px solid #888888;">'.$service_tax_amount_show.'</td>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Visa Cost </td> <td style="text-align:left;border: 1px solid #888888;">'.currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['visa_cost']).'</td>
									</tr>
									<tr>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Guide Cost </td> <td style="text-align:left;border: 1px solid #888888;">'.currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['guide_cost']).'</td>
										<td style="text-align:left;border: 1px solid #888888;width:30%">Miscellaneous Cost </td> <td style="text-align:left;border: 1px solid #888888;">'.currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['misc_cost']).'</td>
										
									</tr>
									<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Tcs </td> <td style="text-align:left;border: 1px solid #888888;">'.$tcs_amount_show.'</td>
									<td style="text-align:left;border: 1px solid #888888;width:30%"> </td> <td style="text-align:left;border: 1px solid #888888;"></td>
									</tr>
								</table>
							</tr>';
							if($sq_plane_count > 0 || $sq_train_count > 0 || $sq_cruise_count > 0){
								$content .= '<tr>
									<table width="85%" cellspacing="0" cellpadding="4" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
									<thead>
										<tr class="table-heading-row">
											<th style="text-align:left;border: 1px solid #888888;width:30%">Travel_Type</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Adult(PP)</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Child(PP)</th>
											<th style="text-align:left;border: 1px solid #888888;width:30%">Infant(PP)</th>
										</tr>
									</thead>
									<tbody>';
								if($sq_plane_count>0){
							
									$content .='	
										<tr>
											<td style="text-align:left;border: 1px solid #888888;width:30%">Flight</td>
											<td style="text-align:left;border: 1px solid #888888;">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_acost'])).'</td>
											<td style="text-align:left;border: 1px solid #888888;width:30%">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_ccost'])).'</td> 
											<td style="text-align:left;border: 1px solid #888888;width:30%">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_icost'])).'</td>
										</tr>';
								}
								if($sq_train_count>0){
							
									$content .='	
										<tr>
											<td style="text-align:left;border: 1px solid #888888;width:30%">Train</td>
											<td style="text-align:left;border: 1px solid #888888;">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_acost'])).'</td>
											<td style="text-align:left;border: 1px solid #888888;width:30%">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_ccost'])).'</td> 
											<td style="text-align:left;border: 1px solid #888888;width:30%">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_icost'])).'</td>
										</tr>';
								}
								if($sq_cruise_count>0){
							
									$content .='	
										<tr>
											<td style="text-align:left;border: 1px solid #888888;width:30%">Cruise</td>
											<td style="text-align:left;border: 1px solid #888888;">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['cruise_acost'])).'</td>
											<td style="text-align:left;border: 1px solid #888888;width:30%">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['cruise_ccost'])).'</td> 
											<td style="text-align:left;border: 1px solid #888888;width:30%">'.currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['cruise_icost'])).'</td>
										</tr>';
								}
								// if($tcs_note_show != ''){
								// 	$content .= '
								// 	<tr>
								// 		<table style="width:100%;margin-top:10px">
								// 			<tr>
								// 				<td style="padding-left: 10px;border-bottom: 1px solid #eee;"><span style="font-weight: 600; color: '.$theme_color.'">'.$tcs_note_show.'</span></td>
								// 			</tr>
								// 		</table>	
								// 	<tr>';
								// }
								$content .='
									<tbody> 
								</table>
							</tr>';
							}
						}
						}else{
							$sq_costing1 = mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id='$quotation_id' order by package_type");
							while($sq_costing = mysqli_fetch_assoc($sq_costing1)){
								
								$basic_cost = $sq_costing['basic_amount'];
								$service_charge = $sq_costing['service_charge'];
								$service_tax_amount = 0;
								$tax_show = '';
								$bsmValues = json_decode($sq_costing['bsmValues']);
								$discount_in = $sq_costing['discount_in'];
								$discount = $sq_costing['discount'];
								if($discount_in == 'Percentage'){
								  $act_discount = floatval($service_charge) * floatval($discount) / 100;
								}else{
								  $act_discount = ($service_charge != 0) ? $discount : 0;
								}
								$service_charge = $service_charge - floatval($act_discount);
								$tour_cost = $basic_cost + $service_charge;
		  
								$name = '';
								if ($sq_costing['service_tax_subtotal'] !== 0.00 && ($sq_costing['service_tax_subtotal']) !== '') {
								  $service_tax_subtotal1 = explode(',', $sq_costing['service_tax_subtotal']);
								  for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
									$service_tax = explode(':', $service_tax_subtotal1[$i]);
									$service_tax_amount = floatval($service_tax_amount) + floatval($service_tax[2]);
									$name .= $service_tax[0] . $service_tax[1] . ', ';
								  }
								}

								if($bsmValues[0]->tcsvalue != ''){ 
									$tcsvalue = $bsmValues[0]->tcsvalue;
									
									}else{
										$tcsvalue='0';
									}

								

								$service_tax_amount_show = currency_conversion($currency, $sq_quotation['currency_code'], $service_tax_amount);
								$quotation_cost = $basic_cost + $service_charge + $service_tax_amount + $sq_quotation['train_cost'] + $sq_quotation['cruise_cost'] + $sq_quotation['flight_cost'] + $sq_quotation['visa_cost'] + $sq_quotation['guide_cost'] + $sq_quotation['misc_cost']+ $tcsvalue;
								$quotation_cost = ceil($quotation_cost);
								$tcsvalue = currency_conversion($currency, $sq_quotation['currency_code'], $tcsvalue);
								////////////////Currency conversion ////////////
								$currency_amount1 = currency_conversion($currency, $sq_quotation['currency_code'], $quotation_cost);
								$act_tour_cost = floatval($quotation_cost) - floatval($service_charge) + floatval($sq_costing['service_charge']);
                                $act_tour_cost = ceil($act_tour_cost);
								$act_tour_cost_camount = ($discount!=0) ? currency_conversion($currency, $sq_quotation['currency_code'], $act_tour_cost) : '';
		  
								$newBasic = currency_conversion($currency, $sq_quotation['currency_code'], $tour_cost);
								$travel_cost = floatval($sq_quotation['train_cost']) + floatval($sq_quotation['flight_cost']) + floatval($sq_quotation['cruise_cost']) + floatval($sq_quotation['visa_cost']) + floatval($sq_quotation['guide_cost']) + floatval($sq_quotation['misc_cost']);
								$travel_cost = currency_conversion($currency, $sq_quotation['currency_code'], $travel_cost);
								$content .= '
								<tr>
									<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
										<tr><td colspan="6" style="text-align:left;border: 1px solid #888888;width:30%">'.$sq_costing['package_type'].'</td></tr>
										<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Tour Cost</td>   <td style="text-align:left;border: 1px solid #888888;">'.$newBasic.'</td><td colspan="1" style="text-align:left;border: 1px solid #888888;width:30%">Tax </td>   <td style="text-align:left;border: 1px solid #888888;">'.$service_tax_amount_show.'</td><td style="text-align:left;border: 1px solid #888888;width:30%">Tcs</td><td colspan="4" style="text-align:left;border: 1px solid #888888;width:30%">'.$tcsvalue.'</td></tr>
										<tr><td style="text-align:left;border: 1px solid #888888;width:30%">Travel and other cost</td><td style="text-align:left;border: 1px solid #888888;">'.$travel_cost.'</td><td style="text-align:left;border: 1px solid #888888;width:30%">Quotation Cost</td> <td style="text-align:left;border: 1px solid #888888;">'.$currency_amount1.' <s>'.$act_tour_cost_camount.'</s>'.'</td><td style="text-align:left;border: 1px solid #888888;width:30%"> </td><td style="text-align:left;border: 1px solid #888888;width:30%"></td></tr>
									</table>
								</tr>';
								// for cond end
								// if($tcs_note_show != ''){
								// 	$content .= '<tr> 
								// 	<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;margin: 0px auto;margin-top:10px; min-width: 100%;" role="presentation">
								// 	<tr><td style="text-align:left;width:30%;margin-left:30px!important;" colspan=6>'.$tcs_note_show.' </td></tr>
								// 	</table>
								// 	</tr>';
								// }
							}
						}
		} 
		$content .= '
		<tr>
			<table style="width:100%;margin-top:20px">
				<tr>
					<td style="padding-left: 10px;border-bottom: 1px solid #eee;"><span style="font-weight: 600; color: '.$theme_color.'">'.$quot_note.'</span></td>
				</tr>
			</table>	
		<tr>';

		$subject = 'New Quotation : ('.$sq_tours_package['package_name'].' )';
		$model->app_email_send('8',$sq_quotation['customer_name'],$sq_quotation['email_id'], $content,$subject,'1');

		echo "Quotation successfully sent.";
		exit;
	}

	public function quotation_whatsapp(){

		$quotation_id_arr = $_POST['quotation_id_arr'];
		global $app_contact_no,$app_name;
		
		$all_message = "";
		for($i=0;$i<sizeof($quotation_id_arr);$i++){
			$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$quotation_id_arr[$i]'"));
			$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_quotation[emp_id]'"));
			if($sq_quotation['emp_id'] == 0){
				$contact = $app_contact_no;
			}
			else{
				$contact = $sq_emp_info['mobile_no'];
			}
			$sq_tours_package = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id = '$sq_quotation[package_id]'"));		

			$quotation_no = base64_encode($quotation_id_arr[$i]);
		$whatsapp_msg = rawurlencode('Dear '.$sq_quotation['customer_name'].',
Hope you are doing great. This is package tour quotation details as per your request. We look forward to having you onboard with us.
*Destination* : '.$sq_tours_package['package_name'].'
*Duration* : '.$sq_quotation['total_days'].'N/'.($sq_quotation['total_days']+1).'D'.'
*Link* : '.BASE_URL.'model/package_tour/quotation/single_quotation.php?quotation='.$quotation_no.'

Please contact for more details : '.$app_name.' '.$contact.'
Thank you.');
			$all_message .=$whatsapp_msg;
		}
		$link = 'https://web.whatsapp.com/send?phone='.$sq_quotation['mobile_no'].'&text='.$all_message;
		echo $link;
	}
}
?>
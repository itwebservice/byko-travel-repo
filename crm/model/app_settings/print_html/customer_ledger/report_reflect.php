<?php
include "../../../../model/model.php";
include "../print_functions.php";
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = isset($_POST['branch_status']) ? $_POST['branch_status'] : '';
$branch_id = isset($_POST['branch_id_filter']) ? $_POST['branch_id_filter'] : ''; 
$customer_id = $_GET['customer_id'];
$from_date1 = $_GET['from_date'];
$to_date1 = $_GET['to_date'];

$from_date = get_date_db($from_date1);
$to_date = get_date_db($to_date1);
$count = 0;
$total_amount = 0;
$total_paid   = 0;
$total_balance = 0;
$total_cancel = 0;

if ($branch_admin_id != 0) {
  $branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$branch_admin_id'"));
  $sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
  $sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
} else {
  $branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='1'"));
  $sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='1' and active_flag='Active'"));
  $sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='1' and active_flag='Active'"));
}
?>

<!-- header -->
<section class="print_header main_block">
	<div class="col-md-8 no-pad">
	<span class="title"><i class="fa fa-file-text"></i> Outstanding Payment Summary Report</span>
		<div class="print_header_logo">
		<img src="<?php echo $admin_logo_url; ?>" class="img-responsive mg_tp_10">
		</div>
	</div>
	<div class="col-md-4 no-pad">
		<div class="print_header_contact text-right">
		<span class="title"><?php echo $app_name; ?></span><br>
		<p><?php echo ($branch_status=='yes' && $role!='Admin') ? $branch_details['address1'].','.$branch_details['address2'].','.$branch_details['city'] : $app_address ?></p>
		<p class="no-marg"><i class="fa fa-phone" style="margin-right: 5px;"></i> <?php echo ($branch_status=='yes' && $role!='Admin') ? 
		$branch_details['contact_no'] : $app_contact_no ?></p>
		<p><i class="fa fa-envelope" style="margin-right: 5px;"></i> <?php echo $app_email_id; ?></p>

		</div>
	</div>

		<div class="row">
			<div class="col-xs-12">
				<div class="print_info_block">
					<ul class="main_block noType">
						<?php $cust_name=mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'")); ?>
					<li class="col-md-12"><span>CUSTOMER NAME : </span><?= ($cust_name['type'] == 'Corporate'||$cust_name['type'] == 'B2B') ? $cust_name['company_name'] : $cust_name['first_name'].' '.$cust_name['last_name']  ?></li>
					<li class="col-md-6"><span>FROM DATE : </span> <?= $from_date1 ?></li>
					<li class="col-md-6"><span>TO DATE : </span> <?= $to_date1 ?></li>
					</ul>
				</div>
			</div>
		</div>

    <!-- print-detail -->
		<div class="row"> <div class="col-md-12"> <div class="table-responsive">

		<table class="table table-bordered" id="tbl_list" style="width:1500px !important;padding: 0 !important; background: #fff;">
			<thead>
				<tr class="table-heading-row">
					<th>S_No.</th>
					<th>Service(booking_date)</th>
					<th>booking_ID</th>
					<th class="text-right info">Sale</th>
					<th class="text-right success">Paid</th>
					<th class="text-right danger">Cancel</th>
					<th class="text-right warning">Balance</th>
				</tr>
			</thead>
			<tbody>
			<?php
			//B2C
			if($customer_id!="" || $from_date!='' && $to_date!=''){
				$query = "select * from b2c_sale where 1 ";

				if($customer_id!=""){
					$query .=" and customer_id='$customer_id'";
				}
				if($from_date!='' || $to_date!=''){
					$query .=" and DATE(created_at) between '$from_date' and '$to_date'";
				}
				$sq_booking = mysqlQuery($query);
				while($row_sale = mysqli_fetch_assoc($sq_booking)){

					$date = $row_sale['created_at'];
					$yr = explode("-", $date);
					$year =$yr[0];
					$guest_data = json_decode($row_sale['guest_data']);
					$pass_name = ' ('.$guest_data[0]->adult[0]->honorific.' '.$guest_data[0]->adult[0]->first_name.' '.$guest_data[0]->adult[0]->last_name.')';
					$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(`credit_charges`) as sumc from b2c_payment_master where booking_id='$row_sale[booking_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
					$credit_card_charges = $sq_payment_info['sumc'];
					$paid_amount = $sq_payment_info['sum'];
					
					$costing_data = json_decode($row_sale['costing_data']);
					$enq_data = json_decode($row_sale['enq_data']);
					$net_total = $costing_data[0]->net_total;
				
					$cancel_amount = $row_sale['cancel_amount'];
					$total_cost1 = $net_total - $cancel_amount;
					
					if ($row_sale['status'] == 'Cancel') {
						if ($cancel_amount <= $paid_amount) {
							$balance_amount = 0;
						} else {
							$balance_amount =  $cancel_amount - $paid_amount;
						}
					} else {
						$cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
						$balance_amount = $net_total - $paid_amount;
					}
					if(floatval($balance_amount) > 0){
						?>
						<tr>
							<td><?= ++$count ?></td>
							<td><?= "B2C Booking".'('.$row_sale['service'].')'. get_date_user($row_sale['created_at']) ?></td>
							<td><?= get_b2c_booking_id($row_sale['booking_id'],$year).$pass_name ?></td>
							<td class="info text-right"><?= number_format($net_total,2)?></td>
							<td class="text-right success"><?= number_format($paid_amount,2) ?></td>
							<td class="danger text-right"><?= number_format($cancel_amount,2)?></td>
							<td class="warning text-right"><?= number_format($balance_amount,2)?></td>
						</tr>
						<?php
						$total_amount +=$net_total;
						$total_paid   +=$paid_amount;
						$total_balance+=$balance_amount;
						$total_cancel +=$cancel_amount;
					}
				}
			}
			//B2B
			global $currency; //Get default currency rate
			$sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
			$to_currency_rate = $sq_to['currency_rate'];
			if($customer_id!="" || $from_date!='' && $to_date!=''){
				$query = "select * from b2b_booking_master where 1 ";

				if($customer_id!=""){
					$query .=" and customer_id='$customer_id'";
				}
				if($from_date!='' || $to_date!=''){
					$query .=" and DATE(created_at) between '$from_date' and '$to_date'";
				}
				$sq_booking = mysqlQuery($query);
				while($row_sale = mysqli_fetch_assoc($sq_booking)){

					$date = $row_sale['created_at'];
					$yr = explode("-", $date);
					$year =$yr[0];
					
					$cart_checkout_data = json_decode($row_sale['cart_checkout_data']);

					$hotel_list_arr = array();
					$transfer_list_arr = array();
					$activity_list_arr = array();
					$tours_list_arr = array();
					$ferry_list_arr = array();
					$group_list_arr = array();
					for($i=0;$i<sizeof($cart_checkout_data);$i++){
						if($cart_checkout_data[$i]->service->name == 'Hotel'){
							array_push($hotel_list_arr,$cart_checkout_data[$i]);
						}
						if($cart_checkout_data[$i]->service->name == 'Transfer'){
							array_push($transfer_list_arr,$cart_checkout_data[$i]);
						}
						if($cart_checkout_data[$i]->service->name == 'Activity'){
							array_push($activity_list_arr,$cart_checkout_data[$i]);
						}
						if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
							array_push($tours_list_arr,$cart_checkout_data[$i]);
						}
						if($cart_checkout_data[$i]->service->name == 'Ferry'){
							array_push($ferry_list_arr,$cart_checkout_data[$i]);
						}
						if($cart_checkout_data[$i]->service->name == 'Group Tours'){
							array_push($group_list_arr,$cart_checkout_data[$i]);
						}
					}
					$hotel_total = 0;
					// Hotel
					if(sizeof($hotel_list_arr)>0){
						$tax_arr = explode(',',$hotel_list_arr[$i]->service->hotel_arr->tax);
						for($j=0;$j<sizeof($hotel_list_arr[$i]->service->item_arr);$j++){
							$room_types = explode('-',$hotel_list_arr[$i]->service->item_arr[$j]);
							$room_cost = $room_types[2];
							$h_currency_id = $room_types[3];
							$tax_amount = 0;
							
							$tax_arr1 = explode('+',$tax_arr[0]);
							for($t=0;$t<sizeof($tax_arr1);$t++){
								if($tax_arr1[$t]!=''){
									$tax_arr2 = explode(':',$tax_arr1[$t]);
									if($tax_arr2[2] == "Percentage"){
										$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
									}else{
										$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
									}
								}
							}
							$total_amount = $room_cost + $tax_amount;
							//Convert into default currency
							$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
							$from_currency_rate = $sq_from['currency_rate'];
							$total_amount1 = ($from_currency_rate / $to_currency_rate * $total_amount);

							$hotel_total += $total_amount1;
						}
					}
					//Transfer
					$transfer_total = 0;
					if(sizeof($transfer_list_arr)>0){
						for($i=0;$i<sizeof($transfer_list_arr);$i++){
							
							$services = ($transfer_list_arr[$i]->service!='') ? $transfer_list_arr[$i]->service : [];
							for($j=0;$j<count(array($services));$j++){
							
								$tax_arr = explode(',',$services->service_arr[$j]->taxation);
								$transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
								$room_cost = $transfer_cost[0];
								$h_currency_id = $transfer_cost[1];
								$tax_amount = 0;
								
								$tax_arr1 = explode('+',$tax_arr[0]);
								for($t=0;$t<sizeof($tax_arr1);$t++){
									if($tax_arr1[$t]!=''){
										$tax_arr2 = explode(':',$tax_arr1[$t]);
										if($tax_arr2[2] == "Percentage"){
											$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
										}else{
											$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
										}
									}
								}
								$total_amount = $room_cost + $tax_amount;
								//Convert into default currency
								$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
								$from_currency_rate = $sq_from['currency_rate'];
								$total_amount1 = ($from_currency_rate / $to_currency_rate * $total_amount);
		
								$transfer_total += $total_amount1;
							}
						}
					}
					// Activity
					$activity_total = 0;
					if(sizeof($activity_list_arr)>0){
						$tax_amount = 0;
						$tax_arr = explode(',',$activity_list_arr[$i]->service->service_arr[0]->taxation);
						$transfer_types = explode('-',$activity_list_arr[$i]->service->service_arr[0]->transfer_type);
						$transfer = $transfer_types[0];
						$room_cost = $transfer_types[1];
						$h_currency_id = $transfer_types[2];
						
						$tax_arr1 = explode('+',$tax_arr[0]);
						for($t=0;$t<sizeof($tax_arr1);$t++){
							if($tax_arr1[$t]!=''){
								$tax_arr2 = explode(':',$tax_arr1[$t]);
								if($tax_arr2[2] === "Percentage"){
									$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								}else{
									$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
								}
							}
						}
						$total_amount = $room_cost + $tax_amount;
						//Convert into default currency
						$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
						$from_currency_rate = $sq_from['currency_rate'];
						$total_amount1 = ($from_currency_rate / $to_currency_rate * $total_amount);

						$activity_total += $total_amount1;
					}
					// Holiday
					$tours_total = 0;
					if(sizeof($tours_list_arr)>0){
						$tax_amount = 0;
						$tax_arr = explode(',',$tours_list_arr[$i]->service->service_arr[0]->taxation);
						$room_cost = $package_item[1];
						$h_currency_id = $package_item[2];
						
						$tax_arr1 = explode('+',$tax_arr[0]);
						for($t=0;$t<sizeof($tax_arr1);$t++){
							if($tax_arr1[$t]!=''){
								$tax_arr2 = explode(':',$tax_arr1[$t]);
								if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
								}
							}
						}
						$total_amount = $room_cost + $tax_amount;
						//Convert into default currency
						$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
						$from_currency_rate = $sq_from['currency_rate'];
						$total_amount1 = ($from_currency_rate / $to_currency_rate * $total_amount);
	
						$tours_total += $total_amount1;
					}
					// Ferry
					$ferry_total = 0;
					if(sizeof($ferry_list_arr)>0){
						for($i=0;$i<sizeof($ferry_list_arr);$i++){
							$package_item = explode('-',$ferry_list_arr[$i]->service->service_arr[0]->total_cost);
							$tax_amount = 0;
							$tax_arr = explode(',',$ferry_list_arr[$i]->service->service_arr[0]->taxation);
							$room_cost = $package_item[0];
							$h_currency_id = $package_item[1];
							
							$tax_arr1 = explode('+',$tax_arr[0]);
							for($t=0;$t<sizeof($tax_arr1);$t++){
								if($tax_arr1[$t]!=''){
									$tax_arr2 = explode(':',$tax_arr1[$t]);
									if($tax_arr2[2] == "Percentage"){
									$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
									}else{
									$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
									}
								}
							}
							$total_amount = $room_cost + $tax_amount;
							//Convert into default currency
							$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
							$from_currency_rate = $sq_from['currency_rate'];
							$total_amount1 = ($from_currency_rate / $to_currency_rate * $total_amount);
		
							$ferry_total += $total_amount1;
						}
					}
					// Group Tour
					$gtours_total = 0;
					if(sizeof($group_list_arr)>0){
						for($i=0;$i<sizeof($group_list_arr);$i++){
	
							$services = isset($group_list_arr[$i]->service) ? $group_list_arr[$i]->service : [];
							for($j=0;$j<count(array($services));$j++){
								$tax_arr = explode(',',$group_list_arr[$i]->service->service_arr[$j]->taxation);
								$room_cost = $group_list_arr[$i]->service->service_arr[$j]->total_cost;
								$h_currency_id = $group_list_arr[$i]->service->service_arr[$j]->currency_id;
								
								$tax_amount = 0;
								$tax_arr1 = explode('+',$tax_arr[0]);
								for($t=0;$t<sizeof($tax_arr1);$t++){
									if($tax_arr1[$t]!=''){
										$tax_arr2 = explode(':',$tax_arr1[$t]);
										if($tax_arr2[2] == "Percentage"){
											$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
										}else{
											$tax_amount = $tax_amount + $tax_arr2[1];
										}
									}
								}
								$total_amount = $room_cost + $tax_amount;
								//Convert into default currency
								$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
								$from_currency_rate = $sq_from['currency_rate'];
								$total_amount1 = ($from_currency_rate / $to_currency_rate * $total_amount);
			
								$gtours_total += $total_amount1;
							}
						}
					}
					$grnd_total = $hotel_total + $transfer_total + $activity_total + $tours_total + $ferry_total + $gtours_total;
					if($row_sale['coupon_code'] != ''){
						$sq_hotel_count = mysqli_num_rows(mysqlQuery("select offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_sale[coupon_code]'"));
						$sq_exc_count = mysqli_num_rows(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_sale[coupon_code]'"));
						if($sq_hotel_count > 0){
							$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer as offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_sale[coupon_code]'"));
						}else if($sq_exc_count > 0){
							$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_sale[coupon_code]'"));
						}else{
							$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from custom_package_offers where coupon_code='$row_sale[coupon_code]'"));
						}
					
						if($sq_coupon['offer']=="Flat"){
							$grnd_total = $grnd_total - $sq_coupon['offer_amount'];
						}else{
							$grnd_total = $grnd_total - ($grnd_total*$sq_coupon['offer_amount']/100);
						}
					}
					$query1 = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from b2b_payment_master where booking_id='$row_sale[booking_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
					$paid_amount = $query1['sum'];
					$paid_amount = ($paid_amount == '') ? '0' : $paid_amount;
					$sale_total_amount = $grnd_total;
					if ($sale_total_amount == "") {
						$sale_total_amount = 0;
					}
					$cancel_amount = $row_sale['cancel_amount'];
					if ($row_sale['status'] == 'Cancel') {
						if ($cancel_amount <= $paid_amount) {
							$balance_amount = 0;
						} else {
							$balance_amount =  $cancel_amount - $paid_amount;
						}
					} else {
						$cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
						$balance_amount = $sale_total_amount - $paid_amount;
					}
					if(floatval($balance_amount) > 0){
						?>
						<tr>
							<td><?= ++$count ?></td>
							<td><?= "B2B Booking ".' ('.get_date_user($row_sale['created_at']).')' ?></td>
							<td><?= get_b2b_booking_id($row_sale['booking_id'],$year) ?></td>
							<td class="info text-right"><?= number_format($sale_total_amount,2)?></td>
							<td class="text-right success"><?= number_format($paid_amount,2) ?></td>
							<td class="danger text-right"><?= number_format($cancel_amount,2)?></td>
							<td class="warning text-right"><?= number_format($balance_amount,2)?></td>
						</tr>
						<?php
						$total_amount +=$sale_total_amount;
						$total_paid   +=$paid_amount;
						$total_balance+=$balance_amount;
						$total_cancel +=$cancel_amount;
					}
				}
			}
			//FIT
			$query = "select * from package_tour_booking_master where 1 and delete_status='0' ";
			if($customer_id!=""){
				$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
				$query .=" and booking_date between '$from_date' and '$to_date'";
			}
			$sq_booking = mysqlQuery($query);
			while($row_booking = mysqli_fetch_assoc($sq_booking)){
				
				$date = $row_booking['booking_date'];
				$yr = explode("-", $date);
				$year = $yr[0];
				$sale_total_amount=$row_booking['net_total'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum from package_payment_master where booking_id='$row_booking[booking_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '')?0:$paid_amount;

				$q = "select * from package_refund_traveler_estimate where booking_id='$row_booking[booking_id]'";
				$cancel_est_count = mysqli_num_rows(mysqlQuery($q));
				$cancel_est = mysqli_fetch_assoc(mysqlQuery($q));
				$cancel_amount = ($cancel_est_count > 0) ? $cancel_est['cancel_amount'] : 0;
				if ($cancel_est_count > 0) {
					if ($cancel_amount <= $paid_amount) {
						$balance_amount = 0;
					} else {
						$balance_amount =  $cancel_amount - $paid_amount;
					}
				} else {
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
					$sq_entry = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from package_travelers_details where booking_id='$row_booking[booking_id]'"));
					$pass_name = ($sq_entry['first_name'] != '' ) ? ' ('.$sq_entry['first_name']." ".$sq_entry['last_name'].')' : '';
					?>
					<tr>
						<td><?= ++$count ?></td>
						<td><?= "Package Booking ".' ('.get_date_user($row_booking['booking_date']).')' ?></td>
						<td><?= get_package_booking_id($row_booking['booking_id'],$year).$pass_name ?></td>
						<td class="info text-right"><?= number_format($sale_total_amount,2)?></td>
						<td class="text-right success"><?= number_format($paid_amount,2) ?></td>
						<td class="danger text-right"><?= number_format($cancel_amount,2)?></td>
						<td class="warning text-right"><?= number_format($balance_amount,2)?></td>
					</tr>
					<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount;
					$total_cancel +=$cancel_amount;
				}
			}
			//visa
			$query = "select * from visa_master where 1 and delete_status='0'";
			if($customer_id!=""){
				$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
				$query .=" and created_at between '$from_date' and '$to_date'";
			}
			$sq_visa = mysqlQuery($query);
			while($row_visa = mysqli_fetch_assoc($sq_visa)){
			
				$date = $row_visa['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];

				//Sale
				$sale_total_amount=$row_visa['visa_total_cost'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//Cancel
				$cancel_amount=$row_visa['cancel_amount'];
				$pass_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$row_visa[visa_id]'"));
				$cancel_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$row_visa[visa_id]' and status='Cancel'"));

				//Paid
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from visa_payment_master where visa_id='$row_visa[visa_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];

				$paid_amount = ($paid_amount == '')?'0':$paid_amount;

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
						$balance_amount = $sale_total_amount - $paid_amount;
					}
					if(floatval($balance_amount) > 0){
						$sq_entry = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from visa_master_entries where visa_id='$row_visa[visa_id]'"));
						$pass_name = ($sq_entry['first_name'] != '' ) ? ' ('.$sq_entry['first_name']." ".$sq_entry['last_name'].')' : '';
						?>	
						<tr>
							<td><?= ++$count ?></td>
							<td><?= "Visa Booking".' ('.get_date_user($row_visa['created_at']).')'?></td>
							<td><?= get_visa_booking_id($row_visa['visa_id'],$year).$pass_name ?></td>
							<td class="info text-right"><?= number_format($sale_total_amount,2) ?></td>
							<td class="success text-right"><?= number_format($paid_amount,2) ?></td>
							<td class="danger text-right"><?= number_format($cancel_amount,2) ?></td>
							<td class="warning text-right"><?= number_format($balance_amount,2) ?></td>
						</tr>
					<?php
						$total_amount +=$sale_total_amount;
						$total_paid   +=$paid_amount;
						$total_balance+=$balance_amount;
						$total_cancel +=$cancel_amount;
					}
			}
			//Air Ticket
			$query = "select * from ticket_master where 1 and delete_status='0' ";
			if($customer_id!=""){
				$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
				$query .=" and created_at between '$from_date' and '$to_date'";
			}
			$sq_ticket = mysqlQuery($query);
			while($row_ticket = mysqli_fetch_assoc($sq_ticket)){

				$date = $row_ticket['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
				//Sale
				$sale_total_amount=$row_ticket['ticket_total_cost'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//Cancel
				$cancel_amount=$row_ticket['cancel_amount'];
				$pass_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_ticket[ticket_id]'"));
				$cancel_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_ticket[ticket_id]' and status='Cancel'"));

				//Paid
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from ticket_payment_master where ticket_id='$row_ticket[ticket_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '')?'0':$paid_amount;

				if($row_ticket['cancel_type'] == '1'){
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
				}else if($row_ticket['cancel_type'] == '2'||$row_ticket['cancel_type'] == '3'){
					$cancel_estimate = json_decode($row_ticket['cancel_estimate']);
					$balance_amount = (($sale_total_amount - floatval($cancel_estimate[0]->ticket_total_cost)) + $cancel_amount) - $paid_amount;
				}
				else{
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
					$sq_entry = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from ticket_master_entries where ticket_id='$row_ticket[ticket_id]'"));
					$pass_name = ($sq_entry['first_name'] != '' ) ? ' ('.$sq_entry['first_name']." ".$sq_entry['last_name'].')' : '';
				?>	
					<tr>
						<td><?= ++$count ?></td>
						<td><?= "Flight Ticket".' ('.get_date_user($row_ticket['created_at']).')' ?></td>
						<td><?= get_ticket_booking_id($row_ticket['ticket_id'],$year).$pass_name ?></td>
						<td class="info text-right"><?= number_format($sale_total_amount,2) ?></td>
						<td class="text-right success"><?= ($paid_amount=="") ? number_format(0,2) : number_format($paid_amount,2) ?></td>
						<td class="danger text-right"><?= number_format($cancel_amount,2) ?></td>
						<td class="warning text-right"><?= number_format($balance_amount,2) ?></td>
					</tr>
				<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount;
					$total_cancel +=$cancel_amount;
				}
			}
			//Train ticket
			$query = "select * from train_ticket_master where 1 and delete_status='0'";
			if($customer_id!=""){
			$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
			$query .=" and created_at between '$from_date' and '$to_date'";
			}
			$sq_ticket = mysqlQuery($query);
			while($row_ticket = mysqli_fetch_assoc($sq_ticket)){

				$date = $row_ticket['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
				//sale
				$sale_total_amount=$row_ticket['net_total'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
				//Cancel
				$cancel_amount=$row_ticket['cancel_amount'];
				$pass_count = mysqli_num_rows(mysqlQuery("select * from  train_ticket_master_entries where train_ticket_id='$row_ticket[train_ticket_id]'"));
				$cancel_count = mysqli_num_rows(mysqlQuery("select * from  train_ticket_master_entries where train_ticket_id='$row_ticket[train_ticket_id]' and status='Cancel'"));


				//Paid
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from train_ticket_payment_master where train_ticket_id='$row_ticket[train_ticket_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '')?'0':$paid_amount;

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
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
					$sq_entry = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from train_ticket_master_entries where train_ticket_id='$row_ticket[train_ticket_id]'"));
					$pass_name = ($sq_entry['first_name'] != '' ) ? ' ('.$sq_entry['first_name']." ".$sq_entry['last_name'].')' : '';
					?>
					<tr>
						<td><?= ++$count ?></td>
							<td><?= "Train Ticket Booking".' ('.get_date_user($row_ticket['created_at']).')' ?></td>
						<td><?= get_train_ticket_booking_id($row_ticket['train_ticket_id'],$year).$pass_name ?></td>
						<td class="text-right info"><?= number_format($sale_total_amount,2) ?></td>
						<td  class="text-right success"><?= ($paid_amount=="") ? number_format(0,2) : number_format($paid_amount,2) ?></td>
						<td class="text-right danger"><?= number_format($cancel_amount,2) ?></td>
						<td class="text-right warning"><?= number_format($balance_amount,2)?></td>
					</tr>
					<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount;
					$total_cancel +=$cancel_amount;
				}
				
			}
			//Hotel 
			$query = "select * from hotel_booking_master where 1 and delete_status='0' ";
			$query .=" and customer_id='$customer_id'";
			if($from_date!='' || $to_date!=''){
				$query .=" and created_at between '$from_date' and '$to_date'";
			}	
			$sq_booking = mysqlQuery($query);
			while($row_booking = mysqli_fetch_assoc($sq_booking)){

				$date = $row_booking['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
				//sale 
				$sale_total_amount=$row_booking['total_fee'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//Cancel
				$cancel_amount=$row_booking['cancel_amount'];
				$pass_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$row_booking[booking_id]'"));
				$cancel_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$row_booking[booking_id]' and status='Cancel'"));

				//Paid
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from hotel_booking_payment where booking_id='$row_booking[booking_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '')?'0':$paid_amount;
				if($pass_count == $cancel_count){
					if($paid_amount > 0){
						if($cancel_amount >0){
							if($paid_amount > $cancel_amount){
								$balance_amount1 = 0;
							}else{
								$balance_amount1 = $cancel_amount - $paid_amount;
							}
						}else{
						$balance_amount1 = 0;
						}
					}
					else{
						$balance_amount1 = $cancel_amount;
					}
				}
				else{
					$balance_amount1 = $sale_total_amount - $paid_amount;
				}
				if($balance_amount1 > 0){
					$pass_name = ($row_booking['pass_name'] != '' ) ? ' ('.$row_booking['pass_name'].')' : '';
				?>
				<tr>
					<td><?= ++$count ?></td>
					<td><?= "Hotel Booking".' ('.get_date_user($row_booking['created_at']).')' ?></td>
					<td><?= get_hotel_booking_id($row_booking['booking_id'],$year).$pass_name ?></td>
					<td class="text-right  info"><?= number_format($sale_total_amount,2) ?></td>
					<td class="text-right  success"><?= number_format($paid_amount,2)?></td>
					<td class="text-right danger"><?= number_format($cancel_amount,2) ?></td>
					<td class="text-right warning"><?= number_format($balance_amount1,2) ?></td>	
				</tr>
				<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount1;
					$total_cancel +=$cancel_amount;
				}
			}
			//Bus
			$query = "select * from bus_booking_master where 1 and delete_status='0' ";
			if($customer_id!=""){
			$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
			$query .=" and created_at between '$from_date' and '$to_date'";
			}
			$sq_booking = mysqlQuery($query);
			while($row_booking = mysqli_fetch_assoc($sq_booking)){
				
				$date = $row_booking['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
				//sale 
				$sale_total_amount=$row_booking['net_total'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//paid
				$cancel_amount=$row_booking['cancel_amount'];
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from bus_booking_payment_master where booking_id='$row_booking[booking_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '')?'0':$paid_amount;

				//Cancel
				$cancel_amount=$row_booking['cancel_amount'];
				$pass_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$row_booking[booking_id]'"));
				$cancel_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$row_booking[booking_id]' and status='Cancel'"));

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
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
				?>
				<tr>
					<td><?= ++$count ?></td>
					<td><?= "Bus Booking".' ('.get_date_user($row_booking['created_at']).')' ?></td>
					<td><?= get_bus_booking_id($row_booking['booking_id'],$year) ?></td>
					<td class="text-right info"><?= number_format($sale_total_amount,2) ?></td>
					<td class="text-right success"><?= number_format($paid_amount,2) ?></td>
					<td class="text-right danger"><?= number_format($cancel_amount,2) ?></td>
					<td class="text-right warning"><?= number_format($balance_amount,2) ?></td>
				</tr>
				<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount;
					$total_cancel +=$cancel_amount;
				}
			}
			//Car Rental
			$query = "select * from car_rental_booking where 1 and delete_status='0' ";
			if($customer_id!=""){
			$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
			$query .=" and created_at between '$from_date' and '$to_date'";
			}
			$sq_booking = mysqlQuery($query);
			while($row_booking = mysqli_fetch_assoc($sq_booking))
			{
				$count++;
				$date = $row_booking['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
				//Sale
				$sale_total_amount=$row_booking['total_fees'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//Cacnel
				$cancel_amount=$row_booking['cancel_amount'];

				//Paid
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from car_rental_payment where booking_id='$row_booking[booking_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '')?'0':$paid_amount;

				if($row_booking['status'] == 'Cancel'){
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
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
					$pass_name = ($row_booking['pass_name'] != '' ) ? ' ('.$row_booking['pass_name'].')' : '';			
				?>
				<tr>
					<td><?= $count ?></td>
					<td><?= "Car Rental".' ('.get_date_user($row_booking['created_at']).')' ?></td>
					<td><?= get_car_rental_booking_id($row_booking['booking_id'],$year).$pass_name ?></td>
					<td class="text-right info"><?= number_format($sale_total_amount, 2) ?></td>
					<td class="text-right success"><?= number_format($paid_amount,2)?></td>
					<td class="text-right danger"><?= number_format($cancel_amount,2)?></td>
					<td class="text-right warning"><?= number_format($balance_amount,2);?></td>
				</tr>
				<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount;
					$total_cancel +=$cancel_amount;
				}
			}
			//Group
			$query = "select * from tourwise_traveler_details where 1 and delete_status='0' ";
			if($customer_id!=""){
				$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
				$query .=" and DATE(form_date) between '$from_date' and '$to_date'";
			}
			$sq1 =mysqlQuery($query);
			while($row1 = mysqli_fetch_assoc($sq1))
			{
				$count++;
				$date = $row1['form_date'];
				$yr = explode("-", $date);
				$year =$yr[0];
				$sale_total_amount=$row1['net_total'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//paid
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum from payment_master where tourwise_traveler_id='$row1[id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '') ? '0' : $paid_amount;

				$pass_count = mysqli_num_rows(mysqlQuery("select * from  travelers_details where traveler_group_id='$row1[traveler_group_id]'"));
				$cancelpass_count = mysqli_num_rows(mysqlQuery("select * from  travelers_details where traveler_group_id='$row1[traveler_group_id]' and status='Cancel'"));
				if($row1['tour_group_status'] == 'Cancel'){
					//Group Tour cancel
					$cancel_tour_count2=mysqli_num_rows(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row1[id]'"));
					if($cancel_tour_count2 >= '1'){
						$q = "SELECT * from refund_tour_estimate where tourwise_traveler_id='$row1[id]'";
						$cancel_tour = mysqli_fetch_assoc(mysqlQuery($q));
						$cancel_tour_count = mysqli_num_rows(mysqlQuery($q));
						$cancel_amount = ($cancel_tour_count > 0) ? $cancel_tour['cancel_amount'] : 0;
					}
					else{ $cancel_amount = 0; }
				}
				else{
					// Group booking cancel
					if($pass_count==$cancelpass_count){
						
						$q = "SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row1[id]'";
						$cancel_tour = mysqli_fetch_assoc(mysqlQuery($q));
						$cancel_tour_count = mysqli_num_rows(mysqlQuery($q));
						$cancel_amount = ($cancel_tour_count > 0) ? $cancel_tour['cancel_amount'] : 0;
					}
					else{ $cancel_amount = 0; }
				}
				
				$cancel_amount = ($cancel_amount == '')?'0':$cancel_amount;
				if($row1['tour_group_status'] == 'Cancel'){
					if($cancel_amount > $paid_amount){
						$balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
					}
					else{
						$balance_amount = 0;
					}
				}else if($pass_count==$cancelpass_count){
					
						if($cancel_amount > $paid_amount){
							$balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
						}
						else{
							$balance_amount = 0;
						}
				}
				else{
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
					$sq_entry = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from travelers_details where traveler_group_id='$row1[traveler_group_id]'"));
					$pass_name = ($sq_entry['first_name'] != '' ) ? ' ('.$sq_entry['first_name']." ".$sq_entry['last_name'].')' : '';
					?>
					<tr>
						<td><?php echo $count ?></td>
						<td><?= "Group Booking".' ('.get_date_user($row1['form_date']).')' ?></td>
						<td><?= get_group_booking_id($row1['id'],$year).$pass_name ?></td>
						<td class="text-right info"><?= number_format($sale_total_amount,2) ?></td>
						<td class="text-right success"><?= number_format($paid_amount,2) ?></td>
						<td class="text-right danger"><?=  number_format($cancel_amount,2)?></td>
						<td class="text-right warning"><?= number_format($balance_amount,2) ?></td>
					</tr>	
					<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount;
					$total_cancel +=$cancel_amount;
				}
			}
			//Excursion
			$query = "select * from excursion_master where 1 and delete_status='0' ";
			if($customer_id!=""){
				$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
				$query .=" and created_at between '$from_date' and '$to_date'";
			}	
			$sq_ex = mysqlQuery($query);
			while($row_ex= mysqli_fetch_assoc($sq_ex)){

				$date = $row_ex['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
				// sale
				$sale_total_amount=$row_ex['exc_total_cost'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//Cancel
				$cancel_amount=$row_ex['cancel_amount'];
				$pass_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$row_ex[exc_id]'"));
				$cancel_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$row_ex[exc_id]' and status='Cancel'"));

				// Paid
				$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from exc_payment_master where exc_id='$row_ex[exc_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query['sum'];
				$paid_amount = ($paid_amount == '')?'0':$paid_amount;

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
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
				?>
				<tr>
					<td><?= ++$count ?></td>
					<td><?= "Excursion Booking".' ('.get_date_user($row_ex['created_at']).')'?></td>
					<td><?= get_exc_booking_id($row_ex['exc_id'],$year) ?></td>
					<td class="info text-right"><?= number_format($sale_total_amount,2) ?></td>
					<td class="success text-right"><?= number_format($paid_amount,2) ?></td>
					<td class="danger text-right"><?= number_format($cancel_amount,2) ?></td>
					<td class="warning text-right"><?= number_format($balance_amount,2) ?></td>
				</tr>
				<?php
					$total_amount +=$sale_total_amount;
					$total_paid   +=$paid_amount;
					$total_balance+=$balance_amount;
					$total_cancel +=$cancel_amount;
				}
			}
			//Miscellaneous
			$query = "select * from miscellaneous_master where 1 and delete_status='0'";
			if($customer_id!=""){
				$query .=" and customer_id='$customer_id'";
			}
			if($from_date!='' || $to_date!=''){
				$query .=" and created_at between '$from_date' and '$to_date'";
			}
			$sq_visa = mysqlQuery($query);
			while($row_visa = mysqli_fetch_assoc($sq_visa)){

				$date = $row_visa['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
			
				//Sale
				$sale_total_amount=$row_visa['misc_total_cost'];
				if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

				//Cancel
				$cancel_amount=$row_visa['cancel_amount'];
				$pass_count = mysqli_num_rows(mysqlQuery("select * from miscellaneous_master_entries where misc_id='$row_visa[misc_id]'"));
				$cancel_count = mysqli_num_rows(mysqlQuery("select * from miscellaneous_master_entries where misc_id='$row_visa[misc_id]' and status='Cancel'"));

				//Paid
				$query1 = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from miscellaneous_payment_master where misc_id='$row_visa[misc_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
				$paid_amount = $query1['sum'];

				$paid_amount = ($paid_amount == '') ? '0' : $paid_amount;

				if($pass_count == $cancel_count){
					if($paid_amount > 0){
						if($cancel_amount > 0){
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
					$balance_amount = $sale_total_amount - $paid_amount;
				}
				if(floatval($balance_amount) > 0){
					$sq_entry = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from miscellaneous_master_entries where misc_id='$row_visa[misc_id]'"));
					$pass_name = ($sq_entry['first_name'] != '' ) ? ' ('.$sq_entry['first_name']." ".$sq_entry['last_name'].')' : '';
					?>
					<tr>
						<td><?= ++$count ?></td>
						<td><?= "Miscellaneous Booking".' ('.get_date_user($row_visa['created_at']).')' ?></td>
						<td><?= get_misc_booking_id($row_visa['misc_id'],$year).$pass_name ?></td>
						<td class="info text-right"><?= number_format($sale_total_amount,2) ?></td>
						<td class="success text-right"><?= number_format($paid_amount,2) ?></td>
						<td class="danger text-right"><?= number_format($cancel_amount,2) ?></td>
						<td class="warning text-right"><?= number_format($balance_amount,2) ?></td>
					</tr>
					<?php
					$total_amount  += $sale_total_amount;
					$total_paid    += $paid_amount;
					$total_balance += $balance_amount;
					$total_cancel  += $cancel_amount;
				}
			}
		?>
		<tr class="active">
			<th colspan="3" class="text-right info">Total</th>
			<th colspan="1" class="text-right info"><?= number_format($total_amount,2) ?></th>

			<th colspan="1" class="text-right success"><?= number_format($total_paid,2) ?></th>

			<th class="text-right danger"><?= ($total_cancel=='')?number_format(0,2): number_format($total_cancel,2); ?></th>

			<th colspan="1" class="text-right warning"><?= number_format($total_balance,2);?></th>

		</tr>

	</tbody>
	</table>

</div> </div> </div>

    <!-- invoice_receipt_back_detail -->

    <div class="border_block inv_rece_back_detail">

        <div class="row">

            <div class="col-md-4">

                <p class="border_lt"><span class="font_5">BANK NAME :

                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting ?>

                </p>

            </div>

            <div class="col-md-4">

                <p class="border_lt"><span class="font_5">A/C TYPE :

                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_type'] != '') ? $sq_bank_branch['account_type'] : $acc_name ?></p>

            </div>

            <div class="col-md-4">

                <p class="border_lt"><span class="font_5">BRANCH :

                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['branch_name'] != '') ? $sq_bank_branch['branch_name'] : $bank_branch_name ?>

                </p>

            </div>

            <div class="col-md-4">

                <p class="border_lt no-marg"><span class="font_5">A/C NO :

                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_no'] != '') ? $sq_bank_branch['account_no'] : $bank_acc_no ?>

                </p>

            </div>

            <div class="col-md-4">

                <p class="border_lt no-marg"><span class="font_5">IFSC/SWIFT Code :

                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['ifsc_code'] != '') ? strtoupper($sq_bank_branch['ifsc_code']) : strtoupper($bank_ifsc_code) ?><?= ($sq_bank_count>0 || $sq_bank_branch['swift_code'] != '') ? '/' . strtoupper($sq_bank_branch['swift_code']) :  '/' .strtoupper($bank_swift_code) ?> 

                </p>

            </div>

            <div class="col-md-4">

                <p class="border_lt no-marg"><span class="font_5">BANK ACCOUNT NAME :

                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_name'] != '') ? $sq_bank_branch['account_name'] : $bank_account_name ?>

                </p>

            </div>

        </div>

    </div>

	<?php
	if (check_qr()) { ?>
		<div class="col-md-12 text-center" style="margin-top:20px; margin-bottom:20px;">
			<?= get_qr('Landscape Standard') ?>
			<br>
			<h4 class="no-marg">Scan & Pay </h4>
		</div>
	<?php } ?>
</section>
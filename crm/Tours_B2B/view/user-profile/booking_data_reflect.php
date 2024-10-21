<?php
include_once("../../../model/model.php");
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$customer_id = $_POST['customer_id'];
$agent_flag = $_SESSION['agent_flag'];
$user_id = $_SESSION['user_id'];
$array_s = array();
$temp_arr = array();
$footer_data = array();
$financial_year_id = $_SESSION['financial_year_id'];
global $currency;
$sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
$to_currency_rate = $sq_to['currency_rate'];

if($agent_flag == '1'){
	$query = "select * from b2b_booking_master where customer_id='$customer_id' ";
}else{
	$query = "select * from b2b_booking_master where agent_flag='$agent_flag' and user_id='$user_id'";
}

if($from_date!="" && $to_date !=""){
    $from_date = date('Y-m-d', strtotime($from_date));
	$to_date1 = date('Y-m-d', strtotime($to_date));
	$query .=" and (DATE(created_at)>='$from_date' and DATE(created_at)<='$to_date1') ";
}
$query .= " order by booking_id desc";	
$count = 0;
$net_total = 0;
$balance_total = 0;
$bg = '';
$sq_customer = mysqlQuery($query);
while($row_customer = mysqli_fetch_assoc($sq_customer)){
	
	$hotel_total = 0;
	$transfer_total = 0;
	$activity_total = 0;
	$tours_total = 0;
	$ferry_total = 0;
	$gtours_total = 0;
	$servie_total = 0;
	$yr = explode("-", get_datetime_db($row_customer['created_at']));
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_customer[customer_id]'"));
	$cart_checkout_data = ($row_customer['cart_checkout_data'] != '' && $row_customer['cart_checkout_data'] != 'null') ? json_decode($row_customer['cart_checkout_data']) : [];
	
	for($i=0;$i<sizeof($cart_checkout_data);$i++){
		if($cart_checkout_data[$i]->service->name == 'Hotel'){
			$hotel_flag = 1;
			$tax_arr = explode(',',$cart_checkout_data[$i]->service->hotel_arr->tax);
			for($j=0;$j<sizeof($cart_checkout_data[$i]->service->item_arr);$j++){
				$room_types = explode('-',$cart_checkout_data[$i]->service->item_arr[$j]);
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
				$total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
			
				$hotel_total += $total_amount;
			}
		}
		if($cart_checkout_data[$i]->service->name == 'Transfer'){

			$services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
			for($j=0;$j<count(array($services));$j++){
				$tax_amount = 0;
				$tax_arr = explode(',',$services->service_arr[$j]->taxation);
				$transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
				$room_cost = $transfer_cost[0];
				$h_currency_id = $transfer_cost[1];
				
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
				$total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
			
				$transfer_total += $total_amount;
			}
		}
		if($cart_checkout_data[$i]->service->name == 'Activity'){
			$activity_flag = 1;
			$services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
			for($j=0;$j<count(array($services));$j++){
			
				$tax_amount = 0;
				$tax_arr = explode(',',$services->service_arr[$j]->taxation);
				$transfer_cost = explode('-',$services->service_arr[$j]->transfer_type);
				$room_cost = $transfer_cost[1];
				$h_currency_id = $transfer_cost[2];
				
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
				$total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
			
				$activity_total += $total_amount;
			}
		}
		if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
			$services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
			for($j=0;$j<count(array($services));$j++){
			
				$tax_amount = 0;
			    $tax_arr = explode(',',$services->service_arr[$j]->taxation);
				$package_item = explode('-',$services->service_arr[$j]->package_type);
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
				$total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
			
				$tours_total += $total_amount;
			}
		}
		if($cart_checkout_data[$i]->service->name == 'Ferry'){
			$services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
			for($j=0;$j<count(array($services));$j++){
			
				$tax_amount = 0;
			    $tax_arr = explode(',',$services->service_arr[$j]->taxation);
				$package_item = explode('-',$services->service_arr[$j]->total_cost);
				$room_cost = $package_item[0];
				$h_currency_id = $package_item[1];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						}else{
							$tax_amount = $tax_amount + ($room_cost + $tax_arr2[1]);
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;

				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
			
				$ferry_total += $total_amount;
			}
		}
		if($cart_checkout_data[$i]->service->name == 'Group Tours'){
			
			$services = isset($cart_checkout_data[$i]->service) ? $cart_checkout_data[$i]->service : [];
			for($j=0;$j<count(array($services));$j++){
				
			    $tax_arr = explode(',',$cart_checkout_data[$i]->service->service_arr[$j]->taxation);
				$room_cost = $cart_checkout_data[$i]->service->service_arr[$j]->total_cost;
				$h_currency_id = $cart_checkout_data[$i]->service->service_arr[$j]->currency_id;
				$tax_amount = 0;
                $tax_arr1 = explode('+',$tax_arr[0]);
                for($t=0;$t<sizeof($tax_arr);$t++){
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
				$total_amount = ($from_currency_rate / $to_currency_rate * $total_amount);
			
				$gtours_total += $total_amount;
			}
		}
	}

	$servie_total = $servie_total + $hotel_total + $transfer_total + $activity_total + $tours_total + $ferry_total + $gtours_total;

    if($row_customer['coupon_code'] != ''){
		$sq_hotel_count = mysqli_num_rows(mysqlQuery("select offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_customer[coupon_code]'"));
		$sq_exc_count = mysqli_num_rows(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_customer[coupon_code]'"));
		if($sq_hotel_count > 0){
			$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer as offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_customer[coupon_code]'"));
		}else if($sq_exc_count > 0){
			$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_customer[coupon_code]'"));
		}else{
			$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from custom_package_offers where coupon_code='$row_customer[coupon_code]'"));
    	}
        if($sq_coupon['offer']=="Flat"){
            $servie_total = $servie_total - $sq_coupon['offer_amount'];
        }else{
            $servie_total = $servie_total - ($servie_total*$sq_coupon['offer_amount']/100);
        }
    }
	$net_total += $servie_total;
    $sq_payment_info = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from b2b_payment_master where booking_id='$row_customer[booking_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
    $payment_amount = $sq_payment_info['sum'];
    $paid_amount +=$sq_payment_info['sum'];
    $invoice_no = get_b2b_booking_id($row_customer['booking_id'],$yr[0]);	
	$service_url = BASE_URL."model/app_settings/print_html/voucher_html/b2b_voucher.php?booking_id=$row_customer[booking_id]";

	$cancel_amount = $row_customer['cancel_amount'];
	if($row_customer['status'] == 'Cancel'){
		$bg='table-danger';
		if($payment_amount > 0){
			if($cancel_amount >0){
				if($payment_amount > $cancel_amount){
					$balance_amount = 0;
				}else{
					$balance_amount = $cancel_amount - $payment_amount;
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
		$bg='';
		$balance_amount = $servie_total - $payment_amount;
	}
	$balance_total += $balance_amount;
	if($row_customer['agent_flag'] == '0'){
		$sq_agent = mysqlI_fetch_assoc(mysqlQuery("select full_name from b2b_users where id='$row_customer[user_id]'"));
		$emp_name = $sq_agent['full_name'];
	}else{
		$sq_agent = mysqlI_fetch_assoc(mysqlQuery("select company_name from  b2b_registration where register_id='$row_customer[user_id]'"));
		$emp_name = $sq_agent['company_name'];
	}
	$voucher_button = ($bal_amount == 0) ? '&nbsp;&nbsp;<button data-toggle="tooltip" style="margin-left:5px" onclick="loadOtherPage(\''. $service_url .'\')" class="btn btn-info btn-sm" title="Generate Service Voucher"><i class="fa fa-print"></i></button>' : '';
	if($row_customer['status'] == 'Cancel'){
		$voucher_button = '';
	}
    $temp_arr = array( "data" => array (
        (int)(++$count),
		$invoice_no,
		get_date_user($row_customer['created_at']),
		number_format($servie_total,2),
		number_format($payment_amount,2),
		number_format($balance_amount,2),
		$emp_name,
        '<button class="btn btn-info btn-sm" onclick="booking_view_modal('.$row_customer['booking_id'].')" title="View Details" id="view-'.$row_customer['booking_id'].'"><i class="fa fa-eye"></i></button>'.$voucher_button), "bg" => $bg
    );
    array_push($array_s,$temp_arr); 
}
$footer_data = array("footer_data" => array(
	'total_footers' => 6,
	'foot0' => '',
	'col0' => 2,
	'namecol0' => "",
	'foot1' => '',
	'col1' => 1,
	'namecol1' => "Total",
	'foot2' => number_format($net_total,2),
	'col2' => 1,
	'namecol2' => "",
	'foot3' => number_format($paid_amount,2),
	'col3' => 1,
	'namecol3' => "",
	'foot4' => number_format($balance_total,2),
	'col4' => 1,
	'namecol4' => "",
	'foot5' => '',
	'col5' => 2,
	'namecol5' => ""
	)
);
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>
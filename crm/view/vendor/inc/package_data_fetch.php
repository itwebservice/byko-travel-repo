<?php
include "../../../model/model.php";

$estimate_type = $_POST['estimate_type'];
$estimate_type_id = $_POST['estimate_type_id'];
$final_arr = [];

if($estimate_type=="Package Tour"){
	
	$sq_booking = mysqlQuery("select * from package_hotel_accomodation_master where booking_id='$estimate_type_id' ");
	while($row_booking = mysqli_fetch_assoc($sq_booking)){
		
		$sq_package = mysqli_fetch_assoc(mysqlQuery("select quotation_id from package_tour_booking_master where booking_id='$row_booking[booking_id]'"));
		$sq_quot = mysqli_fetch_assoc(mysqlQuery("select children_without_bed,children_with_bed from package_tour_quotation_master where quotation_id='$sq_package[quotation_id]'"));
		$child_with_bed = isset($sq_quot['children_without_bed']) ? $sq_quot['children_without_bed'] : 0;
		$child_without_bed = isset($sq_quot['children_with_bed']) ? $sq_quot['children_with_bed'] : 0;
		
		$hotel_cost = 0;
		$room_cost = 0;
		$cwb_cost = 0;
		$cwob_cost = 0;
		$extra_bed_cost = 0;
		$hotel_cost_arr1 = array();
		$hotel_id = $row_booking['hotel_id'];
		$total_rooms = $row_booking['rooms'];
		$room_category = $row_booking['catagory'];
		$extra_beds = $row_booking['room_type'];
		$from_date = $row_booking['from_date'];
		$to_date = $row_booking['to_date'];
		
		$checkDate_array = array(); //Array of Check-in and Check-out date
		$check_in = strtotime($from_date);
		$check_out = strtotime($to_date);
		for ($i_date=$check_in; $i_date<=$check_out; $i_date+=86400){
			array_push($checkDate_array,date("Y-m-d", $i_date));  
		}
		$hotel_cost_arr1 = get_hotel_cost($hotel_id,json_encode($checkDate_array),$room_category);
		
		$hotel_cost_arr1 = json_decode($hotel_cost_arr1);
	
		if(sizeof($hotel_cost_arr1) >0){
			for($j=0;$j<sizeof($hotel_cost_arr1);$j++){
				$room_cost += $hotel_cost_arr1[$j]->room_cost;
				$cwb_cost += $hotel_cost_arr1[$j]->child_with_bed;
				$cwob_cost += $hotel_cost_arr1[$j]->child_without_bed;
				$extra_bed_cost += $hotel_cost_arr1[$j]->extra_bed;
			}
			$total_rooms = ($total_rooms == '') ? 0 : $total_rooms;
			$child_with_bed = ($child_with_bed == '') ? 0 : $child_with_bed;
			$child_without_bed = ($child_without_bed == '') ? 0 : $child_without_bed;
			$extra_beds = ($extra_beds == '') ? 0 : $extra_beds;
			if($total_rooms!=0){

				$hotel_cost = ($total_rooms * $room_cost) + ($child_without_bed * $cwob_cost) + ($child_with_bed * $cwb_cost) + ($extra_beds * $extra_bed_cost);
			}
		}
		array_push($final_arr,array(
			'hotel_id'=>$hotel_id,
			'hotel_cost'=>$hotel_cost));
	}
}
if($estimate_type=="Hotel"){
	
	$sq_booking = mysqlQuery("select * from hotel_booking_entries where booking_id='$estimate_type_id' ");
	while($row_booking = mysqli_fetch_assoc($sq_booking)){
		
		$sq_package = mysqli_fetch_assoc(mysqlQuery("select quotation_id,childrens,child_with_bed from hotel_booking_master where booking_id='$row_booking[booking_id]'"));
		$child_with_bed = isset($sq_package['childrens']) ? $sq_package['childrens'] : 0;
		$child_without_bed = isset($sq_package['child_with_bed']) ? $sq_package['child_with_bed'] : 0;
		
		$hotel_cost = 0;
		$room_cost = 0;
		$cwb_cost = 0;
		$cwob_cost = 0;
		$extra_bed_cost = 0;
		$hotel_cost_arr1 = array();
		$hotel_id = $row_booking['hotel_id'];
		$total_rooms = $row_booking['rooms'];
		$room_category = $row_booking['category'];
		$extra_beds = $row_booking['extra_beds'];
		$from_date = $row_booking['check_in'];
		$to_date = $row_booking['check_out'];
		
		$checkDate_array = array(); //Array of Check-in and Check-out date
		$check_in = strtotime($from_date);
		$check_out = strtotime($to_date);
		for ($i_date=$check_in; $i_date<=$check_out; $i_date+=86400){
			array_push($checkDate_array,date("Y-m-d", $i_date));  
		}
		$hotel_cost_arr1 = get_hotel_cost($hotel_id,json_encode($checkDate_array),$room_category);
		
		$hotel_cost_arr1 = json_decode($hotel_cost_arr1);
	
		if(sizeof($hotel_cost_arr1) >0){
			for($j=0;$j<sizeof($hotel_cost_arr1);$j++){
				$room_cost += $hotel_cost_arr1[$j]->room_cost;
				$cwb_cost += $hotel_cost_arr1[$j]->child_with_bed;
				$cwob_cost += $hotel_cost_arr1[$j]->child_without_bed;
				$extra_bed_cost += $hotel_cost_arr1[$j]->extra_bed;
			}
			$total_rooms = ($total_rooms == '') ? 0 : $total_rooms;
			$child_with_bed = ($child_with_bed == '') ? 0 : $child_with_bed;
			$child_without_bed = ($child_without_bed == '') ? 0 : $child_without_bed;
			$extra_beds = ($extra_beds == '') ? 0 : $extra_beds;
			if($total_rooms!=0){

				$hotel_cost = ($total_rooms * $room_cost) + ($child_without_bed * $cwob_cost) + ($child_with_bed * $cwb_cost) + ($extra_beds * $extra_bed_cost);
			}
		}
		array_push($final_arr,array(
			'hotel_id'=>$hotel_id,
			'hotel_cost'=>$hotel_cost));
	}
}
echo json_encode($final_arr);

function get_hotel_cost($hotel_id,$checkDate_array,$room_category){
	
	$checkDate_array = json_decode($checkDate_array);

	//Get selected currency rate
	global $currency;
	$sq_to = mysqli_fetch_assoc(mysqlQuery("select currency_rate from roe_master where currency_id='$currency'"));
	$to_currency_rate = $sq_to['currency_rate'] ?: 1;  //1 is need to stop Uncaught DivisionByZeroError
	$hotel_cost_arr = array();
	$row_tariff_count = mysqli_num_rows(mysqlQuery("select * from hotel_vendor_price_master where 1 and hotel_id='$hotel_id' order by pricing_id desc"));
	if($row_tariff_count > 0){

		$row_tariff_master1 = mysqlQuery("select * from hotel_vendor_price_master where 1 and hotel_id='$hotel_id' order by pricing_id desc");
		while($row_tariff_master = mysqli_fetch_assoc($row_tariff_master1)){
			
			$blackdated_count = 0;
			$weekenddated_count = 0;
			$contracted_count = 0;
			$currency_id = $row_tariff_master['currency_id'];
			$sq_from = mysqli_fetch_assoc(mysqlQuery("select currency_rate from roe_master where currency_id='$currency_id'"));
			$from_currency_rate = $sq_from['currency_rate'];

			for($i_date=0; $i_date<sizeof($checkDate_array)-1; $i_date++){

				$blackdated_count = mysqli_num_rows(mysqlQuery("select * from hotel_blackdated_tarrif where pricing_id='$row_tariff_master[pricing_id]' and room_category = '$room_category' and (from_date <='$checkDate_array[$i_date]' and to_date>='$checkDate_array[$i_date]')"));
				$day = date("l", strtotime($checkDate_array[$i_date]));
				$weekenddated_count = mysqli_num_rows(mysqlQuery("select * from hotel_weekend_tarrif where pricing_id='$row_tariff_master[pricing_id]' and room_category = '$room_category' and day='$day'"));
				$qq = "select * from hotel_contracted_tarrif where pricing_id='$row_tariff_master[pricing_id]' and room_category = '$room_category' and (from_date <='$checkDate_array[$i_date]' and to_date>='$checkDate_array[$i_date]')";
				$contracted_count = mysqli_num_rows(mysqlQuery($qq));
				if($blackdated_count>0){

					$sq_tariff = mysqli_fetch_assoc(mysqlQuery("select * from hotel_blackdated_tarrif where pricing_id='$row_tariff_master[pricing_id]' and room_category = '$room_category' and (from_date <='$checkDate_array[$i_date]' and to_date>='$checkDate_array[$i_date]') "));
					$arr = array(
						'room_cost' =>  ($from_currency_rate / $to_currency_rate) * $sq_tariff['double_bed'],
						'child_with_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['child_with_bed'],
						'child_without_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['child_without_bed'],
						'extra_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['extra_bed'],
					);
					array_push($hotel_cost_arr, $arr);
				}
				else if($weekenddated_count>0){

					$sq_tariff = mysqli_fetch_assoc(mysqlQuery("select * from hotel_weekend_tarrif where pricing_id='$row_tariff_master[pricing_id]' and room_category = '$room_category' and day='$day' "));
					$arr = array(
						'room_cost' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['double_bed'],
						'child_with_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['child_with_bed'],
						'child_without_bed' =>($from_currency_rate / $to_currency_rate) *  $sq_tariff['child_without_bed'],
						'extra_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['extra_bed'],
					);
					array_push($hotel_cost_arr, $arr);
				}
				else if($contracted_count>0){

					$sq_tariff = mysqli_fetch_assoc(mysqlQuery("select * from hotel_contracted_tarrif where pricing_id='$row_tariff_master[pricing_id]' and room_category = '$room_category' and (from_date <='$checkDate_array[$i_date]' and to_date>='$checkDate_array[$i_date]')"));
					$arr = array(
						'room_cost' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['double_bed'],
						'child_with_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['child_with_bed'],
						'child_without_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['child_without_bed'],
						'extra_bed' => ($from_currency_rate / $to_currency_rate) * $sq_tariff['extra_bed'],
					);
					array_push($hotel_cost_arr, $arr);
				}
				else{
					break;
				}
			}
		}
	}
	return json_encode($hotel_cost_arr);
}
?>        
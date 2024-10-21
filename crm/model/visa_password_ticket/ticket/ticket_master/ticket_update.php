<?php

$flag = true;

class ticket_update{



public function ticket_master_update()
{
	$row_spec = "sales";
	$ticket_id = $_POST['ticket_id'];
	$customer_id = $_POST['customer_id'];	
	$guest_name = isset($_POST['guest_name']) ? $_POST['guest_name'] : '';
	$tour_type = isset($_POST['tour_type']) ? $_POST['tour_type'] : '';
	$arrival_terminal_arr = isset($_POST['arrival_terminal_arr']) ? $_POST['arrival_terminal_arr'] : [];
	$departure_terminal_arr = isset($_POST['departure_terminal_arr']) ? $_POST['departure_terminal_arr'] : [];
	$canc_policy = isset($_POST['canc_policy']) ? mysqlREString($_POST['canc_policy']) : '';

	$adults = $_POST['adults'];
	$childrens = $_POST['childrens'];
	$infant = $_POST['infant'];
	$adult_fair = $_POST['adult_fair'];
	$children_fair = $_POST['children_fair'];
	$infant_fair = $_POST['infant_fair'];
	$basic_cost = $_POST['basic_cost'];
	$discount = $_POST['discount'];
	$yq_tax = $_POST['yq_tax'];
	$other_taxes = $_POST['other_taxes'];
	$markup = $_POST['markup'];
	$service_tax_markup = $_POST['service_tax_markup'];

	$service_charge = $_POST['service_charge'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$tds = $_POST['tds'];
	$due_date = $_POST['due_date'];
	$booking_date = $_POST['booking_date1'];
	$old_total = $_POST['old_total'];

	$roundoff = $_POST['roundoff'];
	$ticket_reissue = $_POST['ticket_reissue'];

	$bsmValues = json_decode(json_encode($_POST['bsmValues']));
	foreach($bsmValues[0] as $key => $value){
		switch($key){
			case 'basic' : $basic_cost = ($value != "") ? $value : $basic_cost;break;
			case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
			case 'markup' : $markup = ($value != "") ? $value : $markup;break;
			case 'discount' : $discount = ($value != "") ? $value : $discount;break;
		}
    }

	$ticket_total_cost = $_POST['ticket_total_cost'];

	$first_name_arr = $_POST['first_name_arr'];

	$middle_name_arr = $_POST['middle_name_arr'];

	$last_name_arr = isset($_POST['last_name_arr']) ? $_POST['last_name_arr'] : [];

	$birth_date_arr = isset($_POST['birth_date_arr']) ? $_POST['birth_date_arr'] : [];

	$adolescence_arr = isset($_POST['adolescence_arr']) ? $_POST['adolescence_arr'] : [];

	$ticket_no_arr = isset($_POST['ticket_no_arr']) ? $_POST['ticket_no_arr'] : [];
	$seat_no_arr = isset($_POST['seat_no_arr']) ? $_POST['seat_no_arr'] : [];
	$gds_pnr_arr = isset($_POST['gds_pnr_arr']) ? $_POST['gds_pnr_arr'] : [];
	$meal_plan_arr = isset($_POST['meal_plan_arr']) ? $_POST['meal_plan_arr'] : [];
	$baggage_info_arr = isset($_POST['baggage_info_arr']) ? $_POST['baggage_info_arr'] : [];
	$main_ticket_arr = isset($_POST['main_ticket_arr']) ? $_POST['main_ticket_arr'] : [];

	$entry_id_arr = isset($_POST['entry_id_arr']) ? $_POST['entry_id_arr'] : [];
	$trip_details_arr1 = isset($_POST['trip_details_arr1']) ? $_POST['trip_details_arr1'] : [];
	$checked_arr = isset($_POST['checked_arr']) ? $_POST['checked_arr'] : [];
	$reflections = json_encode($_POST['reflections']);

	$due_date=date('Y-m-d',strtotime($due_date));
	$booking_date=date('Y-m-d',strtotime($booking_date));

	begin_t();

	//**Old information
	$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$ticket_id'"));

	//**Update ticket
	$bsmValues = json_encode($bsmValues);
	$sq_ticket = mysqlQuery("UPDATE ticket_master set customer_id='$customer_id',ticket_reissue='$ticket_reissue', tour_type='$tour_type', adults='$adults', childrens='$childrens', infant='$infant', adult_fair='$adult_fair', children_fair='$children_fair', infant_fair='$infant_fair', basic_cost='$basic_cost', markup='$markup', basic_cost_discount='$discount',other_taxes = '$other_taxes', yq_tax='$yq_tax', service_tax_markup='$service_tax_markup',service_charge='$service_charge', service_tax_subtotal='$service_tax_subtotal', tds='$tds', due_date='$due_date', ticket_total_cost='$ticket_total_cost',created_at='$booking_date',reflections='$reflections',roundoff='$roundoff',bsm_values='$bsmValues',canc_policy='$canc_policy',guest_name='$guest_name' where ticket_id='$ticket_id' ");

	if(!$sq_ticket){
		$GLOBALS['flag'] = false;
		echo "error--Sorry, Ticket not updated!";
	}



	//**Update Member

	for($i=0; $i<sizeof($first_name_arr); $i++){

		$trip_details_arr3 = json_decode($trip_details_arr1[$i]);
		$ttour_type = $trip_details_arr3[0]->type_of_tour;
		$entry_id_arr[$i]   = isset($entry_id_arr[$i]) ? $entry_id_arr[$i] : '';
		$birth_date_arr[$i]   = isset($birth_date_arr[$i]) ? get_date_db($birth_date_arr[$i]) : '';
		$first_name_arr[$i]   = mysqlREString($first_name_arr[$i]);
		$middle_name_arr[$i]  = mysqlREString($middle_name_arr[$i]); 
		$last_name_arr[$i]    = mysqlREString($last_name_arr[$i]);
		$baggage_info_arr[$i] = mysqlREString($baggage_info_arr[$i]);
		if($checked_arr[$i] != 'true'){
			$sq_entry = mysqlQuery("delete from ticket_master_entries where entry_id='$entry_id_arr[$i]'");
			$sq_entry = mysqlQuery("delete from ticket_trip_entries where passenger_id='$entry_id_arr[$i]'");
			if(!$sq_entry){
				$GLOBALS['flag'] = false;
				echo "error--Error in delete member information!";
			}
		}else{

			if($entry_id_arr[$i]==""){

				$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from ticket_master_entries"));
				$pass_id = $sq_max['max'] + 1;
				$sq_entry = mysqlQuery("insert into ticket_master_entries(entry_id, ticket_id, first_name, middle_name, last_name, birth_date, adolescence, ticket_no, gds_pnr,baggage_info,seat_no,main_ticket,meal_plan,type_of_tour) values('$pass_id', '$ticket_id', '$first_name_arr[$i]','$middle_name_arr[$i]','$last_name_arr[$i]', '$birth_date_arr[$i]', '$adolescence_arr[$i]', '$ticket_no_arr[$i]', '$gds_pnr_arr[$i]','$baggage_info_arr[$i]','$seat_no_arr[$i]','$main_ticket_arr[$i]','$meal_plan_arr[$i]','$ttour_type')");
	
				if(!$sq_entry){
					$GLOBALS['flag'] = false;
					echo "error--Error in insert member information!";
				}
			}
			else{
				$sq_entry = mysqlQuery("update ticket_master_entries set first_name='$first_name_arr[$i]', middle_name='$middle_name_arr[$i]', last_name='$last_name_arr[$i]', birth_date='$birth_date_arr[$i]', adolescence='$adolescence_arr[$i]', ticket_no='$ticket_no_arr[$i]', gds_pnr='$gds_pnr_arr[$i]',baggage_info='$baggage_info_arr[$i]',seat_no='$seat_no_arr[$i]',meal_plan='$meal_plan_arr[$i]',main_ticket='$main_ticket_arr[$i]',type_of_tour='$ttour_type' where entry_id='$entry_id_arr[$i]' ");
	
				if(!$sq_entry){
					$GLOBALS['flag'] = false;
					echo "error--Error in update member information!";
				}
			}
			$departure_datetime_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->departure_datetime_arr : [];
			$arrival_datetime_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->arrival_datetime_arr : [];
			$from_city_id_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->from_city_id_arr : [];
			$to_city_id_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->to_city_id_arr : [];
			$arrival_terminal_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->arrival_terminal_arr : [];
			$departure_terminal_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->departure_terminal_arr : [];
			$airlines_name_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->airlines_name_arr : [];
			$class_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->class_arr : [];
			$flight_no_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->flight_no_arr : [];
			$airlin_pnr_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->airlin_pnr_arr : [];
			$departure_city_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->departure_city_arr : [];
			$arrival_city_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->arrival_city_arr : [];
			$luggage_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->luggage_arr : [];
			$special_note_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->special_note_arr : [];
			$sub_category_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->sub_category_arr : [];
			$no_of_pieces_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->no_of_pieces_arr : [];
			$aircraft_type_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->aircraft_type_arr : [];
			$operating_carrier_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->operating_carrier_arr : [];
			$frequent_flyer_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->frequent_flyer_arr : [];
			$ticket_status_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->ticket_status_arr : [];
			$basic_fare_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->basic_fare_arr : [];
			$flight_duration_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->flight_duration_arr : [];
			$layover_time_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->layover_time_arr : [];
			$refund_type_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->refund_type_arr : [];
			$trip_entry_id_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->trip_entry_id_arr : [];
			$trip_data_check_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->trip_data_check_arr : [];
			$cancel_status_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->cancel_status_arr : [];
			$sector = '';
			for($j=0; $j<sizeof($departure_datetime_arr); $j++){
				
				$departure_datetime_arr[$j] = isset($departure_datetime_arr[$j]) ? $departure_datetime_arr[$j] : '';
				$arrival_datetime_arr[$j] = isset($arrival_datetime_arr[$j]) ? $arrival_datetime_arr[$j] : '';
				$from_city_id_arr[$j] = isset($from_city_id_arr[$j]) ? $from_city_id_arr[$j] : '';
				$to_city_id_arr[$j] = isset($to_city_id_arr[$j]) ? $to_city_id_arr[$j] : '';
				$arrival_terminal_arr[$j] = isset($arrival_terminal_arr[$j]) ? $arrival_terminal_arr[$j] : '';
				$departure_terminal_arr[$j] = isset($departure_terminal_arr[$j]) ? $departure_terminal_arr[$j] : '';
				$airlines_name_arr[$j] = isset($airlines_name_arr[$j]) ? $airlines_name_arr[$j] : '';
				$class_arr[$j] = isset($class_arr[$j]) ? $class_arr[$j] : '';
				$flight_no_arr[$j] = isset($flight_no_arr[$j]) ? $flight_no_arr[$j] : '';
				$airlin_pnr_arr[$j] = isset($airlin_pnr_arr[$j]) ? $airlin_pnr_arr[$j] : '';
				$departure_city_arr[$j] = isset($departure_city_arr[$j]) ? $departure_city_arr[$j] : '';
				$arrival_city_arr[$j] = isset($arrival_city_arr[$j]) ? $arrival_city_arr[$j] : '';
				$luggage_arr[$j] = isset($luggage_arr[$j]) ? $luggage_arr[$j] : '';
				$special_note_arr[$j] = isset($special_note_arr[$j]) ? $special_note_arr[$j] : '';
				$sub_category_arr[$j] = isset($sub_category_arr[$j]) ? $sub_category_arr[$j] : '';
				$no_of_pieces_arr[$j] = isset($no_of_pieces_arr[$j]) ? $no_of_pieces_arr[$j] : '';
				$aircraft_type_arr[$j] = isset($aircraft_type_arr[$j]) ? $aircraft_type_arr[$j] : '';
				$operating_carrier_arr[$j] = isset($operating_carrier_arr[$j]) ? $operating_carrier_arr[$j] : '';
				$frequent_flyer_arr[$j] = isset($frequent_flyer_arr[$j]) ? $frequent_flyer_arr[$j] : '';
				$ticket_status_arr[$j] = isset($ticket_status_arr[$j]) ? $ticket_status_arr[$j] : '';
				$basic_fare_arr[$j] = isset($basic_fare_arr[$j]) ? $basic_fare_arr[$j] : '';
				$flight_duration_arr[$j] = isset($flight_duration_arr[$j]) ? $flight_duration_arr[$j] : '';
				$layover_time_arr[$j] = isset($layover_time_arr[$j]) ? $layover_time_arr[$j] : '';
				$refund_type_arr[$j] = isset($refund_type_arr[$j]) ? $refund_type_arr[$j] : '';
				$trip_entry_id_arr[$j] = isset($trip_entry_id_arr[$j]) ? $trip_entry_id_arr[$j] : '';
				$trip_data_check_arr[$j] = isset($trip_data_check_arr[$j]) ? $trip_data_check_arr[$j] : '';
				$cancel_status_arr[$j] = isset($cancel_status_arr[$j]) ? $cancel_status_arr[$j] : '';

				if($trip_data_check_arr[$j]){
	
					$departure_datetime_arr[$j] = get_datetime_db($departure_datetime_arr[$j]);
					$arrival_datetime_arr[$j] = get_datetime_db($arrival_datetime_arr[$j]);	

					$filterAirline = explode('(',$airlines_name_arr[$j]);
					$tempAirlineCode = substr($filterAirline[1],0,strlen($filterAirline[1])-1);
					$airlineIdMain = mysqli_fetch_assoc(mysqlQuery('select * from airline_master where airline_code="'.$tempAirlineCode.'"'))['airline_id'];
					$airline_id = $airlineIdMain;
		
					$special_note1 = addslashes($special_note_arr[$j]);
					if($entry_id_arr[$i]=="" && $cancel_status_arr[$j] != "Cancel"){
					
						$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from ticket_trip_entries"));
						$entry_id = $sq_max['max'] + 1;
						$sq_entry = mysqlQuery("insert into ticket_trip_entries(entry_id, ticket_id,airline_id,passenger_id, departure_datetime, arrival_datetime, airlines_name, class, flight_no, airlin_pnr, departure_city, arrival_city,meal_plan,luggage, special_note, from_city, to_city, arrival_terminal, departure_terminal,sub_category,no_of_pieces,aircraft_type,operating_carrier,frequent_flyer,ticket_status,basic_fare,flight_duration,layover_time,refund_type) values('$entry_id', '$ticket_id','$airline_id','$pass_id','$departure_datetime_arr[$j]', '$arrival_datetime_arr[$j]', '$airlines_name_arr[$j]', '$class_arr[$j]', '$flight_no_arr[$j]', '$airlin_pnr_arr[$j]', '$departure_city_arr[$j]', '$arrival_city_arr[$j]', '', '$luggage_arr[$i]', '$special_note1','$from_city_id_arr[$j]','$to_city_id_arr[$j]', '$arrival_terminal_arr[$j]','$departure_terminal_arr[$j]','$sub_category_arr[$j]','$no_of_pieces_arr[$j]','$aircraft_type_arr[$j]','$operating_carrier_arr[$j]','$frequent_flyer_arr[$j]','$ticket_status_arr[$j]','$basic_fare_arr[$j]','$flight_duration_arr[$j]','$layover_time_arr[$j]','$refund_type_arr[$j]')");
						
						if(!$sq_entry){
							$GLOBALS['flag'] = false;
							echo "error--Error in trip information save!";
						}	
					}
					else{
						if($trip_entry_id_arr[$j] == '' && $cancel_status_arr[$j] != "Cancel"){
							
							$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from ticket_trip_entries"));
							$entry_id = $sq_max['max'] + 1;
							$sq_entry = mysqlQuery("insert into ticket_trip_entries(entry_id, ticket_id,airline_id,passenger_id, departure_datetime, arrival_datetime, airlines_name, class, flight_no, airlin_pnr, departure_city, arrival_city,meal_plan,luggage, special_note, from_city, to_city, arrival_terminal, departure_terminal,sub_category,no_of_pieces,aircraft_type,operating_carrier,frequent_flyer,ticket_status,basic_fare,flight_duration,layover_time,refund_type) values('$entry_id', '$ticket_id','$airline_id','$entry_id_arr[$i]','$departure_datetime_arr[$j]', '$arrival_datetime_arr[$j]', '$airlines_name_arr[$j]', '$class_arr[$j]', '$flight_no_arr[$j]', '$airlin_pnr_arr[$j]', '$departure_city_arr[$j]', '$arrival_city_arr[$j]', '', '$luggage_arr[$i]', '$special_note1','$from_city_id_arr[$j]','$to_city_id_arr[$j]', '$arrival_terminal_arr[$j]', '$departure_terminal_arr[$j]','$sub_category_arr[$j]','$no_of_pieces_arr[$j]','$aircraft_type_arr[$j]','$operating_carrier_arr[$j]','$frequent_flyer_arr[$j]','$ticket_status_arr[$j]','$basic_fare_arr[$j]','$flight_duration_arr[$j]','$layover_time_arr[$j]','$refund_type_arr[$j]')");
							
							if(!$sq_entry){
								$GLOBALS['flag'] = false;
								echo "error--Error in trip information save!";
							}
						}
						$sq_entry = mysqlQuery("update ticket_trip_entries set departure_datetime='$departure_datetime_arr[$j]', arrival_datetime='$arrival_datetime_arr[$j]',airline_id='$airline_id', airlines_name='$airlines_name_arr[$j]', class='$class_arr[$j]', flight_no='$flight_no_arr[$j]', airlin_pnr='$airlin_pnr_arr[$j]', departure_city='$departure_city_arr[$j]', arrival_city='$arrival_city_arr[$j]', luggage='$luggage_arr[$j]', special_note='$special_note1', from_city='$from_city_id_arr[$j]', to_city='$to_city_id_arr[$j]',arrival_terminal='$arrival_terminal_arr[$j]', departure_terminal='$departure_terminal_arr[$j]',sub_category='$sub_category_arr[$j]', no_of_pieces='$no_of_pieces_arr[$j]', aircraft_type='$aircraft_type_arr[$j]',operating_carrier='$operating_carrier_arr[$j]', frequent_flyer='$frequent_flyer_arr[$j]',ticket_status='$ticket_status_arr[$j]', basic_fare='$basic_fare_arr[$j]', flight_duration='$flight_duration_arr[$j]', layover_time='$layover_time_arr[$j]', refund_type='$refund_type_arr[$j]' where entry_id='$trip_entry_id_arr[$j]'");
						if(!$sq_entry){
							$GLOBALS['flag'] = false;
							echo "error--Error in trip information update!";
						}
					}
					
					$dep = explode('(',$departure_city_arr[$j]);
					$arr = explode('(',$arrival_city_arr[$j]);
					if($i == 0)
						$sector = str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
					if($i>0)
						$sector = $sector.','.str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
				}
				else{
					$sq_delete = mysqlQuery("delete from ticket_trip_entries where entry_id='$trip_entry_id_arr[$j]' ");
					if(!$sq_delete){
						$GLOBALS['flag'] = false;
						echo "error--Error in trip information delete!";
					}
				}
			}
		}

	
	}

	//***Trip information


	//Get Particular
	$pax = $adults + $childrens;
	$particular = $this->get_particular($customer_id,$pax,$sector,$ticket_no_arr[0],$gds_pnr_arr[0],$ticket_id);
	//Finance update

	$this->finance_update($sq_ticket_info, $row_spec,$particular);

	global $transaction_master;
	if(floatval($old_total) != floatval($ticket_total_cost)){

		$yr = explode("-", $booking_date);
		$year = $yr[0];
        $sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
        if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
            $cust_name = $sq_ct['company_name'];
        }else{
            $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
        }

		$trans_id = get_ticket_booking_id($ticket_id,$year).' : '.$cust_name;
		$transaction_master->updated_entries('Flight Sale',$ticket_id,$trans_id,$old_total,$ticket_total_cost);
	}


	if($GLOBALS['flag']){

		commit_t();

		echo "Flight Ticket Booking has been successfully updated.";

		exit;	

	}

	else{

		rollback_t();

		exit;

	}		

}

function get_particular($customer_id,$pax,$sector,$ticket_no,$pnr,$ticket_id){
	
	$row_ticket = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$ticket_id'"));
	$booking_date = $row_ticket['created_at'];
	$yr = explode("-", $booking_date);
	$year = $yr[0];
	$sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where ticket_id='$ticket_id' and status!='Cancel'"));
	
	$sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	$cust_name = ($sq_ct['type'] == 'Corporate' || $sq_ct['type'] == 'B2B') ? $sq_ct['company_name'] : $sq_ct['first_name'].' '.$sq_ct['last_name'];



	
	$passenger_name = $sq_pass['first_name'].' '.$sq_pass['last_name'];

    if ($cust_name == $passenger_name) {
        $cust_name_display = $cust_name;
    } else {
        $cust_name_display = $cust_name.' ('.$passenger_name.')';
    }

    return get_ticket_booking_id($ticket_id, $year). ' for '.$cust_name_display.' * '.$pax.' travelling for '.$sector.' against ticket no '.strtoupper($ticket_no).'/Airline PNR '.strtoupper($pnr);

	// return get_ticket_booking_id($ticket_id,$year). ' for '.$cust_name. '('.$sq_pass['first_name'].' '.$sq_pass['last_name'].') * '.$pax.' travelling for '.$sector.' against ticket no '.strtoupper($ticket_no).'/Airline PNR '.strtoupper($pnr);
}

public function finance_update($sq_ticket_info, $row_spec,$particular){

	$ticket_id = $_POST['ticket_id'];
	$customer_id = $_POST['customer_id'];
	$tour_type = $_POST['tour_type'];
	$basic_cost = $_POST['basic_cost'];
	$markup = $_POST['markup'];
	$discount = $_POST['discount'];
	$service_tax_markup = $_POST['service_tax_markup'];
	$yq_tax = $_POST['yq_tax'];
	$other_taxes = $_POST['other_taxes'];
	$service_charge = $_POST['service_charge'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$tds = $_POST['tds'];
	$due_date = $_POST['due_date'];
	$ticket_total_cost = $_POST['ticket_total_cost'];
	$booking_date = $_POST['booking_date1'];
	$bsmValues = json_decode(json_encode($_POST['bsmValues']));
	foreach($bsmValues[0] as $key => $value){
		switch($key){
			case 'basic' : $basic_cost = ($value != "") ? $value : $basic_cost;break;
			case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
			case 'markup' : $markup = ($value != "") ? $value : $markup;break;
			case 'discount' : $discount = ($value != "") ? $value : $discount;break;
		}
	}
	$roundoff = $_POST['roundoff'];	

	$reflections = json_decode(json_encode($_POST['reflections']));
	$booking_date = date('Y-m-d', strtotime($booking_date));
	$year1 = explode("-", $booking_date);
	$yr1 =$year1[0];
		
	$total_sale = floatval($basic_cost) + floatval($yq_tax) + floatval($other_taxes);
	//get total payment against ticket id
    $sq_ticket = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as payment_amount from ticket_payment_master where ticket_id='$ticket_id'"));
	$balance_amount = $ticket_total_cost - $sq_ticket['payment_amount'];

    //Getting customer Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
	$cust_gl = $sq_cust['ledger_id'];

	global $transaction_master;
	$sale_gl = ($tour_type == 'Domestic') ? 50 : 174;

    ////////////Sales/////////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $total_sale;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $old_gl_id = $gl_id = $sale_gl;
    $payment_side = "Credit";
    $clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	
	////////////service charge/////////////
	$module_name = "Air Ticket Booking";
	$module_entry_id = $ticket_id;
	$transaction_id = "";
	$payment_amount = $service_charge;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
	$old_gl_id = $gl_id = ($reflections[0]->flight_sc != '') ? $reflections[0]->flight_sc : 187;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

	  /////////Service Charge Tax Amount////////
	// Eg. CGST:(9%):24.77, SGST:(9%):24.77
	$service_tax_subtotal = explode(',',$service_tax_subtotal);
	$tax_ledgers = explode(',',$reflections[0]->flight_taxes);
	for($i=0;$i<sizeof($service_tax_subtotal);$i++){

		$service_tax = explode(':',$service_tax_subtotal[$i]);
		$tax_amount = $service_tax[2];
		$ledger = $tax_ledgers[$i];

		$module_name = "Air Ticket Booking";
		$module_entry_id = $ticket_id;
		$transaction_id = "";
		$payment_amount = $tax_amount;
		$payment_date = $booking_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
		$old_gl_id = $gl_id = $ledger;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	}

    ////////////markup/////////////
	$module_name = "Air Ticket Booking";
	$module_entry_id = $ticket_id;
	$transaction_id = "";
	$payment_amount = $markup;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
	$old_gl_id = $gl_id = ($reflections[0]->flight_markup != '') ? $reflections[0]->flight_markup : 199;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	
	/////////Markup Tax Amount////////
	// Eg. CGST:(9%):24.77, SGST:(9%):24.77
	$service_tax_markup = explode(',',$service_tax_markup);
	$tax_ledgers = explode(',',$reflections[0]->flight_markup_taxes);
	for($i=0;$i<sizeof($service_tax_markup);$i++){
  
	  $service_tax = explode(':',$service_tax_markup[$i]);
	  $tax_amount = $service_tax[2];
	  $ledger = $tax_ledgers[$i];
  
	  $module_name = "Air Ticket Booking";
	  $module_entry_id = $ticket_id;
	  $transaction_id = "";
	  $payment_amount = $tax_amount;
	  $payment_date = $booking_date;
	  $payment_particular = $particular;
	  $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
	  $old_gl_id = $gl_id = $ledger;
	  $payment_side = "Credit";
	  $clearance_status = "";
	  $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '1',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	}

	/////////roundoff/////////
	$module_name = "Air Ticket Booking";
	$module_entry_id = $ticket_id;
	$transaction_id = "";
	$payment_amount = $roundoff;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Air Ticket Sales');
	$old_gl_id = $gl_id = 230;	
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    /////////TDS////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $old_gl_id = $gl_id = ($reflections[0]->flight_tds != '') ? $reflections[0]->flight_tds : 127;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');


    /////////Discount////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $discount;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $old_gl_id = $gl_id = 36;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');


	////////Customer Amount//////
  $module_name = "Air Ticket Booking";
  $module_entry_id = $ticket_id;
  $transaction_id = "";
  $payment_amount = $ticket_total_cost;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
  $old_gl_id = $gl_id = $cust_gl;
  $payment_side = "Debit";
  $clearance_status = "";
  $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

}
}

?>
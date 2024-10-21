<?php
$flag = true;
class ticket_save{

public function pnr_check(){

	$airlin_pnr_arr = $_POST['airlin_pnr_arr'];
	$type = $_POST['type'];
	$entry_id = $_POST['entry_id'];

	for($i=0; $i<sizeof($airlin_pnr_arr); $i++){
		if($type=='save'){
			$sq_count = mysqli_num_rows(mysqlQuery("select * from ticket_trip_entries where airlin_pnr='$airlin_pnr_arr[$i]' and airlin_pnr!='' and status!='Cancel'"));
		}else{
			$sq_count = mysqli_num_rows(mysqlQuery("select * from ticket_trip_entries where airlin_pnr='$airlin_pnr_arr[$i]' and airlin_pnr!=''  and entry_id!='$entry_id[$i]' and status!='Cancel'"));
		}
	}
}
public function ticket_master_save(){

	$row_spec = "sales";
	$customer_id = $_POST['customer_id'];
	$emp_id = $_POST['emp_id'];
	$tour_type = $_POST['tour_type'];
	$guest_name = $_POST['guest_name'];
	$branch_admin_id = $_SESSION['branch_admin_id'];
	$financial_year_id = $_SESSION['financial_year_id'];

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
	$booking_date = $_POST['booking_date'];
	$ticket_total_cost = $_POST['ticket_total_cost'];
	$ticket_reissue = $_POST['ticket_reissue'];

	$payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : '';
	$payment_amount = isset($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;
	$payment_mode = isset($_POST['payment_mode']) ? $_POST['payment_mode'] : '';
	$bank_name = isset($_POST['bank_name']) ? $_POST['bank_name'] : '';
	$transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
	$bank_id = isset($_POST['bank_id']) ? $_POST['bank_id'] : 0;

	$first_name_arr = $_POST['first_name_arr'];
	$middle_name_arr = $_POST['middle_name_arr'];
	$last_name_arr = $_POST['last_name_arr'];
	$adolescence_arr = $_POST['adolescence_arr'];
	$seat_no_arr = $_POST['seat_no_arr'];
	$ticket_no_arr = $_POST['ticket_no_arr'];
	$gds_pnr_arr = $_POST['gds_pnr_arr'];
	$baggage_info_arr = $_POST['baggage_info_arr'];
	$main_ticket_arr = $_POST['main_ticket_arr'];
	$trip_details_arr1 = $_POST['trip_details_arr1'];
	$canc_policy = mysqlREString($_POST['canc_policy']);
	
	$meal_plan_arr = isset($_POST['meal_plan_arr']) ? $_POST['meal_plan_arr'] : [];
	$roundoff = isset($_POST['roundoff']) ? $_POST['roundoff'] : 0;
	$credit_charges = isset($_POST['credit_charges']) ? $_POST['credit_charges'] : 0;
	$credit_card_details = isset($_POST['credit_card_details']) ? $_POST['credit_card_details'] : '';
	$control = isset($_POST['control']) ? $_POST['control'] : '';
	$entryidArray = isset($_POST['entryidArray']) ? $_POST['entryidArray'] : [];
	
	$bsmValues = json_decode(json_encode($_POST['bsmValues']));
	
	foreach($bsmValues[0] as $key => $value){

		switch($key){
			case 'basic' : $basic_cost = ($value != "") ? $value : $basic_cost;break;
			case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
			case 'markup' : $markup = ($value != "") ? $value : $markup;break;
			case 'discount' : $discount = ($value != "") ? $value : $discount;break;
		}
	}
	$reflections = json_encode($_POST['reflections']);
	$due_date = get_date_db($due_date);
	$payment_date = get_date_db($payment_date);
	$booking_date = get_date_db($booking_date);

	if($payment_mode == "Cheque" || $payment_mode == "Credit Card"){
		$clearance_status = "Pending"; }
	else {  $clearance_status = ""; }
	$financial_year_id = $_SESSION['financial_year_id'];

	begin_t();
    //Get Customer id
    if($customer_id == '0'){
		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
		$customer_id = $sq_max['max'];
    }
	//***Booking information
	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(ticket_id) as max from ticket_master"));
	$ticket_id = $sq_max['max'] + 1;

    //Invoice number reset to one in new financial year
    $sq_count = mysqli_num_rows(mysqlQuery("select entry_id from invoice_no_reset_master where service_name='flight' and financial_year_id='$financial_year_id'"));
    if($sq_count > 0){ // Already having bookings for this financial year
    
		$sq_invoice = mysqli_fetch_assoc(mysqlQuery("select max_booking_id from invoice_no_reset_master where service_name='flight' and financial_year_id='$financial_year_id'"));
		$invoice_pr_id = $sq_invoice['max_booking_id'] + 1;
		$sq_invoice = mysqlQuery("update invoice_no_reset_master set max_booking_id = '$invoice_pr_id' where service_name='flight' and financial_year_id='$financial_year_id'");
    }
    else{ // This financial year's first booking
		// Get max entry_id of invoice_no_reset_master here
		$sq_entry_id = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as entry_id from invoice_no_reset_master"));
		$max_entry_id = $sq_entry_id['entry_id'] + 1;
		
		// Insert booking-id(1) for new financial_year only for first the time
		$sq_invoice = mysqlQuery("insert into invoice_no_reset_master(entry_id ,service_name, financial_year_id ,max_booking_id) values ('$max_entry_id','flight','$financial_year_id','1')");
		$invoice_pr_id = 1;
    }

	$bsmValues = json_encode($bsmValues);
	$sq_ticket = mysqlQuery("INSERT INTO ticket_master (ticket_id, ticket_reissue,customer_id, branch_admin_id,financial_year_id, tour_type, adults, childrens, infant, adult_fair, children_fair, infant_fair, basic_cost, markup, basic_cost_discount, yq_tax, other_taxes, service_charge , service_tax_subtotal, service_tax_markup, tds, due_date, ticket_total_cost, created_at,emp_id, reflections,roundoff,bsm_values, canc_policy,guest_name,invoice_pr_id) VALUES ('$ticket_id','$ticket_reissue','$customer_id','$branch_admin_id','$financial_year_id', '$tour_type', '$adults', '$childrens', '$infant', '$adult_fair', '$children_fair', '$infant_fair', '$basic_cost','$markup', '$discount', '$yq_tax', '$other_taxes', '$service_charge', '$service_tax_subtotal', '$service_tax_markup' , '$tds', '$due_date', '$ticket_total_cost', '$booking_date','$emp_id','$reflections','$roundoff','$bsmValues', '$canc_policy','$guest_name','$invoice_pr_id')");

	if(!$sq_ticket){
		$GLOBALS['flag'] = false;
		echo "error--Sorry, information not saved!";
	}

	//***Member information
	for($i=0; $i<sizeof($first_name_arr); $i++){
		
		$trip_details_arr2 = json_decode($trip_details_arr1[$i]);
		$trip_details_arr3 = json_decode($trip_details_arr2);
		$ttour_type = $trip_details_arr3[0]->type_of_tour;

		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from ticket_master_entries"));
		$pass_id = $sq_max['max'] + 1;

		$first_name_arr[$i]    =  mysqlREString($first_name_arr[$i]);
		$middle_name_arr[$i]   =  mysqlREString($middle_name_arr[$i]);
		$last_name_arr[$i]     =  mysqlREString($last_name_arr[$i]);
		$baggage_info_arr[$i]  =  mysqlREString($baggage_info_arr[$i]);

		$sq_entry = mysqlQuery("insert into ticket_master_entries(entry_id, ticket_id, first_name, middle_name, last_name, adolescence,ticket_no, gds_pnr,baggage_info,seat_no,main_ticket,meal_plan,type_of_tour) values('$pass_id', '$ticket_id', '$first_name_arr[$i]','$middle_name_arr[$i]','$last_name_arr[$i]', '$adolescence_arr[$i]', '$ticket_no_arr[$i]', '$gds_pnr_arr[$i]','$baggage_info_arr[$i]','$seat_no_arr[$i]','$main_ticket_arr[$i]','$meal_plan_arr[$i]','$ttour_type')");

		if(!$sq_entry){
			$GLOBALS['flag'] = false;
			echo "error--Error in passenger information!";
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
		$trip_data_check_arr = isset($trip_details_arr3[0]) ? $trip_details_arr3[0]->trip_data_check_arr : [];
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
			$trip_data_check_arr[$j] = isset($trip_data_check_arr[$j]) ? $trip_data_check_arr[$j] : '';

			if($trip_data_check_arr[$j]){
				$filterAirline = explode('(',$airlines_name_arr[$j]);
				$tempAirlineCode = substr($filterAirline[1],0,strlen($filterAirline[1])-1);
				$airlineIdMain = mysqli_fetch_assoc(mysqlQuery('select * from airline_master where airline_code="'.$tempAirlineCode.'"'))['airline_id'];
				$airline_id = $airlineIdMain;

				$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from ticket_trip_entries"));
				$entry_id = $sq_max['max'] + 1;
				$sq_count = mysqli_num_rows(mysqlQuery("select * from ticket_trip_entries where airlin_pnr='$airlin_pnr_arr[$j]' and airlin_pnr!='' and status!='Cancel'"));

				$departure_datetime_arr[$j] = get_datetime_db($departure_datetime_arr[$j]);
				$arrival_datetime_arr[$j] = get_datetime_db($arrival_datetime_arr[$j]);

				$special_note1 = addslashes($special_note_arr[$j]);
				$sq_entry = mysqlQuery("insert into ticket_trip_entries(entry_id,passenger_id, ticket_id,airline_id, departure_datetime, arrival_datetime, airlines_name, class ,flight_no, airlin_pnr, from_city, to_city, departure_city, arrival_city,meal_plan,luggage, special_note, arrival_terminal, departure_terminal,sub_category,no_of_pieces,aircraft_type,operating_carrier,frequent_flyer,ticket_status,basic_fare,flight_duration,layover_time,refund_type) values ('$entry_id','$pass_id','$ticket_id','$airline_id','$departure_datetime_arr[$j]', '$arrival_datetime_arr[$j]','$airlines_name_arr[$j]', '$class_arr[$j]', '$flight_no_arr[$j]', '$airlin_pnr_arr[$j]','$from_city_id_arr[$j]','$to_city_id_arr[$j]','$departure_city_arr[$j]','$arrival_city_arr[$j]', '','$luggage_arr[$j]','$special_note1','$arrival_terminal_arr[$j]','$departure_terminal_arr[$j]','$sub_category_arr[$j]','$no_of_pieces_arr[$j]','$aircraft_type_arr[$j]','$operating_carrier_arr[$j]','$frequent_flyer_arr[$j]','$ticket_status_arr[$j]','$basic_fare_arr[$j]','$flight_duration_arr[$j]','$layover_time_arr[$j]','$refund_type_arr[$j]')");

				$dep = explode('(',$departure_city_arr[$j]);
				$arr = explode('(',$arrival_city_arr[$j]);
				if($j == 0)
					$sector = str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
				if($j>0)
					$sector = $sector.','.str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
				if(!$sq_entry){
					$GLOBALS['flag'] = false;
					echo "error--Error in ticket information!";
				}
			}
		}
	}
	//***Payment Information
	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from ticket_payment_master"));
	$payment_id = $sq_max['max'] + 1;

	$sq_payment = mysqlQuery("insert into ticket_payment_master (payment_id, ticket_id, financial_year_id,branch_admin_id, payment_date, payment_amount, payment_mode, bank_name, transaction_id, bank_id, clearance_status,credit_charges,credit_card_details) values ('$payment_id', '$ticket_id', '$financial_year_id', '$branch_admin_id', '$payment_date', '$payment_amount', '$payment_mode', '$bank_name', '$transaction_id', '$bank_id', '$clearance_status','$credit_charges','$credit_card_details') ");

	if(!$sq_payment){
		$GLOBALS['flag'] = false;
		echo "error--Sorry, Payment not saved!";
	}
	//Update customer credit note balance
	$payment_amount1 = $payment_amount;
	$sq_credit_note = mysqlQuery("select * from credit_note_master where customer_id='$customer_id'");
	$i=0;
	while($row_credit = mysqli_fetch_assoc($sq_credit_note)) {

		if($row_credit['payment_amount'] <= $payment_amount1 && $payment_amount1 != '0'){
			$payment_amount1 = $payment_amount1 - $row_credit['payment_amount'];
			$temp_amount = 0;
		}
		else{
			$temp_amount = $row_credit['payment_amount'] - $payment_amount1;
			$payment_amount1 = 0;
		}
		$sq_credit = mysqlQuery("update credit_note_master set payment_amount ='$temp_amount' where id='$row_credit[id]'");

	}
	//Get Particular
	$pax = $adults + $childrens;
	$particular = $this->get_particular($customer_id,$pax,$sector,$ticket_no_arr[0],$gds_pnr_arr[0],$ticket_id);
	//Finance save
	$this->finance_save($ticket_id, $payment_id, $row_spec, $branch_admin_id,$particular);
	//Bank and Cash Book Save
	if($payment_mode != 'Credit Note'){
		$this->bank_cash_book_save($ticket_id, $payment_id, $branch_admin_id,$particular);
	}

	if($GLOBALS['flag']){

		commit_t();
		//Ticket Booking email send
		$this->ticket_booking_email_send($ticket_id,$payment_amount);
        $this->booking_sms($ticket_id, $customer_id, $booking_date);

		//Ticket payment email send
		$ticket_payment_master  = new ticket_payment_master;
		$ticket_payment_master->payment_email_notification_send($ticket_id, $payment_amount, $payment_mode, $payment_date);

		//Ticket payment sms send
		if($payment_amount != 0){
			$ticket_payment_master->payment_sms_notification_send($ticket_id, $payment_amount, $payment_mode);
		}

		echo "Flight Ticket Booking has been successfully saved-".$ticket_id;
		if($control == 'Airfile'){
			foreach($entryidArray as $entryid){
				mysqlQuery("UPDATE `ticket_master_entries_airfile` SET `status` = 'Cleared' WHERE `entry_id` = ".$entryid);
			}
		}
		exit;
	}
	else{
		rollback_t();
		exit;
	}
}

public function ticket_master_delete(){

	global $delete_master,$transaction_master;
	$ticket_id = $_POST['booking_id'];

	$deleted_date = date('Y-m-d');
	$row_spec = "sales";

	$row_ticket = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$ticket_id'"));

	$reflections = json_decode($row_ticket['reflections']);
	$service_tax_markup = $row_ticket['service_tax_markup'];
	$service_tax_subtotal = $row_ticket['service_tax_subtotal'];
	$ticket_total_cost = $row_ticket['ticket_total_cost'];
	$customer_id = $row_ticket['customer_id'];
	$booking_date = $row_ticket['created_at'];
	$yr = explode("-", $booking_date);
	$year = $yr[0];
	$sale_gl = ($row_ticket['tour_type'] == 'Domestic') ? 50 : 174;
	
	$sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
		$cust_name = $sq_ct['company_name'];
	}else{
		$cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
	}
	$pax = $row_ticket['adults'] + $row_ticket['childrens'];

	$i = 0;
	$sq_master = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where ticket_id='$ticket_id'"));
	$sq_trip = mysqlQuery("select * from ticket_trip_entries where ticket_id='$ticket_id' and status!='Cancel'");
	while($row_trip = mysqli_fetch_assoc($sq_trip)){
		
		$dep = explode('(',$row_trip['departure_city']);
		$arr = explode('(',$row_trip['arrival_city']);
		if($i == 0)
			$sector = str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
		if($i>0)
			$sector = $sector.','.str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
		$i++;
	}
	$sq_trip1 = mysqli_fetch_assoc(mysqlQuery("select * from ticket_trip_entries where ticket_id='$ticket_id' and status!='Cancel'"));

	$sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
		$cust_name = $sq_ct['company_name'];
	}else{
		$cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
	}

	$trans_id = get_ticket_booking_id($ticket_id,$year).' : '.$cust_name;
	$transaction_master->updated_entries('Flight Sale',$ticket_id,$trans_id,$ticket_total_cost,0);

	$particular = $this->get_particular($customer_id,$pax,$sector,$sq_master['ticket_no'],$sq_trip1['airlin_pnr'],$ticket_id);
	$delete_master->delete_master_entries('Invoice','Flight Ticket',$ticket_id,get_ticket_booking_id($ticket_id,$year),$cust_name,$row_ticket['ticket_total_cost']);

	//Getting customer Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
	$cust_gl = $sq_cust['ledger_id'];

    ////////////Sales/////////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $old_gl_id = $gl_id = $sale_gl;
    $payment_side = "Credit";
    $clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	
	////////////Service Charge/////////////
	$module_name = "Air Ticket Booking";
	$module_entry_id = $ticket_id;
	$transaction_id = "";
	$payment_amount = 0;
	$payment_date = $deleted_date;
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
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
		$old_gl_id = $gl_id = $ledger;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	}

    ////////////Markup/////////////
	$module_name = "Air Ticket Booking";
	$module_entry_id = $ticket_id;
	$transaction_id = "";
	$payment_amount = 0;
	$payment_date = $deleted_date;
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
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
		$old_gl_id = $gl_id = $ledger;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '1',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	}

	/////////Roundoff/////////
	$module_name = "Air Ticket Booking";
	$module_entry_id = $ticket_id;
	$transaction_id = "";
	$payment_amount = 0;
	$payment_date = $deleted_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
	$old_gl_id = $gl_id = 230;	
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    /////////TDS////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = 0;
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
    $payment_amount = 0;
    $payment_date = $deleted_date;
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
	$payment_amount = 0;
	$payment_date = $deleted_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
	$old_gl_id = $gl_id = $cust_gl;
	$payment_side = "Debit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

	$sq_delete = mysqlQuery("update ticket_master set adult_fair='0',children_fair='0',infant_fair='0', basic_cost = '0', service_charge='0', markup='0', service_tax_markup='', service_tax_subtotal='', tds='0', basic_cost_discount='0', yq_tax='0', other_taxes='0', ticket_total_cost='0', roundoff='0', delete_status='1' where ticket_id='$ticket_id'");
	if($sq_delete){
		echo 'Entry deleted successfully!';
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

    $sq_flight = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from ticket_master_entries where ticket_id='$ticket_id' "));
    $guest_name = (($sq_ct['type']=='Corporate' || $sq_ct['type'] == 'B2B') && $sq_flight['first_name']!='') ? '('.$sq_flight['first_name'].' '.$sq_flight['last_name'].')' : '';
	
	// return get_ticket_booking_id($ticket_id,$year). ' for '.$cust_name.$guest_name.' * '.$pax.' travelling for '.$sector.' against ticket no '.strtoupper($ticket_no).'/Airline PNR '.strtoupper($pnr);

	$passenger_name = $sq_pass['first_name'].' '.$sq_pass['last_name'];

    if ($cust_name == $passenger_name) {
        $cust_name_display = $cust_name;
    } else {
        $cust_name_display = $cust_name.' ('.$passenger_name.')';
    }

    return get_ticket_booking_id($ticket_id, $year). ' for '.$cust_name_display.' * '.$pax.' travelling for '.$sector.' against ticket no '.strtoupper($ticket_no).'/Airline PNR '.strtoupper($pnr);
}

public function finance_save($ticket_id, $payment_id, $row_spec, $branch_admin_id,$particular)
{
	$customer_id = $_POST['customer_id'];
	$tour_type = $_POST['tour_type'];
	$basic_cost = $_POST['basic_cost'];
	$markup = $_POST['markup'];
	$discount = $_POST['discount'];
	$yq_tax = $_POST['yq_tax'];
	$service_charge = $_POST['service_charge'];
	$service_tax_markup = $_POST['service_tax_markup'];
	$other_taxes = $_POST['other_taxes'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$tds = $_POST['tds'];
	$ticket_total_cost = $_POST['ticket_total_cost'];
    $booking_date = $_POST['booking_date'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];

	$reflections = json_decode(json_encode($_POST['reflections']));
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
	$booking_date = date('Y-m-d', strtotime($booking_date));
	$payment_date1 = date('Y-m-d', strtotime($payment_date));
	$year1 = explode("-", $booking_date);
	$yr1 =$year1[0];
	$year2 = explode("-", $payment_date1);
	$yr2 =$year2[0];

	$total_sale = floatval($basic_cost) + floatval($yq_tax) + floatval($other_taxes);
	$payment_amount1 = floatval($payment_amount1) + floatval($credit_charges);

	//Get Customer id
    if($customer_id == '0'){
    	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
	    $customer_id = $sq_max['max'];
    }

    //Getting customer Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
	$cust_gl = $sq_cust['ledger_id'];

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT';}
    else{
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
		$pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK RECEIPT';
    }

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
    $gl_id = $sale_gl;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

    /////////Service Charge////////
	$module_name = "Air Ticket Booking";
	$module_entry_id = $ticket_id;
	$transaction_id = "";
	$payment_amount = $service_charge;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
	$gl_id = ($reflections[0]->flight_sc != '') ? $reflections[0]->flight_sc : 187;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

	/////////Service Charge Tax Amount////////
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
		$gl_id = $ledger;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');
	}

	///////////Markup//////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $markup;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $gl_id = ($reflections[0]->flight_markup != '') ? $reflections[0]->flight_markup : 199;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

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
		$gl_id = $ledger;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '1',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');
	}

    /////////TDS////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $gl_id = ($reflections[0]->flight_tds != '') ? $reflections[0]->flight_tds : 127;
    $payment_side = "Debit";
    $clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

    /////////Discount////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $discount;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $gl_id = 36;
    $payment_side = "Debit";
    $clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

	////////Customer Amount//////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $ticket_total_cost;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $gl_id = $cust_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

	////Roundoff Value
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $roundoff;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Flight Ticket Sales');
    $gl_id = 230;
    $payment_side = "Credit";
    $clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

	//////////Payment Amount///////////
	if($payment_mode != 'Credit Note'){

		if($payment_mode == 'Credit Card'){

			//////Customer Credit charges///////
			$module_name = "Air Ticket Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_charges;
			$payment_date = $payment_date1;
			$payment_particular = get_sales_paid_particular(get_ticket_booking_id($ticket_id,$yr1), $payment_date1, $credit_charges, $customer_id, $payment_mode, get_ticket_booking_id($ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $cust_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Credit charges ledger///////
			$module_name = "Air Ticket Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_charges;
			$payment_date = $payment_date1;
			$payment_particular = get_sales_paid_particular(get_ticket_booking_id($ticket_id,$yr1), $payment_date1, $credit_charges, $customer_id, $payment_mode, get_ticket_booking_id($ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = 224;
			$payment_side = "Credit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Get Credit card company Ledger///////
			$credit_card_details = explode('-',$credit_card_details);
			$entry_id = $credit_card_details[0];
			$sq_cust1 = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$entry_id' and user_type='credit company'"));
			$company_gl = $sq_cust1['ledger_id'];
			//////Get Credit card company Charges///////
			$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select * from credit_card_company where entry_id='$entry_id'"));
			//////company's credit card charges
			$company_card_charges = ($sq_credit_charges['charges_in'] =='Flat') ? $sq_credit_charges['credit_card_charges'] : ($payment_amount1 * ($sq_credit_charges['credit_card_charges']/100));
			//////company's tax on credit card charges
			$tax_charges = ($sq_credit_charges['tax_charges_in'] =='Flat') ? $sq_credit_charges['tax_on_credit_card_charges'] : ($company_card_charges * ($sq_credit_charges['tax_on_credit_card_charges']/100));
			$finance_charges = $company_card_charges + $tax_charges;
			$finance_charges = number_format($finance_charges,2);
			$credit_company_amount = $payment_amount1 - $finance_charges;

			//////Finance charges ledger///////
			$module_name = "Air Ticket Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $finance_charges;
			$payment_date = $payment_date1;
			$payment_particular = get_sales_paid_particular(get_ticket_booking_id($ticket_id,$yr1), $payment_date1, $finance_charges, $customer_id, $payment_mode, get_ticket_booking_id($ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = 231;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Credit company amount///////
			$module_name = "Air Ticket Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_company_amount;
			$payment_date = $payment_date1;
			$payment_particular = get_sales_paid_particular(get_ticket_booking_id($ticket_id,$yr1), $payment_date1, $credit_company_amount, $customer_id, $payment_mode, get_ticket_booking_id($ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $company_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
		else{

			$module_name = "Air Ticket Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $payment_amount1;
			$payment_date = $payment_date1;
			$payment_particular = $particular;
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $pay_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}

		//////Customer Payment Amount///////
		$module_name = "Air Ticket Booking Payment";
		$module_entry_id = $payment_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $payment_amount1;
		$payment_date = $payment_date1;
		$payment_particular = get_sales_paid_particular(get_ticket_booking_id($ticket_id,$yr1), $payment_date1, $payment_amount1, $customer_id, $payment_mode, get_ticket_booking_id($ticket_id,$yr1),$bank_id,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = $cust_gl;
		$payment_side = "Credit";
		$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
}

public function bank_cash_book_save($ticket_id, $payment_id, $branch_admin_id){

	global $bank_cash_book_master;

	$customer_id = $_POST['customer_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];

	if($payment_mode == 'Credit Card'){

		$payment_amount = $payment_amount + $credit_charges;
		$credit_card_details = explode('-',$credit_card_details);
		$entry_id = $credit_card_details[0];
		$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
		$bank_id = $sq_credit_charges['bank_id'];
	}

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year2 = explode("-", $payment_date);
	$yr2 =$year2[0];

	//Get Customer id
    if($customer_id == '0'){
		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
		$customer_id = $sq_max['max'];
    }

	$module_name = "Air Ticket Booking Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_sales_paid_particular(get_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $payment_amount, $customer_id, $payment_mode, get_ticket_booking_id($ticket_id,$yr2),$bank_id,$transaction_id);
	$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
	$payment_side = "Debit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";
	$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);
}

public function ticket_booking_email_send($ticket_id,$payment_amount)
{
	global $currency_logo,$encrypt_decrypt,$secret_key;

	$link = BASE_URL.'view/customer';

	$sq_ticket = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$ticket_id'"));

	$date = $sq_ticket['created_at'];
	$yr = explode("-", $date);
	$year = $yr[0];

	$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_ticket[customer_id]'"));
	$customer_name = ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') ? $sq_customer['company_name'] : $sq_customer['first_name'].' '.$sq_customer['last_name'];

	$subject = 'Booking confirmation acknowledgement! ( '.get_ticket_booking_id($ticket_id,$year). ' )';

	$username = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);
	$password = $encrypt_decrypt->fnDecrypt($sq_customer['email_id'], $secret_key);

	$balance_amount = $sq_ticket['ticket_total_cost'] - $payment_amount;

	$flDetails = mysqlQuery('SELECT * FROM `ticket_trip_entries` where ticket_id = '.$ticket_id);
	$content = '
	<tr>
		<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Customer Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.$customer_name.'</td></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Total Amount</td>   <td style="text-align:left;border: 1px solid #888888;">'.$currency_logo.' '.number_format($sq_ticket['ticket_total_cost'],2).'</td></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Paid Amount</td>   <td style="text-align:left;border: 1px solid #888888;">'.$currency_logo.' '.number_format($payment_amount,2).'</td></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Balance Amount</td>   <td style="text-align:left;border: 1px solid #888888;">'.$currency_logo.' '.number_format($balance_amount,2).'</td></tr>
		</table>
	</tr>
	';
	while($rows = mysqli_fetch_assoc($flDetails)){
		$city_from = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id = ".$rows['from_city']));
		$city_to = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id = ".$rows['to_city']));
		$content .= '<tr>
		<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
			<tr><th colspan=2>Flight Details</th></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Sector From</td>   <td style="text-align:left;border: 1px solid #888888;" >'.$city_from['city_name'].'</td></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Sector To</td>   <td style="text-align:left;border: 1px solid #888888;">'.$city_to['city_name'].'</td></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Departure</td>   <td style="text-align:left;border: 1px solid #888888;">'.$rows['departure_city'].'</td></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Arrival</td>   <td style="text-align:left;border: 1px solid #888888;">'.$rows['arrival_city'].'</td></tr>
		</table>
		</tr>';
	}

	$content .= mail_login_box($username, $password, $link);

	global $model,$backoffice_email_id;
	$model->app_email_send('16',$customer_name,$password, $content,$subject);
	if(!empty($backoffice_email_id))
	$model->app_email_send('16',"Admin",$backoffice_email_id, $content,$subject);


}
public function employee_sign_up_mail($first_name, $last_name, $username, $password, $email_id)
{
	global $model;
	$link = BASE_URL.'view/customer';
	$content = mail_login_box($username, $password, $link);
	$subject ='Welcome aboard!';

	$model->app_email_send('2',$first_name,$email_id, $content,$subject,'1');
}

public function booking_sms($booking_id, $customer_id, $created_at){

    global $encrypt_decrypt, $secret_key,$app_contact_no;
    $sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	$mobile_no = $encrypt_decrypt->fnDecrypt($sq_customer_info['contact_no'], $secret_key);

    global $model;
	$message = "Dear ".$sq_customer_info['first_name']." ".$sq_customer_info['last_name'].", your Air Ticket booking is confirmed. Ticket voucher details will send you shortly. Please contact for more details ".$app_contact_no."";
    $model->send_message($mobile_no, $message);
}
public function whatsapp_send(){
	global $app_contact_no, $encrypt_decrypt, $secret_key,$app_name,$session_emp_id;

	$booking_date = $_POST['booking_date'];
	$customer_id = $_POST['customer_id'];

	if($customer_id == '0'){
		$sq_customer = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM customer_master ORDER BY customer_id DESC LIMIT 1"));
	}
	else{
		$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	}
	$mobile_no = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);
	$customer_name = ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') ? $sq_customer['company_name'] : $sq_customer['first_name'].' '.$sq_customer['last_name'];

	$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id= '$session_emp_id'"));
	if($session_emp_id == 0){
		$contact = $app_contact_no;
	}
	else{
		$contact = $sq_emp_info['mobile_no'];
	}

	$whatsapp_msg = rawurlencode('Dear '.$customer_name.',
Hope you are doing great. This is to inform you that your booking is confirmed with us. We look forward to provide you a great experience.
*Booking Date* : '.get_date_user($booking_date).'

Please contact for more details : '.$app_name.' '.$contact.'
Thank you.');
	$link = 'https://web.whatsapp.com/send?phone='.$mobile_no.'&text='.$whatsapp_msg;
	echo $link;
}

}
?>

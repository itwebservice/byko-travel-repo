<?php 

$flag = true;

class ticket_update{



public function ticket_master_update(){

	$row_spec = 'sales';

	$train_ticket_id = $_POST['train_ticket_id'];
	$customer_id = $_POST['customer_id'];
	$type_of_tour = $_POST['type_of_tour'];
	$basic_fair = $_POST['basic_fair'];

	$service_charge = $_POST['service_charge'];

	$delivery_charges = $_POST['delivery_charges'];

	$gst_on = isset($_POST['gst_on']) ? $_POST['gst_on'] : '';

	$service_tax_subtotal = $_POST['service_tax_subtotal'];

	$net_total = $_POST['net_total'];
	$old_total = $_POST['old_total'];

	$payment_due_date = $_POST['payment_due_date'];
	$booking_date1 = $_POST['booking_date1'];



	$honorific_arr = isset($_POST['honorific_arr']) ? $_POST['honorific_arr'] : [];

	$first_name_arr = isset($_POST['first_name_arr']) ? $_POST['first_name_arr'] : [];

	$middle_name_arr = isset($_POST['middle_name_arr']) ? $_POST['middle_name_arr'] : [];

	$last_name_arr = isset($_POST['last_name_arr']) ? $_POST['last_name_arr'] : [];

	$birth_date_arr = isset($_POST['birth_date_arr']) ? $_POST['birth_date_arr'] : [];

	$adolescence_arr = isset($_POST['adolescence_arr']) ? $_POST['adolescence_arr'] : [];

	$coach_number_arr = isset($_POST['coach_number_arr']) ? $_POST['coach_number_arr'] : [];

	$seat_number_arr = isset($_POST['seat_number_arr']) ? $_POST['seat_number_arr'] : [];

	$ticket_number_arr = isset($_POST['ticket_number_arr']) ? $_POST['ticket_number_arr'] : [];
	$entry_id_arr = isset($_POST['entry_id_arr']) ? $_POST['entry_id_arr'] : [];
	$e_checkbox_arr = isset($_POST['e_checkbox_arr']) ? $_POST['e_checkbox_arr'] : [];


	$travel_datetime_arr = isset($_POST['travel_datetime_arr']) ? $_POST['travel_datetime_arr'] : [];
	$travel_from_arr = isset($_POST['travel_from_arr']) ? $_POST['travel_from_arr'] : [];
	$travel_to_arr = isset($_POST['travel_to_arr']) ? $_POST['travel_to_arr'] : [];
	$train_name_arr = isset($_POST['train_name_arr']) ? $_POST['train_name_arr'] : [];
	$train_no_arr = isset($_POST['train_no_arr']) ? $_POST['train_no_arr'] : [];
	$ticket_status_arr = isset($_POST['ticket_status_arr']) ? $_POST['ticket_status_arr'] : [];
	$class_arr = isset($_POST['class_arr']) ? $_POST['class_arr'] : [];
	$booking_from_arr = isset($_POST['booking_from_arr']) ? $_POST['booking_from_arr'] : [];
	$boarding_at_arr = isset($_POST['boarding_at_arr']) ? $_POST['boarding_at_arr'] : [];
	$arriving_datetime_arr = isset($_POST['arriving_datetime_arr']) ? $_POST['arriving_datetime_arr'] : [];
	$trip_entry_id = isset($_POST['trip_entry_id']) ? $_POST['trip_entry_id'] : [];

	$roundoff = $_POST['roundoff'];
	$reflections = json_decode(json_encode($_POST['reflections']));
	$bsmValues = json_decode(json_encode($_POST['bsmValues']));
	foreach($bsmValues[0] as $key => $value){
		switch($key){
		case 'basic' : $basic_fair = ($value != "") ? $value : $basic_fair;break;
		case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
		}
	}
	$payment_due_date = get_date_db($payment_due_date);	
	$booking_date1 = get_date_db($booking_date1);	

	$reflections = json_encode($reflections);
	$bsmValues = json_encode($bsmValues);
	begin_t();

	$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$train_ticket_id' and delete_status='0'"));

	//**Update ticket
	$sq_ticket = mysqlQuery("UPDATE train_ticket_master SET customer_id='$customer_id', type_of_tour='$type_of_tour', basic_fair='$basic_fair', service_charge='$service_charge', delivery_charges='$delivery_charges', gst_on='$gst_on', service_tax_subtotal='$service_tax_subtotal', net_total='$net_total', payment_due_date='$payment_due_date',created_at='$booking_date1',reflections='$reflections',bsm_values='$bsmValues',roundoff='$roundoff' WHERE train_ticket_id='$train_ticket_id'");

	if(!$sq_ticket){

		$GLOBALS['flag'] = false;

		echo "error--Sorry, Ticket not updated!";

	}

	//**Updating entries
	$pax = 0;
	for($i=0; $i<sizeof($first_name_arr); $i++){



		$birth_date_arr[$i] = get_date_db($birth_date_arr[$i]);



		if($e_checkbox_arr[$i] == 'true'){
			if($entry_id_arr[$i]==""){



				$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from train_ticket_master_entries"));

				$entry_id = $sq_max['max'] + 1;



				$sq_entry = mysqlQuery("INSERT INTO train_ticket_master_entries (entry_id, train_ticket_id, honorific, first_name, middle_name, last_name, birth_date, adolescence, coach_number, seat_number, ticket_number) VALUES ('$entry_id', '$train_ticket_id', '$honorific_arr[$i]', '$first_name_arr[$i]', '$middle_name_arr[$i]', '$last_name_arr[$i]', '$birth_date_arr[$i]', '$adolescence_arr[$i]', '$coach_number_arr[$i]', '$seat_number_arr[$i]', '$ticket_number_arr[$i]')");
				if(!$sq_entry){

					$GLOBALS['flag'] = false;

					echo "error--Some entries not saved!";

				}



			}

			else{



				$sq_entry = mysqlQuery("UPDATE train_ticket_master_entries SET  honorific='$honorific_arr[$i]', first_name='$first_name_arr[$i]', middle_name='$middle_name_arr[$i]', last_name='$last_name_arr[$i]', birth_date='$birth_date_arr[$i]', adolescence='$adolescence_arr[$i]', coach_number='$coach_number_arr[$i]', seat_number='$seat_number_arr[$i]', ticket_number='$ticket_number_arr[$i]' WHERE entry_id='$entry_id_arr[$i]' ");

				if(!$sq_entry){

					$GLOBALS['flag'] = false;

					echo "error--Some entries not updated!";

				}



			}
			if($adolescence_arr[$i] != "Infant"){
				$pax++;
			}
		}else{
			
			$sq_entry = mysqlQuery("delete from train_ticket_master_entries where entry_id='$entry_id_arr[$i]'");
			if(!$sq_entry){

				$GLOBALS['flag'] = false;

				echo "error--Some entries not deleted!";

			}
		}

		



	}



	//**Updating trip

	for($i=0; $i<sizeof($travel_datetime_arr); $i++){



		$travel_datetime_arr[$i] = get_datetime_db($travel_datetime_arr[$i]);

		$arriving_datetime_arr[$i] = get_datetime_db($arriving_datetime_arr[$i]);


		$travel_datetime_arr[$i] = isset($travel_datetime_arr[$i]) ? $travel_datetime_arr[$i] : '';
		$travel_from_arr[$i] = isset($travel_from_arr[$i]) ? $travel_from_arr[$i] : '';
		$travel_to_arr[$i] = isset($travel_to_arr[$i]) ? $travel_to_arr[$i] : '';
		$train_name_arr[$i] = isset($train_name_arr[$i]) ? $train_name_arr[$i] : '';
		$train_no_arr[$i] = isset($train_no_arr[$i]) ? $train_no_arr[$i] : '';
		$ticket_status_arr[$i] = isset($ticket_status_arr[$i]) ? $ticket_status_arr[$i] : '';
		$class_arr[$i] = isset($class_arr[$i]) ? $class_arr[$i] : '';
		$booking_from_arr[$i] = isset($booking_from_arr[$i]) ? $booking_from_arr[$i] : '';
		$boarding_at_arr[$i] = isset($boarding_at_arr[$i]) ? $boarding_at_arr[$i] : '';
		$arriving_datetime_arr[$i] = isset($arriving_datetime_arr[$i]) ? $arriving_datetime_arr[$i] : '';
		$trip_entry_id[$i] = isset($trip_entry_id[$i]) ? $trip_entry_id[$i] : '';

		if($trip_entry_id[$i]==""){

			$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from train_ticket_master_trip_entries"));
			$entry_id = $sq_max['max'] + 1;

			$sq_entry = mysqlQuery("INSERT INTO train_ticket_master_trip_entries (entry_id, train_ticket_id, travel_datetime, travel_from, travel_to, train_name, train_no, ticket_status, class, booking_from, boarding_at, arriving_datetime) VALUES ('$entry_id', '$train_ticket_id', '$travel_datetime_arr[$i]', '$travel_from_arr[$i]', '$travel_to_arr[$i]', '$train_name_arr[$i]', '$train_no_arr[$i]', '$ticket_status_arr[$i]', '$class_arr[$i]', '$booking_from_arr[$i]', '$boarding_at_arr[$i]', '$arriving_datetime_arr[$i]')");
			if(!$sq_entry){

				$GLOBALS['flag'] = false;
				echo "error--Some trip entries not saved!";
			}
		}
		else{


			$sq_entry = mysqlQuery("UPDATE train_ticket_master_trip_entries SET  travel_datetime='$travel_datetime_arr[$i]', travel_from='$travel_from_arr[$i]', travel_to='$travel_to_arr[$i]', train_name='$train_name_arr[$i]', train_no='$train_no_arr[$i]', ticket_status='$ticket_status_arr[$i]', class='$class_arr[$i]', booking_from='$booking_from_arr[$i]', boarding_at='$boarding_at_arr[$i]', arriving_datetime='$arriving_datetime_arr[$i]' WHERE entry_id='$trip_entry_id[$i]' ");

			if(!$sq_entry){
				$GLOBALS['flag'] = false;
				echo "error--Some trip entries not updated!";
			}

		}
		if($i == 0)
			$sector = $travel_from_arr[$i].'-'.$travel_to_arr[$i];
		if($i>0)
			$sector = $sector.','.$travel_from_arr[$i].'-'.$travel_to_arr[$i];

	}

	$sq_train = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master_trip_entries where train_ticket_id='$train_ticket_id'"));
	$sq_train_master = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master_entries where train_ticket_id='$train_ticket_id'"));
	//Get Particular
	$particular = $this->get_particular($customer_id,$pax,$sector,$sq_train['train_no'],$sq_train_master['ticket_number'],$sq_train['class'],$train_ticket_id);
	//Finance update
	$this->finance_update($sq_ticket_info, $row_spec, $particular);

	global $transaction_master;
	if(floatval($old_total) != floatval($net_total)){

		$yr = explode("-", $booking_date1);
		$year = $yr[0];
        $sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
        if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
            $cust_name = $sq_ct['company_name'];
        }else{
            $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
        }

		$trans_id = get_train_ticket_booking_id($train_ticket_id,$year).' : '.$cust_name;
		$transaction_master->updated_entries('Train Sale',$train_ticket_id,$trans_id,$old_total,$net_total);
	}
	if($GLOBALS['flag']){
		commit_t();
		echo "Train Ticket Booking has been successfully updated.";
		exit;	
	}
	else{
		rollback_t();
		exit;
	}
}

function get_particular($customer_id,$pax,$sector,$train_no,$ticket_number,$class,$train_ticket_id){
	$sq_train = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$train_ticket_id'"));
	$booking_date = $sq_train['created_at'];
	$yr = explode("-", $booking_date);
	$year = $yr[0];
	$sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master_entries where train_ticket_id='$sq_train[train_ticket_id]' and status!='Cancel'"));

	$sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	$cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
	return get_train_ticket_booking_id($train_ticket_id,$year).' and towards the train tkt of '.$cust_name. '('.$sq_pass['first_name'].' '.$sq_pass['last_name'].') * '.$pax.' traveling for '.$sector.' against ticket no '.$ticket_number.' by '.$train_no.'/'.$class;
}

public function finance_update($sq_ticket_info, $row_spec,$particular)
{
	$train_ticket_id = $_POST['train_ticket_id'];
	$customer_id = $_POST['customer_id'];
	$basic_fair = $_POST['basic_fair'];
	$service_charge = $_POST['service_charge'];
    $delivery_charges = $_POST['delivery_charges'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$net_total = $_POST['net_total'];
	$booking_date1 = $_POST['booking_date1'];
	$roundoff = $_POST['roundoff'];
	
	$reflections = json_decode(json_encode($_POST['reflections']));
	$bsmValues = json_decode(json_encode($_POST['bsmValues']));
	$booking_date = get_date_db($booking_date1);

	foreach($bsmValues[0] as $key => $value){
		switch($key){
		case 'basic' : $basic_fair = ($value != "") ? $value : $basic_fair;break;
		case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
		}
	}
	$train_sale_amount = $basic_fair;

  	//Getting customer Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
	$cust_gl = $sq_cust['ledger_id'];

	global $transaction_master;

	////////////Sales/////////////
	$module_name = "Train Ticket Booking";
	$module_entry_id = $train_ticket_id;
	$transaction_id = "";
	$payment_amount = $train_sale_amount;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Train Ticket Sales');
	$old_gl_id = $gl_id = 133;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

	////////////service charge/////////////
	$module_name = "Train Ticket Booking";
	$module_entry_id = $train_ticket_id;
	$transaction_id = "";
	$payment_amount = $service_charge;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Train Ticket Sales');
	$old_gl_id = $gl_id = ($reflections[0]->train_sc != '') ? $reflections[0]->train_sc : 189;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

	/////////Service Charge Tax Amount////////
	// Eg. CGST:(9%):24.77, SGST:(9%):24.77
	$service_tax_subtotal = explode(',',$service_tax_subtotal);
	$tax_ledgers = explode(',',$reflections[0]->train_taxes);
	for($i=0;$i<sizeof($service_tax_subtotal);$i++){

		$service_tax = explode(':',$service_tax_subtotal[$i]);
		$tax_amount = $service_tax[2];
		$ledger = isset($tax_ledgers[$i]) ? $tax_ledgers[$i] : '';

		$module_name = "Train Ticket Booking";
		$module_entry_id = $train_ticket_id;
		$transaction_id = "";
		$payment_amount = $tax_amount;
		$payment_date = $booking_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To','Train Ticket Sales');
		$old_gl_id = $gl_id = $ledger;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	}
	/////////roundoff/////////
	$module_name = "Train Ticket Booking";
	$module_entry_id = $train_ticket_id;
	$transaction_id = "";
	$payment_amount = $roundoff;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Train Ticket Sales');
	$old_gl_id = $gl_id = 230;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	///////// Delivery charges //////////
	$module_name = "Train Ticket Booking";
	$module_entry_id = $train_ticket_id;
	$transaction_id = "";
	$payment_amount = $delivery_charges;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Train Ticket Sales');
	$old_gl_id = $gl_id = 33;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	////////Customer Amount//////
	$module_name = "Train Ticket Booking";
	$module_entry_id = $train_ticket_id;
	$transaction_id = "";
	$payment_amount = $net_total;
	$payment_date = $booking_date;
	$payment_particular = $particular;
	$ledger_particular = get_ledger_particular('To','Train Ticket Sales');
	$old_gl_id = $gl_id = $cust_gl;
	$payment_side = "Debit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
	

}



}

?>
<?php
$flag = true;
class ticket_refund_estimate{

public function refund_estimate_update(){

  $row_spec ='sales';
  $ticket_id = $_POST['ticket_id'];
  $cancel_amount = $_POST['cancel_amount'];
  $total_refund_amount = $_POST['total_refund_amount'];
  $estimate_arr = json_encode($_POST['estimate_arr']);
  $tax_value = $_POST['tax_value'];
  $tour_service_tax_subtotal = $_POST['tour_service_tax_subtotal'];
  $cancel_amount_exc = $_POST['cancel_amount_exc'];

  begin_t();

  $sq_refund = mysqlQuery("update ticket_master set cancel_amount='$cancel_amount', total_refund_amount='$total_refund_amount',cancel_flag='1',cancel_estimate='$estimate_arr',`tax_value`='$tax_value', `tax_amount`='$tour_service_tax_subtotal', `cancel_amount_exc`='$cancel_amount_exc' where ticket_id='$ticket_id'");

  if($sq_refund){

  	//Finance save
    $this->finance_save($ticket_id,$row_spec);
  	if($GLOBALS['flag']){

  		commit_t();
  		echo "Refund estimate has been successfully saved.";
  		exit;
  	}
  	else{
  		rollback_t();
  		exit;
  	}
  }
  else{
  	rollback_t();
  	echo "Cancellation not saved!";
  	exit;
  }
}

public function finance_save($ticket_id,$row_spec)
{
  $branch_admin_id =$_SESSION['branch_admin_id'];
	$ticket_id = $_POST['ticket_id'];
  $cancel_amount = $_POST['cancel_amount'];
  $estimate_arr = json_decode(json_encode($_POST['estimate_arr']));
  $ledger_posting = $_POST['ledger_posting'];
  $cancel_amount_exc = $_POST['cancel_amount_exc'];
  $tour_service_tax_subtotal_cancel = $_POST['tour_service_tax_subtotal'];

  $basic_cost = $estimate_arr[0]->basic_cost;
  $yq_tax = $estimate_arr[0]->yq_tax;
  $other_taxes = $estimate_arr[0]->other_taxes;
  $discount = $estimate_arr[0]->discount;
  $service_charge = $estimate_arr[0]->service_charge;
  $service_tax_subtotal = $estimate_arr[0]->service_tax_subtotal;
  $markup = $estimate_arr[0]->markup;
  $service_tax_markup = $estimate_arr[0]->service_tax_markup;
  $tds = $estimate_arr[0]->tds;
  $roundoff = $estimate_arr[0]->roundoff;
  $ticket_total_cost = $estimate_arr[0]->ticket_total_cost;

  $created_at = date("Y-m-d");
  $sq_ticket = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$ticket_id'"));
  $customer_id = $sq_ticket['customer_id'];
  $reflections = json_decode($sq_ticket['reflections']);
	$year2 = explode("-", $sq_ticket['created_at']);
	$yr2 = $year2[0];
  //Getting customer Ledger
  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
  $cust_gl = $sq_cust['ledger_id'];

  $total_sale = floatval($basic_cost) + floatval($yq_tax) + floatval($other_taxes);
  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	$cust_name = ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') ? $sq_cust['company_name'] : $sq_cust['first_name'].' '.$sq_cust['last_name'];
  $sq_flight = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from ticket_master_entries where ticket_id='$ticket_id' "));
  $guest_name = (($sq_cust['type']=='Corporate' || $sq_cust['type'] == 'B2B') && $sq_flight['first_name']!='') ? '('.$sq_flight['first_name'].' '.$sq_flight['last_name'].')' : '';

  $pax = $sq_ticket['adults'] + $sq_ticket['childrens'];
  
  // Full/Sectorwise
  if($sq_ticket['cancel_type'] == 1 || $sq_ticket['cancel_type'] == 3){

    $i=0;
    $sector = '';
    $flight_no = '';
    $airline_pnr = '';
    $sq_trip1 = mysqlQuery("select * from ticket_trip_entries where ticket_id='$ticket_id' and status='Cancel'");
    while($sq_trip = mysqli_fetch_assoc($sq_trip1)){
  
      $dep = explode('(',$sq_trip['departure_city']);
      $arr = explode('(',$sq_trip['arrival_city']);
      if($i == 0){
        $sector .= str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
        $airline_pnr .= $sq_trip['airlin_pnr'];
        $flight_no .= $sq_trip['flight_no'];
      }
      else{
        $sector = $sector.','.str_replace(')','',$dep[1]).'-'.str_replace(')','',$arr[1]);
        $airline_pnr .= '/'.$sq_trip['airlin_pnr'];
        $flight_no .= '/'.$sq_trip['flight_no'];
      }
      $i++;
    }
    $particular = 'Sales against ('.$sq_ticket['tour_type'].' Air Ticket) pax: '.$cust_name.$guest_name.' * '.$pax.' sector(s) '.$sector.', flight no ('.$flight_no.') /GDS PNR ('.$airline_pnr.') [Invoice no '.get_ticket_booking_id($ticket_id,$yr2).' '.get_date_user($sq_ticket['created_at']).']';
  }
  else if($sq_ticket['cancel_type'] == 2){ // Passengerwise

    $i=0;
    $pnr = '';
    $ticket_no = '';
    $pass = '';
    $sq_pax1 = mysqlQuery("select * from ticket_master_entries where ticket_id='$ticket_id' and status='Cancel'");
    while($sq_trip = mysqli_fetch_assoc($sq_pax1)){
      
      if($i == 0){
        $pass .= $sq_trip['first_name']." ".$sq_trip['last_name'];
        $pnr .= $sq_trip['gds_pnr'];
        $ticket_no .= $sq_trip['ticket_no'];
      }
      else{
        $pass = $pass.'/'.$sq_trip['first_name']." ".$sq_trip['last_name'];
        $pnr .= '/'.$sq_trip['gds_pnr'];
        $ticket_no .= '/'.$sq_trip['ticket_no'];
      }
      $i++;
    }
    $particular = 'Sales against ('.$sq_ticket['tour_type'].' Air Ticket) pax: '.$cust_name.$guest_name.' * '.$pax.' passenger(s) '.$pass.', ticket no ('.strtoupper($ticket_no).') /Airline PNR ('.strtoupper($pnr).') [Invoice no '.get_ticket_booking_id($ticket_id,$yr2).' '.get_date_user($sq_ticket['created_at']).'](Cancelled)';
  }

  global $transaction_master;
	$sale_gl = ($sq_ticket['tour_type'] == 'Domestic') ? 51 : 175;

    //////////Sales/////////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $total_sale;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = $sale_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');

    /////////Service Charge////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $service_charge;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = ($reflections[0]->flight_sc != '') ? $reflections[0]->flight_sc : 187;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');

	  /////////Service Charge Tax Amount////////
    $tax_ledgers = explode(',',$reflections[0]->flight_taxes);
    $tax_amount = (sizeof($tax_ledgers) == 1) ? $service_tax_subtotal : floatval($service_tax_subtotal)/sizeof($tax_ledgers);
    for($i=0;$i<sizeof($tax_ledgers);$i++){

      $ledger = isset($tax_ledgers[$i]) ? $tax_ledgers[$i] : '';

      $module_name = "Air Ticket Booking";
      $module_entry_id = $ticket_id;
      $transaction_id = "";
      $payment_amount = $tax_amount;
      $payment_date = $created_at;
      $payment_particular = $particular;
      $ledger_particular = '';
      $gl_id = $ledger;
      $payment_side = "Debit";
      $clearance_status = "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');
	  }
	
	  ///////////Markup//////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $markup;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = ($reflections[0]->flight_markup != '') ? $reflections[0]->flight_markup : 199;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');

    /////////Markup Tax Amount////////
    // Eg. CGST:(9%):24.77, SGST:(9%):24.77
    $tax_ledgers = explode(',',$reflections[0]->flight_markup_taxes);
    $tax_amount = (sizeof($tax_ledgers) == 1) ? $service_tax_markup : floatval($service_tax_markup)/2;
    for($i=0;$i<sizeof($tax_ledgers);$i++){

      $ledger = isset($tax_ledgers[$i]) ? $tax_ledgers[$i] : '';
      $module_name = "Air Ticket Booking";
      $module_entry_id = $ticket_id;
      $transaction_id = "";
      $payment_amount = $tax_amount;
      $payment_date = $created_at;
      $payment_particular = $particular;
      $ledger_particular = '';
      $gl_id = $ledger;
      $payment_side = "Debit";
      $clearance_status = "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');
	  }

    /////////TDS////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = ($reflections[0]->flight_tds != '') ? $reflections[0]->flight_tds : 127;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');

    /////////Discount////////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $discount;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 36;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');

    ////Roundoff Value
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $roundoff;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 230;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');

    ////////Customer Sale Amount//////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $ticket_total_cost;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = $cust_gl;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');    

	  /////////Service Charge Tax Amount////////
    $service_tax_subtotal = explode(',',$tour_service_tax_subtotal_cancel);
    $tax_ledgers = explode(',',$ledger_posting);
    for($i=0;$i<sizeof($service_tax_subtotal);$i++){

      $service_tax = explode(':',$service_tax_subtotal[$i]);
      $tax_amount = $service_tax[2];
      $ledger = isset($tax_ledgers[$i]) ? $tax_ledgers[$i] : '';

      $module_name = "Air Ticket Booking";
      $module_entry_id = $ticket_id;
      $transaction_id = "";
      $payment_amount = $tax_amount;
      $payment_date = $created_at;
      $payment_particular = $particular;
      $ledger_particular = '';
      $gl_id = $ledger;
      $payment_side = "Credit";
      $clearance_status = "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');
	  }

    ////////Cancel Amount//////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $cancel_amount_exc;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 161;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');    

    ////////Customer Cancel Amount//////
    $module_name = "Air Ticket Booking";
    $module_entry_id = $ticket_id;
    $transaction_id = "";
    $payment_amount = $cancel_amount;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = $cust_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND'); 

}

}
?>
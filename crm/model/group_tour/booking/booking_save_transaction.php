<?php 
class booking_save_transaction
{
function finance_save($tourwise_traveler_id, $row_spec, $branch_admin_id,$particular)
{
  global $transaction_master;
  $row_spec = 'sales';
  $customer_id = $_POST['customer_id'];
  $form_date = $_POST['form_date'];

  //**tour details
  $service_tax = $_POST['service_tax'];
  $net_total = $_POST['net_total'];
  $tcs_tax = $_POST['tcs_tax'];    

  //**Payment details
  $basic_amount = $_POST['basic_amount'];
  $roundoff = $_POST['roundoff'];
  $total_discount = $_POST['total_discount'];
  
  $booking_date = get_date_db($form_date);
  $reflections = json_decode(json_encode($_POST['reflections']));
  $bsmValues = json_decode(json_encode($_POST['bsmValues']));
    foreach($bsmValues[0] as $key => $value){
      switch($key){
      case 'basic' : $basic_amount = ($value != "") ? $value : $basic_amount;break;
      }
    }

  $total_sale_amount = $basic_amount+$total_discount;
  
  //Getting customer Ledger
  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
  $cust_gl = $sq_cust['ledger_id'];
  ////////////Sales/////////////

  $module_name = "Group Booking";
  $module_entry_id = $tourwise_traveler_id;
  $transaction_id = "";
  $payment_amount = $total_sale_amount;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Group Tour Sales');
  $gl_id = 59;
  $payment_side = "Credit";
  $clearance_status = "";
  $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

  // Discount 
  $module_name = "Group Booking";
  $module_entry_id = $tourwise_traveler_id;
  $transaction_id = "";
  $payment_amount = $total_discount;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Group Tour Sales');
  $gl_id = 36;
  $payment_side = "Debit";
  $clearance_status = "";
  $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

  /////////Service Charge Tax Amount////////
  // Eg. CGST:(9%):24.77, SGST:(9%):24.77
  $service_tax_subtotal = explode(',',$service_tax);
  $tax_ledgers = explode(',',$reflections[0]->hotel_taxes);
  for($i=0;$i<sizeof($service_tax_subtotal);$i++){

    $service_tax = explode(':',$service_tax_subtotal[$i]);
    $tax_amount = $service_tax[2];
    $ledger = $tax_ledgers[$i];

    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = $tax_amount;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Group Tour Sales');
    $gl_id = $ledger;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');
  }
  // TCS charge 
  $module_name = "Group Booking";
  $module_entry_id = $tourwise_traveler_id;
  $transaction_id = "";
  $payment_amount = $tcs_tax;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Group Tour Sales');
  $gl_id = 232;
  $payment_side = "Credit";
  $clearance_status = "";
  $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

  ////Roundoff Value
  $module_name = "Group Booking";
  $module_entry_id = $tourwise_traveler_id;
  $transaction_id = "";
  $payment_amount = $roundoff;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Group Tour Sales');
  $gl_id = 230;
  $payment_side = "Credit";
  $clearance_status = "";
  $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');
  ////////Customer Amount//////
  $module_name = "Group Booking";
  $module_entry_id = $tourwise_traveler_id;
  $transaction_id = "";
  $payment_amount = $net_total;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Group Tour Sales');
  $gl_id = $cust_gl;
  $payment_side = "Debit";
  $clearance_status = "";
  $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');
}

public function complete_booking_information_delete(){
  
    global $delete_master,$transaction_master;
    $tourwise_traveler_id = $_POST['booking_id'];

    $deleted_date = date('Y-m-d');
    $row_spec = "sales";

    $row_booking = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$tourwise_traveler_id' and delete_status='0'"));
    $reflections = json_decode($row_booking['reflections']);
    $service_tax = $row_booking['service_tax'];
    $customer_id = $row_booking['customer_id'];
    $booking_date = $row_booking['form_date'];
    $tour_id = $row_booking['tour_id'];
    $tour_group_id = $row_booking['tour_group_id'];
    $net_total = $row_booking['net_total'];
    $yr = explode("-", $booking_date);
    $year = $yr[0];
    
    $sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
    if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
      $cust_name = $sq_ct['company_name'];
    }else{
      $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
    }

    $sq_tour = mysqli_fetch_assoc(mysqlQuery("select tour_name from tour_master where tour_id='$tour_id'"));
    $tour_name = $sq_tour['tour_name'];
    $sq_tourgroup = mysqli_fetch_assoc(mysqlQuery("select from_date,to_date from tour_groups where group_id='$tour_group_id'"));
    $from_date = new DateTime($sq_tourgroup['from_date']);
    $to_date = new DateTime($sq_tourgroup['to_date']);
    $numberOfNights = $from_date->diff($to_date)->format("%a");
    
    $pass_count= mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and status!='Cancel'"));
    $sq_pass= mysqli_fetch_assoc(mysqlQuery("select * from travelers_details where traveler_group_id='$row_booking[traveler_group_id]' and status!='Cancel'"));

    $particular = get_group_booking_id($tourwise_traveler_id,$year).' and '.$tour_name.' for '.$cust_name. '('.$sq_pass['first_name'].' '.$sq_pass['last_name'].') *'.$pass_count.' for '.$numberOfNights.' Nights starting from '.get_date_user($sq_tourgroup['from_date']);

    $delete_master->delete_master_entries('Invoice','Group Tour',$tourwise_traveler_id,get_group_booking_id($tourwise_traveler_id,$year),$cust_name,$net_total);

    /////////////////// // Update entries log//////////////////////
    global $transaction_master;

    $yr = explode("-", $booking_date);
    $year = $yr[0];
    $sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
    if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
        $cust_name = $sq_ct['company_name'];
    }else{
        $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
    }

    $trans_id = get_group_booking_id($tourwise_traveler_id,$year).' : '.$cust_name;
    $transaction_master->updated_entries('Group Tour Sale',$tourwise_traveler_id,$trans_id,$net_total,0);
    /////////////////// // Update entries log end//////////////////

    //Getting customer Ledger
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
    $cust_gl = $sq_cust['ledger_id'];

    ////////////Sales/////////////
    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $olg_gl_id = $gl_id = 59;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$olg_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    // Discount 
    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Group Tour Sales');
    $olg_gl_id = $gl_id = 36;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $olg_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    //TCS charges
    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Group Tour Sales');
    $olg_gl_id = $gl_id = 232;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $olg_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    /////////roundoff/////////
    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Group Tour Sales');
    $old_gl_id = $gl_id = 230;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    /////////Service Charge Tax Amount////////
    // Eg. CGST:(9%):24.77, SGST:(9%):24.77
    $service_tax_subtotal = explode(',',$service_tax);
    $tax_ledgers = explode(',',$reflections[0]->hotel_taxes);
    for($i=0;$i<sizeof($service_tax_subtotal);$i++){

      // $service_tax = explode(':',$service_tax_subtotal[$i]);
      $ledger = $tax_ledgers[$i];

      $module_name = "Group Booking";
      $module_entry_id = $tourwise_traveler_id;
      $transaction_id = "";
      $payment_amount = 0;
      $payment_date = $booking_date;
      $payment_particular = $particular;
      $ledger_particular = get_ledger_particular('To','Group Tour Sales');
      $old_gl_id = $gl_id = $ledger;
      $payment_side = "Credit";
      $clearance_status = "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    }

    ////////Customer Amount//////
    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Group Tour Sales');
    $old_gl_id = $gl_id = $cust_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    
    $sq_delete = mysqlQuery("update tourwise_traveler_details set adult_expense='0',child_with_bed='0',child_without_bed='0',children_expense='0',infant_expense='0',tour_fee='0',repeater_discount='0',adjustment_discount='0',total_discount='0',tour_fee_subtotal_1='0',service_tax_per='',tour_taxation_id='0',service_tax='',tour_fee_subtotal_2='0',net_total='0', roundoff='0',total_travel_expense='0',cruise_expense='0',plane_expense='0',train_expense='0',total_cruise_expense='0',total_plane_expense='0',total_train_expense='0',basic_amount='0',delete_status='1',tcs_tax='0',tcs_per='0' where id = '$tourwise_traveler_id'");
    if($sq_delete){
      echo 'Entry deleted successfully!';
      exit;
    }
}
public function payment_finance_save($booking_id, $payment_id, $payment_date, $payment_mode, $payment_amount, $transaction_id1, $bank_id, $branch_admin_id,$credit_charges,$credit_card_details)
{
  global $transaction_master;

  $customer_id = $_POST['customer_id'];
  $row_spec = 'sales';

  $payment_date = get_date_db($payment_date);
	$year1 = explode("-", $payment_date);
	$yr1 =$year1[0];
  //Getting customer Ledger
  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
  $cust_gl = $sq_cust['ledger_id'];
  //Getting cash/Bank Ledger
  if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
  else{ 
    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK RECEIPT';
  } 

  $payment_amount1 = floatval($payment_amount) + floatval($credit_charges);
   //////////Payment Amount///////////
	if($payment_mode != 'Credit Note'){
		
		if($payment_mode == 'Credit Card'){

			//////Customer Credit charges///////
			$module_name = "Group Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $booking_id;
			$payment_amount = $credit_charges;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_group_booking_id($booking_id,$yr1), $payment_date, $credit_charges, $customer_id, $payment_mode, get_group_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $cust_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Credit charges ledger///////
			$module_name = "Group Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_charges;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_group_booking_id($booking_id,$yr1), $payment_date, $credit_charges, $customer_id, $payment_mode, get_group_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
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
      $credit_company_amount = $payment_amount1 - $finance_charges;
      $finance_charges = number_format($finance_charges,2);

			//////Finance charges ledger///////
			$module_name = "Group Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $finance_charges;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_group_booking_id($booking_id,$yr1), $payment_date, $finance_charges, $customer_id, $payment_mode, get_group_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = 231;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
			//////Credit company amount///////
			$module_name = "Group Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_company_amount;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_group_booking_id($booking_id,$yr1), $payment_date, $credit_company_amount, $customer_id, $payment_mode, get_group_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $company_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
		else{
			$module_name = "Group Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $payment_amount1;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_group_booking_id($booking_id,$yr1), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_group_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $pay_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}

		//////Customer Payment Amount///////
		$module_name = "Group Booking Payment";
		$module_entry_id = $payment_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $payment_amount1;
		$payment_date = $payment_date;
		$payment_particular = get_sales_paid_particular(get_group_booking_id($booking_id,$yr1), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_group_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = $cust_gl;
		$payment_side = "Credit";
		$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
}

public function bank_cash_book_save($booking_id, $payment_id, $payment_date, $payment_mode, $payment_amount, $transaction_id, $bank_name, $bank_id,$branch_admin_id)
{
  global $bank_cash_book_master;
  $payment_date = get_date_db($payment_date);
	$year1 = explode("-", $payment_date);
	$yr1 =$year1[0];

  $customer_id = $_POST['customer_id'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];

	if($payment_mode == 'Credit Card'){

		$payment_amount = $payment_amount + $credit_charges;
		$credit_card_details = explode('-',$credit_card_details);
		$entry_id = $credit_card_details[0];
		$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
		$bank_id = $sq_credit_charges['bank_id'];
	}

  $module_name = "Group Booking Payment";
  $module_entry_id = $payment_id;
  $payment_date = $payment_date;
  $payment_amount = $payment_amount;
  $payment_mode = $payment_mode;
  $bank_name = $bank_name;
  $transaction_id = $transaction_id;
  $bank_id = $bank_id;
  $particular = get_sales_paid_particular(get_group_booking_payment_id($payment_id,$yr1), $payment_date, $payment_amount, $customer_id, $payment_mode, get_group_booking_id($booking_id,$yr1),$bank_id,$transaction_id);
  $clearance_status = ($payment_mode=="Cheque" || $payment_mode=="Credit Card") ? "Pending" : "";
  $payment_side = "Debit";
  $payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";
  $bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);

}
}
?>

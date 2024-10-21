<?php

class booking_save_transaction{


function finance_save($booking_id, $row_spec, $branch_admin_id,$particular)
{
  global $transaction_master;
  $row_spec = 'sales';
  $customer_id = $_POST['customer_id'];
  $booking_date = $_POST['booking_date'];
  
  //**tour details
  $service_charge = $_POST['service_charge'];
  $basic_amount = $_POST['basic_amount'];
  $tour_service_tax_subtotal = $_POST['tour_service_tax_subtotal'];
  $act_discount = $_POST['act_discount'];
  $net_total = $_POST['net_total'];
  $tcs_tax = $_POST['tcs'];
  $tds = $_POST['tds'];

  //**Payment details
  $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
  $roundoff = isset($_POST['roundoff']) ? $_POST['roundoff'] : 0;
  $bsmValues = json_decode(json_encode($_POST['bsmValues']));
    foreach($bsmValues[0] as $key => $value){
        switch($key){
        case 'basic' : $basic_amount = ($value != "") ? $value : $basic_amount;break;
        case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
        }
      }
  $reflections = json_decode(json_encode($_POST['reflections']));
  $booking_date = get_date_db($booking_date);
	$year1 = explode("-", $booking_date);
  $yr1 =$year1[0];
  //Getting customer Ledger
  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
  $cust_gl = $sq_cust['ledger_id'];
  $total_sale_amount = $basic_amount;

  ////////////Sales/////////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $total_sale_amount;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Pacakge Sales');
    $gl_id = 91;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

    /////////Service Charge////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $service_charge;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $gl_id = ($reflections[0]->hotel_sc != '') ? $reflections[0]->hotel_sc : 185;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

    /////////Discount////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $act_discount;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $gl_id = 36;
    $payment_side = "Debit";
    $clearance_status = "";
	  $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

     /////////Service Charge Tax Amount////////
    // Eg. CGST:(9%):24.77, SGST:(9%):24.77
    $service_tax_subtotal = explode(',',$tour_service_tax_subtotal);
    $tax_ledgers = explode(',',$reflections[0]->hotel_taxes);
    for($i=0;$i<sizeof($service_tax_subtotal);$i++){

      $service_tax = explode(':',$service_tax_subtotal[$i]);
      $tax_amount = $service_tax[2];
      $ledger = $tax_ledgers[$i];
      

      $module_name = "Package Booking";
      $module_entry_id = $booking_id;
      $transaction_id = "";
      $payment_amount = $tax_amount;
      $payment_date = $booking_date;
      $payment_particular = $particular;
      $ledger_particular = get_ledger_particular('To','Package Sales');
      $gl_id = $ledger;
      $payment_side = "Credit";
      $clearance_status = "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');
    }
    ////Roundoff Value
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $roundoff;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $gl_id = 230;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

    /////////TCS Charge////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $tcs_tax;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $gl_id = 232;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

    /////////TDS Charge////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $gl_id = 127;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');

    ////////Customer Amount//////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $net_total;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $gl_id = $cust_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'INVOICE');
    
}
function package_tour_booking_master_delete(){
  
    global $delete_master,$transaction_master;
    $booking_id = $_POST['booking_id'];

    $deleted_date = date('Y-m-d');
    $row_spec = "sales";

    $row_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
    $reflections = json_decode($row_booking['reflections']);
    $tour_service_tax_subtotal = $row_booking['tour_service_tax_subtotal'];
    $customer_id = $row_booking['customer_id'];
    $booking_date = $row_booking['booking_date'];
    $yr = explode("-", $booking_date);
    $year = $yr[0];
    
    $sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
    if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
      $cust_name = $sq_ct['company_name'];
    }else{
      $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
    }
    $total_tour_days = $row_booking['total_tour_days'];
    $tour_name = $row_booking['tour_name'];
    $tour_from_date = $row_booking['tour_from_date'];
    $net_total = $row_booking['net_total'];
    
    $pass_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id' and status!='Cancel'"));
    $sq_pass= mysqli_fetch_assoc(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id' and status!='Cancel'"));

    $particular = get_package_booking_id($booking_id,$year).' and '.$tour_name.' for '.$cust_name. '('.$sq_pass['first_name'].' '.$sq_pass['last_name'].') *'.$pass_count.' for '.$total_tour_days.' Night(s) starting from '.get_date_user($tour_from_date);

    $delete_master->delete_master_entries('Invoice','Package Tour',$booking_id,get_package_booking_id($booking_id,$year),$cust_name,$net_total);
    /////////////////// // Update entries log///////////////////////
    global $transaction_master;
    $sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
    if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
        $cust_name = $sq_ct['company_name'];
    }else{
        $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
    }
    
    $trans_id = get_package_booking_id($booking_id,$year).' : '.$cust_name;
    $transaction_master->updated_entries('Package Tour Sale',$booking_id,$trans_id,$net_total,0);
    /////////////////// // Update entries log end///////////////////////

    //Getting customer Ledger
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
    $cust_gl = $sq_cust['ledger_id'];

    ////////////Sales/////////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $old_gl_id = $gl_id = 91;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

      ////////////service charge/////////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = ($reflections[0]->hotel_sc != '') ? $reflections[0]->hotel_sc : 185;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    /////////Service Charge Tax Amount////////
    // Eg. CGST:(9%):24.77, SGST:(9%):24.77
    $service_tax_subtotal = explode(',',$tour_service_tax_subtotal);
    $tax_ledgers = explode(',',$reflections[0]->hotel_taxes);
    for($i=0;$i<sizeof($service_tax_subtotal);$i++){

      $service_tax = explode(':',$service_tax_subtotal[$i]);
      $tax_amount = $service_tax[2];
      $ledger = $tax_ledgers[$i];

      $module_name = "Package Booking";
      $module_entry_id = $booking_id;
      $transaction_id = "";
      $payment_amount = 0;
      $payment_date = $deleted_date;
      $payment_particular = $particular;
      $ledger_particular = get_ledger_particular('To','Package Sales');
      $old_gl_id = $gl_id = $ledger;
      $payment_side = "Credit";
      $clearance_status = "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    }

    /////////roundoff/////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = 230;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    
    ////////////service charge/////////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = 232;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');



    ////TDS
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = 127;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');


    ///Discount

    $module_name = "Package Booking";
  $module_entry_id = $booking_id;
  $transaction_id = "";
  $payment_amount = 0;
  $payment_date = $deleted_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Package Sales');
  $old_gl_id = $gl_id = 36;
  $payment_side = "Debit";
  $clearance_status = "";
  $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    
    ////////Customer Amount//////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = $cust_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    
    $sq_delete = mysqlQuery("update package_tour_booking_master set total_hotel_expense = '0',service_charge='0',subtotal='0',tour_service_tax='', tour_service_tax_subtotal='', net_total='0', roundoff='0',subtotal_with_rue='0',actual_tour_expense='0',total_travel_expense='0',cruise_expense='0',plane_expense='0',train_expense='0',total_cruise_expense='0',total_plane_expense='0',total_train_expense='0',basic_amount='0',delete_status='1',tcs_tax='0',tcs_per='0' where booking_id='$booking_id'");
    if($sq_delete){
      echo 'Entry deleted successfully!';
      exit;
    }
}
public function payment_finance_save($booking_id, $payment_id, $branch_admin_id, $payment_date, $payment_mode, $payment_amount, $transaction_id1,$bank_id, $clearance_status,$credit_charges_arr,$credit_card_details_arr){

  $row_spec='sales';
  global $transaction_master;
  
  $payment_amount1 = floatval($payment_amount)+floatval($credit_charges_arr);
  $customer_id = $_POST['customer_id'];
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
    $pay_gl = $sq_bank['ledger_id'];
    $type='BANK RECEIPT';
  }
  if($payment_mode != 'Credit Note'){
		
		if($payment_mode == 'Credit Card'){

			//////Customer Credit charges///////
			$module_name = "Package Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_charges_arr;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_package_booking_id($booking_id,$yr1), $payment_date, $credit_charges_arr, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $cust_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Credit charges ledger///////
			$module_name = "Package Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_charges_arr;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_package_booking_id($booking_id,$yr1), $payment_date, $credit_charges_arr, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = 224;
			$payment_side = "Credit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Get Credit card company Ledger///////
			$credit_card_details_arr1 = explode('-',$credit_card_details_arr);
      $entry_id = $credit_card_details_arr1[0];
      
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
			$module_name = "Package Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $finance_charges;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_package_booking_id($booking_id,$yr1), $payment_date, $finance_charges, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = 231;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
			//////Credit company amount///////
			$module_name = "Package Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_company_amount;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_package_booking_id($booking_id,$yr1), $payment_date, $credit_company_amount, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $company_gl;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
		else{
        //////Payment Amount///////
        $module_name = "Package Booking Payment";
        $module_entry_id = $payment_id;
        $transaction_id = $transaction_id1;
        $payment_amount = $payment_amount1;
        $payment_date = $payment_date;
        $payment_particular = get_sales_paid_particular(get_package_booking_id($booking_id,$yr1), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
        $ledger_particular = get_ledger_particular('By','Cash/Bank');
        $gl_id = $pay_gl;
        $payment_side = "Debit";
        $clearance_status = ($payment_mode=="Cheque" ||$payment_mode=="Credit Card") ? "Pending" : "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
    }
    
        //////Customer Payment Amount///////
        $module_name = "Package Booking Payment";
        $module_entry_id = $payment_id;
        $transaction_id = $transaction_id1;
        $payment_amount = $payment_amount1;
        $payment_date = $payment_date;
        $payment_particular = get_sales_paid_particular(get_package_booking_id($booking_id,$yr1), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1);
        $ledger_particular = get_ledger_particular('By','Cash/Bank');
        $gl_id = $cust_gl;
        $payment_side = "Credit";
        $clearance_status = ($payment_mode=="Cheque" ||$payment_mode=="Credit Card") ? "Pending" : "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
    }
}

public function bank_cash_book_save($booking_id, $payment_id, $payment_date, $payment_mode, $payment_amount1, $transaction_id, $bank_name, $bank_id, $branch_admin_id,$credit_charges_arr,$credit_card_details_arr)
{

    global $bank_cash_book_master;

    

    $customer_id = $_POST['customer_id'];
    $payment_date = get_date_db($payment_date);
    $year1 = explode("-", $payment_date);
    $yr1 =$year1[0];


    if($payment_mode == 'Credit Card')
    {
      $payment_amount1 = $payment_amount1 + $credit_charges_arr;
      $credit_card_details = explode('-',$credit_card_details_arr);
      $entry_id = $credit_card_details[0];
      
      $sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
      $bank_id = $sq_credit_charges['bank_id'];
  
    }


    $module_name = "Package Booking Payment";

    $module_entry_id = $payment_id;

    $payment_date = $payment_date;

    $payment_amount = $payment_amount1;

    $payment_mode = $payment_mode;

    $bank_name = $bank_name;

    $transaction_id = $transaction_id;

    $bank_id = $bank_id;

    $particular = get_sales_paid_particular(get_package_booking_id($booking_id,$yr1), $payment_date, $payment_amount, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id);
    $clearance_status = ($payment_mode=="Cheque" || $payment_mode=="Credit Card") ? "Pending" : "";

    $payment_side = "Debit";

    $payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";



    $bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);

    

}



}

?>
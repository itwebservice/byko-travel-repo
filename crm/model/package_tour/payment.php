<?php
$flag = true;
class payment{
/////////////////***** Package Tour payment master save start *********////////////////////////////////////
public function package_tour_payment_master_save()
{
  $booking_id = $_POST['booking_id'];
  $payment_date = $_POST['payment_date'];
  $payment_mode = $_POST['payment_mode'];
  $payment_amount = $_POST['payment_amount'];
  $bank_name = $_POST['bank_name'];
  $transaction_id = $_POST['transaction_id'];
  $payment_for = isset($_POST['payment_for']) ? $_POST['payment_for'] : '';
  $p_travel_type = isset($_POST['p_travel_type']) ? $_POST['p_travel_type'] : '';
  $bank_id = $_POST['bank_id'];
  $emp_id = $_POST['emp_id'];
  $branch_admin_id = $_POST['branch_admin_id'];
  $credit_charges = $_POST['credit_charges'];
  $credit_card_details = $_POST['credit_card_details'];
  $payment_date = date("Y-m-d", strtotime($payment_date));
  $clearance_status = ($payment_mode=="Cheque" || $payment_mode=="Credit Card") ? "Pending" : "";
  $financial_year_id = $_SESSION['financial_year_id'];
  $canc_status = $_POST['canc_status'];
  
  begin_t();

  $sq = mysqlQuery("SELECT max(payment_id) as max FROM package_payment_master");
  $value = mysqli_fetch_assoc($sq);
  $max_payment_id = $value['max'] + 1;

  $sq = mysqlQuery(" insert into package_payment_master (payment_id, booking_id, financial_year_id, branch_admin_id, emp_id, date, payment_mode, amount, bank_name, transaction_id, payment_for, travel_type, bank_id, clearance_status,credit_charges,credit_card_details ,status) values ('$max_payment_id', '$booking_id', '$financial_year_id', '$branch_admin_id', '$emp_id', '$payment_date', '$payment_mode', '$payment_amount', '$bank_name', '$transaction_id', '$payment_for', '$p_travel_type', '$bank_id', '$clearance_status','$credit_charges','$credit_card_details','$canc_status') ");

  if(!$sq){
    rollback_t();
    echo "Error for payment information save.";
    exit;
  }
  else{

    $booking_save = new booking_save();
    $booking_save->package_receipt_master_save( $booking_id, $max_payment_id, $payment_for);

    if($payment_mode != 'Credit Note'){
      //Finance Save
      $this->payment_finance_save($max_payment_id); 
      //Bank and Cash Book Save
      $this->bank_cash_book_save($max_payment_id);
    }
    if($GLOBALS['flag']){

      commit_t();
      //Payment email notification
      $this->payment_email_notification_send($booking_id, $payment_amount, $payment_mode, $payment_date);
      //Payment sms notification send
      $sq_c = mysqli_fetch_assoc(mysqlQuery("SELECT customer_id FROM package_tour_booking_master where booking_id='$booking_id'"));
      if($payment_amount !=0 ){
        $this->payment_sms_notification_send($booking_id, $payment_amount, $payment_mode,$sq_c['customer_id']);
      }
      echo "Payment has been successfully saved.";
    }
    else{
      rollback_t();
      echo "error--Payment not saved!";  
    }
	}
}

public function payment_finance_save($payment_id)
{
    $row_spec = 'sales';
    $booking_id = $_POST['booking_id'];
    $payment_date1 = $_POST['payment_date'];
    $payment_mode = $_POST['payment_mode'];
    $payment_amount1 = $_POST['payment_amount'];
    $transaction_id1 = $_POST['transaction_id'];
    $bank_id1 = $_POST['bank_id'];
    $credit_charges = $_POST['credit_charges'];
    $credit_card_details = $_POST['credit_card_details'];
    $branch_admin_id = $_POST['branch_admin_id'];
    $canc_status = $_POST['canc_status'];
  
    $payment_date = date('Y-m-d', strtotime($payment_date1));
    $year1 = explode("-", $payment_date);
    $yr1 =$year1[0];

    $sq_group_info = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
    $customer_id = $sq_group_info['customer_id'];  
    global $transaction_master;


    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
    else{ 
      $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id1' and user_type='bank'"));
      $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : 0;
      $type='BANK RECEIPT';
    } 
    $payment_amount1 = floatval($payment_amount1) + floatval($credit_charges);
     //Getting customer Ledger
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
    $cust_gl = $sq_cust['ledger_id'];

    if($payment_mode != 'Credit Note'){
      if($payment_mode == 'Credit Card'){

      //////Customer Credit charges///////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = $credit_charges;
      $payment_date = $payment_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($booking_id,$yr1), $payment_date, $credit_charges, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id1,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $gl_id = $cust_gl;
      $payment_side = "Debit";
      $clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
  
      //////Credit charges ledger///////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = $credit_charges;
      $payment_date = $payment_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($booking_id,$yr1), $payment_date1, $credit_charges, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id1,$transaction_id1,$canc_status);
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
  
      //////Finance charges ledger///////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = $finance_charges;
      $payment_date = $payment_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($booking_id,$yr1), $payment_date1, $finance_charges, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id1,$transaction_id1,$canc_status);
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
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($booking_id,$yr1), $payment_date, $credit_company_amount, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id1,$transaction_id1,$canc_status);
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
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($booking_id,$yr1), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id1,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $gl_id = $pay_gl;
      $payment_side = "Debit";
      $clearance_status = ($payment_mode!="Cash") ? "Pending" : "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
    }
      ////////Customer Amount//////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = "";
      $payment_amount = $payment_amount1;
      $payment_date = $payment_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($booking_id,$yr1), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id1,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $gl_id = $cust_gl;
      $payment_side = "Credit";
      $clearance_status = "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
  }
}
function package_tour_payment_master_delete(){
  
  global $transaction_master,$bank_cash_book_master,$delete_master;
  $row_spec = 'sales';
  $payment_id = $_POST['payment_id'];
  $deleted_date = date('Y-m-d');

  $sq_package_payment = mysqli_fetch_assoc(mysqlQuery("select * from package_payment_master where payment_id='$payment_id'"));
  $booking_id = $sq_package_payment['booking_id'];
  $credit_charges = $sq_package_payment['credit_charges'];
  $credit_card_details = $sq_package_payment['credit_card_details'];
  $payment_mode = $sq_package_payment['payment_mode'];
  $payment_amount = $sq_package_payment['amount'];
  $bank_id = $sq_package_payment['bank_id'];
  $bank_name = $sq_package_payment['bank_name'];
  $transaction_id1 = $sq_package_payment['transaction_id'];
  $payment_date1 = isset($sq_package_payment['payment_date']) ? $sq_package_payment['payment_date'] : ''; 
  $canc_status = $sq_package_payment['status'];
  $sq_package_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
  $customer_id = $sq_package_booking['customer_id'];
  $booking_date = $sq_package_booking['booking_date'];
  
	$year2 = explode("-", $payment_date1);
  $yr2 = $year2[0];
	$year1 = explode("-", $booking_date);
  $yr1 = $year1[0];

  $sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
  if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
    $cust_name = $sq_ct['company_name'];
  }else{
    $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
  }

  $trans_id = get_package_booking_payment_id($payment_id,$yr2).' : '.$cust_name;
  $transaction_master->updated_entries('Package Tour Receipt',$booking_id,$trans_id,$payment_amount,0);

  //Getting customer Ledger
  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
  $cust_gl = $sq_cust['ledger_id'];
  
  //Getting cash/Bank Ledger
  if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
  else{ 
    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id']:0;
    $type='BANK RECEIPT';
  }

  $payment_amount1 = floatval($payment_amount) + floatval($credit_charges);

  $delete_master->delete_master_entries('Receipt('.$payment_mode.')','Package Tour Receipt',$payment_id,get_package_booking_payment_id($payment_id,$yr2),$cust_name,$payment_amount);
  //////////Payment Amount///////////
  if($payment_mode != 'Credit Note'){
    if($payment_mode == 'Credit Card'){

      //////Customer Credit charges///////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = 0;
      $payment_date = $deleted_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $deleted_date, $credit_charges, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $old_gl_id = $gl_id = $cust_gl;
      $payment_side = "Debit";
      $clearance_status = ($payment_mode=="Cheque" || $payment_mode=="Credit Card") ? "Pending" : "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);
  
      //////Credit charges ledger///////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = 0;
      $payment_date = $deleted_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $deleted_date, $credit_charges, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $old_gl_id = $gl_id = 224;
      $payment_side = "Credit";
      $clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);
  
      //////Get Credit card company Ledger///////
      $credit_card_details = explode('-',$credit_card_details);
      $entry_id = $credit_card_details[0];
      $sq_cust1 = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$entry_id' and user_type='credit company'"));
      $company_gl = $sq_cust1['ledger_id'];
  
      //////Finance charges ledger///////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = 0;
      $payment_date = $deleted_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $deleted_date, 0, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $old_gl_id = $gl_id = 231;
      $payment_side = "Debit";
      $clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);

      //////Credit company amount///////
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = 0;
      $payment_date = $deleted_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $deleted_date, 0, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $old_gl_id = $gl_id = $company_gl;
      $payment_side = "Debit";
      $clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);
    }
    else{
    
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = 0;
      $payment_date = $deleted_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr1), $deleted_date, $payment_amount1, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $old_gl_id = $gl_id = $pay_gl;
      $payment_side = "Debit";
      $clearance_status = ($payment_mode=="Cheque" || $payment_mode == 'Credit Card') ? "Pending" : "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);
    }
    ////////Customer Amount//////
    $module_name = "Package Booking Payment";
    $module_entry_id = $payment_id;
    $transaction_id = $transaction_id1;
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($booking_id,$yr1), $deleted_date, $payment_amount1, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id1,$canc_status);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $old_gl_id = $gl_id = $cust_gl;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);
  }
  
  //bank cash book
  $module_name = "Package Booking Payment";
  $module_entry_id = $payment_id;
  $payment_date = $payment_date;
  $payment_amount = $payment_amount;
  $payment_mode = $payment_mode;
  $bank_name = $bank_name;
  $transaction_id = $transaction_id;
  $bank_id = $bank_id;
  $particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr1), $payment_date, $payment_amount, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id,$canc_status);
  $clearance_status = $clearance_status;
  $payment_side = "Debit";
  $payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

  $bank_cash_book_master->bank_cash_book_master_update($module_name, $payment_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);

  $sq_delete = mysqlQuery("update package_payment_master set amount = '0', delete_status='1',credit_charges='0' where payment_id='$payment_id'");
  if($sq_delete){
    echo 'Entry deleted successfully!';
    exit;
  }
}
public function bank_cash_book_save($payment_id)
{
  global $bank_cash_book_master;

  $booking_id = $_POST['booking_id'];
  $payment_date = $_POST['payment_date'];
  $payment_mode = $_POST['payment_mode'];
  $payment_amount = $_POST['payment_amount'];
  $bank_name = $_POST['bank_name'];
  $transaction_id = $_POST['transaction_id'];
  $bank_id = $_POST['bank_id'];
  $canc_status = $_POST['canc_status'];
  $payment_date = date("Y-m-d", strtotime($payment_date));
  $year1 = explode("-", $payment_date);
  $yr1 =$year1[0];

  $sq_booking_info = mysqli_fetch_assoc(mysqlQuery("select customer_id from package_tour_booking_master where booking_id='$booking_id'"));    
  $credit_charges = isset($_POST['credit_charges']) ? $_POST['credit_charges'] : 0;
	$credit_card_details = isset($_POST['credit_card_details']) ? $_POST['credit_card_details'] : '';

	if($payment_mode == 'Credit Card'){

		$payment_amount = $payment_amount + $credit_charges;
		$credit_card_details = explode('-',$credit_card_details);
		$entry_id = $credit_card_details[0];
		$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
		$bank_id = $sq_credit_charges['bank_id'];
  }
  
  $module_name = "Package Booking Payment";
  $module_entry_id = $payment_id;
  $payment_date = $payment_date;
  $payment_amount = $payment_amount;
  $payment_mode = $payment_mode;
  $bank_name = $bank_name;
  $transaction_id = $transaction_id;
  $bank_id = $bank_id;
  $particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr1), $payment_date, $payment_amount, $sq_booking_info['customer_id'], $payment_mode, get_package_booking_id($booking_id,$yr1),$bank_id,$transaction_id,$canc_status);
  $clearance_status = ($payment_mode=="Cheque" ||$payment_mode=="Credit Card")  ? "Pending" : "";
  $payment_side = "Debit";
  $payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

  $bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);
  
}
/////////////***** Package Tour payment master save end /////

////////////***** Package Tour Payment Master update start//////

function package_tour_payment_master_update()
{
  $payment_id = $_POST['payment_id'];
  $booking_id = $_POST['booking_id'];
  $payment_date = $_POST['payment_date'];
  $payment_mode = $_POST['payment_mode'];
  $payment_amount = $_POST['payment_amount'];
  $bank_name = $_POST['bank_name'];
  $transaction_id = $_POST['transaction_id'];
  $payment_for = isset($_POST['payment_for'])? $_POST['payment_for'] : '';
  $p_travel_type = isset($_POST['p_travel_type']) ? $_POST['p_travel_type'] : '';
  $bank_id = $_POST['bank_id']; 
  $payment_old_value = isset($_POST['payment_old_value']) ? $_POST['payment_old_value'] : 0; 

	$credit_charges = isset($_POST['credit_charges']) ? $_POST['credit_charges'] : 0;
  $payment_date = date("Y-m-d", strtotime($payment_date));

  $financial_year_id = $_SESSION['financial_year_id'];
  $sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from package_payment_master where payment_id='$payment_id'"));
  $clearance_status = $sq_payment_info['clearance_status'];
  if($payment_mode=="Cash"){ $clearance_status = ""; }
  
  begin_t();
  $sq_payment = mysqlQuery("update package_payment_master set financial_year_id='$financial_year_id', booking_id='$booking_id', date='$payment_date', payment_mode='$payment_mode', amount='$payment_amount', bank_name='$bank_name', transaction_id='$transaction_id', payment_for='$payment_for', travel_type='$p_travel_type', bank_id='$bank_id', clearance_status='$clearance_status',credit_charges='$credit_charges' where payment_id='$payment_id' ");

	global $transaction_master;
	if(floatval($payment_old_value) != floatval($payment_amount)){

    $yr = explode("-", $payment_date);
    $year = $yr[0];
		$sq_package = mysqli_fetch_assoc(mysqlQuery("select customer_id from package_tour_booking_master where booking_id='$booking_id'"));
		$sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_package[customer_id]'"));
    if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
        $cust_name = $sq_ct['company_name'];
    }else{
        $cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
    }

		$trans_id = get_package_booking_payment_id($payment_id,$year).' : '.$cust_name;
		$transaction_master->updated_entries('Package Tour Receipt',$booking_id,$trans_id,$payment_old_value,$payment_amount);
	}

  if(!$sq_payment)
  {
    rollback_t();
    echo "error--Details not updated.";
    exit;
  }  
  else
  {
    $sq_receipt = mysqlQuery("update package_receipt_master set receipt_of='$payment_for' where payment_id='$payment_id'");
    if(!$sq_receipt)
    {
      $GLOBALS['flag'] = false;
      echo "error--Receipt details not updated.";
    }

		if($payment_mode != 'Credit Note' && $payment_mode != 'Advance'){
      //Finance Update
      $this->finance_update($sq_payment_info, $clearance_status);

      //Bank and Cash Book update
      $this->bank_cash_book_update($sq_payment_info, $clearance_status);
    }

    if($GLOBALS['flag']){
      commit_t();

      //Payment email notification
      $this->payment_update_email_notification_send($payment_id);

      echo "Payment updated!";  
      exit;
    }
  }  
}

function finance_update($sq_payment_info, $clearance_status1)
{
    $row_spec ='sales';
    $payment_id = $_POST['payment_id'];
    $booking_id = $_POST['booking_id'];
    $payment_date = $_POST['payment_date'];
    $payment_mode = $_POST['payment_mode'];
    $payment_amount1 = $_POST['payment_amount'];
    $bank_name = $_POST['bank_name'];
    $transaction_id1 = $_POST['transaction_id'];
    $bank_id = $_POST['bank_id']; 
    $credit_card_details = isset($_POST['credit_card_details']) ? $_POST['credit_card_details'] : '';
    $credit_charges_old = isset($_POST['credit_charges_old']) ?  $_POST['credit_charges_old'] : 0;
    $branch_admin_id = isset($_POST['branch_admin_id']) ? $_POST['branch_admin_id'] : '';
    $canc_status = isset($_POST['canc_status'])  ?$_POST['canc_status'] : '';
    $payment_old_value = $_POST['payment_old_value']; 

    $payment_date = date('Y-m-d', strtotime($payment_date));
    $year1 = explode("-", $payment_date);
    $yr2 =$year1[0];

    $sq_group_info = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
    $customer_id = $sq_group_info['customer_id'];  
    global $transaction_master;

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
    else{ 
      $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
      $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : 0;
      $type='BANK RECEIPT';
      } 

      //Getting customer Ledger
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
    $cust_gl = $sq_cust['ledger_id'];
    if($payment_amount1 != $payment_old_value){
      //////////Payment Amount///////////
      if($payment_mode != 'Credit Note'){
        if($payment_mode == 'Credit Card'){

        $payment_old_value = $payment_old_value + $credit_charges_old;
        //////Customer Credit charges///////
        $module_name = "Package Booking Payment";
        $module_entry_id = $payment_id;
        $transaction_id = $transaction_id1;
        $payment_amount = $credit_charges_old;
        $payment_date = $payment_date;
        $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $payment_date, $credit_charges_old, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr2),$bank_id,$transaction_id1,$canc_status);
        $ledger_particular = get_ledger_particular('By','Cash/Bank');
        $gl_id = $cust_gl;
        $payment_side = "Credit";
        $clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
  
        //////Credit charges ledger///////
        $module_name = "Package Booking Payment";
        $module_entry_id = $payment_id;
        $transaction_id = $transaction_id1;
        $payment_amount = $credit_charges_old;
        $payment_date = $payment_date;
        $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $payment_date, $credit_charges_old, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr2),$bank_id,$transaction_id1,$canc_status);
        $ledger_particular = get_ledger_particular('By','Cash/Bank');
        $gl_id = 224;
        $payment_side = "Debit";
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
        $company_card_charges = ($sq_credit_charges['charges_in'] =='Flat') ? $sq_credit_charges['credit_card_charges'] : ($payment_old_value * ($sq_credit_charges['credit_card_charges']/100));
        //////company's tax on credit card charges
        $tax_charges = ($sq_credit_charges['tax_charges_in'] =='Flat') ? $sq_credit_charges['tax_on_credit_card_charges'] : ($company_card_charges * ($sq_credit_charges['tax_on_credit_card_charges']/100));
        $finance_charges = $company_card_charges + $tax_charges;
        $finance_charges = number_format($finance_charges,2);
        $credit_company_amount = $payment_old_value - $finance_charges;
  
        //////Finance charges ledger///////
        $module_name = "Package Booking Payment";
        $module_entry_id = $payment_id;
        $transaction_id = $transaction_id1;
        $payment_amount = $finance_charges;
        $payment_date = $payment_date;
        $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $payment_date, $finance_charges, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr2),$bank_id,$transaction_id1,$canc_status);
        $ledger_particular = get_ledger_particular('By','Cash/Bank');
        $gl_id = 231;
        $payment_side = "Credit";
        $clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
  
        //////Credit company amount///////
        $module_name = "Package Booking Payment";
        $module_entry_id = $payment_id;
        $transaction_id = $transaction_id1;
        $payment_amount = $credit_company_amount;
        $payment_date = $payment_date;
        $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $payment_date, $credit_company_amount, $customer_id, $payment_mode, get_package_booking_id($booking_id,$yr2),$bank_id,$transaction_id1,$canc_status);
        $ledger_particular = get_ledger_particular('By','Cash/Bank');
        $gl_id = $company_gl;
        $payment_side = "Credit";
        $clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
      }
      else{
  
        $module_name = "Package Booking Payment";
        $module_entry_id = $payment_id;
        $transaction_id = $transaction_id1;
        $payment_amount = $payment_old_value;
        $payment_date = $payment_date;
        $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $payment_date, $payment_old_value, $sq_group_info['customer_id'], $payment_mode, get_package_booking_id($booking_id,$yr2),$bank_id,$transaction_id1,$canc_status);
        $ledger_particular = get_ledger_particular('By','Cash/Bank');
        $gl_id = $pay_gl;
        $payment_side = "Credit";
        $clearance_status = ($payment_mode=="Cheque" || $payment_mode == 'Credit Card') ? "Pending" : "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
      }
  
      $module_name = "Package Booking Payment";
      $module_entry_id = $payment_id;
      $transaction_id = $transaction_id1;
      $payment_amount = $payment_old_value;
      $payment_date = $payment_date;
      $payment_particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr2), $payment_date, $payment_old_value, $sq_group_info['customer_id'], $payment_mode, get_package_booking_id($booking_id,$yr2),$bank_id,$transaction_id1,$canc_status);
      $ledger_particular = get_ledger_particular('By','Cash/Bank');
      $gl_id = $cust_gl;
      $payment_side = "Debit";
      $clearance_status = "";
      $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
    }
  }
}


public function bank_cash_book_update($sq_payment_info, $clearance_status)
{
  global $bank_cash_book_master;

  $payment_id = $_POST['payment_id'];
  $payment_date = $_POST['payment_date'];
  $payment_mode = $_POST['payment_mode'];
  $payment_amount = $_POST['payment_amount'];
  $bank_name = $_POST['bank_name'];
  $transaction_id = $_POST['transaction_id'];
  $bank_id = $_POST['bank_id'];
  $canc_status = $_POST['canc_status'];

  $payment_date = date("Y-m-d", strtotime($payment_date));
	$year1 = explode("-", $payment_date);
	$yr1 =$year1[0];

	$credit_charges = isset($_POST['credit_charges']) ? $_POST['credit_charges'] : 0;
	$credit_card_details = isset($_POST['credit_card_details']) ? $_POST['credit_card_details'] : '';
  $sq_booking_info = mysqli_fetch_assoc(mysqlQuery("select customer_id from package_tour_booking_master where booking_id='$sq_payment_info[booking_id]'"));    
  if($payment_mode == 'Credit Card'){

		$payment_amount = $payment_amount + $credit_charges;
		$credit_card_details = explode('-',$credit_card_details);
		$entry_id = $credit_card_details[0];
		$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
		$bank_id = $sq_credit_charges['bank_id'];
	}
  $module_name = "Package Booking Payment";
  $module_entry_id = $payment_id;
  $payment_date = $payment_date;
  $payment_amount = $payment_amount;
  $payment_mode = $payment_mode;
  $bank_name = $bank_name;
  $transaction_id = $transaction_id;
  $bank_id = $bank_id;
  $particular = get_sales_paid_particular(get_package_booking_payment_id($payment_id,$yr1), $payment_date, $payment_amount, $sq_booking_info['customer_id'], $payment_mode, get_package_booking_id($sq_payment_info['booking_id'],$yr1),$bank_id,$transaction_id,$canc_status);
  $clearance_status = $clearance_status;
  $payment_side = "Debit";
  $payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

  $bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);
}

//////////////////***** Package Tour Payment Master update end /////////////


////////////////**Payment email notification send start**/////////////////
public function payment_email_notification_send($booking_id, $payment_amount, $payment_mode, $payment_date){

    $sq_total_paid = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum,sum(`credit_charges`) as sumc from package_payment_master where booking_id='$booking_id' and clearance_status!='Cancelled'"));

    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
    $date = $sq_booking['booking_date'];
    $yr = explode("-", $date);
    $year =$yr[0];
    $credit_card_amount = $sq_total_paid['sumc'];
    $total_amount = $sq_booking['net_total']+$credit_card_amount;
    $paid_amount = $sq_total_paid['sum']+$credit_card_amount;
    
    $pass_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id'"));
    $cancle_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id' and status='Cancel'"));
    if($pass_count == $cancle_count){
      $sq_esti = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$booking_id'"));
      $canc_amount = $sq_esti['cancel_amount'];
      $outstanding = ($paid_amount > $canc_amount) ? 0 : (floatval($canc_amount) - floatval($paid_amount) + $credit_card_amount);
    }else{
      $outstanding =  $total_amount - $paid_amount;
    }
    
    $sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));
    $customer_name = ($sq_customer_info['type'] == 'Corporate' || $sq_customer_info['type'] == 'B2B') ? $sq_customer_info['company_name'] : $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];

    $due_date = ($sq_booking['due_date'] == '1970-01-01') ? '' : $sq_booking['due_date'];
    $subject = 'Payment Acknowledgement (Booking ID : '.get_package_booking_id($booking_id,$year).' )';
    global $model;
    $model->generic_payment_mail('45',$payment_amount, $payment_mode, $total_amount, $paid_amount, $payment_date, $due_date,$sq_booking['email_id'], $subject,$customer_name,$sq_booking['currency_code'],$outstanding);
}
 //////////////////////////////////**Payment email notification send end**/////////////////////////////////////

//////////////////////////////////**Payment email notification send start**/////////////////////////////////////
public function payment_update_email_notification_send($payment_id)
{
    $sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from package_payment_master where payment_id='$payment_id' and clearance_status!='Cancelled'"));
    $payment_amount = $sq_payment_info['amount'];
    $payment_mode = $sq_payment_info['payment_mode'];
    $payment_date = $sq_payment_info['date'];
    $booking_id = $sq_payment_info['booking_id'];

    $sq_total_paid = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum,sum(`credit_charges`) as sumc from package_payment_master where booking_id='$booking_id' and clearance_status!='Cancelled'"));
    $paid_amount = $sq_total_paid['sum'];

    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
    $cust_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));
    $customer_name = ($cust_info['type'] == 'Corporate' || $cust_info['type'] == 'B2B') ? $cust_info['company_name'] : $cust_info['first_name'].' '.$cust_info['last_name'];
    
    $credit_card_amount = $sq_total_paid['sumc'];
    $total_amount = $sq_booking['net_total']+$credit_card_amount;
    $paid_amount = $sq_total_paid['sum']+$credit_card_amount;
    $email_id = $sq_booking['email_id'];

    $date = $sq_booking['booking_date'];
    $yr = explode("-", $date);
    $year =$yr[0];
    $due_date = ($sq_booking['due_date'] == '1970-01-01') ? '' : $sq_booking['due_date'];

    $payment_id = get_package_booking_payment_id($payment_id,$year);
    $subject = 'Package Booking Payment Correction (Booking ID : '.get_package_booking_id($booking_id,$year).' )';
    global $model;
    $model->generic_payment_mail('56',$payment_amount, $payment_mode, $total_amount, $paid_amount, $payment_date, $due_date,$email_id,$subject, $customer_name,$sq_booking['currency_code']);
}
//////////////////////////////////**Payment email notification send end**/////////////////////////////////////

//////////////////////////////////**Payment sms notification send start**/////////////////////////////////////
public function payment_sms_notification_send($booking_id, $payment_amount, $payment_mode,$customer_id){
    
  global $app_name,$model,$currency;
    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
    $fname = $sq_customer['first_name'];
    $lname = $sq_customer['last_name'];
    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
    $mobile_no = $sq_booking['mobile_no'];
    $sq_currency = mysqli_fetch_assoc(mysqlQuery("select * from currency_name_master where id='$currency'"));
    $currency_code = $sq_currency['currency_code'];
    $message = "Dear ". $fname." ". $lname.", Acknowledge your payment of ".$payment_amount." ".$currency_code." , which we received for Package Tour installment.";

  
    $model->send_message($mobile_no, $message);
}
//////////////////////////////////**Payment sms notification send end**///////////////////////////////////// 
public function whatsapp_send(){

	global $app_contact_no,$session_emp_id,$secret_key,$encrypt_decrypt,$currency,$app_name;
  $booking_id = $_POST['booking_id'];
  
  $sq_booking_info = mysqli_fetch_assoc(mysqlQuery("SELECT total_travel_expense,actual_tour_expense,customer_id,net_total,currency_code from package_tour_booking_master where booking_id='$booking_id'"));

  $sq_total_paid = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum,sum(`credit_charges`) as sumc from package_payment_master where booking_id='$booking_id' and clearance_status!='Cancelled'"));

  $credit_card_amount = $sq_total_paid['sumc'];
  $total_amount = $sq_booking_info['net_total'] + $credit_card_amount;
  $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
  
	$pass_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id'"));
	$cancle_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id' and status='Cancel'"));
	if($pass_count == $cancle_count){
    $sq_esti = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$booking_id'"));
    $canc_amount = $sq_esti['cancel_amount'];
    $outstanding = ($paid_amount > $canc_amount) ? 0 : (floatval($canc_amount) - floatval($paid_amount)) + $credit_card_amount;
  }else{
    $outstanding =  $total_amount - $paid_amount;
  }
  $sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id= '$session_emp_id'"));
  if($session_emp_id == 0){
    $contact = $app_contact_no;
  }
  else{
    $contact = $sq_emp_info['mobile_no'];
  }

  $total_amount1 = currency_conversion($currency,$sq_booking_info['currency_code'],$total_amount);
  $paid_amount1 = currency_conversion($currency,$sq_booking_info['currency_code'],$paid_amount);
  $outstanding1 = currency_conversion($currency,$sq_booking_info['currency_code'],$outstanding);

$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id=".$sq_booking_info['customer_id']));
$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);
$customer_name = ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') ? $sq_customer['company_name'] : $sq_customer['first_name'].' '.$sq_customer['last_name'];

$whatsapp_msg = rawurlencode('Dear '.$customer_name.',
Hope you are doing great. This is to inform you that we have received your payment. We look forward to provide you a great experience.
*Total Amount* : '.$total_amount1.'
*Paid Amount* : '.$paid_amount1.'
*Balance Amount* : '.$outstanding1.'
  
Please contact for more details : '.$app_name.' '.$contact.'
Thank you.');

  $link = 'https://web.whatsapp.com/send?phone='.$contact_no.'&text='.$whatsapp_msg;
  echo $link;
  }
}
?>

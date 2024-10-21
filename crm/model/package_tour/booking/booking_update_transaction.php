<?php 
class booking_update_transaction{

public function finance_update($booking_id, $row_spec,$particular)
{
  global $transaction_master;
  $row_spec = 'sales';
  $customer_id = $_POST['customer_id'];
  $booking_date = $_POST['booking_date'];

  //**tour details
  $service_charge = $_POST['service_charge'];

  $basic_amount = $_POST['basic_amount'];
  $tour_service_tax_subtotal = $_POST['tour_service_tax_subtotal'];
  $total_travel_expense = $_POST['total_travel_expense']; 
  $actual_tour_cost = isset($_POST['actual_tour_cost']) ? $_POST['actual_tour_cost'] : 0; 
  $net_total = $_POST['net_total'];
  $tcs_tax = $_POST['tcs_tax'];
  $tds = $_POST['tds'];
  $act_discount = $_POST['act_discount'];

  //**Payment details
  $roundoff = $_POST['roundoff'];
  $booking_date = get_date_db($booking_date);
	$year1 = explode("-", $booking_date);
	$yr1 =$year1[0];
  //Getting customer Ledger
  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
  $cust_gl = $sq_cust['ledger_id'];

  $reflections = json_decode(json_encode($_POST['reflections']));
  $bsmValues = json_decode(json_encode($_POST['bsmValues']));
  foreach($bsmValues[0] as $key => $value){
      switch($key){
      case 'basic' : $basic_amount = ($value != "") ? $value : $basic_amount;break;
      case 'service' : $service_charge = ($value != "") ? $value : $service_charge;break;
      }
    }
  $total_sale_amount = $basic_amount;
  
  ////////////Sales/////////////
  $module_name = "Package Booking";
  $module_entry_id = $booking_id;
  $transaction_id = "";
  $payment_amount = $total_sale_amount;
  $payment_date = $booking_date;
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
  $payment_amount = $service_charge;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Package Sales');
  $old_gl_id = $gl_id = ($reflections[0]->hotel_sc != '') ? $reflections[0]->hotel_sc : 185;
  $payment_side = "Credit";
  $clearance_status = "";
  $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

  /////////Discount////////
  $module_name = "Package Booking";
  $module_entry_id = $booking_id;
  $transaction_id = "";
  $payment_amount = $act_discount;
  $payment_date = $booking_date;
  $payment_particular = $particular;
  $ledger_particular = get_ledger_particular('To','Package Sales');
  $old_gl_id = $gl_id = 36;
  $payment_side = "Debit";
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
    $payment_amount = $tax_amount;
    $payment_date = $booking_date;
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
    $payment_amount = $roundoff;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = 230;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    
    /////////tds/////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = 127;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');

    ////////////service charge/////////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $tcs_tax;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = 232;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    
    ////////Customer Amount//////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $net_total;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Package Sales');
    $old_gl_id = $gl_id = $cust_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    /////////Tax Amount/////////
    // tax_reflection_update('Package Booking',$tax_amount,$taxation_type,$booking_id,get_package_booking_id($booking_id,$yr1),$booking_date, $customer_id, $row_spec);

    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as amount from package_payment_master where booking_id='$booking_id'"));
    $total_tour_expense = $total_travel_expense + $actual_tour_cost;
    $balance_amount = $total_tour_expense - $sq_pay['amount'];

}

}
?>
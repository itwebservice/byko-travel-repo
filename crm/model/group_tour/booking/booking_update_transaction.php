<?php 
class booking_update_transaction
{

function finance_update($tourwise_traveler_id, $row_spec,$booking_date,$particular)
{
  global $transaction_master;
  $row_spec = 'sales';
  $customer_id = $_POST['customer_id'];

  $service_tax = $_POST['service_tax'];
  $net_total = $_POST['net_total'];  

  //**Payment details
  $basic_amount = $_POST['basic_amount'];
  $roundoff = $_POST['roundoff'];
  $total_discount = $_POST['total_discount'];
  $tcs_tax = $_POST['tcs_tax'];
  $reflections = json_decode(json_encode($_POST['reflections']));
  $bsmValues = json_decode(json_encode($_POST['bsmValues']));
    foreach($bsmValues[0] as $key => $value){
      switch($key){
      case 'basic' : $basic_amount = ($value != "") ? $value : $basic_amount;break;
      }
    }
  $booking_date = get_date_db($booking_date);

  $total_sale_amount = $basic_amount+$total_discount;
  
    ////////////Sales/////////////
    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = $total_sale_amount;
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
    $payment_amount = $total_discount;
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
    $payment_amount = $tcs_tax;
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
    $payment_amount = $roundoff;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Group Tour Sales');
    $old_gl_id = $gl_id = 230;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    //Getting customer Ledger
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
    $cust_gl = $sq_cust['ledger_id'];

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
      $old_gl_id = $gl_id = $ledger;
      $payment_side = "Credit";
      $clearance_status = "";
      $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
    }

    ////////Customer Amount//////
    $module_name = "Group Booking";
    $module_entry_id = $tourwise_traveler_id;
    $transaction_id = "";
    $payment_amount = $net_total;
    $payment_date = $booking_date;
    $payment_particular = $particular;
    $ledger_particular = get_ledger_particular('To','Group Tour Sales');
    $old_gl_id = $gl_id = $cust_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'INVOICE');
}
}
?>
<?php

$flag = true;

class booking_refund_estimate{



public function refund_estimate_update()

{
    $row_spec= 'sales';
	$booking_id = $_POST['booking_id'];  
    $cancel_amount = $_POST['cancel_amount'];
    $total_refund_amount = $_POST['total_refund_amount'];
    $tax_value = $_POST['tax_value'];
    $tour_service_tax_subtotal = $_POST['tour_service_tax_subtotal'];
    $cancel_amount_exc = $_POST['cancel_amount_exc'];

	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select customer_id, taxation_type from package_tour_booking_master where booking_id='$booking_id'"));

    $customer_id = $sq_booking['customer_id'];

	$taxation_type = $sq_booking['taxation_type'];



	begin_t();



		$created_at = date('Y-m-d H:i');



		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(estimate_id) as max from package_refund_traveler_estimate"));

		$estimate_id = $sq_max['max'] + 1;
		$sq_est = mysqlQuery("insert into package_refund_traveler_estimate(estimate_id, booking_id, cancel_amount, total_refund_amount, created_at, `tax_value`, `tax_amount`, `cancel_amount_exc`) values ('$estimate_id', '$booking_id', '$cancel_amount', '$total_refund_amount', '$created_at', '$tax_value', '$tour_service_tax_subtotal', '$cancel_amount_exc')");		

	if($sq_est){

		if($GLOBALS['flag']){

            $this->finance_save($booking_id,$row_spec);
			commit_t();

			echo "Refund Estimate has been successfully saved.";

			exit;

		}

		else{

			rollback_t();

			exit;	

		}



	}

	else{

		rollback_t();

		echo "error--Sorry, Cancellation not done!";

		exit;

	}





}


public function finance_save($booking_id,$row_spec){
        
    $booking_id = $_POST['booking_id'];
    $cancel_amount = $_POST['cancel_amount'];
    $ledger_posting = $_POST['ledger_posting'];
    $cancel_amount_exc = $_POST['cancel_amount_exc'];
    $tour_service_tax_subtotal_cancel = $_POST['tour_service_tax_subtotal'];

    $created_at = date("Y-m-d");
    $year1 = explode("-", $created_at);
    $yr1 =$year1[0];

    $sq_pck_info = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
    $customer_id = $sq_pck_info['customer_id'];
    $total_sale_amount = $sq_pck_info['basic_amount'];
    $tour_service_tax_subtotal = $sq_pck_info['tour_service_tax_subtotal'];
    $reflections = json_decode($sq_pck_info['reflections']);
    $service_charge = $sq_pck_info['service_charge'];
    $tds = $sq_pck_info['tds'];
    $discount_in = $sq_pck_info['discount_in'];
    $discount = $sq_pck_info['discount'];
    $act_discount = ($discount_in == 'Percentage') ? ($discount * floatval($service_charge) / 100 ) : $discount;

    //Getting customer Ledger
    $sq_cust1 = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
    $cust_gl = $sq_cust1['ledger_id'];
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
    $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];;
    $particular = 'Against Invoice no '.get_package_booking_id($booking_id,$yr1).' for '.$sq_pck_info['tour_name'].' for '.$cust_name.' for '.$sq_pck_info['total_tour_days'].' Nights starting from '.get_date_user($sq_pck_info['tour_from_date']);

    global $transaction_master;

    //////////Sales/////////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $total_sale_amount;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 92;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');

    /////////Service Charge////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $service_charge;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = ($reflections[0]->hotel_sc != '') ? $reflections[0]->hotel_sc : 185;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');

    /////////Discount////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $act_discount;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 36;
    $payment_side = "Credit";
    $clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,'',$ledger_particular,'INVOICE');

    /////////Service Charge Tax Amount////////
    // Eg. CGST:(9%):24.77, SGST:(9%):24.77
    $service_tax_subtotal = explode(',',$tour_service_tax_subtotal);
    $tax_ledgers = explode(',',$reflections[0]->hotel_taxes);
    for($i=0;$i<sizeof($service_tax_subtotal);$i++){

        $service_tax = explode(':',$service_tax_subtotal[$i]);
        $tax_amount = $service_tax[2];
        $ledger = isset($tax_ledgers[$i]) ? $tax_ledgers[$i] : '';

        $module_name = "Package Booking";
        $module_entry_id = $booking_id;
        $transaction_id = "";
        $payment_amount = $tax_amount;
        $payment_date = $created_at;
        $payment_particular = $particular;
        $ledger_particular = '';
        $gl_id = $ledger;
        $payment_side = "Debit";
        $clearance_status = "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');
    }

    ////////Customer Sale Amount//////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $sq_pck_info['net_total'];
    $payment_date = $created_at;
    $payment_particular =  $particular;
    $ledger_particular = '';
    $gl_id = $cust_gl;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');    

    ////Roundoff Value
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $sq_pck_info['roundoff'];
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 230;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');

    /////////TCS Charge////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $sq_pck_info['tcs_tax'];
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 232;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');
    /////////////////////////////////////////////////////////////
    $service_tax_subtotal = explode(',',$tour_service_tax_subtotal_cancel);
    $tax_ledgers = explode(',',$ledger_posting);
    for($i=0;$i<sizeof($service_tax_subtotal);$i++){

        $service_tax = explode(':',$service_tax_subtotal[$i]);
        $tax_amount = $service_tax[2];
        $ledger = isset($tax_ledgers[$i]) ? $tax_ledgers[$i] : '';

        $module_name = "Package Booking";
        $module_entry_id = $booking_id;
        $transaction_id = "";
        $payment_amount = $tax_amount;
        $payment_date = $created_at;
        $payment_particular = $particular;
        $ledger_particular = '';
        $gl_id = $ledger;
        $payment_side = "Credit";
        $clearance_status = "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');
    }

    /////////TDS Charge////////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $created_at;
    $payment_particular = $particular;
    $ledger_particular = '';
    $gl_id = 127;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,'',$ledger_particular,'REFUND');

    ////////Cancel Amount//////
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
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
    $module_name = "Package Booking";
    $module_entry_id = $booking_id;
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
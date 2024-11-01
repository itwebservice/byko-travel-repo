<?php

$flag = true;

class miscellaneous_master
{
	public function miscellaneous_master_save()
	{
		$row_spec = 'sales';
		$customer_id = $_POST['customer_id'];
		$emp_id = $_POST['emp_id'];
		$misc_issue_amount = $_POST['misc_issue_amount'];
		$branch_admin_id = $_POST['branch_admin_id'];
		$service_charge = $_POST['service_charge'];
		$markup = $_POST['markup'];
		$service_tax_markup = $_POST['service_tax_markup'];
		$service_tax_subtotal = $_POST['service_tax_subtotal'];
		$misc_total_cost = $_POST['misc_total_cost'];
		$roundoff = $_POST['roundoff'];
		$narration = $_POST['narration'];
		$services = $_POST['service'];

		$due_date = $_POST['due_date'];
		$balance_date = $_POST['balance_date'];

		$payment_date = $_POST['payment_date'];
		$payment_amount = $_POST['payment_amount'];
		$payment_mode = $_POST['payment_mode'];
		$bank_name = $_POST['bank_name'];
		$transaction_id = $_POST['transaction_id'];
		$bank_id = $_POST['bank_id'];
		$credit_charges = $_POST['credit_charges'];
		$credit_card_details = $_POST['credit_card_details'];

		$first_name_arr = isset($_POST['first_name_arr'])?$_POST['first_name_arr']:[];
		$middle_name_arr = isset($_POST['middle_name_arr']) ? $_POST['middle_name_arr']: [];
		$last_name_arr = isset($_POST['last_name_arr']) ? $_POST['last_name_arr']: [];
		$birth_date_arr = isset($_POST['birth_date_arr']) ? $_POST['birth_date_arr']: [];
		$adolescence_arr = isset($_POST['adolescence_arr']) ? $_POST['adolescence_arr']: [];
		$passport_id_arr = isset($_POST['passport_id_arr']) ? $_POST['passport_id_arr']: [];
		$issue_date_arr = isset($_POST['issue_date_arr']) ? $_POST['issue_date_arr']: [];
		$expiry_date_arr = isset($_POST['expiry_date_arr']) ? $_POST['expiry_date_arr']: [];

		$payment_date = date('Y-m-d', strtotime($payment_date));
		$balance_date = date("Y-m-d", strtotime($balance_date));
		$due_date = date("Y-m-d", strtotime($due_date));
		$reflections = json_decode(json_encode($_POST['reflections']));

		if ($payment_mode == 'Cheque' || $payment_mode == 'Credit Card') {
			$clearance_status = "Pending";
		} else {
			$clearance_status = "";
		}

		$financial_year_id = $_SESSION['financial_year_id'];
		begin_t();

		//Get Customer id
		if ($customer_id == '0') {
			$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
			$customer_id = $sq_max['max'];
		}
		$bsmValues = json_decode(json_encode($_POST['bsmValues']));
		foreach ($bsmValues[0] as $key => $value) {
			switch ($key) {
				case 'basic':
					$misc_issue_amount = ($value != "") ? $value : $misc_issue_amount;
					break;
				case 'service':
					$service_charge = ($value != "") ? $value : $service_charge;
					break;
				case 'markup':
					$markup = ($value != "") ? $value : $markup;
					break;
			}
		}
		
		//Invoice number reset to one in new financial year
		$sq_count = mysqli_num_rows(mysqlQuery("select entry_id from invoice_no_reset_master where service_name='misc' and financial_year_id='$financial_year_id'"));
		if($sq_count > 0){ // Already having bookings for this financial year
		
			$sq_invoice = mysqli_fetch_assoc(mysqlQuery("select max_booking_id from invoice_no_reset_master where service_name='misc' and financial_year_id='$financial_year_id'"));
			$invoice_pr_id = $sq_invoice['max_booking_id'] + 1;
			$sq_invoice = mysqlQuery("update invoice_no_reset_master set max_booking_id = '$invoice_pr_id' where service_name='misc' and financial_year_id='$financial_year_id'");
		}
		else{ // This financial year's first booking
		
			// Get max entry_id of invoice_no_reset_master here
			$sq_entry_id = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as entry_id from invoice_no_reset_master"));
			$max_entry_id = $sq_entry_id['entry_id'] + 1;
			
			// Insert booking-id(1) for new financial_year only for first the time
			$sq_invoice = mysqlQuery("insert into invoice_no_reset_master(entry_id ,service_name, financial_year_id ,max_booking_id) values ('$max_entry_id','misc','$financial_year_id','1')");
			$invoice_pr_id = 1;
		}

		//visa save
		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(misc_id) as max from miscellaneous_master"));
		$misc_id = $sq_max['max'] + 1;
		$reflections = json_encode($reflections);
		$bsmValues = json_encode($bsmValues);

		$sq_visa = mysqlQuery("insert into miscellaneous_master (misc_id, customer_id,branch_admin_id,financial_year_id, misc_issue_amount, service_charge, markup, service_tax_markup, service_tax_subtotal, misc_total_cost, created_at, due_date,emp_id,narration,service, reflections , roundoff , bsm_values,invoice_pr_id) values ('$misc_id', '$customer_id', '$branch_admin_id','$financial_year_id', '$misc_issue_amount', '$service_charge', '$markup', '$service_tax_markup', '$service_tax_subtotal', '$misc_total_cost', '$balance_date', '$due_date', '$emp_id','$narration','$services','$reflections' , '$roundoff' , '$bsmValues','$invoice_pr_id')");


		if (!$sq_visa) {
			rollback_t();
			echo "error--Sorry Miscellaneous information not saved successfully!";
			exit;
		} else {

			for ($i = 0; $i < sizeof($first_name_arr); $i++) {

				$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from miscellaneous_master_entries"));
				$entry_id = $sq_max['max'] + 1;

				$birth_date_arr[$i] = get_date_db($birth_date_arr[$i]);
				$issue_date_arr[$i] = get_date_db($issue_date_arr[$i]);
				$expiry_date_arr[$i] = get_date_db($expiry_date_arr[$i]);

				$sq_entry = mysqlQuery("insert into miscellaneous_master_entries(entry_id, misc_id, first_name, middle_name, last_name, birth_date, adolescence, passport_id, issue_date, expiry_date) values('$entry_id', '$misc_id', '$first_name_arr[$i]', '$middle_name_arr[$i]', '$last_name_arr[$i]', '$birth_date_arr[$i]', '$adolescence_arr[$i]',  '$passport_id_arr[$i]', '$issue_date_arr[$i]', '$expiry_date_arr[$i]')");

				if (!$sq_entry) {

					$GLOBALS['flag'] = false;
					echo "error--Some Miscellaneous entries are not saved!";
					//exit;
				}
			}


			$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from miscellaneous_payment_master"));
			$payment_id = $sq_max['max'] + 1;

			$sq_payment = mysqlQuery("insert into miscellaneous_payment_master (payment_id, misc_id, financial_year_id, branch_admin_id,  payment_date, payment_amount, payment_mode, bank_name, transaction_id, bank_id, clearance_status,credit_charges,credit_card_details) values ('$payment_id', '$misc_id', '$financial_year_id', '$branch_admin_id', '$payment_date', '$payment_amount', '$payment_mode', '$bank_name', '$transaction_id', '$bank_id', '$clearance_status','$credit_charges','$credit_card_details') ");
			if (!$sq_payment) {
				$GLOBALS['flag'] = false;
				echo "error--Sorry, Payment not saved!";
			}

			//Update customer credit note balance
			$payment_amount1 = $payment_amount;
			if($payment_mode=='Credit Note'){
			$sq_credit_note = mysqlQuery("select * from credit_note_master where customer_id='$customer_id'");
			$i = 0;
			while ($row_credit = mysqli_fetch_assoc($sq_credit_note)) {
				if ($row_credit['payment_amount'] <= $payment_amount1 && $payment_amount1 != '0') {
					$payment_amount1 = $payment_amount1 - $row_credit['payment_amount'];
					$temp_amount = 0;
				} else {
					$temp_amount = $row_credit['payment_amount'] - $payment_amount1;
					$payment_amount1 = 0;
				}
				$sq_credit = mysqlQuery("update credit_note_master set payment_amount ='$temp_amount' where id='$row_credit[id]'");
			}
		}
			//Get Particular
			$particular = $this->get_particular($customer_id, $services,$misc_id);

			//Finance save
			$this->finance_save($misc_id, $payment_id, $row_spec, $branch_admin_id, $particular);

			if($payment_mode != 'Credit Note'){
				//Bank and Cash Book Save
				$this->bank_cash_book_save($misc_id, $payment_id, $branch_admin_id);
			}




			if ($GLOBALS['flag']) {



				commit_t();



				//Visa Booking email send
				$sq_cms_count = mysqli_num_rows(mysqlQuery("select * from cms_master_entries where id='11' and active_flag='Active'"));
				if ($sq_cms_count != '0') {
					$this->miscellaneous_booking_email_send($misc_id);
				}
				$this->booking_sms($misc_id, $customer_id, $balance_date);


				//Visa payment email send
				$visa_payment_master  = new miscellaneous_payment_master;

				$visa_payment_master->payment_email_notification_send($misc_id, $payment_amount, $payment_mode, $payment_date);

				//Visa payment sms send
				if ($payment_amount != 0) {
					$visa_payment_master->payment_sms_notification_send($misc_id, $payment_amount, $payment_mode, $credit_charges);
				}

				echo "Miscellaneous Booking has been successfully saved-".$misc_id;
				exit;
			} else {

				rollback_t();

				exit;
			}
		}
	}

	function get_particular($customer_id, $services,$misc_id)
	{

		$sq_misc=mysqli_fetch_assoc(mysqlQuery("select created_at from miscellaneous_master where misc_id='$misc_id'"));
		$booking_date = $sq_misc['created_at'];
		$yr = explode("-", $booking_date);
		$year = $yr[0];

		$sq_total_member=mysqli_num_rows(mysqlQuery("select misc_id from miscellaneous_master_entries where misc_id='$misc_id' and status!='Cancel' "));     
		$sq_pass=mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from miscellaneous_master_entries where misc_id='$misc_id' and status!='Cancel' "));
		$pass_name = $sq_pass['first_name'].' '.$sq_pass['last_name'];

		$particular =  get_misc_booking_id($misc_id,$year).' and '.$services . ' for ' . $pass_name.' *'.$sq_total_member;

		return $particular;
	}
	public function miscellaneous_master_delete(){

		global $delete_master,$transaction_master;
		$misc_id = $_POST['booking_id'];

		$deleted_date = date('Y-m-d');
		$row_spec = "sales";
	
		$row_misc = mysqli_fetch_assoc(mysqlQuery("select * from miscellaneous_master where misc_id='$misc_id'"));
		$reflections = json_decode($row_misc['reflections']);
		$service_tax_markup = $row_misc['service_tax_markup'];
		$service_tax_subtotal = $row_misc['service_tax_subtotal'];
		$misc_total_cost = $row_misc['misc_total_cost'];
		$customer_id = $row_misc['customer_id'];
		$services = $row_misc['service'];
		$booking_date = $row_misc['created_at'];
		$yr = explode("-", $booking_date);
		$year = $yr[0];
		
		$sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
		if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
			$cust_name = $sq_ct['company_name'];
		}else{
			$cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
		}
		$sq_total_member=mysqli_num_rows(mysqlQuery("select misc_id from miscellaneous_master_entries where misc_id='$misc_id' and status!='Cancel' "));
		$sq_pass = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from miscellaneous_master_entries where misc_id='$misc_id' and status!='Cancel' "));
		$pass_name = $sq_pass['first_name'].' '.$sq_pass['last_name'];

		$particular =  get_misc_booking_id($misc_id,$year).' and '.$services . ' for ' . $pass_name.' *'.$sq_total_member;

		global $transaction_master;

		$trans_id = get_misc_booking_id($misc_id,$year).' : '.$cust_name;
		$transaction_master->updated_entries('Miscellaneous Sale',$misc_id,$trans_id,$misc_total_cost,0);

		$delete_master->delete_master_entries('Invoice','Miscellaneous',$misc_id,get_misc_booking_id($misc_id,$year),$cust_name,$row_misc['misc_total_cost']);

		//Getting customer Ledger
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
		$cust_gl = $sq_cust['ledger_id'];

		////////////Sales/////////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = 169;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		////////////Service Charge/////////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = ($reflections[0]->misc_sc != '') ? $reflections[0]->misc_sc : 193;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		/////////Service Charge Tax Amount////////
		// Eg. CGST:(9%):24.77, SGST:(9%):24.77
		$service_tax_subtotal = explode(',', $service_tax_subtotal);
		$tax_ledgers = explode(',', $reflections[0]->misc_taxes);
		for ($i = 0; $i < sizeof($service_tax_subtotal); $i++) {

			$ledger = $tax_ledgers[$i];

			$module_name = "Miscellaneous Booking";
			$module_entry_id = $misc_id;
			$transaction_id = "";
			$payment_amount = 0;
			$payment_date = $deleted_date;
			$payment_particular = $particular;
			$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
			$old_gl_id = $gl_id = $ledger;
			$payment_side = "Credit";
			$clearance_status = "";
			$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '',  $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');
		}

		////////////markup/////////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = ($reflections[0]->misc_markup != '') ? $reflections[0]->misc_markup : 205;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		/////////Markup Tax Amount////////
		// Eg. CGST:(9%):24.77, SGST:(9%):24.77
		$service_tax_markup = explode(',', $service_tax_markup);
		$tax_ledgers = explode(',', $reflections[0]->misc_markup_taxes);
		for ($i = 0; $i < sizeof($service_tax_markup); $i++) {

			$ledger = $tax_ledgers[$i];

			$module_name = "Miscellaneous Booking";
			$module_entry_id = $misc_id;
			$transaction_id = "";
			$payment_amount = 0;
			$payment_date = $deleted_date;
			$payment_particular = $particular;
			$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
			$old_gl_id = $gl_id = $ledger;
			$payment_side = "Credit";
			$clearance_status = "";
			$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '1', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');
		}
		/////////roundoff/////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = 230;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		////////Customer Amount//////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = $cust_gl;
		$payment_side = "Debit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');
		
		$sq_delete = mysqlQuery("update miscellaneous_master set misc_issue_amount = '0',service_charge='0',markup='0',service_tax_markup='', service_tax_subtotal='', misc_total_cost='0', roundoff='0', delete_status='1' where misc_id='$misc_id'");
		if($sq_delete){
			echo 'Entry deleted successfully!';
			exit;
		}
	}
	public function finance_save($misc_id, $payment_id, $row_spec, $branch_admin_id, $particular)
	{

		$customer_id = $_POST['customer_id'];
		$misc_issue_amount = $_POST['misc_issue_amount'];
		$service_charge = $_POST['service_charge'];
		$markup = $_POST['markup'];
		$service_tax_markup = $_POST['service_tax_markup'];
		$service_tax_subtotal = $_POST['service_tax_subtotal'];
		$misc_total_cost = $_POST['misc_total_cost'];
		$roundoff = $_POST['roundoff'];
		$payment_date = $_POST['payment_date'];
		$payment_amount1 = $_POST['payment_amount'];
		$payment_mode = $_POST['payment_mode'];
		$transaction_id1 = $_POST['transaction_id'];
		$bank_id1 = $_POST['bank_id'];
		$booking_date = $_POST['balance_date'];
		$credit_charges = isset($_POST['credit_charges']) ? $_POST['credit_charges'] : 0;
		$credit_card_details = isset($_POST['credit_card_details']) ? $_POST['credit_card_details'] : '';

		$booking_date = date("Y-m-d", strtotime($booking_date));
		$payment_date1 = date('Y-m-d', strtotime($payment_date));
		$year1 = explode("-", $booking_date);
		$yr1 = $year1[0];
		$year2 = explode("-", $payment_date1);
		$yr2 = $year2[0];
		$bsmValues = json_decode(json_encode($_POST['bsmValues']));
		foreach ($bsmValues[0] as $key => $value) {
			switch ($key) {
				case 'basic':
					$misc_issue_amount = ($value != "") ? $value : $misc_issue_amount;
					break;
				case 'service':
					$service_charge = ($value != "") ? $value : $service_charge;
					break;
				case 'markup':
					$markup = ($value != "") ? $value : $markup;
					break;
			}
		}
		$misc_sale_amount = $misc_issue_amount;
		$payment_amount1 = intval($payment_amount1) + intval($credit_charges);

		$reflections = json_decode(json_encode($_POST['reflections']));

		//Get Customer id
		if ($customer_id == '0') {
			$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
			$customer_id = $sq_max['max'];
		}

		//Getting customer Ledger
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
		$cust_gl = $sq_cust['ledger_id'];

		//Getting cash/Bank Ledger
		if ($payment_mode == 'Cash') {
			$pay_gl = 20;
			$type = 'CASH RECEIPT';
		} else {
			$sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id1' and user_type='bank'"));
			$pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
			$type = 'BANK RECEIPT';
		}

		global $transaction_master;
		////////////Sales/////////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $misc_sale_amount;
		$payment_date = $booking_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$gl_id = 169;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, 'INVOICE');

		/////////Service Charge////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $service_charge;
		$payment_date = $booking_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$gl_id = ($reflections[0]->misc_sc != '') ? $reflections[0]->misc_sc : 193;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',  $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, 'INVOICE');

		/////////Service Charge Tax Amount////////
		// Eg. CGST:(9%):24.77, SGST:(9%):24.77
		$service_tax_subtotal = explode(',', $service_tax_subtotal);
		$tax_ledgers = explode(',', $reflections[0]->misc_taxes);
		for ($i = 0; $i < sizeof($service_tax_subtotal); $i++) {

			$service_tax = explode(':', $service_tax_subtotal[$i]);
			$tax_amount = $service_tax[2];
			$ledger = $tax_ledgers[$i];

			$module_name = "Miscellaneous Booking";
			$module_entry_id = $misc_id;
			$transaction_id = "";
			$payment_amount = $tax_amount;
			$payment_date = $booking_date;
			$payment_particular = $particular;
			$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
			$gl_id = $ledger;
			$payment_side = "Credit";
			$clearance_status = "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, 'INVOICE');
		}

		///////////Markup//////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $markup;
		$payment_date = $booking_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$gl_id = ($reflections[0]->misc_markup != '') ? $reflections[0]->misc_markup : 205;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',  $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, 'INVOICE');

		/////////Markup Tax Amount////////
		// Eg. CGST:(9%):24.77, SGST:(9%):24.77
		$service_tax_markup = explode(',', $service_tax_markup);
		$tax_ledgers = explode(',', $reflections[0]->misc_markup_taxes);
		for ($i = 0; $i < sizeof($service_tax_markup); $i++) {

			$service_tax = explode(':', $service_tax_markup[$i]);
			$tax_amount = $service_tax[2];
			$ledger = $tax_ledgers[$i];

			$module_name = "Miscellaneous Booking";
			$module_entry_id = $misc_id;
			$transaction_id = "";
			$payment_amount = $tax_amount;
			$payment_date = $booking_date;
			$payment_particular = $particular;
			$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
			$gl_id = $ledger;
			$payment_side = "Credit";
			$clearance_status = "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '1', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, 'INVOICE');
		}

		////Roundoff Value
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $roundoff;
		$payment_date = $booking_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$gl_id = 230;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, 'INVOICE');

		////////Customer Amount//////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $misc_total_cost;
		$payment_date = $booking_date;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$gl_id = $cust_gl;
		$payment_side = "Debit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',  $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, 'INVOICE');

		//////////Payment Amount///////////
		if ($payment_mode != 'Credit Note') {

			if ($payment_mode == 'Credit Card') {

				//////Customer Credit charges///////
				$module_name = "Miscellaneous Booking Payment";
				$module_entry_id = $payment_id;
				$transaction_id = $transaction_id1;
				$payment_amount = $credit_charges;
				$payment_date = $payment_date1;
				$payment_particular = get_sales_paid_particular(get_misc_booking_id($misc_id, $yr1), $payment_date1, $credit_charges, $customer_id, $payment_mode, get_misc_booking_id($misc_id, $yr1), $bank_id1, $transaction_id1);
				$ledger_particular = get_ledger_particular('By', 'Cash/Bank');
				$gl_id = $cust_gl;
				$payment_side = "Debit";
				$clearance_status = ($payment_mode == "Cheque" || $payment_mode == "Credit Card") ? "Pending" : "";
				$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, $type);

				//////Credit charges ledger///////
				$module_name = "Miscellaneous Booking Payment";
				$module_entry_id = $payment_id;
				$transaction_id = $transaction_id1;
				$payment_amount = $credit_charges;
				$payment_date = $payment_date1;
				$payment_particular = get_sales_paid_particular(get_misc_booking_id($misc_id, $yr1), $payment_date1, $credit_charges, $customer_id, $payment_mode, get_misc_booking_id($misc_id, $yr1), $bank_id1, $transaction_id1);
				$ledger_particular = get_ledger_particular('By', 'Cash/Bank');
				$gl_id = 224;
				$payment_side = "Credit";
				$clearance_status = ($payment_mode == "Cheque" || $payment_mode == "Credit Card") ? "Pending" : "";
				$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, $type);

				//////Get Credit card company Ledger///////
				$credit_card_details = explode('-', $credit_card_details);
				$entry_id = $credit_card_details[0];
				$sq_cust1 = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$entry_id' and user_type='credit company'"));
				$company_gl = $sq_cust1['ledger_id'];
				//////Get Credit card company Charges///////
				$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select * from credit_card_company where entry_id='$entry_id'"));
				//////company's credit card charges
				$company_card_charges = ($sq_credit_charges['charges_in'] == 'Flat') ? $sq_credit_charges['credit_card_charges'] : ($payment_amount1 * ($sq_credit_charges['credit_card_charges'] / 100));
				//////company's tax on credit card charges
				$tax_charges = ($sq_credit_charges['tax_charges_in'] == 'Flat') ? $sq_credit_charges['tax_on_credit_card_charges'] : ($company_card_charges * ($sq_credit_charges['tax_on_credit_card_charges'] / 100));
				$finance_charges = intval($company_card_charges) + intval($tax_charges);
				$finance_charges = number_format($finance_charges, 2);
				$credit_company_amount = intval($payment_amount1) - intval($finance_charges);

				//////Finance charges ledger///////
				$module_name = "Miscellaneous Booking Payment";
				$module_entry_id = $payment_id;
				$transaction_id = $transaction_id1;
				$payment_amount = $finance_charges;
				$payment_date = $payment_date1;
				$payment_particular = get_sales_paid_particular(get_misc_booking_id($misc_id, $yr1), $payment_date1, $finance_charges, $customer_id, $payment_mode, get_misc_booking_id($misc_id, $yr1), $bank_id1, $transaction_id1);
				$ledger_particular = get_ledger_particular('By', 'Cash/Bank');
				$gl_id = 231;
				$payment_side = "Debit";
				$clearance_status = ($payment_mode == "Cheque" || $payment_mode == "Credit Card") ? "Pending" : "";
				$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, $type);
				//////Credit company amount///////
				$module_name = "Miscellaneous Booking Payment";
				$module_entry_id = $payment_id;
				$transaction_id = $transaction_id1;
				$payment_amount = $credit_company_amount;
				$payment_date = $payment_date1;
				$payment_particular = get_sales_paid_particular(get_misc_booking_id($misc_id, $yr1), $payment_date1, $credit_company_amount, $customer_id, $payment_mode, get_misc_booking_id($misc_id, $yr1), $bank_id1, $transaction_id1);
				$ledger_particular = get_ledger_particular('By', 'Cash/Bank');
				$gl_id = $company_gl;
				$payment_side = "Debit";
				$clearance_status = ($payment_mode == "Cheque" || $payment_mode == "Credit Card") ? "Pending" : "";
				$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, $type);
			} else {

				$module_name = "Miscellaneous Booking Payment";
				$module_entry_id = $payment_id;
				$transaction_id = $transaction_id1;
				$payment_amount = $payment_amount1;
				$payment_date = $payment_date1;
				$payment_particular = get_sales_paid_particular(get_misc_booking_id($misc_id, $yr1), $payment_date1, $payment_amount1, $customer_id, $payment_mode, get_misc_booking_id($misc_id, $yr1), $bank_id1, $transaction_id1);;
				$ledger_particular = get_ledger_particular('By', 'Cash/Bank');
				$gl_id = $pay_gl;
				$payment_side = "Debit";
				$clearance_status = ($payment_mode == "Cheque" || $payment_mode == "Credit Card") ? "Pending" : "";
				$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, $type);
			}

			//////Customer Payment Amount///////
			$module_name = "Miscellaneous Booking Payment";
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $payment_amount1;
			$payment_date = $payment_date1;
			$payment_particular = get_sales_paid_particular(get_misc_booking_id($misc_id, $yr1), $payment_date1, $payment_amount1, $customer_id, $payment_mode, get_misc_booking_id($misc_id, $yr1), $bank_id1, $transaction_id1);
			$ledger_particular = get_ledger_particular('By', 'Cash/Bank');
			$gl_id = $cust_gl;
			$payment_side = "Credit";
			$clearance_status = ($payment_mode == "Cheque" || $payment_mode == "Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '', $payment_side, $clearance_status, $row_spec, $branch_admin_id, $ledger_particular, $type);
		}
	}



	public function bank_cash_book_save($misc_id, $payment_id, $branch_admin_id)
	{
		global $bank_cash_book_master;

		$customer_id = $_POST['customer_id'];
		$payment_date = $_POST['payment_date'];
		$payment_amount = $_POST['payment_amount'];
		$payment_mode = $_POST['payment_mode'];
		$bank_name = $_POST['bank_name'];
		$transaction_id = $_POST['transaction_id'];
		$bank_id = $_POST['bank_id'];
		$credit_charges = isset($_POST['credit_charges']) ? $_POST['credit_charges'] : 0;
		$credit_card_details = isset($_POST['credit_card_details']) ? $_POST['credit_card_details'] : '';

		$sq_visa_info = mysqli_fetch_assoc(mysqlQuery("select created_at from  miscellaneous_master where misc_id='$misc_id'"));
		$created_at = $sq_visa_info['created_at'];
		$created_at = date('Y-m-d', strtotime($created_at));
		$year2 = explode("-", $created_at);
		$yr2 = $year2[0];

		if ($payment_mode == 'Credit Card') {

			$payment_amount = intval($payment_amount) + intval($credit_charges);
			$credit_card_details = explode('-', $credit_card_details);
			$entry_id = $credit_card_details[0];
			$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
			$bank_id = $sq_credit_charges['bank_id'];
		}
		$payment_date = date('Y-m-d', strtotime($payment_date));
		$year1 = explode("-", $payment_date);
		$yr1 = $year1[0];

		//Get Customer id
		if ($customer_id == '0') {
			$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
			$customer_id = $sq_max['max'];
		}

		$module_name = "Miscellaneous Booking Payment";
		$module_entry_id = $payment_id;
		$payment_date = $payment_date;
		$payment_amount = $payment_amount;
		$payment_mode = $payment_mode;
		$bank_name = $bank_name;
		$transaction_id = $transaction_id;
		$bank_id = $bank_id;
		$particular = get_sales_paid_particular(get_misc_booking_payment_id($payment_id, $yr1), $payment_date, $payment_amount, $customer_id, $payment_mode, get_misc_booking_id($misc_id, $yr2), $bank_id, $transaction_id);
		$clearance_status = ($payment_mode == "Cheque" || $payment_mode == "Credit Card") ? "Pending" : "";
		$payment_side = "Debit";
		$payment_type = ($payment_mode == "Cash") ? "Cash" : "Bank";

		$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);
	}


	public function booking_sms($booking_id, $customer_id, $created_at)
	{

		global $model, $app_name, $secret_key, $encrypt_decrypt;
		$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select contact_no from customer_master where customer_id='$customer_id'"));

		$mobile_no = $encrypt_decrypt->fnDecrypt($sq_customer_info['contact_no'], $secret_key);
		$date = $created_at;
		$yr = explode("-", $date);
		$yr1 = $yr[0];

		$message = 'Thank you for booking with ' . $app_name . '. Booking No : ' . get_misc_booking_id($booking_id, $yr1) . '  Date :' . get_date_user($created_at);

		$model->send_message($mobile_no, $message);
	}
	public function miscellaneous_master_update()
	{
		$row_spec = "sales";
		$misc_id = $_POST['misc_id'];
		$customer_id = $_POST['customer_id'];
		$misc_issue_amount = $_POST['misc_issue_amount'];
		$service_charge = $_POST['service_charge'];
		$markup = $_POST['markup'];
		$service_tax_markup = $_POST['service_tax_markup'];
		$service_tax_subtotal = $_POST['service_tax_subtotal'];
		$misc_total_cost = $_POST['misc_total_cost'];
		$bsmValues = json_decode(json_encode($_POST['bsmValues']));
		$roundoff = $_POST['roundoff'];
		$due_date1 = $_POST['due_date1'];
		$balance_date1 = $_POST['balance_date1'];
		
		$first_name_arr = isset($_POST['first_name_arr'])?$_POST['first_name_arr']:[];
		$middle_name_arr = isset($_POST['middle_name_arr']) ? $_POST['middle_name_arr']: [];
		$last_name_arr = isset($_POST['last_name_arr']) ? $_POST['last_name_arr']: [];
		$birth_date_arr = isset($_POST['birth_date_arr']) ? $_POST['birth_date_arr']: [];
		$adolescence_arr = isset($_POST['adolescence_arr']) ? $_POST['adolescence_arr']: [];
		$passport_id_arr = isset($_POST['passport_id_arr']) ? $_POST['passport_id_arr']: [];
		$issue_date_arr = isset($_POST['issue_date_arr']) ? $_POST['issue_date_arr']: [];
		$expiry_date_arr = isset($_POST['expiry_date_arr']) ? $_POST['expiry_date_arr']: [];
		$entry_id_arr = isset($_POST['entry_id_arr']) ? $_POST['entry_id_arr'] : [];
		$e_checkbox_arr = isset($_POST['e_checkbox_arr']) ? $_POST['e_checkbox_arr'] : [];
		$old_total = isset($_POST['old_total']) ? $_POST['old_total'] : 0;
		$serv = isset($_POST['service']) ? $_POST['service'] : '';
		$narration = isset($_POST['narration']) ? $_POST['narration'] : '';
		$reflections = json_decode(json_encode($_POST['reflections']));
		$sq_visa_info = mysqli_fetch_assoc(mysqlQuery("select * from miscellaneous_master where misc_id='$misc_id'"));

		$due_date1 = date('Y-m-d', strtotime($due_date1));
		$balance_date1 = date('Y-m-d', strtotime($balance_date1));
		$reflections = json_encode($reflections);
		foreach ($bsmValues[0] as $key => $value) {
			switch ($key) {
				case 'basic':
					$misc_issue_amount = ($value != "") ? $value : $misc_issue_amount;
					break;
				case 'service':
					$service_charge = ($value != "") ? $value : $service_charge;
					break;
				case 'markup':
					$markup = ($value != "") ? $value : $markup;
					break;
			}
		}
		begin_t();
		$bsmValues = json_encode($bsmValues);

		$sq_visa = mysqlQuery("UPDATE miscellaneous_master set customer_id='$customer_id', misc_issue_amount='$misc_issue_amount', service_charge='$service_charge', markup='$markup', service_tax_markup='$service_tax_markup', service_tax_subtotal='$service_tax_subtotal', misc_total_cost='$misc_total_cost', due_date='$due_date1',created_at='$balance_date1',narration='$narration',service='$serv',reflections='$reflections',bsm_values='$bsmValues' , roundoff='$roundoff'  where misc_id='$misc_id'");

		if (!$sq_visa) {

			rollback_t();

			echo "error--Sorry, Miscellaneous information not updated successfully!";

			exit;
		} else {



			for ($i = 0; $i < sizeof($first_name_arr); $i++) {


				$birth_date_arr[$i] = get_date_db($birth_date_arr[$i]);
				$issue_date_arr[$i] = get_date_db($issue_date_arr[$i]);
				$expiry_date_arr[$i] = get_date_db($expiry_date_arr[$i]);

				if($e_checkbox_arr[$i] == 'true'){
					if ($entry_id_arr[$i] == "") {

						$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from miscellaneous_master_entries"));

						$entry_id = $sq_max['max'] + 1;

						$sq_entry = mysqlQuery("insert into miscellaneous_master_entries(entry_id, misc_id, first_name, middle_name, last_name, birth_date, adolescence,passport_id, issue_date, expiry_date) values('$entry_id', '$misc_id', '$first_name_arr[$i]', '$middle_name_arr[$i]', '$last_name_arr[$i]', '$birth_date_arr[$i]', '$adolescence_arr[$i]', '$passport_id_arr[$i]', '$issue_date_arr[$i]', '$expiry_date_arr[$i]')");

						if (!$sq_entry) {

							$GLOBALS['flag'] = false;

							echo "error--Some Miscellaneous entries are not saved!";

							//exit;

						}
					} else {

						$sq_entry = mysqlQuery("update miscellaneous_master_entries set misc_id='$misc_id', first_name='$first_name_arr[$i]', middle_name='$middle_name_arr[$i]', last_name='$last_name_arr[$i]', birth_date='$birth_date_arr[$i]', adolescence='$adolescence_arr[$i]', passport_id='$passport_id_arr[$i]', issue_date='$issue_date_arr[$i]', expiry_date='$expiry_date_arr[$i]' where entry_id='$entry_id_arr[$i]'");

						if (!$sq_entry) {

							$GLOBALS['flag'] = false;

							echo "error--Some Miscellaneous entries are not updated!";

							//exit;

						}
					}
				}else{
					$sq_entry = mysqlQuery("delete from miscellaneous_master_entries where entry_id='$entry_id_arr[$i]'");
					if(!$sq_entry){
						$GLOBALS['flag'] = false;
						echo "error--Some entries not deleted!";
					}
				}
			}

			global $transaction_master;
			if(floatval($old_total) != floatval($misc_total_cost)){
		
				$yr = explode("-", $balance_date1);
				$year = $yr[0];
				$sq_ct = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
				if($sq_ct['type']=='Corporate'||$sq_ct['type'] == 'B2B'){
					$cust_name = $sq_ct['company_name'];
				}else{
					$cust_name = $sq_ct['first_name'].' '.$sq_ct['last_name'];
				}
		
				$trans_id = get_misc_booking_id($misc_id,$year).' : '.$cust_name;
				$transaction_master->updated_entries('Miscellaneous Sale',$misc_id,$trans_id,$old_total,$misc_total_cost);
			}


			//Get Particular
			$particular = $this->get_particular($customer_id, $serv,$misc_id);
			//Finance update
			$this->finance_update($sq_visa_info, $row_spec, $particular);

			if ($GLOBALS['flag']) {

				commit_t();

				echo "Miscellaneous Booking has been successfully updated.";

				exit;
			} else {

				rollback_t();

				exit;
			}
		}
	}


	public function finance_update($sq_visa_info, $row_spec, $particular)
	{
		$misc_id = $_POST['misc_id'];
		$customer_id = $_POST['customer_id'];
		$misc_issue_amount = $_POST['misc_issue_amount'];
		$service_charge = $_POST['service_charge'];
		$markup = $_POST['markup'];
		$service_tax_markup = $_POST['service_tax_markup'];
		$service_tax_subtotal = $_POST['service_tax_subtotal'];
		$misc_total_cost = $_POST['misc_total_cost'];
		$roundoff = $_POST['roundoff'];
		$balance_date1 = $_POST['balance_date1'];
		$created_at = date('Y-m-d', strtotime($balance_date1));
		$reflections = json_decode(json_encode($_POST['reflections']));
		$bsmValues = json_decode(json_encode($_POST['bsmValues']));
		foreach ($bsmValues[0] as $key => $value) {
			switch ($key) {
				case 'basic':
					$misc_issue_amount = ($value != "") ? $value : $misc_issue_amount;
					break;
				case 'service':
					$service_charge = ($value != "") ? $value : $service_charge;
					break;
				case 'markup':
					$markup = ($value != "") ? $value : $markup;
					break;
			}
		}
		global $transaction_master;

		$misc_sale_amount = $misc_issue_amount;

		//Getting customer Ledger
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
		$cust_gl = $sq_cust['ledger_id'];

		////////////Sales/////////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $misc_sale_amount;
		$payment_date = $created_at;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = 169;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		////////////service charge/////////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $service_charge;
		$payment_date = $created_at;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = ($reflections[0]->misc_sc != '') ? $reflections[0]->misc_sc : 193;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		/////////Service Charge Tax Amount////////
		// Eg. CGST:(9%):24.77, SGST:(9%):24.77
		$service_tax_subtotal = explode(',', $service_tax_subtotal);
		$tax_ledgers = explode(',', $reflections[0]->misc_taxes);
		for ($i = 0; $i < sizeof($service_tax_subtotal); $i++) {

			$service_tax = explode(':', $service_tax_subtotal[$i]);
			$tax_amount = $service_tax[2];
			$ledger = $tax_ledgers[$i];

			$module_name = "Miscellaneous Booking";
			$module_entry_id = $misc_id;
			$transaction_id = "";
			$payment_amount = $tax_amount;
			$payment_date = $created_at;
			$payment_particular = $particular;
			$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
			$old_gl_id = $gl_id = $ledger;
			$payment_side = "Credit";
			$clearance_status = "";
			$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '',  $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');
		}

		////////////markup/////////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $markup;
		$payment_date = $created_at;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = ($reflections[0]->misc_markup != '') ? $reflections[0]->misc_markup : 205;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		/////////Markup Tax Amount////////
		// Eg. CGST:(9%):24.77, SGST:(9%):24.77
		$service_tax_markup = explode(',', $service_tax_markup);
		$tax_ledgers = explode(',', $reflections[0]->misc_markup_taxes);
		for ($i = 0; $i < sizeof($service_tax_markup); $i++) {

			$service_tax = explode(':', $service_tax_markup[$i]);
			$tax_amount = $service_tax[2];
			$ledger = $tax_ledgers[$i];

			$module_name = "Miscellaneous Booking";
			$module_entry_id = $misc_id;
			$transaction_id = "";
			$payment_amount = $tax_amount;
			$payment_date = $created_at;
			$payment_particular = $particular;
			$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
			$old_gl_id = $gl_id = $ledger;
			$payment_side = "Credit";
			$clearance_status = "";
			$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '1', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');
		}
		/////////roundoff/////////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $roundoff;
		$payment_date = $created_at;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = 230;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');

		////////Customer Amount//////
		$module_name = "Miscellaneous Booking";
		$module_entry_id = $misc_id;
		$transaction_id = "";
		$payment_amount = $misc_total_cost;
		$payment_date = $created_at;
		$payment_particular = $particular;
		$ledger_particular = get_ledger_particular('To', 'Miscellaneous Sales');
		$old_gl_id = $gl_id = $cust_gl;
		$payment_side = "Debit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id, '', $payment_side, $clearance_status, $row_spec, $ledger_particular, 'INVOICE');
	}



	public function miscellaneous_booking_email_send($misc_id)
	{

		global $secret_key, $encrypt_decrypt,$currency;
		$link = BASE_URL . 'view/customer';

		$sq_visa = mysqli_fetch_assoc(mysqlQuery("select * from miscellaneous_master where misc_id='$misc_id'"));
		$booking_date = $sq_visa['created_at'];
		$yr = explode("-", $booking_date);
		$year = $yr[0];
		$sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(`credit_charges`) as sumc from miscellaneous_payment_master where clearance_status!='Cancelled' and misc_id='$misc_id'"));
		$credit_card_amount = $sq_pay['sumc'];
		$total_amount = intval($sq_visa['misc_total_cost']) + intval($credit_card_amount);
		$total_pay_amt = intval($sq_pay['sum']) + intval($credit_card_amount);
		$outstanding =  intval($total_amount) - intval($total_pay_amt);

		$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_visa[customer_id]'"));

		$password = $encrypt_decrypt->fnDecrypt($sq_customer['email_id'], $secret_key);
		$username = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);
		$email_id = $encrypt_decrypt->fnDecrypt($sq_customer['email_id'], $secret_key);
		$customer_name = ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') ? $sq_customer['company_name'] : $sq_customer['first_name'].' '.$sq_customer['last_name'];

		$subject = "Booking confirmation acknowledgement! ( " . get_misc_booking_id($misc_id, $year) . ' )';
		
		$total_amount1 = currency_conversion($currency,$currency,$total_amount);
		$total_pay_amt1 = currency_conversion($currency,$currency,$total_pay_amt);
		$outstanding1 = currency_conversion($currency,$currency,$outstanding);
		$content = '<tr>
			<table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Total Amount</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $total_amount1 . '</td></tr>
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Paid Amount</td>   <td style="text-align:left;border: 1px solid #888888;">' . $total_pay_amt1 . '</td></tr> 
			<tr><td style="text-align:left;border: 1px solid #888888;width:50%">Balance Amount</td>   <td style="text-align:left;border: 1px solid #888888;">' . $outstanding1 . '</td></tr>
				</table>
		</tr>';
		$content .= mail_login_box($username, $password, $link);

		global $model, $backoffice_email_id;

		$model->app_email_send('101', $customer_name, $email_id, $content, $subject);
		$model->app_email_send('101', "Team", $backoffice_email_id, $content, $subject);
	}

	public function employee_sign_up_mail($first_name, $last_name, $username, $password, $email_id)
	{
		global $app_email_id, $app_name, $app_contact_no, $admin_logo_url, $app_website;
		global $mail_em_style, $mail_em_style1, $mail_font_family, $mail_strong_style, $mail_color;
		$link = BASE_URL . 'view/customer';
		$content = mail_login_box($username, $password, $link);
		$subject = 'Welcome aboard!';
		global $model;
		$model->app_email_send('2', $first_name, $email_id, $content, $subject, '1');
	}

	public function whatsapp_send()
	{
		global $app_contact_no, $secret_key, $encrypt_decrypt,$app_name,$session_emp_id;
		$booking_date = $_POST['booking_date'];
		$customer_id = $_POST['customer_id'];

		if ($customer_id == '0') {
			$sq_customer = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM customer_master ORDER BY customer_id DESC LIMIT 1"));
		} else {
			$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
		}
		$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);
		$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id= '$session_emp_id'"));
		if ($session_emp_id == 0) {
			$contact = $app_contact_no;
		} else {
			$contact = $sq_emp_info['mobile_no'];
		}
		$customer_name = ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') ? $sq_customer['company_name'] : $sq_customer['first_name'].' '.$sq_customer['last_name'];

		$whatsapp_msg = rawurlencode('Dear ' . $customer_name . ',
Hope you are doing great. This is to inform you that your booking is confirmed with us. We look forward to provide you a great experience.
*Booking Date* : ' . get_date_user($booking_date) . '

Please contact for more details : '.$app_name.' '.$contact);
	if ($customer_id == '0') {

		//Customer Whatsapp message
		$username = $_POST['contact_no'];
		$password = $_POST['email_id'];
		$whatsapp_msg .= whatsapp_login_box($username,$password);
	}
	$whatsapp_msg .= '%0aThank%20you.%0a';
		$link = 'https://web.whatsapp.com/send?phone=' . $contact_no . '&text=' . $whatsapp_msg;
		echo $link;
	}
}

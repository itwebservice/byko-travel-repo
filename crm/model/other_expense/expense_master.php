<?php

$flag = true;

class expense_master{
public function expense_save()
{
	$row_spec = 'other expense';
	$expense_type = $_POST['expense_type'];
	$supplier_type = $_POST['supplier_type'];
	$sub_total = $_POST['sub_total'];
	$ledger_ids = $_POST['ledger_ids'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$service_tax_subtotals = $_POST['service_tax_subtotals'];
	$tds = $_POST['tds'];
	$net_total = $_POST['net_total'];
	$due_date = $_POST['due_date'];
	$booking_date = $_POST['booking_date'];
	$invoice_no = $_POST['invoice_no'];
	$id_upload_url =$_POST['id_upload_url'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$payment_amount = $_POST['payment_amount'];
	$bank_name =$_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$branch_admin_id = $_POST['branch_admin_id'];
	$emp_id = $_POST['emp_id']; 
	$payment_evidence_url = $_POST['payment_evidence_url'];

	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$financial_year_id = $_SESSION['financial_year_id'];

	$created_at = date('Y-m-d H:i');
	$due_date = date('Y-m-d',strtotime($due_date));
	$booking_date = date('Y-m-d',strtotime($booking_date));
	$payment_date = date('Y-m-d', strtotime($payment_date));

	begin_t();

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(expense_id) as max from other_expense_master"));
	$expense_id = $sq_max['max'] + 1;
	$sq_expense = mysqlQuery("insert into other_expense_master (expense_id, expense_type_id, supplier_id, financial_year_id, branch_admin_id, amount,ledgers, service_tax_subtotal, tds, total_fee, due_date,invoice_no,expense_date,invoice_url,created_at,tax_refl) values ('$expense_id', '$expense_type', '$supplier_type', '$financial_year_id', '$branch_admin_id', '$sub_total','$ledger_ids','$service_tax_subtotal', '$tds','$net_total','$due_date','$invoice_no','$booking_date','$id_upload_url','$created_at','$service_tax_subtotals' ) ");

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from other_expense_payment_master"));
	$payment_id = $sq_max['max']+1;
	$sq_payment = mysqlQuery("insert into other_expense_payment_master(payment_id,expense_id, expense_type_id,supplier_id, financial_year_id, branch_admin_id, payment_amount, payment_mode, payment_date, bank_name, transaction_id, bank_id, clearance_status,evidance_url, created_at, emp_id) values ('$payment_id','$expense_id', '$expense_type', '$supplier_type', '$financial_year_id', '$branch_admin_id', '$payment_amount', '$payment_mode', '$payment_date', '$bank_name', '$transaction_id', '$bank_id', '$clearance_status','$payment_evidence_url', '$created_at', '$emp_id')");
    if(!$sq_payment){
  	  $GLOBALS['flag'] = false;
	    echo "error--Sorry, Payment not done!";
	    exit;
    }
	if($sq_expense){
		
		if($payment_mode!='Credit Note'){
			//Finance save
			$this->finance_save($expense_id, $payment_id, $row_spec, $branch_admin_id);
			//Cash/bank book
			$this->bank_cash_book_save($expense_id, $payment_id, $branch_admin_id);
		}
	    if($GLOBALS['flag']){
			commit_t();
			echo "Expense has been successfully saved";
			exit;
	    }
	    else{
			rollback_t();
			exit;
	    }
	}
	else{
		rollback_t();
		echo "error--Expense Booking Not Done!";
		exit;
	}
}

function expense_delete(){

		global $delete_master,$transaction_master;
		$expense_id = $_POST['expense_id'];
		$deleted_date = date('Y-m-d');
		$row_spec = "other expense";
	
		$row_expense = mysqli_fetch_assoc(mysqlQuery("select * from other_expense_master where expense_id='$expense_id'"));
		$ledger_ids = $row_expense['ledgers'];
		$expense_type = $row_expense['expense_type_id'];
		$supplier_type = $row_expense['supplier_id'];
		$booking_date = $row_expense['expense_date'];
		$yr = explode("-", $booking_date);
		$year = $yr[0];
		
		$sq_exp = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$expense_type' "));
		$sq_supplier = mysqli_fetch_assoc(mysqlQuery("select * from other_vendors where vendor_id='$row_expense[supplier_id]'"));
		$sq_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$supplier_type' and user_type='Other Vendor'"));
		$cust_gl = $sq_ledger['ledger_id'];

		$trans_id = get_other_expense_booking_id($row_expense['expense_id'],$year).' : '.$sq_exp['ledger_name'].' ('.$sq_supplier['vendor_name'].')';
		$transaction_master->updated_entries('Other Expense Booking',$expense_id,$trans_id,$row_expense['total_fee'],0);

		$delete_master->delete_master_entries('Voucher',$sq_exp['ledger_name'],$expense_id,get_other_expense_booking_id($row_expense['expense_id'],$year),$sq_supplier['vendor_name'],$row_expense['total_fee']);

		////////////Basic Expense Amount/////////////
		$module_name = "Other Expense Booking";
		$module_entry_id = $expense_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $deleted_date;
		$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, 0, '');    
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$old_gl_id = $gl_id = $expense_type;
		$payment_side = "Debit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');

		//Tax Amount
		$ledger_ids_arr = explode(',',$ledger_ids);
		if(sizeof($ledger_ids_arr) == 1){

			// Debit
			$module_name = "Other Expense Booking";
			$module_entry_id = $expense_id;
			$transaction_id = "";
			$payment_amount = 0;
			$payment_date = $booking_date;
			$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, 0,  '');
			$ledger_particular = get_ledger_particular('To','Expense');
			$old_gl_id = $gl_id = $ledger_ids_arr[0];
			$payment_side = "Debit";
			$clearance_status = "";
			$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');
		}
		else{
			for($i=0;$i<sizeof($ledger_ids_arr);$i++){

				//Debit
				$module_name = "Other Expense Booking";
				$module_entry_id = $expense_id;
				$transaction_id = "";
				$payment_amount = 0;
				$payment_date = $booking_date;
				$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, 0, '');
				$ledger_particular = get_ledger_particular('To','Expense');
				$old_gl_id = $gl_id = $ledger_ids_arr[$i];
				$payment_side = "Debit";
				$clearance_status = "";
				$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');
			}
		}
		
		/////////TDS Debit////////
		$module_name = "Other Expense Booking";
		$module_entry_id = $expense_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $booking_date;
		$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, 0,  '');
		$ledger_particular = get_ledger_particular('To','Expense');
		$old_gl_id = $gl_id = 126;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');

		////////Net total Amount//////
		$module_name = "Other Expense Booking";
		$module_entry_id = $expense_id;
		$transaction_id = "";
		$payment_amount = 0;
		$payment_date = $booking_date;
		$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, 0,  '');
		$ledger_particular = get_ledger_particular('To','Expense');
		$old_gl_id = $gl_id = $cust_gl;
		$payment_side = "Credit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');

		$sq_delete = mysqlQuery("update other_expense_master set amount = '0',service_tax_subtotal='0',tds='0',total_fee='0',delete_status='1' where expense_id='$expense_id'");
		if($sq_delete){
			echo 'Entry deleted successfully!';
			exit;
		}
}

public function finance_save($expense_id, $payment_id, $row_spec, $branch_admin_id){


	global $transaction_master;
	$expense_type = $_POST['expense_type'];
	$supplier_type = $_POST['supplier_type'];
	$sub_total = $_POST['sub_total'];
	$ledger_ids = $_POST['ledger_ids'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$tds = $_POST['tds'];
	$net_total = $_POST['net_total'];
	$booking_date = $_POST['booking_date'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$payment_amount1 = $_POST['payment_amount'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
    $branch_admin_id = $_POST['branch_admin_id'];

	$booking_date = get_date_db($_POST['booking_date']);
	$payment_date1 = get_date_db($payment_date);

	$yr = explode("-", $booking_date);
	$year = $yr[0];
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$supplier_type' and user_type='Other Vendor'"));
	$cust_gl = $sq_cust['ledger_id'];

	//Getting cash/Bank Ledger
	if($payment_mode == 'Cash') {  $pay_gl = 20;$type='CASH PAYMENT';  }
	else{ 
		$sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
		$pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
	}

	$service_tax_subtotal1 = floatval($service_tax_subtotal) / intval(2);
    ////////////Total Expense Amount/////////////
    $module_name = "Other Expense Booking";
    $module_entry_id = $expense_id;
    $transaction_id = "";
    $payment_amount = $sub_total;
    $payment_date = $booking_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $sub_total, $payment_mode);    
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $gl_id = $expense_type;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'VOUCHER');

	//Tax Amount
	$ledger_ids_arr = explode(',',$ledger_ids);
	if(sizeof($ledger_ids_arr) == 1){
		// Debit
		$module_name = "Other Expense Booking";
		$module_entry_id = $expense_id;
		$transaction_id = "";
		$payment_amount = $service_tax_subtotal;
		$payment_date = $booking_date;
		$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $service_tax_subtotal, $payment_mode);    
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = $ledger_ids_arr[0];
		$payment_side = "Debit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'VOUCHER');
	}
	else{
		for($i=0;$i<sizeof($ledger_ids_arr);$i++){
			//Debit 
			$module_name = "Other Expense Booking";
			$module_entry_id = $expense_id;
			$transaction_id = "";
			$payment_amount = $service_tax_subtotal1;
			$payment_date = $booking_date;
			$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $service_tax_subtotal1, $payment_mode);    
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $ledger_ids_arr[$i];
			$payment_side = "Debit";
			$clearance_status = "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'VOUCHER');
		}
	}
    /////////TDS Debit////////
    $module_name = "Other Expense Booking";
    $module_entry_id = $expense_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $booking_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $tds, $payment_mode);
    $ledger_particular = get_ledger_particular('To','Expense');
    $gl_id = 126;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'VOUCHER');

    ////////////Basic Amount/////////////
    $module_name = "Other Expense Booking";
    $module_entry_id = $expense_id;
    $transaction_id = "";
    $payment_amount = $net_total;
    $payment_date = $booking_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $net_total, $payment_mode);    
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $gl_id = $cust_gl;
    $payment_side = "Credit";
    $clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'VOUCHER');

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') { $type = 'CASH PAYMENT'; }
    else{
		$type = 'BANK PAYMENT';
    }

    //////Payment Amount///////
    $module_name = "Other Expense Booking Payment";
    $module_entry_id = $payment_id;
    $transaction_id = "";
    $payment_amount = $payment_amount1;
    $payment_date = $payment_date1;
    $payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $payment_date1, $payment_amount1, $payment_mode);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $gl_id = $pay_gl;
    $payment_side = "Credit";
    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

    ////////Supplier Payment Amount//////
	$module_name = "Other Expense Booking Payment";
	$module_entry_id = $payment_id;
	$transaction_id = "";
	$payment_amount = $payment_amount1;
	$payment_date = $payment_date1;
	$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $payment_date1, $payment_amount1, $payment_mode);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$gl_id = $cust_gl;
	$payment_side = "Debit";
	$clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date,$payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
}

public function bank_cash_book_save($expense_id, $payment_id, $branch_admin_id)
{
	global $bank_cash_book_master;

	$supplier_type = $_POST['supplier_type'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name =$_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$yr = explode("-", $payment_date);
	$year = $yr[0];

	$module_name = "Other Expense Booking Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id; 
	$particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $payment_date, $payment_amount, $payment_mode);
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);
}
public function expense_update(){
	$row_spec = 'other expense';
	$expense_id = $_POST['expense_id'];
	$expense_type = $_POST['expense_type'];
	$supplier_type = $_POST['supplier_type'];
	$sub_total = $_POST['sub_total'];
	$ledger_ids = $_POST['ledger_ids'];
    $service_tax_subtotal = $_POST['service_tax_subtotal'];
	$service_tax_subtotals = $_POST['service_tax_subtotals'];
	$tds = $_POST['tds'];
	$net_total = $_POST['net_total'];
	$old_amount = $_POST['old_total'];
	$due_date = $_POST['due_date'];
	$booking_date = $_POST['booking_date'];
	$invoice_no = $_POST['invoice_no'];
	$id_upload_url =$_POST['id_upload_url'];

	$due_date = get_date_db($due_date);
    $booking_date = get_date_db($booking_date);

	begin_t();
	$sq_expense_u = mysqlQuery("update other_expense_master set expense_type_id='$expense_type',supplier_id='$supplier_type',amount='$sub_total',ledgers='$ledger_ids', service_tax_subtotal='$service_tax_subtotal', tds='$tds', total_fee='$net_total', due_date='$due_date',invoice_no='$invoice_no',expense_date='$booking_date',invoice_url='$id_upload_url',tax_refl='$service_tax_subtotals' where expense_id='$expense_id'");

	if($sq_expense_u){
		//Finance save
		$this->finance_update($expense_id, $row_spec);
		if($old_amount != $net_total){
	
			global $transaction_master;
			$yr = explode("-", $booking_date);
			$sq_exp = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$expense_type' "));
			$sq_supplier = mysqli_fetch_assoc(mysqlQuery("select * from other_vendors where vendor_id='$supplier_type'"));
		
			$trans_id = get_other_expense_booking_id($expense_id,$yr[0]).' : '.$sq_exp['ledger_name'].' ('.$sq_supplier['vendor_name'].')';
			$transaction_master->updated_entries('Other Expense Booking',$expense_id,$trans_id,$old_amount,$net_total);
		}

		if($GLOBALS['flag']){
			commit_t();
			echo "Expense has been successfully updated.";
			exit;
		}
		else{
			rollback_t();
			exit;
		}
	}
	else{
		rollback_t();
		echo "error--Expense not updated!";
		exit;
	}
}

public function finance_update($expense_id, $row_spec)
{
	$expense_type = $_POST['expense_type'];
	$supplier_type = $_POST['supplier_type'];
	$sub_total = $_POST['sub_total'];
	$ledger_ids = $_POST['ledger_ids'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$tds = $_POST['tds'];
	$net_total = $_POST['net_total'];
	$due_date = $_POST['due_date'];
	$booking_date = $_POST['booking_date'];
	$invoice_no = $_POST['invoice_no'];
	$id_upload_url =$_POST['id_upload_url'];

	$booking_date = get_date_db($_POST['booking_date']);
	$booking_date1 = get_date_db($booking_date);
	$yr = explode("-", $booking_date1);
	$year = $yr[0];

    //Getting customer Ledger
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$supplier_type' and user_type='Other Vendor'"));
    $cust_gl = $sq_cust['ledger_id'];

    global $transaction_master;
    ////////////Basic Expense Amount/////////////
    $module_name = "Other Expense Booking";
    $module_entry_id = $expense_id;
    $transaction_id = "";
    $payment_amount = $sub_total;
    $payment_date = $booking_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $sub_total, '');    
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $old_gl_id = $gl_id = $expense_type;
    $payment_side = "Debit";
    $clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');

	//Tax Amount
	$ledger_ids_arr = explode(',',$ledger_ids);
	if(sizeof($ledger_ids_arr) == 1){

		// Debit
		$module_name = "Other Expense Booking";
		$module_entry_id = $expense_id;
		$transaction_id = "";
		$payment_amount = $service_tax_subtotal;
		$payment_date = $booking_date;
		$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $service_tax_subtotal,  '');
		$ledger_particular = get_ledger_particular('To','Expense');
		$old_gl_id = $gl_id = $ledger_ids_arr[0];
		$payment_side = "Debit";
		$clearance_status = "";
		$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');
	}
	else{
		$service_tax_subtotal = $service_tax_subtotal / 2;
		for($i=0;$i<sizeof($ledger_ids_arr);$i++){

			//Debit
			$module_name = "Other Expense Booking";
			$module_entry_id = $expense_id;
			$transaction_id = "";
			$payment_amount = $service_tax_subtotal;
			$payment_date = $booking_date;
			$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $service_tax_subtotal, '');
			$ledger_particular = get_ledger_particular('To','Expense');
			$old_gl_id = $gl_id = $ledger_ids_arr[$i];
			$payment_side = "Debit";
			$clearance_status = "";
			$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');
		}
	}
	
    /////////TDS Debit////////
    $module_name = "Other Expense Booking";
    $module_entry_id = $expense_id;
    $transaction_id = "";
    $payment_amount = $tds;
    $payment_date = $booking_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $tds,  '');
    $ledger_particular = get_ledger_particular('To','Expense');
    $old_gl_id = $gl_id = 126;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER');

    ////////Net total Amount//////
	$module_name = "Other Expense Booking";
	$module_entry_id = $expense_id;
	$transaction_id = "";
	$payment_amount = $net_total;
	$payment_date = $booking_date;
	$payment_particular = get_expense_paid_particular(get_other_expense_booking_id($expense_id,$year), $supplier_type, $booking_date, $net_total,  '');
	$ledger_particular = get_ledger_particular('To','Expense');
	$old_gl_id = $gl_id = $cust_gl;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,'VOUCHER'); 
}

}
?>
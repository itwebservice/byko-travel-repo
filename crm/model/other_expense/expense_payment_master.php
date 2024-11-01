<?php
$flag = true;
class expense_payment_master{
	
public function expense_payment_save()
{
	$row_spec='other expense';
	$branch_admin_id = $_SESSION['branch_admin_id'];
	$supplier_type = $_POST['supplier_type'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
    $emp_id = $_POST['emp_id'];
	$payment_evidence_url = $_POST['payment_evidence_url'];

	$payment_amount_arr = $_POST['payment_amount_arr'];
	$purchase_type_arr = $_POST['purchase_type_arr'];
	$purchase_id_arr = $_POST['purchase_id_arr'];
	$expense_arr = $_POST['expense_arr'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$created_at = date('Y-m-d H:i');

	
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";

	$financial_year_id = $_SESSION['financial_year_id'];


	begin_t();

	for($i=0;$i<sizeof($purchase_type_arr);$i++){
		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from other_expense_payment_master"));
		$payment_id = $sq_max['max'] + 1;

		$sq_payment = mysqlQuery("insert into other_expense_payment_master (payment_id,expense_id, expense_type_id, supplier_id, financial_year_id, branch_admin_id, payment_amount, payment_mode, payment_date, bank_name, transaction_id, bank_id, clearance_status, evidance_url, created_at,emp_id) values ('$payment_id', '$purchase_id_arr[$i]','$expense_arr[$i]', '$supplier_type', '$financial_year_id', '$branch_admin_id', '$payment_amount_arr[$i]', '$payment_mode', '$payment_date', '$bank_name', '$transaction_id','$bank_id', '$clearance_status', '$payment_evidence_url', '$created_at', '$emp_id') ");
	}
	
	if(!$sq_payment){
		rollback_t();
		echo "error--Sorry, Expense Payment not saved!";
		exit;
	}
	else{

		if($payment_mode!='Credit Note'){
			//Finance Save
			$this->finance_save($payment_id,$row_spec);
			//Bank and Cash Book Save
			$this->bank_cash_book_save($payment_id,$branch_admin_id);
		}
		if($GLOBALS['flag']){
			commit_t();
	    	echo "Payment has been successfully saved.";
			exit;
		}
		
	}
}

public function expense_payment_delete(){
	
	global $delete_master,$transaction_master,$bank_cash_book_master;
	$payment_id = $_POST['payment_id'];
	$deleted_date = date('Y-m-d');
	$row_spec = 'other expense';
	
	$row_payment = mysqli_fetch_assoc(mysqlQuery("select * from other_expense_payment_master where payment_id='$payment_id'"));

	$transaction_id = $row_payment['transaction_id'];
	$payment_mode = $row_payment['payment_mode'];
	$bank_id = $row_payment['bank_id'];
	$supplier_type = $row_payment['supplier_id'];
	$expense_type = $row_payment['expense_type_id'];
	$bank_name = $row_payment['bank_name'];
	$payment_date = $row_payment['payment_date'];
	$yr = explode("-", $payment_date);
	$year = $yr[0];

	$sq_exp = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$expense_type' "));
	$sq_supplier = mysqli_fetch_assoc(mysqlQuery("select * from other_vendors where vendor_id='$supplier_type'"));

	$trans_id = get_other_expense_payment_id($payment_id,$year).' : '.$sq_exp['ledger_name'].' ('.$sq_supplier['vendor_name'].')';
	$transaction_master->updated_entries('Other Expense Booking Payment',$payment_id,$trans_id,$row_payment['payment_amount'],0);

	$delete_master->delete_master_entries('Voucher('.$payment_mode.')',$sq_exp['ledger_name'],$payment_id,get_other_expense_payment_id($payment_id,$year),$sq_supplier['vendor_name'],$row_payment['payment_amount']);

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH PAYMENT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    }

    //Getting supplier Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$supplier_type' and user_type='Other Vendor' and group_sub_id='105'"));
	$supplier_gl = $sq_cust['ledger_id'];

	//////Payment Amount///////
    $module_name = "Other Expense Booking Payment";
    $module_entry_id = $payment_id;
    $transaction_id = $transaction_id;
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_type, $payment_date, 0, $payment_mode);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $old_gl_id = $gl_id = $pay_gl;
    $payment_side = "Credit";
    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);

    //////Supplier Amount///////
    $module_name = "Other Expense Booking Payment";
    $module_entry_id = $payment_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $payment_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_type, $payment_date, 0, $payment_mode);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $old_gl_id = $gl_id = $supplier_gl;
    $payment_side = "Debit";
    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec,$ledger_particular,$type);
	
	$module_name = "Other Expense Booking Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_type, $payment_date, $payment_amount, $payment_mode);
	$clearance_status = $clearance_status;
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);

	$sq_delete = mysqlQuery("update other_expense_payment_master set payment_amount = '0' , delete_status='1' where payment_id='$payment_id'");
	if($sq_delete){
		echo 'Entry deleted successfully!';
		exit;
	}
}
public function finance_save($payment_id,$row_spec)
{
	$supplier_type = $_POST['supplier_type'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$branch_admin_id = isset($_POST['branch_admin_id']) ? $_POST['branch_admin_id'] : '';

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$yr = explode("-", $payment_date);
	$year = $yr[0];

	global $transaction_master;

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH PAYMENT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    }

    //Getting supplier Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$supplier_type' and user_type='Other Vendor' and group_sub_id='105'"));
	$supplier_gl = $sq_cust['ledger_id'];

	//////Payment Amount///////
    $module_name = "Other Expense Booking Payment";
    $module_entry_id = $payment_id;
    $transaction_id = $transaction_id1;
    $payment_amount = $payment_amount1;
    $payment_date = $payment_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_type, $payment_date, $payment_amount1, $payment_mode);
    $ledger_particular = get_ledger_particular('To','Expense');
    $gl_id = $pay_gl;
    $payment_side = "Credit";
    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

    //////Supplier Amount///////
    $module_name = "Other Expense Booking Payment";
    $module_entry_id = $payment_id;
    $transaction_id = "";
    $payment_amount = $payment_amount1;
    $payment_date = $payment_date;
    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_type, $payment_date, $payment_amount1, $payment_mode);
    $ledger_particular = get_ledger_particular('By','Expense');
    $gl_id = $supplier_gl;
    $payment_side = "Debit";
    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

}

public function bank_cash_book_save($payment_id, $branch_admin_id)
{
	global $bank_cash_book_master;

	$supplier_type = $_POST['supplier_type'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name =$_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$yr = explode("-", $payment_date);
	$year = $yr[0];

	$module_name = "Other Expense Booking Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount1;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id; 
	$particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_type, $payment_date, $payment_amount1, $payment_mode);
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);
}

public function expense_payment_update()
{
	$payment_id = $_POST['payment_id'];
	$supplier_id = $_POST['supplier_id'];
	$expense_type_id = $_POST['expense_type_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_evidence_url = $_POST['payment_evidence_url'];
	$payment_old_value = $_POST['payment_old_value'];
	$financial_year_id = $_SESSION['financial_year_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from other_expense_payment_master where payment_id='$payment_id'"));

	$clearance_status = ($sq_payment_info['payment_mode']=='Cash' && $payment_mode!="Cash") ? "Pending" : $sq_payment_info['clearance_status'];
	if($payment_mode=="Cash"){ $clearance_status = ""; }
	begin_t();

	$sq_payment = mysqlQuery("update other_expense_payment_master set financial_year_id='$financial_year_id', supplier_id='$supplier_id', expense_type_id='$expense_type_id', payment_date='$payment_date', payment_amount='$payment_amount', payment_mode='$payment_mode', bank_name='$bank_name', transaction_id='$transaction_id', bank_id='$bank_id', evidance_url='$payment_evidence_url', clearance_status='$clearance_status' where payment_id='$payment_id' ");
	if(!$sq_payment){
		rollback_t();
		echo "error--Sorry, Expense Payment not updated!";
		exit;
	}
	else{

		//Finance update
		$this->finance_update($sq_payment_info, $clearance_status);
		//Bank and Cash Book update
		$this->bank_cash_book_update($clearance_status);

		if($payment_old_value != $payment_amount){
	
			global $transaction_master;
			$yr = explode("-", $payment_date);
			$sq_exp = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$expense_type_id' "));
			$sq_supplier = mysqli_fetch_assoc(mysqlQuery("select * from other_vendors where vendor_id='$supplier_id'"));
		
			$trans_id = get_other_expense_payment_id($payment_id,$yr[0]).' : '.$sq_exp['ledger_name'].' ('.$sq_supplier['vendor_name'].')';
			$transaction_master->updated_entries('Other Expense Booking Payment',$payment_id,$trans_id,$payment_old_value,$payment_amount);
		}

		if($GLOBALS['flag']){
			commit_t();
	    	echo "Payment has been successfully updated.";
			exit;	
		}
		
	}
}

public function finance_update($sq_payment_info, $clearance_status1)
{
	$row_spec='other expense';
	$payment_id = $_POST['payment_id'];
	$supplier_id = $_POST['supplier_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_old_value = $_POST['payment_old_value'];
	$payment_old_mode =  $_POST['payment_old_mode'];
	$branch_admin_id = $_SESSION['branch_admin_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$yr = explode("-", $payment_date);
	$year = $yr[0];

	global $transaction_master;

    //Getting New cash/Bank Ledger
    if($payment_old_mode == 'Cash') {  $pay_gl = 20; $type='CASH PAYMENT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    } 

    //Getting supplier Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$supplier_id' and user_type='Other Vendor' and group_sub_id='105'"));
	$supplier_gl = $sq_cust['ledger_id'];

	if($payment_amount1 < $payment_old_value)
	{
		$supp_amount= $payment_old_value - $payment_amount1;
		////////Supplier Amount//////   
	    $module_name = "Other Expense Booking Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $supp_amount;
	    $payment_date = $payment_date;
	    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_id, $payment_date, $supp_amount, $payment_mode);
			$ledger_particular = get_ledger_particular('To','Expense');
	    $gl_id = $supplier_gl;
	    $payment_side = "Credit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Old Payment Amount///////
	    $module_name = "Other Expense Booking Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_old_value;
	    $payment_date = $payment_date;
	    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_id, $payment_date, $payment_old_value, $payment_mode);
			$ledger_particular = get_ledger_particular('To','Expense');
	    $gl_id = $pay_gl;
	    $payment_side = "Debit";
	    $clearance_status = ($payment_old_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Payment Amount///////
	    $module_name = "Other Expense Booking Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date;
	    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_id, $payment_date, $payment_amount1, $payment_mode);
			$ledger_particular = get_ledger_particular('To','Expense');
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
	    $clearance_status = ($payment_old_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
	else if($payment_amount1 > $payment_old_value)
	{
		$supp_amount = $payment_amount1 - $payment_old_value;
		////////Supplier Amount//////   
	    $module_name = "Other Expense Booking Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $supp_amount;
	    $payment_date = $payment_date;
	    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_id, $payment_date, $supp_amount, $payment_mode);
			$ledger_particular = get_ledger_particular('To','Expense');
	    $gl_id = $supplier_gl;
	    $payment_side = "Debit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Old Payment Amount///////
	    $module_name = "Other Expense Booking Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_old_value;
	    $payment_date = $payment_date;
	    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_id, $payment_date, $payment_old_value, $payment_mode);
			$ledger_particular = get_ledger_particular('To','Expense');
	    $gl_id = $pay_gl;
	    $payment_side = "Debit";
	    $clearance_status = ($payment_old_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Payment Amount///////
	    $module_name = "Other Expense Booking Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date;
	    $payment_particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_id, $payment_date, $payment_amount1, $payment_mode);
		$ledger_particular = get_ledger_particular('To','Expense');
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
	    $clearance_status = ($payment_old_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
	else{
		//Do Nothing
	}

}

public function bank_cash_book_update($clearance_status)
{
	$payment_id = $_POST['payment_id'];
	$supplier_id = $_POST['supplier_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$yr = explode("-", $payment_date);
	$year = $yr[0];

	global $bank_cash_book_master;
	
	$module_name = "Other Expense Booking Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_expense_paid_particular(get_other_expense_payment_id($payment_id,$year),$supplier_id, $payment_date, $payment_amount, $payment_mode);
	$clearance_status = $clearance_status;
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);
}

}
?>
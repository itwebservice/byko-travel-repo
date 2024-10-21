<?php 
$flag = true;
class income_master{

public function income_save()
{
	$row_spec = 'other income';
	$income_type_id = $_POST['income_type_id'];
	$r_from = $_POST['r_from'];
	$sub_total = $_POST['sub_total'];
	$service_tax_subtotal = $_POST['service_tax_subtotal'];
	$tds = $_POST['tds'];
	$net_total = $_POST['net_total'];
	$booking_date = $_POST['booking_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$particular = $_POST['particular'];
	$cust_pan_no = $_POST['cust_pan_no'];
	$branch_admin_id = $_POST['branch_admin_id'];
	$financial_year_id = $_SESSION['financial_year_id'];

	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";

	$financial_year_id = $_SESSION['financial_year_id']; 

	$created_at = date('Y-m-d H:i');

	$payment_date = get_date_db($payment_date);
	$booking_date = get_date_db($booking_date);

	begin_t();

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(income_id) as max from other_income_master"));
	$income_id = $sq_max['max'] + 1;
	
	$particular = addslashes($particular);
	$sq_income = mysqlQuery("insert into other_income_master (income_id, income_type_id,receipt_from, financial_year_id,branch_admin_id, amount, service_tax_subtotal, tds, total_fee,receipt_date, particular,pan_no, created_at) values ('$income_id', '$income_type_id','$r_from', '$financial_year_id','$branch_admin_id', '$sub_total','$service_tax_subtotal', '$tds','$net_total','$booking_date', '$particular','$cust_pan_no', '$created_at')");

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from other_income_payment_master"));
	$payment_id = $sq_max['max'] + 1;
	
	$sq_pay = mysqlQuery("insert into other_income_payment_master (payment_id, income_type_id, financial_year_id, payment_amount, payment_date, payment_mode, bank_name, transaction_id, bank_id, clearance_status, created_at) values ('$payment_id', '$income_id', '$financial_year_id', '$payment_amount', '$payment_date', '$payment_mode', '$bank_name', '$transaction_id', '$bank_id', '$clearance_status', '$created_at')");

	if($sq_income){
	

		if($payment_mode != 'Credit Note'){
			//Finance Save
			$this->finance_save($income_id,$row_spec,$payment_id);
			//Bank and Cash Book Save
			$this->bank_cash_book_save($income_id);
		}
		if($GLOBALS['flag']){
			commit_t();
			echo "Income has been successfully saved.";
			exit;
		}
	}
	else{
		rollback_t();
		echo "error--Income not saved!";
		exit;
	}

}

public function income_delete(){

	global $delete_master,$transaction_master,$bank_cash_book_master;
	$income_id = $_POST['entry_id'];
	$deleted_date = date('Y-m-d');
	$row_spec = "other income";

	$sq_income = mysqli_fetch_assoc(mysqlQuery("select * from other_income_master where income_id='$income_id'"));
	$sq_income_p = mysqli_fetch_assoc(mysqlQuery("select * from other_income_payment_master where income_type_id='$income_id'"));
	$payment_id = $sq_income_p['payment_id'];
	$particular = $sq_income['particular'];
	$payment_mode = $sq_income_p['payment_mode'];
	$income_type_id = $sq_income['income_type_id'];
	$bank_id = $sq_income_p['bank_id'];
	$bank_name = $sq_income_p['bank_name'];

	$sq_income_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$income_type_id'"));
	//Getting cash/Bank Ledger
	if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
	else{ 
		$sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
		$pay_gl = $sq_bank['ledger_id'];
		$type='BANK RECEIPT';
	}
	
	$year1 = explode("-", $sq_income_p['payment_date']);
	$yr1 = $year1[0];
	$f_income_id = get_other_income_payment_id($income_id,$yr1);

	$trans_id = $f_income_id.' : '.$sq_income['receipt_from'];
	$transaction_master->updated_entries('Other Income',$income_id,$trans_id,$sq_income['total_fee'],0);

	$delete_master->delete_master_entries('Other Income('.$payment_mode.')','Other Income',$income_id,$f_income_id,$sq_income['receipt_from'],$sq_income_p['payment_amount']);

	//Bank Or Cash    
	$module_name = "Other Income Payment";
	$module_entry_id = $payment_id;
	$transaction_id = $sq_income_p['transaction_id'];
	$payment_amount = 0;
	$payment_date = $deleted_date;
	$payment_particular = get_other_income_particular($payment_mode, $deleted_date, $sq_income_ledger['ledger_name'], 0,$transaction_id);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$old_gl_id = $gl_id = $pay_gl;
	$payment_side = "Debit";
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec, $ledger_particular,$type);

	//Dynamic GL
	$module_name = "Other Income Payment";
	$module_entry_id = $payment_id;
	$transaction_id = $sq_income_p['transaction_id'];
	$payment_amount = 0;
	$payment_date = $deleted_date;
	$payment_particular = get_other_income_particular($payment_mode, $deleted_date, $sq_income_ledger['ledger_name'], 0,$transaction_id);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$old_gl_id = $gl_id = $income_type_id;
	$payment_side = "Credit";
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec, $ledger_particular,$type);
	
	$module_name = "Other Income Payment";
	$module_entry_id = $payment_id;
	$payment_date = $deleted_date;
	$payment_amount = 0;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $sq_income_p['transaction_id'];
	$bank_id = $bank_id;
	$particular = get_other_income_particular($payment_mode, $deleted_date, $sq_income_ledger['ledger_name'], $payment_amount,$transaction_id);
	$clearance_status = '';
	$payment_side = "Debit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";
	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);

	$sq_up = mysqlQuery("update other_income_master set delete_status = '1', amount = '0' , service_tax_subtotal = '0', tds='0', total_fee='0'  where income_id='$income_id'");
	$sq_up1 = mysqlQuery("update other_income_payment_master set payment_amount = '0' where income_type_id='$income_id'");
	if($sq_up1){
		echo 'Entry deleted successfully!';
		exit;
	}
}

public function finance_save($income_id,$row_spec,$payment_id)
{
	$income_type_id = $_POST['income_type_id'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_date = $_POST['payment_date'];
	$branch_admin_id = $_SESSION['branch_admin_id'];

	$payment_date1 = date('Y-m-d', strtotime($payment_date));
	global $transaction_master;

	$sq_income_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$income_type_id'"));
	//Getting cash/Bank Ledger
	if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
	else{ 
		$sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
		$pay_gl = $sq_bank['ledger_id'];
		$type='BANK RECEIPT';
	} 

	//Bank Or Cash
	$module_name = "Other Income Payment";
	$module_entry_id = $payment_id;
	$transaction_id = $transaction_id1;
	$payment_amount = $payment_amount1;
	$payment_date = $payment_date1;
	$payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$gl_id = $pay_gl;
	$payment_side = "Debit";
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	//Dynamic GL
	$module_name = "Other Income Payment";
	$module_entry_id = $payment_id;
	$transaction_id = $transaction_id1;
	$payment_amount = $payment_amount1;
	$payment_date = $payment_date1;
	$payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$gl_id = $income_type_id;
	$payment_side = "Credit";
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
}

public function bank_cash_book_save($payment_id)
{
	global $bank_cash_book_master;

	$income_type_id = $_POST['income_type_id'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$particular = $_POST['particular'];

	$payment_date1 = date('Y-m-d', strtotime($payment_date));
	$sq_income_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$income_type_id'"));
	
	$module_name = "Other Income Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date1;
	$payment_amount = $payment_amount1;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id1;
	$bank_id = $bank_id;
	$particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id1);
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$payment_side = "Debit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);
	
}


public function income_update()
{
	$row_spec = 'other income';
	$payment_id = $_POST['payment_id'];
	$income_type_id = $_POST['income_type_id'];
	$payment_amount = $_POST['payment_amount'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_old_value =  $_POST['payment_old_value'];

	$financial_year_id = $_SESSION['financial_year_id']; 

	$payment_date = get_date_db($payment_date);

	$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from other_income_payment_master where payment_id='$payment_id'"));
	$sq_info = mysqli_fetch_assoc(mysqlQuery("select * from other_income_master where income_type_id='$income_type_id'"));

	$clearance_status = ($sq_payment_info['payment_mode']=='Cash' && $payment_mode=="Cheque") ? "Pending" : $sq_payment_info['clearance_status'];
	if($payment_mode=="Cash"){ $clearance_status = ""; }

	begin_t();
	$sq_income = mysqlQuery("update other_income_payment_master set financial_year_id='$financial_year_id', payment_amount='$payment_amount', payment_date='$payment_date', payment_mode='$payment_mode', bank_name='$bank_name', transaction_id='$transaction_id', bank_id='$bank_id', clearance_status='$clearance_status' where payment_id='$payment_id'");
	if($sq_income){

		//Finance Update
		$this->finance_update($sq_payment_info, $clearance_status,$row_spec);

		//Bank and Cash Book Save
		$this->bank_cash_book_update($clearance_status);
		if($payment_old_value != $payment_amount){

			global $transaction_master;
			$year1 = explode("-", $payment_date);
			$yr1 = $year1[0];
			$f_income_id = get_other_income_payment_id($sq_info['income_id'],$yr1);
		
			$trans_id = $f_income_id.' : '.$sq_info['receipt_from'];
			$transaction_master->updated_entries('Other Income',$sq_info['income_id'],$trans_id,$payment_old_value,$payment_amount);
		}

		if($GLOBALS['flag']){
			commit_t();
			echo "Income has been successfully updated.";
			exit;
		}

	}
	else{
		rollback_t();
		echo "error--Income not updated!";
		exit;
	}

}


public function finance_update($sq_payment_info, $clearance_status1,$row_spec)
{
	$payment_id = $_POST['payment_id'];
	$income_type_id = $_POST['income_type_id'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_old_value =  $_POST['payment_old_value'];
	$branch_admin_id = $_SESSION['branch_admin_id'];

	$sq_income_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$income_type_id'"));

	$payment_date1 = date('Y-m-d', strtotime($payment_date));

	global $transaction_master;//Getting New cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = $sq_bank['ledger_id'];
		$type='BANK RECEIPT';
     } 

	if($payment_amount1 < $payment_old_value)
	{
		$supp_amount= $payment_old_value - $payment_amount1;
		////////Supplier Amount//////   
	    $module_name = "Other Income Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $supp_amount;
	    $payment_date = $payment_date1;
	    $payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $income_type_id;
	    $payment_side = "Debit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Old Payment Amount///////
	    $module_name = "Other Income Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_old_value;
	    $payment_date = $payment_date1;
	    $payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
	    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Payment Amount///////
	    $module_name = "Other Income Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date1;
	    $payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $pay_gl;
	    $payment_side = "Debit";
	    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
	else if($payment_amount1 > $payment_old_value)
	{
		$supp_amount = $payment_amount1 - $payment_old_value;
		////////Supplier Amount//////   
	    $module_name = "Other Income Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $supp_amount;
	    $payment_date = $payment_date1;
	    $payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $income_type_id;
	    $payment_side = "Credit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Old Payment Amount///////
	    $module_name = "Other Income Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_old_value;
	    $payment_date = $payment_date1;
	    $payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
	    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Payment Amount///////
	    $module_name = "Other Income Payment";
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date1;
	    $payment_particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount1,$transaction_id);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $pay_gl;
	    $payment_side = "Debit";
	    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
	else{
		//Do Nothing
	}
}

public function bank_cash_book_update($clearance_status1)
{
	global $bank_cash_book_master;

	$payment_id = $_POST['payment_id'];
	$income_type_id = $_POST['income_type_id'];
	$payment_amount = $_POST['payment_amount'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];

	$payment_date1 = date('Y-m-d', strtotime($payment_date));
	$sq_income_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$income_type_id'"));
	
	$module_name = "Other Income Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date1;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_other_income_particular($payment_mode, $payment_date1, $sq_income_ledger['ledger_name'], $payment_amount,$transaction_id);
	$clearance_status = $clearance_status1;
	$payment_side = "Debit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);
	
}

}
?>
<?php
$flag = true;
class vendor_payment_master{

public function vendor_payment_save()
{
	$vendor_type = $_POST['vendor_type'];
	$vendor_type_id = $_POST['vendor_type_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$branch_admin_id = $_POST['branch_admin_id'];
    $emp_id = $_POST['emp_id'];
	$bank_id = $_POST['bank_id'];
	$payment_evidence_url = $_POST['payment_evidence_url'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$created_at = date('Y-m-d H:i');

	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$financial_year_id = $_SESSION['financial_year_id']; 

	begin_t();

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from vendor_advance_master"));
	$payment_id = $sq_max['max'] + 1;

	$sq_payment = mysqlQuery("insert into vendor_advance_master (payment_id, financial_year_id, branch_admin_id, emp_id, vendor_type, vendor_type_id, payment_date, payment_amount, payment_mode, bank_name, transaction_id, remark, bank_id, payment_evidence_url, clearance_status, created_at) values ('$payment_id', '$financial_year_id', '$branch_admin_id', '$emp_id', '$vendor_type', '$vendor_type_id', '$payment_date', '$payment_amount', '$payment_mode', '$bank_name', '$transaction_id', '', '$bank_id', '$payment_evidence_url', '$clearance_status', '$created_at') ");
	if(!$sq_payment){
		rollback_t();
		echo "error--Sorry,Supplier Advance not saved!";
		exit;
	}
	else{
		$sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type' and group_sub_id='105'"));
		$ledger_id = $sq_vendor['ledger_id'];
		$sq_update = mysqlQuery("update vendor_advance_master set ledger_id='$ledger_id' where payment_id='$payment_id'");
		$vendor_name = get_vendor_name($vendor_type,$vendor_type_id);
		$vendor_name = addslashes($vendor_name);

		//Finance Save
		$this->finance_save($payment_id,$ledger_id,$vendor_name,$branch_admin_id);

		//Bank and Cash Book Save
		$this->bank_cash_book_save($payment_id,$vendor_name,$branch_admin_id);

		if($GLOBALS['flag']){
			commit_t();
	    	echo "Supplier Advance has been successfully saved"; 
			exit;	
		}
	}
}
public function finance_save($payment_id,$ledger_id,$vendor_name,$branch_admin_id)
{
	$row_spec = 'purchase advance';
	$vendor_type = $_POST['vendor_type'];
	$payment_date1 = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];	
	$bank_id = $_POST['bank_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date1));
	$year1 = explode("-", $payment_date);
	$yr1 = $year1[0];

	global $transaction_master;

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20;  $type='CASH PAYMENT';}
    else{
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    } 

	//////Payment Amount///////
    $module_name = $vendor_type;
    $module_entry_id = $payment_id;
    $transaction_id = $transaction_id1;
    $payment_amount = $payment_amount1;
    $payment_date = $payment_date;
    $payment_particular = get_advance_purchase_particular($vendor_name,$payment_mode,$payment_date1,$bank_id,$transaction_id);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $gl_id = $pay_gl;
    $payment_side = "Credit";
    $clearance_status = ($payment_mode!="Cash") ? "Pending" : "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

    ////////Supplier Amount//////
    $module_name = $vendor_type;
    $module_entry_id = $payment_id;
    $transaction_id = $transaction_id1;
    $payment_amount = $payment_amount1;
    $payment_date = $payment_date;
    $payment_particular = get_advance_purchase_particular($vendor_name,$payment_mode,$payment_date1,$bank_id,$transaction_id);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $gl_id = $ledger_id;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
}

public function bank_cash_book_save($payment_id,$vendor_name,$branch_admin_id)
{
	$payment_date1 = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date1));

	global $bank_cash_book_master;
	
	$module_name = "Vendor Advance Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_advance_purchase_particular($vendor_name,$payment_mode,$payment_date1,$bank_id,$transaction_id);
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type,$branch_admin_id);
	
}

function payment_delete(){
	
	global $delete_master,$transaction_master,$bank_cash_book_master;
	$payment_id = $_POST['payment_id'];
	$branch_admin_id = $_SESSION['branch_admin_id'];
	$deleted_date = date('Y-m-d');
	$row_spec = "purchase advance";
	
	$sq_advance = mysqli_fetch_assoc(mysqlQuery("select * from vendor_advance_master where payment_id='$payment_id'"));
	$payment_mode = $sq_advance['payment_mode'];
	$payment_amount = $sq_advance['payment_amount'];
	$payment_date = $sq_advance['payment_date'];
	$transaction_id = $sq_advance['transaction_id'];
	$bank_id = $sq_advance['bank_id'];
	$bank_name = $sq_advance['bank_name'];
	$vendor_type = $sq_advance['vendor_type'];
	$vendor_type_id = $sq_advance['vendor_type_id'];

	$sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type' and group_sub_id='105'"));
	$ledger_id = $sq_vendor['ledger_id'];
	$vendor_name = get_vendor_name($vendor_type,$vendor_type_id);
	$vendor_name = addslashes($vendor_name);
    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20;  $type='CASH PAYMENT';}
    else{
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    } 

	$year1 = explode("-", $payment_date);
    $yr1 = $year1[0];
	$vendor_type_val = get_vendor_name($vendor_type, $vendor_type_id);
	$cust_name = addslashes($vendor_type_val).' ('.$vendor_type.')';

	$trans_id = get_puradv_payment_id($payment_id,$yr1).' : '.$cust_name;
	$transaction_master->updated_entries('PrePurchase Advances',$payment_id,$trans_id,$payment_amount,0);

	$delete_master->delete_master_entries('Supplier Advances','PrePurchase Advance',$payment_id,$payment_id,$vendor_name.'('.$vendor_type.')',$sq_advance['payment_amount']);

	//////Payment Amount///////
    $module_name = $vendor_type;
    $module_entry_id = $payment_id;
    $transaction_id = $transaction_id;
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = get_advance_purchase_particular($vendor_name,$payment_mode,$deleted_date,$bank_id,$transaction_id);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $old_gl_id = $gl_id = $pay_gl;
    $payment_side = "Credit";
    $clearance_status = ($payment_mode!="Cash") ? "Pending" : "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec, $ledger_particular,$type);

    ////////Supplier Amount//////
    $module_name = $vendor_type;
    $module_entry_id = $payment_id;
    $transaction_id = $transaction_id;
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = get_advance_purchase_particular($vendor_name,$payment_mode,$deleted_date,$bank_id,$transaction_id);
    $ledger_particular = get_ledger_particular('By','Cash/Bank');
    $old_gl_id = $gl_id = $ledger_id;
    $payment_side = "Debit";
    $clearance_status = "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec, $ledger_particular,$type);

	//Bank cash book
	$module_name = "Vendor Advance Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_advance_purchase_particular($vendor_name,$payment_mode,$payment_date,$bank_id,$transaction_id);
	$clearance_status = $clearance_status;
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type,$branch_admin_id);

	$sq_up1 = mysqlQuery("update vendor_advance_master set payment_amount = '0', delete_status = '1' where payment_id='$payment_id'");
	if($sq_up1){
		echo 'Entry deleted successfully!';
		exit;
	}
}

public function vendor_payment_update()
{
	$payment_id = $_POST['payment_id'];
	$vendor_type = $_POST['vendor_type'];
	$vendor_type_id = $_POST['vendor_type_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_evidence_url = $_POST['payment_evidence_url'];
	$ledger_id = $_POST['ledger_id'];
	$payment_old_value = $_POST['payment_old_value'];

	$payment_date = date('Y-m-d', strtotime($payment_date));

	$financial_year_id = $_SESSION['financial_year_id'];

	$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from vendor_advance_master where payment_id='$payment_id'"));

	$clearance_status = ($sq_payment_info['payment_mode']=='Cash' && $payment_mode!="Cash") ? "Pending" : $sq_payment_info['clearance_status'];
	if($payment_mode=="Cash"){ $clearance_status = ""; }

	begin_t();

	$sq_payment = mysqlQuery("update vendor_advance_master set financial_year_id='$financial_year_id', vendor_type='$vendor_type', vendor_type_id='$vendor_type_id', payment_date='$payment_date', payment_amount='$payment_amount', payment_mode='$payment_mode', bank_name='$bank_name', transaction_id='$transaction_id', bank_id='$bank_id', payment_evidence_url='$payment_evidence_url', clearance_status='$clearance_status' where payment_id='$payment_id' ");
	
	$vendor_name = get_vendor_name($vendor_type,$vendor_type_id);	

	if(!$sq_payment){
		rollback_t();
		echo "error--Sorry, Supplier Advance not updated!";
		exit;
	}
	else{

		//Finance update
		$this->finance_update($sq_payment_info, $clearance_status,$vendor_name);

		//Bank and Cash Book update
		$this->bank_cash_book_update($clearance_status,$vendor_name);

		if(floatval($payment_old_value) != floatval($payment_amount)){

			global $transaction_master;
			$year1 = explode("-", $payment_date);
			$yr1 = $year1[0];
			$cust_name =addslashes($vendor_name).' ('.$vendor_type.')';
		
			$trans_id = get_puradv_payment_id($payment_id,$yr1).' : '.$cust_name;
			$transaction_master->updated_entries('PrePurchase Advances',$payment_id,$trans_id,$payment_old_value,$payment_amount);
		}

		if($GLOBALS['flag']){
			commit_t();
	    	echo "Supplier Advance has been successfully updated.";
			exit;	
		}
		
	}
}

public function finance_update($sq_payment_info, $clearance_status1,$vendor_name)
{
	$row_spec ='purchase advance';
	$payment_id = $_POST['payment_id'];
	$vendor_type = $_POST['vendor_type'];
	$payment_date = $_POST['payment_date'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$ledger_id = $_POST['ledger_id'];
	$payment_old_value = $_POST['payment_old_value'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	
	$sq_advance = mysqli_fetch_assoc(mysqlQuery("select * from vendor_advance_master where payment_id='$payment_id'"));
	$branch_admin_id = $sq_advance['branch_admin_id'];
	global $transaction_master;
	
    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20;  $type='CASH PAYMENT';}
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    } 
	//////Payment Amount///////
	$module_name = $vendor_type;
	$module_entry_id = $payment_id;
	$transaction_id = $transaction_id1;
	$payment_amount = $payment_old_value;
	$payment_date = $payment_date;
	$payment_particular = get_advance_purchase_particular($vendor_name,$payment_mode,$payment_date,$bank_id,$transaction_id);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$gl_id = $pay_gl;
	$payment_side = "Debit";
	$clearance_status = ($payment_mode!="Cash") ? "Pending" : "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	// ////////Balance Amount//////
	$module_name = $vendor_type;
	$module_entry_id = $payment_id;
	$transaction_id = $transaction_id1;
	$payment_amount = $payment_old_value;
	$payment_date = $payment_date;
	$payment_particular = get_advance_purchase_particular($vendor_name,$payment_mode,$payment_date,$bank_id,$transaction_id);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$gl_id = $ledger_id;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);  

}

public function bank_cash_book_update($clearance_status,$vendor_name)
{
	$payment_id = $_POST['payment_id'];
	$vendor_type = $_POST['vendor_type'];
	$vendor_type_id = $_POST['vendor_type_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_evidence_url = $_POST['payment_evidence_url'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$sq_advance = mysqli_fetch_assoc(mysqlQuery("select * from vendor_advance_master where payment_id='$payment_id'"));
	$branch_admin_id = $sq_advance['branch_admin_id'];

	global $bank_cash_book_master;
	
	$module_name = "Vendor Advance Payment";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_advance_purchase_particular($vendor_name,$payment_mode,$payment_date,$bank_id,$transaction_id);
	$clearance_status = $clearance_status;
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type,$branch_admin_id);
}

}
?>
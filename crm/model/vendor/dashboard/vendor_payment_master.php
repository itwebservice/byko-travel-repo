<?php
$flag = true;
class vendor_payment_master{

public function vendor_payment_save()
{
	$row_spec='purchase';
	$vendor_type = $_POST['vendor_type'];
	$vendor_type_id = $_POST['vendor_type_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$advance_nullify = $_POST['advance_nullify'];
	$total_payment_amount = $_POST['total_payment_amount'];
	$total_purchase = $_POST['total_purchase'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$branch_admin_id = $_POST['branch_admin_id'];
    $emp_id = $_POST['emp_id'];
	$bank_id = $_POST['bank_id'];
	$payment_evidence_url = $_POST['payment_evidence_url'];

	$payment_amount_arr = $_POST['payment_amount_arr'];
	$purchase_type_arr = $_POST['purchase_type_arr'];
	$purchase_id_arr = $_POST['purchase_id_arr'];
	$estimate_id_arr = $_POST['estimate_id_arr'];
	$payment_date = date('Y-m-d', strtotime($payment_date));
	$created_at = date('Y-m-d H:i');

	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";

	$financial_year_id = $_SESSION['financial_year_id'];

	begin_t();

	for($i=0;$i<sizeof($purchase_type_arr);$i++){
		$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from vendor_payment_master"));
		$payment_id = $sq_max['max'] + 1;

		$vendor_type_val = get_vendor_name($vendor_type, $vendor_type_id);
		$estimate_type_val = get_estimate_type_name($purchase_type_arr[$i], $purchase_id_arr[$i]);
		$yr = explode("-", $created_at);
		$year = $yr[0];
		$estimate_id_full = get_vendor_estimate_id($estimate_id_arr[$i],$year)." : ".$vendor_type_val."(".$vendor_type.") : ".$estimate_type_val;

		$sq_payment = mysqlQuery("insert into vendor_payment_master (payment_id,estimate_id, financial_year_id, branch_admin_id, emp_id, vendor_type, vendor_type_id,estimate_type,estimate_type_id, payment_date, payment_amount, payment_mode, bank_name, transaction_id, remark, bank_id, payment_evidence_url, clearance_status, created_at) values ('$payment_id', '$estimate_id_arr[$i]','$financial_year_id', '$branch_admin_id', '$emp_id', '$vendor_type', '$vendor_type_id', '$purchase_type_arr[$i]', '$purchase_id_arr[$i]', '$payment_date', '$payment_amount_arr[$i]', '$payment_mode', '$bank_name', '$transaction_id', '', '$bank_id', '$payment_evidence_url', '$clearance_status', '$created_at') ");
		
		$payment_date = date('Y-m-d', strtotime($payment_date));
		$year1 = explode("-", $payment_date);
		$yr1 =$year1[0];
		if($payment_mode != 'Debit Note' && $payment_mode != 'Advance'){
			//Bank and Cash Book Save
			$this->bank_cash_book_save($payment_id,$purchase_type_arr[$i],$payment_amount_arr[$i],get_vendor_payment_id($purchase_id_arr[$i],$yr1),$purchase_id_arr[$i],$branch_admin_id,$estimate_id_full);
		}
	}
	
	//Update supplier debit note balance
	// $payment_amount1 = $payment_amount;
	// $sq_debit_note = mysqlQuery("select * from debit_note_master where vendor_type='$vendor_type' and vendor_type_id='$vendor_type_id'");	
	// while($row_debit = mysqli_fetch_assoc($sq_debit_note)) 
	// {	
	// 	if($row_debit['payment_amount'] <= $payment_amount1 && $payment_amount1 != '0'){		
	// 		$payment_amount1 = $payment_amount1 - $row_debit['payment_amount'];
	// 		$temp_amount = 0;
	// 	}
	// 	else{
	// 		$temp_amount = $row_debit['payment_amount'] - $payment_amount1;
	// 		$payment_amount1 = 0;
	// 	}
	// 	$sq_debit = mysqlQuery("update debit_note_master set payment_amount ='$temp_amount' where id='$row_debit[id]'");
	// }

	if(!$sq_payment){
		rollback_t();
		echo "error--Sorry,Supplier Payment not saved!";
		exit;
	}
	else{

		if($payment_mode != 'Debit Note' && $payment_mode != 'Advance'){
			//Finance Save
			$this->finance_save($payment_id,$row_spec,$branch_admin_id);
		}

		if($GLOBALS['flag']){
			commit_t();
	    	echo "Supplier Payment has been successfully saved.";
			exit;	
		}
		
	}
}
public function vendor_payment_delete(){
	
	global $delete_master,$transaction_master,$bank_cash_book_master;
	$branch_admin_id = $_SESSION['branch_admin_id'];
	$payment_id = $_POST['payment_id'];
	$deleted_date = date('Y-m-d');
	$row_spec = "purchase";

	$sq_estimate_info = mysqli_fetch_assoc(mysqlQuery("select * from vendor_payment_master where payment_id='$payment_id'"));
    $estimate_id = $sq_estimate_info['estimate_id'];
    $estimate_type = $sq_estimate_info['estimate_type'];
    $estimate_type_id = $sq_estimate_info['estimate_type_id'];
    $vendor_type = $sq_estimate_info['vendor_type'];
    $vendor_type_id = $sq_estimate_info['vendor_type_id'];
    $payment_date = $sq_estimate_info['payment_date'];
    $payment_amount = $sq_estimate_info['payment_amount'];
	$payment_old_mode = $payment_mode = $sq_estimate_info['payment_mode'];
	$bank_name = $sq_estimate_info['bank_name'];
	$transaction_id = $sq_estimate_info['transaction_id'];
	$bank_id = $sq_estimate_info['bank_id'];
	$clearance_status = $sq_estimate_info['clearance_status'];
	$created_at = $sq_estimate_info['created_at'];
	$canc_status = $sq_estimate_info['status'];
	$payment_amount1 = 0;

	$vendor_type_val = get_vendor_name($vendor_type, $vendor_type_id);
	$estimate_type_val = get_estimate_type_name($estimate_type, $estimate_type_id);
	$yr = explode("-", $created_at);
	$year = $yr[0];
	$estimate_id_full = get_vendor_estimate_id($estimate_id,$year)." : ".$vendor_type_val."(".$vendor_type.") : ".$estimate_type_val;

	$vendor_name = get_vendor_name($vendor_type,$vendor_type_id);
	$vendor_name = addslashes($vendor_name);
	$estimate_type_val = get_estimate_type_name($sq_estimate_info['estimate_type'], $sq_estimate_info['estimate_type_id']);
	$year1 = explode("-", $payment_date);
    $yr1 = $year1[0];
	$vendor_type_val = get_vendor_name($vendor_type, $vendor_type_id);
	$cust_name = addslashes($vendor_type_val).' ('.$vendor_type.')';

	$trans_id = get_vendor_payment_id($payment_id,$year).' : '.$cust_name;
	$transaction_master->updated_entries('Purchase Payment',$payment_id,$trans_id,$payment_amount,0);
	
	
	$delete_master->delete_master_entries('Payment('.$payment_mode.')',$estimate_type,$payment_id,$estimate_type_val,$vendor_name,$payment_amount);

    //Getting New cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH PAYMENT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    }

    //Getting supplier Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type' and group_sub_id='105'"));
	$supplier_gl = $sq_cust['ledger_id'];
	
	//////Payment Amount///////
	$module_name = $vendor_type;
	$module_entry_id = $payment_id;
	$transaction_id = $transaction_id;
	$payment_amount = 0;
	$payment_date = $payment_date;
	$payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, 0, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$old_gl_id = $gl_id = $pay_gl;
	$payment_side = "Credit";
	$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,'',$type);

	//////Supplier Amount///////
	$module_name = $vendor_type;
	$module_entry_id = $payment_id;
	$transaction_id = $transaction_id;
	$payment_amount = 0;
	$payment_date = $payment_date;
	$payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, 0, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$old_gl_id = $gl_id = $supplier_gl;
	$payment_side = "Debit";
	$clearance_status = ''; 
	$transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id, '',$payment_side, $clearance_status, $row_spec,'',$type);

	//Bank cash book
	$module_name = $vendor_type;
	$module_entry_id = $payment_id;
	$payment_date = $deleted_date;
	$payment_amount = 0;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	$clearance_status = $clearance_status;
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type,$branch_admin_id);

	$sq_up1 = mysqlQuery("update vendor_payment_master set payment_amount = '0', delete_status = '1' where payment_id='$payment_id'");
	if($sq_up1){
		echo 'Entry deleted successfully!';
		exit;
	}
}
public function finance_save($payment_id,$row_spec,$branch_admin_id)
{
	$vendor_type = $_POST['vendor_type'];
	$vendor_type_id = $_POST['vendor_type_id'];
	$advance_nullify = $_POST['advance_nullify'];
	$total_payment_amount = $_POST['total_payment_amount'];
	$total_purchase = $_POST['total_purchase'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];	
	$bank_id = $_POST['bank_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year1 = explode("-", $payment_date);
	$yr1 =$year1[0];
	$estimate_id_full = '';
	global $transaction_master;

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH PAYMENT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
    } 

    //Getting supplier Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type' and group_sub_id='105'"));
	$supplier_gl = $sq_cust['ledger_id'];

	if($total_payment_amount > $total_purchase){
		$balance_amount = $total_payment_amount - $total_purchase;
		////////Supplier Amount//////   
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $total_purchase;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $total_purchase, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $supplier_gl;
	    $payment_side = "Debit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		
		if($advance_nullify!=0){
			//////Advance Nullify Amount///////
			$module_name = $vendor_type;
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $advance_nullify;
			$payment_date = $payment_date;
			$payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $advance_nullify, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $supplier_gl;
			$payment_side = "Credit";
			$clearance_status = ''; 
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
	    //////Payment Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $advance_nullify, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
		$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

	    //////Advance Nullify Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $balance_amount;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $balance_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $supplier_gl;
	    $payment_side = "Debit";
	    $clearance_status = ''; 
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
	else if($total_payment_amount < $total_purchase){
		////////Supplier Amount//////   
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $total_payment_amount;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $total_payment_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $supplier_gl;
	    $payment_side = "Debit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		
		if($advance_nullify!=0){
			//////Advance Nullify Amount///////
			$module_name = $vendor_type;
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $advance_nullify;
			$payment_date = $payment_date;
			$payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $advance_nullify, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $supplier_gl;
			$payment_side = "Credit";
			$clearance_status = ''; 
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
	    //////Payment Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_amount1, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
		$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);	
	}
	else
	{
		////////Supplier Amount//////   
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $total_payment_amount;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $total_payment_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $supplier_gl;
	    $payment_side = "Debit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		
		if($advance_nullify!=0){
			//////Advance Nullify Amount///////
			$module_name = $vendor_type;
			$module_entry_id = $payment_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $advance_nullify;
			$payment_date = $payment_date;
			$payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $advance_nullify, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $supplier_gl;
			$payment_side = "Credit";
			$clearance_status = ''; 
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
	    //////Payment Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_amount1, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,'');
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
		$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
}

public function bank_cash_book_save($payment_id,$purchase_type,$pay_amount,$purchase_id,$suppl_type_id,$branch_admin_id,$estimate_id_full)
{
		$vendor_type = $_POST['vendor_type'];
		$vendor_type_id = $_POST['vendor_type_id'];
		$payment_date1 = $_POST['payment_date'];
		$payment_amount = $_POST['payment_amount'];
		$payment_mode = $_POST['payment_mode'];
		$bank_name = $_POST['bank_name'];
		$transaction_id = $_POST['transaction_id'];
		$canc_status = isset($_POST['canc_status']) ? $_POST['canc_status'] : '';
		$bank_id = $_POST['bank_id'];

		if($payment_mode != 'Debit Note'){
			
			$payment_date = date('Y-m-d', strtotime($payment_date1));
			$year1 = explode("-", $payment_date);
			$yr1 = $year1[0];

			global $bank_cash_book_master;
			
			$module_name = $vendor_type;
			$module_entry_id = $payment_id;
			$payment_date = $payment_date;
			$payment_amount = $pay_amount;
			$payment_mode = $payment_mode;
			$bank_name = $bank_name;
			$transaction_id = $transaction_id;
			$bank_id = $bank_id;
			$particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date1, $pay_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
			$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
			$payment_side = "Credit";
			$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

			$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type,$branch_admin_id);
		}
}

public function vendor_payment_update()
{
	$payment_id = $_POST['payment_id'];
	$vendor_type = $_POST['vendor_type'];
	$vendor_type_id = $_POST['vendor_type_id'];
	$estimate_type = $_POST['estimate_type'];
	$estimate_type_id = $_POST['estimate_type_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_evidence_url = $_POST['payment_evidence_url'];
	$payment_old_value = $_POST['payment_old_value'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$financial_year_id = $_SESSION['financial_year_id'];

	$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from vendor_payment_master where payment_id='$payment_id'")); 
	$clearance_status = ($sq_payment_info['payment_mode']=='Cash' && $payment_mode!="Cash") ? "Pending" : $sq_payment_info['clearance_status'];
	if($payment_mode=="Cash"){ $clearance_status = ""; }

	$vendor_type_val = get_vendor_name($vendor_type, $vendor_type_id);
	$estimate_type_val = get_estimate_type_name($estimate_type, $estimate_type_id);
	$yr = explode("-", $payment_date);
	$year = $yr[0];
	$estimate_id_full = get_vendor_estimate_id($sq_payment_info['estimate_id'],$year)." : ".$vendor_type_val."(".$vendor_type.") : ".$estimate_type_val;

	begin_t();

	$sq_payment = mysqlQuery("update vendor_payment_master set financial_year_id='$financial_year_id', vendor_type='$vendor_type', vendor_type_id='$vendor_type_id', estimate_type='$estimate_type', estimate_type_id='$estimate_type_id', payment_date='$payment_date', payment_amount='$payment_amount', payment_mode='$payment_mode', bank_name='$bank_name', transaction_id='$transaction_id', bank_id='$bank_id', payment_evidence_url='$payment_evidence_url', clearance_status='$clearance_status' where payment_id='$payment_id' ");
	if(!$sq_payment){
		rollback_t();
		echo "error--Sorry, Supplier Payment not updated!";
		exit;
	}
	else{

		if($payment_mode != 'Debit Note' && $payment_mode != 'Advance'){
			//Finance update
			$this->finance_update($sq_payment_info, $clearance_status,$estimate_id_full);
			//Bank and Cash Book update
			$this->bank_cash_book_update($clearance_status,$estimate_id_full);
		}
		global $transaction_master;
		if(floatval($payment_old_value) != floatval($payment_amount)){

			$yr = explode("-", $payment_date);
			$year = $yr[0];
			$vendor_type_val = get_vendor_name($vendor_type, $vendor_type_id);
			$cust_name = addslashes($vendor_type_val).' ('.$vendor_type.')';
		
			$trans_id = get_vendor_payment_id($payment_id,$year).' : '.$cust_name;
			$transaction_master->updated_entries('Purchase Payment',$payment_id,$trans_id,$payment_old_value,$payment_amount);
		}
		if($GLOBALS['flag']){
			commit_t();
	    	echo "Supplier Payment has been successfully updated.";
			exit;	
		}
		
	}
}

public function finance_update($sq_payment_info, $clearance_status1,$estimate_id_full)
{
	$row_spec = 'purchase';
	$payment_id = $_POST['payment_id'];
	$vendor_type = $_POST['vendor_type'];
	$vendor_type_id = $_POST['vendor_type_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$payment_old_value = $_POST['payment_old_value'];
	$payment_old_mode =  $_POST['payment_old_mode'];
	$branch_admin_id = $_SESSION['branch_admin_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year1 = explode("-", $payment_date);
	$yr1 =$year1[0];
	global $transaction_master;
	$sq_payment = mysqli_fetch_assoc(mysqlQuery("select status from vendor_payment_master where payment_id='$payment_id'"));
	$canc_status = $sq_payment['status'];

    //Getting New cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl_new = 20; $type='CASH PAYMENT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl_new = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
		$type='BANK PAYMENT';
     } 

    //Getting old cash/Bank Ledger
    if($payment_old_mode == 'Cash') {  $pay_gl_old = 20; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl_old = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
     }
      
    //Getting supplier Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type' and group_sub_id='105'"));
	$supplier_gl = $sq_cust['ledger_id'];

	if($payment_amount1 < $payment_old_value)
	{
		$supp_amount= $payment_old_value - $payment_amount1;
		$pay_gl = ($payment_mode != $payment_old_mode) ? $pay_gl_new : $pay_gl_old; 
		////////Supplier Amount//////   
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $supp_amount;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $supp_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	    $gl_id = $supplier_gl;
	    $payment_side = "Credit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,'',$type);

	    //////Payment Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_old_value;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_old_value, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	    $gl_id = $pay_gl;
	    $payment_side = "Debit";
	    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,'',$type);

	    //////Payment Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_amount1;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_amount1, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	    $gl_id = $pay_gl;
	    $payment_side = "Credit";
	    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,'',$type);
	}
	else if($payment_amount1 > $payment_old_value)
	{
		$supp_amount = $payment_amount1 - $payment_old_value;
		$pay_gl = ($payment_mode != $payment_old_mode) ? $pay_gl_new : $pay_gl_old; 
		////////Supplier Amount//////   
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $supp_amount;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $supp_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	    $gl_id = $supplier_gl;
	    $payment_side = "Debit";
	    $clearance_status = "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,'',$type);

	    //////Payment Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_old_value;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_old_value, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	    $gl_id = $pay_gl;
	    $payment_side = "Debit";
	    $clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,'',$type);

	    //////Payment Amount///////
	    $module_name = $vendor_type;
	    $module_entry_id = $payment_id;
	    $transaction_id = $transaction_id1;
	    $payment_amount = $payment_old_value;
	    $payment_date = $payment_date;
	    $payment_particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_old_value, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	    $gl_id = $supplier_gl;
	    $payment_side = "Credit";
		$clearance_status = ($payment_mode=="Cheque") ? "Pending" : "";
	    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,'',$type);
	}
	else{
		//Do Nothing
	}

}

public function bank_cash_book_update($clearance_status,$estimate_id_full)
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
	$branch_admin_id = $_SESSION['branch_admin_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year1 = explode("-", $payment_date);
	$yr1 =$year1[0];
	$sq_payment = mysqli_fetch_assoc(mysqlQuery("select status from vendor_payment_master where payment_id='$payment_id'"));
	$canc_status = $sq_payment['status'];

	global $bank_cash_book_master;
	
	$module_name = $vendor_type;
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_purchase_paid_partucular(get_vendor_payment_id($payment_id,$yr1), $payment_date, $payment_amount, $vendor_type, $vendor_type_id,$payment_mode,$bank_id,$transaction_id,$estimate_id_full,$canc_status);
	$clearance_status = $clearance_status;
	$payment_side = "Credit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type,$branch_admin_id);
}

}
?>
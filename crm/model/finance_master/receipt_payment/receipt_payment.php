<?php 
class receipt_payment{

public function save()
{
	$receipt_type = $_POST['receipt_type'];
	$ledger_id = $_POST['ledger_id'];
	$bank_id = $_POST['bank_id'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
    $payment_amount = $_POST['payment_amount'];
    $payment_date = $_POST['payment_date'];
    $payment_mode = $_POST['payment_mode'];
    $payment_evidence_url = $_POST['payment_evidence_url'];
    $narration = addslashes($_POST['narration']);
    $branch_admin_id = $_POST['branch_admin_id'];
    $emp_id = $_POST['emp_id'];

	$financial_year_id = $_SESSION['financial_year_id'];

	$created_at = date('Y-m-d');
    $payment_date = get_date_db($payment_date);

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(id) as max from receipt_payment_master"));
	$entry_id = $sq_max['max'] + 1;

	begin_t();
	$sq_bank = mysqlQuery("INSERT INTO `receipt_payment_master`(`id`, `receipt_type`, `ledger_id`, `payment_date`, `payment_mode`, `payment_amount`, `bank_name`, `transaction_id`,`bank_id`, `narration`, `url`, `emp_id`, `branch_admin_id`, `financial_year_id`, `created_at`) VALUES ('$entry_id','$receipt_type','$ledger_id','$payment_date','$payment_mode','$payment_amount','$bank_name','$transaction_id','$bank_id','$narration','$payment_evidence_url','$emp_id','$branch_admin_id','$financial_year_id','$created_at')");

    if ($payment_mode == "Cheque" || $payment_mode == "Credit Card") {
        $clearance_status = "Pending";
    } else {
        $clearance_status = "";
    }
    //Finance Save
    $this -> finance_save($entry_id);
    $this -> bank_cash_book_save($clearance_status,$entry_id);

	if($sq_bank){
		commit_t();
		echo $receipt_type." Entry saved.";
		exit;
	}
	else{
		rollback_t();
		echo "error--Sorry,".$receipt_type." Entry not saved!";
		exit;
	}
}
function delete(){
    
	global $delete_master,$transaction_master,$bank_cash_book_master;
	$entry_id = $_POST['entry_id'];
	$branch_admin_id = $_SESSION['branch_admin_id'];
	$deleted_date = date('Y-m-d');

	$sq_rp = mysqli_fetch_assoc(mysqlQuery("select * from receipt_payment_master where id='$entry_id'"));
	$row_spec = $receipt_type = $sq_rp['receipt_type'];
	$payment_mode = $sq_rp['payment_mode'];
	$payment_amount = $sq_rp['payment_amount'];
	$payment_date = $sq_rp['payment_date'];
	$bank_id = $sq_rp['bank_id'];
	$ledger_id = $sq_rp['ledger_id'];
    $narration = $sq_rp['narration'];
	$sq_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$ledger_id'"));
    
    $yr = explode("-", $payment_date);
    $trans_id = get_receipt_payment_id($entry_id,$receipt_type,$yr[0]).' : '.$sq_ledger['ledger_name'];
    $transaction_master->updated_entries($receipt_type,$entry_id,$trans_id,$payment_amount,0);

    //Getting cash/Bank Ledger
    if ($payment_mode == 'Cash') {
        $pay_gl = 20;
        $type = 'CASH ';
    } else {
        $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
        $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
        $type = 'BANK ';
    }
    $pr = ($receipt_type == 'Receipt') ? 'RECEIPT' : 'PAYMENT';
    $type .= $pr;
    
	$delete_master->delete_master_entries($receipt_type.'('.$payment_mode.')',$receipt_type,$entry_id,$entry_id,$sq_ledger['ledger_name'],$sq_rp['payment_amount']);

    //Selected ledger
	$module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $narration;
    $ledger_particular = get_ledger_particular('By', 'Cash/Bank');
    $old_gl_id = $gl_id = $ledger_id;
    $payment_side = ($receipt_type == 'Receipt') ? "Credit" : "Debit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec, $ledger_particular,$type);

    //Payment mode
	$module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $transaction_id = "";
    $payment_amount = 0;
    $payment_date = $deleted_date;
    $payment_particular = $narration;
    $ledger_particular = get_ledger_particular('By', 'Cash/Bank');
    $old_gl_id = $gl_id = $pay_gl;
    $payment_side = ($receipt_type == 'Receipt') ? "Debit" : "Credit";
    $clearance_status = "";
    $transaction_master->transaction_update($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular,$old_gl_id, $gl_id,'', $payment_side, $clearance_status, $row_spec, $ledger_particular,$type);

    // Bank cash book
    $module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $payment_date = $payment_date;
    $payment_amount = $payment_amount;
    $payment_mode = $payment_mode;
    $bank_name = $sq_rp['bank_name'];
    $transaction_id = $transaction_id;
    $bank_id = $bank_id;
    $particular = $narration;
    $clearance_status = ($payment_mode == "Cheque" || $payment_mode == 'Credit Card') ? "Pending" : "";
    $payment_side = ($receipt_type == 'Receipt') ? "Debit" : "Credit";
    $payment_type = ($payment_mode == "Cash") ? "Cash" : "Bank";

    $bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type,$branch_admin_id);
    $sq_delete = mysqlQuery("update receipt_payment_master set payment_amount = '0',delete_status = '1' where id='$entry_id'");
	if($sq_delete){
		echo 'Entry deleted successfully!';
		exit;
	}

}
public function finance_save($entry_id)
{
	$receipt_type = $_POST['receipt_type'];
	$branch_admin_id = $_SESSION['branch_admin_id'];
	$ledger_id = $_POST['ledger_id'];
	$bank_id = $_POST['bank_id'];
	$transaction_id = $_POST['transaction_id'];
    $payment_amount = $_POST['payment_amount'];
    $payment_date = $_POST['payment_date'];
    $payment_mode = $_POST['payment_mode'];
    $narration = addslashes($_POST['narration']);

	$row_spec = $receipt_type;
	$payment_date = get_date_db($payment_date);
	global $transaction_master;

    //Getting cash/Bank Ledger
    if ($payment_mode == 'Cash') {
        $pay_gl = 20;
        $type = 'CASH ';
    } else {
        $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
        $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
        $type = 'BANK ';
    }
    $pr = ($receipt_type == 'Receipt') ? 'RECEIPT' : 'PAYMENT';
    $type .= $pr; 

    //Selected ledger
	$module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $transaction_id = "";
    $payment_amount = $payment_amount;
    $payment_date = $payment_date;
    $payment_particular = $narration;
    $ledger_particular = get_ledger_particular('By', 'Cash/Bank');
    $gl_id = $ledger_id;
    $payment_side = ($receipt_type == 'Receipt') ? "Credit" : "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

    //Payment mode
	$module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $transaction_id = "";
    $payment_amount = $payment_amount;
    $payment_date = $payment_date;
    $payment_particular = $narration;
    $ledger_particular = get_ledger_particular('By', 'Cash/Bank');
    $gl_id = $pay_gl;
    $payment_side = ($receipt_type == 'Receipt') ? "Debit" : "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
}

public function bank_cash_book_save($clearance_status,$entry_id)
{
    global $bank_cash_book_master;

    $receipt_type = $_POST['receipt_type'];
	$branch_admin_id = $_SESSION['branch_admin_id'];
	$bank_id = $_POST['bank_id'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
    $payment_amount = $_POST['payment_amount'];
    $payment_date = $_POST['payment_date'];
    $payment_mode = $_POST['payment_mode'];
    $narration = addslashes($_POST['narration']);

    $payment_date = date('Y-m-d', strtotime($payment_date));

    $module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $payment_date = $payment_date;
    $payment_amount = $payment_amount;
    $payment_mode = $payment_mode;
    $bank_name = $bank_name;
    $transaction_id = $transaction_id;
    $bank_id = $bank_id;
    $particular = $narration;
    $clearance_status = ($payment_mode == "Cheque" || $payment_mode == 'Credit Card') ? "Pending" : "";
    $payment_side = ($receipt_type == 'Receipt') ? "Debit" : "Credit";
    $payment_type = ($payment_mode == "Cash") ? "Cash" : "Bank";

    $bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);
}

public function update()
{
    $id = $_POST['entry_id'];
	$receipt_type = $_POST['receipt_type'];
	$ledger_id = $_POST['ledger_id'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
    $payment_amount = $_POST['payment_amount'];
    $old_amount = $_POST['old_amount'];
    $payment_date = get_date_db($_POST['payment_date']);
    $narration = addslashes($_POST['narration']);
    $payment_evidence_url = $_POST['payment_evidence_url'];

	begin_t();
	$sq_bank = mysqlQuery("UPDATE `receipt_payment_master` SET `payment_amount`='$payment_amount',`bank_name`='$bank_name',`transaction_id`='$transaction_id',`narration`='$narration',`url`='$payment_evidence_url' WHERE id='$id'");
    
    if($old_amount != $payment_amount){
        //Finance Save
        $this -> finance_update($id);
        $this -> bank_cash_book_update($id);

        global $transaction_master;
        $yr = explode("-", $payment_date);
        $sq_package = mysqli_fetch_assoc(mysqlQuery("select ledger_name from ledger_master where ledger_id='$ledger_id'"));
    
        $trans_id = get_receipt_payment_id($id,$receipt_type,$yr[0]).' : '.$sq_package['ledger_name'];
        $transaction_master->updated_entries($receipt_type,$id,$trans_id,$old_amount,$payment_amount);
    }

	if($sq_bank){
		commit_t();
		echo $receipt_type." Entry updated.";
		exit;
	}
	else{
		rollback_t();
		echo "error--Sorry,".$receipt_type." Entry not updated!";
		exit;
	}
}
public function finance_update($entry_id)
{
	global $transaction_master;
	$receipt_type = $_POST['receipt_type'];
	$ledger_id = $_POST['ledger_id'];
	$bank_id = $_POST['bank_id'];
	$transaction_id = $_POST['transaction_id'];
    $old_amount = $_POST['old_amount'];
    $payment_date = $_POST['payment_date'];
    $narration = addslashes($_POST['narration']);
    $branch_admin_id = $_SESSION['branch_admin_id'];
    $old_mode = $_POST['old_mode'];

    $payment_date = get_date_db($payment_date);

	$row_spec = $receipt_type;
    //Getting cash/Bank Ledger
    if ($old_mode == 'Cash') {
        $pay_gl = 20;
        $type = 'CASH ';
    } else {
        $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
        $pay_gl = isset($sq_bank['ledger_id']) ? $sq_bank['ledger_id'] : '';
        $type = 'BANK ';
    }
    $pr = ($receipt_type == 'Receipt') ? 'RECEIPT' : 'PAYMENT';
    $type .= $pr; 
    
    //Selected ledger
	$module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $transaction_id = "";
    $payment_date = $payment_date;
    $payment_particular = $narration;
    $ledger_particular = get_ledger_particular('By', 'Cash/Bank');
    $gl_id = $ledger_id;
    $payment_side = ($receipt_type == 'Receipt') ? "Debit" : "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $old_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

    //Payment mode
	$module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $transaction_id = "";
    $payment_date = $payment_date;
    $payment_particular = $narration;
    $ledger_particular = get_ledger_particular('By', 'Cash/Bank');
    $gl_id = $pay_gl;
    $payment_side = ($receipt_type == 'Receipt') ? "Credit" : "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $old_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

}
function bank_cash_book_update($entry_id)
{
    global $bank_cash_book_master;
	$receipt_type = $_POST['receipt_type'];
	$bank_id = $_POST['bank_id'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
    $payment_amount = $_POST['payment_amount'];
    $payment_date = $_POST['payment_date'];
    $narration = addslashes($_POST['narration']);
    $old_mode = $_POST['old_mode'];

    $payment_date = date('Y-m-d', strtotime($payment_date));
    $year1 = explode("-", $payment_date);
    $yr1 = $year1[0];

    $module_name = $receipt_type;
    $module_entry_id = $entry_id;
    $payment_date = $payment_date;
    $payment_amount = $payment_amount;
    $bank_name = $bank_name;
    $transaction_id = $transaction_id;
    $bank_id = $bank_id;
    $particular = $narration;
    $clearance_status = "";
    $payment_side = ($receipt_type == 'Receipt') ? "Debit" : "Credit";
    $payment_type = ($old_mode == "Cash") ? "Cash" : "Bank";

    $bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $old_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);
}
}
?>
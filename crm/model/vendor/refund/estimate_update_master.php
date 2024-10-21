<?php 
$flag = true;
class estimate_update_master{

public function estimate_update()
{
    $estimate_id = $_POST['estimate_id'];
    $cancel_amount = $_POST['cancel_amount'];
    $total_refund_amount = $_POST['total_refund_amount'];
    $branch_admin_id = $_SESSION['branch_admin_id'];
    $estimate_arr = json_encode($_POST['estimate_arr']);
    $purchase_return = $_POST['purchase_return'];

    $final_status = ($purchase_return == '1') ? 'Cancel' : '';
    $sq_estimate = mysqli_fetch_assoc(mysqlQuery("select * from vendor_estimate where estimate_id='$estimate_id' and delete_status='0'"));

    $vendor_type = $sq_estimate['vendor_type'];
    $vendor_type_id = $sq_estimate['vendor_type_id'];
    $estimate_type = $sq_estimate['estimate_type'];
    $estimate_type_id = $sq_estimate['estimate_type_id'];
    $service_tax_subtotal = $sq_estimate['service_tax_subtotal'];
    
    $sq_est = mysqlQuery("update vendor_estimate set cancel_amount='$cancel_amount', total_refund_amount='$total_refund_amount', cancel_est_flag='1',status='$final_status',cancel_estimate='$estimate_arr',purchase_return='$purchase_return' where estimate_id='$estimate_id'");

    if($sq_est){

    	//Finance Save
		$this->finance_save($vendor_type,$vendor_type_id,$service_tax_subtotal,$branch_admin_id,$estimate_type,$estimate_type_id);

    	if($GLOBALS['flag']){
    		commit_t();
    		echo "Refund Estimate saved successfully!";
    		exit;
    	}
    	else{
    		rollback_t();
    		exit;
    	}

    }
    else{
    	rollback_t();
    	echo "error--Sorry, Refund Estimate not done!";
    	exit;
    }
}

public function finance_save($vendor_type,$vendor_type_id,$service_tax_subtotal,$branch_admin_id,$estimate_type,$estimate_type_id)
{
    $row_spec = 'purchase';
    $estimate_id = $_POST['estimate_id'];
    $cancel_amount = $_POST['cancel_amount'];
    $purchase_return = $_POST['purchase_return'];

    $purchase_return = ($purchase_return == '1') ? 'Full' : 'Partial';

    $purchase_gl = get_vendor_cancelation_gl_id($vendor_type, $vendor_type_id);   
    
    $estimate_arr = json_decode(json_encode($_POST['estimate_arr']));

    $basic_cost = $estimate_arr[0]->basic_cost;
    $service_charge = $estimate_arr[0]->service_charge;
    $service_tax_subtotal = $estimate_arr[0]->service_tax_subtotal;
    $roundoff = $estimate_arr[0]->roundoff;
    $net_total = $estimate_arr[0]->net_total;
    
    //Getting supplier Ledger
    $sq_sup = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type'"));
    $supplier_gl = $sq_sup['ledger_id'];

    $created_at = date('Y-m-d H:i');
	$year1 = explode("-", $created_at);
	$yr1 =$year1[0];

    global $transaction_master;

    $sq_supplier = mysqli_fetch_assoc(mysqlQuery("select * from vendor_estimate where estimate_id='$estimate_id' and delete_status='0'"));
    $purchase_amount =  floatval($basic_cost);
    $reflections = json_decode($sq_supplier['reflections']);
    // $service_tax_subtotal = $sq_supplier['service_tax_subtotal'];
    
    ////////////purchase return/////////////
    $module_name = $vendor_type;
    $module_entry_id = $estimate_id;
    $transaction_id = "";
    $payment_amount = $purchase_amount;
    $payment_date = $created_at;
    $payment_particular = get_cancel_purchase_particular(get_vendor_estimate_id($estimate_id,$yr1),$vendor_type, $vendor_type_id,$estimate_type,$estimate_type_id,$purchase_return);
    $ledger_particular = '';
    $gl_id = $purchase_gl;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');
    
    ////////////service charge/////////////
    $module_name = $vendor_type;
    $module_entry_id = $estimate_id;
    $transaction_id = "";
    $payment_amount = $service_charge;
    $payment_date = $created_at;
    $payment_particular = get_cancel_purchase_particular(get_vendor_estimate_id($estimate_id,$yr1),$vendor_type, $vendor_type_id,$estimate_type,$estimate_type_id,$purchase_return);
    $ledger_particular = '';
    $gl_id = 117;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');

    /////////Service Charge Tax Amount////////
    $tax_ledgers = explode(',',$reflections[0]->purchase_taxes);
    $tax_amount = (sizeof($tax_ledgers) == 1) ? $service_tax_subtotal : floatval($service_tax_subtotal)/sizeof($tax_ledgers);
    for($i=0;$i<sizeof($tax_ledgers);$i++){

        $ledger = $tax_ledgers[$i];

        $module_name = $vendor_type;
        $module_entry_id = $estimate_id;
        $transaction_id = "";
        $payment_amount = $tax_amount;
        $payment_date = $created_at;
        $payment_particular = get_cancel_purchase_particular(get_vendor_estimate_id($estimate_id,$yr1),$vendor_type, $vendor_type_id,$estimate_type,$estimate_type_id,$purchase_return);
        $ledger_particular = get_ledger_particular('For',$vendor_type.' Purchase');
        $gl_id = $ledger;
        $payment_side = "Credit";
        $clearance_status = "";
        $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');
    }
    ////Roundoff Value
    $module_name = $vendor_type;
    $module_entry_id = $estimate_id;
    $transaction_id = "";
    $payment_amount = $roundoff;
    $payment_date = $created_at;
    $payment_particular = get_cancel_purchase_particular(get_vendor_estimate_id($estimate_id,$yr1),$vendor_type, $vendor_type_id,$estimate_type,$estimate_type_id,$purchase_return);
    $ledger_particular = get_ledger_particular('For',$vendor_type.' Purchase');
    $gl_id = 230;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'PURCHASE');

    ////////supplier purchase Amount//////
    $module_name = $vendor_type;
    $module_entry_id = $estimate_id;
    $transaction_id = "";
    $payment_amount = $net_total;
    $payment_date = $created_at;
    $payment_particular = get_cancel_purchase_particular(get_vendor_estimate_id($estimate_id,$yr1),$vendor_type, $vendor_type_id,$estimate_type,$estimate_type_id,$purchase_return);
    $ledger_particular = '';
    $gl_id = $supplier_gl;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');    

    ////////Cancel Amount//////
    $module_name = $vendor_type;
    $module_entry_id = $estimate_id;
    $transaction_id = "";
    $payment_amount = $cancel_amount;
    $payment_date = $created_at;
    $payment_particular = get_cancel_purchase_particular(get_vendor_estimate_id($estimate_id,$yr1),$vendor_type, $vendor_type_id,$estimate_type,$estimate_type_id,$purchase_return);
    $ledger_particular = '';
    $gl_id = 89;
    $payment_side = "Debit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND');    

    ////////supplier Cancel Amount//////
    $module_name = $vendor_type;
    $module_entry_id = $estimate_id;
    $transaction_id = "";
    $payment_amount = $cancel_amount;
    $payment_date = $created_at;
    $payment_particular = get_cancel_purchase_particular(get_vendor_estimate_id($estimate_id,$yr1),$vendor_type, $vendor_type_id,$estimate_type,$estimate_type_id,$purchase_return);
    $ledger_particular = '';
    $gl_id = $supplier_gl;
    $payment_side = "Credit";
    $clearance_status = "";
    $transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,'REFUND'); 
}

}
?>
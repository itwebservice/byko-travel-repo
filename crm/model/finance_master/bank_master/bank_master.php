<?php
$flag = true; 
class bank_master{

public function bank_master_save()
{	
	$branch_id = $_POST['branch_id'];
	$bank_name = addslashes($_POST['bank_name']);
	$account_name = addslashes($_POST['account_name']);
	$branch_name = $_POST['branch_name'];
	$address = $_POST['address'];
	$account_no = $_POST['account_no'];
	$ifsc_code = $_POST['ifsc_code'];
	$swift_code = $_POST['swift_code'];
	$account_type = $_POST['account_type'];
	$mobile_no = $_POST['mobile_no'];
	// $opening_balance = $_POST['opening_balance'];
	$active_flag = $_POST['active_flag'];
	$as_of_date = $_POST['as_of_date'];
	$op_balance = $_POST['op_balance'];
	$balance_side = $_POST['balance_side'];

	$created_at = date('Y-m-d H:i');
	$as_of_date = get_date_db($as_of_date);
	$address = addslashes($address);

	//**Starting transaction
	begin_t();

	$sq_count = mysqli_num_rows(mysqlQuery("select bank_name from bank_master where bank_name='$bank_name' and branch_name='$branch_name'"));
	if($sq_count>0){
		echo "error--Bank name already exists!";
		exit;
	}

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(bank_id) as max from bank_master"));
	$bank_id = $sq_max['max'] + 1;

	$sq_bank = mysqlQuery("insert into bank_master (bank_id,branch_id, bank_name,account_name, branch_name, address, account_no, ifsc_code, swift_code, account_type, mobile_no, op_balance,active_flag, created_at,balance_side) values ('$bank_id','$branch_id' ,'$bank_name', '$account_name','$branch_name', '$address', '$account_no', '$ifsc_code', '$swift_code', '$account_type', '$mobile_no', '$op_balance', '$active_flag', '$created_at','$balance_side')");

	if($bank_id == 1){
		if($active_flag == 'Active'){
			$sq_app_settings_bank = mysqlQuery("UPDATE app_settings SET bank_name='$bank_name',bank_account_name='$account_name', acc_name='$account_type', bank_acc_no='$account_no', bank_branch_name='$branch_name', bank_ifsc_code='$ifsc_code', bank_swift_code='$swift_code'");
		}else{
			$sq_app_settings_bank = mysqlQuery("UPDATE app_settings SET bank_name='',bank_account_name='', acc_name='', bank_acc_no='', bank_branch_name='', bank_ifsc_code='', bank_swift_code=''");
		}
	}

	//Creating ledger
	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(ledger_id) as max from ledger_master"));
	$ledger_id = $sq_max['max'] + 1;
	$ledger_name = $bank_name.'('.$branch_name.')';

	$balance_side = ($balance_side == '') ? 'Debit' : $balance_side;
	$bal_side = ($balance_side == 'Credit') ? 'Cr' : 'Dr';
	$sq_ledger = mysqlQuery("insert into ledger_master (ledger_id, ledger_name, alias, group_sub_id, balance,balance_side, dr_cr,customer_id,user_type) values ('$ledger_id', '$ledger_name', '', '24', '$op_balance','$balance_side','$bal_side','$bank_id','bank')");
		
	if(!$sq_bank){
		$GLOBALS['flag'] = false;
		echo "error--Sorry, Bank not saved!";
	}

	if($GLOBALS['flag']){
		commit_t();
		echo "Bank has been successfully saved.";
		exit;
	}
	else{
		rollback_t();
		exit;
	}

}

public function bank_master_update()
{
	$branch_id = $_POST['branch_id'];
	$bank_id = $_POST['bank_id'];
	$bank_name = addslashes($_POST['bank_name']);
	$account_name = addslashes($_POST['account_name']);
	$branch_name = $_POST['branch_name'];
	$address = $_POST['address'];
	$account_no = $_POST['account_no'];
	$ifsc_code = $_POST['ifsc_code'];
	$swift_code = $_POST['swift_code'];
	$account_type = $_POST['account_type'];
	$mobile_no = $_POST['mobile_no'];
	$opening_balance = $_POST['opening_balance'];
	$active_flag = $_POST['active_flag'];
	$op_balance = $_POST['op_balance'];
	$balance_side = $_POST['balance_side'];
	$address = addslashes($address);

	$sq_count = mysqli_num_rows(mysqlQuery("select bank_name from bank_master where bank_name='$bank_name' and bank_id!='$bank_id' and branch_name='$branch_name'"));
	if($sq_count>0){
		echo "error--Bank name already exists!";
		exit;
	}
	//**Starting transaction
	begin_t();
	$sq_bank = mysqlQuery("update bank_master set branch_id='$branch_id',bank_name='$bank_name',account_name='$account_name', branch_name='$branch_name', address='$address', account_no='$account_no', ifsc_code='$ifsc_code', swift_code='$swift_code', account_type='$account_type', mobile_no='$mobile_no', op_balance='$op_balance',balance_side='$balance_side', active_flag='$active_flag' where bank_id='$bank_id'");

	if($bank_id == 1){
		if($active_flag == 'Active'){
			$sq_app_settings_bank = mysqlQuery("UPDATE app_settings SET bank_name='$bank_name',bank_account_name='$account_name', acc_name='$account_type', bank_acc_no='$account_no', bank_branch_name='$branch_name', bank_ifsc_code='$ifsc_code', bank_swift_code='$swift_code'");
		}else{
			$sq_app_settings_bank = mysqlQuery("UPDATE app_settings SET bank_name='',bank_account_name='', acc_name='', bank_acc_no='', bank_branch_name='', bank_ifsc_code='', bank_swift_code=''");
		}
	}
	$ledger_name = $bank_name.'('.$branch_name.')';
	$bal_side = ($balance_side == 'Credit') ? 'Cr' : 'Dr';
	$sq_bank = mysqlQuery("update ledger_master set ledger_name='$ledger_name',balance='$op_balance',balance_side='$balance_side', dr_cr='$bal_side' where user_type='bank' and customer_id='$bank_id'");
	
	if(!$sq_bank){
		$GLOBALS['flag'] = false;
		echo "error--Sorry, Bank not updated!";
	}	

	if($GLOBALS['flag']){
		commit_t();
		echo "Bank has been successfully updated.";
		exit;
	}
	else{
		rollback_t();
		exit;
	}
}
}
?>
<?php
global $show_entries_switch;
if($branch_status=='yes'){
	
	if($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
		$query .= " and branch_admin_id = '$branch_admin_id'";
	}
	elseif($role!='Admin' && $role!='Branch Admin' && $role!='Accountant' && $role_id!='7' && $role_id<'7'){
		
		if($role == 'Backoffice' && $show_entries_switch == 'Yes'){
			$query .=" and emp_id in(select emp_id from emp_master where branch_id='$branch_admin_id')";
		}else{
			$query .= " and emp_id='$emp_id' and branch_admin_id = '$branch_admin_id'";
		}
	}
}
elseif($role!='Admin' && $role!='Branch Admin' && $role!='Accountant' && $role_id!='7' && $role_id<'7'){

	if($role == 'Backoffice' && $show_entries_switch == 'Yes'){
		$query .=" and emp_id in(select emp_id from emp_master where branch_id='$branch_admin_id')";
	}else{
		$query .= " and emp_id='$emp_id'";
	}
}
?>

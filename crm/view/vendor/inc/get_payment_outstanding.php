<?php
include "../../../model/model.php";
$branch_status = $_POST['branch_status'];
$estimate_id = isset($_POST['estimate_id']) ?$_POST['estimate_id'] : '';
$branch_admin_id = $_SESSION['branch_admin_id'];
$role_id = $_SESSION['role_id'];
$role = $_SESSION['role'];
$emp_id = $_SESSION['emp_id'];
$balance_amount = 0;

$query = "select * from vendor_estimate where estimate_id='$estimate_id' and delete_status='0'";
include "../../../model/app_settings/branchwise_filteration.php";
$sq_supplier = mysqlQuery($query);
while($row_supplier = mysqli_fetch_assoc($sq_supplier)){	
	$total_payment = 0;
	$sq_supplier_p = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as payment_amount from vendor_payment_master where estimate_id='$row_supplier[estimate_id]' and clearance_status!='Pending' AND clearance_status!='Cancelled'"));
	$total_paid = $sq_supplier_p['payment_amount'];
	$cancel_est = $row_supplier['cancel_amount'];

	if($row_supplier['purchase_return'] == '1'){
		$status = 'cancel';
		if($total_paid > 0){
			if($cancel_est >0){
				if($total_paid > $cancel_est){
					$balance_amount += 0;
				}else{
					$balance_amount += $cancel_est - $total_paid;
				}
			}else{
				$balance_amount += 0;
			}
		}
		else{
			$balance_amount += $cancel_est;
		}
	}else if($row_supplier['purchase_return'] == '2'){
		$status = 'cancel';
		$cancel_estimate = json_decode($row_supplier['cancel_estimate']);
		$balance_amount += (($row_supplier['net_total'] - floatval($cancel_estimate[0]->net_total)) + $cancel_est) - $total_paid;
	}
	else{
		$status = '';
		$balance_amount += $row_supplier['net_total'] - $total_paid;
	}
}
$balance_amount = ($balance_amount) < 0 ? 0 : $balance_amount;
echo $balance_amount.'='.$status;
?>
<?php
include "../../../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];

$branch_status = $_POST['branch_status'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$bank_id = $_POST['bank_id'];
$financial_year_id = $_POST['financial_year_id'];

$query = "select * from receipt_payment_master where 1 and payment_amount!='0' ";
if ($from_date != "" && $to_date != "") {
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);

	$query .= " and payment_date between '$from_date' and '$to_date'";
}
if ($bank_id != "") {
	$query .= " and bank_id='$bank_id' ";
}
if ($financial_year_id != "") {
	$query .= " and financial_year_id='$financial_year_id'";
}
$query .= " order by id desc";
?>
<div class="row mg_tp_20">
	<div class="col-md-12 no-pad">
		<div class="table-responsive">

			<table class="table table-hover" id="deposit_table" style="margin: 20px 0 !important;">
				<thead>
					<tr class="table-heading-row">
						<th>SR_No</th>
						<th>Receipt_ID</th>
						<th>Transaction_Type</th>
						<th>Date</th>
						<th class="text-right">Amount</th>
						<th>Payment_mode</th>
						<th>Payment Evidence</th>
						<th>Created_by</th>
						<th class="text-center">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$count = 1;
					$total_amount = 0;
					$sq_deposit = mysqlQuery($query);
					while ($row_deposit = mysqli_fetch_assoc($sq_deposit)) {

						$yr1 = explode("-", $row_deposit['payment_date']);
						$sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where bank_id='$row_deposit[bank_id]'"));
						$sq_emp = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from emp_master where emp_id='$row_deposit[emp_id]'"));

						$total_amount = $total_amount + $row_deposit['payment_amount'];
						if ($row_deposit['url'] != "") {
							$url = explode('uploads', $row_deposit['url']);
							$url = BASE_URL . 'uploads' . $url[1];
						} else {
							$url = "";
						}
						// PDF
						$payment_id_name = "Receipt/Payment ID";
						$payment_id = get_receipt_payment_id($row_deposit['id'],$row_deposit['receipt_type'],$yr1[0]);
						$receipt_date = get_date_user($row_deposit['payment_date']);
						$booking_id = $row_deposit['id'];
						$customer_id = '';
						$booking_name = addslashes($row_deposit['narration']);
						$travel_date = 'NA';
						$payment_amount = $row_deposit['payment_amount'];
						$payment_mode1 = $row_deposit['payment_mode'];
						$transaction_id = $row_deposit['transaction_id'];
						$payment_date = $receipt_date;
						$bank_name = $row_deposit['bank_name'];
						$receipt_type = $row_deposit['receipt_type'];

						$url1 = BASE_URL."model/app_settings/print_html/receipt_html/receipt_body_html.php?payment_id_name=$payment_id_name&payment_id=$payment_id&receipt_date=$receipt_date&booking_id=$booking_id&customer_id=&booking_name=$booking_name&travel_date=$travel_date&payment_amount=$payment_amount&transaction_id=$transaction_id&payment_date=$payment_date&bank_name=$bank_name&confirm_by=&receipt_type=$receipt_type&payment_mode=$payment_mode1&branch_status=$branch_status&outstanding=0&table_name=receipt_payment_master&customer_field=&in_customer_id=&currency_code=$currency&status=";
						?>
						<tr class="<?= $bg ?>">
							<td><?= $count++ ?></td>
							<td><?= get_receipt_payment_id($row_deposit['id'],$row_deposit['receipt_type'],$yr1[0]) ?></td>
							<td><?= $row_deposit['receipt_type'] ?></td>
							<td><?= get_date_user($row_deposit['payment_date']) ?></td>
							<td class="text-right success"><?= number_format($row_deposit['payment_amount'], 2) ?></td>
							<td><?= $row_deposit['payment_mode'] ?></td>
							<td>
								<?php
								if ($url != "") {
									?>
									<a href="<?= $url ?>" class="btn btn-info btn-sm" download title="download"><i class="fa fa-download"></i></a>
								<?php } ?>
							</td>
							<td><?= ($sq_emp['first_name'] != '') ? $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] : 'Admin' ?></td>
							<td class="text-center" style="white-space:nowrap;">
								<a onclick="loadOtherPage('<?= $url1 ?>')" data-toggle="tooltip" class="btn btn-info btn-sm" title="Download Receipt"><i class="fa fa-print"></i></a>
								<button class="btn btn-info btn-sm form-control" onclick="update_modal(<?= $row_deposit['id'] ?>)" title="Update Details" id="edit-<?= $row_deposit['id'] ?>"><i class="fa fa-pencil-square-o"></i></button>
								<button class="<?= $delete_flag ?> btn btn-danger btn-sm" onclick="rp_delete_entry(<?= $row_deposit['id'] ?>)" title="Delete Entry"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
					<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr class="active">
						<th colspan="4"></th>
						<th class="text-right success">Total : <?= number_format($total_amount, 2) ?></th>
						<th colspan="4"></th>
					</tr>
				</tfoot>
			</table>
			<script type="text/javascript">
				$('#deposit_table').dataTable({
					"pagingType": "full_numbers"
				});
			</script>

		</div>
	</div>
</div>
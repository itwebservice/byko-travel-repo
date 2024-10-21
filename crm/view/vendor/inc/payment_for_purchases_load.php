<?php
include "../../../model/model.php";
include_once('vendor_generic_functions.php');

$q = "select branch_status from branch_assign where link='vendor/dashboard/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
$vendor_type = $_POST['vendor_type'];
$vendor_type_id = $_POST['vendor_type_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$role_id = $_SESSION['role_id'];
$role = $_SESSION['role'];
$emp_id = $_SESSION['emp_id'];
$query = "select * from vendor_estimate where vendor_type='$vendor_type' and vendor_type_id='$vendor_type_id' and delete_status='0'";

include "../../../model/app_settings/branchwise_filteration.php";
$sq_supplier = mysqlQuery($query);
?>
<div class="panel panel-default panel-body app_panel_style mg_tp_30 feildset-panel">
	<legend>Payment Details</legend>
	<div class="row">
		<div class="col-md-12 no-pad">
			<div class="table-responsive">
				<table class="table table-bordered table-hover" id="tbl_pr_payment_list" style="margin: 0 !important; padding-bottom: 0 !important;">
					<thead>
						<tr class="table-heading-row">
							<th>S_No.</th>
							<th>Purchase Type</th>
							<th>Purchase ID</th>
							<th class="hidden">Purchase ID</th>
							<th>Purchase(Customer_name)</th>
							<th class="text-right">Amount</th>
							<th class="text-center">Select</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						while ($row_supplier = mysqli_fetch_assoc($sq_supplier)) {

							$total_payment = 0;
							$sq_supplier_p = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as payment_amount from vendor_payment_master where estimate_id='$row_supplier[estimate_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
							$total_paid = $sq_supplier_p['payment_amount'];
							$cancel_est = $row_supplier['cancel_amount'];

							if ($row_supplier['purchase_return'] == '1') {
								$status = '(Cancelled)';
								if ($total_paid > 0) {
									if ($cancel_est > 0) {
										if ($total_paid > $cancel_est) {
											$balance_amount = 0;
										} else {
											$balance_amount = $cancel_est - $total_paid;
										}
									} else {
										$balance_amount = 0;
									}
								} else {
									$balance_amount = $cancel_est;
								}
							} else if ($row_supplier['purchase_return'] == '2') {
								$status = '(Cancelled)';
								$cancel_estimate = json_decode($row_supplier['cancel_estimate']);
								$balance_amount = (($row_supplier['net_total'] - floatval($cancel_estimate[0]->net_total)) + $cancel_est) - $total_paid;
							} else {
								$status = '';
								$balance_amount = $row_supplier['net_total'] - $total_paid;
							}

							$vendor_type_val = get_vendor_name($row_supplier['vendor_type'], $row_supplier['vendor_type_id']);
							$estimate_type_val = get_estimate_type_name($row_supplier['estimate_type'], $row_supplier['estimate_type_id']);
							$date = $row_supplier['purchase_date'];
							$yr = explode("-", $date);
							$year = $yr[0];
							$purchase = get_vendor_estimate_id($row_supplier['estimate_id'], $year) . $status . ": " . $vendor_type_val . "(" . $row_supplier['vendor_type'] . ") : " . $estimate_type_val;

							if ($balance_amount > '0') {
								if ($row_supplier['estimate_type'] == 'Group Tour') {
									$sq_tour_group = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where group_id='$row_supplier[estimate_type_id]'"));
									$tour_group = date('d-m-Y', strtotime($sq_tour_group['from_date'])) . ' to ' . date('d-m-Y', strtotime($sq_tour_group['to_date']));

									$sq_tour = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id='$sq_tour_group[tour_id]'"));
									$tour_name = $sq_tour['tour_name'];
									$estimate_type_val = $tour_name . "( " . $tour_group . " )";
									$title = $estimate_type_val;
								} ?>
								<tr>
									<td class="col-md-1"><?= $count ?></td>
									<td class="col-md-3"><input type="text" id="pr_payment_type<?= $count ?>" name="pr_payment_type" value="<?= $row_supplier['estimate_type'] ?>" readonly></td>
									<td class="col-md-1"><input type="text" id="pr_estimate_id<?= $count ?>" name="pr_estimate_id" title="<?= $title ?>" value="<?= $row_supplier['estimate_id'] ?>" readonly></td>
									<td class="col-md-2 hidden"><input type="text" id="pr_payment_id<?= $count ?>" name="pr_payment_id" title="<?= $title ?>" value="<?= $row_supplier['estimate_type_id'] ?>" readonly></td>
									<td class="col-md-5"><input type="text" id="pr_customer_name<?= $count ?>" name="pr_customer_name" title="<?= $title ?>" value="<?= $purchase ?>" readonly></td>
									<td class="col-md-2"><input type="text" id="pr_payment_<?= $count ?>" name="pr_payment" value="<?= $balance_amount ?>" class="text-right" readonly></td>
									<td class="text-center col-md-2"><input type="checkbox" id="chk_pr_payment_<?= $count ?>" name="chk_pr_payment" onchange="calculate_total_purchase('<?= 'pr_payment_' . $count ?>','<?= 'chk_pr_payment_' . $count ?>')"></td>
								</tr>
						<?php $count++;
							}
						} ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row mg_tp_20">
	<div class="col-md-3 col-md-offset-8">
		<input type="text" placeholder="Total Purchase" title="Total Purchase" value="0.00" class="form-control text-right" id="total_purchase" name="total_purchase" readonly>
	</div>
</div>

<?php
$sq_ledger_count = mysqli_num_rows(mysqlQuery("select * from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type' and group_sub_id='105'"));
if ($sq_ledger_count != '0') {

	$sq_advance = mysqli_fetch_assoc(mysqlQuery("SELECT sum(`payment_amount`) as sum FROM `vendor_advance_master` WHERE `vendor_type`='$vendor_type' and `vendor_type_id`='$vendor_type_id' and `clearance_status`!='Pending' and `clearance_status`!='Cancelled'"));
	$sq_payment = mysqli_fetch_assoc(mysqlQuery("SELECT sum(`payment_amount`) as sum FROM `vendor_payment_master` WHERE `vendor_type`='$vendor_type' and `vendor_type_id`='$vendor_type_id' and `clearance_status`!='Pending' and `clearance_status`!='Cancelled' and payment_mode='Advance'"));
?>
	<div class="panel panel-default panel-body app_panel_style mg_tp_20 feildset-panel">
		<legend>Advance Details</legend>
		<div class="row mg_tp_20">
			<div class="col-md-4">
				<?php
				$balance = 0;
				$sq_ledger = mysqli_fetch_assoc(mysqlQuery("select ledger_name,ledger_id from ledger_master where customer_id='$vendor_type_id' and user_type='$vendor_type' and group_sub_id='105'"));
				$balance = $sq_advance['sum'] - $sq_payment['sum'];
				$balance = (floatval($balance) < 0) ? 0 : $balance;
				echo 'Advances to ' . $sq_ledger['ledger_name'] . ' : (DR)' . $sq_advance['sum'];
				?>
			</div>
			<div class="col-md-2">
				<input type="text" class="form-control" id="advance_amount" title="Advance Amount" name="advance_amount" value="<?= ($balance) ?>" readonly>
			</div>
			<div class="col-md-6">
				<input type="text" placeholder="Advances to be nullify" title="Advances to be nullify" class="form-control" id="advance_nullify" name="advance_nullify" onchange="pay_amount_nullify('advance_amount',this.id)">
			</div>
		</div>
	</div>
<?php } ?>
<!-- ======================================================================================================================== -->
<?php
$sq_debit_count = mysqli_num_rows(mysqlQuery("select * from debit_note_master where vendor_type='$vendor_type' and vendor_type_id='$vendor_type_id'"));
if ($sq_debit_count != '0') {

	$row_debit_note = mysqli_fetch_assoc(mysqlQuery("SELECT sum(`payment_amount`)as sum FROM `debit_note_master` WHERE `vendor_type`='$vendor_type' and `vendor_type_id`='$vendor_type_id' "));
	$sq_payment = mysqli_fetch_assoc(mysqlQuery("SELECT sum(`payment_amount`)as sum FROM `vendor_payment_master` WHERE payment_mode='Debit Note' and `vendor_type`='$vendor_type' and `vendor_type_id`='$vendor_type_id' and `clearance_status`!='Pending' and `clearance_status`!='Cancelled'"));
	$total_debit_amount = $row_debit_note['sum'] - $sq_payment['sum'];
	if(floatval($total_debit_amount) > 0){
	?>
		<div class="panel panel-default panel-body app_panel_style mg_tp_20 feildset-panel">
			<legend>Debit Note Details</legend>
			<div class="row mg_tp_20">
				<div class="col-md-3">
					<?php echo "Debit Note Amount : " ?>
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control" id="debit_note_amount" name="debit_note_amount" title="Debit Note Amount" value="<?= ($total_debit_amount) ?>" readonly>
				</div>
			</div>
		</div>
<?php }
} ?>
<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
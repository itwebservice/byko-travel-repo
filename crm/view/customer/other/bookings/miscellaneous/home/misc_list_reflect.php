<?php
include "../../../../../../model/model.php";
$misc_id = $_POST['misc_id'];
$customer_id = $_SESSION['customer_id'];
?>
<div class="row mg_tp_20">
	<div class="col-md-12">
		<div class="table-responsive">

			<table class="table table-bordered bg_white cust_table" id="tbl_miscellaneous_list" style="margin:20px 0 !important">
				<thead>
					<tr class="table-heading-row">
						<th>S_No.</th>
						<th>Booking_ID</th>
						<th>Total_pax</th>
						<th>View</th>
						<th class="info">Total_Amount</th>
						<th class="success">Paid_Amount</th>
						<th class="danger">Cncel_amount</th>
						<th class="warning">Balance</th>
						<th class="text-center">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$query = "select * from miscellaneous_master where customer_id='$customer_id' and delete_status='0'";
					if ($misc_id != '') {
						$query .= " and misc_id='$misc_id'";
					}
					$count = 0;
					$booking_amount = 0;
					$cancelled_amount = 0;
					$total_amount = 0;
					$total_paid = 0;
					$total_cancel = 0;
					$total_balance = 0;
					$sq_miscellaneous = mysqlQuery($query);
					while ($row_miscellaneous = mysqli_fetch_assoc($sq_miscellaneous)) {

						$pass_count = mysqli_num_rows(mysqlQuery("select * from  miscellaneous_master_entries where misc_id='$row_miscellaneous[misc_id]'"));
						$cancel_count = mysqli_num_rows(mysqlQuery("select * from  miscellaneous_master_entries where misc_id='$row_miscellaneous[misc_id]' and status='Cancel'"));
						$bg = "";
						if ($pass_count == $cancel_count) {
							$bg = "danger";
						} else {
							$bg = "";
						}
						$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_miscellaneous[customer_id]'"));

						//Get Total no of miscellaneous members
						$sq_total_member = mysqli_num_rows(mysqlQuery("select misc_id from miscellaneous_master_entries where misc_id='$row_miscellaneous[misc_id]'"));

						$query1 = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from miscellaneous_payment_master where misc_id='$row_miscellaneous[misc_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
						//Get Total miscellaneous Cost
						$sale_total_amount = $row_miscellaneous['misc_total_cost'];
						if ($sale_total_amount == "") {
							$sale_total_amount = 0;
						}
						$cancel_amount = $row_miscellaneous['cancel_amount'];
						$paid_amount = $query1['sum'];
						$paid_amount = ($paid_amount == '') ? '0' : $paid_amount;

						if ($pass_count == $cancel_count) {
							if ($paid_amount > 0) {
								if ($cancel_amount > 0) {
									if ($paid_amount > $cancel_amount) {
										$balance_amount = 0;
									} else {
										$balance_amount = $cancel_amount - $paid_amount;
									}
								} else {
									$balance_amount = 0;
								}
							} else {
								$balance_amount = $cancel_amount;
							}
						} else {
							$balance_amount = $sale_total_amount - $paid_amount;
						}

						//Total
						$total_amount += $sale_total_amount + $query1['sumc'];
						$total_paid += $paid_amount + $query1['sumc'];
						$total_cancel += $cancel_amount;
						$total_balance += $balance_amount;
						$created_at = $row_miscellaneous['created_at'];
						$year = explode("-", $created_at);
						$year = $year[0];
						
						if(floatval($balance_amount) == 0 && $bg == ''){

							$invoice_no = get_misc_booking_id($row_miscellaneous['misc_id'],$year);
							$booking_id = $row_miscellaneous['misc_id'];
							$invoice_date = date('d-m-Y',strtotime($row_miscellaneous['created_at']));
							$customer_id = $row_miscellaneous['customer_id'];
							
							$service_name = "Miscellaneous Invoice";
							$service_charge = $row_miscellaneous['service_charge'];
							$service_tax = $row_miscellaneous['service_tax_subtotal'];
							//**Basic Cost
							$credit_card_charges = $query1['sumc'];
							$basic_cost = $row_miscellaneous['misc_issue_amount'];
							$net_amount = $row_miscellaneous['misc_total_cost'];
							$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Miscellaneous'"));   
							$sac_code = $sq_sac['hsn_sac_code'];
							
							$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/misc_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&service_charge=$service_charge&total_paid=$paid_amount&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&booking_id=$booking_id&credit_card_charges=$credit_card_charges&canc_amount=$cancel_amount&bg=$bg";
							$invoice_btn = '<a data-toggle="tooltip" onclick="loadOtherPage(\'' .$url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
						}else{
							$invoice_btn = 'NA';
						}
					?>
						<tr class="<?= $bg ?>">
							<td><?= ++$count ?></td>
							<td><?= get_misc_booking_id($row_miscellaneous['misc_id'], $year) ?></td>
							<td><?php echo $sq_total_member; ?></td>
							<td><button class="btn btn-info btn-sm" onclick="misc_display_modal(<?= $row_miscellaneous['misc_id'] ?>)" title="View Details" id="misc-<?= $row_miscellaneous['misc_id'] ?>"><i class="fa fa-eye" aria-hidden="true"></i></button>
							</td>
							<td class="info"><?php echo $sale_total_amount + $query1['sumc']; ?></td>
							<td class="success"><?= $paid_amount + $query1['sumc'] ?></td>
							<td class="danger"><?php echo $cancel_amount; ?></td>
							<td class="warning"><?php echo number_format($balance_amount, 2); ?></td>
        					<td><?= $invoice_btn ?></td>
						</tr>
					<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4" class="text-right active">Total</th>
						<th class="info text-right"><?php echo number_format($total_amount, 2); ?></th>
						<th class="success text-right"><?php echo number_format($total_paid, 2); ?></th>
						<th class="danger text-right"><?php echo number_format($total_cancel, 2); ?></th>
						<th class="warning text-right"><?php echo number_format($total_balance, 2); ?></th>
						<th class="active"></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<script>
$('#tbl_miscellaneous_list').dataTable({
	"pagingType": "full_numbers"
});
</script>
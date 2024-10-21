<?php
include "../../../../../../model/model.php";
$ticket_id = $_POST['ticket_id'];
$customer_id = $_SESSION['customer_id'];
?>

<div class="row mg_tp_20">
	<div class="col-md-12">
		<div class="table-responsive">



			<table class="table table-bordered cust_table" id="train_ticket_list" style="margin:20px 0 !important;">

				<thead>
					<tr class="table-heading-row">
						<th>S_No.</th>
						<th>Booking_ID</th>
						<th>Train_No</th>
						<th>Travel_Date/Time</th>
						<th>Status</th>
						<th>View</th>
						<th class="info">Total_Amount</th>
						<th class="success">Paid_Amount</th>
						<th class="danger">Cncl_amount</th>
						<th class="warning">Balance</th>
						<th class="text-center">Actions</th>
					</tr>
				</thead>

				<tbody>

					<?php

					$query = "select * from train_ticket_master where 1 and delete_status='0'";
					$query .= " and customer_id='$customer_id'";
					if ($ticket_id != "") {
						$query .= " and train_ticket_id='$ticket_id'";
					}

					$count = 0;
					$available_bal = 0;
					$pending_bal = 0;
					$total_amount = 0;
					$total_paid = 0;
					$total_cancel = 0;
					$total_balance = 0;

					$sq_ticket = mysqlQuery($query);

					while ($row_ticket = mysqli_fetch_assoc($sq_ticket)) {

						$date = $row_ticket['created_at'];
						$yr = explode("-", $date);
						$year = $yr[0];

						$pass_count = mysqli_num_rows(mysqlQuery("select * from  train_ticket_master_entries where train_ticket_id='$row_ticket[train_ticket_id]'"));
						$cancel_count = mysqli_num_rows(mysqlQuery("select * from  train_ticket_master_entries where train_ticket_id='$row_ticket[train_ticket_id]' and status='Cancel'"));
						if ($pass_count == $cancel_count) {
							$bg = "danger";
						} else {
							$bg = "";
						}

						$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
						$sq_train_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master_trip_entries where train_ticket_id='$row_ticket[train_ticket_id]'"));

						$sq_payment = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum_pay,sum(credit_charges) as sumc from train_ticket_payment_master where train_ticket_id='$row_ticket[train_ticket_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
						$credit_card_charges = $sq_payment['sumc'];
						$sale_total_amount = $row_ticket['net_total'];
						$cancel_amount = $row_ticket['cancel_amount'];
						$paid_amount = $sq_payment['sum_pay'];
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
						$total_amount += $sale_total_amount + $credit_card_charges;
						$total_paid += $paid_amount + $credit_card_charges;
						$total_cancel += $cancel_amount;
						$total_balance += $balance_amount;
						$url = '';
						$paid_amount1 = $paid_amount + $credit_card_charges;
						if(floatval($balance_amount) == 0 && $bg == ''){
							
							$sq_tickets_count = mysqli_num_rows(mysqlQuery("select train_ticket_url from train_ticket_master_upload_entries where train_ticket_id='$row_ticket[train_ticket_id]'"));
							if($sq_tickets_count > 0){
								$sq_tickets = mysqli_fetch_assoc(mysqlQuery("select train_ticket_url from train_ticket_master_upload_entries where train_ticket_id='$row_ticket[train_ticket_id]'"));
								$url1 = explode('uploads/', $sq_tickets['train_ticket_url']);
								$url2 = ($sq_tickets['train_ticket_url'] != '') ? BASE_URL.'uploads/'.$url1[1] : '';
								$url = '<button class="btn btn-info btn-sm"><a href="'. $url2.'" download title="Download train ticket"><i class="fa fa-download"></i></a></button>';
							}
							$invoice_no = get_train_ticket_booking_id($row_ticket['train_ticket_id'],$year);
							$invoice_date = date('d-m-Y',strtotime($row_ticket['created_at']));
							$customer_id = $row_ticket['customer_id'];
							$service_name = "Train Invoice";
							$train_ticket_id = $row_ticket['train_ticket_id'];
							$service_charge =  $row_ticket['service_charge'];
							$service_tax = $row_ticket['service_tax_subtotal'];
							
							$basic_cost = $row_ticket['basic_fair'];
							$net_amount = $row_ticket['net_total'];
						
							$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Train'"));   
							$sac_code = $sq_sac['hsn_sac_code'];

							$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/train_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&service_charge=$service_charge&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&train_ticket_id=$train_ticket_id&total_paid=$paid_amount1&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&credit_card_charges=$credit_card_charges&canc_amount=$cancel_amount&bg=$bg";
							$invoice_btn = '<a onclick="loadOtherPage(\''. $url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
						}else{
							$invoice_btn = 'NA';
						}
						?>
						<tr class="<?= $bg ?>">
							<td><?= ++$count ?></td>
							<td><?= get_train_ticket_booking_id($row_ticket['train_ticket_id'], $year) ?></td>
							<td><?= $sq_train_info['train_no']; ?></td>
							<td><?= get_datetime_user($sq_train_info['travel_datetime']); ?></td>
							<td><?= $sq_train_info['ticket_status']; ?></td>
							<td><button class="btn btn-info btn-sm" onclick="train_ticket_view_modal(<?= $row_ticket['train_ticket_id'] ?>)" title="View Details" id="train-<?= $row_ticket['train_ticket_id'] ?>"><i class="fa fa-eye"></i></button></td>
							<td class="info"><?= number_format($sale_total_amount + $credit_card_charges,2)  ?></td>
							<td class="success"><?= number_format($paid_amount + $credit_card_charges, 2) ?></td>
							<td class="danger"><?= $cancel_amount ?></td>
							<td class="warning"><?= number_format($balance_amount, 2) ?></td>
        					<td><?= $invoice_btn.' '.$url ?></td>
						</tr>
					<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="6" class="text-right">Total</th>
						<th class="active text-right info"><?= number_format($total_amount, 2); ?></th>
						<th class="active text-right success"><?= number_format($total_paid, 2); ?></th>
						<th class="active text-right danger"><?= number_format($total_cancel, 2); ?></th>
						<th class="active text-right warning"><?= number_format(($total_balance), 2); ?></th>
		    			<th class="active"></th>
					</tr>
				</tfoot>

			</table>
		</div>
	</div>
</div>
<script>
	$('#train_ticket_list').dataTable({
		"pagingType": "full_numbers"
	});
</script>
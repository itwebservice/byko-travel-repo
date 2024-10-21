<?php
include "../../../../../../model/model.php";

$customer_id = $_SESSION['customer_id'];
$booking_id = $_POST['booking_id'];

$query = "select * from car_rental_booking where customer_id='$customer_id' and delete_status='0' ";
if ($booking_id != "") {
	$query .= " and booking_id='$booking_id'";
}
?>
<div class="row mg_tp_20">
	<div class="col-xs-12">
		<div class="table-responsive">
			<table class="table table-bordered table-hover cust_table" id="tbl_vendor_list" style="margin:20px 0 !important;">
				<thead>
					<tr class="table-heading-row">
						<th>S_No.</th>
						<th>Booking_ID</th>
						<th>NO_OF_PAX</th>
						<th>Traveling_Date</th>
						<th>View</th>
						<th class="info">Total Amount</th>
						<th class="success">Paid Amount</th>
						<th class="danger">Cncl_amount</th>
						<th class="warning">Balance</th>
						<th class="text-center">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$count = 0;
					$pen_bal = 0;
					$paid_bal = 0;
					$total_cancel = 0; $total_amount = 0; $total_paid = 0; $total_balance = 0;
					$sq_booking = mysqlQuery($query);
					while ($row_booking = mysqli_fetch_assoc($sq_booking)) {

						$date = $row_booking['created_at'];
						$yr = explode("-", $date);
						$year = $yr[0];
						$count++;
						$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));

						$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from car_rental_payment where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' AND clearance_status!='Cancelled'"));
						
						$sale_total_amount = $row_booking['total_fees'];
						$cancel_amount = $row_booking['cancel_amount'];

						$paid_amount = $sq_payment_info['sum'];

						if($row_booking['status'] == 'Cancel'){
							if ($paid_amount > 0) {
								if ($cancel_amount > 0) {
									if ($paid_amount > $cancel_amount){
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
						}else{
							$balance_amount = $sale_total_amount - $paid_amount;
						}

						//Total
						$total_amount += $sale_total_amount+$sq_payment_info['sumc'];
						$total_paid += $paid_amount+$sq_payment_info['sumc'];
						$total_cancel += $cancel_amount;
						$total_balance += $balance_amount;

						$bg = ($row_booking['status'] == "Cancel") ? "danger" : "";
						$trav_date = ($row_booking['travel_type'] == 'Local') ? 'NA' : get_date_user($row_booking['traveling_date']);
						if(floatval($balance_amount) == 0 && $bg == ''){
							$duty_slip = '<button data-toggle="tooltip" display="inline" class="btn btn-danger btn-sm" onclick="booking_registration_pdf('.$row_booking['booking_id'] .')" title="Download Duty Slip"><i class="fa fa-file-pdf-o"></i></button>';
							
							$invoice_no = get_car_rental_booking_id($row_booking['booking_id'],$year);
							$invoice_date = date('d-m-Y',strtotime($row_booking['created_at']));
							$customer_id = $row_booking['customer_id'];
							$booking_id = $row_booking['booking_id'];
							$service_name = "Car Rental Invoice"; 
							$service_tax1 = $row_booking['service_tax_subtotal'];
							//**Basic Cost
							$basic_cost = $row_booking['basic_amount'];
							$other_charge = $row_booking['driver_allowance'] + $row_booking['permit_charges'] + $row_booking['toll_and_parking'] + $row_booking['state_entry_tax'] + $row_booking['other_charges'];
							$basic_cost += $other_charge;
							$credit_card_charges = $sq_payment_info['sumc'];
							$net_amount = $row_booking['total_fees'];
							$basic_cost1 = $row_booking['basic_amount'];

							$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Car Rental'"));   
							$sac_code = $sq_sac['hsn_sac_code'];
							$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/carrental_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=&service_tax_per=&service_tax=$service_tax1&net_amount=$net_amount&service_charge=$other_charge&total_paid=$paid_amount&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&booking_id=$booking_id&credit_card_charges=$credit_card_charges&canc_amount=$cancel_amount&bg=$bg";

							$invoice_pdf = '<a onclick="loadOtherPage(\''. $url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
						}else{
							$invoice_pdf = '';
							$duty_slip = 'NA';
						}
						?>
						<tr class="<?= $bg ?>">
							<td><?= $count ?></td>
							<td><?= get_car_rental_booking_id($row_booking['booking_id'], $year) ?></td>
							<td><?= $row_booking['total_pax'] ?></td>
							<td><?= $trav_date ?></td>
							<td>
								<button class="btn btn-info btn-sm" onclick="car_display_modal(<?= $row_booking['booking_id'] ?>)" title="View Details" id="car-<?= $row_booking['booking_id'] ?>"><i class="fa fa-eye" aria-hidden="true"></i></button>
							</td>
							<td class="info"><?= number_format($sale_total_amount, 2) ?></td>
							<td class="success"><?= number_format($paid_amount, 2) ?></td>
							<td class="danger"><?= number_format($cancel_amount, 2) ?></td>
							<td class="warning"><?= number_format($balance_amount, 2); ?></td>
        					<td><?= $invoice_pdf.' '.$duty_slip ?></td>
						</tr>
					<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr class="active">
						<th colspan="5" class="text-right">Total</th>
						<th class="text-right info"><?= number_format($total_amount, 2) ?></th>
						<th class="text-right success"><?= number_format($total_paid, 2) ?></th>
						<th class="text-right danger"><?= number_format($total_cancel, 2); ?></th>
						<th class="text-right warning"><?= number_format(($total_balance), 2); ?></th>
		    			<th class="active"></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript">
	$('#tbl_vendor_list').dataTable({
		"pagingType": "full_numbers"
	});
</script>
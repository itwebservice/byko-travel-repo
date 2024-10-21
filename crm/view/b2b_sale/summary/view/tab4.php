<?php
$query1 = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(`credit_charges`) as sumc from b2b_payment_master where booking_id='$query[booking_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
$credit_card_amount = $query1['sumc'];
$paid_amount = $query1['sum'];
$paid_amount = ($paid_amount == '') ? '0' : $paid_amount;
$sale_total_amount = $grnd_total;
if ($sale_total_amount == "") {
	$sale_total_amount = 0;
}
$cancel_amount = $query['cancel_amount'];
if ($cancel_amount != 0) {
	if ($cancel_amount <= $paid_amount) {
		$balance_amount = 0;
	} else {
		$balance_amount =  $cancel_amount - $paid_amount;
	}
} else {
	$cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
	$balance_amount = $sale_total_amount - $paid_amount;
}
include "../../../../model/app_settings/generic_sale_widget.php";
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="profile_box main_block">
				<legend>Summary</legend>
				<div class="table-responsive">
					<table class="table table-bordered no-marg">
						<thead>
							<tr class="table-heading-row">
								<th>S_No</th>
								<th>Date</th>
								<th class="text-right">Amount</th>
								<th>Mode</th>
								<th>Payment_ID</th>
								<th>Order_ID</th>
								<th>Bank_Name</th>
								<th>Cheque_No/ID</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$count = 0;
							$sq_payment = mysqlQuery("SELECT * from b2b_payment_master where booking_id='$booking_id'");
							while($row_payment = mysqli_fetch_assoc($sq_payment)){
								if($row_payment['payment_amount'] != '0'){
									$count++;
									$bg='';
									$bg1='';
									if($row_payment['clearance_status']=="Pending"){ 
										$bg='warning';
										$bg1='table-warning';
									}
									else if($row_payment['clearance_status']=="Cancelled"){ 
										$bg='danger';
										$bg1='table-danger';
									}

									?>
									<tr class="<?= $bg.' '.$bg1?>">				
										<td><?= $count ?></td>
										<td><?= date('m-d-Y', strtotime($row_payment['payment_date'])); ?></td>
										<td class="text-right"><?= $row_payment['payment_amount'] ?></td>
										<td><?= $row_payment['payment_mode'] ?></td>
										<td><?= $row_payment['payment_id'] ?></td>
										<td><?php echo ($row_payment['order_id']=='')?'NA':$row_payment['order_id'];  ?></td>
										<td><?php echo ($row_payment['bank_name']=="")?'NA': $row_payment['bank_name']; ?></td>
										<td><?php echo ($row_payment['transaction_id']=='')?'NA': $row_payment['transaction_id']; ?></td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
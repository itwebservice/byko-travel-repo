<?php
//paid
$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from bus_booking_payment_master where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
$paid_amount = $query['sum'] + $query['sumc'];
$paid_amount = ($paid_amount == '')?'0':$paid_amount;

//sale
$sale_total_amount = $sq_booking['net_total'] + $query['sumc'];
if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

//Cancel
$cancel_amount=$sq_booking['cancel_amount'];
$pass_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$sq_booking[booking_id]'"));
$cancel_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$sq_booking[booking_id]' and status='Cancel'"));

if($pass_count == $cancel_count){
	if($paid_amount > 0){
		if($cancel_amount >0){
			if($paid_amount > $cancel_amount){
				$balance_amount = 0;
			}else{
				$balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
			}
		}else{
			$balance_amount = 0;
		}
	}
	else{
		$balance_amount = $cancel_amount;
	}
}
else{
	$balance_amount = $sale_total_amount - $paid_amount;
}

include "../../../../../../../model/app_settings/generic_sale_widget.php";
?>
	<div class="row">
		<div class="col-xs-12">
			<div class="profile_box main_block" style="margin-top: 25px">
				<h3 class="editor_title">Summary</h3>
				<div class="table-responsive">
					<table class="table table-bordered no-marg">
						<thead>
							<tr class="table-heading-row">
								<th>S_No</th>
								<th>Date</th>
								<th>Mode</th>
								<th>Bank_Name</th>
								<th>Cheque_No/ID</th>
								<th class="text-right">Amount</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$query = "SELECT * from bus_booking_payment_master where 1";		
							if($booking_id!=""){
								$query .= " and booking_id='$booking_id'";
							}
							$bg;
							$count = 0;
							$total_paid_amt=0;
							$sq_pending_amount=0;
							$sq_cancel_amount=0;
							$sq_paid_amount=0;
							$Total_payment=0;
						
							$sq_payment = mysqlQuery($query);
							while($row_payment = mysqli_fetch_assoc($sq_payment)){
								if($row_payment['payment_amount'] != '0'){
									
									$count++;

									$sq_bus_info = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id='$row_payment[booking_id]'"));
									$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_bus_info[customer_id]'"));
									
									$bg='';
									$sq_paid_amount = $sq_paid_amount + $row_payment['payment_amount'];
									if($row_payment['clearance_status']=="Pending"){ 
										$bg='warning';
										$sq_pending_amount = $sq_pending_amount + $row_payment['payment_amount'];
									}
									else if($row_payment['clearance_status']=="Cancelled"){ 
										$bg='danger';
										$sq_cancel_amount = $sq_cancel_amount + $row_payment['payment_amount'];
									}
									else if ($row_payment['clearance_status'] == "Cleared") {
										$bg = "success";
									}

									?>
									<tr class="<?= $bg;?>">				
										<td><?= $count ?></td>
										<td><?= get_date_user($row_payment['payment_date']) ?></td>
										<td><?= $row_payment['payment_mode'] ?></td>
										<td><?= $row_payment['bank_name'] ?></td>
										<td><?= $row_payment['transaction_id'] ?></td>
										<td class="text-right"><?= number_format($row_payment['payment_amount']+$row_payment['credit_charges'],2) ?></td>
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
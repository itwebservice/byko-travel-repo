<?php
include "../../../../../../model/model.php";

$visa_id = $_POST['visa_id'];
$customer_id = $_SESSION['customer_id'];
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">

<table class="table table-bordered bg_white cust_table" id="tbl_visa_list" style="margin:20px 0 !important">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Booking_ID</th>
			<th>Total_pax</th>
			<th>View</th>
			<th class="text-right info">Total_Amount</th>
			<th class="text-right success">Paid_Amount</th>
			<th class="text-right danger">Cncl_amount</th> 
			<th class="text-right warning">Balance</th>
			<th class="text-center">Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$query = "select * from visa_master where 1 and delete_status='0' ";
		$query .= " and customer_id='$customer_id'";
		if($visa_id != ''){
			$query .= " and visa_id='$visa_id'";
		}
		$count = 0;
		$booking_amount = 0;
		$cancelled_amount = 0;
		$total_amount = 0;
		$total_paid = 0;
		$total_cancel = 0;
		$total_balance = 0;
		$sq_visa = mysqlQuery($query);
		while($row_visa = mysqli_fetch_assoc($sq_visa)){
            
			$pass_count = mysqli_num_rows(mysqlQuery("select * from  visa_master_entries where visa_id='$row_visa[visa_id]'"));
			$cancel_count = mysqli_num_rows(mysqlQuery("select * from  visa_master_entries where visa_id='$row_visa[visa_id]' and status='Cancel'"));
			$bg="";
			if($pass_count==$cancel_count){
				$bg="danger";
			}
			else{
				$bg="";
			}
			$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_visa[customer_id]'"));
			$date = $row_visa['created_at'];
			$yr = explode("-", $date);
			$year =$yr[0];
			//Get Total no of visa members
            $sq_total_member=mysqli_num_rows(mysqlQuery("select visa_id from visa_master_entries where visa_id='$row_visa[visa_id]' ")); 

			$cancel_amount=$row_visa['cancel_amount'];
			$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from visa_payment_master where visa_id='$row_visa[visa_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
			$paid_amount = $query['sum'];
			$paid_amount = ($paid_amount == '')?'0':$paid_amount;

            $sale_total_amount=$row_visa['visa_total_cost'];
			if($pass_count == $cancel_count){
				if($paid_amount > 0){
					if($cancel_amount >0){
						if($paid_amount > $cancel_amount){
							$balance_amount = 0;
						}else{
							$balance_amount = $cancel_amount - $paid_amount;
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
			if(floatval($balance_amount) == 0 && $bg == ''){

				$invoice_no = get_visa_booking_id($row_visa['visa_id'],$year);
				$visa_id = $row_visa['visa_id'];
				$invoice_date = date('d-m-Y',strtotime($row_visa['created_at']));
				$customer_id = $row_visa['customer_id'];
				$service_name = "Visa Invoice";
				$pass_count = $sq_total_member;
				//**Service Tax==
				$service_charge = $row_visa['service_charge'];
				$service_tax = $row_visa['service_tax_subtotal'];
				//**Basic Cost
				$basic_cost = $row_visa['visa_issue_amount'];
				$net_amount = $row_visa['visa_total_cost'];
				$roundoff = $row_visa['roundoff'];
				$credit_card_charges = $query['sumc'];
				$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Visa'"));
				$sac_code = $sq_sac['hsn_sac_code'];
				
				$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/visa_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&service_charge=$service_charge&total_paid=$paid_amount&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&visa_id=$visa_id&roundoff=$roundoff&credit_card_charges=$credit_card_charges&currency_code=$row_visa[currency_code]&canc_amount=$cancel_amount&bg=$bg";
				$invoice_btn = '<a data-toggle="tooltip" onclick="loadOtherPage(\'' .$url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
			}else{
				$invoice_btn = 'NA';
			}

			$sale_total_amount1 = currency_conversion($currency,$row_visa['currency_code'],$sale_total_amount+$query['sumc']);
			$paid_amount1 = currency_conversion($currency,$row_visa['currency_code'],$paid_amount+$query['sumc']);
			$cancel_amount1 = currency_conversion($currency,$row_visa['currency_code'],$cancel_amount);
			$balance_amount1 = currency_conversion($currency,$row_visa['currency_code'],$balance_amount);

			$net_total1_string = explode(' ',$sale_total_amount1);
			$footer_net_total = str_replace(',', '', $net_total1_string[1]);
			$paid_amount1_string = explode(' ',$paid_amount1);
			$footer_paid_amount = str_replace(',', '', $paid_amount1_string[1]);
			$cancel_amount1_string = explode(' ',$cancel_amount1);
			$footer_cancel_amount = str_replace(',', '', $cancel_amount1_string[1]);
			$balance_amount1_string = explode(' ',$balance_amount1);
			$footer_balance_amount = str_replace(',', '', $balance_amount1_string[1]);

			//Total
			$total_amount += $footer_net_total;
			$total_paid += $footer_paid_amount;
			$total_cancel += $footer_cancel_amount;
			$total_balance += $footer_balance_amount;
			?>	
			<tr class="<?=$bg?>">
				<td><?= ++$count ?></td>
				<td><?= get_visa_booking_id($row_visa['visa_id'],$year) ?></td>
				<td><?php echo $sq_total_member; ?></td>
				<td><button class="btn btn-info btn-sm" onclick="visa_display_modal(<?= $row_visa['visa_id'] ?>)" title="View Details" id="visa-<?= $row_visa['visa_id'] ?>"><i class="fa fa-eye" aria-hidden="true"></i></button>
				</td>
				<td class="info text-right"><?php echo number_format($sale_total_amount+$query['sumc'],2); ?></td>
				<td class="success text-right"><?= number_format($paid_amount+$query['sumc'],2) ?></td>
				<td class="danger text-right"><?php echo $cancel_amount; ?></td>
				<td class="warning text-right"><?php echo number_format($balance_amount,2); ?></td>
				<td><?= $invoice_btn ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
	<tfoot>
		<tr>
		    <th colspan="4" class="text-right active">Total</th>		    
			<th class="info text-right"><?php echo number_format($total_amount,2); ?></th>
			<th class="success text-right"><?php echo number_format($total_paid,2); ?></th>
			<th class="danger text-right"><?php echo number_format($total_cancel,2); ?></th>
			<th class="warning text-right"><?php echo number_format($total_balance,2); ?></th>
			<th class="active"></th>
		</tr>
	</tfoot>
</table>
</div> </div> </div>
<script>
$('#tbl_visa_list').dataTable({
	"pagingType": "full_numbers"
});
</script>
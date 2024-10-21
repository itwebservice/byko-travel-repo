<?php 
include "../../../../../../model/model.php";

$exc_id = $_POST['exc_id'];
$customer_id = $_SESSION['customer_id'];
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">

<table class="table table-bordered bg_white cust_table" id="tbl_exc_list" style="margin:20px 0 !important">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Booking_ID</th>
			<th>View</th>
			<th class="info">Total Amount</th>
			<th class="success">Paid Amount</th>
			<th class="danger">CNCEL_AMOUNT</th> 
			<th class="warning">Balance</th>
			<th class="text-center">Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$query = "select * from excursion_master where 1 and delete_status='0'";
		$query .= " and customer_id='$customer_id'";
		if($exc_id != ''){
			$query .= " and exc_id='$exc_id'";
		}
		$count = 0;
		$booking_amount = 0;
		$cancelled_amount = 0;
		$total_amount = 0;
		$total_paid = 0;
		$total_cancel = 0;
		$total_balance = 0;

		$sq_exc = mysqlQuery($query);
		while($row_exc = mysqli_fetch_assoc($sq_exc)){

			$pass_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$row_exc[exc_id]'"));
			$cancel_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$row_exc[exc_id]' and status='Cancel'"));
			if($pass_count==$cancel_count){
				$bg="danger";
			}
			else {
				$bg="";
			}

			$date = $row_exc['created_at'];
			$yr = explode("-", $date);
			$year =$yr[0];
			$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_exc[customer_id]'"));


			$cancel_amount=$row_exc['cancel_amount'];
			$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from exc_payment_master where exc_id='$row_exc[exc_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
			$paid_amount = $query['sum'];
			$paid_amount = ($paid_amount == '')?'0':$paid_amount;
			
           //Get Total exc cost
			$sale_total_amount=$row_exc['exc_total_cost'];
			if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
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

			$sale_total_amount1 = currency_conversion($currency,$row_exc['currency_code'],$sale_total_amount + $query['sumc']);
			$paid_amount1 = currency_conversion($currency,$row_exc['currency_code'],$paid_amount + $query['sumc']);
			$cancel_amount1 = currency_conversion($currency,$row_exc['currency_code'],$cancel_amount);
			$balance_amount1 = currency_conversion($currency,$row_exc['currency_code'],$balance_amount);
			
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
			if(floatval($balance_amount) == 0 && $bg == ''){

				$voucher_btn = '<button data-toggle="tooltip" title="Download Service Voucher" onclick="voucher_display('.$row_exc['exc_id'].')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></button>';

				$invoice_no = get_exc_booking_id($row_exc['exc_id'],$year);
				$booking_id = $row_exc['exc_id'];
				$invoice_date = date('d-m-Y',strtotime($row_exc['created_at']));
				$customer_id = $row_exc['customer_id'];
				$service_name = "Activity Invoice";
				$service_charge = $row_exc['service_charge'];
				$service_tax = $row_exc['service_tax_subtotal'];
				//**Basic Cost
				$basic_cost = $row_exc['exc_issue_amount'];
				$net_amount = $row_exc['exc_total_cost'];
				$credit_card_charges = $query['sumc'];
				$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Excursion'"));   
				$sac_code = $sq_sac['hsn_sac_code'];
				
				$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/excursion_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&service_charge=$service_charge&total_paid=$paid_amount&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&booking_id=$booking_id&credit_card_charges=$credit_card_charges&currency_code=$row_exc[currency_code]&canc_amount=$cancel_amount&bg=$bg";
				$invoice_btn = '<a onclick="loadOtherPage(\''.$url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
			}else{
				$invoice_btn = '';
				$voucher_btn = 'NA';
			}
			?>	
			<tr class="<?=$bg?>">
				<td><?= ++$count ?></td>
				<td><?= get_exc_booking_id($row_exc['exc_id'],$year) ?></td>
				<td>
					<button class="btn btn-info btn-sm" onclick="exc_display_modal(<?= $row_exc['exc_id'] ?>)" title="View Details" id="exc-<?= $row_exc['exc_id'] ?>"><i class="fa fa-eye" aria-hidden="true"></i></button>
				</td>
				<td class="info"><?php echo $sale_total_amount1; ?></td>
				<td class="success"><?= $paid_amount1 ?></td>
				<td class="danger"><?php echo $cancel_amount1; ?></td>
				<td class="warning"><?php echo $balance_amount1; ?></td>
        		<td><?= $invoice_btn.' '.$voucher_btn ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
	<tfoot>
		<tr>
		    <th colspan="3" class="text-right active">Total</th>		    
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
$('#tbl_exc_list').dataTable({
	"pagingType": "full_numbers"
});
</script>
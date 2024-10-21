<?php
include "../../../../../../model/model.php";

$customer_id = $_SESSION['customer_id'];
$booking_id = $_POST['booking_id'];

$query = "select * from bus_booking_master where 1 and delete_status='0' ";
if($booking_id!=""){
	$query .= " and booking_id='$booking_id'";
}
$query .= " and customer_id='$customer_id'";
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">
	
<table class="table table-bordered cust_table" id="tbl_list" style="margin:20px 0 !important;">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Booking_ID</th>
			<th>Booking_Date</th>
			<th>Total_bus</th>
			<th>Bus_Operator</th>
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
		$count = 0;
		$total_amount = 0;
		$total_paid = 0;
		$total_cancel = 0;
		$total_balance = 0;
		$sq_booking = mysqlQuery($query);
		while($row_booking = mysqli_fetch_assoc($sq_booking)){

			$pass_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$row_booking[booking_id]'"));
			$cancel_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$row_booking[booking_id]' and status='Cancel'"));
			if($pass_count==$cancel_count){
				$bg="danger";
			}
			else{
				$bg="";
			}

			$date = $row_booking['created_at'];
			$yr = explode("-", $date);
			$year =$yr[0];
			$sq_total_seates = mysqli_num_rows(mysqlQuery("select booking_id from bus_booking_entries where booking_id='$row_booking[booking_id]'")); 

			$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from bus_booking_payment_master where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' AND clearance_status!='Cancelled'"));
			$sale_total_amount = $row_booking['net_total'];
			$cancel_amount = $row_booking['cancel_amount'];
			
			$paid_amount =$sq_payment_info['sum'];
			$paid_amount = ($paid_amount == '')?'0':$paid_amount;
			
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

			$sq_entry = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_entries where booking_id='$row_booking[booking_id]'"));
			$sq_customer = mysqli_fetch_assoc(mysqlQuery("select first_name, last_name from customer_master where customer_id='$row_booking[customer_id]'"));

			//Total
			$total_amount += $sale_total_amount + $sq_payment_info['sumc'];
			$total_paid += $paid_amount + $sq_payment_info['sumc'];
			$total_cancel += $cancel_amount;
			$total_balance += $balance_amount;

			if(floatval($balance_amount) == 0 && $bg == ''){

				$invoice_no = get_bus_booking_id($row_booking['booking_id'],$year);
				$booking_id = $row_booking['booking_id'];
				$invoice_date = date('d-m-Y',strtotime($row_booking['created_at']));
				$customer_id = $row_booking['customer_id'];
				$service_name = "Bus Invoice";
				$service_charge = $row_booking['service_charge'];
				$service_tax = $row_booking['service_tax_subtotal'];

				//**Basic Cost
				$basic_cost = $row_booking['basic_cost'];			
				$net_amount = $row_booking['net_total'];
				$credit_card_charges = $sq_payment_info['sumc'];
				$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Bus'"));   
				$sac_code = $sq_sac['hsn_sac_code'];
				
				$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/bus_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&service_charge=$service_charge&total_paid=$paid_amount&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&booking_id=$booking_id&credit_card_charges=$credit_card_charges&canc_amount=$cancel_amount&bg=$bg";
				$invoice_btn = '<a data-toggle="tooltip" onclick="loadOtherPage(\'' .$url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
			}else{
				$invoice_btn = 'NA';
			}
			?>
			<tr class="<?= $bg ?>">
				<td><?= ++$count ?></td>
				<td><?= get_bus_booking_id($row_booking['booking_id'],$year) ?></td>
				<td><?= get_date_user($row_booking['created_at']) ?></td>
				<td><?= $sq_total_seates ?></td>
				<td><?= $sq_entry['company_name'] ?></td>
				<td>
					<button class="btn btn-info btn-sm" onclick="view_modal(<?= $row_booking['booking_id'] ?>)" title="View Details" id="bus-<?= $row_booking['booking_id'] ?>"><i class="fa fa-eye"></i></button>
				</td>
				<td class="info"><?= number_format($sale_total_amount,2) ?></td>
				<td class="success"><?= $paid_amount ?></td>
				<td class="danger"><?= $row_booking['cancel_amount'] ?></td>
				<td class="warning"><?= number_format($balance_amount,2) ?></td>
        		<td><?= $invoice_btn ?></td>
			</tr>
			<?php
		}
		?>	
	</tbody>
	<tfoot>
		<tr class="active">
			<th colspan="5"></th>
			<th class="text-right">Total</th>
			<th class="text-right info"><?= number_format($total_amount,2) ?></th>
			<th class="text-right success"><?= number_format($total_paid,2) ?></th>
			<th class="text-right danger"><?=  number_format($total_cancel,2) ?></th>
			<th class="text-right warning"><?= number_format($total_balance,2) ?></th>
			<th class="active"></th>
		</tr>
</tfoot>
</table>

</div> </div> </div>
<script type="text/javascript">
$('#tbl_list').dataTable({
	"pagingType": "full_numbers"
});
</script>

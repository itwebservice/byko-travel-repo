<?php
include "../../../../../../model/model.php";
$customer_id = $_SESSION['customer_id'];
$booking_id = $_POST['booking_id'];

$query = "select * from hotel_booking_master where 1 and delete_status='0' ";
$query .=" and customer_id='$customer_id'";
if($booking_id!=""){
	$query .=" and booking_id='$booking_id'";
}
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">

<table class="table table-bordered bg_white cust_table" id='tbl_booking_list' style="margin:20px 0 !important;">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Booking_ID</th>
			<th>Booking_Date</th>
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
		$count = 0;
		$available_bal=0;
		$pending_bal=0;
		$total_amount = 0;
		$total_paid = 0;
		$total_cancel = 0;
		$total_balance = 0;

		$sq_booking = mysqlQuery($query);
		while($row_booking = mysqli_fetch_assoc($sq_booking)){

			$date = $row_booking['created_at'];
			$yr = explode("-", $date);
			$year =$yr[0];
			$pass_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$row_booking[booking_id]'"));
			$cancel_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$row_booking[booking_id]' and status='Cancel'"));
			if($pass_count==$cancel_count){
				$bg="danger";
			}
			else {
				$bg="";
			}
			$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
			$cancel_amount = $row_booking['cancel_amount'];
			$sq_payment_total = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(`credit_charges`) as sumc from hotel_booking_payment where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' AND clearance_status!='Cancelled'"));

			$credit_card_charges = $sq_payment_total['sumc'];
			$paid_amount = $sq_payment_total['sum'];
			$paid_amount = ($paid_amount == '')?'0':$paid_amount;		

			$sale_total_amount = $row_booking['total_fee'];
			
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

			$sale_total_amount1 = currency_conversion($currency,$row_booking['currency_code'],$sale_total_amount + $credit_card_charges);
			$paid_amount1 = currency_conversion($currency,$row_booking['currency_code'],$paid_amount + $credit_card_charges);
			$cancel_amount1 = currency_conversion($currency,$row_booking['currency_code'],$cancel_amount);
			$balance_amount1 = currency_conversion($currency,$row_booking['currency_code'],$balance_amount);
			
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

				$serv_voucher = '<button data-toggle="tooltip" title="Download Service Voucher" class="btn btn-info btn-sm" onclick="voucher_display('.$row_booking['booking_id'] .')" id="edith-'.$row_booking['booking_id'] .'" title="Update Details"><i class="fa fa-print"></i></button>';

				$invoice_no = get_hotel_booking_id($row_booking['booking_id'],$year);
				$booking_id = $row_booking['booking_id'];
				$invoice_date = date('d-m-Y',strtotime($row_booking['created_at']));
				$customer_id = $row_booking['customer_id'];
				$service_name = "Hotel Invoice";	
				$service_tax = $row_booking['service_tax_subtotal'];
				//**Basic Cost
				$basic_cost = $row_booking['sub_total'];
				$service_charge = $row_booking['service_charge'];
				//**Net Amount
				$hotel_total_cost=$row_booking['total_fee'] + $credit_card_charges;
				$net_amount = $hotel_total_cost;
				$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Hotel / Accommodation'"));   
				$sac_code = $sq_sac['hsn_sac_code'];
				$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/hotel_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&service_charge=$service_charge&total_paid=$paid_amount&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&booking_id=$booking_id&credit_card_charges=$credit_card_charges&canc_amount=$cancel_amount&bg=$bg";
				$invoice_btn = '<a onclick="loadOtherPage(\''. $url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
			}else{
				$invoice_btn = '';
				$serv_voucher = 'NA';
			}
			?>
			<tr class="<?=$bg?>">
				<td><?= ++$count ?></td>
				<td><?= get_hotel_booking_id($row_booking['booking_id'],$year) ?></td>
				<td><?= date('d-m-Y', strtotime($row_booking['created_at'])) ?></td>	
				<td><button class="btn btn-info btn-sm" onclick="booking_display_modal(<?= $row_booking['booking_id'] ?>)" title="View Details" id="hotel-<?= $row_booking['booking_id'] ?>"><i class="fa fa-eye"></i></button>
				</td>		
				<td class="info"><?= $sale_total_amount1 ?></td>
				<td class="success"><?= $paid_amount1 ?></td>
				<td class="danger"><?= $cancel_amount1 ?></td>
				<td class="warning"><?= $balance_amount1 ?></td>
        		<td><?= $invoice_btn.' '.$serv_voucher ?></td>	
			</tr>
			<?php
		}
		?>
	</tbody>
	<tfoot>
		<tr class="active">
			<th colspan="4" class="text-right">Total</th>
			<th class="text-right info"><?= number_format($total_amount,2) ?></th>
			<th class="text-right success"><?= number_format($total_paid,2) ?></th>
			<th class="text-right danger"><?= number_format($total_cancel,2) ?></th>
			<th class="text-right warning"><?= number_format(($total_balance),2) ?></th>
		    <th class="active"></th>
		</tr>
	</tfoot>
</table>

</div> </div> </div>
<script type="text/javascript">
$('#tbl_booking_list').dataTable({
	"pagingType": "full_numbers"
});
</script>
<?php
include "../../../../../../model/model.php";
$ticket_id = $_POST['ticket_id'];
$customer_id = $_SESSION['customer_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='visa_passport_ticket/ticket/index.php'"));
$branch_status = $sq['branch_status'];
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">
<table class="table table-bordered bg_white cust_table" id="ticket_list" style="margin:20px 0 !important">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Booking_ID</th>
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
		$total_amount = 0;
		$total_paid = 0;
		$total_cancel = 0;
		$total_balance = 0;
		$query = "select * from ticket_master where 1 and delete_status='0' ";
		$query .= " and customer_id='$customer_id'";
		if($ticket_id!=""){
			$query .= " and ticket_id='$ticket_id'";
		}
		$count = 0;
		$sq_ticket = mysqlQuery($query);
		while($row_ticket = mysqli_fetch_assoc($sq_ticket)){

			$cancel_type = $row_ticket['cancel_type'];
			$date = $row_ticket['created_at'];
			$yr = explode("-", $date);
			$year = $yr[0];
			$pass_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_ticket[ticket_id]'"));
			$cancel_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_ticket[ticket_id]' and status='Cancel'"));
			if($pass_count==$cancel_count){
				$bg="danger";
			}
			else if($cancel_type == 2 || $cancel_type == 3){
				$bg="warning";
			} 
			else {
				$bg="";
			}
			$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
			$sq_paid_amount = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from ticket_payment_master where ticket_id='$row_ticket[ticket_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
			
			$sale_total_amount = $row_ticket['ticket_total_cost'];
			$cancel_amount = $row_ticket['cancel_amount'];
			$paid_amount = $sq_paid_amount['sum'];
			$paid_amount = ($paid_amount == '') ? '0' : $paid_amount;
			
			if($row_ticket['cancel_type'] == '1'){
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
			}else if($row_ticket['cancel_type'] == '2' || $row_ticket['cancel_type'] == '3'){
				$cancel_estimate_data = json_decode($row_ticket['cancel_estimate']);
				$cancel_estimate = (!isset($cancel_estimate_data)) ? 0 : $cancel_estimate_data[0]->ticket_total_cost;
				$balance_amount = $sale_total_amount - floatval($cancel_estimate) + $cancel_amount - $paid_amount;
			}
			else{
				$balance_amount = $sale_total_amount - $paid_amount;
			}
			$balance_amount = ($balance_amount < 0) ? 0 : $balance_amount;
			//Total
			$total_amount += $sale_total_amount + $sq_paid_amount['sumc'];
			$total_paid += $paid_amount + $sq_paid_amount['sumc'];
			$total_cancel += $cancel_amount;
			$total_balance += $balance_amount;
			$url = '';
			$btn_eticket = '';
			$paid_amount1 = $paid_amount + $sq_paid_amount['sumc'];
			if(floatval($balance_amount) == 0 && $row_ticket['cancel_type'] != 1){
				
				$sq_tickets_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_upload_entries where ticket_id='$row_ticket[ticket_id]'"));
				if($sq_tickets_count > 0){

					$sq_tickets = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_upload_entries where ticket_id='$row_ticket[ticket_id]'"));
					$url = explode('uploads/', $sq_tickets['ticket_url']);
					$url = ($sq_tickets['ticket_url'] != '') ? BASE_URL.'uploads/'.$url[1] : '';
					$url = '<button class="btn btn-info btn-sm"><a href="'. $url.'" download title="Download ticket"><i class="fa fa-download"></i></a></button>';
				}
				$ticket_id = $row_ticket['ticket_id'];
				$invoice_date = date('d-m-Y',strtotime($row_ticket['created_at']));
				$btn_eticket = '<a style="display:inline-block" onclick="loadOtherPage(\''. BASE_URL."model/app_settings/print_html/booking_form_html/flightTicket.php?ticket_id=$ticket_id&invoice_date=$invoice_date&branch_status=yes" .'\')" class="btn btn-info btn-sm" title="Download E_Ticket"><i class="fa fa-print"></i></a>';
				
				$invoice_no = get_ticket_booking_id($ticket_id,$year);
				$customer_id = $row_ticket['customer_id'];
				$service_name = "Flight Invoice";		
				$service_charge = $row_ticket['service_charge'];
				$service_tax = $row_ticket['service_tax_subtotal'];
				//Other taxes
				$other_tax = $row_ticket['other_taxes'];
				$yq_tax = $row_ticket['yq_tax'];
				//**Basic Cost
				$basic_cost1 = $row_ticket['basic_cost'] + $other_tax + $yq_tax;
				$basic_cost2 = $row_ticket['basic_cost'];

				$roundoff = $row_ticket['roundoff'];
				$bsmValues = $row_ticket['bsm_values'];
				$bsmValues = http_build_query(array('bsmValues' => $bsmValues));
				$tds = $row_ticket['tds'];
				$discount = $row_ticket['basic_cost_discount'];
				$markup = $row_ticket['markup'];
				$markup_tax = $row_ticket['service_tax_markup'];
				$credit_card_charges = $sq_paid_amount['sumc'];
				$cancel_type = $row_ticket['cancel_type'];

				$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Flight'"));   
				$sac_code = $sq_sac['hsn_sac_code'];
				$net_amount = $row_ticket['ticket_total_cost'];
				$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/flight_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost2&service_charge=$service_charge&taxation_type=&service_tax_per=&service_tax=$service_tax&net_amount=$net_amount&ticket_id=$ticket_id&total_paid=$paid_amount1&balance_amount=$balance_amount&sac_code=$sac_code&branch_status=yes&credit_card_charges=$credit_card_charges&canc_amount=$cancel_amount&bg=$bg&cancel_type=$cancel_type";
				$invoice_btn = '<a style="display:inline-block" onclick="loadOtherPage(\''. $url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print"></i></a>';
				
			}else{
				$url = '';
				$btn_eticket = 'NA';
				$invoice_btn = '';
			}
			?>
			<tr class="<?=$bg?>">
				<td><?= ++$count ?></td>
				<td><?= get_ticket_booking_id($row_ticket['ticket_id'],$year) ?></td>
				<td><button class="btn btn-info btn-sm" onclick="package_view_modal(<?= $row_ticket['ticket_id'] ?>)" title="View Details" id="flight-<?= $row_ticket['ticket_id'] ?>"><i class="fa fa-eye" aria-hidden="true"></i></button></td>
				<td class="info"><?= number_format($sale_total_amount + $sq_paid_amount['sumc'],2) ?></td>
				<td class="success"><?= number_format($paid_amount + $sq_paid_amount['sumc'],2) ?></td>
				<td class="danger"><?= $cancel_amount ?></td>
				<td class="warning"><?= number_format($balance_amount,2) ?></td>
        		<td><?= $invoice_btn.' '.$btn_eticket.' '.$url ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
	<tfoot>
		<tr class="active">
			<th colspan="3"></th>
			<th class="text-right info"><?= number_format($total_amount, 2); ?></th>
			<th class="text-right success"><?= number_format($total_paid, 2); ?></th>
			<th class="text-right danger"><?= number_format($total_cancel, 2); ?></th>
			<th class="text-right warning"><?= number_format($total_balance, 2); ?></th>
		    <th class="active"></th>
		</tr>
	</tfoot>
</table>
</div> </div> </div>
<div id="div_ticket_content_display"></div>
<script>
$('#ticket_list').dataTable({
	"pagingType": "full_numbers"
});

function package_view_modal(booking_id)
{
	$('#flight-'+booking_id).button('loading');
	var base_url = $('#base_url').val();
	$.post(base_url+'view/customer/other/bookings/ticket/home/view/index.php', { ticket_id : booking_id }, function(data){
		$('#div_ticket_content_display').html(data);
		$('#flight-'+booking_id).button('reset');
    });
}
</script>

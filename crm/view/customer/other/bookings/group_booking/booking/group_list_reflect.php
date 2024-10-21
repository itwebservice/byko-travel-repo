<?php
include "../../../../../../model/model.php";

$tourwise_traveler_id = $_POST['tourwise_traveler_id'];
$customer_id = $_SESSION['customer_id'];
$status = true;

$query = "select * from tourwise_traveler_details where customer_id='$customer_id' and delete_status='0' ";
if($tourwise_traveler_id!=""){
	$query .=" and id = '$tourwise_traveler_id'";
}
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">
	<table class="table table-bordered bg_white cust_table" id="group_table" style="margin: 20px 0 !important;">

		<thead>
			<tr class="table-heading-row1">
			    <th>S_No.</th>
			    <th>Booking_ID</th>
			    <th>Tour_Name</th>
			    <th>Tour_Date</th>
			    <th>View</th>
			    <th class="info">total_Amount</th>
			    <th class="success">Paid_Amount </th>  
			    <th class="danger">Cncl_Amount</th>
			    <th class="warning">Balance</th>
				<th class="text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$count = 0;
		$pen_ref=0;
		$pen_paid=0;
		$total_amount = 0;
		$total_paid1 = 0;
		$total_cancel = 0;
		$total_balance = 0;
		$sq1 =mysqlQuery($query);
		while($row1 = mysqli_fetch_assoc($sq1))
		{
			$date = $row1['form_date'];
			$yr = explode("-", $date);
			$year = $yr[0];
			$tourwise_id = $row1['id']; 
			$pass_count = mysqli_num_rows(mysqlQuery("select * from  travelers_details where traveler_group_id='$row1[id]'"));
			$cancelpass_count = mysqli_num_rows(mysqlQuery("select * from  travelers_details where traveler_group_id='$row1[id]' and status='Cancel'"));
			$bg="";
			if($row1['tour_group_status']=="Cancel"){
				$bg="danger";
			}
			else{
				if($pass_count==$cancelpass_count){
					$bg="danger";
				}
			}
			$sq_travler_personal_info = mysqli_fetch_assoc(mysqlQuery("select * from traveler_personal_info where tourwise_traveler_id='$tourwise_id'"));

			$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row1[customer_id]'"));
			
			$sq_tour_name = mysqlQuery("select tour_name from tour_master where tour_id='$row1[tour_id]'");
			$row1_tour_name = mysqli_fetch_assoc($sq_tour_name);
			$tour_name = $row1_tour_name['tour_name'];

			$sq_tour_group_name = mysqlQuery("select from_date,to_date from tour_groups where group_id='$row1[tour_group_id]'");
			$row1_tour_group_name = mysqli_fetch_assoc($sq_tour_group_name);
			$tour_group_from = date("d-m-Y", strtotime($row1_tour_group_name['from_date']));
			$tour_group_to = date("d-m-Y", strtotime($row1_tour_group_name['to_date']));

			$sale_total_amount=$row1['net_total'];
			if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

			//Paid
			$query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum,sum(`credit_charges`) as sumc from payment_master where tourwise_traveler_id='$row1[id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
			$credit_card_charges = $query['sumc'];
			$paid_amount = $query['sum'];

			if($row1['tour_group_status'] == 'Cancel'){
				//Group Tour cancel
				$cancel_tour_count2=mysqli_num_rows(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row1[id]'"));
				if($cancel_tour_count2 >= '1'){
					$cancel_tour=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row1[id]'"));
					$cancel_amount = $cancel_tour['cancel_amount'];
				}
				else{ 
					$cancel_amount = 0;
				}
			}
			else{
				// Group booking cancel
				$cancel_esti_count1=mysqli_num_rows(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row1[id]'"));
				if($cancel_esti_count1 >= '1'){
					$cancel_esti1=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row1[id]'"));
					$cancel_amount = $cancel_esti1['cancel_amount'];
				}
				else{ $cancel_amount = 0; }
			}

			if($row1['tour_group_status'] == 'Cancel'){
				if($cancel_amount > $paid_amount){
					$balance_amount = $cancel_amount - $paid_amount;
				}
				else{
					$balance_amount = 0;
				}
			}else{
				if($cancel_esti_count1 >= '1'){
					if($cancel_amount > $paid_amount){
						$balance_amount = $cancel_amount - $paid_amount;
					}
					else{
						$balance_amount = 0;
					}
				}
				else{
					$balance_amount = $sale_total_amount - $paid_amount;
				}
			}

			$net_total1 = currency_conversion($currency,$row1['currency_code'],$row1['net_total']+$credit_card_charges);
			$paid_amount1 = currency_conversion($currency,$row1['currency_code'],$paid_amount+$credit_card_charges);
			$cancel_amount1 = currency_conversion($currency,$row1['currency_code'],$cancel_amount);
			$balance_amount1 = currency_conversion($currency,$row1['currency_code'],$balance_amount);

			$net_total1_string = explode(' ',$net_total1);
			$footer_net_total = str_replace(',', '', $net_total1_string[1]);
			$paid_amount1_string = explode(' ',$paid_amount1);
			$footer_paid_amount = str_replace(',', '', $paid_amount1_string[1]);
			$cancel_amount1_string = explode(' ',$cancel_amount1);
			$footer_cancel_amount = str_replace(',', '', $cancel_amount1_string[1]);
			$balance_amount1_string = explode(' ',$balance_amount1);
			$footer_balance_amount = str_replace(',', '', $balance_amount1_string[1]);
			
			//Total
			$total_amount += $footer_net_total;
			$total_paid1 += $footer_paid_amount;
			$total_cancel += $footer_cancel_amount;
			$total_balance += $footer_balance_amount;

			$count++;
			$link = "";
			$link2 = "";
			$link3 = "";
			if(floatval($balance_amount) == 0 && $bg == ''){
				if($row1['train_upload_ticket'] !=""){
					$newUrl = preg_replace('/(\/+)/','/',$row1['train_upload_ticket']);
					$newUrl = str_replace("../","", $newUrl);
					$newUrl = BASE_URL.$newUrl;
				
					$link = '<a href="'.$newUrl .'" class="btn btn-info btn-sm" title="Download Train Ticket" download><i class="fa fa-download"></i></a>';
				}	
				if($row1['plane_upload_ticket'] != ""){
					$newUrl = preg_replace('/(\/+)/','/',$row1['plane_upload_ticket']);      
					$newUrl = str_replace("../","", $newUrl);  
					$newUrl = BASE_URL.$newUrl;        
					$link2 = '<a href="'. $newUrl .'" class="btn btn-info btn-sm" title="Download Flight Ticket" download><i class="fa fa-download"></i></a>';
				}
				if($row1['cruise_upload_ticket'] != ""){
					$newUrl = preg_replace('/(\/+)/','/',$row1['cruise_upload_ticket']);      
					$newUrl = str_replace("../","", $newUrl);  
					$newUrl = BASE_URL.$newUrl;
					$link3 = '<a href="'. $newUrl .'" class="btn btn-info btn-sm" title="Download Cruise Ticket" download><i class="fa fa-download"></i></a>';
				}
				
				$invoice_no = get_group_booking_id($row1['id'],$year);
				$invoice_date = date('d-m-Y',strtotime($row1['form_date']));
				$customer_id = $row1['customer_id'];
				$service_name = "Group Invoice";
			
				//Net amount
				$net_total = 0;
				$net_total  =$row1['net_total'] + $credit_card_charges;
			
				$taxation_type = $row1['taxation_type'];
			
				//basic amount
				$train_expense = $row1['train_expense'];
				$plane_expense = $row1['plane_expense'];
				$cruise_expense = $row1['cruise_expense'];
				$visa_amount = $row1['visa_amount'];
				$insuarance_amount = $row1['insuarance_amount'];
				$tour_subtotal = $row1['tour_fee_subtotal_1'];
				$basic_cost = $train_expense +$plane_expense +$cruise_expense +$visa_amount +$insuarance_amount +$tour_subtotal;	
				
				$sq_tour = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id='$row1[tour_id]'"));
				$sq_group = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where group_id='$row1[tour_group_id]'"));
				
				$sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Group Tour'"));   
				$sac_code = $sq_sac['hsn_sac_code'];
				$tour_date = get_date_user($sq_group['from_date']);
				$tour_to_date = get_date_user($sq_group['to_date']);
				$booking_id = $row1['id'];
				
				$adults = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row1[traveler_group_id]' and adolescence='Adult'"));
				$childw = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row1[traveler_group_id]' and adolescence='Child With Bed'"));
				$childwo = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row1[traveler_group_id]' and adolescence='Child Without Bed'"));
				$child = intval($childw) + intval($childwo);
				$infants = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row1[traveler_group_id]' and adolescence='Infant'"));
				//Flights
				$sq_f_count = mysqli_num_rows(mysqlQuery("select * from plane_master where tourwise_traveler_id='$row1[id]'")); 
				$flights = '';
				$count = 1;
				if($sq_f_count != '0'){
					$sq_entry = mysqlQuery("select * from plane_master where tourwise_traveler_id='$row1[id]'");
					while($row_entry = mysqli_fetch_assoc($sq_entry)){
				
						$seperator = ($sq_f_count != $count) ? '/ ' : '';
						$flights .= 'From '.$row_entry['from_location'].' To '.$row_entry['to_location'].$seperator;
						$count++;
					}
				}
				//Train
				$sq_f_count = mysqli_num_rows(mysqlQuery("select * from train_master where tourwise_traveler_id='$row1[id]'")); 
				$trains = '';
				$count = 1;
				if($sq_f_count != '0'){
					$sq_entry = mysqlQuery("select * from train_master where tourwise_traveler_id='$row1[id]'");
					while($row_entry = mysqli_fetch_assoc($sq_entry)){
						$seperator = ($sq_f_count != $count) ? '/ ' : '';
						$trains .= 'From '.$row_entry['from_location'].' To '.$row_entry['to_location'].$seperator;
						$count++;
					}
				}
				//Cruise
				$sq_f_count = mysqli_num_rows(mysqlQuery("select * from group_cruise_master where booking_id='$row1[id]'")); 
				$cruises = '';
				if($sq_f_count != '0'){
					$count = 0;
					$sq_entry = mysqlQuery("select * from group_cruise_master where booking_id='$row1[id]'");
					while($row_entry = mysqli_fetch_assoc($sq_entry)){
						$count++;
						$cruises .= 'Cabin '.$row_entry['cabin'].', Route '.$row_entry['route'];
						$cruises .= ($count<$sq_f_count)?' / ':'';
					}
				}
				$tour = $sq_tour['tour_name'];
				$group = get_date_user($sq_group['from_date']).' to '.get_date_user($sq_group['to_date']);
				
				$url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/git_fit_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&service_name=$service_name&basic_cost=$basic_cost&taxation_type=$taxation_type&train_expense=$train_expense&plane_expense=$plane_expense&cruise_expense=$cruise_expense&visa_amount=$visa_amount&insuarance_amount=$insuarance_amount&tour_subtotal=$tour_subtotal&train_service_charge=&plane_service_charge=&cruise_service_charge=&visa_service_charge=&insuarance_service_charge=&train_service_tax=&plane_service_tax=&cruise_service_tax=&visa_service_tax=&insuarance_service_tax=&tour_service_tax=&train_service_tax_subtotal=&plane_service_tax_subtotal=&cruise_service_tax_subtotal=&visa_service_tax_subtotal=&insuarance_service_tax_subtotal=&tour_service_tax_subtotal=&total_paid=$paid_amount&net_total=$net_total&sac_code=$sac_code&branch_status=yes&tour_name=$tour&booking_id=$booking_id&credit_card_charges=$credit_card_charges&tcs_tax=$row1[tcs_tax]&tcs_per=$row1[tcs_per]&tour_date=$tour_date&tour_to_date=$tour_to_date&child=$child&adults=$adults&infants=$infants&flights=$flights&trains=$trains&cruises=$cruises&canc_amount=&bg=$bg";
				$invoice_btn = '<a data-toggle="tooltip" onclick="loadOtherPage(\''. $url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print" data-toggle="tooltip"></i></a>';
			
			}else{
				$invoice_btn = 'NA';
			}
			?>
			<tr class="<?= $bg ?>">
				<td><?php echo $count; ?></td>
				<td><?php echo get_group_booking_id($tourwise_id,$year); ?></td>
				<td><?php echo $tour_name; ?></td>
				<td><?php echo $tour_group_from." to ".$tour_group_to; ?></td>
				<td><button class="btn btn-info btn-sm" onclick="display_modal(<?php echo $row1['id']; ?>)" title="View Details" id="group-<?php echo $row1['id']; ?>"><i class="fa fa-eye"></i></button></td>
				<td class="info"><?= $net_total1 ?></td>
				<td class="success"><?= $paid_amount1?></td>
				<td class="danger"><?= $cancel_amount1 ?></td>
				<td class="warning"><?= $balance_amount1 ?></td>
        		<td><?= $invoice_btn.' '.$link.' '.$link2.' '.$link3 ?></td>
			</tr>
			<?php		
		}
		?>
		</tbody>
		<tfoot>
			<tr class="active">
				<th class="text-right" colspan="5"><?= 'TOTAL' ?></th>
				<th class="text-right info"><?= number_format($total_amount,2);?></th>
				<th class="text-right success"><?= number_format($total_paid1,2);?></th>
				<th class="text-right danger"><?=  number_format($total_cancel,2);?></th>
				<th class="text-right warning"><?= number_format($total_balance,2);?></th>
		    	<th class="active"></th>
			</tr>
		</tfoot>
	</table>
</div></div></div>
<div id="view_modal"></div>
<script>
$('#group_table').dataTable({
	"pagingType": "full_numbers"
});
function display_modal(id)
{
    $('#group-'+id).button('loading');
    $.post('bookings/group_booking/booking/view/index.php', { id : id }, function(data){
    	$('#view_modal').html(data);
    	$('#group-'+id).button('reset');
    });
}
</script>
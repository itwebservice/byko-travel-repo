<?php
include "../../../../../../model/model.php";

$customer_id = $_SESSION['customer_id'];
$ticket_id = $_POST['ticket_id'];

$query = "select * from ticket_trip_entries where 1 ";
if($ticket_id!=""){
	$query .=" and ticket_id='$ticket_id'";	
}
if($customer_id!=""){
	$query .=" and ticket_id in ( select ticket_id from ticket_master where customer_id='$customer_id' )";	
}
$query .= " and ticket_id in (select ticket_id from ticket_master where delete_status='0')";
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">

<table class="table table-bordered cust_table bg_white" id="tbl_ticket_report" style="margin:20px 0 !important">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Booking_ID</th>
			<th>Customer_Name</th>
			<th>Passenger_Name</th>
			<th>Departure_Date&Time</th>
			<th>Arrival_Date&Time</th>
			<th>Airline</th>
			<th>Cabin</th>
			<th>Flight_No.</th>
			<th>GDS_PNR</th>
			<th>Sector(From_To)</th>
			<th>Ticket_Status</th>
			<th>Basic_Fare</th>
			<th>Ticket</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		
		$sq_trip = mysqlQuery($query);	
		while($row_trip = mysqli_fetch_assoc($sq_trip)){

			$sq_tickets = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_upload_entries where ticket_id='$row_trip[ticket_id]'"));
			if(isset($sq_tickets['ticket_url'])){
				$url = $sq_tickets['ticket_url'];
				$url = explode('uploads/', $url);
				$url = BASE_URL.'uploads/'.$url[1];
			}else{
				$url = '';
			}

			$pass_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_trip[ticket_id]'"));
			$cancel_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$row_trip[ticket_id]' and status='Cancel'"));
			if($row_trip['status']=='Cancel'){
				$bg="danger";
			}
			else {
				$bg="#fff";
			}

			$sq_ticket = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$row_trip[ticket_id]' and delete_status='0'"));
			$date = $sq_ticket['created_at'];
			$yr = explode("-", $date);
			$year =$yr[0];

			$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_ticket[customer_id]'"));
			if($sq_customer_info['type'] == 'Corporate'||$sq_customer_info['type'] == 'B2B'){
				$cust_name = $sq_customer_info['company_name'];
			}else{
				$cust_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
			}
			$sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$row_trip[passenger_id]'"));
			?>
			<tr class="<?= $bg ?>">
				<td><?= ++$count ?></td>
				<td><?= get_ticket_booking_id($row_trip['ticket_id'],$year) ?></td>
				<td><?= $cust_name ?></td>
				<td><?= $sq_pass['first_name'].' '.$sq_pass['last_name'] ?></td>
				<td><?=  date('d-m-Y H:i', strtotime($row_trip['departure_datetime'])) ?></td>
				<td><?= date('d-m-Y H:i', strtotime($row_trip['arrival_datetime'])) ?></td>
				<td><?= $row_trip['airlines_name'] ?></td>
				<td><?= $row_trip['class'] ?></td>
				<td><?= $row_trip['flight_no'] ?></td>
				<td><?= strtoupper($row_trip['airlin_pnr']) ?></td>
				<td><?= $row_trip['departure_city'].' -- '.$row_trip['arrival_city'] ?></td>
				<td><?= $row_trip['ticket_status'] ?></td>
				<td><?= $row_trip['basic_fare'] ?></td>
				<td>
					<?php if(isset($sq_tickets['ticket_url'])){  ?>
					<a href="<?= $url ?>" download title="Download Ticket" class="btn btn-info btn-sm"><i class="fa fa-download"></i></a>
					<?php }else{ echo 'NA'; } ?>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

</div> </div> </div>

<script>
	$('#tbl_ticket_report').dataTable({
	"pagingType": "full_numbers"
});
</script>
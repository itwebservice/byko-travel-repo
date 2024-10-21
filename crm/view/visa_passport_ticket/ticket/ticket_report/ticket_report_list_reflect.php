<?php
include "../../../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$branch_status = $_POST['branch_status'];
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$ticket_id = $_POST['ticket_id'];
$cust_type = isset($_POST['cust_type']) ? $_POST['cust_type'] : '';
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';

$query = "select * from ticket_trip_entries where 1 ";
if($ticket_id!=""){
	$query .=" and ticket_id='$ticket_id'";	
}
if($customer_id!=""){
	$query .=" and ticket_id in ( select ticket_id from ticket_master where customer_id='$customer_id' )";	
}
if($cust_type != ""){
	$query .= " and ticket_id in (select ticket_id from ticket_master where customer_id in ( select customer_id from customer_master where type='$cust_type' ))";
}
if($company_name != ""){
	$query .= " and ticket_id in (select ticket_id from ticket_master where customer_id in ( select customer_id from customer_master where company_name='$company_name' ))";
}
if($branch_status=='yes'){
	if($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
		$query .= " and ticket_id in (select ticket_id from ticket_master where branch_admin_id = '$branch_admin_id')";
	}
	elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
		$query .= " and ticket_id in (select ticket_id from ticket_master where emp_id='$emp_id') and ticket_id in (select ticket_id from ticket_master where branch_admin_id = '$branch_admin_id')";
	}
}
elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
	$query .= " and ticket_id in (select ticket_id from ticket_master where emp_id='$emp_id' ))";
}

if($financial_year_id != ""){
	$query .= " and ticket_id in (select ticket_id from ticket_master where financial_year_id='$financial_year_id')";
}
$query .= " and ticket_id in (select ticket_id from ticket_master where delete_status='0')";
?>
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">

<table class="table table-hover" id="tbl_ticket_report" style="margin: 20px 0 !important;">
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
	    </tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		
		$sq_trip = mysqlQuery($query);	
		while($row_trip = mysqli_fetch_assoc($sq_trip)){

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

			$sq_customer_count = mysqli_num_rows(mysqlQuery("select * from customer_master where customer_id='$sq_ticket[customer_id]'"));
			if($sq_customer_count > 0){
				$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_ticket[customer_id]'"));
				if($sq_customer_info['type'] == 'Corporate'||$sq_customer_info['type'] == 'B2B'){
					$cust_name = $sq_customer_info['company_name'];
				}else{
					$cust_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
				}
			}else{
				$cust_name = '';
			}
			$sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$row_trip[passenger_id]'"));
			?>
			<tr class="<?=$bg?>">
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
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

</div> </div> </div>

<script>
	$('#tbl_ticket_report').dataTable({
		"pagingType": "full_numbers",
		order: [[0, 'desc']],
	});
</script>
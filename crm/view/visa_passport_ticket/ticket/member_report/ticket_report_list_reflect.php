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

$query = "select * from ticket_master where financial_year_id='$financial_year_id' and delete_status='0' ";
if($customer_id!=""){
	$query .=" and customer_id='$customer_id'";
}
if($ticket_id!=""){
	$query .=" and ticket_id='$ticket_id'";
}
if($cust_type != ""){
	$query .= " and customer_id in (select customer_id from customer_master where type = '$cust_type')";
}
if($company_name != ""){
	$query .= " and customer_id in (select customer_id from customer_master where company_name = '$company_name')";
}	
if($role == "B2b"){
	$query .= " and emp_id='$emp_id'";
}
include "../../../../model/app_settings/branchwise_filteration.php";
?>
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">

<table class="table table-hover" id="tbl_ticket_report" style="margin: 20px 0 !important;">
	<thead>
	    <tr class="table-heading-row">
	    	<th>S_No.</th>
			<th>Booking_ID</th>
			<th>Customer_Name</th>
			<th>Passenger_Name</th>
			<th>Adolescence</th>
			<th>Ticket_No</th>
			<th>Airline_Pnr</th>
			<th>Main_Ticket_Number</th>
			<th>Check_In&Cabin_Baggage</th>
			<th>Seat_No</th>
			<th>Meal_plan</th>
	    </tr>
	</thead>
	<tbody>
		<?php
		$count = 0;
		$sq_ticket = mysqlQuery($query);
		while($row_ticket =mysqli_fetch_assoc($sq_ticket)){

			$sq_customer_count = mysqli_num_rows(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
			if($sq_customer_count > 0){
				$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
				if($sq_customer_info['type'] == 'Corporate'||$sq_customer_info['type'] == 'B2B'){
					$cust_name = $sq_customer_info['company_name'];
				}else{
					$cust_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
				}
			}else{
				$cust_name = '';
			}
			$date = $row_ticket['created_at'];
            $yr = explode("-", $date);
        	$year = $yr[0];

			$sq_entry = mysqlQuery("select * from ticket_master_entries where ticket_id='$row_ticket[ticket_id]'");
			while($row_passenger1 = mysqli_fetch_assoc($sq_entry)){
				
				$trip_seat_arr = array();
				$trip_meal_arr = array();
				$from_city_arr = array();
				$to_city_arr = array();
				$seat_nos = explode('/',$row_passenger1['seat_no']);
				$meal_plans = explode('/',$row_passenger1['meal_plan']);
				$i = 0;
				$sq_ticket_trip = mysqlQuery("SELECT * FROM ticket_trip_entries WHERE passenger_id='$row_passenger1[entry_id]'");
				while ($row_trip = mysqli_fetch_assoc($sq_ticket_trip)) {
					if($row_trip['status'] != 'Cancel'){
						$seat_no = isset($seat_nos[$i]) ? $seat_nos[$i] : '';
						$meal_plan = isset($meal_plans[$i]) ? $meal_plans[$i] : '';
						array_push($trip_seat_arr,$seat_no);
						array_push($trip_meal_arr,$meal_plan);
						$dep_city = explode('(',$row_trip['departure_city']);
						$arr_city = explode('(',$row_trip['arrival_city']);
		
						$dep_city1 = explode(')',$dep_city[1]);
						$arr_city1 = explode(')',$arr_city[1]);
						array_push($from_city_arr,$dep_city1[0]);
						array_push($to_city_arr,$arr_city1[0]);
					}
					$i++;
				}
				$seat_no_string = '';
				$meal_plan_string = '';
				for($i = 0; $i < sizeof($trip_seat_arr); $i++){
					$seat_no_string .= ($trip_seat_arr[$i]!='' && $from_city_arr[$i]) ? $trip_seat_arr[$i].' ('.$from_city_arr[$i].'-'.$to_city_arr[$i].')' : '';
					if($i != (sizeof($trip_seat_arr)-1)){
						$seat_no_string .= ($from_city_arr[$i]!='') ? ', ' : '';
					}
				}
				for($i = 0; $i < sizeof($trip_meal_arr); $i++){
					$meal_plan_string .= ($trip_meal_arr[$i]!='' && $from_city_arr[$i]) ? $trip_meal_arr[$i].' ('.$from_city_arr[$i].'-'.$to_city_arr[$i].')' : '';
					if($i != (sizeof($trip_meal_arr)-1)){
						$meal_plan_string .= ($from_city_arr[$i]!='') ? ', ' : '';
					}
				}
				$bg = ($row_passenger1['status']=='Cancel') ? 'danger' : '';
				?>
				<tr class="<?= $bg ?>">
					<td><?= ++$count ?></td>
					<td><?= get_ticket_booking_id($row_ticket['ticket_id'],$year) ?></td>
					<td><?= $cust_name ?></td>
					<td><?= $row_passenger1['first_name']." ".$row_passenger1['last_name'] ?></td>
					<td><?= $row_passenger1['adolescence'] ?></td>
					<td><?php echo ($row_passenger1['ticket_no'] != '') ? strtoupper($row_passenger1['ticket_no']) : 'NA' ?></td>
					<td><?php echo ($row_passenger1['gds_pnr'] != '') ? strtoupper($row_passenger1['gds_pnr']) : 'NA'; ?></td>
                    <td><?php echo ($row_ticket['ticket_reissue']) ? strtoupper($row_passenger1['main_ticket']) : 'NA'; ?></td>
                    <td><?php echo ($row_passenger1['baggage_info'] != '') ? $row_passenger1['baggage_info'] : 'NA'; ?></td>
                    <td><?php echo ($seat_no_string != '' ) ? $seat_no_string : 'NA'; ?></td>
                    <td><?php echo ($meal_plan_string != '' ) ? $meal_plan_string : 'NA'; ?></td>
				</tr>
				<?php
			}
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
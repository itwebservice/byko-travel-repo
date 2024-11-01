<?php
include "../../../../../../model/model.php";
$enquiry_type = $_GET['enquiry_type'];
$emp_id = $_GET['emp_id'];
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];
$enquiry_status = $_GET['enquiry_status'];
$branch_status = $_GET['branch_status'];
$role = $_SESSION['role'];
?>

<div class="row mg_tp_20"> <div class="col-md-12 no-pad mg_tp_10"> <div class="table-responsive">
	<table id="enquiry_table" class="table table-hover table-bordered mg_bt_0 bg_white" style="margin: 20px 0 !important;">
		<thead>
			<tr class="table-heading-row">
				<th>S_No.</th>
				<th>Enquiry_Type</th>
				<th>Assigned_To</th>
				<th>Interested Tour</th>
				<th>Enquiry Status</th>
				<th>Followup Status</th>
				<th>Budgeted Amount</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$query = "SELECT * FROM `enquiry_master` where status!='Disabled'";
			if($emp_id!=""){
				$query .=" and assigned_emp_id='$emp_id'";
			}
			elseif($branch_status=='yes' && $role=='Branch Admin'){
				$query .=" and branch_admin_id = '$branch_admin_id'";
				$query .=" or assigned_emp_id='$emp_id' ";
			}
			if($enquiry_type!=""){
				$query .=" and enquiry_type='$enquiry_type' ";
			}
			if($from_date!="" && $to_date!=""){
				$from_date1 = date('Y-m-d', strtotime($from_date));
				$to_date1 = date('Y-m-d', strtotime($to_date));
				$query .=" and enquiry_date between '$from_date1' and '$to_date1'";
			}
			if($enquiry_status!=""){
				$query.=" and enquiry='$enquiry_status'";
			}
			$query .= " ORDER BY enquiry_id DESC";

			$count = 0;
			$sq_enquiries = mysqlQuery($query);
			while($row = mysqli_fetch_assoc($sq_enquiries)){
				$budget = 0;
				$assigned_emp_id = $row['assigned_emp_id'];
				$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$assigned_emp_id'"));

				$enquiry_content = $row['enquiry_content'];
				$enquiry_content_arr1 = (json_decode($enquiry_content)=== null) ? json_decode($enquiry_content, true) : [];
				if(isset($enquiry_content_arr1) && sizeof($enquiry_content_arr1) > 0){
					foreach($enquiry_content_arr1 as $enquiry_content_arr2){
						
						if(isset($enquiry_content_arr2['name']) && $enquiry_content_arr2['name']=="budget"){

							$budget = $enquiry_content_arr2['value'];
						}
					}
				}

				$status_count1 = mysqli_num_rows(mysqlQuery("select * from enquiry_master_entries where enquiry_id='$row[enquiry_id]' and followup_type='' "));
				if($status_count1==1){
					$followup_date1 = $row['followup_date'];
				}
				else{
					$enquiry_status1 = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master_entries where entry_id=(select max(entry_id) from enquiry_master_entries where enquiry_id='$row[enquiry_id]') "));
					$followup_date1 = $enquiry_status1['followup_date'];
				}

				$status_count = mysqli_num_rows(mysqlQuery("select * from enquiry_master_entries where enquiry_id='$row[enquiry_id]' "));
				if($status_count>0){
					$enquiry_status = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master_entries where entry_id=(select max(entry_id) from enquiry_master_entries where enquiry_id='$row[enquiry_id]') "));
					$bg = ($enquiry_status['followup_status']=='Converted') ? "success" : "";
					$bg = ($enquiry_status['followup_status']=='Dropped') ? "danger" : $bg;
					$bg = ($enquiry_status['followup_status']=='Active') ? "warning" : $bg;

				}
				else{
					$bg = "";
				}

				$count++;
				?>
				<tr class="<?= $bg ?>">
					<td><?= $count ?></td>
					<td><?= $row['enquiry_type']; ?></td>					
					<td><?=  ($assigned_emp_id == 0)?'Admin':$sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>  
					<td><?= (isset($enquiry_content_arr1[0]) && $row['enquiry_type']=="Package Booking" || $row['enquiry_type']=="Group Booking") ? $enquiry_content_arr1[0]['value'] : "NA" ?></td>
					<td><?= $row['enquiry'] ?></td>
					<td><?= $enquiry_status['followup_status'] ?></td>
					<td><?= number_format(floatval($budget),2); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

</div> </div> </div>
<script>

	$('#enquiry_table').dataTable({
		"pagingType": "full_numbers"
	});
</script>
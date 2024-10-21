<?php
include_once("../../../model/model.php");
$status = $_POST['status'];
?>
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
	
<table class="table table-hover" id="tbl_list" style="margin: 20px 0 !important;">
	<thead>
		<tr class="table-heading-row">
			<th>Dest_ID</th>
			<th>Destination</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		if($status != ''){
			$query = "select * from destination_master where 1 and status='$status'";
		}else{
			
			$query = "select * from destination_master where 1 and status='Active'";
		}
		$sq_airline = mysqlQuery($query);
		while($row_airline = mysqli_fetch_assoc($sq_airline)){
			$bg = ($row_airline['status']=="Inactive") ? "danger" : "";
			?>
			<tr class="<?= $bg ?>">
				<td><?= $row_airline['dest_id'] ?></td>
				<td><?= $row_airline['dest_name'] ?></td>
				<td>
					<button class="btn btn-info btn-sm" onclick="update_modal(<?= $row_airline['dest_id'] ?>)" title="Update Details" id="dest_update-<?= $row_airline['dest_id'] ?>"><i class="fa fa-pencil-square-o"></i></button>
				</td>
			</tr>
			<?php

		}
		?>
	</tbody>
</table>

</div> </div> </div>

<script>
$('#tbl_list').dataTable({
		"pagingType": "full_numbers"
	});
</script>
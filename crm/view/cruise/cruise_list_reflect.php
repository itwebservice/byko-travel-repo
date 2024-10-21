<?php

include "../../model/model.php";

$active_flag = $_POST['active_flag'];
$city_id = $_POST['city_id'];

$query = "select * from cruise_master where 1 ";

if($active_flag!=""){
	$query .=" and active_flag='$active_flag' ";
}
if($city_id!=""){
	$query .=" and city_id='$city_id' ";
}
?>

<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">

	

<table class="table table-bordered table-hover" id="tbl_cruise_list" style="margin: 20px 0 !important;">

	<thead>

		<tr  class="table-heading-row">

			<th>S_No.</th>
			<th>City</th>
			<th>Company_Name</th>
			<th>Mobile</th>
			<th>Contact_Person</th>	
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php 

		$count = 0;

		$sq_cruise = mysqlQuery($query);

		while($row_cruise = mysqli_fetch_assoc($sq_cruise)){




			$bg = ($row_cruise['active_flag']=="Inactive") ? "danger" : "";
			$sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$row_cruise[city_id]'"));
			$mobile_no = $encrypt_decrypt->fnDecrypt($row_cruise['mobile_no'], $secret_key);

			?>

			<tr class="<?= $bg ?>">

				<td><?= ++$count ?></td>
				<td><?= $sq_city['city_name'] ?></td>
				<td><?= $row_cruise['company_name'] ?></td>
				<td><?= $mobile_no ?></td>
				<td><?= $row_cruise['contact_person_name'] ?></td>
				<td>
					<button class="btn btn-info btn-sm" onclick="cruise_update_modal(<?= $row_cruise['cruise_id'] ?>)" title="Update Details" id="update_btn-<?= $row_cruise['cruise_id'] ?>"><i class="fa fa-pencil-square-o"></i></button>
					<button class="btn btn-info btn-sm" onclick="cruise_view_modal(<?= $row_cruise['cruise_id'] ?>)" title="View Details" id="view_btn-<?= $row_cruise['cruise_id'] ?>"><i class="fa fa-eye"></i></button>
				</td>

			</tr>

			<?php

		}

		?>

	</tbody>

</table>



</div> </div> </div>



<div id="div_cruise_update"></div>
<div id="div_cruise_view"></div>


<script>

$('#tbl_cruise_list').dataTable({
		"pagingType": "full_numbers"
	});

function cruise_update_modal(cruise_id){

    $('#update_btn-'+cruise_id).button('loading');
    $('#update_btn-'+cruise_id).prop('disabled',true);
	$.post('cruise_update_modal.php', { cruise_id : cruise_id }, function(data){

		$('#div_cruise_update').html(data);
		$('#update_btn-'+cruise_id).button('reset');
		$('#update_btn-'+cruise_id).prop('disabled',false);

	});
}

function cruise_view_modal(cruise_id){

    $('#view_btn-'+cruise_id).button('loading');
    $('#view_btn-'+cruise_id).prop('disabled',true);
	$.post('view_modal.php', { cruise_id : cruise_id }, function(data){

		$('#div_cruise_view').html(data);
		$('#view_btn-'+cruise_id).button('reset');
		$('#view_btn-'+cruise_id).prop('disabled',false);

	});

}

</script>
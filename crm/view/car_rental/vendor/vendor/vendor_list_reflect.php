<?php

include "../../../../model/model.php";

$active_flag = $_POST['active_flag'];
$city_id = $_POST['city_id'];

$query = "select * from car_rental_vendor where 1 ";
if($active_flag!=""){

	$query .=" and active_flag='$active_flag' ";

}
if($city_id!=""){

	$query .=" and city_id='$city_id' ";

}

?>

<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> 

<div class="table-responsive">

<table class="table table-bordered table-hover" id="tbl_vendor_list" style="margin: 20px 0 !important;width:100%;">

	<thead>

		<tr class="table-heading-row">

			<th>S_No.</th>

			<th>Company_Name</th>

			<th>City</th>

			<th>Mobile</th>
			<th>Contact_Person</th>
			<th>Actions</th>

		</tr>

	</thead>

	<tbody>

		<?php 

		$count = 0;

		$sq_vendor = mysqlQuery($query);

		while($row_venndor = mysqli_fetch_assoc($sq_vendor))

		{

			$count++;




			$sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$row_venndor[city_id]'"));

			$bg = ($row_venndor['active_flag']=="Inactive") ? "danger" : "";
			$mobile_no = $encrypt_decrypt->fnDecrypt($row_venndor['mobile_no'], $secret_key);

			?>

			<tr class="<?= $bg ?>">

				<td><?= $count ?></td>

				<td><?= $row_venndor['vendor_name'] ?></td>
				<td><?= $sq_city['city_name'] ?></td>

				<td><?= $mobile_no ?></td>
				<td><?= $row_venndor['contact_person_name'] ?></td>
				<td>


					<button class="btn btn-info btn-sm" onclick="vendor_update_modal(<?= $row_venndor['vendor_id'] ?>)" title="Update Details" id="update_btn-<?= $row_venndor['vendor_id'] ?>"><i class="fa fa-pencil-square-o"></i> </button>
					<button class="btn btn-info btn-sm" onclick="vendor_view_modal(<?= $row_venndor['vendor_id'] ?>)" title="View Details" id="view_btn-<?= $row_venndor['vendor_id'] ?>"><i class="fa fa-eye"></i></button>

				</td>

			</tr>

			<?php

		}

		?>

	</tbody>

</table>

</div>

</div></div>

<script>

$('#tbl_vendor_list').dataTable({
		"pagingType": "full_numbers"
	});

</script>
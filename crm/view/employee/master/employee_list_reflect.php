<?php
include "../../../model/model.php";
$login_role = isset($_POST['login_role']) ? $_POST['login_role'] : '';
$branch_admin_id = $_SESSION['branch_admin_id'];

$role = $_POST['role'];
$active_flag = isset($_POST['active_flag']) ? $_POST['active_flag'] : '';
$location_id = isset($_POST['location_id']) ? $_POST['location_id'] : '';
$branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : '';
$branch_status = $_POST['branch_status'];

$query = "select * from emp_master where 1";

if ($role != "") {
	$query_role = mysqli_fetch_assoc(mysqlQuery("select * from role_master where role_name = '$role'"));
	$query .= " and role_id='$query_role[role_id]'";
}
if ($active_flag != "") {
	$query .= " and active_flag='$active_flag' ";
}
if ($location_id != "") {
	$query .= " and location_id='$location_id' ";
}
if ($branch_id != "") {
	$query .= " and branch_id='$branch_id' ";
}
if ($branch_status == 'yes' && $login_role != 'Admin' && $role != 'Admin') {
	$query .= " and branch_id = '$branch_admin_id'";
}
?>
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table class="table" id="tbl_emp_list" style="margin: 20px 0 !important;">
	<thead>
		<tr class="table-heading-row">
			<th>User_Id</th>
			<th>User_Name</th>
			<th>Location</th>
			<th>Branch</th>
			<th>Role</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		$sq_emp = mysqlQuery($query);
		while($row_emp = mysqli_fetch_assoc($sq_emp)){
			if($row_emp['emp_id']!=0){
				if($row_emp['id_proof_url']!=""){
					$url = $row_emp['id_proof_url'];
					$url = explode('uploads/', $url);
					$url = BASE_URL.'uploads/'.$url[1];
				}

				$sq_location = mysqli_fetch_assoc(mysqlQuery("select * from locations where location_id='$row_emp[location_id]'"));
				$sq_branch = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$row_emp[branch_id]'"));
				$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where emp_id='$row_emp[emp_id]'"));

				$sq_role = mysqli_fetch_assoc(mysqlQuery("select * from role_master where role_id='$row_emp[role_id]'"));
				$bg = ($row_emp['active_flag'] == "Inactive") ? "danger" : "";
					?>
				<tr class="<?= $bg ?>">
					<td><?= $row_emp['emp_id'] ?></td>
					<td><?= $row_emp['first_name'] . ' ' . $row_emp['last_name'] ?></td>
					<td><?= $sq_location['location_name'] ?></td>
					<td><?= $sq_branch['branch_name'] ?></td>
					<td><?= strtoupper($sq_role['role_name']) ?></td>
					<td>
						<button class="btn btn-info btn-sm" onclick="update_modal(<?= $row_emp['emp_id'] ?>);btnDisableEnable(this.id)" id="display_modal_user_edit_btn-<?= $row_emp['emp_id'] ?>" title="Update Details"><i class="fa fa-pencil-square-o"></i></button>
						<button class="btn btn-info btn-sm" onclick="display_modal(<?= $row_emp['emp_id'] ?>);btnDisableEnable(this.id)" id="display_modal_user_view_btn-<?= $row_emp['emp_id'] ?>" title="View Details"><i class="fa fa-eye"></i></button>
					</td>
                    </tr>
                    <?php
						}
					}
					?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$('#tbl_emp_list').dataTable({
    "pagingType": "full_numbers"
});
</script>
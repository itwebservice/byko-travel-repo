<?php
include "../../model/model.php";

$cust_type = $_POST['cust_type'];
$type = $_POST['type'];
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

if($cust_type == 'Corporate' || $cust_type == 'B2B') {
	?>
	<div class="panel panel-default panel-body app_panel_style mg_tp_30 feildset-panel">
		<legend>User Information</legend>
		<?php
		if($type == 'save'){
			?>
			<div class="row mg_bt_10"> <div class="col-md-12 text-right">
				<button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_cust_users')"><i class="fa fa-plus"></i></button>
				<button type="button" class="btn btn-pdf btn-sm" title="Delete Row" onclick="deleteRow('tbl_cust_users')"><i class="fa fa-trash"></i></button>
			</div> </div>
			<div class="row"> <div class="col-md-12"> <div class="table-responsive">
				<table id="tbl_cust_users" name="tbl_cust_users" class="table border_0 table-hover no-marg">
					<tr>
						<td><input id="chk_user1" type="checkbox"></td>
						<td><input maxlength="15" value="1" type="text" name="no" placeholder="Sr. No." class="form-control" disabled /></td>
						<td><input type="text" id="user_name" name="user_name" placeholder="*User Name" title="User Name"></td>
						<td><input type="number" id="mobile_no" name="mobile_no" placeholder="Mobile No" title="Mobile No"></td>
						<td><input type="text" id="email_id" name="email_id" placeholder="Email ID" title="Email ID"></td>
						<td><select name="active_flag" id="active_flag" title="Status" style="width:100%">
								<option value="Active">Active</option>
								<option value="Inactive">Inactive</option>
							</select>
						</td>
					</tr>
				</table>  
			</div> </div> </div>
		<?php }
		else{
			$sq_user_c = mysqli_num_rows(mysqlQuery("select * from customer_users where customer_id='$customer_id'"));
			?>
			<div class="row mg_bt_10"> <div class="col-md-12 text-right">
				<button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_cust_users1')"><i class="fa fa-plus"></i></button>
			</div> </div>
			<div class="row"> <div class="col-md-12"> <div class="table-responsive">
				<table id="tbl_cust_users1" name="tbl_cust_users1" class="table border_0 table-hover no-marg">
					<?php
					if($sq_user_c > 0){
						$count = 1;
						$sq_customer = mysqlQuery("select * from customer_users where customer_id='$customer_id'");
						while($row_customer = mysqli_fetch_assoc($sq_customer)){

							$bg = ($row_customer['status'] == 'Inactive') ? 'danger' : '';
							?>
							<tr class="<?= $bg ?>">
								<td><input id="chk_user-u<?= $count ?>" type="checkbox" checked></td>
								<td><input maxlength="15" value="1" type="text" name="no" placeholder="Sr. No." class="form-control" disabled /></td>
								<td><input type="text" id="user_name-u<?= $count ?>" name="user_name" placeholder="*User Name" title="User Name" value="<?= $row_customer['name'] ?>"></td>
								<td><input type="number" id="mobile_no-u<?= $count ?>" name="mobile_no" placeholder="Mobile No" title="Mobile No" value="<?= $row_customer['mobile_no'] ?>"></td>
								<td><input type="text" id="email_id-u<?= $count ?>" name="email_id" placeholder="Email ID" title="Email ID" value="<?= $row_customer['email_id'] ?>"></td>
								<td><select name="active_flag" id="active_flag-u<?= $count ?>" title="Status" style="width:100%">
										<option value="<?= $row_customer['status'] ?>"><?= $row_customer['status'] ?></option>
										<?php if($row_customer['status'] != 'Active') { ?> <option value="Active">Active</option> <?php } ?>
										<?php if($row_customer['status'] != 'Inactive') { ?> <option value="Inactive">Inactive</option> <?php } ?>
									</select>
								</td>
								<td><input type="hidden" id="entry_id-u<?= $count ?>" name="entry_id" value="<?= $row_customer['user_id'] ?>"></td>
							</tr>
						<?php $count++; }
					}else{
						?>
						<tr>
							<td><input id="chk_user1" type="checkbox"></td>
							<td><input maxlength="15" value="1" type="text" name="no" placeholder="Sr. No." class="form-control" disabled /></td>
							<td><input type="text" id="user_name" name="user_name" placeholder="*User Name" title="User Name"></td>
							<td><input type="number" id="mobile_no" name="mobile_no" placeholder="Mobile No" title="Mobile No"></td>
							<td><input type="text" id="email_id" name="email_id" placeholder="Email ID" title="Email ID"></td>
							<td><select name="active_flag" id="active_flag" title="Status" style="width:100%">
									<option value="Active">Active</option>
									<option value="Inactive">Inactive</option>
								</select>
							</td>
						</tr>
					<?php } ?>
				</table>  
			</div> </div> </div>
			<?php
		}
}
else
{

}
?>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
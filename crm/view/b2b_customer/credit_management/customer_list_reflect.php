<?php
include "../../../model/model.php";
$branch_admin_id = $_SESSION['branch_admin_id'];
$approve_status = $_POST['approve_status'];
$branch_status = $_POST['branch_status'];

$query = "select * from b2b_creditlimit_master where 1 and credit_amount!=0 ";
if($approve_status!="" && $approve_status!="Pending"){
	$query .=" and approval_status='$approve_status' ";
}
if($approve_status=="Pending"){
	$query .=" and approval_status='' ";
}
$query .=" order by entry_id desc";
?>
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table class="table table-hover" id="tbl_customer_list" style="margin: 20px 0 !important;">
	<thead>
		<tr class="table-heading-row">
			<th>S_No.</th>
			<th>Entry_Date</th>
			<th>Company_Name</th>
			<th>Contact_Person</th>
			<th>Credit_Amount</th>
			<th>Payment_Days</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$count = 0;
		$sq_customer = mysqlQuery($query);
		while($row_customer = mysqli_fetch_assoc($sq_customer)){

			$sq_reg = mysqli_fetch_assoc(mysqlQuery("select * from b2b_registration where register_id='$row_customer[register_id]'"));
			if($row_customer['approval_status']=="Rejected"){ $bg = "danger"; $icon = 'fa-times';}
			else if($row_customer['approval_status']=="Approved"){ $bg = "success"; $icon = 'fa-check';} 
			else{ $bg = '';  $icon = 'fa-check-square-o'; }

			$color = ($row_customer['approval_status']=="Rejected") ? 'btn-danger' : 'btn-info';
			?>
			<tr class="<?= $bg ?>">
				<td><?= ++$count ?></td>
				<td><?= get_date_user($row_customer['created_at']) ?></td>
				<td><?= $sq_reg['company_name']?></td>
				<td><?= $sq_reg['cp_first_name'].' '.$sq_reg['cp_last_name']  ?></td>
				<td><?= $row_customer['credit_amount'] ?></td>
				<td><?= $row_customer['payment_days'].' Days' ?></td>
				<td><a class="btn <?= $color ?> btn-sm" href="javascript:void(0)" title="" id="mark_btn-10" onclick="customer_update_modal(<?= $row_customer['entry_id'] ?>,<?= $row_customer['register_id'] ?>)" data-original-title="Update status"><i class="fa fa-thumb-tack"></i></a>
				</td>
			</tr>
			<?php } ?>
	</tbody>
</table>	
</div></div></div>
<div id="div_customer_update_modals"></div>
<script>
$('#tbl_customer_list').dataTable({
		"pagingType": "full_numbers"
	});
function customer_update_modal(entry_id,register_id){

    $('#updatec-'+entry_id).prop('disabled',true);
    $('#updatec-'+entry_id).button('loading');
	$.post('credit_management/customer_update_modal.php', { entry_id : entry_id ,register_id:register_id}, function(data){
		$('#div_customer_update_modals').html(data);
		$('#updatec-'+entry_id).prop('disabled',false);
		$('#updatec-'+entry_id).button('reset');
	})
}
</script>
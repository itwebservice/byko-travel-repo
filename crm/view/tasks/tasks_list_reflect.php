<?php
include "../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$login_id = $_SESSION['login_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];

$from_date_filter = $_POST['from_date_filter'];
$to_date_filter = $_POST['to_date_filter'];
$emp_id_filter = isset($_POST['emp_id_filter']) ? $_POST['emp_id_filter'] : '';
$branch_status = $_POST['branch_status'];

$query = "select * from tasks_master where 1 ";
if($emp_id_filter!=""){
	$query .= " and emp_id='$emp_id_filter'";
}
if($from_date_filter!="" && $to_date_filter!=""){
	$from_date_filter = date('Y-m-d', strtotime($from_date_filter));
	$to_date_filter = date('Y-m-d', strtotime($to_date_filter));

	$query .= " and date(due_date) between '$from_date_filter' and '$to_date_filter' ";
}
include "../../model/app_settings/branchwise_filteration.php";
$query .=" order by task_id desc";
$sq_tasks = mysqlQuery($query);
while($row_tasks = mysqli_fetch_assoc($sq_tasks)){

	$cur_time = date('Y-m-d H:i');
	$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$row_tasks[emp_id]'"));
	$emp_name = $sq_emp['first_name'].' '.$sq_emp['last_name'];
	$class_name = $row_tasks['task_status'];
	$due_date =  strtotime($row_tasks['due_date']);
	$status_date = strtotime($row_tasks['status_date']); 
	$hour = round(($due_date - $status_date) / 3600);

	if(($hour > 5) && $row_tasks['task_status']=='Completed'){
		$performance = "Excellent";
	}
	if(($hour==0) && $row_tasks['task_status']=='Completed'){
		$performance = 'Good';
	}
	if(($hour < 0 && $hour > '-10') && $row_tasks['task_status']=='Completed'){
		$performance = 'Poor';
	}
	if(($hour < 0 && $hour < '-10') && $row_tasks['task_status']=='Completed'){
		$performance = 'Very Poor';
	}

	if(strtotime($row_tasks['due_date'])<strtotime($cur_time) && $class_name=="Created"){
		$class_name = "created";
	}
	if($class_name=="Completed"){
		$class_name = 'completed';
	}
	if($class_name=="Cancelled"){
		$class_name = 'danger';
	}
	if($class_name=="Incomplete"){
		$class_name = 'warning';
	}
	if($class_name=="Disabled"){
		$class_name = 'hidden';
	}
	?>
	<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >
	<div class="tasks_item main_block mg_tp_20 <?= $class_name ?>">
		<div class="row">
			<div class="col-sm-9">
				<div class="task_content main_block">
					<div class="content_head main_block">
						<?= $row_tasks['task_name'] ?>
					</div>
					<div class="content_footer main_block">
						<ul>
							<?php 
							if($role=='Admin' || $role=='Branch Admin'){ ?><li>Assigned To : <?= $emp_name ?></li> <?php } ?>
							<li>Due : <?= date('d-m-Y H:i', strtotime($row_tasks['due_date'])) ?></li>
							<li>Status : <?= $row_tasks['task_status'] ?> </li>
							<?php if($class_name=="Completed"){ ?>
								<li>Performance : <?= $performance ?> </li>
							<?php }  if($class_name=="Incomplete"){ ?>
								<li>Reason : <?= $row_tasks['extra_note'] ?> </li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<?php if($row_tasks['task_status']=='Created'||$row_tasks['task_status']=='Incomplete') : ?>
					<a href="javascript:void(0)" title="Mark Status" id="mark_btn-<?= $row_tasks['task_id'] ?>" onclick="task_status_update(<?= $row_tasks['task_id'] ?>)"><i class="fa fa-thumb-tack"></i></a>&nbsp;&nbsp;
				<?php endif; 
				?>
					<a href="javascript:void(0)" title="Update Details" id="update_btn-<?= $row_tasks['task_id'] ?>" onclick="task_update_modal(<?= $row_tasks['task_id'] ?>)"><i class="fa fa-pencil-square-o"></i></a>&nbsp;&nbsp;
				<?php  if($row_tasks['task_status']!='Created') : ?>
					<a href="javascript:void(0)" title="View Details" id="view_btn-<?= $row_tasks['task_id'] ?>" onclick="task_extra_note_modal(<?= $row_tasks['task_id'] ?>)"><i class="fa fa-eye"></i></a>
				<?php endif; if($role=='Admin' || $role=='Branch Admin'){ ?>
					<a href="javascript:void(0)"  title="Delete" id="delete_btn-<?= $row_tasks['task_id'] ?>" onclick="task_status_disable(<?= $row_tasks['task_id'] ?>)"><i class="fa fa-trash icon-danger-r"></i></a>&nbsp;&nbsp;
				<?php } ?>
			</div>
		</div>
	</div>
	<?php } ?>
<script>
function task_update_modal(task_id){
    $('#update_btn-'+task_id).button('loading');
    $('#update_btn-'+task_id).prop('disabled',true);
	var branch_status = $('#branch_status').val();
	$.post('task_update_modal.php', { task_id : task_id , branch_status : branch_status}, function(data){
		$('#div_task_update_modal').html(data);
		$('#update_btn-'+task_id).button('reset');
		$('#update_btn-'+task_id).prop('disabled',false);
	});
}
function task_status_update(task_id){
    $('#mark_btn-'+task_id).button('loading');
    $('#mark_btn-'+task_id).prop('disabled',true);
	$.post('task_status_modal.php', { task_id : task_id }, function(data){
		$('#div_task_status_modal').html(data);
		$('#mark_btn-'+task_id).button('reset');
		$('#mark_btn-'+task_id).prop('disabled',false);
	});
}
function task_extra_note_modal(task_id){
    $('#view_btn-'+task_id).button('loading');
    $('#view_btn-'+task_id).prop('disabled',true);
	$.post('task_extra_note_modal.php', { task_id : task_id }, function(data){
		$('#div_task_extra_note_modal').html(data);
		$('#view_btn-'+task_id).button('reset');
		$('#view_btn-'+task_id).prop('disabled',false);
	});
}
function task_status_disable(task_id){
    $('#delete_btn-'+task_id).button('loading');
    $('#delete_btn-'+task_id).prop('disabled',true);
	var base_url = $('#base_url').val();
	$('#vi_confirm_box').vi_confirm_box({
		callback: function(data1){
			if(data1=="yes"){
				$.post(base_url+'controller/tasks/task_status_disable.php', { task_id : task_id }, function(data){
					msg_alert(data);
					tasks_list_reflect();
					$('#delete_btn-'+task_id).button('reset');
					$('#delete_btn-'+task_id).prop('disabled',false);
				})
			}else{
				$('#delete_btn-'+task_id).button('reset');
				$('#delete_btn-'+task_id).prop('disabled',false);
			}
		}
	});
}
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
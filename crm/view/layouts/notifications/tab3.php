<div class="notification_scroller notification_scroller_task">
	<ul class="no-pad">
		<?php
			$q = "select branch_status from branch_assign where link='leave_magt/index.php'";
			$sq_count = mysqli_num_rows(mysqlQuery($q));
			$sq = mysqli_fetch_assoc(mysqlQuery($q));
			$branch_status1 = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
			if($role=='Admin' || $role=='Branch Admin' || $role=="Hr"){
				if($role=="Hr"){
					$query = "select * from leave_request where 1 and status='' and emp_id in(select emp_id from emp_master where branch_id='$branch_admin_id')";
					$query .= " order by request_id desc limit 5";
					$sq_leave = mysqlQuery($query);
				}
				else{
				if($role=="Branch Admin"){
					$query = "select * from leave_request where 1 and status=''";
					if($branch_status1=='yes'){
						$query .=" and emp_id in(select emp_id from emp_master where branch_id='$branch_admin_id')";
					}
					$query .= " UNION ALL (select * from leave_request where 1 and status!='' and emp_id='$emp_id' ";
					if($branch_status1=='yes'){
						$query .=" and emp_id in(select emp_id from emp_master where branch_id='$branch_admin_id')";
					}
					$query .= ") order by request_id desc limit 5";
					$sq_leave = mysqlQuery($query);
				}
				else{
					$sq_leave = mysqlQuery("select * from leave_request where 1 and status='' order by request_id desc limit 5");
				}
			}
			while($row_leave = mysqli_fetch_assoc($sq_leave)){
				$sq_emp = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from emp_master where emp_id='$row_leave[emp_id]'"));
				$d_date = '('.get_date_user($row_leave['from_date']).' To '.get_date_user($row_leave['to_date']).')';
				$l_status = ($row_leave['status'] != '') ? '('.$row_leave['status'].')' : '';
			?>
			<li class="single_notification">
				<h5 class="single_notification_text no-marg"><?= $row_leave['type_of_leave'].$d_date ?></h5>
				<p class="single_notification_date_time no-marg"><?= $l_status.' '.$sq_emp['first_name'].' '.$sq_emp['last_name'] ?></p>
			</li>
			<?php }
		}
		else{
			$sq_leave = mysqlQuery("select * from leave_request where 1 and status!='' and emp_id='$emp_id' order by request_id desc limit 5");
			while($row_leave = mysqli_fetch_assoc($sq_leave)){
			$sq_emp = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from emp_master where emp_id='$row_leave[emp_id]'"));
			$d_date = '('.get_date_user($row_leave['from_date']).' To '.get_date_user($row_leave['to_date']).')';
			$l_status = ($row_leave['status'] != '') ? '('.$row_leave['status'].')' : $d_date;
			?>
			<li class="single_notification">
				<h5 class="single_notification_text no-marg"><?= $row_leave['type_of_leave'].$d_date ?></h5>
				<p class="single_notification_date_time no-marg"><?= $row_leave['status']?></p>
			<?php if($role_id==6){ ?> <p class="single_notification_date_time no-marg"><?= $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></p><?php } ?>
			</li>
			<?php }
		}?>
	</ul>

	<div class="all_notification">
		<?php $path="view/leave_magt/index.php"; ?>
		<a href= <?= BASE_URL.$path ?> target="_blank">View All Notifications</a>
	</div>
</div>
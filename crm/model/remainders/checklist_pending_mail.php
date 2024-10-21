<?php
include_once('../model.php');
$today = date('Y-m-d');
$start_date = date('Y-m-d');

$sq_type = mysqli_fetch_assoc(mysqlQuery("select days from cms_master where id='68'"));
$days = $sq_type['days'];
$tommorow = date('Y-m-d', strtotime('+ '.$days.' days', strtotime($start_date)));
$group_tour_arr = array();
$group_tour_pending_arr = array();

$package_tour_arr = array();
$package_tour_pending_arr = array();

$group_tour_contnet = '';
$package_tour_contnet = '';

$sq_tour_count = mysqli_num_rows(mysqlQuery("SELECT * from tour_groups where from_date = '$tommorow' and status!='Cancel'"));
if($sq_tour_count > 0){

	$sq_tour_groups = mysqlQuery("SELECT * from tour_groups where from_date = '$tommorow' and status!='Cancel'");
	while($row_tour_groups = mysqli_fetch_assoc($sq_tour_groups)){

		if($tommorow==$row_tour_groups['from_date']){

			$sq_tour = mysqli_fetch_assoc(mysqlQuery("select tour_name from tour_master where tour_id='$row_tour_groups[tour_id]'"));
			$tour_name = $sq_tour['tour_name'].'('.date('d-m-Y', strtotime($row_tour_groups['from_date'])).' to '.date('d-m-Y', strtotime($row_tour_groups['to_date'])).')';

			array_push($group_tour_arr, $tour_name);

            $query = "select * from tourwise_traveler_details where tour_id='$row_tour_groups[tour_id]' and tour_group_id='$row_tour_groups[group_id]' and tour_group_status!='Cancel'";
			$sq_tour = mysqlQuery($query);
			while($row_tour = mysqli_fetch_assoc($sq_tour)){
				$entity_list = "";
				$sq_checklist_count = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where tour_type='Group Tour' and booking_id='$row_tour[id]'"));
				if($sq_checklist_count!=0){
					$sq_checklist = mysqlQuery("select * from checklist_package_tour where tour_type='Group Tour' and booking_id='$row_tour[id]'");
					while($row_checklist = mysqli_fetch_assoc($sq_checklist)){
						$sq_to_do = mysqli_fetch_assoc(mysqlQuery("select * from to_do_entries where id='$row_checklist[entity_id]'"));
						$entity_list .= addslashes($sq_to_do['entity_name']).", ";		
					}
				}
				$entity_list = trim($entity_list, ', ');
				array_push($group_tour_pending_arr, $entity_list);
			}
		}
	}
}
var_dump($group_tour_pending_arr);
$sq_booking_count = mysqli_num_rows(mysqlQuery("SELECT * from package_tour_booking_master where tour_from_date = '$tommorow'"));
if($sq_booking_count > 0){

	$sq_booking = mysqlQuery("select * from package_tour_booking_master where tour_from_date='$tommorow' and delete_status='0'");
	while($row_booking = mysqli_fetch_assoc($sq_booking)){

		if($tommorow==$row_booking['tour_from_date']){
			$tour_name = $row_booking['tour_name'].'('.date('d-m-Y', strtotime($row_booking['tour_from_date'])).' to '.date('d-m-Y', strtotime($row_booking['tour_to_date'])).')';
			array_push($package_tour_arr, $tour_name);

			$sq_entities = mysqlQuery("select * from checklist_entities where entity_for!='Group Tour'");
			while($row_entity = mysqli_fetch_assoc($sq_entities)){

				$entity_list = "";
				$sq_checklist_count = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where tour_type='Package Tour' and booking_id='$row_booking[booking_id]'"));
				if($sq_checklist_count!=0){
					$sq_checklist = mysqlQuery("select * from checklist_package_tour where tour_type='Package Tour' and booking_id='$row_booking[booking_id]'");
					while($row_checklist = mysqli_fetch_assoc($sq_checklist)){
						$sq_to_do = mysqli_fetch_assoc(mysqlQuery("select * from to_do_entries where id='$row_checklist[entity_id]'"));
						$entity_list .= addslashes($sq_to_do['entity_name']).", ";
					}
				}
			}
			$entity_list = trim($entity_list, ', ');
			array_push($package_tour_pending_arr, $entity_list);
		}
	}
}

if(sizeof($group_tour_pending_arr) >0){
	$group_tour_contnet = '<p style="line-height: 24px;">
							<span style="color:#3434f5; font-weight : 600;">Group Tour:</span>
						</p>';
}

for($i=0; $i<sizeof($group_tour_pending_arr); $i++){
	if($group_tour_pending_arr[$i]!=""){
		$group_tour_contnet .=  '<p style="line-height: 24px;">
									&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#3434f5; font-weight : 600;">'.$group_tour_arr[$i].'<br></span>
									&nbsp;&nbsp;&nbsp;&nbsp;Pending Points:&nbsp;&nbsp;'.$group_tour_pending_arr[$i].' <br>
								</p>';
	}
}

if(sizeof($package_tour_pending_arr) >0){
	$package_tour_contnet = '<p style="line-height: 24px;">
							<span style="color:#3434f5; font-weight : 600;">Package Tour:</span>
						</p>';
}

for($i=0; $i<sizeof($package_tour_pending_arr); $i++){
	if($package_tour_pending_arr[$i]!=""){
		$package_tour_contnet .='<p style="line-height: 24px;">
									&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#3434f5; font-weight : 600;">'.$package_tour_arr[$i].'<br></span>
									&nbsp;&nbsp;&nbsp;&nbsp;Pending Points:&nbsp;&nbsp;'.$package_tour_pending_arr[$i].' <br>
								</p>';
	}
}

$content = '
<tr>
	<td>
		<table style="width:100;">
			<tr>
				<td>
					'.$group_tour_contnet.' '.$package_tour_contnet.'							
				</td>
			</tr>
		</table>
	</td>
</tr>
';
global $model, $app_email_id;
$sq_count = mysqli_num_rows(mysqlQuery("SELECT * from  remainder_status where remainder_name = 'checklist_remainder' and date='$today' and status='Done'"));
if($sq_count==0){
	if($group_tour_contnet != '' || $package_tour_contnet != ''){
		
		$model->app_email_send('68',"Admin",$app_email_id, $content,'Tour Checklist reminder');	
		$row=mysqlQuery("SELECT max(id) as max from remainder_status");
		$value=mysqli_fetch_assoc($row);
		$max=$value['max']+1;
		$sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','checklist_remainder','$today','Done')");
	}
}
?>
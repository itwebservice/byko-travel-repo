<?php
include "../../../model/model.php";

$array_s = array();
$temp_arr = array();

$query = "select * from ferry_master where 1 order by entry_id desc";
$sq_query = mysqlQuery($query);

$count = 0;
while($row_req = mysqli_fetch_assoc($sq_query)){

	$sq_tariff_count = mysqli_num_rows(mysqlQuery("select * from ferry_tariff where entry_id='$row_req[entry_id]'"));
	if($sq_tariff_count > 0){

		$temp_arr = array( "data" => array(
			(int)(++$count),
			$row_req['ferry_name'].'('.$row_req['ferry_type'].')',
			'<button style="display:inline-block" data-toggle=tooltip" id="updatet_btn-'.$row_req['entry_id'] .'" class="btn btn-info btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true" onclick="tredit_modal(\''.$row_req['entry_id'].'\')" data-toggle="tooltip" title="Update Details"></i></button>
			<button style="display:inline-block" id="viewt_btn-'.$row_req['entry_id'] .'" class="btn btn-info btn-sm" onclick="view_modal(\''.$row_req['entry_id'].'\')" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'), "bg" => '');
		array_push($array_s,$temp_arr);
	}
}
echo json_encode($array_s);	
?>	
<?php
include_once("../../../../model/model.php");
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$financial_year_id = $_POST['financial_year_id'];

$array_s = array();
$temp_arr = array();
$footer_data = array();
$query = "select * from journal_entry_master where financial_year_id='$financial_year_id' and delete_status='0' ";
if($from_date != '' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and entry_date between '$from_date' and '$to_date' ";
}
$query .= " order by entry_id desc";

$count = 0;
$total_dr = 0;
$total_cr = 0;

$sq_journal = mysqlQuery($query);
while($row_journal = mysqli_fetch_assoc($sq_journal)){

	$date = $row_journal['entry_date'];
	$yr = explode("-", $date);
	$year = $yr[0];
	$sq_journal_entry = mysqli_fetch_assoc(mysqlQuery("select * from journal_entry_accounts where entry_id='$row_journal[entry_id]' limit 1"));	
	$bg = " ";
	$sq_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$sq_journal_entry[ledger_id]'"));	
	$sq_journal_debit = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as amount from journal_entry_accounts where type = 'Debit' and entry_id='$row_journal[entry_id]'"));
	$total_cr += $sq_journal_debit['amount'];	
	$temp_arr = array (
		(int)(++$count),
		get_jv_entry_id($row_journal['entry_id'],$year),
		get_date_user($row_journal['entry_date']),
		$sq_ledger['ledger_name'],
		$sq_journal_entry['type'],
		$row_journal['narration'],
		number_format($sq_journal_debit['amount'],2),
		'<button class="btn btn-info btn-sm" data-toggle="tooltip" onclick="update_modal('.$row_journal['entry_id'] .')" title="Update Details" id="editj-'.$row_journal['entry_id'].'"><i class="fa fa-pencil-square-o"></i></button><button data-toggle="tooltip" class="btn btn-info btn-sm" onclick="entry_display_modal('.$row_journal['entry_id'].')" title="View Details" id="view-'.$row_journal['entry_id'].'"><i class="fa fa-eye"></i></button>
		<button class="'.$delete_flag.' btn btn-danger btn-sm" onclick="delete_entry('.$row_journal['entry_id'].')" title="Delete Entry"><i class="fa fa-trash"></i></button>'
	);
	array_push($array_s,$temp_arr); 
}
$footer_data = array("footer_data" => array(
	'total_footers' => 2,
	'foot0' => "Total Debit : ".number_format($total_cr,2),
	'col0' => 7,
	'class0' => 'text-right',
	'foot1' => "",
	'col1' => 1,
	)
);
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>
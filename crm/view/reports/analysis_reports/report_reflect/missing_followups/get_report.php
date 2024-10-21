<?php
include "../../../../../model/model.php";
$branch_admin_id = $_SESSION['branch_admin_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$emp_id = $_SESSION['emp_id'];
$q = "select * from branch_assign where link='attractions_offers_enquiry/enquiry/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';

$today = date('Y-m-d');
$array_s = array();
$query = "SELECT * FROM enquiry_master where status!='Disabled'";
if($branch_status=='yes' && $role!='Admin'){
	$query .= " and branch_admin_id = '$branch_admin_id'";
}
if($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
	$query .=" and assigned_emp_id='$emp_id' ";
}
$sq_enq1 = mysqlQuery($query);
$count = 0;
while ($row_enq = mysqli_fetch_assoc($sq_enq1)) {

    $enquiry_id = $row_enq['enquiry_id'];
    $invalid_count = mysqli_num_rows(mysqlQuery("SELECT entry_id FROM enquiry_master_entries where enquiry_id='$enquiry_id' and followup_status in ('Converted','Dropped') order by entry_id desc"));
    if($invalid_count == 0){

        $sq_max_count = mysqli_fetch_assoc(mysqlQuery("SELECT max(entry_id) as max_entry_id FROM enquiry_master_entries where enquiry_id='$enquiry_id'"));
        $entry_id = $sq_max_count['max_entry_id'];
        $sq_enq_count = mysqli_num_rows(mysqlQuery("SELECT followup_date FROM enquiry_master_entries where enquiry_id='$enquiry_id' and followup_status in('Active','In-Followup') and DATE(followup_date) < '$today' and entry_id='$entry_id'"));
        if($sq_enq_count > 0){

            $sq_enq = mysqli_fetch_assoc(mysqlQuery("SELECT followup_date FROM enquiry_master_entries where enquiry_id='$enquiry_id' and followup_status in('Active','In-Followup') and DATE(followup_date) < '$today' and entry_id='$entry_id'"));
            $date = $row_enq['enquiry_date'];
            $yr = explode("-", $date);
            $year = $yr[0];

            $sq_emp = mysqli_fetch_assoc(mysqlQuery("SELECT first_name,last_name FROM emp_master where emp_id='$row_enq[assigned_emp_id]'"));
            $emp_name = isset($sq_emp['first_name']) ? $sq_emp['first_name'].' '.$sq_emp['last_name'] : 'NA';
            $tour_name = '';
            $tourdetail  = json_decode($row_enq['enquiry_content'], true);
            if(isset($tourdetail)){
                foreach ($tourdetail as $dc) {
                    if (isset($dc['name']) && $dc['name'] == 'tour_name') {
                        $tourname = $dc['value'];
                    }
                }
            }
            $cust_user_name = '';
            if($row_enq['user_id'] != 0){ 
                $row_user = mysqli_fetch_assoc(mysqlQuery("Select name from customer_users where user_id =" . $row_enq['user_id']));
                $cust_user_name = ' ('.$row_user['name'].')';
            }

            $temparr = array("data" => array(
                (int)(++$count),
                get_enquiry_id($enquiry_id,$year),
                get_date_user($row_enq['enquiry_date']),
                $row_enq['name'].$cust_user_name,
                $row_enq['enquiry_type'] == 'Package Booking' || $row_enq['enquiry_type'] == 'Group Booking' ? $row_enq['enquiry_type'].' ('.$tourname.')' : $row_enq['enquiry_type'],
                get_datetime_user($sq_enq['followup_date']),
                $emp_name,
                ($enquiry_id != '') ? '<button class="btn btn-info btn-sm" id="view_followup_btn-'. $enquiry_id .'" onclick="view_followup_modal('.$enquiry_id .')" data-toggle="tooltip" title="View Followup History"><i class="fa fa-eye" aria-hidden="true"></i></button>' : 'NA',
            ), "bg" => '');
            array_push($array_s, $temparr);
        }
    }
}
echo json_encode($array_s);
?>

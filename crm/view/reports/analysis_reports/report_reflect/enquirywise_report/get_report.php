<?php
include "../../../../../model/model.php";
//require_once "../../../../../model/customer_master.php";


// $customer = new customer_master();
// $data = $customer->whereCondition('customer_id','=','7')
// ->whereCondition('first_name','=','Balaji')
// ->joinTableHasMany('package_tour_booking_master','customer_id','customer_id')
// ->joinTableHasOne('destination_master','dest_id','dest_id','package_tour_booking_master')
// ->joinTableHasOne('state_master','state_id','id')
// ->firstData(); 


// echo "<pre>";
// print_r($data);
// echo "</pre>";

$fromdate = !empty($_POST['fromdate']) ? get_date_db($_POST['fromdate']) : null;
$todate = !empty($_POST['todate']) ? get_date_db($_POST['todate']) : null;
$array_s = array();

if (empty($fromdate) && empty($todate)) {
    $_SESSION['dateqry'] = "";
} else {
    $_SESSION['dateqry'] = "and enquiry_master.enquiry_date between '" . $fromdate . "' and '" . $todate . "'";
}

$total_budget = 0;
function get_enq_etr_budget($id='', $type='display')
{
    global $total_budget;

    if ($type == 'display') {
        $query1 = "SELECT * FROM enquiry_master Inner join emp_master on emp_master.emp_id = enquiry_master.assigned_emp_id INNER JOIN enquiry_master_entries ON enquiry_master.enquiry_id = enquiry_master_entries.enquiry_id  where enquiry_master.enquiry_id ='$id' and enquiry_master_entries.status != 'False'" . $_SESSION['dateqry'];
        $res = mysqlQuery($query1);
        $budget = 0;
        while ($db = mysqli_fetch_assoc($res)) {
            $decodeec = json_decode($db['enquiry_content'], true);

            if($db['enquiry_type'] == 'Flight Ticket')
            {
                foreach($decodeec as $detail)
                {
                    $budget += floatval($detail['budget']);
                }
            }else{

                foreach ($decodeec as $dc) {
        
                    if (isset($dc['name']) && $dc['name'] == 'budget') {
                        if($db['enquiry_type'] == 'Flight Ticket') { echo $dc['value']; }
                        $budget += floatval($dc['value']);
                    }
                }
            }
        }
        $total_budget += $budget;

        return ' (' . number_format(floatval($budget),2) . ')';
    } elseif ($type == 'total') {
        return '(' . number_format(floatval($total_budget),2) . ')';
    }
}
function get_followupStatus($id)
{
    $query =  "SELECT (followup_status) FROM  enquiry_master_entries where enquiry_id='$id' order by created_at DESC LIMIT 1";
    $res = mysqli_fetch_assoc(mysqlQuery($query))['followup_status'];
    return $res;
}
if (empty($fromdate) && empty($todate)) {
    $query =  "SELECT * FROM enquiry_master Inner join emp_master on emp_master.emp_id = enquiry_master.assigned_emp_id left JOIN enquiry_master_entries ON enquiry_master.enquiry_id = enquiry_master_entries.enquiry_id GROUP BY enquiry_master.enquiry_id";
} else {
    $query =  "SELECT * FROM enquiry_master Inner join emp_master on emp_master.emp_id = enquiry_master.assigned_emp_id left JOIN enquiry_master_entries ON enquiry_master.enquiry_id = enquiry_master_entries.enquiry_id  where enquiry_master.enquiry_date between '" . $fromdate . "' and '" . $todate . "' and enquiry_master_entries.status != 'False'";
}


$type = 'display';
$result = mysqlQuery($query);
$count = 1;
while ($data = mysqli_fetch_assoc($result)) {
    if ($data['enquiry_type'] == 'Flight Ticket') {
        $tourdetail  = json_decode($data['enquiry_content'], true);
        foreach ($tourdetail as $detail) {
            $budget += floatval($detail['budget']);
        }
    } else {
        $tourdetail  = json_decode($data['enquiry_content'], true);
        foreach ($tourdetail as $dc) {
            if ($dc['name'] == 'budget') {
                $budget = (int)$dc['value'];
            }
            if ($dc['name'] == 'tour_name') {

                $tourname = $dc['value'];
            }
        }
    }
    $bg = '';
    if (get_followupStatus($data['enquiry_id']) == 'Dropped') {
        $bg = 'danger';
    }
    if (get_followupStatus($data['enquiry_id']) == 'Converted') {
        $bg = 'success';
    }
    $cust_user_name = '';
    if($data['user_id'] != 0){ 
        $row_user = mysqli_fetch_assoc(mysqlQuery("Select name from customer_users where user_id =" . $data['user_id']));
        $cust_user_name = ' ('.$row_user['name'].')';
    }




    $temparr = array("data" => array(
        (int) ($count++),
        get_date_user($data['enquiry_date']),
        $data['name'].$cust_user_name,
        $data['enquiry_type'],
        $data['enquiry_type'] == 'Package Booking' || $data['enquiry_type'] == 'Group Booking' ? $tourname : 'NA',
        $data['enquiry_specification'],
        $data['followup_stage'],
        $data['first_name'] . $data['last_name'],
        get_followupStatus($data['enquiry_id']),
        get_enq_etr_budget($data['enquiry_id'], $type),

    ), "bg" => $bg);
    array_push($array_s, $temparr);
}
$footer_data = array(
    "footer_data" => array(
        'total_footers' => 10,

        'foot0' => "",
        'class0' => "text-left",

        'foot1' => "",
        'class1' => "text-left",

        'foot2' => "",
        'class2' => "text-left",

        'foot3' => "",
        'class3' => "text-left",

        'foot4' => "",
        'class4' => "text-left",

        'foot5' => "",
        'class5' => "text-left",

        'foot6' => "",
        'class6' => "text-left",

        'foot7' => "",
        'class7' => "text-left",

        'foot8' => "Total :",
        'class8' => "text-left info",

        'foot9' => get_enq_etr_budget('', 'total'),
        'class9' => "text-left success",

    )
);
array_push($array_s, $footer_data);
echo json_encode($array_s);
?>

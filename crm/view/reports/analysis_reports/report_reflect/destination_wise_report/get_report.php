<?php
include "../../../../../model/model.php";
$fromdate = !empty($_POST['fromdate']) ? get_date_db($_POST['fromdate']) : null;
$todate = !empty($_POST['todate']) ? get_date_db($_POST['todate']) : null;
$array_s = array();

if (empty($fromdate) && empty($todate)) {
    $_SESSION['tourwise'] = "";
    $_SESSION['package'] = "";
} else {
    $_SESSION['tourwise'] = "and tourwise_traveler_details.form_date between '" . $fromdate . "' and '" . $todate . "'";
    $_SESSION['package'] = "and package_tour_booking_master.booking_date between '" . $fromdate . "' and '" . $todate . "'";
}

function total_bookings_package($dest_id)
{
    $amt = 0;
    $qry = "select *,COUNT(*) as `booking` from package_tour_booking_master inner join destination_master on package_tour_booking_master.dest_id=destination_master.dest_id where destination_master.dest_id=" . $dest_id . " and package_tour_booking_master.delete_status=0 " . $_SESSION['package'];
    $res = mysqlQuery($qry);
    while ($db = mysqli_fetch_array($res)) {
        $pass_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$db[booking_id]'"));
        $cancle_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$db[booking_id]' and status='Cancel'"));
        if ($pass_count != $cancle_count) {
            $amt += $db['booking'];
        }
    }

    return empty($amt) ? 0 : $amt;
}
function total_bookings_tour($dest_id)
{
    $amt = 0;
    $qry = "select * from tourwise_traveler_details inner join tour_master on tourwise_traveler_details.tour_id=tour_master.tour_id inner join destination_master on tour_master.dest_id=destination_master.dest_id where destination_master.dest_id=" . $dest_id . " and tourwise_traveler_details.delete_status=0 and tourwise_traveler_details.tour_group_status='' " . $_SESSION['tourwise'];
    $res = mysqlQuery($qry);
    while ($db = mysqli_fetch_array($res)) {
        $pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$db[traveler_group_id]'"));
        $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$db[traveler_group_id]' and status='Cancel'"));
        if ($pass_count == $cancelpass_count) {
            continue;
        }
        if ($db['tour_group_status'] == "Cancel") {
            continue;
        }
        $amt++;
    }
    return empty($amt) ? 0 : $amt;
}
function total_selling_package($dest_id)
{
    $amt = 0;
    $qry = "select * from package_tour_booking_master inner join destination_master on package_tour_booking_master.dest_id=destination_master.dest_id where destination_master.dest_id=" . $dest_id . " and package_tour_booking_master.delete_status=0 " . $_SESSION['package'];
    $res = mysqlQuery($qry);
    while ($db = mysqli_fetch_array($res)) {
        $pass_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$db[booking_id]'"));
        $cancle_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$db[booking_id]' and status='Cancel'"));

        if ($pass_count != $cancle_count) {
            $amt += $db['net_total'];
        }
    }
    return empty($amt) ? 0 : $amt;
}
function total_selling_tour($dest_id)
{
    $amt = 0;
    $qry = "select *,net_total as `booking` from tourwise_traveler_details inner join tour_master on tourwise_traveler_details.tour_id=tour_master.tour_id inner join destination_master on tour_master.dest_id=destination_master.dest_id where destination_master.dest_id=" . $dest_id . " and tourwise_traveler_details.delete_status=0 and tourwise_traveler_details.tour_group_status='' " . $_SESSION['tourwise'];
    $res = mysqlQuery($qry);
    while ($db = mysqli_fetch_array($res)) {
        $added = 0;
        $pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$db[traveler_group_id]'"));
        $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$db[traveler_group_id]' and status='Cancel'"));
        if ($pass_count == $cancelpass_count) {
            continue;
        }
        if ($db['tour_group_status'] == "Cancel") {
            continue;
        }
        $amt += $db['booking'];
    }
    return empty($amt) ? 0 : $amt;
}



if (empty($fromdate) && empty($todate)) {
    $query =  "SELECT * FROM destination_master";
} else {
    $query =  "SELECT * FROM destination_master";
}

$type = 'display';
$result = mysqlQuery($query);
$count = 1;
while ($data = mysqli_fetch_assoc($result)) {
    $total_bookings =  total_bookings_package($data['dest_id']) + total_bookings_tour($data['dest_id']);
    $total_selling = total_selling_package($data['dest_id']) + total_selling_tour($data['dest_id']);

    if ($total_bookings > 0 || $total_selling > 0) {
        $temparr = array("data" => array(
            (int) ($count++),
            $data['dest_name'],
            $total_bookings,
            number_format(floatval($total_selling), 2),
            '<button class="btn btn-info btn-sm" onclick="view_desti_wise_modal(' . $data['dest_id'] . ')" data-toggle="tooltip" title="View Details" id="view_btn-' . $data['dest_id'] . '"><i class="fa fa-eye"></i></button>'
        ), "bg" => '');
        array_push($array_s, $temparr);
    }
}

$footer_data = array(
    "footer_data" => array()
);

array_push($array_s, $footer_data);
echo json_encode($array_s);

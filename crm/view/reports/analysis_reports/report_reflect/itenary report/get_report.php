<?php
include "../../../../../model/model.php";
$selectedDate = !empty($_POST['date']) ? get_date_db($_POST['date']) : null;
$array_s = array();
$i = 1;
function addDayInDate($date)
{
    $Date1 = $date;
    $date = new DateTime($Date1);
    $date->add(new DateInterval('P1D')); // P1D means a period of 1 day
    $Date2 = $date->format('Y-m-d');
    return $Date2;
}

function getTodaysItenaryByPackage($id, $selectedDate)
{
    $temp = array();
    $query =  "SELECT * FROM package_quotation_program inner join package_tour_booking_master on package_quotation_program.quotation_id=package_tour_booking_master.quotation_id where package_tour_booking_master.booking_id='$id'";
    
    $res = mysqlQuery($query);
    $first = 0;
    global $i;
    $usedId = 0;
    while ($data = mysqli_fetch_assoc($res)) {

        if (!empty($selectedDate)) {
            if ($first == 0) {
                $date = new DateTime($data['tour_from_date']);
            }

            if ($date >= new DateTime($data['tour_from_date'])  &&  $date <= new DateTime($data['tour_to_date'])) {
                if ($date->format('Y-m-d') == $selectedDate) {

                    if ($usedId != $data['booking_id']) {
                        $getPassenger = getPassengers($data['booking_id']);
                        $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$data[customer_id]'"));
                        if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                            $customer_name = $sq_customer['company_name'];
                        } else {
                            $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                        }
                        $date = $data['booking_date'];
                        $yr = explode("-", $date);
                        $year = $yr[0];
                        $bookedId =  get_package_booking_id($data['booking_id'], $year);
                        return  $temparr = array("data" => array(
                            (int) ($i++),
                            $bookedId,
                            $customer_name . ' [' . json_encode($getPassenger) . ']',
                            $data['attraction'],
                            substr($data['day_wise_program'], 0, 100),
                            $data['stay'],
                            $data['meal_plan']
                        ), "bg" => '');
                        $usedId = $data['booking_id'];
                    }
                }
                $date = addDayInDate($date->format('Y-m-d'));
                $date = new DateTime($date);
                $first = 1;
            }
        } else {
            if ($first == 0) {
                $date = new DateTime($data['tour_from_date']);
                // $date = $date->format('Y-m-d');
            }

            if ($date >= new DateTime($data['tour_from_date'])  &&  $date <= new DateTime($data['tour_to_date'])) {

                if ($date->format('Y-m-d') == date('Y-m-d')) {

                    if ($usedId != $data['booking_id']) {

                        $getPassenger = getPassengers($data['booking_id']);
                        $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$data[customer_id]'"));
                        if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                            $customer_name = $sq_customer['company_name'];
                        } else {
                            $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                        }
                        $date = $data['booking_date'];
                        $yr = explode("-", $date);
                        $year = $yr[0];
                        $bookedId =  get_package_booking_id($data['booking_id'], $year);
                        return  $temparr = array("data" => array(
                            (int) ($i++),
                            $bookedId,
                            $customer_name . ' [' . json_encode($getPassenger) . ']',
                            $data['attraction'],
                            $data['day_wise_program'],
                            $data['stay'],
                            $data['meal_plan']

                        ), "bg" => '');
                        $usedId = $data['booking_id'];
                    }
                }
                $date = addDayInDate($date->format('Y-m-d'));
                $date = new DateTime($date);
                $first = 1;
            }
        }
    }
}


function getTodaysItenary($id, $selectedDate)
{
    $temp = array();
    $query =  "SELECT * FROM package_tour_schedule_master inner join package_tour_booking_master on package_tour_schedule_master.booking_id=package_tour_booking_master.booking_id where package_tour_schedule_master.booking_id='$id'";
    $res = mysqlQuery($query);
    $first = 0;
    global $i;
    $usedId = 0;
    //date('Y-m-d', strtotime($Date. ' + 1 days'));
    while ($data = mysqli_fetch_assoc($res)) {

        if (!empty($selectedDate)) {
            if ($first == 0) {
                $date = new DateTime($data['tour_from_date']);
            }

            if ($date >= new DateTime($data['tour_from_date'])  &&  $date <= new DateTime($data['tour_to_date'])) {
                if ($date->format('Y-m-d') == $selectedDate) {

                    if ($usedId != $data['booking_id']) {
                        $getPassenger = getPassengers($data['booking_id']);
                        $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$data[customer_id]'"));
                        if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                            $customer_name = $sq_customer['company_name'];
                        } else {
                            $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                        }

                        $date = $data['booking_date'];
                        $yr = explode("-", $date);
                        $year = $yr[0];
                        $bookedId =  get_package_booking_id($data['booking_id'], $year);

                        return  $temparr = array("data" => array(
                            (int) ($i++),
                            $bookedId,
                            $customer_name . ' [' . json_encode($getPassenger) . ']',
                            $data['attraction'],
                            $data['day_wise_program'],
                            $data['stay'],
                            $data['meal_plan']
                        ), "bg" => '');
                        $usedId = $data['booking_id'];
                    }
                }
                $date = addDayInDate($date->format('Y-m-d'));
                $date = new DateTime($date);
                $first = 1;
            }
        } else {
            if ($first == 0) {
                $date = new DateTime($data['tour_from_date']);
                // $date = $date->format('Y-m-d');
            }

            if ($date >= new DateTime($data['tour_from_date'])  &&  $date <= new DateTime($data['tour_to_date'])) {
                if ($date->format('Y-m-d') == date('Y-m-d')) {

                    if ($usedId != $data['booking_id']) {
                        $getPassenger = getPassengers($data['booking_id']);
                        $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$data[customer_id]'"));
                        if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                            $customer_name = $sq_customer['company_name'];
                        } else {
                            $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                        }
                        $date = $data['booking_date'];
                        $yr = explode("-", $date);
                        $year = $yr[0];
                        $bookedId =  get_package_booking_id($data['booking_id'], $year);
                        return  $temparr = array("data" => array(
                            (int) ($i++),
                            $bookedId,
                            $customer_name . ' [' . json_encode($getPassenger) . ']',
                            $data['attraction'],
                            substr($data['day_wise_program'], 0, 100),
                            $data['stay'],
                            $data['meal_plan']

                        ), "bg" => '');
                        $usedId = $data['booking_id'];
                    }
                }
                $date = addDayInDate($date->format('Y-m-d'));
                $date = new DateTime($date);
                $first = 1;
            }
        }
    }
}

function getPassengers($bookingId)
{
    $names = array();
    $query = "select * from package_travelers_details inner join package_tour_booking_master on package_travelers_details.booking_id=package_tour_booking_master.booking_id where package_tour_booking_master.booking_id='$bookingId'";
    $run = mysqlQuery($query);
    if (mysqli_num_rows($run) > 0) {
        while ($data = mysqli_fetch_array($run)) {
            array_push($names, $data['first_name'] . ' ' . $data['last_name']);
        }
    }
    if (!empty($names)) {
        return $names[0];
    }
}

$query =  "SELECT * FROM package_tour_booking_master where  booking_id in ( select booking_id from package_travelers_details where status!='Cancel') and delete_status='0'";
$query2 =  "SELECT * FROM package_quotation_program inner join package_tour_booking_master on package_quotation_program.quotation_id=package_tour_booking_master.quotation_id";


$type = 'display';
$result = mysqlQuery($query);
$result2 = mysqlQuery($query2);
$count = 1;
$usedId = array();
$usedId2 = array();

while ($data = mysqli_fetch_assoc($result)) {
    
    if (!in_array((int)$data['booking_id'], $usedId)) {
        $temparr =  getTodaysItenary($data['booking_id'], $selectedDate);
        if (!empty($temparr)) {
            array_push($array_s, $temparr);
        } else {
            $temparr2 =  getTodaysItenaryByPackage($data['booking_id'], $selectedDate);
            if (!empty($temparr2)) {
                            array_push($array_s, $temparr2);
            }   
        }
        array_push($usedId, (int)$data['booking_id']);
    }
}

$footer_data = array(
    "footer_data" => array()
);

array_push($array_s, $footer_data);
echo json_encode($array_s);

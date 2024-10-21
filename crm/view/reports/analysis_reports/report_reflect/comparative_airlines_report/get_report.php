<?php
include "../../../../../model/model.php";
$airlinetype = !empty($_POST['airlinetype']) ? $_POST['airlinetype']:null;
$airlineid = !empty($_POST['airlineid']) ? $_POST['airlineid']:null;
$fromdate = !empty($_POST['fromdate']) ? get_date_db($_POST['fromdate']) :null;
$todate = !empty($_POST['todate']) ? get_date_db($_POST['todate']) :null;
$array_s = array();


$_SESSION['dateqry'] = "";
$_SESSION['group_qry'] = "";
$_SESSION['package_qry'] = "";
if(!empty($airlinetype))
{
    $_SESSION['dateqry'] .= " and ticket_master.tour_type='".$airlinetype."'";    
    $_SESSION['group_qry'] .= " and tour_master.tour_type='".$airlinetype."'";    
    $_SESSION['package_qry'] .= " and package_tour_booking_master.tour_type='".$airlinetype."'";    
}

if(!empty($airlineid))
{
    $_SESSION['dateqry'] .= " and airline_master.airline_id ='".$airlineid."'";    
    $_SESSION['group_qry'] .= " and airline_master.airline_id ='".$airlineid."'";    
    $_SESSION['package_qry'] .= " and airline_master.airline_id ='".$airlineid."' ";    
}
if(!empty($fromdate) && !empty($todate))
{
    $_SESSION['dateqry'] .= " and ticket_master.created_at between '".$fromdate."' and '".$todate."' ";    
    $_SESSION['group_qry'] .= " and tourwise_traveler_details.form_date between '".$fromdate."' and '".$todate."' ";    
    $_SESSION['package_qry'] .= " and package_tour_booking_master.tour_from_date between '".$fromdate."' and '".$todate."' ";    
}

function get_seats($airline){

    $query1 = "SELECT * FROM airline_master INNER JOIN ticket_trip_entries on airline_master.airline_id = ticket_trip_entries.airline_id inner join ticket_master on ticket_trip_entries.ticket_id = ticket_master.ticket_id where airline_master.airline_id='".$airline."'".$_SESSION['dateqry'];
    $query1 .= " GROUP BY ticket_trip_entries.ticket_id";

    $res = mysqlQuery($query1);
    $seats = 0; 
    while($db = mysqli_fetch_array($res))
    {
	    if($db['cancel_type'] == '0'||$db['cancel_type'] == '2'||$db['cancel_type'] == '3'){
            $seats+= intval($db['adults']);
            $seats += intval($db['childrens']);
            $seats += intval($db['infant']);
        }
    }
    return  $seats; 
}

function get_amount($airline){

    $query1 = "SELECT * FROM airline_master INNER JOIN ticket_trip_entries on airline_master.airline_id = ticket_trip_entries.airline_id inner join ticket_master on ticket_trip_entries.ticket_id = ticket_master.ticket_id where airline_master.airline_id='".$airline."' ".$_SESSION['dateqry'];
    $query1 .= " GROUP BY ticket_trip_entries.ticket_id";
    $res = mysqlQuery($query1);
    $amount = 0; 
    $sale_amount = 0;
    while($db = mysqli_fetch_array($res))
    {
	    if($db['cancel_type'] == '0'||$db['cancel_type'] == '2'||$db['cancel_type'] == '3'){
            $sale_amount = $db['ticket_total_cost'];
            $cancel_amt = $db['cancel_amount'];
            if($cancel_amt==""){ $cancel_amt = 0;}
            if($db['cancel_type'] == '2'||$db['cancel_type'] == '3'){
                $cancel_estimate_data = json_decode($db['cancel_estimate']);
                $cancel_estimate = (!isset($cancel_estimate_data)) ? 0 : $cancel_estimate_data[0]->ticket_total_cost;
                $sale_amount = floatval($db['ticket_total_cost']) - floatval($cancel_estimate) + floatval($cancel_amt);
            }
            $amount+=floatval($sale_amount);
        }
    }
    return $amount; 
}

function get_group_seats($airline){

    $query1 = "SELECT * FROM plane_master INNER JOIN tourwise_traveler_details on plane_master.tourwise_traveler_id = tourwise_traveler_details.traveler_group_id INNER JOIN airline_master on plane_master.company = airline_master.airline_id INNER JOIN tour_master on tourwise_traveler_details.tour_id= tour_master.tour_id where airline_master.airline_id='".$airline."' ".$_SESSION['group_qry'];
    $res = mysqlQuery($query1);
    $seats = 0; 
    while($db = mysqli_fetch_array($res))
    {
            $seats+= (int)$db['seats'];
    }
    return  $seats; 
}





function get_group_amount($airline){
   
    $query1 = "SELECT * FROM plane_master INNER JOIN tourwise_traveler_details on plane_master.tourwise_traveler_id = tourwise_traveler_details.traveler_group_id INNER JOIN airline_master on plane_master.company = airline_master.airline_id INNER JOIN tour_master on tourwise_traveler_details.tour_id= tour_master.tour_id where airline_master.airline_id='".$airline."' ".$_SESSION['group_qry'];
    $res = mysqlQuery($query1);
    $amount = 0.00; 
    while($db = mysqli_fetch_array($res))
    {
            $amount+=floatval($db['plane_expense']);
    }
      return $amount; 
}

function get_package_seats($airline){
   
    $query1 = "SELECT * FROM package_plane_master INNER JOIN airline_master ON package_plane_master.company = airline_master.airline_id INNER JOIN package_tour_booking_master on package_plane_master.booking_id = package_tour_booking_master.booking_id INNER JOIN customer_master on package_tour_booking_master.customer_id = customer_master.customer_id where airline_master.airline_id='".$airline."' ".$_SESSION['package_qry'];
    $res = mysqlQuery($query1);
    $seats = 0; 
    while($db = mysqli_fetch_array($res))
    {
            $seats+= intval($db['seats']);
    }
      return  $seats; 
}





function get_package_amount($airline){
   
    $query1 = "SELECT * FROM package_plane_master INNER JOIN airline_master ON package_plane_master.company = airline_master.airline_id  INNER JOIN package_tour_booking_master on package_plane_master.booking_id = package_tour_booking_master.booking_id INNER JOIN customer_master on package_tour_booking_master.customer_id = customer_master.customer_id where airline_master.airline_id='".$airline."' ".$_SESSION['package_qry'];
    $res = mysqlQuery($query1);
    $amount = 0.00; 
    while($db = mysqli_fetch_array($res))
    {
        $amount+=floatval($db['amount']);
    }
    return $amount; 
}

//main qry
$main = array();

//Airline
if(empty($fromdate) && empty($todate))
{
    $query=  "SELECT * FROM airline_master INNER JOIN ticket_trip_entries on airline_master.airline_id = ticket_trip_entries.airline_id inner join ticket_master on ticket_trip_entries.ticket_id = ticket_master.ticket_id INNER JOIN customer_master on ticket_master.customer_id = customer_master.customer_id where 1=1 ".$_SESSION['dateqry']." GROUP BY airline_master.airline_id";
}
else
{                                                                                                                                                      
    $query=  "SELECT * FROM airline_master INNER JOIN ticket_trip_entries on airline_master.airline_id = ticket_trip_entries.airline_id inner join ticket_master on ticket_trip_entries.ticket_id = ticket_master.ticket_id INNER JOIN customer_master on ticket_master.customer_id = customer_master.customer_id where 1=1 ".$_SESSION['dateqry']."GROUP BY airline_master.airline_id";
}
$result = mysqlQuery($query);
$count = 1;
while($dataAirlines = mysqli_fetch_assoc($result))
{
    $temp = [
        'airline_name' => $dataAirlines['airline_name'],
        'airline_id' => $dataAirlines['airline_id'],
    ];
    array_push($main,$temp);
}

//group
if(empty($fromdate) && empty($todate))
{
    $query=  "SELECT * FROM plane_master INNER JOIN tourwise_traveler_details on plane_master.tourwise_traveler_id = tourwise_traveler_details.traveler_group_id INNER JOIN airline_master on plane_master.company = airline_master.airline_id INNER JOIN customer_master on tourwise_traveler_details.customer_id = customer_master.customer_id INNER JOIN tour_master on tourwise_traveler_details.tour_id= tour_master.tour_id where 1=1 ".$_SESSION['group_qry']."group by airline_master.airline_id ";
}
else
{                                                                                                                                                      
    $query=  "SELECT * FROM plane_master INNER JOIN tourwise_traveler_details on plane_master.tourwise_traveler_id = tourwise_traveler_details.traveler_group_id INNER JOIN airline_master on plane_master.company = airline_master.airline_id INNER JOIN customer_master on tourwise_traveler_details.customer_id = customer_master.customer_id INNER JOIN tour_master on tourwise_traveler_details.tour_id= tour_master.tour_id where 1=1 ".$_SESSION['group_qry']."group by airline_master.airline_id ";

}
$result = mysqlQuery($query);
$count = 1;
while($dataGroup = mysqli_fetch_assoc($result))
{
    $temp = [
        'airline_name' => $dataGroup['airline_name'],
        'airline_id' =>  $dataGroup['airline_id'],
        
    ];
    array_push($main,$temp);
    
}

//package
if(empty($fromdate) && empty($todate))
{
    $query=  "SELECT * FROM package_plane_master INNER JOIN airline_master ON package_plane_master.company = airline_master.airline_id INNER JOIN package_tour_booking_master on package_plane_master.booking_id = package_tour_booking_master.booking_id INNER JOIN customer_master on package_tour_booking_master.customer_id = customer_master.customer_id where 1=1 ".$_SESSION['package_qry'] ."group by airline_master.airline_id ";

}
else
{                                                                                                                                                      
    $query=  "SELECT * FROM package_plane_master INNER JOIN airline_master ON package_plane_master.company = airline_master.airline_id INNER JOIN package_tour_booking_master on package_plane_master.booking_id = package_tour_booking_master.booking_id INNER JOIN customer_master on package_tour_booking_master.customer_id = customer_master.customer_id where 1=1 ".$_SESSION['package_qry'] ."group by airline_master.airline_id ";

}

$result = mysqlQuery($query);
$count = 1;
while($dataPackage = mysqli_fetch_assoc($result))
{

    $temp = [
        'airline_name' => $dataPackage['airline_name'],
        'airline_id' =>  $dataPackage['airline_id'],
    ];
    array_push($main,$temp);
    
}

$airlines_main = array();
$id_main = array();
$index = 0;
foreach($main as $data)
{
    if(!in_array($data['airline_name'],$airlines_main))
    {
        array_push($airlines_main,$data['airline_name']);
        array_push($id_main,$data['airline_id']);
    }     
}

for($i=0;$i<=count($airlines_main)-1;$i++)
{
    $temparr = array("data" => array
    (
        $airlines_main[$i],
        get_seats($id_main[$i]) + get_group_seats($id_main[$i]) +  get_package_seats($id_main[$i]),
        number_format(floatval(get_amount($id_main[$i]) + get_group_amount($id_main[$i]) + get_package_amount($id_main[$i])),2),
        '<button class="btn btn-info btn-sm" onclick="view_com_airlines_modal('. $id_main[$i] .')" data-toggle="tooltip" title="View Details" id="view_btn-'. $id_main[$i] .'"><i class="fa fa-eye"></i></button>' 
    ),"bg" =>'' );
    
    array_push($array_s, $temparr);
}

$footer_data = array("footer_data" => array());

array_push($array_s, $footer_data); 
echo json_encode($array_s);

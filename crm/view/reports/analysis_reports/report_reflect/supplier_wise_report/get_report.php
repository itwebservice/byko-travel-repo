<?php
include "../../../../../model/model.php";
$_SESSION['dateqry'] = null;

$fromdate = !empty($_POST['fromdate']) ? get_date_db($_POST['fromdate']) : null;
$todate = !empty($_POST['todate']) ? get_date_db($_POST['todate']) : null;
$supplier_type = !empty($_POST['supplier_type']) ? get_date_db($_POST['supplier_type']) : null;

$array_s = array();
$count = 1;
if (!empty($fromdate) && !empty($todate)) {

    $dateQry = "and vendor_estimate.purchase_date between '$fromdate' and '$todate'";
    $_SESSION['dateqry'] = $dateQry;
} else {
    $dateQry = null;
}


//function transport 

function transport($dateQry)
{
    global $array_s;
    global $count;
    $qrytransport = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    transport_agency_master.transport_agency_name as `transportagencyName`,transport_agency_master.transport_agency_id as `transportId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join transport_agency_master on 
    transport_agency_master.transport_agency_id=vendor_estimate.vendor_type_id
    where vendor_estimate.vendor_type="Transport Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $transportRun = mysqlQuery($qrytransport);
    if (mysqli_num_rows($transportRun) > 0) {

        while ($db = mysqli_fetch_assoc($transportRun)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['transportagencyName'],
                'Transport Vendor',
                $db['totalPurchase'],
                $db['basicCost'], 
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
              
            ), "bg" => '');
            array_push($array_s, $temparr);
        }
    }
}
//train ticket vendor
function trainTicket($dateQry)
{
    global $array_s;
    global $count;
    $qrytrain = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`,
    train_ticket_vendor.vendor_name as `vendorName`,train_ticket_vendor.vendor_id as `vendorId`
    from vendor_estimate inner join train_ticket_vendor on 
    train_ticket_vendor.vendor_id=vendor_estimate.vendor_type_id 
    where vendor_estimate.vendor_type="Train Ticket Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $train = mysqlQuery($qrytrain);
    if (mysqli_num_rows($train) > 0) {

        while ($db = mysqli_fetch_assoc($train)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['vendorName'],
                'Train Ticket Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
//Ticket Vendor 
function ticketVendor($dateQry)
{
    global $array_s;
    global $count;
    $qryt = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    ticket_vendor.vendor_name as `vendorName`,ticket_vendor.vendor_id as `vendorId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join ticket_vendor on 
    ticket_vendor.vendor_id=vendor_estimate.vendor_type_id 
    where vendor_estimate.vendor_type="Ticket Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $ticketvendor = mysqlQuery($qryt);
    if (mysqli_num_rows($ticketvendor) > 0) {

        while ($db = mysqli_fetch_assoc($ticketvendor)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['vendorName'],
                'Ticket Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
//cruise Master
function cruiseMsater($dateQry)
{
    global $array_s;
    global $count;
    $qryc = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    cruise_master.company_name as `companyName`,cruise_master.cruise_id as `cruiseId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join cruise_master on 
    cruise_master.cruise_id=vendor_estimate.vendor_type_id
    where vendor_estimate.vendor_type="Cruise Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $cruis = mysqlQuery($qryc);
    if (mysqli_num_rows($cruis) > 0) {

        while ($db = mysqli_fetch_assoc($cruis)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['companyName'],
                'Cruise Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
//site Seeing vendor 
function siteSeeing($dateQry)
{
    global $array_s;
    global $count;
    $qrys = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    site_seeing_vendor.vendor_name as `vendorName`,site_seeing_vendor.vendor_id as `vendorId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join site_seeing_vendor on 
    site_seeing_vendor.vendor_id=vendor_estimate.vendor_type_id 
    where vendor_estimate.vendor_type="Excursion Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $siteSeeingvendor = mysqlQuery($qrys);
    if (mysqli_num_rows($siteSeeingvendor) > 0) {

        while ($db = mysqli_fetch_assoc($siteSeeingvendor)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['vendorName'],
                'Excursion Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
// Dmc Master
function dmcMaster($dateQry)
{
    global $array_s;
    global $count;
    $qryd = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    dmc_master.company_name as `companyName`,dmc_master.dmc_id as `dmcId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join dmc_master on 
    dmc_master.dmc_id=vendor_estimate.vendor_type_id 
    where vendor_estimate.vendor_type="Dmc Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $dmcMaster = mysqlQuery($qryd);
    if (mysqli_num_rows($dmcMaster) > 0) {

        while ($db = mysqli_fetch_assoc($dmcMaster)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['companyName'],
                'Dmc Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
// Visa Vendor
function visaVendor($dateQry)
{
    global $array_s;
    global $count;
    $qryv = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    visa_vendor.vendor_name as `vendorName`,visa_vendor.vendor_id as `vendorId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join visa_vendor on 
    visa_vendor.vendor_id=vendor_estimate.vendor_type_id 
    where vendor_estimate.vendor_type="Visa Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $visaVendor = mysqlQuery($qryv);
    if (mysqli_num_rows($visaVendor) > 0) {

        while ($db = mysqli_fetch_assoc($visaVendor)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['vendorName'],
                'Visa Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
// Insurance Vendor
function insuranceVendor($dateQry)
{
    global $array_s;
    global $count;
    $qryi = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`,
    insuarance_vendor.vendor_name as `vendorName`,insuarance_vendor.vendor_id as `vendorId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join insuarance_vendor on 
    insuarance_vendor.vendor_id=vendor_estimate.vendor_type_id 
    where vendor_estimate.vendor_type="Insurance Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $insuranceVendor = mysqlQuery($qryi);
    if (mysqli_num_rows($insuranceVendor) > 0) {

        while ($db = mysqli_fetch_assoc($insuranceVendor)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['vendorName'],
                'Insurance Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
//Other Vendor 
// function otherVendor($dateQry)
// {
//     global $array_s;
//     global $count;
//     $qryo = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total) as netTotal,count(*) as `totalPurchase`, 
//     other_vendors.vendor_name as `vendorName`,other_vendors.vendor_id as `vendorId`,
//     vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
//     from vendor_estimate inner join other_vendors on other_vendors.vendor_id=vendor_estimate.vendor_type_id 
//     where vendor_estimate.vendor_type="Other Vendor"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
//     $otherVendor = mysqlQuery($qryo);
//     if (mysqli_num_rows($otherVendor) > 0) {

//         while ($db = mysqli_fetch_assoc($otherVendor)) {
//             $temparr = array("data" => array(
//                 (int) ($count++),
//                 $db['vendorName'],
//                 'Other Vendor',
//                 $db['totalPurchase'],
//                 $db['basicCost'],
//                 '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" data-toggle="tooltip" id="view_btn-'. $db['vendorTypeId'] .'" title="View Details"><i class="fa fa-eye"></i></button>'
//             ), "bg" => null);
//             array_push($array_s, $temparr);
//         }
//     }
// }
// Car Rental Vendor
function carrentalVendor($dateQry)
{
    global $array_s;
    global $count;
    $qryr = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total)as netTotal,count(*) as `totalPurchase`,
    car_rental_vendor.vendor_name as `vendorName`,car_rental_vendor.vendor_id as `vendorId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join car_rental_vendor on car_rental_vendor.vendor_id=vendor_estimate.vendor_type_id
     where vendor_estimate.vendor_type="Car Rental Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $carrentalVendor = mysqlQuery($qryr);
    if (mysqli_num_rows($carrentalVendor) > 0) {

        while ($db = mysqli_fetch_assoc($carrentalVendor)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['vendorName'],
                'Car Rental Vendor',
                $db['totalPurchase'],
                $db['basicCost'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}
// Hotel Master
function hotelVendor($dateQry)
{
    global $array_s;
    global $count;
    $qryh = 'select SUM(vendor_estimate.net_total)as basicCost,SUM(vendor_estimate.net_total)as netTotal,count(*) as `totalPurchase`,
    hotel_master.hotel_name as `hotelname`,hotel_master.hotel_id as `hotelId`,
    vendor_estimate.vendor_type_id as `vendorTypeId`, vendor_estimate.vendor_type as `vendorType`, vendor_estimate.estimate_type_id as `estimateTypeId`, vendor_estimate.estimate_type as `estimateType`
    from vendor_estimate inner join hotel_master on hotel_master.hotel_id=vendor_estimate.vendor_type_id
    where vendor_estimate.vendor_type="Hotel Vendor" and vendor_estimate.status!="Cancel"' . $dateQry . ' GROUP BY vendor_estimate.vendor_type_id';
    $hotelVendor = mysqlQuery($qryh);
    if (mysqli_num_rows($hotelVendor) > 0) {

        while ($db = mysqli_fetch_assoc($hotelVendor)) {
            $temparr = array("data" => array(
                (int) ($count++),
                $db['hotelname'],
                'Hotel Vendor',
                $db['totalPurchase'],
                $db['netTotal'],
                '<button class="btn btn-info btn-sm" onclick="allModal(`'.$db['vendorTypeId'].'`,`'.$db['vendorType'].'`,`'.$db['estimateTypeId'].'`,`'.$db['estimateType'].'`)" id="view_btn-'. $db['vendorTypeId'] .'" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></button>'
            ), "bg" => null);
            array_push($array_s, $temparr);
        }
    }
}

//call function
if (!empty($_POST['supplier_type'])) {
    $type = $_POST['supplier_type'];
    if ($type == 'Transport Vendor') 
    {
        transport($dateQry);
    }
    if ($type == 'Train Ticket Vendor') 
    {
        trainTicket($dateQry);
    }
    if ($type == 'Ticket Vendor') 
    {
        ticketVendor($dateQry);
    }
    if ($type == 'Cruise Vendor') 
    {
        cruiseMsater($dateQry);
    }
    if ($type == 'Excursion Vendor') 
    {
        siteSeeing($dateQry);
    }
    if ($type == 'Visa Vendor') 
    {
        visaVendor($dateQry);
    }
    if ($type == 'Insurance Vendor') 
    {
        insuranceVendor($dateQry);
    }
    if ($type == 'Car Rental Vendor')
    {
        carrentalVendor($dateQry);
    }
    if ($type == 'Hotel Vendor')
    {
        hotelVendor($dateQry);
    }
} else {
  transport($dateQry);
    trainTicket($dateQry);
    ticketVendor($dateQry);
    cruiseMsater($dateQry);
    siteSeeing($dateQry);
    dmcMaster($dateQry);
    visaVendor($dateQry);
    insuranceVendor($dateQry);
    // otherVendor($dateQry);
    carrentalVendor($dateQry);
    hotelVendor($dateQry);
}
//footer
$footer_data = array("footer_data" => array(
    'total_footers' => 0,

    'foot0' => "Total :",
    'class0' => "text-left info",
));
//print
array_push($array_s, $footer_data);
echo json_encode($array_s);

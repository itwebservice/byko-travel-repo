<?php
include "../../../../model/model.php";
$quotation_id = $_REQUEST['quotation_id'];
$hotel_option = $_REQUEST['hotel_option'];

$qDetails = mysqli_fetch_assoc(mysqlQuery("SELECT `hotel_details`,`enquiry_details`,`costing_details` FROM `hotel_quotation_master` WHERE `quotation_id` =".$quotation_id));

$appDetails = json_decode($qDetails['hotel_details'], TRUE);
$enqDetails = json_decode($qDetails['enquiry_details'], TRUE);
$costing_details = json_decode($qDetails['costing_details'], TRUE);
$city_hotelDetails = [];
$costDetails = [];
for($index = 0; $index<sizeof($appDetails); $index++){

    $option = $appDetails[$index]['option'];
    if($hotel_option == $option){

        $data = $appDetails[$index]['data'];
        for($i = 0;$i<sizeof($data);$i++){

            $hotel_id = $data[$i]['hotel_id'];
            $city_id = $data[$i]['city_id'];
            $hotelName = mysqli_fetch_assoc(mysqlQuery("SELECT `hotel_name` FROM `hotel_master` WHERE `hotel_id`=".$hotel_id));
            $cityName = mysqli_fetch_assoc(mysqlQuery("SELECT `city_name` FROM `city_master` WHERE `city_id`=".$city_id));

            $object = new stdClass(); // Create a new object
            $object->city_name = $cityName['city_name'];
            $object->hotel_name = $hotelName['hotel_name'];
            $object->data = $data[$i];

            array_push($city_hotelDetails,$object);
        }
    }
}
for($index = 0; $index<sizeof($costing_details); $index++){

    $option = $costing_details[$index]['option'];
    if($hotel_option == $option){

        $data = $costing_details[$index]['costing'];
        $object = new stdClass(); // Create a new object
        $object->costing = $data;
        array_push($costDetails,$object);
    }
}

// $finalArray['hotel_details'] = $appDetails;
$finalArray['city_hotel_details'] = $city_hotelDetails;
$finalArray['enquiry_details'] =  $enqDetails;
$finalArray['costing_details'] =  $costDetails;
echo json_encode($finalArray);
?>
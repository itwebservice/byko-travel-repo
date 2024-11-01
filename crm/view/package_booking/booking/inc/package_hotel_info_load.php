<?php
include_once('../../../../model/model.php');

$package_id = isset($_POST['package_id']) ? $_POST['package_id'] : 0;
$from_date = isset($_POST['from_date']) ? get_date_user($_POST['from_date']) : 0;
$hotel_info_arr = array();
$hotel_info_arr1 = array();
$tr_info_arr1 = array();

$query = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id='$package_id'"));
$sq_hotel = mysqlQuery("select * from custom_package_hotels where package_id='$package_id'");
while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
  
  $total_nights = $row_hotel['total_days'];
  $sq_hotel_id = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id = '$row_hotel[hotel_name]'"));
  $hotel_name1 = $sq_hotel_id['hotel_name'];
  $sq_city_id = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id = '$row_hotel[city_name]'"));
  $city_name1 = $sq_city_id['city_name'];

  $check_in_date = $from_date;
  $check_out_date = date('d-m-Y', strtotime($check_in_date . ' +'.$total_nights.' days'));
  $arr = array(
    'city_id' => $row_hotel['city_name'],
    'hotel_id1' => $row_hotel['hotel_name'],
    'city_name' => $city_name1,
    'hotel_name' => $hotel_name1,
    'check_in_date' => $check_in_date,
    'check_out_date' => $check_out_date
  );
  $from_date = $check_out_date;
  array_push($hotel_info_arr1, $arr);
}

$sq_tr = mysqlQuery("select * from custom_package_transport where package_id='$package_id'");
while($row_tr = mysqli_fetch_assoc($sq_tr)){
    
    $sq_hotel_id = mysqli_fetch_assoc(mysqlQuery("select * from b2b_transfer_master where entry_id = '$row_tr[vehicle_name]'"));
    $bus_name = $sq_hotel_id['vehicle_name'];
    // Pickup
    if($row_tr['pickup_type'] == 'city'){
      $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_tr[pickup]'"));
      $pickup = $row['city_name'];
      $pickup_id = $row['city_id'];
    }
    else if($row_tr['pickup_type'] == 'hotel'){
      $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_tr[pickup]'"));
      $pickup_id = $row['hotel_id'];
      $pickup = $row['hotel_name'];
    }
    else{
      $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_tr[pickup]'"));
      $airport_nam = clean($row['airport_name']);
      $airport_code = clean($row['airport_code']);
      $pickup = $airport_nam." (".$airport_code.")";
      $pickup_id = $row['airport_id'];
    }
    // Drop
    if($row_tr['drop_type'] == 'city'){
      $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_tr[drop]'"));
      $drop = $row['city_name'];
      $drop_id = $row['city_id'];
    }
    else if($row_tr['drop_type'] == 'hotel'){
      $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_tr[drop]'"));
      $drop = $row['hotel_name'];
      $drop_id = $row['hotel_id'];
    }
    else{
      $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_tr[drop]'"));
      $airport_nam = clean($row['airport_name']);
      $airport_code = clean($row['airport_code']);
      $drop = $airport_nam." (".$airport_code.")";
      $drop = $drop;
      $drop_id = $row['airport_id'];
    }
    $pickup_type = $row_tr['pickup_type'];
    $drop_type = $row_tr['drop_type'];

    $arr1 = array(
      'bus_name' => $bus_name,
      'vehicle_id' => $row_tr['vehicle_name'],
      'pickup' => $pickup,
      'drop' => $drop,
      'pickup_id' => $pickup_id,
      'drop_id' => $drop_id,
      'pickup_type' => $pickup_type,
      'drop_type' => $drop_type
    );
    array_push($tr_info_arr1, $arr1);
}

$hotel_info_arr['hotel_info_arr'] = $hotel_info_arr1;
$hotel_info_arr['transport_info_arr'] = $tr_info_arr1;
$hotel_info_arr['package_name'] = $query['package_name'];
$hotel_info_arr['tour_type'] = $query['tour_type'];

echo json_encode($hotel_info_arr);
?>
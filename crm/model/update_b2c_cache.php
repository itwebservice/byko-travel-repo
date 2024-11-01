<?php
include 'model.php';
define('ROOT_DIR', realpath(__DIR__.'/..'));
$final_array = array();
$today_date = date("Y-m-d");

//Company Profile
$company_profile_data = array();
$result = mysqli_fetch_array(mysqlQuery("SELECT * FROM app_settings where setting_id='1'"));
$sq_curr = mysqli_fetch_assoc(mysqlQuery("SELECT currency_code FROM currency_name_master where id='$result[currency]'"));
$app_currency_code = $sq_curr['currency_code'];
$sq_to = mysqli_fetch_assoc(mysqlQuery("select currency_rate from roe_master where currency_id='$result[currency]'"));
$to_currency_rate = $sq_to['currency_rate'];

$temp_array = array(
    'company_name' => $result['app_name'],
    'website' => $result['app_website'],
    'contact_no' => $result['app_contact_no'],
    'address' => $result['app_address'],
    'email_id' => $result['app_email_id'],
    'currency' => $result['currency'],
    'app_version' => $result['app_version'],
    'canc_policy_url' => $result['policy_url'],
    'currency_code' => $app_currency_code
);
array_push($company_profile_data,$temp_array);

//Package Tour
$package_tour_data = array();
$result = mysqlQuery("SELECT * FROM custom_package_master where status!='Inactive'");
while($row = mysqli_fetch_array($result)) {
    
    //Sightseeing
    $result1 = mysqlQuery("SELECT * FROM custom_package_program where package_id='$row[package_id]'");
    $sightseeing_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        $temp_array = array(
            'attraction' => addslashes($row1['attraction']),
            'daywise_program' => addslashes($row1['day_wise_program']),
            'overnight_stay' => addslashes($row1['stay']),
            'meal_plan' => $row1['meal_plan']
        );
        array_push($sightseeing_array,$temp_array);
    }
    //Hotels
    $result1 = mysqlQuery("SELECT * FROM custom_package_hotels where package_id='$row[package_id]'");
    $hotel_array = array();
    while($row_hotel = mysqli_fetch_assoc($result1)) {

        $sq_city = mysqli_fetch_assoc(mysqlQuery("SELECT city_name FROM city_master where city_id='$row_hotel[city_name]'"));
        $sq_hotel = mysqli_fetch_assoc(mysqlQuery("SELECT hotel_name FROM hotel_master where hotel_id='$row_hotel[hotel_name]'"));
        $temp_array = array(
            'city_name' => $sq_city['city_name'],
            'hotel_name' => $sq_hotel['hotel_name'],
            'hotel_type' => $row_hotel['hotel_type'],
            'total_night' => $row_hotel['total_days']
        );
        array_push($hotel_array,$temp_array);
    }
    //Transport
    $result1 = mysqlQuery("SELECT * FROM custom_package_transport where package_id='$row[package_id]'");
    $transport_array = array();
    while($row_transfer = mysqli_fetch_array($result1)) {

        $sq_transfer = mysqli_fetch_assoc(mysqlQuery("SELECT vehicle_name FROM b2b_transfer_master where entry_id='$row_transfer[vehicle_name]'"));
        // Pickup
        if($row_transfer['pickup_type'] == 'city'){
            $row2 = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_transfer[pickup]'"));
            $pickup = $row2['city_name'];
        }
        else if($row_transfer['pickup_type'] == 'hotel'){
            $row2 = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_transfer[pickup]'"));
            $pickup = $row2['hotel_name'];
        }
        else{
            $row2 = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_transfer[pickup]'"));
            $airport_nam = clean($row2['airport_name']);
            $airport_code = clean($row2['airport_code']);
            $pickup = $airport_nam." (".$airport_code.")";
        }
        //Drop-off
        if($row_transfer['drop_type'] == 'city'){
            $row2 = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_transfer[drop]'"));
            $drop = $row2['city_name'];
        }
        else if($row_transfer['drop_type'] == 'hotel'){
            $row2 = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_transfer[drop]'"));
            $drop = $row2['hotel_name'];
        }
        else{
            $row2 = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_transfer[drop]'"));
            $airport_nam = clean($row2['airport_name']);
            $airport_code = clean($row2['airport_code']);
            $drop = $airport_nam." (".$airport_code.")";
        }
        $temp_array = array(
            'vehicle_name' => $sq_transfer['vehicle_name'],
            'pickup_type'=>$row_transfer['pickup_type'],
            'pickup' => $pickup,
            'drop_type'=>$row_transfer['drop_type'],
            'drop' => $drop
        );
        array_push($transport_array,$temp_array);
    }
    // Package tariff
    $package_tariff_array = array();
    $sq_package_tariff = mysqlQuery("SELECT * FROM custom_package_tariff where package_id='$row[package_id]'");
    while($row_package_tariff = mysqli_fetch_array($sq_package_tariff)) {

        $temp_array = array(
            'hotel_type' => $row_package_tariff['hotel_type'],
            'min_pax' => $row_package_tariff['min_pax'],
            'max_pax' => $row_package_tariff['max_pax'],
            'from_date' => $row_package_tariff['from_date'],
            'to_date'=> $row_package_tariff['to_date'],
            'adult_cost'=> $row_package_tariff['cadult'],
            'cwb_cost'=> $row_package_tariff['ccwb'],
            'cwob_cost'=> $row_package_tariff['ccwob'],
            'infant_cost'=> $row_package_tariff['cinfant'],
            'extra_bed_cost'=> $row_package_tariff['cextra']
        );
        array_push($package_tariff_array,$temp_array);
    }
    // Package offers
    $package_offer_array = array();
    $sq_package_tariff = mysqlQuery("SELECT * FROM custom_package_offers where package_id='$row[package_id]'");
    while($row_package_tariff = mysqli_fetch_array($sq_package_tariff)) {

        $temp_array = array(
            'type' => $row_package_tariff['type'],
            'from_date' => $row_package_tariff['from_date'],
            'to_date'=> $row_package_tariff['to_date'],
            'offer_in'=> $row_package_tariff['offer_in'],
            'coupon_code'=> $row_package_tariff['coupon_code'],
            'offer_amount'=> $row_package_tariff['offer_amount'],
            'agent_type'=> $row_package_tariff['agent_type']
        );
        array_push($package_offer_array,$temp_array);
    }
    //Package Tour Array
    $sq_dest = mysqli_fetch_assoc(mysqlQuery("SELECT dest_name FROM destination_master where dest_id='$row[dest_id]'"));
    $sq_gallery = mysqli_fetch_assoc(mysqlQuery("SELECT image_url FROM gallary_master where dest_id='$row[dest_id]'"));
    $sq_curr = mysqli_fetch_assoc(mysqlQuery("SELECT currency_code FROM currency_name_master where id='$row[currency_id]'"));
    
    $sq_from = mysqli_fetch_assoc(mysqlQuery("select currency_rate from roe_master where currency_id='$row[currency_id]'"));
    $from_currency_rate = $sq_from['currency_rate'];
    $adult_cost = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row['adult_cost'] : 0;
    $infant_cost = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row['infant_cost'] : 0;
    $child_with = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row['child_with'] : 0;
    $child_without = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row['child_without'] : 0;
    $extra_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row['extra_bed'] : 0;
    $temp_array = array(
        'package_id' => $row['package_id'],
        'dest_name' => $sq_dest['dest_name'],
        'dest_id' => $row['dest_id'],
        'package_name' => $row['package_name'],
        'package_code' => $row['package_code'],
        'total_days' => $row['total_days'],
        'total_nights' => $row['total_nights'],
        'p_currency_id'=> $row['currency_id'],
        'tour_type' => $row['tour_type'],
        'note' => $row['note'],
        'adult_cost' => $adult_cost,
        'infant_cost' => $infant_cost,
        'child_with' => $child_with,
        'child_without' => $child_without,
        'extra_bed' => $extra_bed,
        'image_url'=>$sq_gallery['image_url'],
        'p_currency_name'=> $sq_curr['currency_code'],
        'inclusions' => addslashes($row['inclusions']),
        'exclusions' => addslashes($row['exclusions']),
        'sightseeing_array' => json_encode($sightseeing_array),
        'hotel_array' => json_encode($hotel_array),
        'transport_array' => json_encode($transport_array),
        'package_tariff_array' => json_encode($package_tariff_array),
        'package_offer_array' => json_encode($package_offer_array),
    );
    array_push($package_tour_data,$temp_array);
}
//Group Tour
$group_tour_data = array();
$result = mysqlQuery("SELECT * FROM tour_master where active_flag!='Inactive'");
while($row = mysqli_fetch_array($result)) {
    
    //Tour Groups
    $result1 = mysqlQuery("SELECT * FROM tour_groups where tour_id='$row[tour_id]' and status!='Cancel'");
    $tour_groups_array = array();
    while($row1 = mysqli_fetch_array($result1)) {

        $date1_ts = strtotime($row1['from_date']);
        $date2_ts = strtotime($row1['to_date']);
        $diff = $date2_ts - $date1_ts;
        $diff = round($diff / 86400) + 1;

        $temp_array = array(
            'from_date' => get_date_user($row1['from_date']),
            'to_date' => get_date_user($row1['to_date']),
            'capacity' => $row1['capacity'],
            'days'=>$diff
        );
        array_push($tour_groups_array,$temp_array);
    }
    //Sightseeing
    $result1 = mysqlQuery("SELECT * FROM group_tour_program where tour_id='$row[tour_id]'");
    $sightseeing_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        $temp_array = array(
            'attraction' => addslashes($row1['attraction']),
            'daywise_program' => addslashes($row1['day_wise_program']),
            'overnight_stay' => addslashes($row1['stay']),
            'meal_plan' => $row1['meal_plan'],
            'daywise_image' => $row1['daywise_images']
        );
        array_push($sightseeing_array,$temp_array);
    }

    //Train Groups
    $result1 = mysqlQuery("SELECT * FROM group_train_entries where tour_id='$row[tour_id]' ");
    $train_groups_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        $temp_array = array(
            'from_location' => $row1['from_location'],
            'to_location' => $row1['to_location'],
            'class' => $row1['class']
        );
        array_push($train_groups_array,$temp_array);
    }
    //Hotel Groups
    $result1 = mysqlQuery("SELECT * FROM group_tour_hotel_entries where tour_id='$row[tour_id]' ");
    $hotel_groups_array = array();
    while($row1 = mysqli_fetch_array($result1)) {

        $sq_city = mysqli_fetch_assoc(mysqlQuery("SELECT city_name FROM city_master where city_id='$row1[city_id]'"));
        $sq_hotel = mysqli_fetch_assoc(mysqlQuery("SELECT hotel_name FROM hotel_master where hotel_id='$row1[hotel_id]'"));
        $temp_array = array(
            'city_name' => $sq_city['city_name'],
            'hotel_name' => $sq_hotel['hotel_name'],
            'hotel_type' => $row1['hotel_type'],
            'total_nights' => $row1['total_nights']
        );
        array_push($hotel_groups_array,$temp_array);
    }
    //Flight Groups
    $result1 = mysqlQuery("SELECT * FROM  group_tour_plane_entries where tour_id='$row[tour_id]' ");
    $flight_groups_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        $temp_array = array(
            'from_location' => $row1['from_location'],
            'to_location' => $row1['to_location'],
            'airline_name' => $row1['airline_name'],
            'class' => $row1['class']
        );
        array_push($flight_groups_array,$temp_array);
    }
    //Cruise
    $result1 = mysqlQuery("SELECT * FROM  group_cruise_entries where tour_id='$row[tour_id]' ");
    $cruise_groups_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        $temp_array = array(
            'route' => $row1['route'],
            'cabin' => $row1['cabin']
        );
        array_push($cruise_groups_array,$temp_array);
    }
    $sq_dest = mysqli_fetch_assoc(mysqlQuery("SELECT dest_name FROM destination_master where dest_id='$row[dest_id]'"));
    $sq_gallery = mysqli_fetch_assoc(mysqlQuery("SELECT image_url FROM gallary_master where dest_id='$row[dest_id]'"));
    $temp_array = array(
        'tour_id' => $row['tour_id'],
        'tour_type' => $row['tour_type'],
        'tour_name' => $row['tour_name'],
        'dest_name' => $sq_dest['dest_name'],
        'dest_id' => $row['dest_id'],
        'inclusions' => $row['inclusions'],
        'exclusions' => $row['exclusions'],
        'tour_groups_array' => json_encode($tour_groups_array),
        'sightseeing_array' => json_encode($sightseeing_array),
        'train_groups_array' => json_encode($train_groups_array),
        'hotel_groups_array'=> json_encode($hotel_groups_array),
        'flight_groups_array' => json_encode($flight_groups_array),
        'cruise_groups_array'=> json_encode($cruise_groups_array),
        'adult_cost'=> $row['adult_cost'],
        'child_with_cost'=> $row['child_with_cost'],
        'child_without_cost'=> $row['child_without_cost'],
        'infant_cost'=> $row['infant_cost'],
        'with_bed_cost'=> $row['with_bed_cost'],
        'image_url'=>$sq_gallery['image_url']
    );
    array_push($group_tour_data,$temp_array);
}
//Hotels
$hotels_data = array();
$hotel_costing_array = array();
$result = mysqlQuery("SELECT * FROM hotel_master where active_flag!='Inactive'");
while($row = mysqli_fetch_array($result)) {
    
    //Hotel Images
    $result1 = mysqlQuery("SELECT * FROM hotel_vendor_images_entries where hotel_id='$row[hotel_id]'");
    $hotel_images_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        $temp_array = array(
            'pic_url' => $row1['hotel_pic_url']
        );
        array_push($hotel_images_array,$temp_array);
    }
    //Hotel Costing
    $string = '';
    $curr_arr = array();
    $hotel_contracted_costing_array = array();
    $hotel_blackdated_costing_array = array();
    $hotel_weekend_costing_array = array();
    $row1 = mysqlQuery("SELECT pricing_id,currency_id FROM hotel_vendor_price_master where hotel_id='$row[hotel_id]'");
    while ($row2 = mysqli_fetch_array($row1)) {
	
        $sq_from = mysqli_fetch_assoc(mysqlQuery("select currency_rate from roe_master where currency_id='$row2[currency_id]'"));
        $from_currency_rate = $sq_from['currency_rate'];

        $resultc1 = mysqlQuery("SELECT * FROM hotel_contracted_tarrif where pricing_id = '$row2[pricing_id]' and to_date>='$today_date'");
        while($rowc1 = mysqli_fetch_array($resultc1)) {

            $double_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc1['double_bed'] : 0;
            $child_with_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc1['child_with_bed'] : 0;
            $child_without_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc1['child_without_bed'] : 0;
            $extra_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc1['extra_bed'] : 0;
            $temp_array = array(
                'room_category' => $rowc1['room_category'],
                'from_date' => $rowc1['from_date'],
                'to_date' => $rowc1['to_date'],
                'room_cost' => $double_bed,
                'child_with_bed' => $child_with_bed,
                'child_without_bed'=>$child_without_bed,
                'extra_bed'=>$extra_bed
            );
            array_push($hotel_contracted_costing_array,$temp_array);
        }
        $resultc2 = mysqlQuery("SELECT * FROM hotel_blackdated_tarrif where pricing_id = '$row2[pricing_id]' and to_date>='$today_date'");
        while($rowc2 = mysqli_fetch_array($resultc2)) {

            $double_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc2['double_bed'] : 0;
            $child_with_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc2['child_with_bed'] : 0;
            $child_without_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc2['child_without_bed'] : 0;
            $extra_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc2['extra_bed'] : 0;
            $temp_array = array(
                'room_category' => $rowc2['room_category'],
                'from_date' => $rowc2['from_date'],
                'to_date' => $rowc2['to_date'],
                'room_cost' => $double_bed,
                'child_with_bed' => $child_with_bed,
                'child_without_bed'=>$child_without_bed,
                'extra_bed'=>$extra_bed
            );
            array_push($hotel_blackdated_costing_array,$temp_array);
        }
        $resultc3 = mysqlQuery("SELECT * FROM hotel_weekend_tarrif where pricing_id = '$row2[pricing_id]'");
        while($rowc3 = mysqli_fetch_array($resultc3)) {

            $double_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc3['double_bed'] : 0;
            $child_with_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc3['child_with_bed'] : 0;
            $child_without_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc3['child_without_bed'] : 0;
            $extra_bed = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $rowc3['extra_bed'] : 0;
            $temp_array = array(
                'room_category' => $rowc3['room_category'],
                'day' => $rowc3['day'],
                'room_cost' => $double_bed,
                'child_with_bed' => $child_with_bed,
                'child_without_bed'=>$child_without_bed,
                'extra_bed'=>$extra_bed
            );
            array_push($hotel_weekend_costing_array,$temp_array);
        }
    }
    array_push($hotel_costing_array,array('hotel_contracted_costing_array'=>$hotel_contracted_costing_array,'hotel_blackdated_costing_array'=>$hotel_blackdated_costing_array,'hotel_weekend_costing_array'=>$hotel_weekend_costing_array));

    $sq_city = mysqli_fetch_assoc(mysqlQuery("SELECT city_name FROM city_master where city_id='$row[city_id]'"));
    $sq_state = mysqli_fetch_assoc(mysqlQuery("SELECT state_name FROM state_master where id='$row[state_id]'"));
    $temp_array = array(
        'hotel_id' => $row['hotel_id'],
        'hotel_name' => $row['hotel_name'],
        'city_name' => $sq_city['city_name'],
        'city_id' => $row['city_id'],
        'email_id' => $row['email_id'],
        'state_name'=>$sq_state['state_name'],
        'country'=>'',
        'category'=>$row['rating_star'],
        'type'=>$row['hotel_type'],
        'meal_plan'=>$row['meal_plan'],
        'address'=>$row['hotel_address'],
        'description'=>$row['description'],
        'amenities'=>$row['amenities'],
        'policies'=>$row['policies'],
        'cwb_ages'=>$row['cwb_from'].'-'.$row['cwb_to'],
        'cwob_ages'=>$row['cwob_from'].'-'.$row['cwob_to'],
        'hotel_images_array' => json_encode($hotel_images_array),
        'hotel_contracted_costing_array'=>$hotel_contracted_costing_array,
        'hotel_blackdated_costing_array'=>$hotel_blackdated_costing_array,
        'hotel_weekend_costing_array'=>$hotel_weekend_costing_array
    );
    array_push($hotels_data,$temp_array);
}
//Activities
$activity_data = array();
$result = mysqlQuery("SELECT * FROM excursion_master_tariff where active_flag!='Inactive'");
while($row = mysqli_fetch_array($result)) {
    
    //Activity Images
    $result1 = mysqlQuery("SELECT * FROM excursion_master_images where exc_id='$row[entry_id]'");
    $exc_images_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        $temp_array = array(
            'image_url' => $row1['image_url']
        );
        array_push($exc_images_array,$temp_array);
    }
    //Costing
    $result1 = mysqlQuery("SELECT * FROM excursion_master_tariff_basics where exc_id='$row[entry_id]' and to_date>='$today_date'");
    $exc_costing_array = array();
    while($row1 = mysqli_fetch_array($result1)) {
        
        $sq_from = mysqli_fetch_assoc(mysqlQuery("select currency_rate from roe_master where currency_id='$row[currency_code]'"));
        $from_currency_rate = $sq_from['currency_rate'];

        $adult_cost = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row1['adult_cost'] : 0;
        $child_cost = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row1['child_cost'] : 0;
        $infant_cost = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $row1['infant_cost'] : 0;
        $temp_array = array(
            'transfer_option' => $row1['transfer_option'],
            'from_date' => $row1['from_date'],
            'to_date' => $row1['to_date'],
            'adult_cost' => $adult_cost,
            'child_cost' => $child_cost,
            'infant_cost' => $infant_cost

        );
        array_push($exc_costing_array,$temp_array);
    }
    
    $sq_city = mysqli_fetch_assoc(mysqlQuery("SELECT city_name FROM city_master where city_id='$row[city_id]'"));
    $sq_curr = mysqli_fetch_assoc(mysqlQuery("SELECT currency_code FROM currency_name_master where id='$row[currency_code]'"));
    $temp_array = array(
        'activity_id' => $row['entry_id'],
        'activity_name' => $row['excursion_name'],
        'city_name' => $sq_city['city_name'],
        'city_id' => $row['city_id'],
        'duration' => $row['duration'],
        'pickup_point'=>$row['departure_point'],
        'rep_time'=>$row['rep_time'],
        'description'=>$row['description'],
        'off_days'=>$row['off_days'],
        'note'=>$row['note'],
        'currency'=>$sq_curr['currency_code'],
        'inclusions'=>addslashes($row['inclusions']),
        'exclusions'=>addslashes($row['exclusions']),
        'terms_condition'=>addslashes($row['terms_condition']),
        'useful_info'=>addslashes($row['useful_info']),
        'booking_policy'=>addslashes($row['booking_policy']),
        'canc_policy'=>addslashes($row['canc_policy']),
        'images_array' => json_encode($exc_images_array),
        'costing_array' => json_encode($exc_costing_array)
    );
    array_push($activity_data,$temp_array);
}
//Transfer
$transfer_data = array();
$costing_array = array();
$result = mysqlQuery("SELECT * FROM b2b_transfer_master where status!='Inactive'");
while($row = mysqli_fetch_array($result)) {
    
    //Costing
    $tr_costing_array = array();
    $string = '';
    $row1 = mysqlQuery("SELECT tariff_id,currency_id FROM b2b_transfer_tariff where vehicle_id='$row[entry_id]'");
    while ($row2 = mysqli_fetch_array($row1)) {

        $currency_id = $row2['currency_id'];
        $sq_from = mysqli_fetch_assoc(mysqlQuery("select currency_rate from roe_master where currency_id='$currency_id'"));
        $from_currency_rate = $sq_from['currency_rate'];

        $resultc = mysqlQuery("SELECT * FROM b2b_transfer_tariff_entries where tariff_id = '$row2[tariff_id]' and to_date>='$today_date'");
        while($rowc = mysqli_fetch_array($resultc)) {

            $tariff_data = json_decode($rowc['tariff_data']);
            // Pickup
            if($rowc['pickup_type'] == 'city'){
                $rowct = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$rowc[pickup_location]'"));
                $pickup = $rowct['city_name'];
            }
            else if($rowc['pickup_type'] == 'hotel'){
                $rowct = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$rowc[pickup_location]'"));
                $pickup = $rowct['hotel_name'];
            }
            else{
                $rowct = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$rowc[pickup_location]'"));
                $airport_nam = clean($rowct['airport_name']);
                $airport_code = clean($rowct['airport_code']);
                $pickup = $airport_nam." (".$airport_code.")";
            }
            //Drop-off
            if($rowc['drop_type'] == 'city'){
                $rowct = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$rowc[drop_location]'"));
                $drop = $rowct['city_name'];
            }
            else if($rowc['drop_type'] == 'hotel'){
                $rowct = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$rowc[drop_location]'"));
                $drop = $rowct['hotel_name'];
            }
            else{
                $rowct = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$rowc[drop_location]'"));
                $airport_nam = clean($rowct['airport_name']);
                $airport_code = clean($rowct['airport_code']);
                $drop = $airport_nam." (".$airport_code.")";
            }
            $total_cost = ($to_currency_rate!=0) ? ($from_currency_rate / $to_currency_rate) * $tariff_data[0]->total_cost : 0;
            $temp_array = array(
                'pickup_type' => $rowc['pickup_type'],
                'drop_type' => $rowc['drop_type'],
                'pickup_location' => $pickup,
                'drop_location' => $drop,
                'from_date' => $rowc['from_date'],
                'to_date' => $rowc['to_date'],
                'service_duration' => $rowc['service_duration'],
                'luggage_capacity' => $tariff_data[0]->seating_capacity,
                'total_cost'=>$total_cost
            );
            array_push($tr_costing_array,$temp_array);
        }
        array_push($costing_array,array('currency'=>$currency_id,'tr_costing_array'=>$tr_costing_array));
    }

    $sq_app = mysqli_fetch_assoc(mysqlQuery("SELECT transfer_service_time FROM app_settings where setting_id='1'"));
    $temp_array = array(
        'transfer_id' => $row['entry_id'],
        'vehicle_type' => $row['vehicle_type'],
        'vehicle_name' => $row['vehicle_name'],
        'seating_capacity' => $row['seating_capacity'],
        'image_url' => $row['image_url'],
        'cancellation_policy'=>$row['cancellation_policy'],
        'service_timing'=>$sq_app['transfer_service_time'],
        'costing_array' => json_encode($costing_array)
    );
    array_push($transfer_data,$temp_array);
}
//Visa
$visa_data = array();
$sq_visa = mysqlQuery("SELECT * FROM visa_crm_master where 1");
while($row_visa = mysqli_fetch_assoc($sq_visa)){

    $temp_array = array(
        'entry_id' => $row_visa['entry_id'],
        'country' => $row_visa['country_id'],
        'visa_type' => $row_visa['visa_type'],
        'basic_amount' => $row_visa['fees'],
        'markup_amount' => $row_visa['markup'],
        'total_days'=>$row_visa['time_taken'],
        'form_1'=>$row_visa['upload_url'],
        'form_2'=>$row_visa['upload_url2'],
        'documents' => $row_visa['list_of_documents']
    );
    array_push($visa_data,$temp_array);
}
//Terms&Conditions
$terms_conditions_data = array();
$sq_termsp = mysqli_fetch_assoc(mysqlQuery("SELECT terms_and_conditions FROM terms_and_conditions where type ='Package Quotation' and active_flag='Active'"));
$sq_termsg = mysqli_fetch_assoc(mysqlQuery("SELECT terms_and_conditions FROM terms_and_conditions where type ='Group Quotation' and active_flag='Active'"));
$sq_termsc = mysqli_fetch_assoc(mysqlQuery("SELECT terms_and_conditions FROM terms_and_conditions where type ='Car Rental Quotation' and active_flag='Active'"));
$sq_termsf = mysqli_fetch_assoc(mysqlQuery("SELECT terms_and_conditions FROM terms_and_conditions where type ='Flight Quotation' and active_flag='Active'"));
$sq_termsh = mysqli_fetch_assoc(mysqlQuery("SELECT terms_and_conditions FROM terms_and_conditions where type ='Hotel Quotation' and active_flag='Active'"));

$temp_array = array(
    'package_quotation' => $sq_termsp['terms_and_conditions'],
    'group_quotation' => $sq_termsg['terms_and_conditions'],
    'car_quotation' => $sq_termsc['terms_and_conditions'],
    'flight_quotation' => $sq_termsf['terms_and_conditions'],
    'hotel_quotation' => $sq_termsh['terms_and_conditions']
);
array_push($terms_conditions_data,$temp_array);

//B2C settings
$cms_data = array();
$sq_cms = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM b2c_settings where 1"));
$temp_array = array(
    'banner_images' => $sq_cms['banner_images'],
    'popular_dest' => $sq_cms['popular_dest'],
    'popular_tours' => $sq_cms['popular_tours'],
    'popular_hotels' => $sq_cms['popular_hotels'],
    'popular_activities' => $sq_cms['popular_activities'],
    'git_tours' => $sq_cms['git_tours'],
    'fit_tours' => $sq_cms['fit_tours'],
    'footer_holidays' => $sq_cms['footer_holidays'],
    'cancellation_policy' => $sq_cms['cancellation_policy'],
    'refund_policy' => $sq_cms['refund_policy'],
    'privacy_policy' => $sq_cms['privacy_policy'],
    'terms_of_use' => $sq_cms['terms_of_use'],
    'social_media' => $sq_cms['social_media'],
    'header_strip_note' => $sq_cms['header_strip_note'],
    'book_enquiry_button' => $sq_cms['book_enquiry_button'],
    'gallery' => $sq_cms['gallery'],
    'google_map_script' => $sq_cms['google_map_script'],
    'google_analytics' => $sq_cms['google_analytics'],
    'tidio_chat' => $sq_cms['tidio_chat'],
    'coupon_codes' => $sq_cms['coupon_codes'],
    'assoc_logos' => $sq_cms['assoc_logos'],
);
array_push($cms_data,$temp_array);

//B2C color scheme
$temp_array1 = array();
$sq_cms = mysqlQuery("SELECT * FROM b2c_color_scheme where 1");
while($row_query = mysqli_fetch_assoc($sq_cms)){

    $temp_array1 = array(
        'text_primary_color' => $row_query['text_primary_color'],
        'titletext_secondary_color' => $row_query['text_secondary_color'],
        'button_color' => $row_query['button_color']
    );
}
array_push($cms_data,$temp_array1);

//B2C meta_tags
$sq_cms = mysqlQuery("SELECT * FROM b2c_meta_tags where 1");
$temp_array = array();
while($row_query = mysqli_fetch_assoc($sq_cms)){

    $temp_array1 = array(
        'page' => $row_query['page'],
        'title' => $row_query['title'],
        'description' => $row_query['descriiption'],
        'keywords' => $row_query['keywords']
    );
    array_push($temp_array,$temp_array1);
}
array_push($cms_data,array('b2c_meta_tags'=>$temp_array));

//B2C blogs
$sq_cms = mysqlQuery("SELECT * FROM b2c_blogs where active_flag='0'");
$temp_array = array();
while($row_query = mysqli_fetch_assoc($sq_cms)){

    $temp_array1 = array(
        'entry_id' => $row_query['entry_id'],
        'title' => $row_query['title'],
        'description' => $row_query['description'],
        'image' => $row_query['image']
    );
    array_push($temp_array,$temp_array1);
}
array_push($cms_data,array('b2c_blogs'=>$temp_array));

//B2C meta_tags
$sq_cms = mysqlQuery("SELECT * FROM b2c_testimonials order by entry_id desc");
$temp_array = array();
while($row_query = mysqli_fetch_assoc($sq_cms)){

    $temp_array1 = array(
        'name' => $row_query['name'],
        'designation' => $row_query['designation'],
        'image' => $row_query['image'],
        'testm' => $row_query['testm']
    );
    array_push($temp_array,$temp_array1);
}
array_push($cms_data,array('customer_testimonials'=>$temp_array));


//Gallery
$result1 = mysqlQuery("SELECT * FROM gallary_master where 1");
$gallary_array = array();
while($row1 = mysqli_fetch_array($result1)) {
    $temp_array = array(
        'dest_id' => $row1['dest_id'],
        'image_url' => $row1['image_url'],
        'description' => addslashes($row1['description'])
    );
    array_push($gallary_array,$temp_array);
}

//City
$result1 = mysqlQuery("SELECT city_id,city_name FROM city_master where active_flag='Active'");
$cities = array();
while($row1 = mysqli_fetch_array($result1)) {
    $temp_array = array(
        'city_id' => $row1['city_id'],
        'city_name' => $row1['city_name']
    );
    array_push($cities,$temp_array);
}
//Destination
$result1 = mysqlQuery("SELECT dest_id,dest_name FROM destination_master where status='Active'");
$destination = array();
while($row1 = mysqli_fetch_array($result1)) {
    $temp_array = array(
        'dest_id' => $row1['dest_id'],
        'dest_name' => $row1['dest_name']
    );
    array_push($destination,$temp_array);
}

array_push($final_array,array('package_tour_data'=>$package_tour_data,'company_profile_data'=>$company_profile_data,'group_tour_data'=>$group_tour_data,'hotels_data'=>$hotels_data,'activity_data'=>$activity_data,'transfer_data'=>$transfer_data,'visa_data'=>$visa_data,'terms_conditions_data'=>$terms_conditions_data,'cms_data'=>$cms_data,'gallary_array'=>$gallary_array,'cities'=>$cities,'destination'=>$destination));
// store query result in b2c_cache.php
$path = getcwd();
$path = explode('model',$path);
$res = json_encode($final_array);
file_put_contents($path[0].'view/b2c_cache.php',$res);
?>
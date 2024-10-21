<?php
//Generic Files
include "../../../../model.php";
include "printFunction.php";
global $app_quot_img, $similar_text, $quot_note, $currency,$tcs_note,$app_quot_format;

$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select * from branch_assign where link='package_booking/quotation/home/index.php'"));
$branch_status = $sq['branch_status'];
if ($branch_admin_id != 0) {
  $branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$branch_admin_id'"));
  $sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
  $sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
} else {
  $branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='1'"));
  $sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='1' and active_flag='Active'"));
  $sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='1' and active_flag='Active'"));
}

$quotation_id = $_GET['quotation_id'];

$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$quotation_id'"));
$tcs_note_show = ($sq_quotation['booking_type'] != 'Domestic') ? $tcs_note : '';
$sq_package_name = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id = '$sq_quotation[package_id]'"));

$sq_dest = mysqli_fetch_assoc(mysqlQuery("select link from video_itinerary_master where dest_id = '$sq_package_name[dest_id]'"));

$sq_terms_cond_count = mysqli_num_rows(mysqlQuery("select dest_id from terms_and_conditions where type='Package Quotation' and dest_id='$sq_package_name[dest_id]' and active_flag ='Active'"));
$dest_id = ($sq_terms_cond_count != 0) ? $sq_package_name['dest_id'] : 0;
$sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Package Quotation' and dest_id='$dest_id' and active_flag ='Active'"));

$sq_transport = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_transport_entries2 where quotation_id='$quotation_id'"));
$sq_costing = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id='$quotation_id'"));
$sq_package_program = mysqlQuery("select * from  package_quotation_program where quotation_id='$quotation_id'");

$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where id='$sq_quotation[login_id]'"));
$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_login[emp_id]'"));

$quotation_date = $sq_quotation['quotation_date'];
$yr = explode("-", $quotation_date);
$year = $yr[0];
if ($sq_emp_info['first_name'] == '') {
    $emp_name = 'Admin';
} else {
    $emp_name = $sq_emp_info['first_name'] . ' ' . $sq_emp_info['last_name'];
}

$basic_cost = $sq_costing['basic_amount'];
$service_charge = $sq_costing['service_charge'];
$tour_cost = $basic_cost + $service_charge;
$service_tax_amount = 0;
$tax_show = '';
$bsmValues = json_decode($sq_costing['bsmValues']);

// Count queries
$sq_package_count = mysqli_num_rows(mysqlQuery("select * from  package_quotation_program where quotation_id='$quotation_id'"));
$sq_train_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_train_entries where quotation_id='$quotation_id'"));
$sq_plane_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_plane_entries where quotation_id='$quotation_id'"));
$sq_cruise_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_cruise_entries where quotation_id='$quotation_id'"));
$sq_hotel_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_hotel_entries where quotation_id='$quotation_id'"));
$sq_transport_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_transport_entries2 where quotation_id='$quotation_id'"));
$sq_exc_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_excursion_entries where quotation_id='$quotation_id'"));
$discount1 = currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['discount']);
if ($sq_quotation['discount'] != 0) {
    $discount = ' (Applied Discount : ' . $discount1 . ')';
} else {
    $discount = '';
}
?>
<style>
    .package_costing table tr:nth-child(even) {
        background-color: #efefef !important;
    }
</style>
<!-- landingPage -->
<section class="landingSec main_block">
    <div class="landingPageTop main_block">
        <img class="land_img" src="<?= getFormatImg($app_quot_format,$sq_package_name['dest_id']) ?>" class="img-responsive">
        <h1 class="landingpageTitle"><?= $sq_package_name['package_name'] ?>
            <em><?= '(' . $sq_package_name['package_code'] . ')' ?></em>
        </h1>
        <span class="landingPageId"><?= get_quotation_id($quotation_id, $year) ?>
        <br><?= get_date_user($sq_quotation['from_date']) . ' To ' . get_date_user($sq_quotation['to_date']) ?></span>
    </div>
    <div class="col-md-12 text-right">

        <div class="ladingPageBottom main_block side_pad">

            <div class="row">
                <div class="col-md-5 text-left">
                    <div class="landigPageCustomer mg_tp_20">
                        <h3 class="customerFrom">PREPARED FOR</h3>
                        <span class="customerName mg_tp_10"><i class="fa fa-user"></i> :
                            <?= $sq_quotation['customer_name'] ?></span><br>
                        <span class="customerMail mg_tp_10"><i class="fa fa-envelope"></i> :
                            <?= $sq_quotation['email_id'] ?></span><br>
                        <span class="customerMobile mg_tp_10"><i class="fa fa-phone"></i> :
                            <?= $sq_quotation['mobile_no'] ?></span><br>
                        <span class="generatorName mg_tp_10">PREPARED BY <?= $emp_name ?></span><br>
                    </div>
                </div>
                <div class="col-md-7 ">
                    <div class="flx" style="display: flex;justify-content: flex-end; width:100%;">
                        <div class="detailBlock text-center">
                            <div class="detailBlockIcon detailBlockBlue">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <div class="detailBlockContent">
                                <h3 class="contentValue"><?= get_date_user($sq_quotation['quotation_date']) ?></h3>
                                <span class="contentLabel">QUOTATION DATE</span>
                            </div>
                        </div>

                        <div class="detailBlock text-center">
                            <div class="detailBlockIcon detailBlockGreen">
                                <i class="fa fa-hourglass-half"></i>
                            </div>
                            <div class="detailBlockContent">
                                <h3 class="contentValue">
                                    <?php echo $sq_quotation['total_days'] . 'N/' . ($sq_quotation['total_days']+1) . 'D' ?>
                                </h3>
                                <span class="contentLabel">DURATION</span>
                            </div>
                        </div>

                        <div class="detailBlock text-center">
                            <div class="detailBlockIcon detailBlockYellow">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="detailBlockContent">
                                <h3 class="contentValue"><?= $sq_quotation['total_passangers'] ?></h3>
                                <span class="contentLabel">TOTAL GUEST(s)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        </div>

    </div>
    </div>

    </div>
</section>

<!-- traveling Information -->
<?php if ($sq_hotel_count != 0 || $sq_plane_count != 0 || $sq_transport_count != 0) { ?>
    <section class="travelingDetails main_block mg_tp_30">
        <!-- Hotel -->
        <?php
        if ($sq_hotel_count != 0) {

            $sq_package_type = mysqlQuery("select DISTINCT(package_type) from package_tour_quotation_hotel_entries where quotation_id='$quotation_id' order by package_type");
            while ($row_hotel1 = mysqli_fetch_assoc($sq_package_type)) {

                $sq_package_type1 = mysqlQuery("select * from package_tour_quotation_hotel_entries where quotation_id='$quotation_id' and package_type='$row_hotel1[package_type]' order by package_type");
        ?>
                <section class="transportDetails main_block side_pad mg_tp_30">
                    <h6 class="text-center no-pad">PACKAGE TYPE - <?= $row_hotel1['package_type'] ?></h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="transportImg">
                                <img src="<?= BASE_URL ?>images/quotation/hotel.png" class="img-responsive">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="table-responsive mg_tp_30">
                                <table class="table table-bordered no-marg" id="tbl_emp_list">
                                    <thead>
                                        <tr class="table-heading-row">
                                            <th>City</th>
                                            <th>Hotel Name</th>
                                            <th>Check_IN</th>
                                            <th>Check_OUT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row_hotel = mysqli_fetch_assoc($sq_package_type1)) {

                                            $hotel_name = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$row_hotel[hotel_name]'"));
                                            $city_name = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_hotel[city_name]'"));
                                        ?>
                                            <tr>
                                                <?php
                                                $sq_count_h = mysqli_num_rows(mysqlQuery("select * from hotel_vendor_images_entries where hotel_id='$row_hotel[hotel_name]' "));
                                                if ($sq_count_h == 0) {
                                                    $download_url =  BASE_URL . 'images/dummy-image.jpg';
                                                } else {
                                                    $sq_hotel_image = mysqlQuery("select * from hotel_vendor_images_entries where hotel_id = '$row_hotel[hotel_name]'");
                                                    while ($row_hotel_image = mysqli_fetch_assoc($sq_hotel_image)) {
                                                        $image = $row_hotel_image['hotel_pic_url'];
                                                        $newUrl = preg_replace('/(\/+)/', '/', $image);
                                                        $newUrl = explode('uploads', $newUrl);
                                                        $download_url = BASE_URL . 'uploads' . $newUrl[1];
                                                    }
                                                }
                                                ?>
                                                <td><?php echo $city_name['city_name']; ?></td>
                                                <td><?php echo $hotel_name['hotel_name'] . $similar_text; ?></td>
                                                <td><?= get_date_user($row_hotel['check_in']) ?></td>
                                                <td><?= get_date_user($row_hotel['check_out']) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </section>
        <?php
            }
        } ?>
        <?php
        if ($sq_plane_count > 0) { ?>
            <!-- flight -->
            <section class="transportDetails main_block side_pad mg_tp_30">
                <div class="row">
                    <div class="col-md-3">
                        <div class="transportImg">
                            <img src="<?= BASE_URL ?>images/quotation/flight.png" class="img-responsive">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="table-responsive mg_tp_30">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>From_Sector</th>
                                        <th>To_Sector</th>
                                        <th>Airline</th>
                                        <th>Class</th>
                                        <th>Departure_D/T</th>
                                        <th>Arrival_D/T</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sq_plane = mysqlQuery("select * from package_tour_quotation_plane_entries where quotation_id='$quotation_id'");
                                    while ($row_plane = mysqli_fetch_assoc($sq_plane)) {
                                        $sq_airline = mysqli_fetch_assoc(mysqlQuery("select * from airline_master where airline_id='$row_plane[airline_name]'"));
                                        $airline = ($row_plane['airline_name'] != '') ? $sq_airline['airline_name'] . ' (' . $sq_airline['airline_code'] . ')' : 'NA';
                                    ?>
                                        <tr>
                                            <td><?= $row_plane['from_location'] ?></td>
                                            <td><?= $row_plane['to_location'] ?></td>
                                            <td><?= $airline ?></td>
                                            <td><?php echo ($row_plane['class'] != '') ? $row_plane['class'] : 'NA'; ?></td>
                                            <td><?= get_datetime_user($row_plane['dapart_time']) ?></td>
                                            <td><?= get_datetime_user($row_plane['arraval_time']) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        <?php } ?>
        <!-- transport -->
        <?php
        if ($sq_transport_count > 0) { ?>
            <section class="transportDetails main_block side_pad mg_tp_30">
                <div class="row">
                    <div class="col-md-3">
                        <div class="transportImg">
                            <img src="<?= BASE_URL ?>images/quotation/car.png" class="img-responsive">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="table-responsive mg_tp_30">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>VEHICLE</th>
                                        <th>START_DATE</th>
                                        <th>END_DATE</th>
                                        <th>PICKUP</th>
                                        <th>DROP</th>
                                        <th>S_duration</th>
                                        <th>VEHICLES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 0;
                                    $sq_hotel = mysqlQuery("select * from package_tour_quotation_transport_entries2 where quotation_id='$quotation_id'");
                                    while ($row_hotel = mysqli_fetch_assoc($sq_hotel)) {

                                        $transport_name = mysqli_fetch_assoc(mysqlQuery("select * from b2b_transfer_master where entry_id='$row_hotel[vehicle_name]'"));
                                        // Pickup
                                        if ($row_hotel['pickup_type'] == 'city') {
                                            $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_hotel[pickup]'"));
                                            $pickup = $row['city_name'];
                                        } else if ($row_hotel['pickup_type'] == 'hotel') {
                                            $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_hotel[pickup]'"));
                                            $pickup = $row['hotel_name'];
                                        } else {
                                            $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_hotel[pickup]'"));
                                            $airport_nam = clean($row['airport_name']);
                                            $airport_code = clean($row['airport_code']);
                                            $pickup = $airport_nam . " (" . $airport_code . ")";
                                        }
                                        //Drop-off
                                        if ($row_hotel['drop_type'] == 'city') {
                                            $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_hotel[drop]'"));
                                            $drop = $row['city_name'];
                                        } else if ($row_hotel['drop_type'] == 'hotel') {
                                            $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_hotel[drop]'"));
                                            $drop = $row['hotel_name'];
                                        } else {
                                            $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_hotel[drop]'"));
                                            $airport_nam = clean($row['airport_name']);
                                            $airport_code = clean($row['airport_code']);
                                            $drop = $airport_nam . " (" . $airport_code . ")";
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $transport_name['vehicle_name'] . $similar_text ?></td>
                                            <td><?= date('d-m-Y', strtotime($row_hotel['start_date'])) ?></td>
                                            <td><?= date('d-m-Y', strtotime($row_hotel['end_date'])) ?></td>
                                            <td><?= $pickup ?></td>
                                            <td><?= $drop ?></td>
				                            <td><?= $row_hotel['service_duration'] ?></td>
                                            <td><?= $row_hotel['vehicle_count'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <!-- train -->
        <?php if ($sq_train_count > 0) { ?>
            <section class="transportDetails main_block side_pad mg_tp_30">
                <div class="row">
                    <div class="col-md-3">
                        <div class="transportImg">
                            <img src="<?= BASE_URL ?>images/quotation/train.png" class="img-responsive">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="table-responsive mg_tp_30">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>From_location</th>
                                        <th>To_location</th>
                                        <th>Class</th>
                                        <th>Departure_D/T</th>
                                        <th>Arrival_D/T</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sq_train = mysqlQuery("select * from package_tour_quotation_train_entries where quotation_id='$quotation_id'");
                                    while ($row_train = mysqli_fetch_assoc($sq_train)) {
                                    ?>
                                        <tr>
                                            <td><?= $row_train['from_location'] ?></td>
                                            <td><?= $row_train['to_location'] ?></td>
                                            <td><?php echo ($row_train['class'] != '') ? $row_train['class'] : 'NA'; ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($row_train['departure_date'])) ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($row_train['arrival_date'])) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <!-- cruise -->
        <?php
        if ($sq_cruise_count > 0) { ?>
            <section class="transportDetails main_block side_pad mg_tp_30">
                <div class="row">
                    <div class="col-md-3">
                        <div class="transportImg">
                            <img src="<?= BASE_URL ?>images/quotation/cruise.png" class="img-responsive">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="table-responsive mg_tp_30">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>Departure_D/T</th>
                                        <th>Arrival_D/T</th>
                                        <th>Route</th>
                                        <th>Cabin</th>
                                        <th>Sharing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sq_cruise = mysqlQuery("select * from package_tour_quotation_cruise_entries where quotation_id='$quotation_id'");
                                    while ($row_cruise = mysqli_fetch_assoc($sq_cruise)) {
                                    ?>
                                        <tr>
                                            <td><?= date('d-m-Y H:i', strtotime($row_cruise['dept_datetime'])) ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($row_cruise['arrival_datetime'])) ?></td>
                                            <td><?= $row_cruise['route'] ?></td>
                                            <td><?= $row_cruise['cabin'] ?></td>
                                            <td><?= $row_cruise['sharing'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <!-- Excursion -->
        <?php
        if ($sq_exc_count > 0) { ?>
            <section class="transportDetails main_block side_pad mg_tp_30">
                <div class="row">
                    <div class="col-md-3">
                        <div class="transportImg">
                            <img src="<?= BASE_URL ?>images/quotation/excursion.png" class="img-responsive">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="table-responsive mg_tp_30">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>Activity Date/Time</th>
                                        <th>City</th>
                                        <th>Activity</th>
                                        <th>Transfer</th>
                                        <th>Adult</th>
                                        <th>CWB</th>
                                        <th>CWOB</th>
                                        <th>Infant</th>
                                        <th>Vehicle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 0;
                                    $sq_ex = mysqlQuery("select * from package_tour_quotation_excursion_entries where quotation_id='$quotation_id'");
                                    while ($row_ex = mysqli_fetch_assoc($sq_ex)) {
                                        $sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_ex[city_name]'"));
                                        $sq_ex_name = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master_tariff where entry_id='$row_ex[excursion_name]'"));
                                    ?>
                                        <tr>
                                            <td><?= get_datetime_user($row_ex['exc_date']) ?></td>
                                            <td><?= $sq_city['city_name'] ?></td>
                                            <td><?= $sq_ex_name['excursion_name'] ?></td>
                                            <td><?= $row_ex['transfer_option'] ?></td>
                                            <td><?= $row_ex['adult'] ?></td>
                                            <td><?= $row_ex['chwb'] ?></td>
                                            <td><?= $row_ex['chwob'] ?></td>
                                            <td><?= $row_ex['infant'] ?></td>
                                            <td><?= $row_ex['vehicles'] ?></td>
                                        </tr>
                                    <?php }  ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        <?php } ?>
    </section>
<?php } ?>
<!-- Itinerary -->
<?php if ($sq_package_count != 0) { ?>
    <section class="itinerarySec main_block side_pad mg_tp_30">
        <div class="vitinerary_div">
            <h6>Destination Guide Video</h6>
            <img src="<?php echo BASE_URL . 'images/quotation/youtube-icon.png'; ?>" class="itinerary-img img-responsive"><br />
            <a href="<?= $sq_dest['link'] ?>" class="no-marg" target="_blank"></a>
        </div>
        <ul class="print_itinenary main_block no-pad no-marg">
            <?php
            $count = 1;
            $i = 0;
            $dates = (array) get_dates_for_package_itineary($_GET['quotation_id']);
            while($row_itinarary = mysqli_fetch_assoc($sq_package_program)) {

                $date_format = isset($dates[$i]) ? $dates[$i] : 'NA';
                $daywise_image = 'http://itourscloud.com/quotation_format_images/dummy-image.jpg';
                $sq_day_image = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_images where quotation_id='$row_itinarary[quotation_id]' "));
                    $day_url1 = explode(',', $sq_day_image['image_url']);
                    for ($count1 = 0; $count1 < sizeof($day_url1); $count1++) {
                        $day_url2 = isset($day_url1[$count1]) ? explode('=', $day_url1[$count1]) : [];
                        if (isset($day_url2[1]) && $day_url2[1] == $row_itinarary['day_count'] && isset($day_url2[0]) && $day_url2[0] == $row_itinarary['package_id']) {
                            $daywise_image = $day_url2[2];
                        }
                }
                if ($count % 2 != 0) {
                    ?>
                    <li class="singleItinenrary leftItinerary col-md-12 no-pad">
                        <div class="itneraryContent col-md-11 no-pad text-right mg_tp_20 mg_bt_20">
                            <div class="itneraryImg col-md-4">
                                <img src="<?= $daywise_image ?>" class="img-responsive" style="border:6px solid #eaeaea;">
                                <div class="dayCount" style="position: static; top: unset; left: unset; margin: 0; text-align: left; padding-left: 15px; margin-top:5px;">
                                    <span>Day-<?= $count . '(' . $date_format . ')' ?> </span>
                                    <span style="margin-right:5px;"><i class="fa fa-bed"></i> </span><?= $row_itinarary['stay'] ?>
                                    <span style="text-align: right; margin-right:5px;"><i class="fa fa-cutlery"></i>
                                    </span><?= $row_itinarary['meal_plan'] ?>
                                </div>
                            </div>
                            <div class="itneraryText col-md-8">
                                <h5 class="specialAttraction no-marg" style="text-align: left;"><?= $row_itinarary['attraction'] ?>
                                </h5>
                                <p style="text-align: left;"><?= $row_itinarary['day_wise_program'] ?></p>
                            </div>
                        </div>
                        <!-- <div class="col-md-1 no-pad"></div> -->
                    </li>

                <?php } else { ?>

                    <li class="singleItinenrary leftItinerary col-md-12 no-pad">
                        <div class="itneraryContent col-md-11 no-pad text-right mg_tp_20 mg_bt_20">
                            <div class="itneraryImg col-md-4">
                                <!--<div style="width: 100%;">-->
                                <img src="<?= $daywise_image ?>" class="img-responsive" style="border:6px solid #eaeaea;">
                                <!--</div>-->
                                <div class="dayCount" style="position: static; top: unset; left: unset; margin: 0; text-align: left; padding-left: 15px; margin-top:5px;">
                                    <span>Day-<?= $count . '(' . $date_format . ')' ?> </span>

                                    <span style="margin-right:5px;"><i class="fa fa-bed"></i> </span><?= $row_itinarary['stay'] ?>
                                    <span style="text-align: right; margin-right:5px;"><i class="fa fa-cutlery"></i>
                                    </span><?= $row_itinarary['meal_plan'] ?>

                                </div>

                            </div>
                            <div class="itneraryText col-md-8">
                                <h5 class="specialAttraction no-marg" style="text-align: left;"><?= $row_itinarary['attraction'] ?>
                                </h5>
                                <p style="text-align: left;"><?= $row_itinarary['day_wise_program'] ?></p>
                            </div>
                        </div>
                        <!-- <div class="col-md-1 no-pad"></div> -->
                    </li>

            <?php }
                $count++;
                $i++;
            } ?>
        </ul>
    </section>
<?php } ?>

<!-- Inclusion -->
<?php if ($sq_quotation['inclusions'] != '' && $sq_quotation['inclusions'] != ' ' && $sq_quotation['inclusions'] != '<div><br></div>') { ?>
    <section class="pageSection main_block">
        <!-- background Image -->
        <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">

        <section class="incluExcluTerms pageSectionInner main_block mg_tp_10">

            <!-- Inclusion -->
            <div class="row">
                <?php if ($sq_quotation['inclusions'] != '' && $sq_quotation['inclusions'] != ' ' && $sq_quotation['inclusions'] != '<div><br></div>') { ?>
                    <div class="col-md-12 mg_bt_10">
                        <div class="incluExcluTermsTabPanel inclusions main_block">
                            <h3 class="lgTitle" style="margin-left:20px!important;">INCLUSIONS</h3>
                            <div class="tabContent">
                                <pre class="real_text" style="margin-left:20px!important;"><?= $sq_quotation['inclusions'] ?></pre>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
    </section>
<?php } ?>

<!-- Exclusion -->
<?php if ($sq_quotation['exclusions'] != '' && $sq_quotation['exclusions'] != ' ' && $sq_quotation['exclusions'] != '<div><br></div>') { ?>
    <section class="pageSection main_block">
        <!-- background Image -->
        <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">

        <section class="incluExcluTerms pageSectionInner main_block mg_tp_10">

            <!-- Exclusion -->
            <div class="row">
                <?php if ($sq_quotation['exclusions'] != '' && $sq_quotation['exclusions'] != ' ' && $sq_quotation['exclusions'] != '<div><br></div>') { ?>
                    <div class="col-md-12 mg_bt_10">
                        <div class="incluExcluTermsTabPanel exclusions main_block">
                            <h3 class="lgTitle" style="margin-left:20px!important;">EXCLUSIONS</h3>
                            <div class="tabContent">
                                <pre class="real_text" style="margin-left:20px!important;"><?= $sq_quotation['exclusions'] ?></pre>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

        </section>
    </section>
<?php } ?>

<!-- Terms/Note -->
<?php if (isset($sq_terms_cond['terms_and_conditions'])) { ?>
    <section class="pageSection main_block">
        <!-- background Image -->
        <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">

        <section class="incluExcluTerms pageSectionInner main_block mg_tp_30">

            <!-- Terms and Conditions -->
            <?php if(isset($sq_terms_cond['terms_and_conditions'])){ ?>
                <div class="row">
                    <div class="col-md-12 mg_bt_30">
                        <div class="incluExcluTermsTabPanel inclusions main_block">
                            <h3 class="lgTitle" style="margin-left:20px!important;">TERMS AND CONDITIONS</h3>
                            <div class="tabContent">
                                <pre class="real_text" style="margin-left:20px!important;"><?= $sq_terms_cond['terms_and_conditions'] ?></pre>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </section>
    </section>
<?php } ?>

<!-- Terms/Note -->
<?php if ($sq_package_name['note'] != '' || $quot_note != '') { ?>
    <section class="pageSection main_block">
        <!-- background Image -->
        <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">

        <section class="incluExcluTerms pageSectionInner main_block mg_tp_30">

            <!-- Note -->
            <div class="row">
                <?php if($sq_package_name['note'] != ''){ ?>
                    <div class="col-md-12 mg_tp_30 mg_bt_30">
                        <div class="incluExcluTermsTabPanel exclusions main_block">
                            <h3 class="lgTitle" style="margin-left:20px!important;">NOTE</h3>
                            <div class="tabContent">
                                <pre class="real_text" style="margin-left:20px!important;"><?= $sq_package_name['note'] ?></pre>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if($quot_note != ''){ ?>
                <div class="row">
                    <div class="col-md-12">
                        <pre class="real_text" style="margin-left:20px!important;"><?php echo $quot_note; ?></pre>
                    </div>
                </div>
            <?php } ?>
        </section>
    </section>
<?php } ?>


<!-- Ending Page -->
<section class="pageSection main_block">
    <!-- background Image -->
    <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">
    <section class="incluExcluTerms pageSectionInner main_block mg_tp_20">

        <!-- Guest Detail -->
        <div class="guestDetail main_block text-center">
            <h3 class="costBankTitle">TOTAL GUEST (s)</h3>
            <img src="<?= BASE_URL ?>images/quotation/guestCount.png" class="img-responsive">
            <span class="guestCount adultCount">Adult : <?= $sq_quotation['total_adult'] ?></span>
            <span class="guestCount infantCount">CWB / CWOB :
                <?= ($sq_quotation['children_with_bed'] + $sq_quotation['children_without_bed']) ?></span>
            <span class="guestCount infantCount">Infant : <?= $sq_quotation['total_infant'] ?></span>
        </div>
        <!-- Bank Detail -->
        <div class="costBankSec main_block mg_tp_20 costing_bank_details_bk">
            <div class="costBankInner main_block side_pad mg_tp_20 mg_bt_20">
                <div class="row mg_tp_10">
                    <div class="col-md-12">
                        <h3 class="costBankTitle text-center">BANK DETAILS</h3>
                        <div class="row mg_bt_20">
                            <div class="col-md-4 text-center">
                                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/bankName.png" class="img-responsive"></div>
                                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting ?></h4>
                                <p>BANK NAME</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/branchName.png" class="img-responsive"></div>
                                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['branch_name'] != '') ? $sq_bank_branch['branch_name'] : $bank_branch_name ?> </h4>
                                <p>BRANCH</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/accName.png" class="img-responsive"></div>
                                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['account_type'] != '') ? $sq_bank_branch['account_type'] : $acc_name ?></h4>
                                <p>A/C TYPE</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/accNumber.png" class="img-responsive"></div>
                                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['account_no'] != '') ? $sq_bank_branch['account_no'] : $bank_acc_no  ?></h4>
                                <p>A/C NO</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/code.png" class="img-responsive"></div>
                                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['account_name'] != '') ? $sq_bank_branch['account_name'] : $bank_account_name ?></h4>
                                <p>BANK ACCOUNT NAME</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/code.png" class="img-responsive"></div>
                                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['swift_code'] != '') ? strtoupper($sq_bank_branch['swift_code']) :  strtoupper($bank_swift_code) ?></h4>
                                <p>SWIFT CODE</p>
                            </div>
                            <?php
                            if (check_qr()) { ?>
                                <div class="col-md-12 text-center" style="margin-top:20px; margin-bottom:20px;">
                                    <?= get_qr('Landscape Standard') ?>
                                    <br>
                                    <h4 class="no-marg">Scan & Pay </h4>
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
<section class="pageSection main_block">
    <!-- background Image -->
    <!-- <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg"> -->
    <section class="incluExcluTerms main_block costing_bank_details_bk">
        <!-- Costing & Bank Detail -->
        <div class="costBankSec main_block mg_tp_30">
            <div class="costBankInner main_block side_pad mg_tp_20 mg_bt_20">
                <!-- Group Costing -->
                <?php
                if ($sq_quotation['costing_type'] == 1) {
                ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="costBankTitle text-center no-pad">COSTING DETAILS</h3>
                            <h5 class="costBankTitle text-center"><?= $discount ?></h5>
                            <div class="row mg_tp_30">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered no-marg" style="width:100% !important;" id="tbl_emp_list">
                                            <thead>
                                                <tr class="table-heading-row">
                                                    <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">
                                                        Package Type</th>
                                                    <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">
                                                        Tour Cost</th>
                                                    <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">
                                                        Tax</th>
                                                        <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">
                                                        Tcs</th>
                                                    <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">
                                                        TRAVEL/OTHER</th>
                                                    <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">
                                                        Total Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sq_costing1 = mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id='$quotation_id' order by package_type");
                                                while ($sq_costing = mysqli_fetch_assoc($sq_costing1)) {
                                                    $basic_cost = $sq_costing['basic_amount'];
                                                    $service_charge = $sq_costing['service_charge'];
                                                    $service_tax_amount = 0;
                                                    $tax_show = '';
                                                    $bsmValues = json_decode($sq_costing['bsmValues'], true);
                                                    $discount_in = $sq_costing['discount_in'];
                                                    $discount = $sq_costing['discount'];
                                                    if($discount_in == 'Percentage'){
                                                      $act_discount = floatval($service_charge) * floatval($discount) / 100;
                                                    }else{
                                                        $act_discount = ($service_charge != 0) ? $discount : 0;
                                                    }
                                                    $service_charge = $service_charge - floatval($act_discount);
                                                    $tour_cost = $basic_cost + $service_charge;

                                                    $name = '';
                                                    if ($sq_costing['service_tax_subtotal'] !== 0.00 && ($sq_costing['service_tax_subtotal']) !== '') {
                                                        $service_tax_subtotal1 = explode(',', $sq_costing['service_tax_subtotal']);
                                                        for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
                                                            $service_tax = explode(':', $service_tax_subtotal1[$i]);
                                                            $service_tax_amount = floatval($service_tax_amount) + floatval($service_tax[2]);
                                                            $name .= $service_tax[0] . $service_tax[1] . ', ';
                                                        }
                                                    }

                                                    if(isset($bsmValues[0]['tcsper']) && $bsmValues[0]['tcsper']!='NaN')
                                                    {
                                                        $tcsper=$bsmValues[0]['tcsper'];
                                                        $tcsvalue=$bsmValues[0]['tcsvalue'];
                                                    }
                                                    else
                                                    {
                                                        $tcsper=0;
                                                        $tcsvalue=0;
                                                    }
                                                    $tcs_amount_show = currency_conversion($currency, $sq_quotation['currency_code'], $tcsvalue);

                                                    $service_tax_amount_show = currency_conversion($currency, $sq_quotation['currency_code'], $service_tax_amount);
                                                    $quotation_cost = $basic_cost + $service_charge + $service_tax_amount + $sq_quotation['train_cost'] + $sq_quotation['cruise_cost'] + $sq_quotation['flight_cost'] + $sq_quotation['visa_cost'] + $sq_quotation['guide_cost'] + $sq_quotation['misc_cost']+$tcsvalue;
                                                    $quotation_cost = ceil($quotation_cost);
                                                    ////////////////Currency conversion ////////////
                                                    $currency_amount1 = currency_conversion($currency, $sq_quotation['currency_code'], $quotation_cost);
                                                    $act_tour_cost = floatval($quotation_cost) - floatval($service_charge) + floatval($sq_costing['service_charge']);
                                                    $act_tour_cost = ceil($act_tour_cost);
                                                    $act_tour_cost_camount = ($discount!=0) ? currency_conversion($currency, $sq_quotation['currency_code'], $act_tour_cost) : '';

                                                    $newBasic = currency_conversion($currency, $sq_quotation['currency_code'], $tour_cost);
                                                    $travel_cost = floatval($sq_quotation['train_cost']) + floatval($sq_quotation['flight_cost']) + floatval($sq_quotation['cruise_cost']) + floatval($sq_quotation['visa_cost']) + floatval($sq_quotation['guide_cost']) + floatval($sq_quotation['misc_cost']);
                                                    $travel_cost = currency_conversion($currency, $sq_quotation['currency_code'], $travel_cost);
                                                ?>
                                                    <tr>
                                                        <td style="font-size: 14px !important; padding: 8px  20px !important;">
                                                            <?php echo $sq_costing['package_type'] ?></td>
                                                        <td style="font-size: 14px !important; padding: 8px  20px !important;">
                                                            <?= $newBasic ?></td>
                                                        <td style="font-size: 14px !important; padding: 8px  20px !important;">
                                                            <?= str_replace(',', '', $name) . $service_tax_amount_show ?></td>
                                                            <td style="font-size: 14px !important; padding: 8px  20px !important;">Tcs:(<?=$tcsper?>%)<br><?=$tcs_amount_show?></td>
                                                        <td style="font-size: 14px !important; padding: 8px  20px !important;">
                                                            <?= $travel_cost ?></td>
                                                        <td style="font-size: 14px !important; padding: 8px  20px !important;">
                                                            <?= $currency_amount1.' <s>'.$act_tour_cost_camount.'</s>' ?></td>
                                                    </tr>
                                                <?php
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                } else {
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="costBankTitle text-center no-pad">COSTING DETAILS</h3>
                            <h5 class="costBankTitle text-center"><?= $discount ?></h5>
                            <?php
                            $sq_costing1 = mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id='$quotation_id'  order by package_type");
                            while ($sq_costing = mysqli_fetch_assoc($sq_costing1)) {

                                $service_charge = $sq_costing['service_charge'];
                                $discount_in = $sq_costing['discount_in'];
                                $discount = $sq_costing['discount'];
                                if($discount_in == 'Percentage'){
                                  $act_discount = floatval($service_charge) * floatval($discount) / 100;
                                }else{
                                    $act_discount = ($service_charge != 0) ? $discount : 0;
                                }
                                $service_charge = $service_charge - floatval($act_discount);
                                $total_pax = floatval($sq_quotation['total_adult']) + floatval($sq_quotation['children_with_bed']) + floatval($sq_quotation['children_without_bed']) + floatval($sq_quotation['total_infant']);
                                $per_service_charge = floatval($service_charge) / floatval($total_pax);
                                $o_per_service_charge = floatval($sq_costing['service_charge']) / floatval($total_pax);

                                $adult_cost = ($sq_quotation['total_adult'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['adult_cost'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
                                $child_with = ($sq_quotation['children_with_bed'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['child_with'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
                                $child_without = ($sq_quotation['children_without_bed'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['child_without'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
                                $infant_cost = ($sq_quotation['total_infant'] != '0') ? currency_conversion($currency, $sq_quotation['currency_code'], (floatval($sq_costing['infant_cost'] + floatval($per_service_charge)))) : currency_conversion($currency, $sq_quotation['currency_code'], 0);
                  
                                // Without currency
                                $adult_costw = ($sq_quotation['total_adult'] != '0') ? (floatval($sq_costing['adult_cost'] + floatval($per_service_charge)) * intval($sq_quotation['total_adult'])) : 0;
                                $child_withw = ($sq_quotation['children_with_bed'] != '0') ? (floatval($sq_costing['child_with'] + floatval($per_service_charge)) * intval($sq_quotation['children_with_bed'])) : 0;
                                $child_withoutw = ($sq_quotation['children_without_bed'] != '0') ? (floatval($sq_costing['child_without'] + floatval($per_service_charge)) * intval($sq_quotation['children_without_bed'])) : 0;
                                $infant_costw = ($sq_quotation['total_infant'] != '0') ? (floatval($sq_costing['infant_cost'] + floatval($per_service_charge)) * intval($sq_quotation['total_infant'])) : 0;
                                $o_adult_costw = ($sq_quotation['total_adult'] != '0') ? (floatval($sq_costing['adult_cost'] + floatval($o_per_service_charge)) * intval($sq_quotation['total_adult'])) : 0;
                                $o_child_withw = ($sq_quotation['children_with_bed'] != '0') ? (floatval($sq_costing['child_with'] + floatval($o_per_service_charge)) * intval($sq_quotation['children_with_bed'])) : 0;
                                $o_child_withoutw = ($sq_quotation['children_without_bed'] != '0') ? (floatval($sq_costing['child_without'] + floatval($o_per_service_charge)) * intval($sq_quotation['children_without_bed'])) : 0;
                                $o_infant_costw = ($sq_quotation['total_infant'] != '0') ? (floatval($sq_costing['infant_cost'] + floatval($o_per_service_charge)) * intval($sq_quotation['total_infant'])) : 0;

                                $service_tax_amount = 0;
                                $tax_show = '';
                                $bsmValues = json_decode($sq_costing['bsmValues'],true);
                                $name = '';
                                if ($sq_costing['service_tax_subtotal'] !== 0.00 && ($sq_costing['service_tax_subtotal']) !== '') {
                                    $service_tax_subtotal1 = explode(',', $sq_costing['service_tax_subtotal']);
                                    for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
                                        $service_tax = explode(':', $service_tax_subtotal1[$i]);
                                        $service_tax_amount = floatval($service_tax_amount) + floatval($service_tax[2]);
                                        $name .= $service_tax[0] . $service_tax[1] . ', ';
                                    }
                                }

                                if(isset($bsmValues[0]['tcsper']) && $bsmValues[0]['tcsper']!='NaN')
                                {
                                    $tcsper=$bsmValues[0]['tcsper'];
                                    $tcsvalue=$bsmValues[0]['tcsvalue'];
                                }
                                else
                                {
                                    $tcsper=0;
                                    $tcsvalue=0;
                                }
                                $service_tax_amount_show = currency_conversion($currency, $sq_quotation['currency_code'], $service_tax_amount);

                                $total_child = floatval($sq_quotation['children_with_bed']) + floatval($sq_quotation['children_without_bed']);

                                $quotation_cost = floatval($adult_costw) + floatval($child_withw) + floatval($child_withoutw) + floatval($infant_costw);
                                $o_quotation_cost = floatval($o_adult_costw) + floatval($o_child_withw) + floatval($o_child_withoutw) + floatval($o_infant_costw);

                                $other_cost = $service_tax_amount + $sq_quotation['visa_cost'] + $sq_quotation['guide_cost'] + $sq_quotation['misc_cost'];
                                $travel_cost = ($sq_plane_count > 0) ? $sq_quotation['flight_ccost'] + $sq_quotation['flight_icost'] + $sq_quotation['flight_acost'] : 0;
                                $travel_cost += ($sq_train_count > 0) ? $sq_quotation['train_ccost'] + $sq_quotation['train_icost'] + $sq_quotation['train_acost'] : 0;
                                $travel_cost += ($sq_cruise_count > 0) ?  $sq_quotation['cruise_acost'] + $sq_quotation['cruise_icost'] + $sq_quotation['cruise_ccost'] : 0;

                                $quotation_cost = floatval($quotation_cost) + floatval($travel_cost) + floatval($other_cost) +floatval($tcsvalue);
                                $currency_amount1 = currency_conversion($currency, $sq_quotation['currency_code'], $quotation_cost);
                                $tcs_show1= currency_conversion($currency, $sq_quotation['currency_code'], $tcsvalue);
                                $o_quotation_cost = floatval($o_quotation_cost) + floatval($travel_cost) + floatval($other_cost);
                                $o_quotation_cost = ceil($o_quotation_cost);
                                $act_tour_cost_camount = ($discount!=0) ? currency_conversion($currency, $sq_quotation['currency_code'], $o_quotation_cost) : ''; ?>
                                <div class="row mg_tp_30">
                                    <div class="col-md-12"><?= $sq_costing['package_type'] . ' (' . $currency_amount1 . ' <s>'.$act_tour_cost_camount.'</s>)' ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                                <thead>
                                                    <tr class="table-heading-row">
                                                        <th>ADULT</th>
                                                        <th>CWB</th>
                                                        <th>CWOB</th>
                                                        <th>INFANT</th>
                                                        <th>TAX</th>
                                                        <th>TCS</th>
                                                        <th>Visa</th>
                                                        <th>Guide</th>
                                                        <th>Misc</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><?= $adult_cost ?></td>
                                                        <td><?= $child_with ?></td>
                                                        <td><?= $child_without  ?></td>
                                                        <td><?= $infant_cost ?></td>
                                                        <td><?= str_replace(',', '', $name) . '<b>' . $service_tax_amount_show . '</b>' ?></td>
                                                        <td>Tcs:(<?=$tcsper?>%)<br><?=$tcs_show1?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['visa_cost']) ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['guide_cost'])  ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['misc_cost'])  ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if($sq_plane_count > 0 || $sq_train_count > 0 || $sq_cruise_count > 0){ ?>
                                <div class="row mg_tp_10">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                                <thead>
                                                    <tr class="table-heading-row">
                                                        <th>Travel_Type</th>
                                                        <th>Adult(PP)</th>
                                                        <th>Child(PP)</th>
                                                        <th>Infant(PP)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if($sq_plane_count>0){ ?>
                                                    <tr>
                                                        <td><?= 'Flight' ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_acost'])) ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_ccost'])) ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_icost'])) ?></td>
                                                    </tr>
                                                    <?php }
                                                        if($sq_train_count>0){ ?>
                                                    <tr>
                                                        <td><?= 'Train' ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_acost'])) ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_ccost'])) ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_icost'])) ?></td>
                                                    </tr>
                                                    <?php }
                                                        if($sq_cruise_count>0){ ?>
                                                    <tr>
                                                        <td><?= 'Cruise' ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['cruise_acost'])) ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['cruise_ccost'])) ?></td>
                                                        <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['cruise_icost'])) ?></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            } ?>
                        </div>
                    </div>
                <?php } ?>
                <!-- Per Person Costing End -->
                <?php
                // if ($tcs_note_show != '') { ?>
                <!-- <h5 class="costBankTitle mg_tp_10"> -->
                    <?php // $tcs_note_show ?></h5>
                <?php //} ?>
                <?php
                if ($sq_quotation['other_desc'] != '') { ?>
                    <h5 class="costBankTitle mg_tp_10">MISCELLANEOUS DESCRIPTION: <?= $sq_quotation['other_desc'] ?></h5>
                <?php } ?>
            </div>
        </div>
    </section>
</section>
<section class="pageSection main_block">
    <!-- background Image -->
    <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">
    <section class="incluExcluTerms pageSectionInner costing_bank_details_bk main_block">

        <!-- contact-detail -->
        <section class="contactsec main_block">
            <div class="row">
                <div class="col-md-7">
                    <div class="contactTitlePanel text-center">
                        <!-- <h3>Contact Us</h3> -->
                        <img src="<?= BASE_URL ?>images/quotation/contactImg.jpg" class="img-responsive">
                        <?php if ($app_website != '') { ?><p class="no-marg"><?php echo $app_website; ?></p><?php } ?>
                    </div>
                </div>
                <div class="col-md-5">
                    <?php //if($app_address != ''){
                    ?>
                    <div class="contactBlock main_block side_pad mg_tp_20">
                        <div class="cBlockIcon"> <i class="fa fa-map-marker"></i> </div>
                        <div class="cBlockContent">
                            <h5 class="cTitle">Corporate Office</h5>
                            <p class="cBlockData" style="color: #ffffff !important;">
                                <?php echo ($branch_status == 'yes' && $role != 'Admin') ? $branch_details['address1'] . ',' . $branch_details['address2'] . ',' . $branch_details['city'] : $app_address; ?>
                            </p>
                        </div>
                    </div>
                    <?php //} 
                    ?>
                    <?php //if($app_contact_no != ''){
                    ?>
                    <div class="contactBlock main_block side_pad mg_tp_20">
                        <div class="cBlockIcon"> <i class="fa fa-phone"></i> </div>
                        <div class="cBlockContent">
                            <h5 class="cTitle">Contact</h5>
                            <p class="cBlockData" style="color: #ffffff !important;">
                                <?php echo ($branch_status == 'yes' && $role != 'Admin') ? $branch_details['contact_no']  : $app_contact_no; ?>
                            </p>
                        </div>
                    </div>
                    <?php //} 
                    ?>
                    <?php //if($app_email_id != ''){
                    ?>
                    <div class="contactBlock main_block side_pad mg_tp_20">
                        <div class="cBlockIcon"> <i class="fa fa-envelope"></i> </div>
                        <div class="cBlockContent">
                            <h5 class="cTitle">Email Id</h5>
                            <p class="cBlockData" style="color: #ffffff !important;">
                                <?php echo ($branch_status == 'yes' && $role != 'Admin' && $branch_details['email_id'] != '') ? $branch_details['email_id'] : $app_email_id; ?>
                            </p>

                        </div>
                    </div>
                    <?php //} 
                    ?>

                </div>
            </div>
        </section>
    </section>
</section>

</body>

</html>
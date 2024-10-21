<?php
//Generic Files
include "../../../model.php";
include "../print_functions.php";
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$q = "select * from branch_assign where link='package_booking/booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';

$booking_id = $_GET['booking_id'];
$sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id' and delete_status='0'"));
$branch_admin_id = isset($_SESSION['branch_admin_id']) ? $_SESSION['branch_admin_id'] : $sq_booking['branch_admin_id'];
$branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$branch_admin_id'"));

if (intval($sq_booking['quotation_id']) == 0) {
    $adults = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$booking_id' and status='Active' and adolescence='Adult'"));
    $children = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$booking_id' and status='Active' and adolescence='Children'"));
    $infants = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$booking_id' and status='Active' and adolescence='Infant'"));
} else {
    $sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$sq_booking[quotation_id]'"));
    $adults = $sq_quotation['total_adult'];
    $children = intval($sq_quotation['children_without_bed']) + intval($sq_quotation['children_with_bed']);
    $infants = $sq_quotation['total_infant'];
}

$sq_service_voucher_hotel = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_hotel_service_voucher1 where hotel_accomodation_id='$booking_id'"));
$sq_accomodation1_hotel = mysqlQuery("select * from package_hotel_accomodation_master where booking_id='$booking_id'");
while ($sq_accomodation = mysqli_fetch_assoc($sq_accomodation1_hotel)) {

    $hotel_id = $sq_accomodation['hotel_id'];
    $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$hotel_id'"));
    $mobile_no = $encrypt_decrypt->fnDecrypt($sq_hotel['mobile_no'], $secret_key);
    $email_id = $encrypt_decrypt->fnDecrypt($sq_hotel['email_id'], $secret_key);
    $booking_id = $sq_accomodation['booking_id'];

    $sq_traveler = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));
    if ($sq_traveler['type'] == 'Corporate' || $sq_traveler['type'] == 'B2B') {
        $name = $sq_traveler['company_name'];
    } else {
        $name = $sq_traveler['first_name'] . ' ' . $sq_traveler['last_name'];
    }

    //Total days
    $total_days1 = strtotime($sq_accomodation['to_date']) - strtotime($sq_accomodation['from_date']);
    $total_days = round($total_days1 / 86400);

    if (intval($sq_booking['quotation_id']) == 0) {
        $adults = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$booking_id' and status='Active' and adolescence='Adult'"));
        $children = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$booking_id' and status='Active' and adolescence='Children'"));
        $infants = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$booking_id' and status='Active' and adolescence='Infant'"));
    } else {
        $sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$sq_booking[quotation_id]'"));
        $adults = $sq_quotation['total_adult'];
        $children = intval($sq_quotation['children_without_bed']) + intval($sq_quotation['children_with_bed']);
        $infants = $sq_quotation['total_infant'];
    }
    $emp_id = $_SESSION['emp_id'];
    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$emp_id'"));
    if ($emp_id == '0') {
        $emp_name = 'Admin';
    } else {
        $emp_name = $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
    }
?>

    <!-- header -->
    <div class="hotel_service_voucher">
        <div class="repeat_section main_block">
            <img src="<?= BASE_URL ?>images/vouchers/hotel-service-voucher.jpg" class="watermark ">
            <section class="print_header main_block" style="margin-bottom: 200px !important;">
                <div class="col-md-6 no-pad">
                    <div class="print_header_logo">
                        <img src="<?= $admin_logo_url ?>" class="img-responsive mg_tp_10 set_logo_on_img">
                    </div>
                </div>
                <div class=" col-md-6 no-pad" style="color: #ddd !important; filter: brightness(1000) !important;">
                    <div class="c-w-main print_header_contact text-right" style="color: #ddd !important; filter: brightness(1000) !important;">
                        <span class="c-w-main title"><?php echo $sq_hotel['hotel_name']; ?></span><br>
                        <div class="c-w-main" style="color: #ddd !important; filter: brightness(1000) !important;">
                            <?php echo $sq_hotel['hotel_address']; ?></div>
                        <p class="c-w-main no-marg"><img src="<?= BASE_URL ?>/images/icons/phone-icon.png" alt="" width="15"> <?php echo $mobile_no; ?></p>
                        <p class="c-w-main"><img src="<?= BASE_URL ?>/images/icons/email-icon.png" alt="" width="15">
                            <?php echo $email_id; ?></p>
                    </div>
                </div>
            </section>

            <!-- print-detail -->
            <section class="print_sec main_block">

                <div class="row">
                    <div class="col-md-12">
                        <div class="print_info_block">
                            <ul class="main_block noType">
                                <li class="col-md-3 mg_tp_10 mg_bt_10">
                                    <div class="print_quo_detail_block">
                                        <i class="fa fa-hourglass-half" aria-hidden="true"></i><br>
                                        <span>DURATION</span><br>
                                        <?= ($total_days) . 'N' ?><br>
                                    </div>
                                </li>
                                <li class="col-md-3 mg_tp_10 mg_bt_10">
                                    <div class="print_quo_detail_block">
                                        <i class="fa fa-users" aria-hidden="true"></i><br>
                                        <span>TOTAL GUEST(s)</span><br>
                                        <?= $adults ?> Adult(s), <?= $children ?> Child(ren), <?= $infants ?> Infant(s)<br>
                                    </div>
                                </li>
                                <li class="col-md-3 mg_tp_10 mg_bt_10">
                                    <div class="print_quo_detail_block">
                                        <i class="fa fa-home" aria-hidden="true"></i><br>
                                        <span>TOTAL ROOM(s)</span><br>
                                        <?= $sq_accomodation['rooms'] ?><br>
                                    </div>
                                </li>
                                <li class="col-md-3 mg_tp_10 mg_bt_10">
                                    <div class="print_quo_detail_block">
                                        <i class="fa fa-university" aria-hidden="true"></i><br>
                                        <span>ROOM CATEGORY</span><br>
                                        <?= $sq_accomodation['catagory'] ?><br>
                                    </div>
                                </li>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- BOOKING -->
            <section class="print_sec main_block">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>GUEST NAME</th>
                                        <th>CHECK-IN </th>
                                        <th>CHECK-OUT</th>
                                        <th>MEAL PLAN</th>
                                        <th>Extra Bed</th>
                                        <th>CONTACT</th>
                                        <th>CONFIRMATION ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= $name ?></td>
                                        <td><?= get_datetime_user($sq_accomodation['from_date']) ?></td>
                                        <td><?= get_datetime_user($sq_accomodation['to_date']) ?></td>
                                        <td><?= $sq_accomodation['meal_plan'] ?></td>
                                        <td><?= $sq_accomodation['room_type'] ?></td>
                                        <td><?= $sq_hotel['immergency_contact_no'] ?></td>
                                        <td><?= $sq_accomodation['confirmation_no'] ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Terms and Conditions -->
            <?php
            $sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Package Service Voucher' and active_flag ='Active'"));
            if (isset($sq_terms_cond['terms_and_conditions']) && $sq_terms_cond['terms_and_conditions'] != '') { ?>
                <section class="print_sec main_block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="section_heding">
                                <h2>Terms and Conditions</h2>
                                <div class="section_heding_img">
                                    <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                                </div>
                            </div>
                            <div class="print_text_bolck">
                                <?php
                                echo $sq_terms_cond['terms_and_conditions'];   ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php } ?>

            <!-- ID Proof -->
            <?php
            $sq_traveler_id = mysqli_fetch_assoc(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id'"));
            $id_proof_image = $sq_traveler_id['id_proof_url'];
            if ($id_proof_image != '') {
                $newUrl = preg_replace('/(\/+)/', '/', $id_proof_image);
                $newUrl = explode('uploads', $newUrl);
                $newUrl = BASE_URL . 'uploads' . $newUrl[1];
            ?>
                <section class="print_sec main_block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="section_heding">
                                <h2>ID PROOF</h2>
                                <div class="section_heding_img">
                                    <img src="<?= $newUrl ?>" class="img-responsive">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php } ?>
            <p style="float: left;width: 100%;"><b>Note: Please present this service voucher to service provider
                    (Hotel/Transport) upon arrival</b></p>
        </div>
        <section class="print_sec main_block">
            <div class="row">
                <div class="col-md-7"></div>
                <div class="col-md-5">
                    <div class="print_quotation_creator text-center">
                        <span>Generated BY </span><br><?= $emp_name ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php }
?>

<!-- Activity Voucher -->
<?php
$sq_service_voucher = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_transport_service_voucher where booking_id='$booking_id'"));

$sq_traveler = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));
if ($sq_traveler['type'] == 'Corporate' || $sq_traveler['type'] == 'B2B') {
    $name = $sq_traveler['company_name'];
} else {
    $name = $sq_traveler['first_name'] . ' ' . $sq_traveler['last_name'];
}
$contact_no = $sq_traveler['contact_no'];
$email_id = $sq_traveler['email_id'];

$sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from package_hotel_accomodation_master where booking_id='$booking_id'"));

//Total days
$total_days1 = strtotime($sq_booking['tour_to_date']) - strtotime($sq_booking['tour_from_date']);
$total_days = round($total_days1 / 86400);

$total_pax = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id' and status='Active'"));
if ($sq_booking['quotation_id'] != 0) {
    $sq_package_program = mysqlQuery("select * from package_quotation_program where quotation_id ='$sq_booking[quotation_id]'");
    $sq_package_program_count = mysqli_num_rows(mysqlQuery("select * from package_quotation_program where quotation_id ='$sq_booking[quotation_id]'"));
} else {
    $sq_package_program = mysqlQuery("select * from package_tour_schedule_master where booking_id ='$sq_booking[booking_id]'");
    $sq_package_program_count = mysqli_num_rows(mysqlQuery("select * from package_tour_schedule_master where booking_id ='$sq_booking[booking_id]'"));
}

$emp_id = $_SESSION['emp_id'];
$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$emp_id'"));
if ($emp_id == '0') {
    $emp_name = 'Admin';
} else {
    $emp_name = $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
}
?>
<?php
$sq_count = mysqli_num_rows(mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'"));
if ($sq_count != 0) {
    $sq_service_voucher = mysqli_fetch_assoc(mysqlQuery("select * from excursion_service_voucher where booking_id='$booking_id' and booking_type='package'"));
    $sq_excname = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'"));
    $sq_traveler = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));
    if ($sq_traveler['type'] == 'Corporate' || $sq_traveler['type'] == 'B2B') {
        $name = $sq_traveler['company_name'];
    } else {
        $name = $sq_traveler['first_name'] . ' ' . $sq_traveler['last_name'];
    }

    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$emp_id'"));
    if ($emp_id == '0') {
        $emp_name = 'Admin';
    } else {
        $emp_name = $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
    }
?>

    <div class="activity-service-voucher">
        <section class="repeat_section main_block">
            <img src="<?= BASE_URL ?>images/vouchers/activity-service-voucher.jpg" class="watermark">

            <section class="print_header main_block" style="margin-bottom: 200px !important;">
                <div class="col-md-6 no-pad">
                    <div class="print_header_logo">
                        <img src="<?= $admin_logo_url ?>" class="img-responsive mg_tp_10 set_logo_on_img">
                    </div>
                </div>
                <div class=" col-md-6 no-pad" style="color: #ddd !important; filter: brightness(1000) !important;">
                    <div class="c-w-main print_header_contact text-right" style="color: #ddd !important; filter: brightness(1000) !important;">
                        <span class="c-w-main title"><?php echo $app_name; ?></span><br>
                        <div class="c-w-main" style="color: #ddd !important; filter: brightness(1000) !important;">
                            <?php echo ($branch_status == 'yes') ? $branch_details['address1'] . ',' . $branch_details['address2'] . ',' . $branch_details['city'] : $app_address; ?>
                        </div>
                        <p class="c-w-main no-marg"><img src="<?= BASE_URL ?>/images/icons/phone-icon.png" alt="" width="15">
                            <?php echo ($branch_status == 'yes') ? $branch_details['contact_no'] : $app_contact_no; ?>
                        </p>
                        <p class="c-w-main"><img src="<?= BASE_URL ?>/images/icons/email-icon.png" alt="" width="15">
                            <?php echo ($branch_status == 'yes' && $branch_details['email_id'] != '') ? $branch_details['email_id'] : $app_email_id;; ?>
                        </p>
                    </div>
                </div>
            </section>



            <!-- BOOKING -->
            <section class="print_sec main_block">
                <div class="section_heding">
                    <h2>BOOKING DETAILS</h2>
                    <div class="section_heding_img">
                        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-7 mg_bt_20">
                        <ul class="print_info_list no-pad noType">
                            <li><span>GUEST NAME :</span> <?= $name ?></li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- EXC Detail -->
            <?php
            $sq_count = mysqli_num_rows(mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'"));
            if ($sq_count != 0) {
            ?>
                <section class="print_sec main_block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered no-marg" id="tbl_emp_list">
                                    <thead>
                                        <tr class="table-heading-row">
                                            <th>ACTIVITY DATE</th>
                                            <th>CITY NAME</th>
                                            <th>ACTIVITY NAME</th>
                                            <th>TRANSFER OPTION</th>
                                            <th>Adult(s)</th>
                                            <th>CWB</th>
                                            <th>CWOB</th>
                                            <th>Infant(s)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sq_exc_acc = mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'");
                                        while ($row_exc_acc = mysqli_fetch_assoc($sq_exc_acc)) {
                                            $sq_city_name = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_exc_acc[city_id]'"));
                                            $sq_exc_name = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master_tariff where entry_id='$row_exc_acc[exc_id]'"));
                                            ?>
                                            <tr>
                                                <td><?= get_datetime_user($row_exc_acc['exc_date']) ?></td>
                                                <td><?= $sq_city_name['city_name'] ?></td>
                                                <td><?= $sq_exc_name['excursion_name'] ?></td>
                                                <td><?= $row_exc_acc['transfer_option'] ?></td>
                                                <td><?= $row_exc_acc['adult'] ?> </td>
                                                <td><?= $row_exc_acc['chwb'] ?> </td>
                                                <td><?= $row_exc_acc['chwob'] ?> </td>
                                                <td><?= $row_exc_acc['infant'] ?> </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            <?php } ?>

            <section class="print_sec main_block">
                <?php if (isset($sq_service_voucher['note'])) { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="print_info_block">
                                <ul class="main_block noType">
                                    <li class="col-md-12 mg_tp_10 mg_bt_10"><span>Note :
                                        </span><?= $sq_service_voucher['note'] ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </section>

            <!-- Terms and Conditions -->
            <?php
            $sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Activity Service Voucher' and active_flag ='Active'"));
            if (isset($sq_terms_cond['terms_and_conditions'])) {
            ?>

                <section class="print_sec main_block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="section_heding">
                                <h2>Terms and Conditions</h2>
                                <div class="section_heding_img">
                                    <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                                </div>
                            </div>
                            <div class="print_text_bolck">
                                <?php
                                echo $sq_terms_cond['terms_and_conditions']; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php } ?>
            <!-- Payment Detail -->
            <section class="print_sec main_block">
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-5">
                        <div class="print_quotation_creator text-center">
                            <span>Generated BY </span><br><?= $emp_name ?>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </div>
<?php } ?>
<?php
$q_transport_count = mysqli_num_rows(mysqlQuery("select * from package_tour_transport_master where booking_id='$booking_id'"));
if($q_transport_count > 0){
?>
<!-- Transport Voucher -->
    <section class="repeat_section main_block">
        <img src="<?= BASE_URL ?>images/vouchers/transfer-service-voucher.jpg" class="watermark">

        <section class="print_header main_block" style="margin-bottom: 200px !important;">
            <div class="col-md-6 no-pad">
                <div class="print_header_logo">
                    <img src="<?= $admin_logo_url ?>" class="img-responsive mg_tp_10 set_logo_on_img">
                </div>
            </div>
            <div class=" col-md-6 no-pad" style="color: #ddd !important; filter: brightness(1000) !important;">
                <div class="c-w-main print_header_contact text-right" style="color: #ddd !important; filter: brightness(1000) !important;">
                    <span class="c-w-main title"><?php echo $app_name; ?></span><br>
                    <div class="c-w-main" style="color: #ddd !important; filter: brightness(1000) !important;">
                        <?php echo ($branch_status == 'yes') ? $branch_details['address1'] . ',' . $branch_details['address2'] . ',' . $branch_details['city'] : $app_address; ?>
                    </div>
                    <p class="c-w-main no-marg"><img src="<?= BASE_URL ?>/images/icons/phone-icon.png" alt="" width="15">
                        <?php echo ($branch_status == 'yes') ? $branch_details['contact_no'] : $app_contact_no; ?>
                    </p>
                    <p class="c-w-main"><img src="<?= BASE_URL ?>/images/icons/email-icon.png" alt="" width="15">
                        <?php echo ($branch_status == 'yes' && $branch_details['email_id'] != '') ? $branch_details['email_id'] : $app_email_id;; ?>
                    </p>
                </div>
            </div>
        </section>
        <!-- print-detail -->
        <section class="print_sec main_block">
            <div class="section_heding">
                <h2>BOOKING DETAILS</h2>
                <div class="section_heding_img">
                    <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="print_info_block">
                        <ul class="main_block noType">
                            <li class="col-md-3 mg_tp_10 mg_bt_10">
                                <div class="print_quo_detail_block">
                                    <i class="fa fa-hourglass-half" aria-hidden="true"></i><br>
                                    <span>DURATION</span><br>
                                    <?php echo ($total_days + 1) . ' Days'; ?><br>
                                </div>
                            </li>
                            <li class="col-md-4 mg_tp_10 mg_bt_10">
                                <div class="print_quo_detail_block">
                                    <i class="fa fa-users" aria-hidden="true"></i><br>
                                    <span>TOTAL GUEST(s)</span><br>
                                    <?= $adults ?> Adult(s), <?= $children ?> Child(ren), <?= $infants ?> Infant(s)<br>
                                </div>
                            </li>
                            <li class="col-md-3 mg_tp_10 mg_bt_10">
                                <div class="print_quo_detail_block">
                                    <i class="fa fa-user" aria-hidden="true"></i><br>
                                    <span>GUEST NAME</span><br>
                                    <?= $name ?><br>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Transport Details -->
        <?php
        $sq_count = mysqli_num_rows(mysqlQuery("select * from package_tour_transport_voucher_entries where booking_id='$booking_id'"));
        if ($sq_count != 0) {
        ?>
            <section class="print_sec main_block">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>Vehicle</th>
                                        <th>Start_d/t</th>
                                        <th>End_d/t</th>
                                        <th>Pickup</th>
                                        <th>Drop</th>
                                        <th>duration</th>
                                        <th>Dr_Name</th>
                                        <th>Dr_Contact</th>
                                        <th>conf_by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $countVehicle = 0;
                                    $sq_tr_acc = mysqlQuery("select * from package_tour_transport_voucher_entries where booking_id='$booking_id'");
                                    while ($row_tr_acc = mysqli_fetch_assoc($sq_tr_acc)) {
                                        $vehicleDetails = array();
                                        $q_transport = mysqli_fetch_assoc(mysqlQuery("select * from b2b_transfer_master where entry_id='$row_tr_acc[transport_bus_id]'"));

                                        $q_transport_info = mysqlQuery("select * from package_tour_transport_master where booking_id='$row_tr_acc[booking_id]'");

                                        while ($rows = mysqli_fetch_assoc($q_transport_info)) {
                                            array_push($vehicleDetails, $rows);
                                        }
                                        // Pickup
                                        if ($vehicleDetails[$countVehicle]['pickup_type'] == 'city') {
                                            $city_id = $vehicleDetails[$countVehicle]['pickup'];
                                            $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$city_id'"));
                                            $pickup = $row['city_name'];
                                        } else if ($vehicleDetails[$countVehicle]['pickup_type'] == 'hotel') {
                                            $hotel_id = $vehicleDetails[$countVehicle]['pickup'];
                                            $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$hotel_id'"));
                                            $pickup = $row['hotel_name'];
                                        } else {
                                            $a_id = $vehicleDetails[$countVehicle]['pickup'];
                                            $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$a_id'"));
                                            $airport_nam = clean($row['airport_name']);
                                            $airport_code = clean($row['airport_code']);
                                            $pickup = $airport_nam . " (" . $airport_code . ")";
                                        }
                                        //Drop-off
                                        if ($vehicleDetails[$countVehicle]['drop_type'] == 'city') {
                                            $city_id = $vehicleDetails[$countVehicle]['drop'];
                                            $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$city_id'"));
                                            $drop = $row['city_name'];
                                        } else if ($vehicleDetails[$countVehicle]['drop_type'] == 'hotel') {
                                            $hotel_id = $vehicleDetails[$countVehicle]['drop'];
                                            $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$hotel_id'"));
                                            $drop = $row['hotel_name'];
                                        } else {
                                            $a_id = $vehicleDetails[$countVehicle]['drop'];
                                            $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$a_id'"));
                                            $airport_nam = clean($row['airport_name']);
                                            $airport_code = clean($row['airport_code']);
                                            $drop = $airport_nam . " (" . $airport_code . ")";
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $q_transport['vehicle_name'] ?></td>
                                            <td><?= get_datetime_user($vehicleDetails[$countVehicle]['transport_from_date']) ?></td>
                                            <td><?= get_datetime_user($vehicleDetails[$countVehicle]['transport_end_date']) ?></td>
                                            <td><?= $pickup ?></td>
                                            <td><?= $drop ?></td>
                                            <td><?= $vehicleDetails[$countVehicle]['service_duration'] ?></td>
                                            <td><?= $row_tr_acc['driver_name'] ?></td>
                                            <td><?= $row_tr_acc['driver_contact'] ?></td>
                                            <td><?= $row_tr_acc['confirm_by'] ?></td>
                                        </tr>
                                    <?php $countVehicle++;
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>

        <!-- INCLUSIONS -->
        <?php if (isset($sq_service_voucher['inclusions']) && $sq_service_voucher['inclusions'] != '') { ?>
            <section class="print_sec main_block">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section_heding">
                            <h2>INCLUSIONS</h2>
                            <div class="section_heding_img">
                                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                            </div>
                        </div>
                        <div class="print_text_bolck">
                            <?= $sq_service_voucher['inclusions'] ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <?php if (isset($sq_service_voucher['special_arrangments']) && $sq_service_voucher['special_arrangments'] != '') { ?>
            <section class="print_sec main_block">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section_heding">
                            <h2>SPECIAL ARRANGEMENT</h2>
                            <div class="print_text_bolck">
                                <?= $sq_service_voucher['special_arrangments'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <!-- HOTEL Detail -->
        <?php
        $sq_count = mysqli_num_rows(mysqlQuery("select * from package_hotel_accomodation_master where booking_id='$booking_id'"));
        if ($sq_count != 0) {
        ?>
            <section class="print_sec main_block">
                <div class="section_heding">
                    <h2>HOTEL DETAILs</h2>
                    <div class="section_heding_img">
                        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>CITY_NAME</th>
                                        <th>HOTEL_NAME</th>
                                        <th>Check_In</th>
                                        <th>Check_Out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sq_hotel_acc = mysqlQuery("select * from package_hotel_accomodation_master where booking_id='$booking_id'");
                                    while ($row_hotel_acc = mysqli_fetch_assoc($sq_hotel_acc)) {
                                        $sq_city_name = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_hotel_acc[city_id]'"));
                                        $sq_hotel_name = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$row_hotel_acc[hotel_id]'"));
                                    ?>
                                        <tr>
                                            <td><?= $sq_city_name['city_name'] ?></td>
                                            <td><?= $sq_hotel_name['hotel_name'] ?></td>
                                            <td><?= date('d-m-Y  H:i', strtotime($row_hotel_acc['from_date'])) ?></td>
                                            <td><?= date('d-m-Y  H:i', strtotime($row_hotel_acc['to_date'])) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>

        <!-- Activity Detail -->
        <?php
        $sq_count = mysqli_num_rows(mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'"));
        if ($sq_count != 0) {
        ?>
            <section class="print_sec main_block">
                <div class="section_heding">
                    <h2>ACTIVITY DETAILS</h2>
                    <div class="section_heding_img">
                        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>Activity_date</th>
                                        <th>City_Name</th>
                                        <th>Activity_name</th>
                                        <th>Transfer_option</th>
                                        <th>Adult(s)</th>
                                        <th>CWB</th>
                                        <th>CWOB</th>
                                        <th>Infant(s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sq_entry = mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'");
                                    while ($row_entry = mysqli_fetch_assoc($sq_entry)) {
                                        $q_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_entry[city_id]'"));
                                        $sq_ex = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master_tariff where entry_id='$row_entry[exc_id]'"));
                                    ?>
                                        <tr>
                                            <td><?php echo get_datetime_user($row_entry['exc_date']) ?></td>
                                            <td><?= $q_city['city_name'] ?></td>
                                            <td><?= $sq_ex['excursion_name'] ?></td>
                                            <td><?= $row_entry['transfer_option'] ?> </td>
                                            <td><?= $row_entry['adult'] ?> </td>
                                            <td><?= $row_entry['chwb'] ?> </td>
                                            <td><?= $row_entry['chwob'] ?> </td>
                                            <td><?= $row_entry['infant'] ?> </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>

        <?php
        $count = 1;
        if ($sq_package_program_count > 0) {
        ?>
            <!-- Tour Itinenary -->
            <section class="print_sec main_block">
                <div class="section_heding">
                    <h2>TOUR ITINERARY</h2>
                    <div class="section_heding_img">
                        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="print_itinenary main_block no-pad no-marg noType">
                            <?php
                            $date1 = $sq_booking['tour_from_date'];
                            $date2 = $sq_booking['tour_to_date'];
                            if ($sq_package_program_count != 0) {
                                $dates = array();
                                $days = array();
                                $current = strtotime($date1);
                                $date2 = strtotime($date2);
                                $stepVal = '+1 day';
                                while ($current <= $date2) {
                                    $dates[] = date('d-m-Y', $current);
                                    $days[] = date('l', $current);
                                    $current = strtotime($stepVal, $current);
                                }
                            }
                            $count = 1;
                            $i = 0;
                            while ($row_itinarary = mysqli_fetch_assoc($sq_package_program)) {
                                $date_format = isset($dates[$i]) ? $dates[$i] : 'NA';
                            ?>
                                <li class="print_single_itinenary main_block">
                                    <div class="print_itinenary_count print_info_block" style="width:200px;">DAY - <?= $count ?>
                                        <b>(<?php echo $date_format ?>) </b>
                                    </div>
                                    <div class="print_itinenary_desciption print_info_block">
                                        <div class="print_itinenary_attraction">
                                            <span class="print_itinenary_attraction_icon"><i class="fa fa-map-marker"></i></span>
                                            <samp class="print_itinenary_attraction_location"><?= $row_itinarary['attraction'] ?></samp>
                                        </div>
                                        <p><?= $row_itinarary['day_wise_program'] ?></p>
                                    </div>
                                    <div class="print_itinenary_details">
                                        <div class="print_info_block">
                                            <ul class="main_block no-pad noType">
                                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span><i class="fa fa-bed"></i> :
                                                    </span><?= $row_itinarary['stay'] ?></li>
                                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span><i class="fa fa-cutlery"></i> :
                                                    </span><?= $row_itinarary['meal_plan'] ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            <?php
                                $count++;
                                $i++;
                            } ?>
                        </ul>
                    </div>
                </div>
            </section>
        <?php } ?>

        <!-- Terms and Conditions -->
        <?php
        $sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Transport Service Voucher' and active_flag ='Active'"));
        if (isset($sq_terms_cond['terms_and_conditions']) && $sq_terms_cond['terms_and_conditions'] != '') { ?>
            <section class="print_sec main_block">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section_heding">
                            <h2>Terms and Conditions</h2>
                            <div class="section_heding_img">
                                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                            </div>
                        </div>
                        <div class="print_text_bolck">
                            <?php
                            echo $sq_terms_cond['terms_and_conditions'];   ?>
                        </div>
                    </div>
                </div>
            </section>
            <section class="print_sec main_block">
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-5">
                        <div class="print_quotation_creator text-center">
                            <span>Generated BY </span><br><?= $emp_name ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php } ?>
    </section>
<?php } ?>
</body>

</html>
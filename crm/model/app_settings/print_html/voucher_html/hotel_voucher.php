<?php
//Generic Files
include "../../../model.php";
include "../print_functions.php";
$hotel_accomodation_id = $_GET['hotel_accomodation_id'];

$sq_service_voucher1 = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Hotel Service Voucher' and active_flag ='Active'"));
$sq_accomodation1 =  mysqlQuery("select * from hotel_booking_entries where booking_id='$hotel_accomodation_id'");
while ($sq_accomodation = mysqli_fetch_assoc($sq_accomodation1)) {

    $hotel_id = $sq_accomodation['hotel_id'];

    $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$hotel_id'"));
    $mobile_no = $encrypt_decrypt->fnDecrypt($sq_hotel['mobile_no'], $secret_key);
    $email_id1 = $encrypt_decrypt->fnDecrypt($sq_hotel['email_id'], $secret_key);

    $booking_id = $sq_accomodation['booking_id'];
    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from hotel_booking_master where booking_id='$booking_id' and delete_status='0'"));

    $total_pax = floatval($sq_booking['adults']) + floatval($sq_booking['childrens']) + floatval($sq_booking['infants']);

    //Total days
    $total_days1 = strtotime($sq_accomodation['check_out']) - strtotime($sq_accomodation['check_in']);
    $total_days = round($total_days1 / 86400);

    $sq_customer_name = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));
    if ($sq_customer_name['type'] == 'Corporate' || $sq_customer_name['type'] == 'B2B') {
        $name = $sq_customer_name['company_name'];
    } else {
        $name = $sq_customer_name['first_name'] . ' ' . $sq_customer_name['last_name'];
    }
	$pass_name = (($sq_customer_name['type']=='Corporate' || $sq_customer_name['type'] == 'B2B') && $sq_booking['pass_name'] != '') ? ' ('.$sq_booking['pass_name'].')' : '';

    $contact_no = $sq_customer_name['contact_no'];
    $email_id = $sq_customer_name['email_id'];

    $emp_id = $_SESSION['emp_id'];
    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$emp_id'"));
    if ($emp_id == '0') {
        $emp_name = 'Admin';
    } else {
        $emp_name = $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
    }
?>
<div class="repeat_section main_block">
    <!-- header -->
    <img src="<?= BASE_URL ?>images/vouchers/hotel-service-voucher.jpg" class="watermark">
    <section class="print_header main_block" style="margin-bottom: 200px;">
        <div class="col-md-6 no-pad">
            <div class="print_header_logo">
                <img src="<?= $admin_logo_url ?>" class="img-responsive mg_tp_10 set_logo_on_img">
            </div>
        </div>
        <div class=" col-md-6 no-pad" style="color: #ddd !important; filter: brightness(1000) !important;">
            <div class="c-w-main print_header_contact text-right"
                style="color: #ddd !important; filter: brightness(1000) !important;">
                <span class="c-w-main title"><?php echo $sq_hotel['hotel_name']; ?></span><br>
                <div class="c-w-main" style="color: #ddd !important; filter: brightness(1000) !important;">
                    <?php echo $sq_hotel['hotel_address']; ?></div>
                <p class="c-w-main no-marg"><img src="<?= BASE_URL ?>/images/icons/phone-icon.png" alt="" width="15">
                    <?php echo $mobile_no; ?></p>
                <?php if($email_id1!='') { ?><p class="c-w-main"><img src="<?= BASE_URL ?>/images/icons/email-icon.png" alt="" width="15">
                    <?php echo $email_id1; ?></p> <?php } ?>
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
                                <?= ($total_days) . 'N' ?><br>
                            </div>
                        </li>
                        <li class="col-md-3 mg_tp_10 mg_bt_10">
                            <div class="print_quo_detail_block">
                                <i class="fa fa-users" aria-hidden="true"></i><br>
                                <span>TOTAL GUEST(s)</span><br>
                                <?= $sq_booking['adults'] ?> Adult(s), <?= $sq_booking['childrens'] ?> Child(wo), <?= $sq_booking['child_with_bed'] ?> Child(wi),
                                <?= $sq_booking['infants'] ?> Infant(s)<br>
                            </div>
                        </li>
                        <li class="col-md-3 mg_tp_10 mg_bt_10">
                            <div class="print_quo_detail_block">
                                <i class="fa fa-home" aria-hidden="true"></i><br>
                                <span>TOTAL ROOM(s)</span><br>
                                <?php echo $sq_accomodation['rooms'];
                                    echo ($sq_accomodation['extra_beds'] != '0') ? ' + ' . $sq_accomodation['extra_beds'] . ' Extra Bed(s)' : ''; ?><br>
                            </div>
                        </li>
                        <li class="col-md-3 mg_tp_10 mg_bt_10">
                            <div class="print_quo_detail_block">
                                <i class="fa fa-university" aria-hidden="true"></i><br>
                                <span>ROOM CATEGORY</span><br>
                                <?= $sq_accomodation['category'] ?><br>
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
                                <th>CUSTOMER_NAME</th>
                                <th>CHECK-IN </th>
                                <th>CHECK-OUT</th>
                                <th>MEAL PLAN</th>
                                <th>CONTACT</th>
                                <th>CONFIRMATION ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $name.$pass_name ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($sq_accomodation['check_in'])) ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($sq_accomodation['check_out'])) ?></td>
                                <td><?= $sq_accomodation['meal_plan'] ?></td>
                                <td><?= $sq_hotel['immergency_contact_no'] ?></td>
                                <td><?= $sq_accomodation['conf_no'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <?php
        if (isset($sq_service_voucher1['terms_and_conditions'])) { ?>

    <!-- Terms and Conditions -->
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
                    <?= $sq_service_voucher1['terms_and_conditions']; ?>
                </div>
            </div>
        </div>
    </section>
    <?php } ?>

    <p style="float: left;width: 100%;"><b>Note: Please present this service voucher to service provider
            (Hotel/Transport) upon arrival</b></p>

    <!-- Payment Detail -->
    <section class="print_sec main_block">
        <div class="row">
            <div class="col-md-7">
            </div>
            <div class="col-md-5">
                <div class="print_quotation_creator text-center">
                    <span>Generated BY </span><br><?= $emp_name ?>
                </div>
            </div>
        </div>
    </section>
</div>
</body>

</html>
<?php } ?>
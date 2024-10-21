<?php
//Generic Files
include "../../../../model.php";
include "printFunction.php";
global $app_quot_img, $currency;

$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select * from branch_assign where link='package_booking/quotation/car_flight/car_rental/index.php'"));
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

$sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Car Rental Quotation' and active_flag ='Active'"));

$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from car_rental_quotation_master where quotation_id='$quotation_id'"));
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
$tax_show = '';
$service_charge = $sq_quotation['service_charge'];
$newBasic = $basic_cost1 = $sq_quotation['subtotal'] + $sq_quotation['other_charge'] + $sq_quotation['state_entry'] + $service_charge + $sq_quotation['markup_cost'];
$bsmValues = json_decode($sq_quotation['bsm_values']);
//////////////////Service Charge Rules
$service_tax_amount = 0;
$percent = '';
if ($sq_quotation['service_tax_subtotal'] !== 0.00 && ($sq_quotation['service_tax_subtotal']) !== '') {
    $service_tax_subtotal1 = explode(',', $sq_quotation['service_tax_subtotal']);
    for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
        $service_tax = explode(':', $service_tax_subtotal1[$i]);
        $service_tax_amount +=  $service_tax[2];
        $percent .= $service_tax[0]  . $service_tax[1] .', ';
    }
}
////////////////////Markup Rules
$markupservice_tax_amount = 0;
if ($sq_quotation['markup_cost_subtotal'] !== 0.00 && $sq_quotation['markup_cost_subtotal'] !== "") {
    $service_tax_markup1 = explode(',', $sq_quotation['markup_cost_subtotal']);
    for ($i = 0; $i < sizeof($service_tax_markup1); $i++) {
        $service_tax = explode(':', $service_tax_markup1[$i]);
        $markupservice_tax_amount += $service_tax[2];
    }
}

$total_tax = currency_conversion($currency, $currency, ($markupservice_tax_amount + $service_tax_amount));
$tax_show = $percent . ' ' .$total_tax;
$quotation_cost = currency_conversion($currency, $currency, $sq_quotation['total_tour_cost']);
?>

<section class="headerPanel main_block">
    <div class="headerImage">
        <img src="<?= $app_quot_img ?>" class="img-responsive">
        <div class="headerImageOverLay"></div>
    </div>

    <!-- header -->
    <section class="print_header main_block side_pad mg_tp_30">
        <div class="col-md-4 no-pad">
            <div class="print_header_logo">
                <img src="<?= $admin_logo_url ?>" class="img-responsive mg_tp_10">
            </div>
        </div>
        <div class="col-md-4 no-pad text-center mg_tp_30">
            <span class="title"><i class="fa fa-pencil-square-o"></i> CAR RENTAL QUOTATION</span>
        </div>

        <?php
    include "standard_header_html.php";
    ?>

        <!-- print-detail -->
        <section class="print_sec main_block side_pad">
            <div class="row">
                <div class="col-md-12">
                    <div class="print_info_block">
                        <ul class="main_block">
                            <li class="col-md-3 mg_tp_10 mg_bt_10">
                                <div class="print_quo_detail_block">
                                    <i class="fa fa-calendar" aria-hidden="true"></i><br>
                                    <span>QUOTATION DATE</span><br>
                                    <?= get_date_user($sq_quotation['quotation_date']) ?><br>
                                </div>
                            </li>
                            <li class="col-md-3 mg_tp_10 mg_bt_10">
                                <div class="print_quo_detail_block">
                                    <i class="fa fa-hourglass-half" aria-hidden="true"></i><br>
                                    <span>DURATION</span><br>
                                    <?php echo $sq_quotation['days_of_traveling'] . ' Days'; ?><br>
                                </div>
                            </li>
                            <li class="col-md-3 mg_tp_10 mg_bt_10">
                                <div class="print_quo_detail_block">
                                    <i class="fa fa-users" aria-hidden="true"></i><br>
                                    <span>TOTAL GUEST</span><br>
                                    <?= $sq_quotation['total_pax'] ?><br>
                                </div>
                            </li>
                            <li class="col-md-3 mg_tp_10 mg_bt_10">
                                <div class="print_quo_detail_block">
                                    <i class="fa fa-tags" aria-hidden="true"></i><br>
                                    <span>PRICE</span><br>
                                    <?= $quotation_cost ?><br>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

    </section>

    <!-- Package -->
    <section class="print_sec main_block side_pad mg_tp_30">
        <div class="section_heding">
            <h2>BOOKING DETAILS</h2>
            <div class="section_heding_img">
                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
            </div>
        </div>
        <div class="row mg_tp_30">
            <div class="col-md-12">
                <div class="print_info_block">
                    <ul class="print_info_list">
                        <li class="col-md-6 mg_tp_10 mg_bt_10"><span>ROUTE
                                :</span><?= ($sq_quotation['travel_type'] == 'Outstation') ? $sq_quotation['places_to_visit'] : $sq_quotation['local_places_to_visit'] ?>
                        </li>
                        <li class="col-md-6 mg_tp_10 mg_bt_10"><span>CUSTOMER NAME :</span>
                            <?= $sq_quotation['customer_name'] ?></li>
                    </ul>
                    <ul class="print_info_list">
                        <li class="col-md-6 mg_tp_10 mg_bt_10"><span>QUOTATION ID :</span>
                            <?= get_quotation_id($quotation_id, $year) ?></li>
                        <li class="col-md-6 mg_tp_10 mg_bt_10"><span>E-MAIL ID :</span> <?= $sq_quotation['email_id'] ?>
                        </li>
                        <?php if ($sq_quotation['mobile_no'] != '') { ?><li class="col-md-6 mg_tp_10 mg_bt_10">
                            <span>MOBILE NO :</span> <?= $sq_quotation['mobile_no'] ?>
                        </li><?php } ?>
                    </ul>
                    <hr class="main_block">
                    <?php if ($sq_quotation['travel_type'] == 'Local') { ?>
                    <ul class="main_block">
                        <li class="col-md-6 mg_tp_10 mg_bt_10"><span>FROM DATE :
                            </span><?= get_date_user($sq_quotation['from_date']) ?></li>
                        <li class="col-md-6 mg_tp_10 mg_bt_10"><span>TO DATE :
                            </span><?= get_date_user($sq_quotation['to_date']) ?></li>
                    </ul>
                    <?php } else { ?>
                    <li class="col-md-6 mg_tp_10 mg_bt_10"><span>FROM DATE :
                        </span><?= get_date_user($sq_quotation['from_date']) ?></li>
                    <li class="col-md-6 mg_tp_10 mg_bt_10"><span>TO DATE :
                        </span><?= get_date_user($sq_quotation['to_date']) ?></li>
                    <?php } ?>
                    <?php $no_of_car = ceil($sq_quotation['total_pax'] / $sq_quotation['capacity']); ?>
                    <ul class="main_block">
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Transport -->
    <section class="print_sec main_block side_pad mg_tp_30">
        <div class="section_heding">
            <h2>VEHICLE DETAILS</h2>
            <div class="section_heding_img">
                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="print_info_block">
                    <ul class="main_block no-pad">
                        <li class="col-md-4 mg_tp_10 mg_bt_10"><span>VEHICLE NAME :
                            </span><?= $sq_quotation['vehicle_name'] ?></li>
                        <li class="col-md-6 mg_tp_10 mg_bt_10"><span>NO OF VEHICLE : </span><?= $no_of_car ?></li>
                    </ul>
                    <ul class="main_block no-pad">
                        <li class="col-md-4 mg_tp_10 mg_bt_10"><span>EXTRA KM COST :
                            </span><?= $sq_quotation['extra_km_cost'] ?></li>
                        <li class="col-md-4 mg_tp_10 mg_bt_10"><span>EXTRA HR COST :
                            </span><?= $sq_quotation['extra_hr_cost'] ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Costing -->
    <section class="print_sec main_block side_pad mg_tp_30">
        <div class="row">
            <div class="col-md-6">
                <div class="section_heding">
                    <h2>COSTING</h2>
                    <div class="section_heding_img">
                        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                    </div>
                </div>
                <div class="print_info_block">
                    <ul class="main_block">
                        <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TOTAL FARE :
                            </span><?= currency_conversion($currency, $currency, $newBasic) ?></li>
                        <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TAX : </span><?= $tax_show ?></li>
                        <?php if ($sq_quotation['travel_type'] == "Outstation") { ?>
                        <li class="col-md-12 mg_tp_10 mg_bt_10"><span>PERMIT :
                            </span><?= currency_conversion($currency, $currency, $sq_quotation['permit']) ?></li>
                        <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TOLL/PARKING :
                            </span><?= currency_conversion($currency, $currency, $sq_quotation['toll_parking']) ?></li>
                        <li class="col-md-12 mg_tp_10 mg_bt_10"><span>DRIVER ALLOWANCE :
                            </span><?= currency_conversion($currency, $currency, $sq_quotation['driver_allowance']) ?></li>
                        <?php } ?>
                        <li class="col-md-12 mg_tp_10 mg_bt_10"><span>ROUND OFF :
                            </span><?= currency_conversion($currency, $currency, $sq_quotation['roundoff']) ?></li>
                        <li class="col-md-12 mg_tp_10 mg_bt_10"><span>QUOTATION COST :
                                <?= currency_conversion($currency, $currency, $sq_quotation['total_tour_cost']) ?></span></li>
                    </ul>
                </div>
            </div>

            <!-- Bank Detail -->
            <div class="col-md-6">
                <div class="section_heding">
                    <h2>BANK DETAILS</h2>
                    <div class="section_heding_img">
                        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                    </div>
                </div>
                <div class="print_info_block">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="main_block">
                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BANK NAME :
                                    </span><?=  ($sq_bank_count>0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting ?></li>
                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>A/C TYPE :
                                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_type'] != '') ? $sq_bank_branch['account_type'] : $acc_name ?></li>
                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BRANCH :
                                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['branch_name'] != '') ? $sq_bank_branch['branch_name'] : $bank_branch_name ?>
                                </li>
                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>A/C NO :
                                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_no'] != '') ? $sq_bank_branch['account_no'] : $bank_acc_no  ?></li>
                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BANK ACCOUNT NAME :
                                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_name'] != '') ? $sq_bank_branch['account_name'] : $bank_account_name ?></li>
                                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>SWIFT CODE :
                                    </span><?= ($sq_bank_count>0 || $sq_bank_branch['swift_code'] != '') ? strtoupper($sq_bank_branch['swift_code']) :  strtoupper($bank_swift_code) ?></li>
                            </ul>
                        </div>
                        <?php if (check_qr()) {
            ?>
                        <div class="col-md-6 text-center">
                            <?= get_qr('Protrait Standard') ?>
                            <br>
                            <h4 class="no-marg">Scan & Pay </h4>

                        </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Terms and Conditions -->
    <section class="print_sec main_block side_pad mg_tp_30">
        <?php if (isset($sq_terms_cond['terms_and_conditions'])) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="section_heding">
                    <h2>Terms and Conditions</h2>
                    <div class="section_heding_img">
                        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
                    </div>
                </div>
                <div class="print_text_bolck">
                    <?= $sq_terms_cond['terms_and_conditions'] ?>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="row mg_tp_30">
            <div class="col-md-7"></div>
            <div class="col-md-5 mg_tp_30">
                <div class="print_quotation_creator text-center">
                    <span>PREPARED BY </span><br><?= $emp_name ?>
                </div>
            </div>
        </div>
    </section>

    </body>

    </html>
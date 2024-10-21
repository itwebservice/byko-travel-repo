<?php
//Generic Files
include "../../../../model.php";
include "printFunction.php";

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
global $app_quot_img, $similar_text, $quot_note, $currency,$tcs_note,$app_quot_format;
$quotation_id = $_GET['quotation_id'];

$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$quotation_id'"));
$tcs_note_show = ($sq_quotation['booking_type'] != 'Domestic') ? $tcs_note : '';
$quotation_date = $sq_quotation['quotation_date'];
$yr = explode("-", $quotation_date);
$year = $yr[0];

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

////////////Basic Amount Rules
if ($bsmValues[0]->basic != '') { //inclusive markup
  $newBasic = $tour_cost + $service_tax_amount;
  $tax_show = '';
}
$sq_package_count = mysqli_num_rows(mysqlQuery("select * from  package_quotation_program where quotation_id='$quotation_id'"));
$sq_hotel_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_hotel_entries where quotation_id='$quotation_id'"));
$sq_transport_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_transport_entries2 where quotation_id='$quotation_id'"));
$sq_exc_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_excursion_entries where quotation_id='$quotation_id'"));

$sq_train_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_train_entries where quotation_id='$quotation_id'"));
$sq_plane_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_plane_entries where quotation_id='$quotation_id'"));
$sq_cruise_count = mysqli_num_rows(mysqlQuery("select * from package_tour_quotation_cruise_entries where quotation_id='$quotation_id'"));
?>
<style>
  .package_costing table tr:nth-child(even) {
    background-color: #efefef !important;
  }
</style>

<section class="headerPanel main_block">
  <div class="headerImage">
    <img src="<?= getFormatImg($app_quot_format,$sq_package_name['dest_id']) ?>" class="img-responsive">
    <div class="headerImageOverLay"></div>
  </div>
  <!-- Header -->
  <section class="print_header main_block side_pad mg_tp_30">
    <div class="col-md-4 no-pad">
      <div class="print_header_logo">
        <img src="<?= $admin_logo_url ?>" class="img-responsive mg_tp_10">
      </div>
    </div>
    <div class="col-md-4 no-pad text-center mg_tp_30">
      <span class="title"><i class="fa fa-pencil-square-o"></i> PACKAGE QUOTATION</span>
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
              <li class="col-md-4 mg_tp_10 mg_bt_10">
                <div class="print_quo_detail_block">
                  <i class="fa fa-hashtag" aria-hidden="true"></i>
                  <span>QUOTATION ID</span><br>
                  <?= get_quotation_id($quotation_id, $year) ?><br>
                </div>
              </li>
              <li class="col-md-4 mg_tp_10 mg_bt_10">
                <div class="print_quo_detail_block">
                  <i class="fa fa-calendar" aria-hidden="true"></i>
                  <span>QUOTATION DATE</span><br>
                  <?= get_date_user($sq_quotation['quotation_date']) ?><br>
                </div>
              </li>
              <li class="col-md-4 mg_tp_10 mg_bt_10">
                <div class="print_quo_detail_block">
                  <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                  <span>DURATION</span><br>
                  <?php echo $sq_quotation['total_days'] . 'N/' . ($sq_quotation['total_days']+1) . 'D' ?><br>
                </div>
              </li>
              <li class="col-md-4 mg_tp_10 mg_bt_10">
                <div class="print_quo_detail_block">
                  <i class="fa fa-users" aria-hidden="true"></i>
                  <span>TOTAL GUEST</span><br>
                  <?= $sq_quotation['total_passangers'] ?><br>
                </div>
              </li>
              <li class="col-md-4 mg_tp_10 mg_bt_10">
                <div class="print_quo_detail_block">
                  <i class="fa fa-users" aria-hidden="true"></i>
                  <span>PREPARED BY </span><br>
                  <?= $emp_name ?><br>
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
      <h2>PACKAGE DETAILS</h2>
      <div class="section_heding_img">
        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="print_info_block">
          <ul class="print_info_list main_block">
            <li class="col-md-12 mg_tp_10 mg_bt_10"><span>PACKAGE NAME :</span> <?= $sq_package_name['package_name'] . '(' . $sq_package_name['package_code'] . ')' ?> </li>
            <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TRAVEL DATE :</span> <?= get_date_user($sq_quotation['from_date']) . ' To ' . get_date_user($sq_quotation['to_date']) ?> </li>
          </ul>
          <ul class="print_info_list main_block">
            <li class="col-md-6 mg_tp_10 mg_bt_10"><span>CUSTOMER NAME :</span> <?= $sq_quotation['customer_name'] ?></li>
            <li class="col-md-6 mg_tp_10 mg_bt_10"><span>E-MAIL ID :</span> <?= $sq_quotation['email_id'] ?></li>
          </ul>
          <hr class="main_block">
          <ul class="main_block">
            <li class="col-md-4 mg_tp_10 mg_bt_10"><span>ADULT : </span><?= $sq_quotation['total_adult'] ?></li>
            <li class="col-md-4 mg_tp_10 mg_bt_10"><span>CWB : </span><?= $sq_quotation['children_with_bed'] ?></li>
            <li class="col-md-4 mg_tp_10 mg_bt_10"><span>CWOB : </span><?= $sq_quotation['children_without_bed'] ?></li>
          </ul>
          <ul class="main_block">
            <li class="col-md-4 mg_tp_10 mg_bt_10"><span>INFANT : </span><?= $sq_quotation['total_infant'] ?></li>
            <li class="col-md-4 mg_tp_10 mg_bt_10"><span>TOTAL : </span><?= $sq_quotation['total_passangers'] ?></li>
          </ul>
        </div>
      </div>
    </div>
  </section>
  <section class="print_sec main_block side_pad mg_tp_20">
    <div class="row">
      <!-- Bank Detail -->
      <div class="col-md-12">
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
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BANK NAME : </span><?= ($sq_bank_count>0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting ?></li>
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>A/C TYPE : </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_type'] != '') ? $sq_bank_branch['account_type'] : $acc_name ?></li>
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BRANCH : </span><?= ($sq_bank_count>0 || $sq_bank_branch['branch_name'] != '') ? $sq_bank_branch['branch_name'] : $bank_branch_name ?></li>
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>A/C NO : </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_no'] != '') ? $sq_bank_branch['account_no'] : $bank_acc_no ?></li>
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BANK ACCOUNT NAME : </span><?= ($sq_bank_count>0 || $sq_bank_branch['account_name'] != '') ? $sq_bank_branch['account_name'] : $bank_account_name ?></li>
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>SWIFT CODE : </span><?= ($sq_bank_count>0 || $sq_bank_branch['swift_code'] != '') ? strtoupper($sq_bank_branch['swift_code']) :  strtoupper($bank_swift_code) ?></li>
              </ul>
            </div>
            <?php
            if (check_qr()) { ?>
              <div class="col-md-6 text-center" style="margin-top:30px;">
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

  <!-- Costing -->
  <section class="print_sec main_block side_pad mg_tp_20">
    <div class="row">
      <div class="col-md-12">
        <div class="section_heding">
          <h2>COSTING DETAILS</h2>
          <div class="section_heding_img">
            <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
          </div>
        </div>
        <!-- Group costing -->
        <?php
        if ($sq_quotation['costing_type'] == 1) { ?>
          <div class="row mg_tp_30">
            <div class="col-md-12">
              <div class="table-responsive">
                <table class="table table-bordered no-marg" id="tbl_emp_list">
                  <thead>
                    <tr class="table-heading-row">
                      <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">P_Type</th>
                      <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Tour Cost</th>
                      <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Tax</th>
                      <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Tcs</th>
                      <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">TRAVEL/OTHER</th>
                      <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Total Cost</th>
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
                      $bsmValues = json_decode($sq_costing['bsmValues'],true);
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
                      $quotation_cost = $basic_cost + $service_charge + $service_tax_amount + $sq_quotation['train_cost'] + $sq_quotation['cruise_cost'] + $sq_quotation['flight_cost'] + $sq_quotation['visa_cost'] + $sq_quotation['guide_cost'] + $sq_quotation['misc_cost']+floatval($tcsvalue);
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
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?php echo $sq_costing['package_type'] ?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= '<b>' . $newBasic . '</b>' ?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= str_replace(',', '', $name) . '<b>' . $service_tax_amount_show . '</b>' ?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;">Tcs:(<?=$tcsper?>%)<br><?= $tcs_amount_show?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= '<b>' . $travel_cost . '</b>' ?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= '<b>' . $currency_amount1 .' <s>'.$act_tour_cost_camount.'</s></b>' ?></td>
                      </tr>
                    <?php
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- group Costing End -->
          <?php } else {
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

            $quotation_cost = floatval($quotation_cost) + floatval($travel_cost) + floatval($other_cost)+floatval($tcsvalue);
            $quotation_cost = ceil($quotation_cost);
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
                        <td >Tcs:(<?=$tcsper?>%)<br><?=$tcs_show1?></td>
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
            if ($sq_plane_count > 0 || $sq_train_count > 0 || $sq_cruise_count > 0) { ?>
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
                        if ($sq_plane_count > 0) { ?>
                          <tr>
                            <td><?= 'Flight' ?></td>
                            <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_acost'])) ?></td>
                            <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_ccost'])) ?></td>
                            <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['flight_icost'])) ?></td>
                          </tr>
                        <?php }
                        if ($sq_train_count > 0) { ?>
                          <tr>
                            <td><?= 'Train' ?></td>
                            <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_acost'])) ?></td>
                            <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_ccost'])) ?></td>
                            <td><?= currency_conversion($currency, $sq_quotation['currency_code'], floatval($sq_quotation['train_icost'])) ?></td>
                          </tr>
                        <?php }
                        if ($sq_cruise_count > 0) { ?>
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
        <?php } ?>
        <?php
        $discount1 = currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['discount']);
        if ($sq_quotation['discount'] != 0) {
          $discount = ' (Applied Discount : ' . $discount1 . ')';
        } else {
          $discount = '';
        } ?>
        <p class="costBankTitle mg_tp_10"><?= $discount ?></p>
        <?php
        // if ($tcs_note_show != '') { ?>
          <!-- <p class="costBankTitle"> -->
            <?php //$tcs_note_show ?></p>
        <?php //} ?>
        <?php
        if ($sq_quotation['other_desc'] != '') { ?>
          <p class="costBankTitle">MISCELLANEOUS DESCRIPTION: <?= $sq_quotation['other_desc'] ?></p>
        <?php } ?>
      </div>
    </div>
    </div>
    </div>
    </div>
  </section>

  <!-- print-detail -->

  <!-- Traveling Sections -->
  <section class="print_sec main_block">

    <!-- Accomodations -->
    <?php if ($sq_hotel_count != 0) { ?>
      <section class="print_sec main_block side_pad mg_tp_30">
        <div class="section_heding">
          <h2>ACCOMMODATION</h2>
          <div class="section_heding_img">
            <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
          </div>
        </div>
        <?php
        $sq_package_type = mysqlQuery("select DISTINCT(package_type) from package_tour_quotation_hotel_entries where quotation_id='$quotation_id' order by package_type");
        while ($row_hotel1 = mysqli_fetch_assoc($sq_package_type)) {
          $package_type = $row_hotel1['package_type'];
        ?>
          <h6 class="text-center">PACKAGE TYPE - <?= $row_hotel1['package_type'] ?></h6>
          <div class="row mg_bt_20">
            <div class="col-md-12">
              <div class="table-responsive">
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
                    $sq_package_type1 = mysqlQuery("select * from package_tour_quotation_hotel_entries where quotation_id='$quotation_id' and package_type='$package_type' order by package_type");
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
        <?php } ?>
      </section>
    <?php } ?>
  </section>
  <!-- Traveling Sections -->
  <section class="print_sec main_block">

    <?php if ($sq_transport_count != 0 || $sq_train_count != 0 || $sq_plane_count != 0 || $sq_train_count != 0 || $sq_exc_count != 0) { ?>
      <section class="print_sec main_block side_pad mg_tp_30">
        <div class="section_heding">
          <h2>Travelling Information</h2>
          <div class="section_heding_img">
            <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
          </div>
        </div>

        <!-- Train -->
        <?php
        if ($sq_train_count > 0) { ?>
          <div class="row mg_tp_30">
            <div class="col-md-12 subTitle">
              <h3>Train</h3>
            </div>
            <div class="col-md-12">
              <div class="table-responsive">
                <table class="table table-bordered no-marg" id="tbl_emp_list">
                  <thead>
                    <tr class="table-heading-row">
                      <th>From_Location</th>
                      <th>To_Location</th>
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
        <?php } ?>
        <!-- Flight -->
        <?php
        if ($sq_plane_count > 0) {
        ?>
          <div class="row mg_tp_30">
            <div class="col-md-12 subTitle">
              <h3>Flight</h3>
            </div>
            <div class="col-md-12">
              <div class="table-responsive">
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
                        <td><?= $row_plane['class'] ?></td>
                        <td><?= get_datetime_user($row_plane['dapart_time']) ?></td>
                        <td><?= get_datetime_user($row_plane['arraval_time']) ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php } ?>
        <!-- Cruise -->
        <?php
        if ($sq_cruise_count > 0) { ?>
          <div class="row mg_tp_30">
            <div class="col-md-12 subTitle">
              <h3>Cruise</h3>
            </div>
            <div class="col-md-12">
              <div class="table-responsive">
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
                        <td><?= get_datetime_user($row_cruise['dept_datetime']) ?></td>
                        <td><?= get_datetime_user($row_cruise['arrival_datetime']) ?></td>
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
        <?php } ?>
        <!-- Transport -->
        <?php
        if ($sq_transport_count > 0) { ?>
          <div class="row mg_tp_30">
            <div class="col-md-12 subTitle">
              <h3>Transport</h3>
            </div>
            <div class="col-md-12">
              <div class="table-responsive">
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
        <?php } ?>
        <!-- Activity -->
        <?php
        if ($sq_exc_count > 0) { ?>
          <div class="row mg_tp_30">
            <div class="col-md-12 subTitle">
              <h3>Activity</h3>
            </div>
            <div class="col-md-12">
              <div class="table-responsive custom-table">
                <table class="table table-bordered no-marg" id="tbl_emp_list">
                  <thead>
                    <tr class="table-heading-row">
                      <th>Date&Time</th>
                      <th>city</th>
                      <th>Activity</th>
                      <th>Transfer</th>
                      <th>ADult</th>
                      <th>CWB</th>
                      <th>CWOB</th>
                      <th>INFANT</th>
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
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php } ?>

      </section>
    <?php } ?>
  </section>

  <section class="print_sec main_block">
    <!-- Inclusion -->
    <section class="print_sec main_block side_pad mg_tp_30">
      <div class="row">
        <?php if ($sq_quotation['inclusions'] != '' && $sq_quotation['inclusions'] != ' ' && $sq_quotation['inclusions'] != '<div><br></div>') { ?>
          <div class="col-md-6">
            <div class="section_heding">
              <h2>INCLUSIONS</h2>
              <div class="section_heding_img">
                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
              </div>
            </div>
            <div class="print_text_bolck">
              <?= $sq_quotation['inclusions'] ?>
            </div>
          </div>
        <?php } ?>

        <!-- Exclusion -->
        <?php if ($sq_quotation['exclusions'] != '' && $sq_quotation['exclusions'] != ' ' && $sq_quotation['exclusions'] != '<div><br></div>') { ?>
          <div class="col-md-6">
            <div class="section_heding">
              <h2>EXCLUSIONS</h2>
              <div class="section_heding_img">
                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
              </div>
            </div>
            <div class="print_text_bolck">
              <?= $sq_quotation['exclusions'] ?>
            </div>
          </div>
        <?php } ?>
      </div>
    </section>
  </section>

  <!-- Terms and Conditions -->
  <section class="print_sec main_block side_pad mg_tp_30">
    <?php if (isset($sq_terms_cond['terms_and_conditions']) || isset($sq_package_name['note'])) { ?>
      <?php
      if (isset($sq_terms_cond['terms_and_conditions']) && $sq_terms_cond['terms_and_conditions'] != ' ') { ?>
        <div class="row">
          <div class="col-md-12">
            <div class="section_heding">
              <h2>TERMS AND CONDITIONS</h2>
              <div class="section_heding_img">
                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
              </div>
            </div>
            <div class="print_text_bolck">
              <?php echo $sq_terms_cond['terms_and_conditions']; ?>
            </div>
          </div>
        </div>
      <?php }
      if ($sq_package_name['note'] != '' && $sq_package_name['note'] != ' ') { ?>
        <div class="row mg_tp_20">
          <div class="col-md-12">
            <div class="section_heding">
              <h2>NOTE</h2>
              <div class="section_heding_img">
                <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
              </div>
            </div>
            <div class="print_text_bolck">
              <?php echo $sq_package_name['note']; ?>
            </div>
          </div>
        </div>
      <?php } ?>
    <?php } ?>
    <div class="row mg_tp_10">
      <div class="col-md-12">
        <?php echo $quot_note; ?>
      </div>
    </div>

  </section>
  <!-- Tour Itinenary -->
  <?php if ($sq_package_count != 0) { ?>
    <section class="print_sec main_block side_pad mg_tp_30">
      <div class="vitinerary_div">
        <h6>Destination Guide Video</h6>
        <img src="<?php echo BASE_URL . 'images/quotation/youtube-icon.png'; ?>" class="itinerary-img img-responsive"><br />
        <a href="<?= $sq_dest['link'] ?>" class="no-marg" target="_blank"></a>
      </div>

      <div class="section_heding mg_tp_20">
        <h2>TOUR ITINERARY</h2>
        <div class="section_heding_img">
          <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
        </div>
      </div>
      <div class="">
        <div class="col-md-12">
          <div class="print_itinenary main_block no-pad no-marg">

            <?php
            $count = 1;
            $i = 0;
            $dates = (array) get_dates_for_package_itineary($_GET['quotation_id']);
            while ($row_itinarary = mysqli_fetch_assoc($sq_package_program)) {
              $last_child = ($sq_package_count == $count) ? 'last-child' : '';
              
              $date_format = isset($dates[$i]) ? $dates[$i] : 'NA';
            ?>
              <section class="print_single_itinenary main_block <?= $last_child ?>">
                <div class="print_itinenary_count print_info_block" style="width:200px;">DAY - <?= $count ?> <b>(<?php echo $date_format ?>) </b></div>
                <div class="print_itinenary_desciption print_info_block">
                  <div class="print_itinenary_attraction">
                    <span class="print_itinenary_attraction_icon"><i class="fa fa-map-marker"></i></span>
                    <samp class="print_itinenary_attraction_location"><?= $row_itinarary['attraction'] ?></samp>
                  </div>
                  <p><?= $row_itinarary['day_wise_program'] ?></p>
                </div>
                <div class="print_itinenary_details">
                  <div class="print_info_block">
                    <ul class="main_block no-pad">
                      <li class="col-md-12 mg_tp_10 mg_bt_10"><span><i class="fa fa-bed"></i> : </span><?= $row_itinarary['stay'] ?></li>
                      <li class="col-md-12 mg_tp_10 mg_bt_10"><span><i class="fa fa-cutlery"></i> : </span><?= $row_itinarary['meal_plan'] ?></li>
                    </ul>
                  </div>
                </div>
              </section>
            <?php $count++;
              $i++;
            } ?>
          </div>
        </div>
      </div>
    </section>
  <?php } ?>

  <style>
    .custom-table th {
      font-size: 12px !important;
      padding: 10px !important;
    }
  </style>

  </body>

  </html>
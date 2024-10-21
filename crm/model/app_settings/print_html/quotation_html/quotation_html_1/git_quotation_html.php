<?php
//Generic Files
include "../../../../model.php";
include "printFunction.php";

$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select * from branch_assign where link='package_booking/quotation/group_tour/index.php'"));
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
global $app_quot_img, $currency, $app_quot_format;

$sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Group Quotation' and active_flag ='Active'"));
$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_quotation_master where quotation_id='$quotation_id'"));
$sq_package_program = mysqlQuery("select * from group_tour_program where tour_id ='$sq_quotation[tour_group_id]'");
$sq_tour = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id='$sq_quotation[tour_group_id]'"));
$sq_dest = mysqli_fetch_assoc(mysqlQuery("select link from video_itinerary_master where dest_id = '$sq_tour[dest_id]'"));
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
$tour_cost = $sq_quotation['tour_cost'];
////////////////Currency conversion ////////////
$currency_amount1 = currency_conversion($currency, $sq_quotation['currency_code'], $sq_quotation['quotation_cost']);
?>

<section class="headerPanel main_block">
  <div class="headerImage">
    <img src="<?= getFormatImg($app_quot_format, $sq_tour['dest_id']) ?>" class="img-responsive">
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
      <span class="title"><i class="fa fa-pencil-square-o"></i> GROUP QUOTATION</span>
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
                  <?php echo ($sq_quotation['total_days']) . 'N/' . ($sq_quotation['total_days'] + 1) . 'D' ?><br>
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
                  <i class="fa fa-tags" aria-hidden="true"></i>
                  <span>PRICE</span><br>
                  <?= $currency_amount1 ?><br>
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
      <h2>TOUR DETAILS</h2>
      <div class="section_heding_img">
        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="print_info_block">
            <ul class="print_info_list">
              <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TOUR NAME :</span> <?= $sq_quotation['tour_name'] ?> </li>
              <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TOUR DATE :</span> <?= get_date_user($sq_quotation['from_date']) . ' To ' . get_date_user($sq_quotation['to_date']) ?> </li>
              <li class="col-md-6 mg_tp_10 mg_bt_10"><span>CUSTOMER NAME :</span> <?= $sq_quotation['customer_name'] ?></li>
            </ul>
            <ul class="print_info_list">
              <li class="col-md-6 mg_tp_10 mg_bt_10"><span>E-MAIL ID :</span> <?= $sq_quotation['email_id'] ?></li>
              <li class="col-md-6 mg_tp_10 mg_bt_10"><span>MOBILE NO :</span> <?= $sq_quotation['mobile_number'] ?></li>
            </ul>
            <hr class="main_block">
            <ul class="main_block">
              <li class="col-md-4 mg_tp_10 mg_bt_10"><span>ADULT : </span><?= $sq_quotation['total_adult']+$sq_quotation['single_person'] ?></li>
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

      <!-- bank  -->
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
                  <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BANK NAME :
                    </span><?= ($sq_bank_count > 0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting ?></li>
                  <li class="col-md-12 mg_tp_10 mg_bt_10"><span>A/C TYPE :
                    </span><?= ($sq_bank_count > 0 || $sq_bank_branch['account_type'] != '') ? $sq_bank_branch['account_type'] : $acc_name ?></li>
                  <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BRANCH :
                    </span><?= ($sq_bank_count > 0 || $sq_bank_branch['branch_name'] != '') ? $sq_bank_branch['branch_name'] : $bank_branch_name ?>
                  </li>
                  <li class="col-md-12 mg_tp_10 mg_bt_10"><span>A/C NO :
                    </span><?= ($sq_bank_count > 0 || $sq_bank_branch['account_no'] != '') ? $sq_bank_branch['account_no'] : $bank_acc_no  ?></li>
                  <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BANK ACCOUNT NAME :
                    </span><?= ($sq_bank_count > 0 || $sq_bank_branch['account_name'] != '') ? $sq_bank_branch['account_name'] : $bank_account_name ?></li>
                  <li class="col-md-12 mg_tp_10 mg_bt_10"><span>SWIFT CODE :
                    </span><?= ($sq_bank_count > 0 || $sq_bank_branch['swift_code'] != '') ? strtoupper($sq_bank_branch['swift_code']) :  strtoupper($bank_swift_code) ?></li>
                </ul>
              </div>
              <?php
              if (check_qr()) { ?>
                <div class="col-md-6 text-center" style="margin-top:20px;">
                  <?= get_qr('Protrait Standard') ?>
                  <br>
                  <h4 class="no-marg">Scan & Pay </h4>

                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>


      <!-- bank  -->

    </div>
  </section>
  <?php
  $tour_cost1 = $sq_quotation['tour_cost'];
  $service_charge = $sq_quotation['service_charge'];
  $tour_cost = $tour_cost1 + $service_charge;
  $service_tax_amount = 0;
  $tax_show = '';
  $bsmValues = json_decode($sq_quotation['bsm_values']);
  $name = '';
  if ($sq_quotation['service_tax_subtotal'] !== 0.00 && ($sq_quotation['service_tax_subtotal']) !== '') {
    $service_tax_subtotal1 = explode(',', $sq_quotation['service_tax_subtotal']);
    for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
      $service_tax = explode(':', $service_tax_subtotal1[$i]);
      $service_tax_amount +=  $service_tax[2];
      $name .= $service_tax[0]  . $service_tax[1] . ', ';
    }
  }
  $service_tax_amount_show = currency_conversion($currency, $sq_quotation['currency_code'], $service_tax_amount);

  if ($bsmValues[0]->service != '') {   //inclusive service charge
    $newBasic = $tour_cost + $service_tax_amount;
    $tax_show = '';
  } else {
    // $tax_show = $service_tax_amount;
    $tax_show =  rtrim($name, ', ') . ' : ' . ($service_tax_amount);
    $newBasic = $tour_cost;
  }

  ////////////Basic Amount Rules
  if ($bsmValues[0]->basic != '') { //inclusive markup
    $newBasic = $tour_cost + $service_tax_amount;
    $tax_show = '';
  }
  $newBasic1 = currency_conversion($currency, $sq_quotation['currency_code'], $newBasic);
  ?>
  <!-- Costing -->
  <section class="print_sec main_block side_pad mg_tp_30">
    <div class="row">
      <div class="col-md-12">
        <div class="section_heding">
          <h2>COSTING DETAILS</h2>
          <div class="section_heding_img">
            <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
          </div>
        </div>
        <div class="print_info_block">
          <ul class="main_block">
            <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TOUR COST : </span><?= $newBasic1 ?></li>
            <li class="col-md-12 mg_tp_10 mg_bt_10"><span>TAX : </span><?= str_replace(',', '', $name) . $service_tax_amount_show ?></li>
            <li class="col-md-12 mg_tp_10 mg_bt_10"><span>QUOTATION COST : </span><?= $currency_amount1 ?></li>
        </div>
      </div>


    </div>
  <!-- Traveling Sections -->

    <!-- <section class="print_sec main_block side_pad mg_tp_30"> -->
      <div class="section_heding">
        <h2>Travelling Information</h2>
        <div class="section_heding_img">
          <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
        </div>
      </div>

      <?php
      $sq_train_count = mysqli_num_rows(mysqlQuery("select * from group_tour_quotation_train_entries where quotation_id='$quotation_id'"));
      if ($sq_train_count > 0) { ?>
        <!-- Train -->
        <div class="row">
          <div class="col-md-12 subTitle">
            <h3>Train</h3>
          </div>
          <div class="col-md-12">
            <div class="table-responsive">
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
                  $sq_train = mysqlQuery("select * from group_tour_quotation_train_entries where quotation_id='$quotation_id'");
                  while ($row_train = mysqli_fetch_assoc($sq_train)) {
                  ?>
                    <tr>
                      <td><?= $row_train['from_location'] ?></td>
                      <td><?= $row_train['to_location'] ?></td>
                      <td><?= $row_train['class'] ?></td>
                      <td><?= get_datetime_user($row_train['departure_date']) ?></td>
                      <td><?= get_datetime_user($row_train['arrival_date']) ?></td>
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
      $sq_plane_count = mysqli_num_rows(mysqlQuery("select * from group_tour_quotation_plane_entries where quotation_id='$quotation_id'"));
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
                  $sq_plane = mysqlQuery("select * from group_tour_quotation_plane_entries where quotation_id='$quotation_id'");
                  while ($row_plane = mysqli_fetch_assoc($sq_plane)) {
                    $sq_airline = mysqli_fetch_assoc(mysqlQuery("select * from airline_master where airline_id='$row_plane[airline_name]'"));
                  ?>
                    <tr>
                      <td><?= $row_plane['from_location'] ?></td>
                      <td><?= $row_plane['to_location'] ?></td>
                      <td><?= $sq_airline['airline_name'] . ' (' . $sq_airline['airline_code'] . ')' ?></td>
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
      <?php
      $sq_h_count = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_hotel_entries where tour_id='$sq_quotation[tour_group_id]'"));
      if ($sq_h_count != '0') {
      ?>
        <div class="main_block mg_tp_30"></div>
        <h3 class="editor_title main_block">Hotel Details</h3>
        <table class="table table-bordered">
          <thead>
            <tr class="table-heading-row">
              <th>City Name</th>
              <th>Hotel Name</th>
              <th>Hotel Category</th>
              <th>Total Nights</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $count = 0;
            $sq_hotel = mysqlQuery("select * from group_tour_hotel_entries where tour_id='$sq_quotation[tour_group_id]'");
            while ($row_hotel = mysqli_fetch_assoc($sq_hotel)) {
            ?>
              <tr>
                <td><?php
                    $city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id = " . $row_hotel['city_id']));
                    echo $city['city_name'] ?></td>
                <td><?php
                    $hotel = mysqli_fetch_assoc(mysqlQuery("select hotel_name from hotel_master where hotel_id = " . $row_hotel['hotel_id']));
                    echo $hotel['hotel_name'] ?></td>
                <td><?= $row_hotel['hotel_type'] ?></td>
                <td><?= $row_hotel['total_nights'] ?></td>
              </tr>
            <?php
            }
            ?>
          </tbody>
        </table>
      <?php } ?>

      <!-- Cruise -->
      <?php
      $sq_cr_count = mysqli_num_rows(mysqlQuery("select * from group_tour_quotation_cruise_entries where quotation_id='$quotation_id'"));
      if ($sq_cr_count > 0) { ?>
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
                  $sq_cruise = mysqlQuery("select * from group_tour_quotation_cruise_entries where quotation_id='$quotation_id'");
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
    <!-- </section> -->
    <?php } ?>

  </section>

  <!-- Tour Itinenary -->
  <section class="print_sec main_block side_pad mg_tp_30">
    <div class="vitinerary_div">
      <h6>Destination Guide Video</h6>
      <img src="<?php echo BASE_URL . 'images/quotation/youtube-icon.png'; ?>" class="itinerary-img img-responsive"><br />
      <a href="<?= $sq_dest['link'] ?>" class="no-marg" target="_blank"></a>
    </div>
    <div class="section_heding">
      <h2>TOUR ITINERARY</h2>
      <div class="section_heding_img">
        <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="print_itinenary main_block no-pad no-marg">

          <?php
          $count = 1;
          $i = 0;
          $dates = (array) get_dates_for_tour_itineary($quotation_id);
          while ($row_itinarary = mysqli_fetch_assoc($sq_package_program)) {
            $date_format = isset($dates[$i]) ? $dates[$i] : 'NA';
          ?>
            <section class="print_single_itinenary main_block">
              <div class="print_itinenary_count print_info_block" style="width:200px;">DAY - <?= $count ?> <b>(<?php echo $date_format ?>) </b> </div>
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



  <!-- Inclusion -->
  <section class="print_sec main_block side_pad mg_tp_30">
    <?php if ($sq_quotation['incl'] != '') { ?>
      <div class="row">
        <div class="col-md-6">
          <div class="section_heding">
            <h2>Inclusions</h2>
            <div class="section_heding_img">
              <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
            </div>
          </div>
          <div class="print_text_bolck">
            <?= $sq_quotation['incl'] ?>
          </div>
        </div>
      <?php } ?>


      <!-- Exclusion -->
      <?php if ($sq_quotation['excl'] != '') { ?>
        <div class="col-md-6">
          <div class="section_heding">
            <h2>Exclusions</h2>
            <div class="section_heding_img">
              <img src="<?php echo BASE_URL . 'images/heading_border.png'; ?>" class="img-responsive">
            </div>
          </div>
          <div class="print_text_bolck">
            <?= $sq_quotation['excl'] ?>
          </div>
        </div>
      </div>
    <?php } ?>
  </section>



  <!-- Terms and Conditions -->
  <?php if (isset($sq_terms_cond['terms_and_conditions'])) { ?>
    <section class="print_sec main_block side_pad mg_tp_30">
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
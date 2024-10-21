<?php
include "../../../../model.php";
require('../../../../../classes/html2text-master/html2text.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>.</title>
	  <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,500" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL ?>css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>css/app/admin.php">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>css/app/app.php">
    <link rel="stylesheet" media="print" href="<?= BASE_URL ?>css/print/quotationGeneric.php"/>
    <link rel="stylesheet" media="print" href="<?= BASE_URL ?>css/print/printQuotationtwo/quotationPrint.php"/>
	  <link rel="stylesheet" media="print" href="<?= BASE_URL ?>css/print/printQuotationtwo/quotationPdf.css"/>

    <script src="<?= BASE_URL ?>js/jquery-3.1.0.min.js"></script>
    <script src="<?= BASE_URL ?>js/jquery-ui.min.js"></script>
    <script src="<?= BASE_URL ?>js/bootstrap.min.js"></script>
</head>
<body>
<?php
global $app_quot_img,$similar_text,$quot_note;
$quotation_id1 = $_GET['quotation_id'];

$quotation_id = base64_decode($quotation_id1);
$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from b2b_quotations where quotation_id='$quotation_id'"));
$created_at = get_date_user($sq_quotation['created_at']);

$pdf_data_array = ($sq_quotation['pdf_data_array']!='')?json_decode($sq_quotation['pdf_data_array']):[];
$cart_list_arr = ($sq_quotation['cart_list_arr']!='')?json_decode($sq_quotation['cart_list_arr']):[];
$quotation_currency = $sq_quotation['currency'];

$register_id = $pdf_data_array[0]->register_id;
$markup_in = $pdf_data_array[0]->markup_in;
$markup_amount = $pdf_data_array[0]->markup_amount;
$tax_in = $pdf_data_array[0]->tax_in;
$tax_amount = $pdf_data_array[0]->tax_amount;
$grand_total = $pdf_data_array[0]->grand_total;
if($markup_in == 'Percentage'){
    $markup = $grand_total*($markup_amount/100);
}
else{
    $markup = $markup_amount;
}
$grand_total += $markup;
if($tax_in == 'Percentage'){
    $tax_amt = ($grand_total*($tax_amount/100));
}
else{
    $tax_amt = $tax_amount;
}

$quotation_cost = $grand_total + $tax_amt;
$sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select terms_and_conditions from terms_and_conditions where type='B2B Quotation' and active_flag ='Active'"));
$sq_incl = mysqli_fetch_assoc(mysqlQuery("select inclusion from inclusions_exclusions_master where for_value='B2B Quotation' and active_flag ='Active' and type='Inclusion'"));
$sq_excl = mysqli_fetch_assoc(mysqlQuery("select inclusion from inclusions_exclusions_master where for_value='B2B Quotation' and active_flag ='Active' and type='Exclusion'"));
$sq_qcurr = mysqli_fetch_assoc(mysqlQuery("select id,default_currency from currency_name_master where id='$quotation_currency'"));
$default_currency = $sq_qcurr['default_currency'];

$hotel_list_arr = array();
$transfer_list_arr = array();
$activity_list_arr = array();
$tours_list_arr = array();
$ferry_list_arr = array();
$group_list_arr = array();
for($i=0;$i<sizeof($cart_list_arr);$i++){
    if($cart_list_arr[$i]->service->name == 'Hotel'){
        array_push($hotel_list_arr,$cart_list_arr[$i]);
    }
    if($cart_list_arr[$i]->service->name == 'Transfer'){
        array_push($transfer_list_arr,$cart_list_arr[$i]);
    }
    if($cart_list_arr[$i]->service->name == 'Activity'){
        array_push($activity_list_arr,$cart_list_arr[$i]);
    }
    if($cart_list_arr[$i]->service->name == 'Combo Tours'){
        array_push($tours_list_arr,$cart_list_arr[$i]);
    }
    if($cart_list_arr[$i]->service->name == 'Ferry'){
      array_push($ferry_list_arr,$cart_list_arr[$i]);
    }
    if($cart_list_arr[$i]->service->name == 'Group Tours'){
      array_push($group_list_arr,$cart_list_arr[$i]);
    }
}
$sq_reg = mysqli_fetch_assoc(mysqlQuery("select * from b2b_registration where register_id='$register_id'"));
$prepare_by = $sq_reg['company_name'];
?>
    <!-- landingPage -->
    <section class="landingSec main_block">
        <div class="landingPageTop main_block">
            <img src="https://itourscloud.com/quotation_format_images/Landscape-Standard-Creative/13.jpg" class="img-responsive">
        </div>
        <div class="ladingPageBottom main_block side_pad">

        <div class="row">
            <div class="col-md-4">
                <div class="landigPageCustomer mg_tp_20">
                <h3 class="customerFrom">PREPARED FOR</h3>
                <span class="customerName mg_tp_10"><i class="fa fa-user"></i> : <?= $pdf_data_array[0]->cust_name ?></span><br>
                <span class="customerMail mg_tp_10"><i class="fa fa-envelope"></i> : <?= $pdf_data_array[0]->email_id ?></span><br>
                <span class="customerMobile mg_tp_10"><i class="fa fa-phone"></i> : <?= $pdf_data_array[0]->contact_no ?></span><br>
                </div>
            </div>
            <div class="col-md-8 text-right">

            <div class="detailBlock text-center">
                <div class="detailBlockIcon detailBlockBlue">
                <i class="fa fa-calendar"></i>
                </div>
                <div class="detailBlockContent">
                <h3 class="contentValue"><?= $created_at ?></h3>
                <span class="contentLabel">QUOTATION DATE</span>
                </div>
            </div>

            <div class="detailBlock text-center">
                <div class="detailBlockIcon detailBlockRed">
                <i class="fa fa-tag"></i>
                </div>
                <div class="detailBlockContent">
                <h3 class="contentValue"><?= $sq_qcurr['default_currency'].' '.number_format($quotation_cost,2) ?></h3>
                <span class="contentLabel">AMOUNT</span>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>
          <!-- Hotel -->
          <?php
          if(sizeof($hotel_list_arr)>0){
          ?>
          <section class="transportDetails main_block side_pad mg_tp_30">
            <div class="row">
              <div class="col-md-3">
                <div class="transportImg">
                  <img src="<?= BASE_URL ?>images/quotation/hotel.png" class="img-responsive">
                </div>
              </div>
              <div class="col-md-12">
                <div class="table-responsive mg_tp_30">
                  <table class="table table-bordered no-marg" id="tbl_emp_list">
                    <thead>
                      <tr class="table-heading-row">
                      <th>City</th>
                      <th>Hotel Name</th>
                      <th>Total Rooms</th>
                      <th>Check-In</th>
                      <th>Check-Out</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
				            for($i=0;$i<sizeof($hotel_list_arr);$i++){
                      $tax_arr = explode(',',$hotel_list_arr[$i]->service->hotel_arr->tax);
                      $hotel_id = $hotel_list_arr[$i]->service->id;
                      $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select city_id from hotel_master where hotel_id='$hotel_id'"));
                      $sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$sq_hotel[city_id]'"));
                      ?>
                      <tr>
                          <td><?php echo $sq_city['city_name']; ?></td>
                          <td><?= $hotel_list_arr[$i]->service->hotel_arr->hotel_name.$similar_text; ?></td>
                          <td><?= sizeof($hotel_list_arr[$i]->service->item_arr) ?></td>
                          <td><?= get_date_user($hotel_list_arr[$i]->service->check_in) ?></td>
                          <td><?= get_date_user($hotel_list_arr[$i]->service->check_out) ?></td>
                        </tr>
                      <?php }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div> 
            </div>
          </section>
          <?php }?>
          <!-- transfer -->
          <?php
          if(sizeof($transfer_list_arr)>0){?>
          <section class="transportDetails main_block side_pad mg_tp_30">
            <div class="row mg_tp_30">
              <div class="col-md-3">
                <div class="transportImg">
                  <img src="<?= BASE_URL ?>images/quotation/car.png" class="img-responsive">
                </div>
              </div>
              <div class="col-md-12">
                <div class="table-responsive mg_tp_30">
                  <table class="table table-bordered no-marg" id="tbl_emp_list">
                    <thead>
                      <tr class="table-heading-row">
                        <th>VEHICLE</th>
                        <th>Passengers</th>
                        <th>Pickup</th>
                        <th>Dropoff</th>
                        <th>Pickup_DateTime</th>
                        <th>Return_DateTime</th>
                        <th>Vehicles</th>
                        <th>S_duration</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
				            for($i=0;$i<sizeof($transfer_list_arr);$i++){
                      //Pickup n drop location
                      $pickup_id = $transfer_list_arr[$i]->service->service_arr[0]->pickup_from;
                      if($transfer_list_arr[$i]->service->service_arr[0]->pickup_type == 'city'){
                        $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$pickup_id'"));
                        $pickup = $row['city_name'];
                      }
                      else if($transfer_list_arr[$i]->service->service_arr[0]->pickup_type == 'hotel'){
                        $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$pickup_id'"));
                        $pickup = $row['hotel_name'];
                      }
                      else{
                        $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$pickup_id'"));
                        $airport_nam = clean($row['airport_name']);
                        $airport_code = clean($row['airport_code']);
                        $pickup = $airport_nam." (".$airport_code.")";
                      }
                      //Drop-off
                      $drop_id = $transfer_list_arr[$i]->service->service_arr[0]->drop_to;
                      if($transfer_list_arr[$i]->service->service_arr[0]->drop_type == 'city'){
                        $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$drop_id'"));
                        $drop = $row['city_name'];
                      }
                      else if($transfer_list_arr[$i]->service->service_arr[0]->drop_type == 'hotel'){
                        $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$drop_id'"));
                        $drop = $row['hotel_name'];
                      }
                      else{
                        $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$drop_id'"));
                        $airport_nam = clean($row['airport_name']);
                        $airport_code = clean($row['airport_code']);
                        $drop = $airport_nam." (".$airport_code.")";
                      }
                      ?>
                      <tr>
                        <td><?= $transfer_list_arr[$i]->service->service_arr[0]->vehicle_name . $similar_text ?></td>
                        <td><?= $transfer_list_arr[$i]->service->service_arr[0]->passengers ?></td>
                        <td><?= $pickup ?></td>
                        <td><?= $drop ?></td>
                        <td><?= get_datetime_user($transfer_list_arr[$i]->service->service_arr[0]->pickup_date) ?></td>
                        <td><?= ($transfer_list_arr[$i]->service->service_arr[0]->return_date != '') ? get_datetime_user($transfer_list_arr[$i]->service->service_arr[0]->return_date) : 'NA' ?></td>
                        <td><?= $transfer_list_arr[$i]->service->service_arr[0]->no_of_vehicles ?></td>
                        <td><?= $transfer_list_arr[$i]->service->service_arr[0]->duration ?></td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </section>
          <?php }?>
          <!-- Excursion -->
          <?php
          if(sizeof($activity_list_arr)>0){ ?>
          <section class="transportDetails main_block side_pad mg_tp_30">
            <div class="row">
              <div class="col-md-3">
                <div class="transportImg">
                  <img src="<?= BASE_URL ?>images/quotation/excursion.png" class="img-responsive">
                </div>
              </div>
              <div class="col-md-12">
                <div class="table-responsive mg_tp_30">
                  <table class="table table-bordered no-marg" id="tbl_emp_list">
                    <thead>
                      <tr class="table-heading-row">
                        <th>Activity_name</th>
                        <th>Date</th>
                        <th>Transfer_type</th>
                        <th>Reporting_Time</th>
                        <th>Pickup_Point</th>
                        <th>Total_Pax</th>
                        <th>Timing_Slot</th>
                        <th>Total_vehicle</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $count = 0;
                      for($i=0;$i<sizeof($activity_list_arr);$i++){
                        $taxation_arr = explode('-',$activity_list_arr[$i]->service->service_arr[0]->taxation);
                        $transfer_type = explode('-',$activity_list_arr[$i]->service->service_arr[0]->transfer_type);
                        ?>
                        <tr>
                          <td><?= $activity_list_arr[$i]->service->service_arr[0]->act_name ?></td>
                          <td><?= $activity_list_arr[$i]->service->service_arr[0]->checkDate ?></td>
                          <td><?= $transfer_type[0] ?></td>
                          <td><?= ($activity_list_arr[$i]->service->service_arr[0]->rep_time != '') ? $activity_list_arr[$i]->service->service_arr[0]->rep_time : 'NA' ?></td>
                          <td><?= $activity_list_arr[$i]->service->service_arr[0]->pick_point ?></td>
                          <td><?= $activity_list_arr[$i]->service->service_arr[0]->total_pax ?></td>
                          <td><?= ($pdf_data_array[0]->timing_slots[$i] != '')?$pdf_data_array[0]->timing_slots[$i]:'NA' ?></td>
                          <td><?= ($activity_list_arr[$i]->service->service_arr[0]->vehicles!='')?$activity_list_arr[$i]->service->service_arr[0]->vehicles : 'NA' ?></td>
                        </tr>
                      <?php }	?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </section>
          <?php } ?>
          <?php
          if(sizeof($tours_list_arr)>0){

            $count = 0;
            for($i=0;$i<sizeof($tours_list_arr);$i++){
              $taxation_arr = explode('-',$tours_list_arr[$i]->service->service_arr[0]->taxation);
              $package_id1 = $tours_list_arr[$i]->service->service_arr[0]->id;
              $sq_package = mysqli_fetch_assoc(mysqlQuery("select total_days from custom_package_master where package_id='$package_id1'"));
              $total_days = $sq_package['total_days'];

		          $travel_date = date('d-m-Y',strtotime($tours_list_arr[$i]->service->service_arr[0]->travel_date));
              $end_date = date('d-m-Y', strtotime('+ ' . $total_days . ' days', strtotime($travel_date)));
              ?>
              <section class="transportDetails main_block side_pad mg_tp_30">
                <div class="row">
                  <div class="col-md-3">
                    <div class="transportImg">
                      <img src="<?= BASE_URL ?>images/quotation/excursion.png" class="img-responsive">
                    </div>
                  </div>
                  <div class="col-md-8 mg_tp_30">
                    <h4>Package Name : <?= $tours_list_arr[$i]->service->service_arr[0]->package.'('.$tours_list_arr[$i]->service->service_arr[0]->package_code.')'?></h4>
                    <h4>Travel Date : <?= $travel_date ?></h4>
                    <h4>Total Pax : <?= $tours_list_arr[$i]->service->service_arr[0]->adult+$tours_list_arr[$i]->service->service_arr[0]->childwo+$tours_list_arr[$i]->service->service_arr[0]->childwi+$tours_list_arr[$i]->service->service_arr[0]->infant ?>&nbsp;&nbsp;&nbsp; Extra Bed : <?= $tours_list_arr[$i]->service->service_arr[0]->extra_bed ?></h4>
                    <h4>Note : <?= $tours_list_arr[$i]->service->service_arr[0]->note ?></h4>
                  </div>
                </div>
                  <?php
                  $sq_hotelc = mysqli_num_rows(mysqlQuery("select * from custom_package_hotels where package_id='$package_id1'"));
                  if($sq_hotelc!=0){ ?>
                    <!-- traveling Information -->
                    <section class="travelingDetails main_block mg_tp_30">
                        <h3 class="customerFrom text-center">Hotel Details</h3>
                        <!-- Hotel -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="table-responsive">
                              <table width="300!important;" class="table table-bordered" id="tbl_emp_list">
                                <thead>
                                  <tr class="table-heading-row">
                                  <th>City</th>
                                  <th>Hotel Name</th>
                                  <th>Total Nights</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php
                                  $sq_hotel = mysqlQuery("select * from custom_package_hotels where package_id='$package_id1'");
                                  while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
                                    $hotel_name = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$row_hotel[hotel_name]'"));
                                    $city_name = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_hotel[city_name]'"));
                                    ?>
                                    <tr>
                                      <td><?php echo $city_name['city_name']; ?></td>
                                      <td><?php echo $hotel_name['hotel_name'].$similar_text; ?></td>
                                      <td></span><?php echo $row_hotel['total_days']; ?></td>
                                    </tr>
                                    <?php
                                  } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>  
                        </div>
                      </section>
                  <?php } ?>
                  <!-- traveling Information -->
                  <?php    
                  $sq_hotelc = mysqli_num_rows(mysqlQuery("select * from custom_package_transport where package_id='$package_id1'"));
                  if($sq_hotelc!=0){?>
                    <section class="travelingDetails main_block">
                    <h3 class="customerFrom text-center">Transport Details</h3>
                        <!-- Transport -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="table-responsive">
                              <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                  <tr class="table-heading-row">
                                    <th>VEHICLE</th>
                                    <th>Pickup</th>
                                    <th>Drop</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php 
                                  $count = 0;
                                  $sq_hotel = mysqlQuery("select * from custom_package_transport where package_id='$package_id1'");
                                  while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
                                    $transport_name = mysqli_fetch_assoc(mysqlQuery("select * from b2b_transfer_master where entry_id ='$row_hotel[vehicle_name]'"));
                                    // Pickup
                                    if($row_hotel['pickup_type'] == 'city'){
                                      $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_hotel[pickup]'"));
                                      $pickup = $row['city_name'];
                                    }
                                    else if($row_hotel['pickup_type'] == 'hotel'){
                                      $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_hotel[pickup]'"));
                                      $pickup = $row['hotel_name'];
                                    }
                                    else{
                                      $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_hotel[pickup]'"));
                                      $airport_nam = clean($row['airport_name']);
                                      $airport_code = clean($row['airport_code']);
                                      $pickup = $airport_nam." (".$airport_code.")";
                                      $html = '<optgroup value="airport" label="Airport Name"><option value="'.$row['airport_id'].'">'.$pickup.'</option></optgroup>';
                                    }
                                    // Drop
                                    if($row_hotel['drop_type'] == 'city'){
                                      $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_hotel[drop]'"));
                                      $drop = $row['city_name'];
                                    }
                                    else if($row_hotel['drop_type'] == 'hotel'){
                                      $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_hotel[drop]'"));
                                      $drop = $row['hotel_name'];
                                    }
                                    else{
                                      $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_hotel[drop]'"));
                                      $airport_nam = clean($row['airport_name']);
                                      $airport_code = clean($row['airport_code']);
                                      $drop = $airport_nam." (".$airport_code.")";
                                      $html = '<optgroup value="airport" label="Airport Name"><option value="'.$row['airport_id'].'">'.$pickup.'</option></optgroup>';
                                    }
                                    ?>
                                    <tr>
                                      <td><?= $transport_name['vehicle_name'].$similar_text ?></td>
                                      <td><?= $pickup ?></td>
                                      <td><?= $drop ?></td>
                                    </tr>
                                  <?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>  
                        </div>
                      </section>
                    <?php
                  } ?>
                  <!-- Itinerary -->
                    <?php
                    $sq_package_name = mysqli_fetch_assoc(mysqlQuery("select dest_id from custom_package_master where package_id = '$package_id1'"));
                    $sq_dest = mysqli_fetch_assoc(mysqlQuery("select link from video_itinerary_master where dest_id = '$sq_package_name[dest_id]'"));
                    ?>
                    <section class="itinerarySec main_block side_pad mg_tp_30">
                      <div class="vitinerary_div">
                        <h6>Destination Guide Video</h6>
                        <img src="<?php echo BASE_URL.'images/quotation/youtube-icon.png'; ?>" class="itinerary-img img-responsive"><br/>
                        <a href="<?=$sq_dest['link']?>" class="no-marg" target="_blank"></a>
                      </div> 
                      <ul class="print_itinenary main_block no-pad no-marg">
                        <?php
                        $result = get_dates_for_itineary($travel_date, $end_date);
                        $iti_dates_days = explode('=', $result);
                        $iti_dates = json_decode($iti_dates_days[0]);
                        $iti_days = json_decode($iti_dates_days[1]);
                        $sq_package_program = mysqlQuery("select attraction,stay,meal_plan,day_wise_program from custom_package_program where package_id = '$package_id1'");
                        $count = 1;
                        $k = 0;
                        while($row_itinarary = mysqli_fetch_assoc($sq_package_program)){
  
                          $date_format = isset($iti_dates[$k]) ? $iti_dates[$k] : 'NA';
                          $day = isset($iti_days[$k]) ? $iti_days[$k] : '';
                          ?>
                          <li class="singleItinenrary leftItinerary col-md-12 no-pad">
                            <div class="itneraryContent col-md-12 no-pad text-right mg_tp_20 mg_bt_20">
                              <div class="itneraryImg col-md-4 no-pad">
                                <img src="http://itourscloud.com/quotation_format_images/dummy-image.jpg" class="img-responsive" style="display: block;">
                                <div style="display: flex; margin-top: 20px; align-items: center;">
                                  <div class="dayCount" style="position: static; ">
                                    <span>Day-<?= $count . ' ' . $day . ' (' . $date_format . ')' ?></span>
                                  </div>
                                  <div class="itineraryDetail" style="position: static; margin-right:auto;">
                                    <ul class="no-marg no-pad" style="margin-top: 15px; display: flex;">
                                      <li><span><i class="fa fa-bed"></i> : </span><?=  $row_itinarary['stay'] ?></li>
                                      <?php if($row_itinarary['meal_plan'] != '') { ?><li><span><i class="fa fa-cutlery"></i> : </span><?= $row_itinarary['meal_plan'] ?></li> <?php } ?>
                                    </ul>
                                  </div>
                                  
                                </div>
                              </div>
                              <div class="itneraryText col-md-8 no-pad" style="padding-left: 20px;">
                                <h5 style="padding-left: 50px;" class="specialAttraction no-marg text-left"><?= $row_itinarary['attraction'] ?></h5>
                                <p style="margin-left: 50px;" class="text-left"><?= $row_itinarary['day_wise_program'] ?></p>
                              </div>
                            </div>
                          </li>
                        <?php $count++; $k++; } ?>
                      </ul>
                    </section>
            <?php
            } ?>
            <?php 
          } ?>
          <!--  Ferry -->
          <?php if(sizeof($ferry_list_arr)>0){ ?>
            <section class="transportDetails main_block side_pad mg_tp_30">
              <div class="row">
                <div class="col-md-3">
                  <div class="transportImg">
                    <img src="<?= BASE_URL ?>images/quotation/cruise.png" class="img-responsive">
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="table-responsive mg_tp_30">
                    <table class="table table-bordered no-marg" id="tbl_emp_list">
                      <thead>
                        <tr class="table-heading-row">
                          <th>Ferry_name(Type)</th>
                          <th>Ferry_class</th>
                          <th>Departure_DateTime</th>
                          <th>Arrival_DateTime</th>
                          <th>Total_Pax</th>
                          <th>From_To_Location</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $count = 0;
                        for($i=0;$i<sizeof($ferry_list_arr);$i++){
                          
                          $total_pax = intval($ferry_list_arr[$i]->service->service_arr[0]->adults) + intval($ferry_list_arr[$i]->service->service_arr[0]->children) + intval($ferry_list_arr[$i]->service->service_arr[0]->infants);
                          ?>
                          <tr>
                            <td><?= $ferry_list_arr[$i]->service->service_arr[0]->ferry_name ?></td>
                            <td><?= $ferry_list_arr[$i]->service->service_arr[0]->ferry_type ?></td>
                            <td><?= date('m-d-Y H:i', strtotime($ferry_list_arr[$i]->service->service_arr[0]->dep_date)) ?></td>
                            <td><?= date('m-d-Y H:i', strtotime($ferry_list_arr[$i]->service->service_arr[0]->arr_date)) ?></td>
                            <td><?= $total_pax ?></td>
                            <td><?= $ferry_list_arr[$i]->service->service_arr[0]->from_loc_city.' To '.$ferry_list_arr[$i]->service->service_arr[0]->to_loc_city ?></td>
                          </tr>
                        <?php }	?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </section>
          <?php
          }
          //Group Tours
          $currency = $_POST['currency'];
          $sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
          $to_currency_rate = $sq_to['currency_rate'];
          if(sizeof($group_list_arr)>0){
            
              $count = 0;
              for($i=0;$i<sizeof($group_list_arr);$i++){
      
                $services = isset($group_list_arr[$i]->service) ? $group_list_arr[$i]->service : [];
                for($j=0;$j<count(array($services));$j++){
      
                  $tour_id = $group_list_arr[$i]->service->id;
                  $sq_tour = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id='$tour_id'"));
                  
                  $total_cost = 0;
                  $tax_amount = 0;
                  $total_cost1 = 0;
                  $package_id1 = $group_list_arr[$i]->service->service_arr[$j]->id;
                  $tour_group = explode('=',$group_list_arr[$i]->service->service_arr[$j]->tour_group);
                  $taxation_arr = explode(',',$group_list_arr[$i]->service->service_arr[$j]->taxation);
                  $room_cost = $group_list_arr[$i]->service->service_arr[$j]->total_cost;
                  $h_currency_id = $group_list_arr[$i]->service->service_arr[$j]->currency_id;

                  $pp_adult_cost = $group_list_arr[$i]->service->service_arr[$j]->pp_adult_cost;
                  $pp_child_without_cost = $group_list_arr[$i]->service->service_arr[$j]->pp_child_without_cost;
                  $pp_child_with_cost = $group_list_arr[$i]->service->service_arr[$j]->pp_child_with_cost;
                  $pp_with_bed_cost = $group_list_arr[$i]->service->service_arr[$j]->pp_with_bed_cost;
                  $pp_infant_cost = $group_list_arr[$i]->service->service_arr[$j]->pp_infant_cost;
                  $pp_extrabed_cost = $group_list_arr[$i]->service->service_arr[$j]->pp_extrabed_cost;

                  $tax_arr1 = explode('+',$taxation_arr[0]);
                  for($t=0;$t<sizeof($tax_arr1);$t++){
                    if($tax_arr1[$t]!=''){
                      $tax_arr2 = explode(':',$tax_arr1[$t]);
                      if($tax_arr2[2] == "Percentage"){
                        $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                      }else{
                        $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
                      }
                    }
                  }
                  $total_amount = $room_cost + $tax_amount;
                  ?>
                  <section class="travelingDetails main_block mg_tp_30">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="transportImg">
                          <img src="<?= BASE_URL ?>images/quotation/excursion.png" class="img-responsive">
                        </div>
                      </div>
                      <div class="col-md-8 mg_tp_30">
                        <h4>Tour Name : <?= $group_list_arr[$i]->service->service_arr[$j]->tour_name?></h4>
                        <h4>Travel Date : <?= $tour_group[1] ?>&nbsp;&nbsp;&nbsp;Total Pax : <?= intval($group_list_arr[$i]->service->service_arr[$j]->adult)+intval($group_list_arr[$i]->service->service_arr[$j]->childwo)+intval($group_list_arr[$i]->service->service_arr[$j]->childwi)+intval($group_list_arr[$i]->service->service_arr[$j]->infant)+intval($group_list_arr[$i]->service->service_arr[$j]->extra_bed)?></h4>
        
                        <h4 style="color: red !important;">Basic Cost : <?= $default_currency." ".number_format($room_cost,2) ?>&nbsp;&nbsp;&nbsp;Tax : <?= $default_currency." ".number_format($tax_amount,2) ?> &nbsp;&nbsp;Total Cost : <?= $default_currency." ".number_format($total_amount,2) ?></h4>
                        <?php if($group_list_arr[$i]->service->service_arr[$j]->adult){ ?><h4 style="color: red !important;">Adult(PP): <?= $default_currency." ".number_format($pp_adult_cost,2) ?><?php } ?>
                        <?php if(intval($group_list_arr[$i]->service->service_arr[$j]->childwi) > 0){?> &nbsp;&nbsp; Child With Bed(PP): <?= $default_currency." ".number_format($pp_child_with_cost,2) ?><?php } ?></h4>
                        <?php if(intval($group_list_arr[$i]->service->service_arr[$j]->childwo) > 0){?><h4 style="color: red !important;"> Child W/o Bed(PP): <?= $default_currency." ".number_format($pp_child_without_cost,2) ?><?php } ?>
                        <?php if(intval($group_list_arr[$i]->service->service_arr[$j]->infant) > 0){?> &nbsp;&nbsp; Infant(PP): <?= $default_currency." ".number_format($pp_infant_cost,2) ?><?php } ?></h4>
                        <?php if(intval($group_list_arr[$i]->service->service_arr[$j]->extra_bed) > 0){?><h4 style="color: red !important;"> Extra Bed(PP): <?= $default_currency." ".number_format($pp_extrabed_cost,2) ?><?php } ?></h4>
                      </div>
                    </div>
                    <?php
                  $sq_hotelc = mysqli_num_rows(mysqlQuery("select * from group_tour_hotel_entries where tour_id = '$tour_id'"));
                  if($sq_hotelc!=0){ ?>
                    <!-- traveling Information -->
                    <section class="travelingDetails main_block side_pad mg_tp_30">
                        <h3 class="customerFrom text-center">Hotel Details</h3>
                        <!-- Hotel -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="table-responsive">
                              <table width="300!important;" class="table table-bordered" id="tbl_emp_list">
                                <thead>
                                  <tr class="table-heading-row">
                                  <th>City</th>
                                  <th>Hotel Name</th>
                                  <th>Hotel Category</th>
                                  <th>Total Nights</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php
                                  $sq_hotel = mysqlQuery("select * from group_tour_hotel_entries where tour_id='$tour_id'");
                                  while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
                                    $sq_hcity = mysqli_fetch_assoc(mysqlQuery("select city_name,city_id from city_master where city_id = '$row_hotel[city_id]'"));
                                    $sq_hhotel = mysqli_fetch_assoc(mysqlQuery("select hotel_name,hotel_id from hotel_master where hotel_id = '$row_hotel[hotel_id]'"));
                                    ?>
                                    <tr>
                                      <td><?php echo $sq_hcity['city_name']; ?></td>
                                      <td><?php echo $sq_hhotel['hotel_name'].$similar_text; ?></td>
                                      <td></span><?php echo $row_hotel['hotel_type']; ?></td>
                                      <td></span><?php echo $row_hotel['total_nights']; ?></td>
                                    </tr>
                                    <?php
                                  } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>  
                        </div>
                      </section>
                  <?php } ?>
                  <!-- traveling Information -->
                  <?php    
                  $sq_hotelc = mysqli_num_rows(mysqlQuery("select * from group_train_entries where tour_id = '$tour_id'"));
                  if($sq_hotelc!=0){?>
                    <section class="travelingDetails main_block side_pad mg_tp_30">
                    <h3 class="customerFrom text-center">Train Details</h3>
                        <!-- Transport -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="table-responsive">
                              <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                  <tr class="table-heading-row">
                                    <th>From_Location</th>
                                    <th>To_Location</th>
                                    <th>Class</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php 
                                  $count = 0;
                                  $sq_hotel = mysqlQuery("select * from group_train_entries where tour_id = '$tour_id'");
                                  while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
                                    ?>
                                    <tr>
                                      <td><?= $row_hotel['from_location'] ?></td>
                                      <td><?= $row_hotel['to_location'] ?></td>
                                      <td><?= $row_hotel['class'] ?></td>
                                    </tr>
                                  <?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>  
                        </div>
                      </section>
                    <?php
                  } ?>
                  <?php
                  $sq_hotelc = mysqli_num_rows(mysqlQuery("select * from group_tour_plane_entries where tour_id = '$tour_id'"));
                  if($sq_hotelc!=0){?>
                    <section class="travelingDetails main_block side_pad mg_tp_30">
                    <h3 class="customerFrom text-center">Flight Details</h3>
                        <!-- Transport -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="table-responsive">
                              <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                  <tr class="table-heading-row">
                                    <th>From Sector</th>
                                    <th>To Sector</th>
                                    <th>Airline</th>
                                    <th>Class</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php 
                                  $count = 0;
                                  $sq_hotel = mysqlQuery("select * from group_tour_plane_entries where tour_id = '$tour_id'");
                                  while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
                                    
                                    $sq_airline = mysqli_fetch_assoc(mysqlQuery("select * from airline_master where airline_id='$row_hotel[airline_name]'"));
                                    $sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$row_hotel[from_city]'"));
                                    $sq_city1 = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$row_hotel[to_city]'"));
                                    ?>
                                    <tr>
                                      <td><?= $sq_city['city_name'].' ('.$row_hotel['from_location'].')' ?></td>
                                      <td><?= $sq_city1['city_name'].' ('.$row_hotel['to_location'].')' ?></td>
                                      <td><?= $sq_airline['airline_name'].' ('.$sq_airline['airline_code'].')' ?></td>
                                      <td><?= $row_hotel['class'] ?></td>
                                    </tr>
                                  <?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>  
                        </div>
                      </section>
                    <?php
                  } ?>
                  <?php
                  $sq_hotelc = mysqli_num_rows(mysqlQuery("select * from group_cruise_entries where tour_id = '$tour_id'"));
                  if($sq_hotelc!=0){?>
                    <section class="travelingDetails main_block side_pad mg_tp_30">
                    <h3 class="customerFrom text-center">Cruise Details</h3>
                        <!-- Transport -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="table-responsive">
                              <table class="table table-bordered no-marg" id="tbl_emp_list">
                                <thead>
                                  <tr class="table-heading-row">
                                    <th>Route</th>
                                    <th>Cabin</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php 
                                  $count = 0;
                                  $sq_hotel = mysqlQuery("select * from group_cruise_entries where tour_id = '$tour_id'");
                                  while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
                                    ?>
                                    <tr>
                                      <td><?= $row_hotel['route'] ?></td>
                                      <td><?= $row_hotel['cabin'] ?></td>
                                    </tr>
                                  <?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>  
                        </div>
                      </section>
                    <?php
                  } ?>
                  </section>
                <!-- Itinerary -->
                <section class="travelingDetails main_block side_pad mg_tp_30">
                  <h3 class="customerFrom text-center">Tour Itinerary</h3>
                  <ul class="print_itinenary main_block no-pad no-marg">
                        <?php
                        $tour_group_dates = explode('To', $tour_group[1]);
                        $result = get_dates_for_itineary($tour_group_dates[0], $tour_group_dates[1]);
                        $iti_dates_days = explode('=', $result);
                        $iti_dates = json_decode($iti_dates_days[0]);
                        $iti_days = json_decode($iti_dates_days[1]);

                        $count = 1;
                        $k = 0;
                        $sq_package_program = mysqlQuery("select * from group_tour_program where tour_id = '$tour_id'");
                        while ($row_itinarary = mysqli_fetch_assoc($sq_package_program)) {

                          $date_format = isset($iti_dates[$k]) ? $iti_dates[$k] : 'NA';
                          $day = isset($iti_days[$k]) ? $iti_days[$k] : '';
                          $img1 = $row_itinarary['daywise_images'];
                          if ($img1 != "") {
                            $pos = strstr($img1, 'uploads');
                            if ($pos != false) {
                              $newUrl1 = preg_replace('/(\/+)/', '/', $img1);
                              $img = BASE_URL . str_replace('../', '', $newUrl1);
                            } else
                              $img = $img1;
                          } else {
                            $img = 'http://itourscloud.com/quotation_format_images/dummy-image.jpg';
                          }
                        
                        ?>
                        <li class="singleItinenrary leftItinerary col-md-12 no-pad">
                          <div class="itneraryContent col-md-12 no-pad text-right mg_tp_20 mg_bt_20">
                            <div class="itneraryImg col-md-4 no-pad">
                              <img src="<?= $img ?>" class="img-responsive" style="display: block;">
                              <div style="display: flex; margin-top: 20px; align-items: center;">
                                <div class="dayCount" style="position: static; ">
                                  <span>Day-<?= $count . ' ' . $day . ' (' . $date_format . ')' ?></span>
                                </div>
                                <div class="itineraryDetail" style="position: static; margin-right:auto;">
                                  <ul class="no-marg no-pad" style="margin-top: 15px; display: flex;">
                                    <li><span><i class="fa fa-bed"></i> : </span><?=  $row_itinarary['stay'] ?></li>
                                    <?php if($row_itinarary['meal_plan'] != '') { ?><li><span><i class="fa fa-cutlery"></i> : </span><?= $row_itinarary['meal_plan'] ?></li> <?php } ?>
                                  </ul>
                                </div>
                                
                              </div>
                            </div>
                            <div class="itneraryText col-md-8 no-pad" style="padding-left: 20px;">
                              <h5 style="padding-left: 50px;" class="specialAttraction no-marg text-left"><?= $row_itinarary['attraction'] ?></h5>
                              <p style="margin-left: 50px;" class="text-left"><?= $row_itinarary['day_wise_program'] ?></p>
                            </div>
                          </div>
                        </li>
                      <?php
                      $count++;
                      $k++;
                    } ?>
                  </ul>
                </section>
      
                <section class="incluExcluTerms main_block">
                  <!-- Inclusion Exclusion -->
                  <div class="incluExclu main_block">
                      <div class="imgPanel">
                        <img src="<?= BASE_URL ?>images/quotation/inexBg.jpg" class="img-responsive">
                        <div class="imgPanelOvelay"></div>
                      </div>
                      <div class="contenPanel main_block side_pad mg_tp_30">
                        <div class="row">
                          <?php if($sq_tour['inclusions'] != ''){ ?>
                          <div class="col-md-6">
                            <h3 class="lgTitle">Inclusions</h3>
                            <pre class="real_text"><?= $sq_tour['inclusions'] ?></pre>
                          </div>
                          <?php } ?>
                          <?php if($sq_tour['exclusions'] != ''){ ?>
                          <div class="col-md-6">
                            <h3 class="lgTitle">Exclusions</h3>
                            <pre class="real_text"><?= $sq_tour['exclusions'] ?></pre>
                          </div>
                          <?php } ?>
                        </div>
                      </div>
                  </div>
                <?php }
                } ?>
                <?php 
              } ?>
      
          <!-- Inclusion -->
          <?php if($sq_incl['inclusion'] != '' && $sq_incl['inclusion'] != ' ' && $sq_incl['inclusion'] != '<div><br></div>'){?>
          <section class="pageSection main_block">
            <!-- background Image -->
              <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">
              <section class="incluExcluTerms pageSectionInner main_block mg_tp_30">
                <!-- Inclusion -->
                <div class="row">
                  <?php if($sq_incl['inclusion'] != '' && $sq_incl['inclusion'] != ' ' && $sq_incl['inclusion'] != '<div><br></div>'){?>
                  <div class="col-md-12">
                    <div class="incluExcluTermsTabPanel inclusions main_block">
                        <h3 class="lgTitle" style="margin-left:20px!important;">Inclusions</h3>
                        <div class="tabContent">
                            <pre class="real_text"><?= $sq_incl['inclusion'] ?></pre>
                        </div>
                    </div>
                  </div>
                  <?php } ?>
                </div>
              </section>
          </section>
          <?php } ?>

          <!-- Exclusion -->
          <?php if($sq_excl['inclusion'] != '' && $sq_excl['inclusion'] != ' ' && $sq_excl['inclusion'] != '<div><br></div>'){?>
          <section class="pageSection main_block">
            <!-- background Image -->
              <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">
              <section class="incluExcluTerms pageSectionInner main_block mg_tp_30">
                <!-- Exclusion -->
                <div class="row">
                  <?php if($sq_excl['inclusion'] != '' && $sq_excl['inclusion'] != ' ' && $sq_excl['inclusion'] != '<div><br></div>'){?>
                  <div class="col-md-12">
                    <div class="incluExcluTermsTabPanel exclusions main_block">
                        <h3 class="lgTitle" style="margin-left:20px!important;">Exclusions</h3>
                        <div class="tabContent">
                            <pre class="real_text"><?= $sq_excl['inclusion'] ?></pre>
                        </div>
                    </div>
                  </div>
                  <?php } ?>
                </div>
              </section>
          </section>
          <?php } ?>
          
          <?php if($sq_terms_cond['terms_and_conditions']!=''){?>
          <section class="pageSection main_block">
              <img src="<?= BASE_URL ?>images/quotation/p6/pageBGF.jpg" class="img-responsive pageBGImg">
              <section class="incluExcluTerms pageSectionInner main_block mg_tp_30">
                <!-- Terms and Conditions -->
                  <?php if($sq_terms_cond['terms_and_conditions']!=''){?>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="incluExcluTermsTabPanel inclusions main_block">
                          <h3 class="lgTitle" style="margin-left:20px!important;">Terms and Conditions</h3>
                          <div class="tabContent">
                              <pre class="real_text"><?= $sq_terms_cond['terms_and_conditions'] ?></pre>
                          </div>
                      </div>
                    </div>
                  </div>
                  <?php } ?>
                  <div class="row">
                    <div class="col-md-12">
                      <pre class="real_text"><?php echo $quot_note; ?></pre>
                    </div>
                  </div>
              </section>
          </section>
          <?php } ?>

    <!-- Ending Page -->
    <section class="incluExcluTerms main_block mg_tp_20">
      <!-- Costing & Bank Detail -->
      <div class="costBankSec main_block mg_tp_20 costing_bank_details_bk">
        <div class="costBankInner main_block side_pad mg_tp_20 mg_bt_20">
          <div class="row">
            <!-- Costing -->
            <div class="col-md-6">
              <h3 class="costBankTitle text-center">COSTING DETAILS</h3>
              <div class="row mg_bt_20">
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/tourCost.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= number_format($grand_total,2) ?></h4>
                  <p>TOTAL AMOUNT</p>
                </div>
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/tax.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= number_format($tax_amt,2) ?></h4>
                  <p>TAX</p>
                </div>
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/travelCost.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= number_format($quotation_cost,2) ?></h4>
                  <p>Net AMOUNT</p>
                </div>
              </div>
            </div>
            <!-- Bank Detail -->
            <div class="col-md-6" style="border-left:1px solid #dddddd;">
              <h3 class="costBankTitle text-center">BANK DETAILS</h3>
              <div class="row mg_bt_20">
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/bankName.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= $sq_reg['b_bank_name'] ?></h4>
                  <p>BANK NAME</p>
                </div>
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/branchName.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= $sq_reg['b_branch_name'] ?></h4>
                  <p>BRANCH</p>
                </div>
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/accName.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= $sq_reg['b_acc_name'] ?></h4>
                  <p>A/C TYPE</p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/accNumber.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= $sq_reg['b_acc_no'] ?></h4>
                  <p>A/C NO</p>
                </div>
                <div class="col-md-4 text-center">
                  <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/code.png" class="img-responsive"></div>
                  <h4 class="no-marg"><?= $sq_reg['b_ifsc_code'] ?></h4>
                  <p>IFSC</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- contact-detail -->
      <section class="contactsec main_block">
        <div class="row">
          <div class="col-md-7">
            <div class="contactTitlePanel text-center">
					  <?php
            if($sq_reg['company_logo']!=''){
              $newUrl = preg_replace('/(\/+)/','/',$sq_reg['company_logo']);
              $newUrl = explode('uploads', $newUrl);
              $newUrl1 = BASE_URL.'uploads'.$newUrl[1];
            }
            ?>
              <img src="<?= $newUrl1 ?>" class="img-responsive">
            </div>
          </div>
          <div class="col-md-5">
            <?php if($sq_reg['address1'] != ''){?>
            <div class="contactBlock main_block side_pad mg_tp_20">
              <div class="cBlockIcon"> <i class="fa fa-map-marker"></i> </div>
              <div class="cBlockContent">
                <h5 class="cTitle">Corporate Office</h5>
                <p class="cBlockData"><?php echo $sq_reg['address1'].' '.$sq_reg['address2']; ?></p>
              </div>
            </div>
            <?php } ?>
            <?php if($sq_reg['mobile_no'] != ''){?>
            <div class="contactBlock main_block side_pad mg_tp_20">
              <div class="cBlockIcon"> <i class="fa fa-phone"></i> </div>
              <div class="cBlockContent">
                <h5 class="cTitle">Contact</h5>
                <p class="cBlockData"><?php echo $sq_reg['mobile_no']; ?></p>
              </div>
            </div>
            <?php } ?>
            <?php if($sq_reg['email_id'] != ''){?>
            <div class="contactBlock main_block side_pad mg_tp_20">
              <div class="cBlockIcon"> <i class="fa fa-envelope"></i> </div>
              <div class="cBlockContent">
                <h5 class="cTitle">Email Id</h5>
                <p class="cBlockData"><?php echo $sq_reg['email_id']; ?></p>
              </div>
            </div>
            <?php } ?>
            <?php if($sq_reg['website'] != ''){?>
            <div class="contactBlock main_block side_pad mg_tp_20">
              <div class="cBlockIcon"><i class="fa fa-globe" aria-hidden="true"></i> </div>
              <div class="cBlockContent">
                <h5 class="cTitle">Website</h5>
                <p class="cBlockData"><?php echo $sq_reg['website']; ?></p>
              </div>
            </div>
            <?php } ?>
          </div>
        </div>
      </section>
      <div class="row">
        <div class="col-md-7">
        </div>
        <div class="col-md-5">
            <span class="generatorName mg_tp_10">PREPARED BY <?= $prepare_by?></span><br>
        </div>
      </div>
    </section>

</body>
</html>
<script type="text/javascript">
  $(document).ready(function () {
    setTimeout(() => {
      window.print();
    }, 800);
  });
</script>
<?php
//Generic Files
include "../../../../model.php"; 
include "printFunction.php";
global $app_quot_img,$currency;

$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select * from branch_assign where link='hotel_quotation/index.php'"));
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
$sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Hotel Quotation' and active_flag ='Active'"));

$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select * from hotel_quotation_master where quotation_id='$quotation_id'"));
$sq_login = mysqli_fetch_assoc(mysqlQuery("select * from roles where id='$sq_quotation[login_id]'"));
$quotation_date = $sq_quotation['quotation_date'];
$yr = explode("-", $quotation_date);
$year = $yr[0];
$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_login[emp_id]'"));
$emp_name = ($sq_emp_info['first_name']=='') ? 'Admin' : $sq_emp_info['first_name'].' '.$sq_emp_info['last_name'];

$enquiryDetails = json_decode($sq_quotation['enquiry_details'], true);
$hotelDetails = json_decode($sq_quotation['hotel_details'], true);
$costDetails = json_decode($sq_quotation['costing_details'], true);

$tax_show = '';
?>

    <section class="headerPanel main_block">
        <div class="headerImage">
          <img src="<?= $app_quot_img ?>" class="img-responsive">
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
          <span class="title"><i class="fa fa-pencil-square-o"></i> HOTEL QUOTATION</span>
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
                    <i class="fa fa-calendar" aria-hidden="true"></i><br>
                    <span>QUOTATION DATE</span><br>
                    <?= get_date_user($sq_quotation['quotation_date']) ?><br>
                  </div>
                </li>
                <li class="col-md-4 mg_tp_10 mg_bt_10">
                  <div class="print_quo_detail_block">
                    <i class="fa fa-hashtag" aria-hidden="true"></i><br>
                    <span>QUOTATION ID</span><br>
                    <?= get_quotation_id($quotation_id,$year) ?><br>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

    </section>

    <!-- Hotel -->
    <section class="print_sec main_block side_pad mg_tp_30">
      <div class="section_heding">
        <h2>CUSTOMER DETAILS</h2>
        <div class="section_heding_img">
          <img src="<?php echo BASE_URL.'images/heading_border.png'; ?>" class="img-responsive">
        </div>
      </div>
      <div class="row">
        <div class="col-md-7 mg_bt_20">
        </div>
        <div class="col-md-5 mg_bt_20">
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="print_info_block">
          <ul class="print_info_list">
            <li class="col-md-6 mg_tp_10 mg_bt_10"><span>CUSTOMER NAME :</span><?= $enquiryDetails['customer_name'] ?></li>
            <li class="col-md-6 mg_tp_10 mg_bt_10"><span>CONTACT NUMBER :</span> <?= $enquiryDetails['country_code'].$enquiryDetails['whatsapp_no'] ?></li>
          </ul>
          <ul class="print_info_list">
            
            <li class="col-md-6 mg_tp_10 mg_bt_10"><span>E-MAIL ID :</span> <?= $enquiryDetails['email_id'] ?></li>
            <li class="col-md-6 mg_tp_10 mg_bt_10"><span>QUOTATION ID : </span><?= get_quotation_id($quotation_id, $year) ?></li>
          </ul>
          <hr class="main_block">
            <ul class="main_block">
                <li class="col-md-6 mg_tp_10 mg_bt_10"><span>Adult(s) : </span><?= $enquiryDetails['total_adult'] ?></li>
                <li class="col-md-6 mg_tp_10 mg_bt_10"><span>CWB : </span><?= $enquiryDetails['children_with_bed'] ?></li>
            </ul>
            <ul class="main_block">
              <li class="col-md-6 mg_tp_10 mg_bt_10"><span>CWOB : </span><?= $enquiryDetails['children_without_bed'] ?></li>
              <li class="col-md-6 mg_tp_10 mg_bt_10"><span>INFANT(s) : </span><?= $enquiryDetails['total_infant'] ?></li>
            </ul>
            <ul class="main_block">
              <li class="col-md-6 mg_tp_10 mg_bt_10"><span>TOTAL GUEST(s) : </span><?= $enquiryDetails['total_members'] ?></li>
            </ul>
          </div>
        </div>
      </div>

      <div class="row">
  <!-- bank -->
  <div class="col-md-12">
          <div class="section_heding">
            <h2>BANK DETAILS</h2>
            <div class="section_heding_img">
              <img src="<?php echo BASE_URL.'images/heading_border.png'; ?>" class="img-responsive">
            </div>
          </div>
          <div class="print_info_block">
          <div class="row"> 
              <div class="col-md-6">
              <ul class="main_block">
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>BANK NAME :
                    </span><?=  ($sq_bank_count>0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting ?></li>
                <li class="col-md-12 mg_tp_10 mg_bt_10"><span>A/C TYPE :
                    </span><?php if($sq_bank_count>0 && $sq_bank_branch['account_type'] != '') echo $sq_bank_branch['account_type'];  else { if($acc_name != '') echo $acc_name;  else echo 'NA';  } ?></li>
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
              <?php 
              if(check_qr()) { ?>
              <div class="col-md-6 text-center" style="margin-top:20px;">
                     <?= get_qr('Protrait Standard') ?>
                        <br>
                        <h4 class="no-marg">Scan & Pay </h4>
         
              </div>
              <?php } ?>
            </div>  
          </div>
        </div>
  <!-- bank -->
      </div>
    </section>

    <section class="print_sec main_block side_pad mg_tp_30">
        <div class="section_heding">
          <h2>ACCOMMODATION</h2>
          <div class="section_heding_img">
            <img src="<?php echo BASE_URL.'images/heading_border.png'; ?>" class="img-responsive">
          </div>
        </div>
        <?php
        $hotelDetails = json_decode($sq_quotation['hotel_details'],true);
        $int_flag = '';
        for($index = 0; $index<sizeof($hotelDetails); $index++){

          $option = $hotelDetails[$index]['option'];
          ?>
            <h6 class="text-center mg_tp_10">OPTION - <?= $option ?></h6>
            <div class="row">
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
                      $data = $hotelDetails[$index]['data'];
                      for($i = 0;$i<sizeof($data);$i++){

                        $hotel_id = $data[$i]['hotel_id'];
                        $city_id = $data[$i]['city_id'];
                        $hotel_name = mysqli_fetch_assoc(mysqlQuery("select hotel_name,state_id from hotel_master where hotel_id='$hotel_id'"));
                        $city_name = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$city_id'"));
                        if($data[$i]['tour_type'] == 'International' && $int_flag == ''){
                          $int_flag = true;
                        }
                        ?>
                        <tr>
                          <td><?php echo $city_name['city_name']; ?></td>
                          <td><?php echo $hotel_name['hotel_name']; ?></td>
                          <td><?php echo get_date_user($data[$i]['checkin']) ?></td>
                          <td><?php echo get_date_user($data[$i]['checkout']) ?></td>
                        </tr>
                        <?php
                    } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php } ?>
    </section>

    <!-- Costing -->
    <section class="print_sec main_block side_pad mg_tp_30">
      <div class="row">
        <div class="col-md-12">
          <div class="section_heding">
            <h2>COSTING</h2>
            <div class="section_heding_img">
              <img src="<?php echo BASE_URL.'images/heading_border.png'; ?>" class="img-responsive">
            </div>
          </div>
          <div class="row mg_tp_30">
            <div class="col-md-12">
              <div class="table-responsive">
                <table class="table table-bordered no-marg" id="tbl_emp_list">
                  <thead>
                    <tr class="table-heading-row">
                      <th style="font-size: 16px !important; font-weight: 400 !important; padding: 8px  20px !important;">Option</th>
                      <th style="font-size: 16px !important; font-weight: 400 !important; padding: 8px  20px !important;">Total Cost</th>
                      <th style="font-size: 16px !important; font-weight: 400 !important; padding: 8px  20px !important;">Tax</th>
                      <th style="font-size: 16px !important; font-weight: 400 !important; padding: 8px  20px !important;">Tcs</th>
                      <th style="font-size: 16px !important; font-weight: 400 !important; padding: 8px  20px !important;">Quotation Cost</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $bsmValues = isset($sq_quotation['bsm_values']) ? json_decode($sq_quotation['bsm_values'],true) : [];
                    for($index = 0; $index<sizeof($costDetails); $index++){

                        $data = $costDetails[$index]['costing'];
                        $option = $costDetails[$index]['option'];
                        $total_cost = $data['total_amount'];
                        $round_off = isset($data['round_off']) ? $data['round_off'] : 0;
                        $basic_cost1 = floatval($data['hotel_cost']);
                        $service_charge = floatval($data['service_charge']);
                        $tcs_amnt= $data['tcs_amnt'];

                        $tcsper=$data['tcs_tax'];
                        $name = '';

                        $service_tax_amount = 0;
                        $markupservice_tax_amount = 0;
                        //////////////////Service Charge Rules
                        if($data['tax_amount'] !== 0.00 && ($data['tax_amount']) !== ''){
                            $service_tax_subtotal1 = explode(',',$data['tax_amount']);
                            for($j=0;$j<sizeof($service_tax_subtotal1);$j++){
                                $service_tax = explode(':',$service_tax_subtotal1[$j]);
                                $service_tax_amount += floatval($service_tax[2]);
                                $percent = $service_tax[1];
                                $name .= $service_tax[0]  . $service_tax[1] .', ';
                            }
                        }
                        ////////////////////Markup Rules
                        if($data['markup_tax'] !== 0.00 && $data['markup_tax'] !== ""){
                            $service_tax_markup1 = explode(',',$data['markup_tax']);
                            for($j=0;$j<sizeof($service_tax_markup1);$j++){
                                $service_tax = explode(':',$service_tax_markup1[$j]);
                                $markupservice_tax_amount += floatval($service_tax[2]);
                            }
                        }

                        if(isset($bsmValues[$index]) && ($bsmValues[$index]->service != '' || $bsmValues[$index]->basic != '')  && $bsmValues[$index]->markup != ''){
                          $tax_show = '';
                          $newBasic = $basic_cost1 + floatval($data['markup_cost']) + $service_charge + $service_tax_amount;
                        }
                        elseif(isset($bsmValues[$index]) && ($bsmValues[$index]->service == '' || $bsmValues[$index]->basic == '')  && $bsmValues[$index]->markup == ''){
                          $tax_show = $percent.' '. ($markupservice_tax_amount + $service_tax_amount);
                          $newBasic = $basic_cost1 + floatval($data['markup_cost']) + $service_charge;
                        }
                        elseif(isset($bsmValues[$index]) && ($bsmValues[$index]->service != '' || $bsmValues[$index]->basic != '') && $bsmValues[$index]->markup == ''){
                          $tax_show = $percent.' '. ($markupservice_tax_amount);
                          $newBasic = $basic_cost1 + floatval($data['markup_cost']) + $service_charge + $service_tax_amount;
                        }
                        else{
                          $tax_show = $percent.' '. ($service_tax_amount);
                          $newBasic = $basic_cost1 + floatval($data['markup_cost']) + $service_charge;
                        }
                        $service_tax_amount_show = currency_conversion($currency,$sq_quotation['currency_code'],$service_tax_amount);
                        $markupservice_tax_amount_show = currency_conversion($currency,$sq_quotation['currency_code'],$markupservice_tax_amount);
                        $total_fare = currency_conversion($currency,$sq_quotation['currency_code'],$newBasic);
                        $service_tax_amount_show = explode(' ',$service_tax_amount_show);
                        $service_tax_amount_show1 = str_replace(',','',$service_tax_amount_show[1]);
                        $markupservice_tax_amount_show = explode(' ',$markupservice_tax_amount_show);
                        $markupservice_tax_amount_show1 = str_replace(',','',$markupservice_tax_amount_show[1]);
                        $currency_amount1 = currency_conversion($currency,$sq_quotation['currency_code'],$total_cost);
                        $tcs_amount = currency_conversion($currency,$sq_hotel['currency_code'],$tcs_amnt);
                      ?>
                      <tr>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?php echo intval($option) ?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?php echo $total_fare ?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= str_replace(',','',$name).$service_tax_amount_show[0].' '.number_format($service_tax_amount_show1 + $markupservice_tax_amount_show1 + $round_off,2) ?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;">Tcs:(<?=$tcsper?>%)<br><?=$tcs_amount?></td>
                        <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= $currency_amount1 ?></td>
                      </tr>
                    <?php
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
              <?php
              // $tcs_note_show = ($int_flag == true) ? $tcs_note : '';
              // if ($tcs_note_show != '') { ?>
                <!-- <p class="costBankTitle"><?php //$tcs_note_show ?></p> -->
              <?php //} ?>
        </div>
    
    <!-- Bank Detail -->
      
      </div>
    </section>

    
    <section class="print_sec main_block side_pad mg_tp_30">
    <?php if(isset($sq_terms_cond['terms_and_conditions'])){ ?>
      <div class="row">
        <div class="col-md-12">
          <div class="section_heding">
            <h2>Terms and Conditions</h2>
            <div class="section_heding_img">
              <img src="<?php echo BASE_URL.'images/heading_border.png'; ?>" class="img-responsive">
            </div>
          </div>
          <div class="print_text_bolck">
          <?php echo $sq_terms_cond['terms_and_conditions']; ?>
          </div>
        </div>
      </div>
    <?php } ?>
    <div class="row mg_tp_10">
      <div class="col-md-12">
        <?php echo $quot_note; ?>
      </div>
    </div>

      <div class="row mg_tp_30">
        <div class="col-md-7"></div>
        <div class="col-md-5 mg_tp_30">
          <div class="print_quotation_creator text-center">
            <span>PREPARED BY </span><br><?= $emp_name?>
          </div>
        </div>
      </div>
    </section>

    <section>
      
    </section>    
  </body>
</html>
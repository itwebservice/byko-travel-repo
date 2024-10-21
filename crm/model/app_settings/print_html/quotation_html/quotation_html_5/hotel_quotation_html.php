<?php
//Generic Files
include "../../../../model.php";
include "printFunction.php";
global $app_quot_img, $similar_text, $quot_note, $currency, $tcs_note;

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

<!-- landingPage -->
<section class="landingSec main_block">
  <div class="col-md-8 no-pad">
    <img src="<?= $app_quot_img ?>" class="img-responsive">
    <span class="landingPageId"><?= get_quotation_id($quotation_id, $year) ?></span>
  </div>
  <div class="col-md-4 no-pad">
  </div>
  <h1 class="landingpageTitle">HOTEL QUOTATION</h1>
  <div class="packageDeatailPanel">
    <div class="landingPageBlocks">

      <div class="detailBlock">
        <div class="detailBlockIcon">
          <i class="fa fa-calendar"></i>
        </div>
        <div class="detailBlockContent">
          <h3 class="contentValue"><?= get_date_user($sq_quotation['quotation_date']) ?></h3>
          <span class="contentLabel">QUOTATION DATE</span>
        </div>
      </div>

      <div class="detailBlock">
        <div class="detailBlockIcon">
          <i class="fa fa-users"></i>
        </div>
        <div class="detailBlockContent">
          <h3 class="contentValue"><?= $enquiryDetails['total_members'] ?></h3>
          <span class="contentLabel">TOTAL GUEST</span>
        </div>
      </div>

    </div>
    <div class="landigPageCustomer">
      <h3 class="customerFrom">PREPARED FOR</h3>
      <span class="customerName"><em><i class="fa fa-user"></i></em> : <?= $enquiryDetails['customer_name'] ?></span><br>
      <span class="customerMail"><em><i class="fa fa-envelope"></i></em> : <?= $enquiryDetails['email_id'] ?></span><br>
      <span class="customerMobile"><em><i class="fa fa-phone"></i></em> : <?= $enquiryDetails['country_code'] . $enquiryDetails['whatsapp_no'] ?></span><br>
    </div>
  </div>
</section>

<!-- Hotel -->
<section class="pageSection main_block">
  <!-- background Image -->
  <img src="<?= BASE_URL ?>images/quotation/p5/pageBGF.jpg" class="img-responsive pageBGImg">

  <section class="incluExcluTerms pageSectionInner main_block mg_tp_30">
    <section class="transportDetailsPanel transportDetailsleft main_block mg_tp_10">
      <?php
      $hotelDetails = json_decode($sq_quotation['hotel_details'],true);
      $int_flag = '';
      for($index = 0; $index<sizeof($hotelDetails); $index++){

        $option = $hotelDetails[$index]['option'];
        ?>
          <h6 class="text-center mg_tp_20">OPTION - <?= $option ?></h6>
          <div class="travsportInfoBlock">
            <div class="transportIcon">
              <img src="<?= BASE_URL ?>images/quotation/p4/TI_hotel.png" class="img-responsive">
            </div>
            <div class="transportDetails">
              <div class="col-md-12 no-pad">
                <div class="table-responsive">
                  <table class="table tableTrnasp no-marg" id="tbl_emp_list">
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
                              <td><?= get_date_user($data[$i]['checkin']) ?></td>
                              <td><?= get_date_user($data[$i]['checkout']) ?></td>
                            </tr>
                            <?php
                        } ?>
                      </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
      <?php } ?>
    </section>
  </section>
</section>


<!-- Terms and Conditions -->
<?php if (isset($sq_terms_cond['terms_and_conditions']) || isset($quot_note)) { ?>
  <section class="pageSection main_block">
    <!-- background Image -->
    <img src="<?= BASE_URL ?>images/quotation/p5/pageBGF.jpg" class="img-responsive pageBGImg">

    <section class="incluExcluTerms pageSectionInner main_block mg_tp_30">
      <?php if (isset($sq_terms_cond['terms_and_conditions'])) { ?>
      <!-- Terms and Conditions -->
      <div class="row">

        <div class="col-md-12">
          <div class="termsPanel">
            <h3 class="incexTitleTwo">TERMS AND CONDITIONS</h3>
            <div class="tncContent">
              <pre class="real_text"><?php echo $sq_terms_cond['terms_and_conditions']; ?></pre>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
      <?php if ($quot_note != '') { ?>
      <div class="row mg_tp_30">
        <div class="col-md-12">
          <div class="termsPanel">
            <div class="tncContent">
              <pre class="real_text"><?php echo $quot_note; ?></pre>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
    </section>
  </section>
<?php } ?>



<!-- Costing & Banking Page -->
<section class="endPageSection main_block mg_tp_30">

  <div class="row">

    <!-- Guest Detail -->
    <div class="col-md-12 passengerPanel endPagecenter mg_bt_30">
      <h3 class="endingPageTitle text-center">TOTAL GUEST(S)</h3>
      <div class="col-md-3 text-center mg_bt_30">
        <div class="iconPassengerBlock">
          <div class="iconPassengerSide leftSide"></div>
          <div class="iconPassenger">
            <img src="<?= BASE_URL ?>images/quotation/p4/adult.png" class="img-responsive">
            <h4 class="no-marg">Adult : <?= $enquiryDetails['total_adult'] ?></h4>
          </div>
          <div class="iconPassengerSide rightSide"></div>
        </div>
      </div>
      <div class="col-md-3 text-center mg_bt_30">
        <div class="iconPassengerBlock">
          <div class="iconPassengerSide leftSide"></div>
          <div class="iconPassenger">
            <img src="<?= BASE_URL ?>images/quotation/p4/child.png" class="img-responsive">
            <h4 class="no-marg">CWB : <?= $enquiryDetails['children_with_bed'] ?></h4>
          </div>
          <div class="iconPassengerSide rightSide"></div>
          <i class="fa fa-plus"></i>
        </div>
      </div>
      <div class="col-md-3 text-center mg_bt_30">
        <div class="iconPassengerBlock">
          <div class="iconPassengerSide leftSide"></div>
          <div class="iconPassenger">
            <img src="<?= BASE_URL ?>images/quotation/p4/child.png" class="img-responsive">
            <h4 class="no-marg">CWOB : <?= $enquiryDetails['children_without_bed'] ?></h4>
          </div>
          <div class="iconPassengerSide rightSide"></div>
          <i class="fa fa-plus"></i>
        </div>
      </div>
      <div class="col-md-3 text-center mg_bt_30">
        <div class="iconPassengerBlock">
          <div class="iconPassengerSide leftSide"></div>
          <div class="iconPassenger">
            <img src="<?= BASE_URL ?>images/quotation/p4/infant.png" class="img-responsive">
            <h4 class="no-marg">Infant : <?= $enquiryDetails['total_infant'] ?></h4>
          </div>
          <div class="iconPassengerSide rightSide"></div>
          <i class="fa fa-plus"></i>
        </div>
      </div>
    </div>

  </div>
  <div class="row">
    <!-- Costing -->
    <div class="col-md-12 passengerPanel endPagecenter mg_bt_10">
      <h3 class="endingPageTitle text-center no-pad">COSTING DETAILS</h3>
      <div class="travsportInfoBlock1">
        <div class="transportDetails_costing package_costing">
          <div class="table-responsive">
            <table class="table no-marg tableTrnasp">
              <thead>
                <tr class="table-heading-row">
                  <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Option</th>
                  <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Total Cost</th>
                  <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Tax</th>
                  <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Tcs</th>
                  <th style="font-size: 16px !important; font-weight: 600 !important; padding: 8px  20px !important;">Quotation Cost</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $bsmValues = isset($sq_quotation['bsm_values']) ? json_decode($sq_quotation['bsm_values'],true) : [];
                for($index = 0; $index<sizeof($costDetails); $index++){

                    $option = $costDetails[$index]['option'];
                    $data = $costDetails[$index]['costing'];
                    $total_cost = $data['total_amount'];
                    $round_off = isset($data['round_off']) ? $data['round_off'] : 0;
                    $basic_cost1 = floatval($data['hotel_cost']);
                    $service_charge = floatval($data['service_charge']);
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
                    $tcs_tax_amount=$data['tcs_amnt'];
                    $tcsper=$data['tcs_tax'];
                    $tcs_tax_amount_show = currency_conversion($currency,$sq_quotation['currency_code'],$tcs_tax_amount);
                    $service_tax_amount_show = currency_conversion($currency,$sq_quotation['currency_code'],$service_tax_amount);
                    $markupservice_tax_amount_show = currency_conversion($currency,$sq_quotation['currency_code'],$markupservice_tax_amount);
                    $total_fare = currency_conversion($currency,$sq_quotation['currency_code'],$newBasic);
                    $service_tax_amount_show = explode(' ',$service_tax_amount_show);
                    $service_tax_amount_show1 = str_replace(',','',$service_tax_amount_show[1]);
                    $markupservice_tax_amount_show = explode(' ',$markupservice_tax_amount_show);
                    $markupservice_tax_amount_show1 = str_replace(',','',$markupservice_tax_amount_show[1]);
                    $currency_amount1 = currency_conversion($currency,$sq_quotation['currency_code'],$total_cost);
                  ?>
                  <tr>
                    <td style="font-size: 14px !important; padding: 8px  20px !important;"><?php echo $option ?></td>
                    <td style="font-size: 14px !important; padding: 8px  20px !important;"><?php echo $total_fare ?></td>
                    <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= str_replace(',','',$name).$service_tax_amount_show[0].' '.number_format($service_tax_amount_show1 + $markupservice_tax_amount_show1 + $round_off,2) ?></td>
                    <td style="font-size: 14px !important; padding: 8px  20px !important;">Tcs:(<?=$tcsper?>%)<br><?= $tcs_tax_amount_show ?></td>
                    <td style="font-size: 14px !important; padding: 8px  20px !important;"><?= $currency_amount1 ?></td>
                  </tr>
                <?php
                } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-12 text-center no-pad">
        <?php
        // $tcs_note_show = ($int_flag == true) ? $tcs_note : '';
        // if ($tcs_note_show != '') { ?>
          <!-- &nbsp;&nbsp;&nbsp;&nbsp;<h5 class="costBankTitle mg_tp_10" style="color:white !important;"> -->
            <?php //$tcs_note_show ?></h5>
        <?php //} ?>
      </div>
    </div>

  </div>
</section>
<section class="endPageSection main_block mg_tp_10">

  <div class="row constingBankingPanelRow">
      <!-- Bank Detail -->
      <div class="col-md-12 constingBankingPanel BankingPanel mg_tp_20">
            <h3 class="costBankTitle text-center">BANK DETAILS</h3>
              <div class="col-md-4 text-center no-pad constingBankingwhite">
                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p5/bankName.png" class="img-responsive"></div>
                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['bank_name'] != '') ? $sq_bank_branch['bank_name'] : $bank_name_setting ?></h4>
                <p>BANK NAME</p>
              </div>
              <div class="col-md-4 text-center no-pad">
                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/branchName.png" class="img-responsive"></div>
                <h4 class="no-marg"><?=  ($sq_bank_count>0 || $sq_bank_branch['branch_name'] != '') ? $sq_bank_branch['branch_name'] : $bank_branch_name ?></h4>
                <p>BRANCH</p>
              </div>
              <div class="col-md-4 text-center no-pad constingBankingwhite">
                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p5/accName.png" class="img-responsive"></div>
                <h4 class="no-marg"><?php if($sq_bank_count>0 && $sq_bank_branch['account_type'] != '') echo $sq_bank_branch['account_type'];  else { if($acc_name != '') echo $acc_name;  else echo 'NA';  } ?></h4>
                <p>A/C TYPE</p>
              </div>
              <div class="col-md-4 text-center no-pad">
                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/accNumber.png" class="img-responsive"></div>
                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['account_no'] != '') ? $sq_bank_branch['account_no'] : $bank_acc_no  ?></h4>
                <p>A/C NO</p>
              </div>
              <div class="col-md-4 text-center no-pad constingBankingwhite">
                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p5/code.png" class="img-responsive"></div>
                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['account_name'] != '') ? $sq_bank_branch['account_name'] : $bank_account_name ?></h4>
                <p>BANK ACCOUNT NAME</p>
              </div>
              <div class="col-md-4 text-center no-pad">
                <div class="icon"><img src="<?= BASE_URL ?>images/quotation/p4/code.png" class="img-responsive"></div>
                <h4 class="no-marg"><?= ($sq_bank_count>0 || $sq_bank_branch['swift_code'] != '') ? strtoupper($sq_bank_branch['swift_code']) :  strtoupper($bank_swift_code) ?></h4>
                <p>SWIFT CODE</p>
              </div>
            <?php 
              if(check_qr()) { ?>
            <div class="col-md-12 text-center" style="margin-top:20px; margin-bottom:20px;">
                        <?= get_qr('Protrait Advance') ?>
                        <br>
                        <h4 class="no-marg">Scan & Pay </h4>
          </div>
          <?php } ?>
          </div>
  </div>
</section>

<!-- Contact Page -->
<section class="pageSection main_block">
  <!-- background Image -->
  <img src="<?= BASE_URL ?>images/quotation/p5/pageBGF.jpg" class="img-responsive pageBGImg">

  <section class="contactSection main_block mg_tp_30 text-center pageSectionInner">
    <div class="companyLogo">
      <img src="<?= $admin_logo_url ?>">
    </div>
    <div class="companyContactDetail">
      <h3><?= $app_name ?></h3>
      <?php //if($app_address != ''){
      ?>
      <div class="contactBlock">
        <i class="fa fa-map-marker"></i>
        <p><?php echo ($branch_status == 'yes' && $role != 'Admin') ? $branch_details['address1'] . ',' . $branch_details['address2'] . ',' . $branch_details['city'] : $app_address; ?></p>
      </div>
      <?php //}
      ?>
      <?php //if($app_contact_no != ''){
      ?>
      <div class="contactBlock">
        <i class="fa fa-phone"></i>
        <p><?php echo ($branch_status == 'yes' && $role != 'Admin') ? $branch_details['contact_no']  : $app_contact_no; ?></p>
      </div>
      <?php //}
      ?>
      <?php //if($app_email_id != ''){
      ?>
      <div class="contactBlock">
        <i class="fa fa-envelope"></i>
        <p><?php echo ($branch_status == 'yes' && $role != 'Admin' && $branch_details['email_id'] != '') ? $branch_details['email_id'] : $app_email_id; ?></p>
      </div>
      <?php //}
      ?>
      <?php if ($app_website != '') { ?>
        <div class="contactBlock">
          <i class="fa fa-globe"></i>
          <p><?php echo $app_website; ?></p>
        </div>
      <?php } ?>
      <div class="contactBlock">
        <i class="fa fa-pencil-square-o"></i>
        <p>PREPARED BY : <?= $emp_name ?></p>
      </div>
    </div>
  </section>
</section>
</body>

</html>
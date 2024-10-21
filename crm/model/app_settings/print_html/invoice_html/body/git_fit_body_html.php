<?php
//Generic Files
include "../../../../model.php"; 
include "../../print_functions.php";
require("../../../../../classes/convert_amount_to_word.php"); 

global $currency;
//Parameters
$invoice_no = $_GET['invoice_no'];
$invoice_date = $_GET['invoice_date'];
$customer_id = $_GET['customer_id'];
$service_name = $_GET['service_name'];
$taxation_type = $_GET['taxation_type'];
$bank_name = isset($_GET['bank_name']) ? $_GET['bank_name'] : '';
$tour_name = isset($_GET['tour_name']) ? $_GET['tour_name'] : '';
$train_expense = $_GET['train_expense'];
$plane_expense = $_GET['plane_expense'];
$cruise_expense = $_GET['cruise_expense'];
$visa_amount = $_GET['visa_amount'];
$insuarance_amount = $_GET['insuarance_amount'];
$tour_subtotal = $_GET['tour_subtotal'];
$tour_date = $_GET['tour_date'];
$tour_to_date = $_GET['tour_to_date'];
$adults = $_GET['adults'];
$child = $_GET['child'];
$infants = $_GET['infants'];
$flights = $_GET['flights'];
$trains = $_GET['trains'];
$cruises = $_GET['cruises'];

$train_service_charge = $_GET['train_service_charge'];
$plane_service_charge = $_GET['plane_service_charge'];
$cruise_service_charge = $_GET['cruise_service_charge'];
$visa_service_charge = $_GET['visa_service_charge'];
$insuarance_service_charge = $_GET['insuarance_service_charge'];

$train_service_tax = $_GET['train_service_tax'];
$plane_service_tax = $_GET['plane_service_tax'];
$cruise_service_tax = $_GET['cruise_service_tax'];
$visa_service_tax = $_GET['visa_service_tax'];
$insuarance_service_tax = $_GET['insuarance_service_tax'];
$tour_service_tax = $_GET['tour_service_tax'];
$booking_id = $_GET['booking_id'];
$train_service_tax_subtotal = $_GET['train_service_tax_subtotal'];
$plane_service_tax_subtotal = $_GET['plane_service_tax_subtotal'];
$cruise_service_tax_subtotal = $_GET['cruise_service_tax_subtotal'];
$visa_service_tax_subtotal = $_GET['visa_service_tax_subtotal'];
$insuarance_service_tax_subtotal = $_GET['insuarance_service_tax_subtotal'];
$tour_service_tax_subtotal = $_GET['tour_service_tax_subtotal'];
$sac_code = $_GET['sac_code'];
$credit_card_charges = $_GET['credit_card_charges'];
$tcs_tax = $_GET['tcs_tax'];
$tcs_per = $_GET['tcs_per'];
$bg = $_GET['bg'];
$canc_amount = floatval($_GET['canc_amount']);
$subtotal = isset($_GET['sub_total']) ? $_GET['sub_total'] : 0;
$charge = isset($credit_card_charges)?$credit_card_charges : 0 ;

if($service_name =='Package Invoice'){
  $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id' and delete_status='0'"));
}else{
  $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$booking_id' and delete_status='0'"));
}
$branch_admin_id = isset($_SESSION['branch_admin_id']) ? $_SESSION['branch_admin_id'] : $sq_booking['branch_admin_id'];

$roundoff = $sq_booking['roundoff'];
$basic_cost1 = $sq_booking['basic_amount'];
$net_total = $sq_booking['net_total'];
$bsmValues = json_decode($sq_booking['bsm_values']);

$tax_show = '';
$newBasic = $basic_cost1;
$name = '';
//////////////////Service Charge Rules
$service_tax_amount = 0;
if($service_name =='Package Invoice'){

  $service_charge = $sq_booking['service_charge'];
  $tds = $sq_booking['tds'];

if($sq_booking['tour_service_tax_subtotal'] !== 0.00 && ($sq_booking['tour_service_tax_subtotal']) !== ''){
  $service_tax_subtotal1 = explode(',',$sq_booking['tour_service_tax_subtotal']);
  for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
    $service_tax = explode(':',$service_tax_subtotal1[$i]);
    $service_tax_amount +=  $service_tax[2];
    $name .= $service_tax[0]  . $service_tax[1] .', ';
  }
}
}else{
  $service_charge = 0;
  $tds = 0;
  if($sq_booking['service_tax'] !== 0.00 && ($sq_booking['service_tax']) !== ''){
    $service_tax_subtotal1 = explode(',',$sq_booking['service_tax']);
    for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
      $service_tax = explode(':',$service_tax_subtotal1[$i]);
      $service_tax_amount +=  $service_tax[2];
      $name .= $service_tax[0]  . $service_tax[1] .', ';
    }
  }
}
$service_tax_amount_show = currency_conversion($currency,$sq_booking['currency_code'],$service_tax_amount);
if($bsmValues[0]->service != ''){   //inclusive service charge
  $newBasic = $basic_cost1;
  $newSC = $service_tax_amount + $service_charge;
}
else{
  $tax_show =  rtrim($name, ', ').' : ' . $currency_code.' '. ($service_tax_amount);
  $newSC = $service_charge;
  
}

////////////Basic Amount Rules
if($bsmValues[0]->basic != ''){ //inclusive markup
  
  $newBasic = $basic_cost1 + $service_tax_amount;
  $tax_show = '';
}
$total_paid = !isset($_GET['total_paid']) ? 0 : floatval($_GET['total_paid']);

$total_balance = $net_total - $total_paid;
$total_paid += floatval($charge);
$net_total1 = currency_conversion($currency,$sq_booking['currency_code'],$net_total);
$amount_in_word = $amount_to_word->convert_number_to_words($net_total1,$sq_booking['currency_code']);
// Passengers string
$passengers = 'Guest(s) : ';
$passengers .= 
(intval($adults)!=0) ? $adults.' Adult(s)':'';
$passengers .= 
(intval($adults)!=0&&intval($child)!=0) ? ', ' : '';
$passengers .= 
(intval($child)!=0) ? $child.' Child(ren)':'';
$passengers .= 
(intval($adults)!=0&&intval($infants)!=0||intval($child)!=0&&intval($infants)!=0) ? ', ' : '';
$passengers .= 
(intval($infants)!=0) ? $infants.' Infant(s)' : '';

//Header
if($app_invoice_format == "Standard"){include "../headers/standard_header_html.php"; }
if($app_invoice_format == "Regular"){include "../headers/regular_header_html.php"; }
if($app_invoice_format == "Advance"){include "../headers/advance_header_html.php"; }
?>
<section class="no-pad main_block">
  <!-- invoice_receipt_body_table-->
  <div class="main_block inv_rece_table">
    <div class="row">
      <div class="col-md-12">
        <div class="table-responsive">
        <table class="table table-bordered no-marg" id="tbl_emp_list" style="padding: 0 !important;">
          <thead>
            <tr class="table-heading-row">
              <th>Description</th>
              <th class="text-right">Basic_Amount</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if($tour_subtotal != '0'){
            $tour_subtotal1 = currency_conversion($currency,$sq_booking['currency_code'],$tour_subtotal);
            ?>
          <tr>
            <td><strong class="font_5"><?php echo 'Tour Name: '.$tour_name.'<br/>'.'
                From '.$tour_date.' To '.$tour_to_date.','. '<br/>'.$passengers; ?></strong></td>
            <td class="text-right"><?php echo $tour_subtotal1; ?></td>
          </tr>
          <?php }
            if($plane_expense != '0'){
              $plane_expense1 = currency_conversion($currency,$sq_booking['currency_code'],$plane_expense);
              ?>
            <tr>
              <td><strong class="font_5"><?= 'Flight: '.$flights .'<br/>'.$passengers ?></strong></td>
              <td class="text-right"><?php echo $plane_expense1; ?></td>
            </tr>
            <?php }
          if($train_expense != '0'){ 
            $train_expense1 = currency_conversion($currency,$sq_booking['currency_code'],$train_expense);
            ?>  
            <tr>
              <td><strong class="font_5"><?= 'Train: '.$trains .'<br/>'.$passengers ?></strong></td>
              <td class="text-right"><?php echo $train_expense1; ?></td>
            </tr>
            <?php } 
            if($cruise_expense != '0'){
              $cruise_expense1 = currency_conversion($currency,$sq_booking['currency_code'],$cruise_expense);
            ?>
            <tr>
              <td><strong class="font_5"><?= 'Cruise: '.$cruises .'<br/>'.$passengers ?></strong></td>
              <td class="text-right"><?php echo $cruise_expense1; ?></td>
            </tr>
            <?php }
            ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="print_sec main_block">
<?php
if($bg != ''){
  $balance = ($total_paid > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_paid) + floatval($charge);
}else{
  $balance = floatval($net_total) - floatval($total_paid) + floatval($charge);
}
$balance = (floatval($balance) < 0) ? 0 : $balance;

$newBasic1 = currency_conversion($currency,$sq_booking['currency_code'],$subtotal);
$charge1 = currency_conversion($currency,$sq_booking['currency_code'],$charge);
$total_paid1 = currency_conversion($currency,$sq_booking['currency_code'],$total_paid);
$roundoff1 = currency_conversion($currency,$sq_booking['currency_code'],$roundoff);
$tcs_per = currency_conversion($currency,$sq_booking['currency_code'],$tcs_per);
$tds = currency_conversion($currency,$sq_booking['currency_code'],$tds);

$total_balance1 = currency_conversion($currency,$sq_booking['currency_code'],$balance);
$canc_amount = currency_conversion($currency,$sq_booking['currency_code'],$canc_amount);
?>
<!-- invoice_receipt_body_calculation -->
  <div class="row">
    <div class="col-md-12">
      <div class="main_block inv_rece_calculation border_block">
      <?php if($service_name =='Package Invoice'){ ?>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">BASIC AMOUNT </span><span class="float_r"><?= $newBasic1 ?></span></p></div>
        <?php }else{ 
            $newBasicd1 = currency_conversion($currency,$sq_booking['currency_code'],$newBasic);?>
            <div class="col-md-6"><p class="border_lt"><span class="font_5">BASIC AMOUNT </span><span class="float_r"><?= $newBasicd1 ?></span></p></div>
        <?php } ?>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">TOTAL </span><span class="font_5 float_r"><?= $net_total1 ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">TAX</span><span class="float_r"><?= str_replace(',','',$name).$service_tax_amount_show ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">CREDIT CARD CHARGES </span><span class="float_r"><?= $charge1 ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">TCS<?= '('.$tcs_tax.'%)'?> </span><span class="font_5 float_r"><?= $tcs_per ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">ADVANCE PAID </span><span class="font_5 float_r"><?= $total_paid1 ?></span></p></div> 


        <?php
        if($service_name =='Package Invoice'){ ?>

            <div class="col-md-6"><p class="border_lt"><span class="font_5">TDS </span><span class="float_r"><?= $tds ?></span></p></div>
            <?php
            if($bg != ''){ ?>
              <div class="col-md-6"><p class="border_lt"><span class="font_5">CANCELLATION CHARGES</span><span class="float_r"><?= $canc_amount ?></span></p></div>
            <?php }
            else{ ?>
              <div class="col-md-6"><p class="border_lt"><span class="font_5">CURRENT DUE </span><span class="font_5 float_r"><?= $total_balance1 ?></span></p></div>
              <?php } ?>
            <div class="col-md-6"><p class="border_lt"><span class="font_5">ROUNDOFF</span><span class="float_r"><?= $roundoff1 ?></span></p></div>
            <?php
            if($bg != ''){ ?>
              <div class="col-md-6"><p class="border_lt"><span class="font_5">CURRENT DUE </span><span class="font_5 float_r"><?= $total_balance1 ?></span></p></div>
            <?php } ?>
        <?php } ?>
        <?php
        if($service_name !='Package Invoice'){ ?>

            <div class="col-md-6"><p class="border_lt"><span class="font_5">ROUNDOFF</span><span class="float_r"><?= $roundoff1 ?></span></p></div>
            <?php
            if($bg != ''){ ?>
              <div class="col-md-6"><p class="border_lt"><span class="font_5">CANCELLATION CHARGES</span><span class="float_r"><?= $canc_amount ?></span></p></div>
              <div class="col-md-6"><p class="border_lt"><span class="font_5"></span></p></div>
            <?php }
            else{ ?>
              <div class="col-md-6"><p class="border_lt"><span class="font_5">CURRENT DUE </span><span class="font_5 float_r"><?= $total_balance1 ?></span></p></div>
              <?php } ?>
            <?php
            if($bg != ''){ ?>
              <div class="col-md-6"><p class="border_lt"><span class="font_5">CURRENT DUE </span><span class="font_5 float_r"><?= $total_balance1 ?></span></p></div>
            <?php } ?>
        <?php } ?>


      </div>
    </div>
  </div>
</section>

<!-- invoice_receipt_body_calculation -->
<?php
//Footer
include "../generic_footer_html.php"; ?>
<?php
//Generic Files
include "../../../../model.php"; 
include "../../print_functions.php";
require("../../../../../classes/convert_amount_to_word.php"); 
global $currency;
//Parameters
$invoice_no = $_GET['invoice_no'];
$income_id = $_GET['income_id'];
$invoice_date = $_GET['invoice_date'];
$customer_id = $_GET['customer_id'];
$service_name = $_GET['service_name'];
$basic_cost1 = $_GET['basic_cost'];
$tds = $_GET['tds'];
$service_tax = $_GET['service_tax'];
$net_amount = $_GET['net_amount'];
$total_paid = $_GET['total_paid'];
$balance_amount = $_GET['balance_amount'];
$sac_code = $_GET['sac_code'];

$balance_amount = ($balance_amount < 0) ? 0 : $balance_amount;

$sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from other_income_master where income_id='$income_id' and delete_status='0'"));
$branch_admin_id = ($_SESSION['branch_admin_id'] != '') ? $_SESSION['branch_admin_id'] : $sq_hotel['branch_admin_id'];
$net_total1 = currency_conversion($currency,$currency,$net_amount);
$amount_in_word = $amount_to_word->convert_number_to_words($net_total1,$currency);
//Header
if($app_invoice_format == "Standard"){include "../headers/standard_header_html.php"; }
if($app_invoice_format == "Regular"){include "../headers/regular_header_html.php"; }
if($app_invoice_format == "Advance"){include "../headers/advance_header_html.php"; }
?>

<hr class="no-marg">

<section class="print_sec main_block">

<!-- invoice_receipt_body_calculation -->
  <div class="row">
    <div class="col-md-12">
      <?php
      $newBasic1 = currency_conversion($currency,$currency,$basic_cost1);
      $tds1 = currency_conversion($currency,$currency,$tds);
      $service_tax = currency_conversion($currency,$currency,$service_tax);
      $total_paid1 = currency_conversion($currency,$currency,$total_paid);      
      $due1 = currency_conversion($currency,$currency,$balance_amount);
      ?>
      <div class="main_block inv_rece_calculation border_block">
        <div class="col-md-6"><p class="border_lt"><span class="font_5">BASIC AMOUNT </span><span class="float_r"><?= $newBasic1 ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">TOTAL </span><span class="font_5 float_r"><?= $net_total1 ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">TAX </span><span class="float_r"><?= $service_tax ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">ADVANCE PAID </span><span class="font_5 float_r"><?= $total_paid1 ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">TDS </span><span class="float_r"><?= $tds1 ?></span></p></div>
        <div class="col-md-6"><p class="border_lt"><span class="font_5">CURRENT DUE </span><span class="font_5 float_r"><?= $due1 ?></span></p></div>
      </div>
    </div>
  </div>

</section>
<?php 
//Footer
include "../generic_footer_html.php"; ?>
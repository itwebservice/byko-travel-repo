<?php
$sq_terms_cond = mysqli_fetch_assoc(mysqlQuery("select * from terms_and_conditions where type='Receipt' and active_flag ='Active'"));
$branch_status = $_GET['branch_status'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$branch_admin_id = isset($_SESSION['branch_admin_id']) ? $_SESSION['branch_admin_id'] : 1;
$emp_id = isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : 1;

if($branch_admin_id != 0){
  $branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$branch_admin_id'"));
  $sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
  $sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id' and active_flag='Active'"));
}
else{
  if($branch_admin_id == ''){
    $branch_admin_id1 = $branch_admin_id;
  }else{
    $branch_admin_id1 = 1;
  }

  $branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$branch_admin_id1'"));
  $sq_bank_count = mysqli_num_rows(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id1' and active_flag='Active'"));
  $sq_bank_branch = mysqli_fetch_assoc(mysqlQuery("select * from bank_master where branch_id='$branch_admin_id1' and active_flag='Active'"));
}

$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$emp_id'"));
if ($emp_id == '0' || $emp_id == '') {
  $emp_name = 'Admin';
} else{
  $emp_name = $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
}
?>

<div class="repeat_section main_block">

  <section class="print_sec_tp_s main_block">

    <!-- invloice_receipt_hedaer_top-->

    <div class="main_block inv_rece_header_top header_seprator header_seprator_4 mg_bt_10">

      <div class="row">

        <div class="col-md-4">

          <div class="inv_rece_header_left">

            <div class="inv_rece_header_logo">

              <img src="<?php echo $admin_logo_url ?>" class="img-responsive">

            </div>

          </div>

        </div>

        <div class="col-md-4 text-center pd_tp_5">

          <div class="inv_rece_header_left">

            <div class="inv_rec_no_detail">

              <h2 class="inv_rec_no_title font_5 font_s_21 no-marg no-pad">RECEIPT</h2>

              <h4 class="inv_rec_no font_5 font_s_14 no-marg no-pad"><?php echo $payment_id; ?></h4>

            </div>

          </div>

        </div>

        <div class="col-md-4 last_h_sep_border_lt">

          <div class="inv_rece_header_right text-right">

            <ul class="no-pad no-marg font_s_12 noType">

              <li>
                <h3 class=" font_5 font_s_16 no-marg no-pad caps_text"><?php echo $app_name; ?></h3>
              </li>

              <li>
                <p><?php echo ($branch_status == 'yes' ) ? $branch_details['address1'] . ',' . $branch_details['address2'] . ',' . $branch_details['city'] : $app_address ?></p>
              </li>

              <li><i class="fa fa-phone" style="margin-right: 5px;"></i> <?php echo ($branch_status == 'yes' )  ? $branch_details['contact_no'] : $app_contact_no ?></li>

              <li><i class="fa fa-envelope" style="margin-right: 5px;"></i><?php echo ($branch_status == 'yes'  && $branch_details['email_id'] != '') ? $branch_details['email_id'] : $app_email_id; ?></li>

              <li><span class="font_5">TAX NO : </span><?php echo ($service_tax_no=='') ? 'NA' : strtoupper($service_tax_no); ?></li>

            </ul>

          </div>

        </div>

      </div>

    </div>







    <!-- invloice_receipt_bottom-->

    <div class="main_block inv_rece_header_bottom mg_tp_10">

      <div class="row">
        <div class="col-md-7">

        <?php if ($customer_id != '' && $customer_id != 0) { ?>
          <div class="inv_rece_header_left mg_bt_10">

            <ul class="no-marg no-pad noType">

              <li>
                <h3 class="title font_5 font_s_16 no-marg no-pad">TO,</h3>
              </li>

              <li>
                <h3 class=" font_5 font_s_14 no-marg no-pad"><?php echo  $sq_customer['company_name']; ?></h3>
              </li>

              <li><i class="fa fa-user"></i> : <?php if ($customer_id != '' && $customer_id != 0) {
                                                  echo  $sq_customer['first_name'] . ' ' . $sq_customer['last_name'].$pass_name;
                                                } else {
                                                  echo $booking_id;
                                                } ?></li>



            </ul>

          </div>
        <?php } ?>

        </div>

        <div class="col-md-5">

          <div class="inv_rece_header_right mg_bt_10">

            <ul class="no-marg no-pad noType">

              <li><span class="font_5">RECEIPT FOR </span>: <?php echo $receipt_type; ?></li>

              <?php if ($payment_date != '') { ?><li><span class="font_5">RECEIPT DATE </span>: <?php echo date('d-m-Y', strtotime($receipt_date)); ?></li><?php } ?>

              <li><span class="font_5">TAX NO </span> : <?php echo ($customer_id != '' && $customer_id != 0 && isset($sq_customer['service_tax_no'])) ? $sq_customer['service_tax_no'] : 'NA'; ?></li>

            </ul>

          </div>

        </div>

      </div>

    </div>

  </section>
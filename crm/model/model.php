<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once dirname(dirname(__FILE__)) . '/classes/PHPMailer-6.2.0/src/PHPMailer.php';
include_once dirname(dirname(__FILE__)) . '/classes/PHPMailer-6.2.0/src/Exception.php';
include_once dirname(dirname(__FILE__)) . '/classes/PHPMailer-6.2.0/src/SMTP.php';
/*$seperator = strstr(strtoupper(substr(PHP_OS, 0, 3)), "WIN") ? "\\" : "/";
session_save_path('..'.$seperator.'xml'.$seperator.'session_dir');
ini_set('session.gc_maxlifetime', 6); // 3 hours
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_secure', FALSE);
ini_set('session.use_only_cookies', TRUE);*/
ini_set("session.gc_maxlifetime", 3 * 60 * 60);
ini_set('session.gc_maxlifetime', 3 * 60 * 60);
session_start();
if (!isset($_SESSION['emp_id']) || !isset($_SESSION['role_id'])) {
  $_SESSION['emp_id'] = 0;
  $_SESSION['role_id'] = 0;
}
date_default_timezone_set('Asia/Kolkata');
set_error_handler("myErrorHandler");
header('Access-Control-Allow-Origin: *');
function myErrorHandler($errno, $errstr, $errfile, $errline){
  // echo  "<br><br>".$errno."<br>".$errstr."<br>".$errfile."<br>".$errline;
}
$localIP = getHostByName(getHostName());

// Create connection
$servername = "localhost";
$username = "bykojourneys_crm_u";
$password = "wkMEL?LLUW;F";
$db_name = "bykojourneys_crm";
global $conn;
$conn = new mysqli($servername, $username, $password, $db_name);

define('BASE_URL', 'https://www.bykojourneys.co.in/crm/');

mysqli_query($conn, "SET SESSION sql_mode = ''");
$b2b_index_url = BASE_URL . 'Tours_B2B/view/index.php';

define('CSV_READ_URL', __DIR__ . '/../');

global $secret_key, $fbappId, $similar_text,$tcs_note;
$secret_key = "secret_key_for_iTours";
$appIdFB = "992136897992262";
$appSecretFB = "027a7dc4307802ae05e10f556ed9b20c";

$admin_logo_url = BASE_URL . 'images/Admin-Area-Logo.png';
$circle_logo_url = BASE_URL . 'images/logo-circle.png';
$report_logo_small_url = BASE_URL . 'images/Receips-Logo-Small.jpg';
$terms_conditions_url = BASE_URL . 'images/terms-condition.jpg';
$hotel_service_voucher = BASE_URL . 'images/hotel_service_voucher.jpg';
$transport_service_voucher = BASE_URL . 'images/transport_service_voucher.jpg';
$transport_service_voucher2 = BASE_URL . 'images/transport_service_voucher2.jpg';
$booking_form = BASE_URL . 'images/Booking-Form-new.jpg';
$b2b_pdf_image = BASE_URL . 'images/b2b_pdf_image.jpg';
$sidebar_strip = BASE_URL . 'images/sidebar-strip.jpg';
$voucher_id_proof = BASE_URL . 'images/voucher_id_proof.jpg';
$quotation_icon = BASE_URL . 'images/quotation-icon.png';

$txn_feild_note = "Please make sure Date, Amount, Mode, Creditor bank entered properly."; //Sale and Purchase transaction feild detail's Note 
$txn_feild_dnote = "Please make sure Date, Amount, Mode, Debitor bank entered properly."; //Sale and Purchase transaction feild detail's Note Debitor
$cancel_feild_note = "Note : Kindly take new booking who will travel from partially cancellation."; //Cancel feild note
$similar_text = ' / Similar'; //simliar hotel and transports
$tcs_note = 'TCS(5%) extra will be applicable on total.';
$quot_note = 'Note : This is only a quotation. No booking is made yet. The costing may differ as per availability.'; //Quot_note

//**********App Settings Global Variables start**************//
$sq_app_tax = mysqli_fetch_assoc(mysqli_query($conn, "select * from generic_count_master where id='1'"));
$setup_country_id = $sq_app_tax['setup_country_id'];
$app_invoice_format = $sq_app_tax['invoice_format'];
$setup_package = $sq_app_tax['setup_type'];
$session_emp_id = $_SESSION['emp_id'];

$sq_app_setting_count = mysqli_num_rows(mysqli_query($conn, "select * from app_settings"));
if ($sq_app_setting_count == 1) {

  $sq_app_setting = mysqli_fetch_assoc(mysqli_query($conn, "select * from app_settings"));
  $app_version = $sq_app_setting['app_version'];
  $currency = $sq_app_setting['currency'];
  $app_contact_no = $sq_app_setting['app_contact_no'];
  $app_landline_no = $sq_app_setting['app_landline_no'];
  $service_tax_no = strtoupper($sq_app_setting['service_tax_no']);
  $app_address = $sq_app_setting['app_address'];
  $app_website = $sq_app_setting['app_website'];
  $app_name = $sq_app_setting['app_name'];
  $bank_acc_no = $sq_app_setting['bank_acc_no'];
  $cin_no = $sq_app_setting['app_cin'];
  $bank_name_setting = $sq_app_setting['bank_name'];
  $bank_account_name = $sq_app_setting['bank_account_name'];
  $acc_name = $sq_app_setting['acc_name'];
  $bank_branch_name = $sq_app_setting['bank_branch_name'];
  $bank_ifsc_code = $sq_app_setting['bank_ifsc_code'];
  $bank_swift_code = $sq_app_setting['bank_swift_code'];
  $sms_username = $sq_app_setting['sms_username'];
  $sms_password = $sq_app_setting['sms_password'];
  $accountant_email = $sq_app_setting['accountant_email'];
  $tax_type = $sq_app_setting['tax_type'];
  $tax_pay_date = $sq_app_setting['tax_pay_date'];
  $b2c_flag = $sq_app_setting['b2c_flag'];
  $tax_name = $sq_app_setting['tax_name'];

  $app_email_id = $sq_app_setting['app_email_id'];
  if ($session_emp_id == 0) {
    $app_email_id_send = $sq_app_setting['app_email_id'];
    $app_user_name_send = "Admin";
    $app_smtp_status = $sq_app_setting['app_smtp_status'];
    $app_smtp_host = $sq_app_setting['app_smtp_host'];
    $app_smtp_port = $sq_app_setting['app_smtp_port'];
    $app_smtp_password = $sq_app_setting['app_smtp_password'];
    $app_smtp_method = $sq_app_setting['app_smtp_method'];
    $app_send_contact_no = $sq_app_setting['app_contact_no'];
  } else {
    $sq_emp = mysqli_fetch_assoc(mysqli_query($conn, "select * from emp_master where emp_id='$session_emp_id'"));
    $app_email_id_send = $sq_emp['email_id'];
    $app_user_name_send = $sq_emp['first_name'];
    $app_smtp_status = $sq_emp['app_smtp_status'];
    $app_smtp_host = $sq_emp['app_smtp_host'];
    $app_smtp_port = $sq_emp['app_smtp_port'];
    $app_smtp_password = $sq_emp['app_smtp_password'];
    $app_smtp_method = $sq_emp['app_smtp_method'];
    $app_send_contact_no = $sq_emp['mobile_no'];
  }
  $app_cancel_pdf = $sq_app_setting['policy_url'];
  $app_credit_charge = $sq_app_setting['credit_card_charges'];
  $_SESSION['unique_receipt_id'] = $app_version . "/";
  $app_quot_format = $sq_app_setting['quot_format'];
  $app_quot_img = $sq_app_setting['quot_img_url'];
}
else{
  $app_version = $app_email_id = $app_email_id_send = $app_user_name_send = $currency = $app_contact_no = $service_tax_no = $app_address = $app_website = $app_name = $bank_acc_no = $bank_name_setting = $bank_branch_name = $bank_ifsc_code = $bank_swift_code = $app_smtp_status = $app_smtp_host = $app_smtp_port = $app_smtp_password = $app_smtp_method = $app_terms_condition = $cin_no = $app_landline_no = $acc_name = $accountant_email = $app_credit_charge = $app_quot_format = $app_send_contact_no = $app_quot_img = $b2c_flag = $tax_name = "";
}

//**********Theme color scheme variables**************//
$sq_count = mysqli_num_rows(mysqli_query($conn, "select * from app_color_scheme"));
if ($sq_count == 1) {
  $sq_scheme = mysqli_fetch_assoc(mysqli_query($conn, "select * from app_color_scheme"));
  $theme_color = $sq_scheme['theme_color'];
  $theme_color_dark = $sq_scheme['theme_color_dark'];
  $theme_color_2 = $sq_scheme['theme_color_2'];
  $topbar_color = $sq_scheme['topbar_color'];
  $sidebar_color = $sq_scheme['sidebar_color'];
} else {
  $theme_color = "#009898";
  $theme_color_dark = "#239ede";
  $theme_color_2 = "#1d4372";
  $topbar_color = "#ffffff";
  $sidebar_color = "#36aae7";
}

global $whatsapp_switch,$modify_entries_switch,$service_charge_switch,$show_entries_switch,$enquiry_assign_switch,$package_flight_switch,$package_train_switch,$package_cruise_switch,$bank_details_switch,$room_category_switch,$mealplan_tariff_switch;
// /////////////System Settings make it global /////////////////////////////////////
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='1'"));
$whatsapp_switch = ($sq_q['answer'] == 'Yes') ? 'on' : 'off';
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='2'"));
$modify_entries_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='3'"));
$service_charge_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='4'"));
$show_entries_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='11'"));
$enquiry_assign_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='12'"));
$package_flight_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='13'"));
$package_train_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='14'"));
$package_cruise_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='15'"));
$bank_details_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='16'"));
$room_category_switch = $sq_q['answer'];
$sq_q = mysqli_fetch_assoc(mysqli_query($conn, "select answer from generic_settings where entry_id='17'"));
$mealplan_tariff_switch = $sq_q['answer'];
// /////////////System Settings make it global end /////////////////////////////////////

//***********Whatsapp Switch***************/

//**Currency Symbol Display******
global $currency_logo, $currency_code;
$currency_logo_d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `default_currency`,`currency_code` FROM `currency_name_master` WHERE id=" . $currency));
$currency_code = $currency_logo_d['currency_code'];
$currency_logo = html_entity_decode($currency_logo_d['default_currency']);
//**********Mailer gloabal Variables**************//
global $mail_em_style, $mail_em_style1, $mail_font_family, $mail_strong_style, $mail_color;
$mail_color = "#2fa6df";
$mail_em_style = "border-bottom: 1px dotted #1f1f1f; padding-bottom: 4px; margin-bottom: 4px; display: inline-block; font-style:normal; color:#2fa6df;";
$mail_em_style1 = "font-style:normal; color:#2fa6df";
$mail_font_family = "font-family: 'Raleway', sans-serif;";
$mail_strong_style = "font-weight: 500; color:#000";

include_once "app_settings/generic_email_hf.php";

$role_id = $_SESSION['role_id'];
global $active_inactive_flag;
$active_inactive_flag = ($role_id == '1' || $role_id == '5') ? 'form-control' : 'hidden';
global $delete_flag;
$delete_flag = ($role_id == '1') ? 'form-control' : 'hidden';

class model extends email_hf
{ // extending email file

  public function generic_payment_mail($cms_id, $payment_amount, $payment_mode, $total_amount, $paid_amount, $payment_date, $due_date, $to, $subject, $fname, $currency_code = '', $outstanding = '')
  {
    global $currency_logo, $currency;
    if ($payment_amount != '0' && $payment_amount != '') {
      $balance_amount = ($outstanding == '') ? ($total_amount - $paid_amount) : $outstanding;
      if ($currency_code == '') {

        $total_amount = $currency_logo . ' ' . number_format($total_amount, 2);
        $balance_amount = $currency_logo . ' ' . number_format($balance_amount, 2);
        $paid_amount = $currency_logo . ' ' . number_format($paid_amount, 2);
      } else {

        $total_amount = currency_conversion($currency, $currency_code, $total_amount);
        $paid_amount = currency_conversion($currency, $currency_code, $paid_amount);
        $balance_amount = currency_conversion($currency, $currency_code, $balance_amount);
      }

      $due_date_html = '';
      if ($due_date != '') {
        $due_date_html = '<tr><td style="text-align:left;border: 1px solid #888888;"> Due Date</td>   <td style="text-align:left;border: 1px solid #888888;">' . get_date_user($due_date) . '</td></tr>';
      }
      $content = '
          <tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
              <tr><td style="text-align:left;border: 1px solid #888888;">Total Amount</td>   <td style="text-align:left;border: 1px solid #888888;">' . $total_amount . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Paid Amount</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $paid_amount . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Payment Mode</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $payment_mode . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Balance Amount</td>   <td style="text-align:left;border: 1px solid #888888;">' . $balance_amount . '</td></tr>
              ' . $due_date_html . '
            </table>
          </tr>';
      $this->app_email_send($cms_id, $fname, $to, $content, $subject);
    }
  }

  public function generic_payment_remainder_mail($cms_id, $fname,$paid_amount, $balance_amount, $tour_name, $booking_id, $customer_id, $to, $acc_status = '', $total_amount = '', $due_date = '')
  {

    global $currency_logo;
    $subject = '';
    $content = '
    <tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
              <tr><td style="text-align:left;border: 1px solid #888888;">Tour Name</td>   <td style="text-align:left;border: 1px solid #888888;">' . $tour_name . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;">Booking ID</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $booking_id . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Total Amount</td>   <td style="text-align:left;border: 1px solid #888888;">' . $currency_logo . ' ' . number_format($total_amount,2) . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Paid Amount</td>   <td style="text-align:left;border: 1px solid #888888;">' . $currency_logo . ' ' . number_format($paid_amount,2) . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Balance Amount</td>   <td style="text-align:left;border: 1px solid #888888;">' . $currency_logo . ' ' . $balance_amount . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Due Date</td>   <td style="text-align:left;border: 1px solid #888888;">' . get_date_user($due_date) . '</td></tr>
            </table>
          </tr>';
    $content;
    if ($acc_status != '') {
      $this->app_email_send($cms_id, $fname, $to, $content, $subject);
    } else {
      $this->app_email_send($cms_id, $fname, $to, $content, $subject, '1');
    }
  }

  public function topup_remainder_mail($balance_amount, $supplier_name)
  {
    global $app_name, $app_email_id, $currency_logo, $app_contact_no;

    $content = '
		<tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
              <tr><td style="text-align:left;border: 1px solid #888888;">Airline Name </td>   <td style="text-align:left;border: 1px solid #888888;">' . $supplier_name . '</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Current low balance</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $currency_logo . ' ' . $balance_amount . '</td></tr>
            </table>
          </tr>';

    $message = "Hello Admin, Your airline balance became low. Please transfer the payment and upgrade the balance.Airline Name : " . $supplier_name . " Current low balance : " . $balance_amount;

    $this->app_email_send('75', $app_name, $app_email_id, $content, '');
    $this->send_message($app_contact_no, $message);
  }

  public function visa_topup_remainder_mail($balance_amount, $supplier_name)
  {
    global $app_contact_no, $app_email_id_send, $app_user_name_send, $currency_logo;

    $content = '   <tr>
    <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
        <tr><td style="text-align:left;border: 1px solid #888888;">Visa Supplier Name</td>   <td style="text-align:left;border: 1px solid #888888;">' . $supplier_name . '</td></tr>
        <tr><td style="text-align:left;border: 1px solid #888888;">Current low balance</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $currency_logo . ' ' . $balance_amount . '</td></tr>
    </table>
    </tr>';
    $content;
    $message = "Hello Admin, Your visa balance became low. Please transfer the payment and upgrade the balance.Visa Supplier Name : " . $supplier_name . " Current low balance : " . $balance_amount;

    $this->app_email_send('76', $app_user_name_send, $app_email_id_send, $content, '');
    $this->send_message($app_contact_no, $message);
  }
  public function while_topup_mail_send($amount, $supplier_name, $for)
  {
    global $currency_logo, $app_email_id_send, $app_user_name_send;
    if ($for == 'visa') {


      $content = '   <tr>
      <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
          <tr><td style="text-align:left;border: 1px solid #888888;">Supplier Name</td>   <td style="text-align:left;border: 1px solid #888888;">' . $supplier_name . '</td></tr>
          <tr><td style="text-align:left;border: 1px solid #888888;">Amount</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $currency_logo . ' ' . $amount . '</td></tr>
      </table>
      </tr>';
      $content;
      $this->app_email_send('66', $app_user_name_send, $app_email_id_send, $content, '');
    } else {

      $content = '   <tr>
      <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
          <tr><td style="text-align:left;border: 1px solid #888888;">Supplier Name</td>   <td style="text-align:left;border: 1px solid #888888;">' . $supplier_name . '</td></tr>
          <tr><td style="text-align:left;border: 1px solid #888888;">Amount</td>   <td style="text-align:left;border: 1px solid #888888;" >' . $currency_logo . ' ' . $amount . '</td></tr>
      </table>
      </tr>';




      $content;
      $this->app_email_send('65', $app_user_name_send, $app_email_id_send, $content, '');
    }
  }

  public function app_email_master($to, $content, $subject, $acc_status = '')
  {
    global $app_email_id_send, $app_name,  $app_smtp_status, $app_smtp_host, $app_smtp_port, $app_smtp_password, $app_smtp_method, $emp_email_id, $emp_id, $accountant_email, $conn;
    if (empty($to)) return; // exit the function if no email is provided

    $body = parent::mail_header();
    $body .= $content;
    $body .= parent::mail_footer();

    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->IsSMTP();
    if ($app_smtp_status == "Yes") {
      $mail->Host = $app_smtp_host;
      $mail->Port = $app_smtp_port;
      $mail->SMTPAuth = true;
      $mail->SMTPDebug = 0;
      $mail->Username = $app_email_id_send;
      $mail->Password = $app_smtp_password;
      $mail->SMTPSecure = $app_smtp_method;
    }

    $mail->AddReplyTo($app_email_id_send, $app_name);
    $mail->SetFrom($app_email_id_send, $app_name);
    $mail->AddReplyTo($app_email_id_send, $app_name);
    $mail->AddAddress($to);
    $mail->AddCC($app_email_id_send, $app_name);

    //keep accountant in accountant
    if ($acc_status == '') {
      $mail->AddCC($accountant_email, $app_name);
    }
    $mail->Subject    = $subject;
    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";
    $mail->MsgHTML($body);
    if (!$mail->Send()) {
      echo "Mailer Error: " . $mail->ErrorInfo;
      echo 'Please enter valid email credentials!';
    } else {
      //echo "Mail sent!";
    }
  }
  ///////////////////////////// New Mail(CMS) Draft///////////////////////////////////////////////
  public function app_email_send($email_for, $fname, $to, $temp_content, $subject, $acc_status = '')
  {
    global $app_email_id_send, $app_name,$app_smtp_status, $app_smtp_host, $app_smtp_port, $app_smtp_password, $app_smtp_method, $accountant_email, $conn;

    if (empty($to)) return; // exit the function if no email is provided
    $sq_cms = mysqli_fetch_assoc(mysqli_query($conn, "select * from cms_master_entries where entry_id='$email_for'"));

    if ($sq_cms['active_flag'] != 'Inactive') {
      $content = $sq_cms['draft'];
      $content .= $temp_content;
      $content .= $sq_cms['signature'];
      $content = str_replace('{name}', ucfirst($fname), $content);

      $body = parent::mail_header();
      $body .= $content;
      $body .= parent::mail_footer();

      $mail = new PHPMailer;
      $mail->CharSet = 'UTF-8';
      $mail->IsSMTP();
      if ($app_smtp_status == "Yes") {
        $mail->Host = $app_smtp_host;
        $mail->Port = $app_smtp_port;
        $mail->SMTPAuth = true;
        $mail->SMTPDebug = 0;
        $mail->Username = $app_email_id_send;
        $mail->Password = $app_smtp_password;
        $mail->SMTPSecure = $app_smtp_method;
      }
      $mail->addReplyTo($app_email_id_send, $app_name);
      $mail->setFrom($app_email_id_send, $app_name);
      $mail->addReplyTo($app_email_id_send, $app_name);
      $mail->addAddress($to);

      //keep accountant in cc
      if ($acc_status == '' && !empty($accountant_email)) {
        $mail->AddCC($accountant_email, $app_name);
      }
      if ($subject == '') {
        $subject = $sq_cms['subject'];
      }
      $mail->Subject  = $subject;
      $mail->AltBody  = "To view the message, please use an HTML compatible email viewer!";
      $mail->MsgHTML($body);
      if (!$mail->Send()) {
        //echo "Mailer Error: ". $mail->ErrorInfo;
        echo 'Please enter valid email credentials!';
      } else {
        //echo "Mail sent!";
      }
    }
  }

  public function app_template_email_master($to, $content, $subject)
  {
    global $app_email_id_send, $app_name, $app_smtp_status, $app_smtp_host, $app_smtp_port, $app_smtp_password, $app_smtp_method;
    $body = '';
    $body .= $content;

    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->IsSMTP();
    if ($app_smtp_status == "Yes") {
      $mail->Host = $app_smtp_host;
      $mail->Port = $app_smtp_port;
      $mail->SMTPAuth = true;
      $mail->SMTPDebug = 0;
      $mail->Username = $app_email_id_send;
      $mail->Password = $app_smtp_password;
      $mail->SMTPSecure = $app_smtp_method;
    }

    $mail->AddReplyTo($app_email_id_send, $app_name);
    $mail->SetFrom($app_email_id_send, $app_name);
    $mail->AddReplyTo($app_email_id_send, $app_name);
    $mail->AddAddress($to, "");
    $mail->AddCC($app_email_id_send, $app_name);
    //$mail->AddCC($sq_emp['email_id'], $app_name);

    $mail->Subject = $subject;
    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";
    $mail->MsgHTML($body);
    if (!$mail->Send()) {
      //echo "Please enter valid email credentials!";
    } else {
      //echo "Mail sent!";
    }
  }
  //=======================Send Mail with attachment===========================//
  public function new_app_email_send($email_for, $to, $subject, $arrayAttachment, $temp_content, $acc_status = '')
  {

    global $app_email_id_send, $app_name, $app_smtp_status, $app_smtp_host, $app_smtp_port, $app_smtp_password, $app_smtp_method, $emp_id, $accountant_email, $conn;

    $sq_cms = mysqli_fetch_assoc(mysqli_query($conn, "select * from cms_master_entries where entry_id='$email_for'"));
    if ($sq_cms['active_flag'] != 'Inactive') {

      $session_emp_id = $_SESSION['emp_id'];
      $sq_emp = mysqli_fetch_assoc(mysqli_query($conn, "select * from emp_master where emp_id='$session_emp_id'"));
      $content = $sq_cms['draft'];
      $content .= $temp_content;
      $content .= $sq_cms['signature'];
      $content = str_replace('{name}', 'Customer', $content);

      $body = parent::mail_header();
      $body .= $content;
      $body .= parent::mail_footer();

      $mail = new PHPMailer;
      $mail->CharSet = 'UTF-8';
      $mail->IsSMTP();
      if ($app_smtp_status == "Yes") {
        $mail->Host = $app_smtp_host;
        $mail->Port = $app_smtp_port;
        $mail->SMTPAuth = true;
        $mail->SMTPDebug = 0;
        $mail->Username = $app_email_id_send;
        $mail->Password = $app_smtp_password;
        $mail->SMTPSecure = $app_smtp_method;
      }
      $mail->AddReplyTo($app_email_id_send, $app_name);
      $mail->SetFrom($app_email_id_send, $app_name);
      $mail->AddAddress($to, "");
      $mail->AddCC($app_email_id_send, $app_name);

      foreach ($arrayAttachment as $attachment) {
        $dir = dirname(dirname(__FILE__));
        $att_url =  str_replace("'/'", "'\'", $dir);
        $temp = explode('/',$attachment);
        $length = sizeof($temp)-1;
        $mail->AddAttachment($att_url . '/' . $attachment,$temp[$length]);
      }
      //keep accountant in cc
      if ($acc_status == '' && !empty($accountant_email)) {
        $mail->AddCC($accountant_email, $app_name);
      }
      $mail->AddCC($sq_emp['email_id'], $app_name);
      if (!empty($subject)) {
        $mail->Subject = $subject;
      } else {
        $mail->Subject = $sq_cms['subject'];
      }
      $mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
      $mail->MsgHTML($body);
      $mail->IsHTML(true);
      if (!$mail->Send()) {
        // echo "Please enter valid email credentials!";
      } else {
        unlink($arrayAttachment[0]);
      }
    }
  }

  //=======================Send Mobile message Message start===========================//
  public function send_message($mobile_no, $message)
  {
    global $sms_username, $sms_password;
    $username = urlencode($sms_username);
    $sender_id = 'ITOURS';
    $sms_password = urlencode($sms_password); // optional (compulsory in transactional sms) 
    $message = isset($message) ? urlencode($message) : '';
    $mobile = isset($mobile_no) ? urlencode($mobile_no) :'';

    // $api = "http://smsjust.com/sms/user/urlsms.php?username=$username&pass=$sms_password&senderid=$sender_id&message=$message&dest_mobileno=$mobile&response=Y"; 
    // $response = file_get_contents($api,FALSE);


    $api = "http://tarangsms.reliableindya.info/sendsms.jsp?user=$username&password=4d47926921XX&senderid=ITOURS&mobiles=$mobile&sms=$message";
    // echo $api;
    $response = file_get_contents($api, FALSE);
  }

  //Send Whatsapp messages  
  public function send_whatspp_message($mobile_no, $message)
  {
    $data = array();
    $data = [
      'phone' => $mobile_no, // Receivers phone
      'body' => $message, // Message
    ];
    $json = json_encode($data); // Encode data to JSON
    // URL for request POST /message
    $url = 'https://eu26.chat-api.com/instance17553/message?token=nrrtgd9v1ktsqid9';
    // Make a POST request
    $options = stream_context_create([
      'http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/json',
        'content' => $json
      ]
    ]);
    // Send a request
    $result = file_get_contents($url, false, $options);
  }
}
global $model;
$model = new model();
//=======================App generic functions===========================//  
include_once('app_settings/app_generic_functions.php');
include_once('app_settings/get_ids.php');
include_once('app_settings/dropdown_master.php');
include_once('app_settings/particular_functions.php');
include_once('encrypt_decrypt.php');
// include_once("get_cache_data.php");
// include_once("app_settings/print_html/qr_sign_print.php");

function get_qr($type)
{
  $QrQry = mysqlQuery('select qr_url,sign_url from app_settings');
  $result = mysqli_fetch_assoc($QrQry);

  if ($type == 'Landscape Advanced') {
    if (!empty($result['qr_url'])) {
      $htmlQR = '<img src="' . BASE_URL . '/' . substr($result['qr_url'], 9) . '" alt="" width=100   class="img-thumbnail">';
    } else {
      $htmlQR = '<h4> No QR Img </h4>';
    }
    return $htmlQR;
  }
  // Protrait Advance
  if ($type == 'Protrait Advance') {

    if (!empty($result['qr_url'])) {
      $htmlQR = '<img src="' . BASE_URL . '/' . substr($result['qr_url'], 9) . '" alt="" width=100  class="img-thumbnail">';
    } else {
      $htmlQR = '<h4> No QR Img </h4>';
    }
    return $htmlQR;
  }
  if ($type == 'Protrait Creative') {

    if (!empty($result['qr_url'])) {
      $htmlQR = '<img src="' . BASE_URL . '/' . substr($result['qr_url'], 9) . '" alt="" width=100  class="img-thumbnail">';
    } else {
      $htmlQR = '<h4> No QR Img </h4>';
    }
    return $htmlQR;
  }
  //Landscape
  if ($type == 'Landscape Creative') {

    if (!empty($result['qr_url'])) {
      $htmlQR = '<img src="' . BASE_URL . '/' . substr($result['qr_url'], 9) . '" alt="" width=100  class="img-thumbnail">';
    } else {
      $htmlQR = '<h4> No QR Img </h4>';
    }
    return $htmlQR;
  }
  //Standard
  if ($type == 'Landscape Standard') {

    if (!empty($result['qr_url'])) {
      $htmlQR = '<img src="' . BASE_URL . '/' . substr($result['qr_url'], 9) . '" alt="" width=100  class="img-thumbnail">';
    } else {
      $htmlQR = '<h4> No QR Img </h4>';
    }
    return $htmlQR;
  }
  //protrait standard
  if ($type == 'Protrait Standard') {

    if (!empty($result['qr_url'])) {
      $htmlQR = '<img src="' . BASE_URL . '/' . substr($result['qr_url'], 9) . '" alt="" width=100  class="img-thumbnail">';
    } else {
      $htmlQR = '<h4> No QR Img </h4>';
    }
    return $htmlQR;
  }
  if ($type == 'general') {

    if (!empty($result['qr_url'])) {
      $htmlQR = '<img src="' . BASE_URL . '/' . substr($result['qr_url'], 9) . '" alt="" width=100  class="img-thumbnail">';
    } else {
      $htmlQR = '<h4> No QR Img </h4>';
    }
    return $htmlQR;
  }
}

function check_qr()
{
  $QrQry = mysqlQuery('select qr_url,sign_url from app_settings');
  $result = mysqli_fetch_assoc($QrQry);
  if (!empty($result['qr_url'])) {
    return true;
  } else {
    return false;
  }
}
function check_sign()
{
  $QrQry = mysqlQuery('select qr_url,sign_url from app_settings');
  $result = mysqli_fetch_assoc($QrQry);
  if (!empty($result['sign_url'])) {
    return true;
  } else {
    return false;
  }
}
function get_signature($url = false)
{
  $QrQry = mysqlQuery('select qr_url,sign_url from app_settings');
  $result = mysqli_fetch_assoc($QrQry);
  if (!empty($result['sign_url'])) {
    $htmlSign = '<img src="' . BASE_URL . '/' . substr($result['sign_url'], 9) . '" alt="" width=110 >';
  } else {
    $htmlSign = '<h4> No Sign Uploaded </h4>';
  }
  if ($url == true && !empty($result['sign_url'])) {
    $htmlSign = BASE_URL . '/' . substr($result['sign_url'], 9);
  }
  return $htmlSign;
}


function get_dates_for_package_itineary($quotation_id)
{
  $booking_id = $quotation_id;
  $package_booking_info = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$booking_id'"));

  $sq_itinerary_count = (empty($package_booking_info['enquiry_id'])) ? "select booking_id from package_tour_schedule_master where booking_id='$booking_id'" : "select id from package_quotation_program where quotation_id='" . $package_booking_info['quotation_id'] . "'";
  $sq_count = mysqli_num_rows(mysqlQuery($sq_itinerary_count));
  $fromDate = $package_booking_info['from_date'];
  $toDate = $package_booking_info['to_date'];

  $date1 = $fromDate;
  $date2 = $toDate;
  if ($sq_count != 0) {
    $dates = array();
    $days = array();
    $current = strtotime($date1);
    $date2 = strtotime($date2);
    $stepVal = '+1 day';
    while ($current <= $date2) {
      $dates[] = date('d-m-Y', $current);
      $days[] = date('l', $current);
      $current = strtotime($stepVal, $current);
    }
  }

  return $dates;
}
function get_dates_for_itineary($from_date,$to_date)
{
  $fromDate = get_date_db($from_date);
  $to_date = get_date_db($to_date);

  $date1 = $fromDate;
  $date2 = $to_date;
  $dates = array();
  $days = array();
  $current = strtotime($date1);
  $date2 = strtotime($date2);
  $stepVal = '+1 day';
  while ($current <= $date2) {
    $dates[] = date('d-m-Y', $current);
    $days[] = date('l', $current);
    $current = strtotime($stepVal, $current);
  }

  return json_encode($dates).'='.json_encode($days);
}


function get_dates_for_tour_itineary($quotation_id)
{
  $booking_id = $quotation_id;
  $group_booking_info = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_quotation_master where quotation_id='$booking_id'"));

  $date1 = $group_booking_info['from_date'];
  $date2 = $group_booking_info['to_date'];

  $dates = array();
  $days = array();
  $current = strtotime($date1);
  $date2 = strtotime($date2);
  $stepVal = '+1 day';
  while ($current <= $date2) {
    $dates[] = date('d-m-Y', $current);
    $days[] = date('l', $current);
    $current = strtotime($stepVal, $current);
  }
  return $dates;
}


global $dbMain;
$dbMain = array(
  'user' => $username,
  'pass' => $password,
  'db'   => $db_name,
  'host' => $servername
);
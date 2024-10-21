<?php
ob_start();
include_once('../model.php');
require("../../classes/convert_amount_to_word.php");
define('FPDF_FONTPATH','../../classes/fpdf/font/');
require('../../classes/fpdf/fpdf.php');

ob_end_clean(); //    the buffer and never prints or returns anything.
ob_start();
global $currency,$currency_code;

$voucher_no = $_GET['v_voucher_no'];
$refund_date = $_GET['v_refund_date'];
$refund_to = $_GET['v_refund_to'];
$customer_id = $_GET['customer_id'];
$refund_id = $_GET['refund_id'];
$cancel_type = isset($_GET['cancel_type']) ? $_GET['cancel_type'] : 'Full';
$booking_id = isset($_GET['booking_id']) ? ' ('.$_GET['booking_id'].')' : '';

$service_name = $_GET['v_service_name'];
$refund_amount = $_GET['v_refund_amount'];
$payment_mode = $_GET['v_payment_mode'];
$currency_code1 = $_GET['currency_code'];
$cust_name = isset($_GET['cust_name']) ? $_GET['cust_name'] : '';

$refund_date = get_date_user($refund_date);
$service_name = ($service_name == 'Activity Booking') ? "Excursion Booking" : $service_name;
$service_name = ($service_name == 'Flight Booking') ? "Air Ticket Booking" : $service_name;
$sq_credit_note = mysqli_fetch_assoc(mysqlQuery("select * from credit_note_master where module_name='$service_name' and customer_id='$customer_id' and refund_id='$refund_id'"));
$credit_note_id = get_credit_note_id($sq_credit_note['id']);

$branch_admin_id = ($_SESSION['branch_admin_id'] != '') ? $_SESSION['branch_admin_id'] : '1';
$branch_details = mysqli_fetch_assoc(mysqlQuery("select * from branches where branch_id='$branch_admin_id'"));
$address = $branch_details['address1'] . ',' . $branch_details['address2'] . ',' . $branch_details['city'];
$contact_no =  $branch_details['contact_no'];
$email_id =  $branch_details['email_id'];

if($cust_name==''){
	$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	$cust_name = ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') ? $sq_customer['company_name'] : $sq_customer['first_name'].' '.$sq_customer['last_name'];
}

if($payment_mode == 'Credit Note') { $refund_mode = 'Credit Note ('.$credit_note_id.')'; }
else{ $refund_mode = $payment_mode; }

if($currency_code1 != '' && $currency != $currency_code1){

	$refund_amount1 = currency_conversion($currency,$currency_code1,$refund_amount);
}else{
	$refund_amount1 = $currency_code.' '.$refund_amount;
}

$pdf = new FPDF();
$pdf->addPage();
$pdf->SetFont('Arial','',10);

$count = 1;

$offset = ($count=="1") ? 0 : 135;
$pdf->SetFillColor(235);
$pdf->rect(0, 0+$offset, 210, 25, 'F');
$pdf->Image($admin_logo_url, 10, 4+$offset, 45, 17);

$pdf->SetFont('Arial','',16);

$pdf->SetXY(150, 10+$offset);

$pdf->MultiCell(200, 5, 'REFUND VOUCHER');

$pdf->SetFont('Arial','',17);

$pdf->SetXY(10, 30+$offset);

$pdf->Cell(200, 7, $app_name);

$pdf->SetFont('Arial','',10);

$pdf->SetXY(10, 38+$offset);

$pdf->MultiCell(85, 4, $address,0);

$pdf->SetFont('Arial','',10);

$pdf->SetXY(10, 57+$offset);

$pdf->MultiCell(200, 5, 'Contact : '.$contact_no."    Email : ".$email_id);

$pdf->SetDrawColor(200, 200, 200);

$pdf->SetFont('Arial','',12);

$pdf->SetXY(130, 35+$offset);

$pdf->MultiCell(30, 8, '  Voucher No.', 1);

$pdf->SetXY(160, 35+$offset);

$pdf->MultiCell(40, 8, $voucher_no, 1);

$pdf->SetXY(130, 43+$offset);

$pdf->MultiCell(30, 8, '  Date', 1);

$pdf->SetXY(160, 43+$offset);

$pdf->MultiCell(40, 8, $refund_date, 1);

$pdf->SetXY(130, 51+$offset);

$pdf->MultiCell(30, 8,'  '.get_tax_name().' No.', 1);

$pdf->SetXY(160, 51+$offset);

$pdf->MultiCell(40, 8, $service_tax_no, 1);

$pdf->line(0, 65+$offset, 210, 65+$offset);

$pdf->SetFont('Arial','',10);

$pdf->SetXY(10, 70+$offset);
$pdf->MultiCell(45, 7, '  Customer', 1);
$pdf->SetXY(55, 70+$offset);
$pdf->MultiCell(145, 7, $cust_name.$booking_id, 1);

$pdf->SetXY(10, 77+$offset);
$pdf->MultiCell(45, 7, '  Behalf Of Service(s)', 1);
$pdf->SetXY(55, 77+$offset);
$pdf->MultiCell(145, 7, $service_name, 1);

$pdf->SetXY(10, 84+$offset);
$pdf->MultiCell(45, 7, '  Refund Amount', 1);
$pdf->SetXY(55, 84+$offset);
$pdf->MultiCell(145, 7, $refund_amount1, 1);

$pdf->SetXY(10, 91+$offset);
$pdf->MultiCell(45, 7, '  Payment Mode', 1);
$pdf->SetXY(55, 91+$offset);
$pdf->MultiCell(145, 7, $refund_mode, 1);

$pdf->SetXY(10, 98+$offset);
$pdf->MultiCell(45, 7, '  Sales Return', 1);
$pdf->SetFont('Arial','',8);
$pdf->SetXY(55, 98+$offset);
$pdf->MultiCell(145, 7, $cancel_type, 1);

$pdf->SetFont('Arial','B',10);
$pdf->SetXY(30, 165+$offset);
$pdf->MultiCell(45, 7, 'Receiver Signature');
$pdf->rect(26, 145+$offset, 40, 20);

$pdf->SetFont('Arial','B',10);
$pdf->SetXY(125, 165+$offset);

$pdf->MultiCell(70, 7, 'For '.$app_name, 0, 'C');

// $pdf->rect(140, 145+$offset, 40, 20);
if(check_sign())
{
$pdf->Image(get_signature(true), 140, 145+$offset, 40, 20);
}

$filename = $refund_to.'_RefundVoucher'.'.pdf';
$pdf->Output($filename,'I');
ob_end_flush();
?>
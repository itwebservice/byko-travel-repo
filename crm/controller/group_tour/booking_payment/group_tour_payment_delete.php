<?php
include "../../../model/model.php";
include "../../../model/group_tour/booking_payment.php";
include_once('../../../model/app_settings/transaction_master.php');
include_once('../../../model/app_settings/bank_cash_book_master.php');
include_once('../../../model/app_settings/deleted_entries_save.php');

$booking_payment=new booking_payment();
$booking_payment->payment_master_delete();
?>

<?php
include_once('../../../model/model.php');
include_once('../../../model/car_rental/booking_master.php');
include_once('../../../model/car_rental/payment_master.php');
include_once('../../../model/app_settings/transaction_master.php');
include_once('../../../model/app_settings/bank_cash_book_master.php');
include_once('../../../model/app_settings/deleted_entries_save.php');

$booking_master = new booking_master;
$booking_master->car_booking_delete();
?>
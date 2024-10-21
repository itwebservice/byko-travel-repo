<?php 
include_once('../../../model/model.php');
include_once('../../../model/visa_password_ticket/train_ticket/ticket_payment_master.php');
include_once('../../../model/app_settings/transaction_master.php');
include_once('../../../model/app_settings/bank_cash_book_master.php');
include_once('../../../model/app_settings/deleted_entries_save.php');

$ticket_payment_master = new ticket_payment_master;
$ticket_payment_master->ticket_payment_master_delete();
?>
<?php 
error_reporting(E_ALL);
include '../config.php';

$general = mysqli_fetch_assoc(mysqlQuery("SELECT setting_id, app_version, app_email_id, currency, app_contact_no, app_landline_no, app_address, app_website, app_name, country FROM app_settings"));

          echo json_encode($general);
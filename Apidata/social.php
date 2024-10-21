<?php 
error_reporting(E_ALL);
include '../config.php';

$general = mysqli_fetch_assoc(mysqlQuery("SELECT social_media FROM b2c_settings LIMIT 1"));

$social=json_decode($general)[0];

          echo json_encode([$social]);
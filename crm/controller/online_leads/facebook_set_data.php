<?php 
include "../../model/model.php"; 
include_once('../../model/online_leads/facebook.php');

$facebook_leads = new facebook(); 
$facebook_leads->setData();
?>
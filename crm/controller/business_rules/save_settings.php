<?php
include "../../model/model.php"; 
include "../../model/business_rules/system_settings.php";

$setting_master = new system_settings(); 
$setting_master->save();
?>
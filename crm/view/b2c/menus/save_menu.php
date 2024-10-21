<?php
include '../../../model/model.php';

$menu = $_POST['menu'];
$encode = !empty($menu) ? json_encode($menu) : json_encode([]); 
mysqlQuery("UPDATE app_settings set menu_option='$encode'");
echo "Updated";

<?php
include "../../../model/model.php";
$package_code = $_POST['package_code'];
$package_id = $_POST['package_id'];

$query = "select * from custom_package_master where package_code='$package_code' ";
if($package_id != ''){
    $query .= " and package_id!='$package_id'";
}
$tour_code_count = mysqli_num_rows(mysqlQuery($query));
if($tour_code_count>0){
echo "This package code already exists.";
}
else{
echo '';
}
<?php
include_once('../../model/model.php');
include_once('../../model/visa_master/b2b_operations.php');

$b2b_operations = new b2b_operations;
$b2b_operations->enquiry_save();
?>
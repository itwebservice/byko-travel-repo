<?php
include_once('../../../model/model.php');
include_once('../../../model/group_tour/b2b_operations.php');

$b2b_operations = new b2b_operations;
$b2b_operations->search_session_save();
?>
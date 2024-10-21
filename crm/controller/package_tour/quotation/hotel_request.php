<?php
include "../../../model/model.php"; 
include "../../../model/package_tour/quotation/quotation_hotel_request.php"; 

$quotation_save = new quotation_hotel_request;
$quotation_save->send_common();
?>
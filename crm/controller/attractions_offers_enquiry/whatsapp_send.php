<?php
include "../../model/model.php";
include "../../model/attractions_offers_enquiry/enquiry_master.php";

$enquiry_master_whatsapp = new enquiry_master();
$enquiry_master_whatsapp->whatsapp_send();
?>
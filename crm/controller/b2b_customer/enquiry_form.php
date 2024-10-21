<?php 
include "../../model/model.php"; 
include "../../model/b2b_customer/enquiry_form.php"; 

$b2b_customer = new enquiry_master(); 
$b2b_customer->form_send();
?>
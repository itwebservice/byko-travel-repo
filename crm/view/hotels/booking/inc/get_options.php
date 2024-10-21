<?php
include "../../../../model/model.php";
$quotation_id = $_GET['quotation_id'];
if($quotation_id == ''){
    ?>
    <option value="">Select Option</option>
<?php
}
else{
    $qDetails = mysqli_fetch_assoc(mysqlQuery("SELECT `hotel_details` FROM `hotel_quotation_master` WHERE `quotation_id` =".$quotation_id));
    $hotelDetails = json_decode($qDetails['hotel_details'],true);
    for($index = 0; $index<sizeof($hotelDetails); $index++){
        echo '<option value="'.($index+1).'">Option-'.($index+1).'</option>';
    }
}
?>
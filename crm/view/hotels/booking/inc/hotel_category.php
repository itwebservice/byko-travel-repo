<?php include "../../../../model/model.php"; 
if($room_category_switch == 'No'){
    get_room_category_dropdown();
    exit;
}?>
<?php 

$hotel_id = $_GET['hotel_id'];

?>
<option value="">Room Category</option>
<?php
$room_category_arr = [];
$sq_hotel = mysqlQuery("select * from hotel_vendor_price_master where hotel_id='$hotel_id'");
while($row_hotel = mysqli_fetch_assoc($sq_hotel))
{
    //contracted tarrif
    $sq_category = mysqlQuery("select * from hotel_contracted_tarrif where pricing_id='$row_hotel[pricing_id]'");
    while($sq_cat = mysqli_fetch_assoc($sq_category)){
    if(!in_array($sq_cat['room_category'],$room_category_arr)){
?>
	<option value="<?php echo $sq_cat['room_category'] ?>"><?php echo $sq_cat['room_category'] ?></option>
<?php	
array_push($room_category_arr,$sq_cat['room_category']);
}
}
    //weekend tarrif
    $sq_category = mysqlQuery("select * from hotel_weekend_tarrif where pricing_id='$row_hotel[pricing_id]'");
    while($sq_cat = mysqli_fetch_assoc($sq_category)){
    if(!in_array($sq_cat['room_category'],$room_category_arr)){
?>
	<option value="<?php echo $sq_cat['room_category'] ?>"><?php echo $sq_cat['room_category'] ?></option>
<?php	
array_push($room_category_arr,$sq_cat['room_category']);
}
}
    //blackdated tarrif
    $sq_category = mysqlQuery("select * from hotel_blackdated_tarrif where pricing_id='$row_hotel[pricing_id]'");
    while($sq_cat = mysqli_fetch_assoc($sq_category)){
    if(!in_array($sq_cat['room_category'],$room_category_arr)){
    ?>
    <option value="<?php echo $sq_cat['room_category'] ?>"><?php echo $sq_cat['room_category'] ?></option>
    <?php	
    array_push($room_category_arr,$sq_cat['room_category']);
}
}

}

?>
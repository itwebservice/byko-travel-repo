<?php  
include "../../../model/model.php";
$booking_id = $_POST['booking_id'];
$count = 0;
$sq_tour = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$booking_id'"));
?>
<option value="">Select Passenger</option>
<?php 
      $sq_traveler = mysqlQuery("select * from travelers_details where traveler_group_id='$sq_tour[traveler_group_id]' and status!='Cancel'");
      while($row_traveler = mysqli_fetch_assoc($sq_traveler))
      {
      	$count++;
       ?>
       <option value="<?php echo $row_traveler['traveler_id'] ?>"><?php echo $count." : ".$row_traveler['first_name']." ".$row_traveler['last_name']; ?></option>
       <?php    
      }    
?>
<?php
include "../../model/model.php";
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$sq_customer = mysqlQuery("select * from customer_users where customer_id='$customer_id' and status='Active'");
?>
<select id="user_id" name="user_id" title="User" class="form-control">
    <option value="">Select User</option>
    <?php
    while($row_customer = mysqli_fetch_assoc($sq_customer)){
        ?>
        <option value="<?= $row_customer['user_id'] ?>"><?= $row_customer['name'] ?></option>
    <?php 
    } ?>
</select>
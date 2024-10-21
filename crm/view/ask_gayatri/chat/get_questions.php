<?php
error_reporting(E_ALL);
include "../../../model/model.php";
$qry = mysqlREString($_POST['query']);
echo $query = "select * from questions where question LIKE '%$qry%'";
die;
$run = mysqlQuery($query);
$data = [];
while ($db = mysqli_fetch_array($run)) {
?>
    <li value="<?= $db['question'] ?>"><?= $db['question'] ?></li>
    <?php
}
    ?>
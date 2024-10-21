<?php
include_once('../../../model/model.php');
$entry_id = $_POST['entry_id'];
if($entry_id != ""){
$sq_location = mysqli_fetch_assoc(mysqlQuery("select image_url from b2b_transfer_master where entry_id='$entry_id'"));
?>
<?php
if ($sq_location['image_url'] != '') {
    $newUrl = preg_replace('/(\/+)/', '/', $sq_location['image_url']);
    $download_url = BASE_URL . str_replace('../', '', $newUrl);
    ?>
    <div class="col-md-3">
        <div class="gallary-single-image mg_bt_20" style="height:100px;max-height: 100px;overflow:hidden;">
            <img src="<?php echo $download_url; ?>" id="<?php echo $entry_id; ?>" width="100%" height="100%">
            <span class="img-check-btn"><button type="button" class="btn btn-danger btn-sm" onclick="delete_image('<?php echo $entry_id; ?>')" title="Remove"><i class="fa fa-times" aria-hidden="true"></i></button></span>
        </div>
    </div>
<?php } ?>
<?php } ?>
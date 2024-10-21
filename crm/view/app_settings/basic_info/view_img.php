<?php

include "../../../model/model.php";

$type = $_REQUEST['type'];
$getUrl = mysqli_fetch_assoc(mysqlQuery('select qr_url,sign_url from app_settings limit 1'));
$img = $type == 'QR' ? BASE_URL.substr($getUrl['qr_url'],9) : BASE_URL.substr($getUrl['sign_url'],9);
?>

<div class="modal fade profile_box_modal" id="dmc_view_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">


        <div class="modal-body profile_box_padding">

        <ul class="nav nav-tabs" role="tablist">

<li role="presentation" class="active"><a href="#basic_information" aria-controls="home" role="tab" data-toggle="tab" class="tab_name">Image</a></li>

<li class="pull-right"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></li>

</ul>
        <div class="text-center">
        <img src="<?= $img ?>" alt="" width="300">
        </div>

            </div>

        </div>

    </div>

</div>



<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>

<script>
    $('#dmc_view_modal').modal('show');
</script>
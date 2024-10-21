<?php
include "../../../model/model.php";

$type = $_REQUEST['type'];
$getUrl = mysqli_fetch_assoc(mysqlQuery('select qr_url,sign_url from app_settings limit 1'));

    
if($type == 'QR')
{
    if(!empty($getUrl['qr_url']))
    {
        $fileUrl = BASE_URL.substr($getUrl['qr_url'],9);
        echo $fileUrl;
        unlink($fileUrl);
        $fire = mysqlQuery('update app_settings set qr_url=NULL');
    }

}
if($type == 'sign')
{
    if(!empty($getUrl['sign_url']))
    {
        if(!empty($getUrl['sign_url']))
        {
            $fileUrl = BASE_URL.substr($getUrl['sign_url'],9);
            unlink($fileUrl);
        }
     
        $fire = mysqlQuery('update app_settings set sign_url=NULL');
    }

}

 header('location:../index.php');

?>
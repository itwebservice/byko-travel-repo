<?php 
error_reporting(E_ALL);
include '../config.php';

$banners = mysqli_fetch_assoc(mysqlQuery("select * from b2c_settings "));
 
$banners = json_decode($banners['banner_images']);
        $imgs = array();
        foreach ($banners as $banner) {
            $url = $banner->image_url;
            $pos = strstr($url, 'uploads');
            if ($pos != false) {
                $newUrl1 = preg_replace('/(\/+)/', '/', $banner->image_url);
                $newUrl = str_replace('../', '', $newUrl1);
            } else {
                $newUrl =  $banner->image_url;
            }
            $imgs[] = $newUrl;
        }
          echo json_encode($imgs);
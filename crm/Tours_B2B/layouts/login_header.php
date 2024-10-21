<?php
global $currency;
$sq_curr = mysqli_fetch_assoc(mysqlQuery("select id from currency_name_master where id='$currency'"));
$query1 = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM `b2b_settings_second`"));
?>
<input type='hidden' id='global_currency' value='<?= $sq_curr['id'] ?>'/>
<input type="hidden" id="base_url" value="<?php echo BASE_URL ?>"/>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= $app_name ?></title>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
        <link rel="stylesheet/less" type="text/css" href="<?php echo BASE_URL ?>Tours_B2B/css/itours-styles.less" />
        <link rel="stylesheet/less" type="text/css" href="<?php echo BASE_URL ?>Tours_B2B/css/css-styles.css" />
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-ui.min.css" type="text/css" />
        <!-- <link rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/bootstrap-4.min.css" /> -->
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.wysiwyg.css">
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-labelauty.css" type="text/css" />
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/dynforms.vi.css">
        <script src="https://cdn.jsdelivr.net/npm/less@4.1.1"></script>
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/vi.alert.css">
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap-tagsinput.css">
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/app.php">
    </head>
    <body>
        <header class="c-header">
        <!-- **** Top Header End ***** -->
        <div class="pageHeader_bottom">
            <div class="container">
            <div class="row align-items-center">
                <div class="col-sm-4 col-6">
                <!-- Menubar Hamb btn for Mobile -->
                <button class="mobile_hamb"></button>
                <!-- Menubar Hamb btn for Mobile End -->

                <a href="<?php echo BASE_URL ?>Tours_B2B/login.php" class="btm_logo">
                    <img src='<?php echo BASE_URL ?>images/Admin-Area-Logo.png' alt="logo" />
                </a>
                </div>
                <div class="col-sm-8 col-6 text-right">
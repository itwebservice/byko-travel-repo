<?php
include "model/model.php";

$tokenUser = md5(uniqid(mt_rand(), true));
if(isset($_SESSION['username'])){ 
      destroy_all();
      recreate($tokenUser);  
}
global $app_version;
$_SESSION['token'] =$tokenUser; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>

  <link rel="icon" href="<?= $circle_logo_url ?>" type="image/gif" sizes="16x16">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,500" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/vi.alert.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/app.php">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/login.php">

  <script src="<?php echo BASE_URL ?>js/jquery-3.1.0.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/bootstrap.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/jquery.validate.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/vi.alert.js"></script>
  <script src="<?php echo BASE_URL ?>js/script.js"></script>

</head>
<body>

<section class="login-section">
  <div class="login-banner">
      <img src="images/login/plane.png" alt="plane" class="banner-plane-img login-banner-icon">
      <img src="images/login/passenger.png" alt="passenger" class="banner-passenger-img login-banner-icon">
      <img src="images/login/holiday.png" alt="holiday" class="banner-holiday-img login-banner-icon">
      <img src="images/login/sail.png" alt="sail" class="banner-sail-img login-banner-icon">
      <img src="images/login/suitcases.png" alt="suitcases" class="banner-suitcases-img login-banner-icon">
      <img src="images/login/travel.png" alt="travel" class="banner-travel-img login-banner-icon">
      <img src="images/login/bus.png" alt="bus" class="banner-bus-img login-banner-icon">
  </div>
  <form id="frm_login">
    <div class="main_block login_screen">
      <div class="login_wrap">
        
        <div class="logo-wrap"> <img src="<?= $circle_logo_url ?>" /> </div>
        <h3>Login to your account </h3>
        <input type="hidden" name="token" id="token" value="<?php echo $tokenUser;   ?>">
        <div class="login_wrap_inner">
          <div class="row">
            <div class="col-md-12">
              <label class="form-label">Username*</label>
              <input class="form-control" id="txt_username" name="txt_username" type="text" maxlength="30" placeholder="Username" autofocus> 
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-12">
              <label class="form-label">Password*</label>
              <div class="login-password-field">
                <input class="form-control" id="txt_password" name="txt_password" type="password" maxlength="30" placeholder="Password"/>    
                <a onclick="show_password('txt_password')" target="_BLANK" class="btn" title="View Password"><i class="fa fa-eye"></i></a>
              </div>
            </div>
            <!-- <div class="col-md-2">
            </div> -->
          </div><br>
  
          <div class="row">
            <div class="col-sm-12 col-xs-12">
              <label class="form-label">Financial Year</label> 
              <select name="financial_year_id" id="financial_year_id" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Financial Year">
                <?php get_financial_year_dropdown(false); ?>
              </select>
              <br>
            </div>
            <div class="col-sm-12 col-xs-12 mg_tp_10_sm_xs">
              <button class="app_btn" id='sign_in'><i class="fa fa-sign-in"></i>&nbsp;&nbsp;Sign In</button>
            </div>
          </div>
        </div>
        
        <div id="site_alert"></div>
      </div>
    </div>
  </form>
</section>

<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
<script src="<?= BASE_URL ?>js/app/auth.js"></script>
<script>
  $(function () {
    $('[data-toggle="tooltip"]').tooltip()
  });
  
</script>
  
</body>
</html>
<?php 
  function destroy_all()
  {
    session_destroy();
  }
  function recreate($tokenUser)
    {
      
      session_start();
      $_SESSION['token'] =$tokenUser;       
    }

?>
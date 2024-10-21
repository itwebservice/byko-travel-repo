<?php
global $currency, $app_name;
include "get_cache_currencies.php";
include "array_column.php";

$register_id = isset($_SESSION['register_id']) ? $_SESSION['register_id'] : '';
$customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : '';
$agent_flag = isset($_SESSION['agent_flag']) ? $_SESSION['agent_flag'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

$array_master = new array_master;
$sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
$to_currency_rate = $sq_to['currency_rate'];

$sq_reg = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM `b2b_registration` where register_id='$register_id'"));
$full_name = '';
$user_mobile_no = $sq_reg['mobile_no'];
if($agent_flag == '0'){
  $sq_user = mysqli_fetch_assoc(mysqlQuery("SELECT full_name,mobile_no FROM `b2b_users` where id='$user_id'"));
  $full_name = ' ('.$sq_user['full_name'].')';
  $user_mobile_no = $sq_user['mobile_no'];
}

//Get Approved Credit Limit Amount
$sq_credit = mysqli_fetch_assoc(mysqlQuery("select credit_amount from b2b_creditlimit_master where register_id='$register_id' and approval_status='Approved' and credit_amount!='0' order by entry_id desc"));

//Get Booking + Payment amount to calculate outstanding amount
$sq_booking = mysqlQuery("select * from b2b_booking_master where customer_id='$customer_id' and status!='Cancel'");
$net_total = 0;
$paid_amount = 0;
while($row_booking = mysqli_fetch_assoc($sq_booking)){

  $cart_checkout_data = ($row_booking['cart_checkout_data']!='' && $row_booking['cart_checkout_data'] != 'null')?json_decode($row_booking['cart_checkout_data']):[];
  $hotel_total = 0;
  $transfer_total = 0;
  $activity_total = 0;
  $tours_total = 0;
  $group_total = 0;
  $ferry_total = 0;
  for($i=0;$i<sizeof($cart_checkout_data);$i++){

		if($cart_checkout_data[$i]->service->name == 'Hotel'){
      $tax_arr = explode(',',$cart_checkout_data[$i]->service->hotel_arr->tax);
      for($j=0;$j<sizeof($cart_checkout_data[$i]->service->item_arr);$j++){
        $room_types = explode('-',$cart_checkout_data[$i]->service->item_arr[$j]);
        $room_cost = $room_types[2];
        $h_currency_id = $room_types[3];
        $tax_amount = 0;
        
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
				  if($tax_arr1[$t]!=''){
					$tax_arr2 = explode(':',$tax_arr1[$t]);
					if($tax_arr2[2] == "Percentage"){
					  $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
					}else{
					  $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
					}
				  }
				}
        $total_amount = $room_cost + $tax_amount;
        //Convert into default currency
        $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
        $from_currency_rate = $sq_from['currency_rate'];
        $total_amount = ($from_currency_rate / $to_currency_rate * $total_amount);
      
        $hotel_total += $total_amount;
      }
    }
    if($cart_checkout_data[$i]->service->name == 'Transfer'){

        $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
        for($j=0;$j<count(array($services));$j++){
          $tax_amount = 0;
          $tax_arr = explode(',',$services->service_arr[$j]->taxation);
          $transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
          $room_cost = $transfer_cost[0];
          $h_currency_id = $transfer_cost[1];
          
          $tax_arr1 = explode('+',$tax_arr[0]);
          for($t=0;$t<sizeof($tax_arr1);$t++){
            if($tax_arr1[$t]!=''){
              $tax_arr2 = explode(':',$tax_arr1[$t]);
              if($tax_arr2[2] == "Percentage"){
                $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
              }else{
                $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
              }
            }
          }
          $total_amount = $room_cost + $tax_amount;

          //Convert into default currency
          $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
          $from_currency_rate = $sq_from['currency_rate'];
          $total_amount = ($from_currency_rate / $to_currency_rate * $total_amount);
        
          $transfer_total += $total_amount;
        }
    }
		if($cart_checkout_data[$i]->service->name == 'Activity'){
      $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
			for($j=0;$j<count(array($services));$j++){
        $tax_amount = 0;
        $tax_arr = explode(',',$services->service_arr[$j]->taxation);
        $transfer_cost = explode('-',$services->service_arr[$j]->transfer_type);
				$room_cost = $transfer_cost[1];
				$h_currency_id = $transfer_cost[2];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
				  if($tax_arr1[$t]!=''){
					$tax_arr2 = explode(':',$tax_arr1[$t]);
					if($tax_arr2[2] === "Percentage"){
					  $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
					}else{
					  $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
					}
				  }
				}
				$total_amount = $room_cost + $tax_amount;

				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount = ($from_currency_rate / $to_currency_rate * $total_amount);
			
				$activity_total += $total_amount;
			}
    }
    if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
      
      $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
      for($j=0;$j<count(array($services));$j++){
			
				$tax_amount = 0;
			  $tax_arr = explode(',',$services->service_arr[$j]->taxation);
				$package_item = explode('-',$services->service_arr[$j]->package_type);
				$room_cost = $package_item[1];
				$h_currency_id = $package_item[2];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
				  if($tax_arr1[$t]!=''){
					$tax_arr2 = explode(':',$tax_arr1[$t]);
					if($tax_arr2[2] == "Percentage"){
					  $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
					}else{
					  $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
					}
				  }
				}
				$total_amount = $room_cost + $tax_amount;

				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount = ($from_currency_rate / $to_currency_rate * $total_amount);
			
				$tours_total += $total_amount;
			}
    }
    if($cart_checkout_data[$i]->service->name == 'Ferry'){
      
      $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
      for($j=0;$j<count(array($services));$j++){
			
				$tax_amount = 0;
			  $tax_arr = explode(',',$services->service_arr[$j]->taxation);
				$package_item = explode('-',$services->service_arr[$j]->total_cost);
				$room_cost = $package_item[0];
				$h_currency_id = $package_item[1];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
				  if($tax_arr1[$t]!=''){
					$tax_arr2 = explode(':',$tax_arr1[$t]);
					if($tax_arr2[2] == "Percentage"){
					  $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
					}else{
					  $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
					}
				  }
				}
				$total_amount = $room_cost + $tax_amount;

				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount = ($from_currency_rate / $to_currency_rate * $total_amount);
			
				$ferry_total += $total_amount;
			}
    }
    if($cart_checkout_data[$i]->service->name == 'Group Tours'){
    
        $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
        for($j=0;$j<count(array($services));$j++){
            $tax_arr = explode(',',$services->service_arr[$j]->taxation);
            $room_cost = $services->service_arr[$j]->total_cost;
            $h_currency_id = $services->service_arr[$j]->currency_id;
            
            $tax_amount = 0;
            $tax_arr1 = explode('+',$tax_arr[0]);
            for($t=0;$t<sizeof($tax_arr1);$t++){
                if($tax_arr1[$t]!=''){
                    $tax_arr2 = explode(':',$tax_arr1[$t]);
                    if($tax_arr2[2] == "Percentage"){
                        $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                    }else{
                        $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
                    }
                }
            }
            $total_amount = $room_cost + $tax_amount;

            $group_total += $total_amount;
        }
    }
  }
	$net_total += $hotel_total + $transfer_total + $activity_total + $tours_total + $ferry_total + $group_total;
  if($row_booking['coupon_code'] != ''){
		$sq_hotel_count = mysqli_num_rows(mysqlQuery("select offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_booking[coupon_code]'"));
    $sq_exc_count = mysqli_num_rows(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_booking[coupon_code]'"));
		if($sq_hotel_count > 0){
			$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer as offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_booking[coupon_code]'"));
		}else if($sq_exc_count > 0){
			$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_booking[coupon_code]'"));
		}else{
			$sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from custom_package_offers where coupon_code='$row_booking[coupon_code]'"));
    }
    if($sq_coupon['offer']=="Flat"){
      $net_total = $net_total - $sq_coupon['offer_amount'];
    }else{
      $net_total = $net_total - ($net_total*$sq_coupon['offer_amount']/100);
    }
  }
  // Paid Amount
  $sq_payment_info = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from b2b_payment_master where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
  $payment_amount = $sq_payment_info['sum'];
  $paid_amount +=$sq_payment_info['sum'];
}
$outstanding =  $net_total - $paid_amount;
$available_credit = ($outstanding >= 0) ? ($sq_credit['credit_amount'] - $outstanding) : $sq_credit['credit_amount'];
$available_credit = ($available_credit < 0)? 0.00 : $available_credit;
$_SESSION['credit_amount'] = $available_credit;
$credit_amount = $_SESSION['credit_amount'];
?>
<!DOCTYPE html>
<html>
    <head>
      <!-- Page Title -->
      <title><?= $app_name ?></title>

      <!-- Meta Tags -->
      <meta charset="utf-8" />
      <meta name="keywords" content="HTML5 Template" />
      <meta
        name="description"
        content="iTours - Travel, Tour Booking HTML5 Template"
      />
      <meta name="author" content="SoapTheme" />

      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <link rel="shortcut icon" href="<?php echo BASE_URL ?>Tours_B2B/images/favicon.png" type="image/x-icon" />

      <!-- Theme Styles -->
      <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
      <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
      <link rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/bootstrap-4.min.css" />
      <link rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/owl.carousel.min.css" />
      <link id="main-style" rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/itours-styles.css" />
      <link id="main-style" rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/itours-components.css" />
      <link id="main-style" rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/vi.alert.css" />
      <link rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/pagination.css" />
      <link rel="stylesheet" href="<?php echo BASE_URL ?>Tours_B2B/css/jquery-confirm.css">
      <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.datetimepicker.css">
      

      <!-- Javascript Page Loader -->
    </head>
    <body>
        <input type="hidden" id="base_url" name="base_url" value="<?= BASE_URL ?>">
        <input type="hidden" id="register_id" value="<?= $register_id ?>"/>
        <input type="hidden" id="customer_id" value="<?= $customer_id ?>"/>
        <input type="hidden" id="global_currency" value="<?= $currency ?>"/>
      <div class="c-pageWrapper">
             <!-- ********** Component :: Header ********** -->
      <div class="clearfix">
          <!-- **** Top Header ***** -->
          <div class="c-pageHeaderTop">
            <div class="pageHeader_top mobileSidebar">

              <!-- Menubar close btn for Mobile -->
              <button class="closeSidebar forMobile"></button>
              <!-- Menubar close btn for Mobile End -->

              <div class="container">
                <div class="row">

                  <div class="col-md-4 col-12 section-1">
                    <span class="staticText">Helpline : <?= $app_contact_no ?></span>
                  </div>

                  <div class="col-md-4 col-12 section-2">
                    <div class="credBalance">
                      <span class="s1">Credit Balance:</span>
                      <span class="currency-icon"></span></span><span class="s2" id="credit_amount"></span>
                    </div>
                  </div>

                  <div class="col-md-4 col-12 section-3">
                    <div class="topListing">
                      <ul>
                        <li>
                          <div class="c-select2DD st-clear">
                          <div id='currency_dropdown'></div>
                          </div>
                        </li>
                        <li>
                          <div class="c-select2DD st-clear">
                            <select name="state">
                              <option value="English">English</option>
                            </select>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Menubar for Mobile -->
              <div class="menuBar forMobile">
                <ul>
                  <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=ComboTours">Holiday</a></li>
                  <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=GroupTours">Group Tour</a></li>
                  <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Hotels">Hotels</a></li>
                  <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Activities">Activities</a></li>
                  <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Transfer">Transfer</a></li>
                  <li><a class="menuLink" onclick="get_tours_data('visa')">Visa</a></li>
                  <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Ferry">Ferry</a></li>
                  <li><a class="menuLink" href="<?php echo BASE_URL ?>Tours_B2B/login.php">Logout</a></li>
                </ul>
              </div>
              <!-- Menubar for Mobile End -->

            </div>
          </div>
          <!-- **** Top Header End ***** -->

          <!-- **** Bottom Header ***** -->
          <div class="c-pageHeader">
            <div class="pageHeader_btm">
              <div class="container">
                <div class="row align-items-center">
                  <div class="col-sm-3 col-7 p0-right">

                    <!-- Menubar Hamb btn for Mobile -->
                    <button class="mobile_hamb"></button>
                    <!-- Menubar Hamb btn for Mobile End -->

                    <a href="<?= $b2b_index_url ?>" class="btm_logo">
                      <img src='<?php echo BASE_URL ?>images/Admin-Area-Logo.png' alt="iTours" />
                    </a>
                  </div>
                  <div class="col-sm-9 col-5 text-right p0-left">
                    <div class="menuBar">
                      <ul>
                        <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=ComboTours">Holiday</a></li>
                        <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=GroupTours">Group Tour</a></li>
                        <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Hotels">Hotels</a></li>
                        <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Activities">Activities</a></li>
                        <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Transfer">Transfer</a></li>
                        <li><a class="menuLink" onclick="get_tours_data('visa')">Visa</a></li>
                        <li><a class="menuLink" href="<?= $b2b_index_url ?>?service=Ferry">Ferry</a></li>
                      </ul>
                    </div>

                    <button class="btnUtil" type="button" data-toggle="modal" data-target="#shopping_list_modal" aria-haspopup="true" aria-expanded="false" onclick='display_cart("cart_item_count");'>
                      <img src="<?php echo BASE_URL ?>Tours_B2B/images/svg/supermarket.svg" alt="iTours" />
                      <span class="notify" id='cart_item_count'></span>
                    </button>


                    <!-- ***** User Profile DD ***** -->
                    <?php
                    $active_status = ($agent_flag == '0') ? 'style="display: none;"' : '';
                    ?>
                    <div class="c-userProf dropdown">
                      <button class="btnUtil" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo BASE_URL ?>Tours_B2B/images/svg/user.svg" alt="iTours" />
                      </button>

                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <div class="userHeading">
                          <div class="profIcon">
                            <img src="<?php echo BASE_URL ?>Tours_B2B/images/svg/user.svg" alt="iTours" />
                          </div>
                          <h4 class="userName"><?=$sq_reg['company_name'].$full_name?></h4>
                          <span class="userCode"><?= $user_mobile_no ?></span>
                        </div>
                        <div class="userNav">
                          <a href="<?php echo BASE_URL ?>Tours_B2B/view/user-profile/profile.php" class="userNavLink" <?= $active_status ?>>
                            <i class="icon user itours-user"></i>
                            <span>Profile</span>
                          </a>
                          <a href="<?php echo BASE_URL ?>Tours_B2B/view/user-profile/profile.php" class="userNavLink" <?= $active_status ?>>
                            <i class="icon itours-user-1"></i>
                            <span>Users</span>
                          </a>
                          <a href="<?php echo BASE_URL ?>Tours_B2B/view/user-profile/profile.php" class="userNavLink">
                            <i class="icon user itours-shopping-cart"></i>
                            <span>Quotation Summary</span>
                          </a>
                          <a href="<?php echo BASE_URL ?>Tours_B2B/view/user-profile/profile.php" class="userNavLink">
                            <i class="icon user itours-shopping-cart"></i>
                            <span>Booking Summary</span>
                          </a>
                          <a href="<?php echo BASE_URL ?>Tours_B2B/view/user-profile/profile.php" class="userNavLink" <?= $active_status ?>>
                            <i class="icon user itours-ledger"></i>
                            <span>Account Ledger</span>
                          </a>
                          <a href="<?php echo BASE_URL ?>Tours_B2B/login.php" class="userNavLink">
                            <i class="icon user itours-logout"></i>
                            <span>Logout</span>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- ***** User Profile DD End ***** -->

                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- **** Bottom Header End ***** -->
      </div>
      <!-- ********** Component :: Header End ********** -->
<?php //include "get_cache_tax_rules.php";?>
<input type="hidden" id='cache_currencies' value='<?= $data ?>'/>
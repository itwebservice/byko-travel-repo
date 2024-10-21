<?php
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
if (isset($_SESSION['username']) && isset($_SESSION['itours_app'])) {

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {

        if (empty($_GET)) {

            $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $role = $_SESSION['role'];
            $role_id = $_SESSION['role_id'];
            $link = explode('view/', $actual_link);
            $link = $link[1];

            if ($link != "dashboard/dashboard_main.php" && $link != "reports/reports_homepage.php" && $link != "layouts/reminders_home.php") {
                $access_count = mysqli_num_rows(mysqlQuery("select * from user_assigned_roles where role_id='$role_id' and link='$link'"));
                if ($access_count == 0) {
                    header("location:" . BASE_URL);
                }
            }
        }
    }
} else {
    header("location:" . BASE_URL);
}

function admin_header_scripts()
{
    global $circle_logo_url;
?>
    <link rel="icon" href="<?= $circle_logo_url ?>" type="image/gif" sizes="16x16">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,500" rel="stylesheet">

    <!--========*****Header Stylsheets*****========-->
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-ui.min.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/select2.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.datetimepicker.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.wysiwyg.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/owl.carousel.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-labelauty.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/menu-style.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/btn-style.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/dynforms.vi.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap-tagsinput.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/admin.php">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/vi.alert.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/notification.php">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/app.php">
    <?php
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $link = explode('view/', $actual_link);
    $link = $link[1];
    if ($link == "dashboard/dashboard_main.php") {
    ?>
        <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/dashboard.php">
        <?php
    }

    //Including modules css
    $dir_name =  dirname(dirname(dirname(__FILE__))) . '/css/app/modules';
    $dir = $dir = new DirectoryIterator($dir_name);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
        ?>
            <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/modules/<?= $fileinfo->getFilename() ?>">
    <?php
        }
    }
    ?>

    <!--========*****Header Scripts*****========-->
    <!-- <link href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"></link> -->
    <script src="<?php echo BASE_URL ?>js/jquery-3.1.0.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery-ui.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.mCustomScrollbar.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.datetimepicker.full.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.wysiwyg.js"></script>
    <script src="<?php echo BASE_URL ?>js/script.js"></script>
    <script src="<?php echo BASE_URL ?>js/select2.full.js"></script>
    <script src="<?php echo BASE_URL ?>js/owl.carousel.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery-labelauty.js"></script>
    <script src="<?php echo BASE_URL ?>js/responsive-tabs.js"></script>
    <script src="<?php echo BASE_URL ?>js/dynforms.vi.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.validate.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/vi.alert.js"></script>
    <script src="<?php echo BASE_URL ?>js/app/data_reflect.js"></script>
    <script src="<?php echo BASE_URL ?>js/app/validation.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/dataTables.bootstrap.min.js"></script>
    <!-- <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script> -->
    <script src="<?php echo BASE_URL ?>js/bootstrap-tagsinput.min.js"></script>

<?php
}
function get_cache_data()
{

    if (file_exists(BASE_URL . 'view/cache_data.txt')) {
        $modified_time = filemtime(BASE_URL . 'view/cache_data.txt');
    } else {
        $modified_time = time() - 1 * 86400001;
    }
    $taxes_data = array();
    $taxes_rules_data = array();
    $other_rules_data = array();
    $credit_card_data = array();
    $new_array = array();
    if ($modified_time < time() - 1 * 86400000) {

        //Taxes
        $result = mysqlQuery("SELECT * FROM tax_master");
        while ($row = mysqli_fetch_array($result)) {
            $temp_array = array(
                'entry_id' => $row['entry_id'],
                'name1' => $row['name1'],
                'amount1' => $row['amount1'],
                'ledger1' => $row['ledger1'],
                'name2' => $row['name2'],
                'amount2' => $row['amount2'],
                'ledger2' => $row['ledger2'],
                'status' => $row['status']
            );
            array_push($taxes_data, $temp_array);
        }
        //Tax Rules
        $result = mysqlQuery("SELECT * FROM tax_master_rules");
        while ($row = mysqli_fetch_array($result)) {
            $temp_array = array(
                'rule_id' => $row['rule_id'],
                'entry_id' => $row['entry_id'],
                'name' => $row['name'],
                'validity' => $row['validity'],
                'from_date' => $row['from_date'],
                'to_date' => $row['to_date'],
                'ledger_id' => $row['ledger_id'],
                'travel_type' => $row['travel_type'],
                'calculation_mode' => json_encode($row['calculation_mode']),
                'target_amount' => $row['target_amount'],
                'applicableOn' => $row['applicableOn'],
                'conditions' => $row['conditions'],
                'status' => $row['status']
            );
            array_push($taxes_rules_data, $temp_array);
        }

        //Other Rules
        $result = mysqlQuery("SELECT * FROM other_master_rules");
        while ($row = mysqli_fetch_array($result)) {
            $temp_array = array(
                'rule_id' => $row['rule_id'],
                'rule_for' => $row['rule_for'],
                'name' => $row['name'],
                'type' => $row['type'],
                'validity' => $row['validity'],
                'from_date' => $row['from_date'],
                'to_date' => $row['to_date'],
                'ledger_id' => $row['ledger_id'],
                'travel_type' => $row['travel_type'],
                'fee' => $row['fee'],
                'fee_type' => $row['fee_type'],
                'target_amount' => $row['target_amount'],
                'conditions' => $row['conditions'],
                'status' => $row['status'],
                'apply_on' => $row['apply_on']
            );
            array_push($other_rules_data, $temp_array);
        }

        //Credit card company
        $result = mysqlQuery("SELECT * FROM credit_card_company where status='Active'");
        while ($row = mysqli_fetch_array($result)) {
            $temp_array = array(
                'entry_id' => $row['entry_id'],
                'company_name' => $row['company_name'],
                'charges_in' => $row['charges_in'],
                'credit_card_charges' => $row['credit_card_charges'],
                'tax_charges_in' => $row['tax_charges_in'],
                'tax_on_credit_card_charges' => $row['tax_on_credit_card_charges'],
                'membership_details_arr' => json_encode($row['membership_details_arr']),
                'status' => $row['status']
            );
            array_push($credit_card_data, $temp_array);
        }

        array_push($new_array, array('taxes' => $taxes_data, 'tax_rules' => $taxes_rules_data, 'other_rules' => $other_rules_data, 'credit_card_data' => $credit_card_data));
        // store query result in cache_data.txt
        // file_put_contents(BASE_URL . 'view/cache_data.txt', serialize(json_encode($new_array)));
        $url = BASE_URL . 'view/cache_data.txt';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, serialize(json_encode($new_array)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            echo 'Error: ' . curl_error($ch);
        } else {
        }
        curl_close($ch);

        $new_array = json_encode($new_array);
    } else {
        $new_array = unserialize(file_get_contents(ROOT_DIR . '\cache_data.txt'));
    }
    return $new_array;
}
function topbar_icon_list()
{
    global $app_name,$app_contact_no;
    $login_id = $_SESSION['login_id'];
    $emp_id = $_SESSION['emp_id'];
    $role_id = $_SESSION['role_id'];
    $financial_year_id = $_SESSION['financial_year_id'];

    $sq_finacial_year = mysqli_fetch_assoc(mysqlQuery("select * from financial_year where financial_year_id='$financial_year_id'"));
    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$emp_id'"));
    if ($sq_emp['first_name'] == '') {
        $emp_name = 'Admin';
        $contact = $app_contact_no;
    } else {
        $emp_name = $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
        $contact = $sq_emp['mobile_no'];
    }
    if ($sq_emp['photo_upload_url'] != "") {
        $newUrl1 = preg_replace('/(\/+)/', '/', $sq_emp['photo_upload_url']);
        $user_id = BASE_URL . str_replace('../', '', $newUrl1);
    } elseif ($sq_emp['first_name'] == '' or $sq_emp['first_name'] != '') {
        $user_id = BASE_URL . 'images/logo-circle.png';
    }
    ?>
    <input type="hidden" id="emp_id" name="emp_id" value="<?= $emp_id ?>">
    <li class="notifications_body">
        <a class="btn app_btn_out" data-toggle="tooltip" title="Dashboard" data-placement="bottom" href="<?php echo BASE_URL ?>view/dashboard/dashboard_main.php"><i class="fa fa-tachometer"></i>
            <pre class="xs_show">Dashboard</pre>
        </a>
    </li>
    <li class="logged_user_body text_center_sm_xs">
        <div class="logged_user" onclick="display_image1()">
            <span class="logged_user_id">
                <img src="<?php echo $user_id ?>" class="img-responsive"></span>
            <span class="logged_user_name"><?= $emp_name ?></span>
        </div>
        <div id="profile_pic_block_id" class="profile_pic_block">
            <?php include_once("display_image_modal1.php")  ?>
        </div>
    </li>
    <?php if ($role_id < 9) {
        ?>
        <li class="notifications_body">
            <a class="btn app_btn_out" data-toggle="tooltip" title="Notifications" data-placement="bottom" onclick="display_notification();enquiry_count_update();"><i class="fa fa-bell-o"></i>
                <pre class="xs_show">Notification</pre>
            </a>
            <span id="notify_count"></span>
        </li>
    <?php } ?>
    <li class="financial_yr">
        <a class="btn app_btn_out" data-toggle="tooltip" title="Financial Year" data-placement="bottom"><i class="fa fa-code-fork"></i><span class="">&nbsp;&nbsp;<?php echo get_date_user($sq_finacial_year['from_date']) . ' - ' . get_date_user($sq_finacial_year['to_date']); ?></span></a>
    </li>

    <li>
        <a class="btn app_btn_out" onclick="user_logout()" data-toggle="tooltip" title="Sign out" data-placement="bottom"><i class="fa fa-power-off"></i>
            <pre class="xs_show">Sign Out</pre>
        </a>
        <input type="hidden" id="login_id1" name="login_id1" value="<?= $login_id ?>">
        <input type="hidden" id="app_name" name="app_name" value="<?= $app_name ?>">
        <input type="hidden" id="app_contact_no" name="app_contact_no" value="<?= $contact ?>">
    </li>
<?php
}

function fullwidth_header_scripts()
{
    global $circle_logo_url;
 ?>
    <link rel="icon" href="<?= $circle_logo_url ?>" type="image/gif" sizes="16x16">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--========*****Header Stylsheets*****========-->
    <link href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet">
    </link>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-ui.min.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/select2.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.datetimepicker.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.wysiwyg.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-labelauty.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/menu-style.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/btn-style.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/vi.alert.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap-tagsinput.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/dynforms.vi.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/notification.php">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/app.php">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/fullwidth_app.php">

    <?php
    //Including modules css
    $dir_name =  dirname(dirname(dirname(__FILE__))) . '/css/app/modules';
    $dir = $dir = new DirectoryIterator($dir_name);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
    ?>
            <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/modules/<?= $fileinfo->getFilename() ?>">
    <?php
        }
    }
    ?>

    <!--========*****Header Scripts*****========-->
    <script src="<?php echo BASE_URL ?>js/jquery-3.1.0.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery-ui.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.mCustomScrollbar.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.datetimepicker.full.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.wysiwyg.js"></script>
    <script src="<?php echo BASE_URL ?>js/select2.full.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/responsive-tabs.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery-labelauty.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.validate.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/vi.alert.js"></script>
    <script src="<?php echo BASE_URL ?>js/app/data_reflect.js"></script>
    <script src="<?php echo BASE_URL ?>js/app/validation.js"></script>
    <script src="<?php echo BASE_URL ?>js/bootstrap-tagsinput.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/ckeditor_4.17.1_full/ckeditor.js"></script>
    <script src="<?php echo BASE_URL ?>js/dynforms.vi.js"></script>
<?php
}

?>
<div id="div_modal"></div>
<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script>
    function user_logout() {
        var login_id = $('#login_id1').val();
        var base_url = $('#base_url').val();

        $.post(base_url + 'controller/login/user_logout.php', {
            login_id: login_id
        }, function(data) {
            if (data == "valid") {
                localStorage.setItem("reminder", true);
                window.location.href = base_url + "index.php";
            }
        });
    }

    function display_image1() {
        $("#profile_pic_block_id").toggleClass('profile_pic_block_display');
    }

    function display_notification() {
        $("#notification_block_bg_id").toggle();
        $("#notification_block_body_id").slideToggle();
    }

    function enquiry_count_update() {
        var base_url = $('#base_url').val();
        $.post(base_url + 'controller/login/notification/enquiry_count_update.php', {     }, function(data) {
            document.getElementsByClassName('notify')[0].style.display = 'none';
        });
    }
</script>
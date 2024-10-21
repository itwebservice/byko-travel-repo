<form id="frm_tab_2_u">
    <div class="app_panel">
        <div class="container-fluid mg_tp_10">

            <div class="app_panel_content no-pad">
                    <!-- <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20"> -->
                        <legend class="<?= $hide_train ?>">Train Details</legend>
                        <div class="bg_white main_block panel-body panel panel-default <?= $hide_train ?>">
                            <?php
                            $train_info_count = mysqli_num_rows(mysqlQuery("select * from package_train_master where booking_id='$booking_id'"));
                            if ($train_info_count == 0) {
                                include_once('../booking_save/tab_2/train_info.php');
                            } else {
                                include_once('train_info.php');
                            }
                            ?>
                        </div>
                    <!-- </div> -->
                    <!-- <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20"> -->
                        <legend class="<?= $hide_flight ?>">Flight Details</legend>
                        <div class="bg_white main_block panel-body panel panel-default <?= $hide_flight ?>">
                            <?php
                            $train_info_count = mysqli_num_rows(mysqlQuery("select * from package_plane_master where booking_id='$booking_id'"));
                            if ($train_info_count == 0) {
                                include_once('../booking_save/tab_2/plane_info.php');
                            } else { ?>
                            <?php include_once('plane_info.php');
                            } ?>
                        </div>
                    <!-- </div> -->
                    <!-- <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20"> -->
                        <legend class="<?= $hide_cruise ?>">Cruise Details</legend>
                        <div class="bg_white main_block panel-body panel panel-default <?= $hide_cruise ?>">
                            <?php
                            $cruise_info_count = mysqli_num_rows(mysqlQuery("select * from package_cruise_master where booking_id='$booking_id'"));
                            if ($cruise_info_count == 0) {
                                include_once('../booking_save/tab_2/cruise_info.php');
                            } else {
                                include_once('cruise_info.php');
                            }
                            ?>
                        </div>
                    <!-- </div> -->
                    <div class="panel panel-default panel-body main_block bg_light mg_bt_10 <?= $all_tab ?>">
                        <div class="row text-center">
                            <div class="col-md-12 text-center">
                                <label>Total Travel Expense</label>
                            </div>
                            <div class="col-md-2 col-md-offset-5">
                                <input type="text" id="txt_travel_total_expense"
                                    class="amount_feild_highlight text-right" name="txt_travel_total_expense"
                                    title="Total Travel Expense" readonly
                                    value="<?php echo $sq_booking_info['total_travel_expense'] ?>" />
                            </div>
                        </div>
                    </div>
                <div class="panel panel-default main_block bg_light pad_8 text-center mg_bt_150" style="background-color: #fff; border: none;">
                    <div class="text-center">
                        <div class="col-md-12">
                            <button class="btn btn-sm btn-info ico_left" type="button" onclick="back_to_tab_1()"><i
                                    class="fa fa-arrow-left"></i>&nbsp;&nbsp;Previous</button>&nbsp;&nbsp;&nbsp;
                            <button class="btn btn-sm btn-info ico_right">Next&nbsp;&nbsp;<i
                                    class="fa fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
                <?= end_panel() ?>
            </div>
</form>

<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script src="../js/tab_2_calculations.js"></script>
<script src="../js/tab_2.js"></script>
<script>
$('#frm_tab_2_u').validate({
    submitHandler: function(form) {

        var valid_state = package_tour_booking_tab2_validate();
        if (valid_state == false) {
            return false;
        }

        calculate_total_travel_amount(true);
        get_auto_values('booking_date', 'total_basic_amt', 'payment_mode', 'service_charge', 'markup',
            'update', 'false', 'service_charge', 'discount');

        $('#tab_2_head').addClass('done');
        $('#tab_3_head').addClass('active');
        $('.bk_tab').removeClass('active');
        $('#tab_3').addClass('active');
        $('html, body').animate({
            scrollTop: $('.bk_tab_head').offset().top
        }, 200);
    }
});
</script>
<form id="frm_tab_2">
<?php //include_once('tour_info_sec.php') ?>
<div class="container-fluid mg_tp_10">
<div class="">
    <div class="app_panel">
        <div class="app_panel_content no-pad">

                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                    <legend>Train Details</legend>
                        <?php include_once('train_info.php'); ?>
                </div>
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                <legend>Hotel Details</legend>
                    <?php include_once('hotel_info.php'); ?>
                </div>
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                    <legend>Flight Details</legend>
                        <?php include_once('plane_info.php'); ?>
                </div>
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                    <legend>Cruise Details</legend>
                        <?php include_once('cruise_info.php'); ?>
                </div>
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                    <div class="row">
                        <div class="col-md-5 col-sm-6 col-xs-12 text-right text_center_xs"><label>Total Travel
                                Expense</label></div>
                        <div class="col-md-2 col-sm-4 col-xs-12"><input type="text" id="txt_travel_total_expense"
                                name="txt_travel_total_expense" value="0" class="text-right amount_feild_highlight"
                                title="Total Travel Expense" readonly /></div>
                    </div>
                </div>

                    <div class="panel panel-default main_block bg_light pad_8 text-center mg_bt_0" style="background-color: #fff; border: none;">
                        <button type="button" onclick="switch_to_tab_1()" class="btn btn-sm btn-info ico_left"><i
                                class="fa fa-arrow-left"></i>&nbsp;&nbsp;Previous</button>&nbsp;&nbsp;
                        <button class="btn btn-sm btn-info ico_right">Next&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
                    </div>
            </div>
        </div>
    </div>
</div>
</form>

<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script src="../js/tab_2.js"></script>
<script src="../js/tab_2_calculations.js"></script>
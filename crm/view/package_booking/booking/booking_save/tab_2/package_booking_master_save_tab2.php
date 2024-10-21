<form id="frm_tab_2">
    <div class="app_panel">

        <div class="app_panel_content no-pad">

            <div class="container-fluid">
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20 <?= $hide_train ?>">
                    <legend>Train Details</legend>
                    <!-- <div class="bg_white main_block panel-body panel panel-default"> -->
                        <?php include_once('train_info.php') ?>
                    <!-- </div> -->
                </div>
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20 <?= $hide_flight ?>">
                    <legend>Flight Details</legend>
                    <!-- <div class="bg_white main_block panel-body panel panel-default"> -->
                        <?php include_once('plane_info.php') ?>
                    <!-- </div> -->
                </div>
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20 <?= $hide_cruise ?>">
                    <legend>Cruise Details</legend>
                    <!-- <div class="bg_white main_block panel-body panel panel-default"> -->
                        <?php include_once('cruise_info.php') ?>
                    <!-- </div> -->
                </div>

                <div class="panel panel-default panel-body bg_light mg_bt_20 <?= $all_tab ?>">
                    <div class="row text-center">
                        <div class="col-xs-12 text-center">
                            <label>Total Travel Expense</label>
                        </div>
                        <div class="col-md-2 col-md-offset-5 col-sm-4 col-sm-offset-4 col-xs-12">
                            <input type="text" id="txt_travel_total_expense" class="amount_feild_highlight text-right"
                                name="txt_travel_total_expense" title="Total Travel Expense" value="0" readonly />
                        </div>
                    </div>
                </div>

                <div class="panel panel-default main_block bg_light pad_8 text-center mg_bt_150" style="background-color: #fff; border: none;">
                    <button class="btn btn-sm btn-info ico_left" type="button" onclick="back_to_tab_1()"><i
                            class="fa fa-arrow-left"></i>&nbsp;&nbsp;Previous</button>&nbsp;&nbsp;&nbsp;
                    <button class="btn btn-sm btn-info ico_right">Next&nbsp;&nbsp;<i
                            class="fa fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
$('#txt_arravl1,#txt_depart1').datetimepicker({
    format: 'd-m-Y H:i'
});
</script>
<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script src="../js/tab_2_calculations.js"></script>
<script src="../js/tab_2.js"></script>
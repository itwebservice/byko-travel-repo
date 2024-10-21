<form id="frm_tab_3">

    <div class="app_panel">
        <div class="app_panel_content no-pad">

            <div class="">
                <div class="container-fluid">
                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                        <legend>Accommodation details</legend>
                        <?php include("hotel_table_row.php"); ?>
                    </div>
                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                        <legend>Transport Details</legend>
                        <?php include("transport_table_row.php"); ?>
                    </div>
                    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                        <legend>Activity Details</legend>
                        <?php include("excursion_table_row.php"); ?>
                    </div>
                    
                    <div class="panel panel-default main_block bg_light pad_8 text-center mg_bt_150" style="background-color: #fff; border: none;">
                        <button class="btn btn-sm btn-info ico_left" type="button" onclick="back_to_tab_2()"><i
                                class="fa fa-arrow-left"></i>&nbsp;&nbsp;Previous</button>&nbsp;&nbsp;&nbsp;
                        <button class="btn btn-sm btn-info ico_right">Next&nbsp;&nbsp;<i
                                class="fa fa-arrow-right"></i></button>
                    </div>
                </div>

                    <!-- <div id="div_modal_content"></div> -->
            </div>
        </div>
    </div>
</form>
<script src="../js/tab_3.js"></script>
<script type="text/javascript">
function disabled_transport_details(id) {
    var id = $('#transport_agency_id').val();
    if (id != 'N/A') {
        $("#vehicle_name1").prop({
            disabled: '',
            value: ''
        });
        $('#txt_tsp_from_date').prop({
            disabled: '',
            value: ''
        });
        $('#txt_tsp_end_date').prop({
            disabled: '',
            value: ''
        });
        $("#txt_tsp_to_date").prop({
            disabled: '',
            value: ''
        });
        $('#txt_tsp_total_amount').prop({
            disabled: '',
            value: ''
        });
    } else {
        $("#vehicle_name1").prop({
            disabled: 'disabled',
            value: ''
        });
        $('#txt_tsp_from_date').prop({
            disabled: 'disabled',
            value: ''
        });
        $('#txt_tsp_end_date').prop({
            disabled: 'disabled',
            value: ''
        });
        $("#txt_tsp_to_date").prop({
            disabled: 'disabled',
            value: ''
        });
        $('#txt_tsp_total_amount').prop({
            disabled: 'disabled',
            value: ''
        });
    }
}
</script>
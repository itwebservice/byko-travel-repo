<div class="app_panel" style="padding:0px 20px;">

    <!--=======Header panel end======-->

    <div class="">
        <div class="container-fluid">
            <div class="app_panel_content no-pad">
                <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                    <legend>Tour Details</legend>

                    <div class="row">

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                            <select class="form-control" style="width:100%" id="cmb_tour_name" name="cmb_tour_name"
                                title="Tour Name"
                                onchange="payment_details_reflected_data('tbl_member_dynamic_row'); seats_availability_reflect();tour_group_reflect(this.id);"
                                disabled>
                                <option value="<?php echo $tour_id ?>"><?php echo $tour_name ?></option>
                                <?php
                                $sq = mysqlQuery("select tour_id,tour_name from tour_master where active_flag = 'Active' order by tour_name asc");
                                while ($row = mysqli_fetch_assoc($sq)) {
                                    echo "<option value='$row[tour_id]'>" . $row['tour_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                            <select class="form-control" id="cmb_tour_group" name="cmb_tour_group" title="Tour Date"
                                onchange="seats_availability_reflect(); seats_availability_check(); due_date_reflect()">
                                <option value="<?php echo $tour_group_id ?>"> <?php echo $tour_group_name ?> </option>
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                            <select id="cmb_traveler_group_id" name="cmb_traveler_group_id" title="Booking ID"
                                onchange="select_color(this.id);">
                                <option value="<?php echo $tourwise_id ?>">
                                    <?php echo get_group_booking_id($tourwise_id, $year) ?> </option>
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12 hidden">
                            <select name="taxation_type" id="taxation_type" title="Taxation Type">
                                <option value="<?= $tourwise_details['taxation_type'] ?>">
                                    <?= $tourwise_details['taxation_type'] ?></option>

                            </select>
                        </div>

                        <div id="div_seats_availability" class="reflect-seats" style=""></div>

                    </div>


                    <input type="hidden" id="txt_available_seats" name="txt_available_seats">
                    <input type="hidden" id="txt_total_seats1" name="txt_total_seats">
                    <input type="hidden" id="seats_booked" name="seats_booked">
                </div>
            </div>
        </div>
        <?= end_panel() ?>

        <script src="../js/tab_1_tour_info_sec.js"></script>
        <script>
        $(document).ready(function() {
            //$("#cmb_tour_name").select2();
        });
        tour_type_reflect('cmb_tour_name', 1);
        seats_availability_reflect();
        seats_availability_check();
        </script>
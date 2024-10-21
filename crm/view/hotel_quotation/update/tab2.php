<form id="frm_tab2">
    <div class="app_panel">
        <div class="container">
        <div class="row">
            <?php
            $count = 1;
            foreach ($hotelDetails as $values) {

                $option = $values['option'];
                $data = $values['data'];
                ?>
                <div class="accordion_content package_content mg_bt_10">
                    <div class="panel panel-default main_block">
                        <div class="panel-heading main_block" role="tab" id="heading_<?= $option ?>">
                            <div class="Normal collapsed main_block" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?= $option; ?>" aria-expanded="false" aria-controls="collapse_<?= $option; ?>" id="collapsed_<?= $option ?>">
                                <div class="col-md-12"><span><em style="margin-left: 15px;">Option - <?php echo $option ?></em></span></div>
                            </div>
                        </div>
                        <div id="collapse_<?= $option ?>" class="panel-collapse collapse main_block" role="tabpanel"
                            aria-labelledby="heading_<?= $option ?>">
                            <div class="panel-body">
                                <div class="col-md-12 no-pad" id="div_list1">
                                    <div class="row mg_bt_10">
                                        <div class="col-xs-6 text_center_xs">
                                            <button type="button" class="btn btn-excel btn-sm" onclick="hotel_save_modal()"><i class="fa fa-plus" title="Add Hotel"></i></button>
                                        </div>
                                        <div class="col-xs-6 text-right text_center_xs">
                                            <button type="button" class="btn btn-excel btn-sm" onClick="addRow('dynamic_table_list_h_<?= $option ?>','<?= $option ?>');city_lzloading('.city_master_dropdown')"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table style="width: 100%" id="dynamic_table_list_h_<?= $option ?>" name="dynamic_table_list_h_<?= $option ?>" class="table table-bordered table-hover table-striped no-marg pd_bt_51 mg_bt_150">
                                            <?php
                                            for($i = 0;$i<sizeof($data);$i++){

                                                $hotel_id = $data[$i]['hotel_id'];
                                                $city_id = $data[$i]['city_id'];
                                                $hotelName = mysqli_fetch_assoc(mysqlQuery("select hotel_name,state_id from hotel_master where hotel_id='$hotel_id'"));
                                                $cityName = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$city_id'"));
                                                ?>
                                                <tr>
                                                    <td style="width: 50px;"><input class="css-checkbox mg_bt_10"
                                                            id="chk_program-u_<?= $count.$i ?>" type="checkbox" checked><label class="css-label"
                                                            for="chk_program-u_<?= $count.$i ?>"> <label></td>
                                                    <td style="width: 50px;"><input maxlength="15" value="<?= $i+1 ?>" type="text"
                                                            name="username" placeholder="Sr. No." class="form-control mg_bt_10" disabled />
                                                    </td>
                                                    <td><select name="tour_type-u_<?= $count.$i ?>" id="tour_type-u_<?= $count.$i ?>" style="width:145px;" title="Tour Type" class="form-control">
                                                            <option value="<?= $data[$i]['tour_type'] ?>" selected><?= $data[$i]['tour_type'] ?>
                                                            <?php if($data[$i]['tour_type'] != 'Domestic'){ ?>
                                                            <option value="Domestic">Domestic</option>
                                                            <?php }
                                                            if($data[$i]['tour_type'] != 'International'){ ?>
                                                            <option value="International">International</option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td><select id="city_name-u_<?= $count.$i ?>" name="city_name-u_<?= $count.$i ?>"
                                                            class="city_master_dropdown" style="width:160px"
                                                            onchange="hotel_name_list_load(this.id);" title="Select City Name">
                                                            <option value="<?= $data[$i]['city_id'] ?>" selected><?= $cityName['city_name'] ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td><select id="hotel_name-u_<?= $count.$i ?>" name="hotel_name-u_<?= $count.$i ?>"
                                                            onchange="hotel_type_load1(this.id);hotel_type_load_cate1(id)" style="width:160px" title="Select Hotel Name">
                                                            <option value="<?= $data[$i]['hotel_id'] ?>" selected><?= $hotelName['hotel_name'] ?>
                                                            </option>
                                                            <option value="">Hotel Name</option>
                                                        </select>
                                                    </td>
                                                    <?php if($room_category_switch == 'No' ){?>
                                                    <td><select name="room_cat-u_<?= $count.$i ?>" id="room_cat-u_<?= $count.$i ?>"
                                                            style="width:145px;" title="Room Category" class="form-control app_select2"
                                                            onchange=""><?php get_room_category_dropdown(); ?>
                                                            <option value="<?= $data[$i]['hotel_cat'] ?>" selected><?= $data[$i]['hotel_cat'] ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <?php } else{?>
                                                    <td><select name="room_cat-1" id="room_cat-u_<?= $count.$i ?>"
                                                            style="width:145px;" title="Room Category" class="form-control app_select2"
                                                            onchange="">
                                                            <option value="">Room Category</option>
                                                            <option value="<?= $data[$i]['hotel_cat'] ?>" selected><?= $data[$i]['hotel_cat'] ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <?php }?>
                                                    <td><select name="meal_plan-u_<?= $count.$i ?>" id="meal_plan-u_<?= $count.$i ?>"
                                                            style="width:145px;" title="Meal Plan" class="form-control app_select2">
                                                            <?php get_mealplan_dropdown(); ?>
                                                            <?php if ($data[$i]['meal_plan'] != '') { ?>
                                                            <option value="<?= $data[$i]['meal_plan'] ?>" selected><?= $data[$i]['meal_plan'] ?>
                                                            </option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" style="width:150px;" class="app_datepicker"
                                                            id="check_in-u_<?= $count.$i ?>" name="check_in-u_<?= $count.$i ?>"
                                                            placeholder="Check-In Date" title="Check-In Date"
                                                            onchange="get_auto_to_date(this.id);" value="<?= $data[$i]['checkin'] ?>">
                                                    </td>
                                                    <td><input type="text" style="width:150px;" class="app_datepicker"
                                                            id="check_out-u_<?= $count.$i ?>" name="check_out-u_<?= $count.$i?>"
                                                            placeholder="Check-Out Date" title="Check-Out Date"
                                                            onchange="calculate_total_nights(this.id);validate_validDates(this.id);"
                                                            value="<?= $data[$i]['checkout'] ?>">
                                                    </td>
                                                    <td><input type="text" id="hotel_type-u_<?= $count.$i ?>" name="hotel_type-1"
                                                            placeholder="Hotel Category" title="Hotel Category" style="width:150px"
                                                            value="<?= $data[$i]['hotel_type'] ?>" readonly>
                                                    </td>
                                                    <td><input type="text" id="hotel_stay_days-u_<?= $count.$i ?>" title="Total Nights"
                                                            name="hotel_stay_days-u_<?= $count.$i ?>" placeholder="Total Nights"
                                                            value="<?= $data[$i]['hotel_stay_days'] ?>" onchange="validate_balance(this.id);"
                                                            style="width:150px;" readonly></td>
                                                    <td><input type="text" id="no_of_rooms-u_<?= $count.$i ?>" title="Total Rooms"
                                                            name="no_of_rooms-u_<?= $count.$i ?>" placeholder="*Total Rooms"
                                                            value="<?= $data[$i]['total_rooms'] ?>" onchange="validate_balance(this.id);"
                                                            style="width:110px"></td>
                                                    <td><input type="text" id="extra_bed-u_<?= $count.$i ?>" name="extra_bed-u_<?= $count.$i ?>"
                                                            title="Extra Bed" placeholder="Extra Bed" onchange="validate_balance(this.id);"
                                                            style="width:100px" value="<?= $data[$i]['extra_bed'] ?>"></td>
                                                    <td class="hidden"><input type="hidden" id="hotel_cost-u_<?= $count.$i ?>"
                                                            name="hotel_cost-u_<?= $count.$i ?>" placeholder="Hotel Cost" title="Hotel Cost"
                                                            onchange="validate_balance(this.id)" style="width:100px;"
                                                            value="<?= $data[$i]['hotel_cost'] ?>"></td>
                                                </tr>
                                                <?php
                                                $count++;
                                            } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        </div>
        <div class="row text-center mg_tp_30 mg_bt_30" style="margin-right:0px">
            <div class="col-xs-12">
                <button class="btn btn-info btn-sm ico_left" type="button" onclick="switch_to_tab1()"><i
                        class="fa fa-arrow-left"></i>&nbsp;&nbsp Previous</button>
                &nbsp;&nbsp;
                <button class="btn btn-info btn-sm ico_right">Next&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
</form>


<script>
$('.app_datepicker').datetimepicker({
    format: 'd-m-Y',
    timepicker: false
});
city_lzloading('.city_master_dropdown');
$('#dest_name').select2();

function switch_to_tab1() {
    $('#tab2_head').addClass('done');
    $('#tab1_head').addClass('active');
    $('.bk_tab').removeClass('active');
    $('#tab1').addClass('active');
    $('html, body').animate({
        scrollTop: $('.bk_tab_head').offset().top
    }, 200);
}

$('#frm_tab2').validate({

submitHandler: function(form, e) {
    e.preventDefault();
    var hotelcostArr = new Array();
    var hcount = 0;

    var nofquotation = $('#nofquotation').val();
    for (var quot = 1; quot <= Number(nofquotation); quot++) {

        var table = document.getElementById("dynamic_table_list_h_" + quot);
        var rowCount = table.rows.length;

        var hcostTotal = 0;
        for (var i = 0; i < rowCount; i++) {

            var row = table.rows[i];
            if (row.cells[0].childNodes[0].checked) {

                hcount++;
                var city_name = row.cells[3].childNodes[0].value;
                var hotel_id = row.cells[4].childNodes[0].value;
                var hotel_cat = row.cells[5].childNodes[0].value;
                var check_in = row.cells[7].childNodes[0].value;
                var checkout = row.cells[8].childNodes[0].value;
                var hotel_stay_days1 = row.cells[10].childNodes[0].value;
                var total_rooms = row.cells[11].childNodes[0].value;
                var hotel_cost = row.cells[13].childNodes[0].value;
                hcostTotal += Number(hotel_cost);

                if (city_name == "") {
                    error_msg_alert('Select Hotel city in Row ' + (i + 1));
                    $('.accordion_content').removeClass("indicator");
                    $('#tbl_package_tour_quotation_dynamic_hotel').parent('div').closest('.accordion_content').addClass("indicator");
                    return false;
                }

                if (hotel_id == "") {
                    error_msg_alert('Enter Hotel in Row ' + (i + 1));
                    $('.accordion_content').removeClass("indicator");
                    $('#tbl_package_tour_quotation_dynamic_hotel').parent('div').closest(
                        '.accordion_content').addClass("indicator");
                    return false;
                }
                if (hotel_cat == "") {
                    error_msg_alert('Enter Room Category in Row ' + (i + 1));
                    $('.accordion_content').removeClass("indicator");
                    $('#tbl_package_tour_quotation_dynamic_hotel').parent('div').closest(
                        '.accordion_content').addClass("indicator");
                    return false;
                }
                if (check_in == "") {
                    error_msg_alert('Select Check-In date in Row ' + (i + 1));
                    $('.accordion_content').removeClass("indicator");
                    $('#tbl_package_tour_quotation_dynamic_hotel').parent('div').closest(
                        '.accordion_content').addClass("indicator");
                    return false;
                }
                if (total_rooms == "") {
                    error_msg_alert('Enter total rooms in Row ' + (i + 1));
                    $('.accordion_content').removeClass("indicator");
                    $('#tbl_package_tour_quotation_dynamic_hotel').parent('div').closest(
                        '.accordion_content').addClass("indicator");
                    return false;
                }
                if (checkout == "") {
                    error_msg_alert('Select Check-Out date in Row ' + (i + 1));
                    $('.accordion_content').removeClass("indicator");
                    $('#tbl_package_tour_quotation_dynamic_hotel').parent('div').closest(
                        '.accordion_content').addClass("indicator");
                    return false;
                }
                if (hotel_stay_days1 == "") {
                    error_msg_alert('Enter Hotel total nights in Row ' + (i + 1));
                    $('.accordion_content').removeClass("indicator");
                    $('#tbl_package_tour_quotation_dynamic_hotel').parent('div').closest(
                        '.accordion_content').addClass("indicator");
                    return false;
                }
            }
        }
        if (parseInt(hcount) === 0) {
            error_msg_alert("Atleast one hotel is required to proceed!");
            return false;
        }
        hotelcostArr.push(hcostTotal);
    }
    $('#tab2_head').addClass('done');
    $('#tab3_head').addClass('active');
    $('.bk_tab').removeClass('active');
    $('#tab3').addClass('active');
    $('html, body').animate({
        scrollTop: $('.bk_tab_head').offset().top
    }, 200);
}
});
</script>
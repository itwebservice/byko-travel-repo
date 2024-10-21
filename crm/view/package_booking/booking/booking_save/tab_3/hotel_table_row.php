
<div class="row" style="margin-top: 5px">
    <div class="col-xs-6 mg_bt_20_sm_xs">
        <button type="button" class="btn btn-excel btn-sm" title="Add Hotel" onclick="hotel_save_modal()"><i class="fa fa-plus"></i></button>
    </div>
    <div class="col-xs-6 text-right mg_bt_20_sm_xs">
        <button type="button" class="btn btn-excel btn-sm" onClick="addRow('tbl_package_hotel_infomration');"><i class="fa fa-plus"></i></button>
        <button type="button" class="btn btn-pdf btn-sm" onClick="deleteRow('tbl_package_hotel_infomration')"><i class="fa fa-trash"></i></button>
        <!-- <button type="button" class="btn btn-info btn-sm ico_left mg_bt_10_sm_xs" onclick="hotel_save_modal()"><i class="fa fa-plus"></i>&nbsp;&nbsp;New Hotel</button> -->
</div> </div>
<div class="row main_block">
    <div class="col-xs-12"> 
        <div class="table-responsive">
            <table id="tbl_package_hotel_infomration" class="table table-bordered table-hover table-striped" style="width: 1475px;">
                <tr>
                    <td><input id="check-btn-hotel-acm-1" type="checkbox" ></td>
                    <td><input maxlength="15" type="text" name="username"  value="1" placeholder="Sr. No." disabled/></td>
                    <td><select id="city_name1" name="city_name1" style="width:150px" title="Select City Name" class="form-control city_name" onchange="load_hotel_list(this.id); ">
                        </select></td>
                    <?php if($room_category_switch == 'No' ){?>
                    <td><select id="hotel_name1" name="hotel_name1" class="form-control" style="width:170px" title="Select Hotel Name" onchange="get_auto_values('txt_booking_date','total_basic_amt','payment_mode','service_charge','markup','save','true','service_charge','discount');">
                            <option value="">*Hotel Name</option>
                        </select></td>
                    <?php }else {?>
                    <td><select id="hotel_name1" name="hotel_name1" class="form-control" style="width:170px" title="Select Hotel Name" onchange="hotel_type_load_cate(this.id);get_auto_values('txt_booking_date','total_basic_amt','payment_mode','service_charge','markup','save','true','service_charge','discount');">
                            <option value="">*Hotel Name</option>
                        </select></td>
                    <?php }?>
                    <td><input class="form-control app_datetimepicker" type="text" id="txt_hotel_from_date1" style="width: 170px" name="txt_hotel_from_date1" onchange="get_to_datetime(this.id,'txt_hotel_to_date1');" placeholder="Check-In DateTime" title="Check-In DateTime" style="width: 170px;"></td>
                    <td><input class="form-control app_datetimepicker" type="text" id="txt_hotel_to_date1" style="width: 170px" name="txt_hotel_to_date1" onchange="validate_validDatetime('txt_hotel_from_date1' ,'txt_hotel_to_date1')" placeholder="Check-Out DateTime" title="Check-Out DateTime" style="width: 170px;"></td>
                    <td><input type="text" id="txt_room1" name="txt_room1" placeholder="*Room(s)" title="Room(s)"  style="width: 110px;"></td>
                    <?php if($room_category_switch == 'No' ){?>
                    <td><select name="txt_catagory1" id="txt_catagory1" title="Category" style="width: 140px;" class="app_select2">
                    <?php get_room_category_dropdown(); ?>
                    </select></td>
                    <?php } else{?>
                    <td><select name="txt_catagory1" id="txt_catagory" title="Category" style="width: 140px;" class="app_select2">
                            <option value="">*Room Category</option>
                        </select></td>
                    <?php }?>
                    <td><select title="Meal Plan" id="cmb_meal_plan1" name="cmb_meal_plan" title="Meal Plan" style="width:130px;">
                    <?php get_mealplan_dropdown(); ?>
                    </select></td>
                    <td><input type="text" id="txt_extra_bed1" name="txt_extra_bed1" placeholder="Extra Bed(s)" title="Extra Bed(s)"  style="width: 110px;" value="0"></td>
                    <td><input type="text" id="txt_hotel_acm_confirmation_no1" onchange="validate_specialChar(this.id)" name="txt_hotel_acm_confirmation_no" placeholder="Confirmation no" title="Confirmation no" style="width: 110px;"></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<script>
    city_lzloading('.city_name');
</script>
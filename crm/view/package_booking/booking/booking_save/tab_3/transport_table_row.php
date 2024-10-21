<div class="row" style="margin-top: 5px">
    <div class="col-xs-6 mg_bt_20_sm_xs">
        <button type="button" class="btn btn-excel btn-sm" title="Add Vehicle" onclick="vehicle_save_modal()"><i class="fa fa-plus"></i></button>
        <button type="button" class="btn btn-excel btn-sm" title="Add Airport" onclick="airport_airline_save_modal()"><i class="fa fa-plus"></i></button>
    </div>
    <div class="col-xs-6 text-right mg_bt_20_sm_xs">
        <button type="button" class="btn btn-excel btn-sm" onClick="addRow('tbl_package_transport_infomration');destinationLoading('select[name^=pickup_from]', 'Pickup Location');
            destinationLoading('select[name^=drop_to]', 'Drop-off Location');"><i class="fa fa-plus"></i></button>
        <button type="button" class="btn btn-pdf btn-sm" onClick="deleteRow('tbl_package_transport_infomration')"><i class="fa fa-trash"></i></button>
</div> </div>
<div class="row main_block">
    <div class="col-xs-12"> 
        <div class="table-responsive">
            <table id="tbl_package_transport_infomration" class="table table-bordered table-hover table-striped" style="width: 100%;">
                <tr>
                    <td><input id="check-btn-tr-acm-1" type="checkbox" ></td>
                    <td><input maxlength="15" type="text" name="username"  value="1" placeholder="Sr. No." disabled/></td>
                    <td><select name="vehicle_name1" id="vehicle_name1" title="Vehicle Name" style="width:250px">
                        <option value="">*Select Vehicle</option>
                            <?php
                            $sq_transport_buses = mysqlQuery("select * from b2b_transfer_master where status!='Inactive' order by vehicle_name asc");
                            while($row_transport_bus = mysqli_fetch_assoc($sq_transport_buses)){
                            ?>
                            <option value="<?= $row_transport_bus['entry_id'] ?>"><?= $row_transport_bus['vehicle_name'] ?></option>
                            <?php } ?>
                        </select></td>
                    <td><input type="text" id="txt_tsp_from_date" name="txt_tsp_from_date" placeholder="*Start Date/Time" title="Start Date/Time" style="width:170px;" onchange="get_to_datetime(this.id,'txt_tsp_end_date')"></td>
                    <td><input type="text" id="txt_tsp_end_date" name="txt_tsp_end_date" placeholder="*End Date/Time" title="End Date/Time" style="width:170px;" onchange="validate_validDatetime('txt_tsp_from_date','txt_tsp_end_date')"></td>
                    <td><select name="pickup_from" id="pickup_from" data-toggle="tooltip" style="width:250px;" title="Pickup Location" class="form-control app_select2">
                    </select></td>
                    <td><select name="drop_to" id="drop_to" style="width:250px;" data-toggle="tooltip" title="Drop-off Location" class="form-control app_select2">
                        </select></td>
                    <td><select name="duration" id="duration" style="width:170px;" title="*Service Duration" data-toggle="tooltip" class="form-control app_select2">
                        <option value="">*Service Duration</option>
                        <?php echo get_service_duration_dropdown(); ?>
                        </select></td>
                    <td><input type="text" id="no_vehicles" name="no_vehicles" placeholder="*No.Of vehicles" title="No.Of vehicles" style="width:150px"></td>
                </tr>
        </table>
        </div>
    </div>
</div>
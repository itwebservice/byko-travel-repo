<?php
include '../../../../../model/model.php';
$hotel_entry_id = $_POST['hotel_entry_id'];
$hotel_name = $_POST['hotel_name'];
$sq_hotel = mysqlQuery("select * from package_tour_quotation_hotel_entries where id='$hotel_entry_id'");
?>
<div class="row">
    <div class="col-md-12 app_accordion">
        <div class="panel-group main_block" id="accordion<?= $hotel_entry_id ?>" role="tablist" aria-multiselectable="true">

            <div class="accordion_content main_block mg_bt_10">

                <div class="panel panel-default main_block">
                    <div class="panel-heading main_block" role="tab" id="heading_<?=$hotel_entry_id?>">
                        <div class="Normal main_block" role="button" data-toggle="collapse"
                            data-parent="#accordion<?= $hotel_entry_id ?>" href="#collapse<?=$hotel_entry_id?>" aria-expanded="true"
                            aria-controls="collapse<?=$hotel_entry_id?>" id="collapsed<?=$hotel_entry_id?>">
                            <div class="col-md-12"><span>Select Similar Hotel Options for <?= $hotel_name ?></span></div>
                        </div>
                    </div>
                    <div id="collapse<?=$hotel_entry_id?>" class="panel-collapse collapse main_block" role="tabpanel"
                        aria-labelledby="heading_<?=$hotel_entry_id?>">
                        <div class="panel-body">
                            <div class="row"><div class="col-xs-12 text-right mg_bt_20_sm_xs">
                                <button type="button" class="btn btn-excel btn-sm" onclick="addRow('tbl_similar_package_hotels<?=$hotel_entry_id?>',<?= $hotel_entry_id ?>,'1');city_lzloading('select[name=city_id1]')" title="Add row"><i class="fa fa-plus"></i></button>
                                <button type="button" class="btn btn-pdf btn-sm" onclick="deleteRow('tbl_similar_package_hotels<?=$hotel_entry_id?>',<?= $hotel_entry_id ?>)" title="Delete row"><i class="fa fa-trash"></i></button>    
                            </div></div>
                            <div class="row">
                                <div class="table-responsive">
                                    <table id="tbl_similar_package_hotels<?=$hotel_entry_id?>" class="table table-bordered pd_bt_51">
                                        <?php
                                        while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
                                            
                                            $optional_hotels = isset($row_hotel['optional_hotels']) ? ($row_hotel['optional_hotels']) : [];
                                            ?>
                                            <tr>
                                                <td style="width:5%"><input class="css-checkbox" id="ochk_plan<?=$hotel_entry_id?>" type="checkbox"><label class="css-label" for="ochk_plan<?=$hotel_entry_id?>"> <label></td>
                                                <td style="width:10%"><input maxlength="15" class="form-control text-center" type="text" name="username"  value="1" placeholder="Sr. No." disabled/></td>
                                                <td><select class="form-control" id="oavail1<?= $hotel_entry_id ?>" style="width:150px !important;" title="Status" data-toggle="tooltip">
                                                    <option value="">Status</option>
                                                    <option value="Available">Available</option>
                                                    <option value="Not Available">Not Available</option>
                                                    <option value="NA">NA</option>
                                                </select></td>
                                                <td><select id="city_id1<?=$hotel_entry_id?>" name="city_id1" title="Select City" onchange="hotel_name_list_load(this.id)" class="form-control app_minselect2" style="width:200px!important;">
                                                    </select>
                                                </td>    
                                                <td><select id="hotel_id1<?=$hotel_entry_id?>" name="hotel_id1" title="Select Hotel" class="form-control app_select2" style="width:200px!important;" onchange="hotel_data_load(this.id)">
                                                        <option value="">*Select Hotel</option>
                                                    </select>
                                                </td>
                                                <td><input class="form-control" type='text' id="omobile_no1<?= $hotel_entry_id ?>" style="width:140px !important;" placeholder="Mobile No" title="Mobile No" readonly/></td>
                                                <td><input class="form-control" type='text' id="oemail_id1<?= $hotel_entry_id ?>" style="width:210px !important;" placeholder="*Email ID" title="Email ID" onchange="validate_email(this.id)" required/></td>
                                                <td><input class="form-control" type='text' id="oreply_by1<?= $hotel_entry_id ?>" style="width:140px !important;" placeholder="Replied By" title="Replied By" readonly/></td>
                                                <td><textarea rows="1" class="form-control" type='text' id="ospec1<?= $hotel_entry_id ?>" style="width:200px !important;" placeholder="Specification" title="Specification" readonly ></textarea></td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>
                            <div class="row text-center mg_tp_10">
                                <div class="col-md-12">
                                    <button type="button" onclick="submit_fun(this.id)" class="btn btn-sm btn-success" id="obtn_send<?=$hotel_entry_id?>" ><i class="fa fa-paper-plane-o"></i>&nbsp;&nbsp;Send To Optional Hotels</button><br/><span style="color: red;" class="note" data-original-title="" title="">Use this button to send request only for optional hotels.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
city_lzloading('select[name="city_id1"]');
$('#hotel_id1<?=$hotel_entry_id?>').select2();
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
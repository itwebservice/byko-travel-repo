<?php
include "../../model/model.php";
$nofquotation = $_REQUEST['nofquotation'];
$hotelcostArr = $_REQUEST['hotelcostArr'];

for($quot=1; $quot <= $nofquotation; $quot++){
    ?>
    <div class="row mg_tp_10">
        <div class="col-xs-12">
            <h3 class="editor_title">Option-<?=$quot?> Costing</h3>
            <div class="panel panel-default panel-body app_panel_style">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <table id="dynamic_quotation_costing_h_<?=$quot?>" class="table table-bordered table-hover table-striped no-marg pd_bt_51 mg_bt_0">
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small style="color:red" id="basic_show-<?=$quot?>">&nbsp;</small>
                                    <input type="number" id="basic_cost-<?=$quot?>" name="basic_cost-<?=$quot?>" placeholder="Basic Amount" title="Basic Amount" value="<?= $hotelcostArr[$quot-1] ?>"  onchange="validate_balance(this.id);get_auto_values('quotation_date','basic_cost-<?=$quot?>','payment_mode','service_charge-<?=$quot?>','markup_cost-<?=$quot?>','save','true','service_charge')" style="width:120px!important;"> </td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small style="color:red" id="service_show-<?=$quot?>">&nbsp;</small><input type="number" id="service_charge-<?=$quot?>" name="service_charge-<?=$quot?>" placeholder="Service Charge" title="Service Charge"   onchange="validate_balance(this.id);get_auto_values('quotation_date','basic_cost-<?=$quot?>','payment_mode','service_charge-<?=$quot?>','markup_cost-<?=$quot?>','save','true','service_charge')" style="width:120px!important;"></td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small style="color:red" id="tax_apply_on<?=$quot?>">&nbsp;</small>
                                    <select title="Tax Apply On" id="tax_apply_on-<?=$quot?>" name="tax_apply_on" class="form-control" onchange="get_auto_values('quotation_date','basic_cost-<?=$quot?>','payment_mode','service_charge-<?=$quot?>','markup_cost-<?=$quot?>','save','true','service_charge');" style="width:180px!important;">
                                    <option value="">*Tax Apply On</option>
                                    <option value="1">Basic Amount</option>
                                    <option value="2">Service Charge</option>
                                    <option value="3">Total</option>
                                </select></td>
                                <td class="header_btn header_btn" style="padding:4px"><small style="color:red" id="select_tax<?=$quot?>">&nbsp;</small><select title="Select Tax" id="tax_value-<?=$quot?>" name="tax_value" class="form-control" onchange="get_auto_values('quotation_date','basic_cost-<?=$quot?>','payment_mode','service_charge-<?=$quot?>','markup_cost-<?=$quot?>','save','true','service_charge');" style="width:180px!important;">
                                    <option value="">*Select Tax</option>
                                    <?php get_tax_dropdown('Income') ?>
                                    </select>
                                </td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small>&nbsp;</small><input type="text" id="tax_amount-<?=$quot?>" name="tax_amount-<?=$quot?>" placeholder="Tax Amount" title="Tax Amount"   onchange="validate_balance(this.id)" style="width:180px!important;" readonly> </td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small style="color:red" id="markup_show-<?=$quot?>">&nbsp;</small><input type="number" id="markup_cost-<?=$quot?>" name="markup_cost-<?=$quot?>" placeholder="Markup Amount" title="Markup Amount"   onchange="validate_balance(this.id);get_auto_values('quotation_date','basic_cost-<?=$quot?>','payment_mode','service_charge-<?=$quot?>','markup_cost-<?=$quot?>','save','false','service_charge')" style="width:162px!important;" > </td>
                                <td class="header_btn header_btn" style="padding:4px"><small style="color:red" id="select_markup_tax<?=$quot?>">&nbsp;</small><select title="Select Markup Tax" id="markup_tax_value-<?=$quot?>" name="markup_tax_value" class="form-control" onchange="get_auto_values('quotation_date','basic_cost-<?=$quot?>','payment_mode','service_charge-<?=$quot?>','markup_cost-<?=$quot?>','save','false','service_charge');" style="width:180px!important;">
                                    <option value="">*Select Markup Tax</option>
                                    <?php get_tax_dropdown('Income') ?>
                                    </select>
                                </td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small>&nbsp;</small><input type="text" id="tax_markup-<?=$quot?>" name="tax_markup-<?=$quot?>" placeholder="Markup Tax" title="Markup Tax"   onchange="validate_balance(this.id)" readonly style="width:180px!important;"> </td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small>&nbsp;</small><input type="number" id="roundoff-<?=$quot?>" class="form-control" name="total_amount-<?=$quot?>" placeholder="Round Off" title="Round Off" style="width:120px!important;" readonly> </td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small id="tcs_tax_show-<?=$quot?>" style="color:#000000">&nbsp;</small>
                                    <select title="TCS" id="tcs_tax-<?=$quot?>" data-optionid="<?=$quot?>" name="tcs_tax-<?=$quot?>" class="form-control tcs_tax_calculation" style="width: 150px!important;">
                                        <option value="0">*TCS Tax</option>
    									<option value="5">5% TCS</option>
    									<option value="20">20% TCS</option>
                                    </select><span>TCS</span>
                                </td>
                                
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small id="tcs_tax_show-<?=$quot?>" style="color:#000000">&nbsp;</small>
                                     <input type="number" name="tcs-<?=$quot?>" id="tcs-<?=$quot?>" readonly class="text-right"
                                        placeholder="TCS" title="TCS" value="0.00" style="width: 100px!important;">
                                        <span>TCS</span>
                                </td>
                                
                                <td class="header_btn header_btn" style="padding:4px; display:none;">
                                    <small id="tcs_tax_show-<?=$quot?>" style="color:#000000">&nbsp;</small>
                                     <input type="number" name="tds-<?=$quot?>" id="tds-<?=$quot?>" readonly class="text-right"
                                        placeholder="TDS" title="TDS" value="0.00" style="width: 100px!important;">
                                        <span>TDS</span>
                                </td>
                                <td class="header_btn header_btn" style="padding:4px">
                                    <small>&nbsp;</small><input type="number" id="total_amount-<?=$quot?>" class="amount_feild_highlight text-right form-control" name="total_amount-<?=$quot?>" placeholder="Total Amount" title="Total Amount" style="width:120px!important;" onchange="validate_balance(this.id)" readonly> </td>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script> $('#basic_cost-<?=$quot?>').trigger('change');</script>
<?php } ?>
<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>
<script>

</script>
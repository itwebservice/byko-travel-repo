<form id="frm_tab_4">

    <div class="app_panel">

        <?php
        $basic_cost = $sq_booking_info['basic_amount'];
        $service_charge = $sq_booking_info['service_charge'];
        // echo($sq_booking_info['tcs_tax']);
        // echo($sq_booking_info['tcs_per']);

        $bsmValues = json_decode($sq_booking_info['bsm_values']);
        $service_tax_amount = 0;
        if ($sq_booking_info['tour_service_tax_subtotal'] !== 0.00 && ($sq_booking_info['tour_service_tax_subtotal']) !== '') {
            $service_tax_subtotal1 = explode(',', $sq_booking_info['tour_service_tax_subtotal']);
            for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
                $service_tax = explode(':', $service_tax_subtotal1[$i]);
                $service_tax_amount = $service_tax_amount + $service_tax[2];
            }
        }
        foreach ($bsmValues[0] as $key => $value) {
            switch ($key) {
                case 'basic':
                    $basic_cost = ($value != "") ? $basic_cost + $service_tax_amount : $basic_cost;
                    $inclusive_b = $value;
                    break;
                case 'service':
                    $service_charge = ($value != "") ? $service_charge + $service_tax_amount : $service_charge;
                    $inclusive_s = $value;
                    break;
            }
        }
        $reflections = json_decode($sq_booking_info['reflections']);
        if($reflections[0]->tax_apply_on == '3') { 
            $tax_apply_on = 'Tour Amount';
        }
        else if($reflections[0]->tax_apply_on == '1') { 
            $tax_apply_on = 'Basic Amount';
        }
        else if($reflections[0]->tax_apply_on == '2') { 
            $tax_apply_on = 'Service Charge';
        }
        else if($reflections[0]->tax_apply_on == '4') { 
            $tax_apply_on = 'Total';
        }else{
            $tax_apply_on = '';
        }
        ?>
        <input type="hidden" id="atax_apply_on" name="atax_apply_on" value="<?php echo $reflections[0]->tax_apply_on ?>">
        <input type="hidden" id="tax_value1" name="tax_value1" value="<?php echo $reflections[0]->tax_value ?>">
        <input type="hidden" id="old_total" value="<?= $sq_booking_info['net_total'] ?>"/>
        <div class="container-fluid mg_tp_10">
            <div class="app_panel_content no-pad">
                    <div class="row">
                        <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                            <legend>Costing Details</legend>
                            <div class="bg_white main_block panel-default-inner">
                                <div class="main_block text-center">
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <label for="txt_hotel_expenses">Tour Amount</label>
                                        <input type="text" id="txt_hotel_expenses" name="txt_hotel_expenses"
                                            placeholder="Tour Amount" title="Tour Amount"
                                            value="<?php echo $sq_booking_info['total_hotel_expense']; ?>"
                                            onchange="validate_balance(this.id);calculate_tour_cost(this.id);get_auto_values('booking_date','total_basic_amt','payment_mode','service_charge','markup','update','true','basic','basic',);">
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
                                        <label for="txt_travel_total_expense1">Travel Amount</label>
                                        <input type="text" id="txt_travel_total_expense1" class="text-right"
                                            name="txt_travel_total_expense1" placeholder="Travel Amount"
                                            title="Travel Amount"
                                            onchange="validate_balance(this.id);calculate_tour_cost(this.id)"
                                            value="<?php echo $sq_booking_info['total_travel_expense']; ?>" readonly>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <small id="basic_show"
                                            style="color:#000000"><?= ($inclusive_b == '') ? '&nbsp;' : 'Inclusive Amount : <span>' . $inclusive_b ?></span></small>
                                        <label for="total_basic_amt">Basic Amount</label>
                                        <input type="text" id="total_basic_amt" class="text-right"
                                            name="total_basic_amt" onchange="validate_balance(this.id);"
                                            placeholder="Total Basic Amount" title="Total Basic Amount"
                                            value="<?= $basic_cost ?>" readonly />
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <small id="service_show"
                                            style="color:#000000"><?= ($inclusive_s == '') ? '&nbsp;' : 'Inclusive Amount : <span>' . $inclusive_s ?></span></small>
                                        <label for="service_charge">Service Charge</label>
                                        <input type="text" id="service_charge" name="service_charge"
                                            placeholder="Service Charge" title="Service Charge"
                                            value="<?= $service_charge ?>"
                                            onchange="get_auto_values('booking_date','total_basic_amt','payment_mode','service_charge','markup','update','false','service_charge','discount_amt');validate_balance(this.id);calculate_tour_cost(this.id)">
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <small id="service_show" style="color:#000000">&nbsp;</small>
                                        <label for="service_charge">Discount In</label>
                                        <select title="Discount In" id="discount_in" name="discount_in" class="form-control" onchange="calculate_tour_cost(this.id);get_auto_values('booking_date','total_basic_amt','payment_mode','service_charge','markup','update','false','service_charge','discount_amt');" >
                                            <option value="<?= $sq_booking_info['discount_in']?>"><?= $sq_booking_info['discount_in']?></option>
                                            <?php if($sq_booking_info['discount_in'] != 'Percentage'){ ?>
                                                <option value="Percentage">Percentage</option>
                                            <?php } ?>
                                            <?php if($sq_booking_info['discount_in'] != 'Flat'){ ?>
                                                <option value="Flat">Flat</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <small id="service_show" style="color:#000000">&nbsp;</small>
                                        <label for="service_charge">Discount</label>
                                        <input type="number" id="discount_amt" name="discount_amt" onchange="calculate_tour_cost(this.id);get_auto_values('booking_date','total_basic_amt','payment_mode','service_charge','markup','update','false','service_charge','discount_amt');validate_balance(this.id);" placeholder="Discount" title="Discount" value="<?= $sq_booking_info['discount'] ?>" >
                                        <input type="hidden" id="act_discount"/>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <small>&nbsp;</small>
                                        <label for="subtotal">Subtotal</label>
                                        <input type="text" id="subtotal" name="subtotal" placeholder="Subtotal"
                                            title="Subtotal" value="<?= $sq_booking_info['subtotal'] ?>"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()" readonly>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <small>&nbsp;</small>
                                        <label for="tour_service_tax_subtotal">Tax Subtotal</label>
                                        <input class="form-control" type="text" id="tour_service_tax_subtotal"
                                            title="Tax Subtotal" name="tour_service_tax_subtotal"
                                            value="<?php echo $sq_booking_info['tour_service_tax_subtotal']; ?>"
                                            readonly>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <small>&nbsp;</small>
                                        <label for="tcs_tax">Select TCS Tax</label>
                                        <select title="Select Tax" id="tcs_tax" name="tcs_tax" class="form-control">
                                            <option value=""></option>
											<option value="5" <?php if($sq_booking_info['tcs_tax']==5){ echo "selected"; } ?>>5% TCS</option>
											<option value="20" <?php if($sq_booking_info['tcs_tax']==20){ echo "selected"; } ?>>20% TCS</option>
                                        </select>
                                    </div>
                                    
                                      <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <label for="tcs1">TCS</label>
                                        <input type="number" name="tcs" id="tcs1" readonly class="text-right"
                                            placeholder="TCS" title="TCS" value="<?= $sq_booking_info['tcs_per'] ?>">
                                            
                                    </div>
                                
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <label for="tds">TDS</label>
                                        <input type="number" name="tds" id="tds" class="text-right"
                                            placeholder="TDS" title="TDS" value="<?= $sq_booking_info['tds'] ?>" onchange="calculate_total_tour_cost()">
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                                        <label for="roundoff">Roundoff</label>
                                        <input type="text" name="roundoff" id="roundoff" class="text-right"
                                            placeholder="Round Off" title="RoundOff"
                                            onchange="calculate_total_tour_cost()"
                                            value="<?= $sq_booking_info['roundoff'] ?>" readonly>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
                                        <label for="txt_actual_tour_cost1">Total Amount</label>
                                        <input type="hidden" id="subtotal_with_rue" name="subtotal_with_rue"
                                            value="<?= $sq_booking_info['subtotal_with_rue'] ?>" readonly>
                                        <input type="text" id="txt_actual_tour_cost1"
                                            class="amount_feild_highlight text-right" name="txt_actual_tour_cost1"
                                            placeholder="Total Amount" title="Total Amount"
                                            value="<?= $sq_booking_info['net_total'] ?>" readonly>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs hidden">
                                        <label for="currency_code">Currency</label>
                                        <select name="currency_code" id="currency_code" style="width:100%">
                                            <option value="<?= $sq_booking_info['currency_code'] ?>">
                                                <?= $sq_booking_info['currency_code'] ?></option>
                                            <?php
                                            $sq_currency = mysqlQuery("select * from currency_name_master order by default_currency desc");
                                            while ($row_currency = mysqli_fetch_assoc($sq_currency)) {
                                            ?>
                                            <option value="<?= $row_currency['currency_code'] ?>">
                                                <?= $row_currency['currency_code'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="main_block text-center">
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs hidden">
                                        <label for="rue_cost">ROE</label>
                                        <input type="text" id="rue_cost" name="rue_cost"
                                            onchange="calculate_total_tour_cost(); validate_balance(this.id)"
                                            placeholder="ROE Cost" title="ROE Cost"
                                            value="<?php echo $sq_booking_info['rue_cost']; ?>">
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs hidden">
                                        <label for="txt_tour_service_tax">Tax</label>
                                        <select name="tour_taxation_id" id="tour_taxation_id" title="Tax"
                                            placeholder="Tax"
                                            onchange="generic_tax_reflect(this.id, 'txt_tour_service_tax', 'calculate_total_tour_cost');">
                                            <input type="hidden" id="txt_tour_service_tax" name="txt_tour_service_tax"
                                                value="<?php echo $sq_booking_info['tour_service_tax']; ?>">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20 hidden">
                            <legend>Visa & Insurance Details</legend>
                            <div class="bg_white main_block panel-default-inner">
                                <div class="main_block text-center mg_bt_10">
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
                                        <input type="text" id="visa_country_name" name="visa_country_name"
                                            onchange="validate_city(this.id)" placeholder="Country Name"
                                            title="Country Name" value="<?= $sq_booking_info['visa_country_name'] ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
                                        <input type="text" id="visa_amount" name="visa_amount" placeholder="Amount"
                                            title="Amount"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()"
                                            value="<?= $sq_booking_info['visa_amount'] ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
                                        <input type="text" id="visa_service_charge" name="visa_service_charge"
                                            placeholder="Service Charge" title="Service Charge" class="text-right"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()"
                                            value="<?= $sq_booking_info['visa_service_charge'] ?>" />
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
                                        <select name="visa_taxation_id" id="visa_taxation_id" title="Tax"
                                            placeholder="Tax"
                                            onchange="generic_tax_reflect(this.id, 'visa_service_tax', 'calculate_total_tour_cost');">
                                        </select>
                                        <input type="hidden" id="visa_service_tax" name="visa_service_tax"
                                            value="<?= $sq_booking_info['visa_service_tax'] ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
                                        <input type="text" id="visa_service_tax_subtotal"
                                            name="visa_service_tax_subtotal"
                                            value="<?= $sq_booking_info['visa_service_tax_subtotal'] ?>"
                                            placeholder="Tax Amount" title="Tax Amount" readonly>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
                                        <input type="text" id="visa_total_amount"
                                            class="amount_feild_highlight text-right" name="visa_total_amount"
                                            placeholder="Total Amount" title="Total"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()" readonly
                                            value="<?= $sq_booking_info['visa_total_amount'] ?>">
                                    </div>
                                </div>
                                <div class="main_block text-center">
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10_xs ">
                                        <input type="text" id="insuarance_company_name" onchange="validate_specialChar(this.id)" name="insuarance_company_name" placeholder="Insurance Company" title="Insurance Company" value="<?= $sq_booking_info['insuarance_company_name'] ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10_xs ">
                                        <input type="text" id="insuarance_amount" name="insuarance_amount"
                                            placeholder="Amount" title="Amount"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()"
                                            value="<?= $sq_booking_info['insuarance_amount'] ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10_xs">
                                        <input type="text" id="insuarance_service_charge"
                                            name="insuarance_service_charge" class="text-right"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()"
                                            placeholder="Service Charge" title="Service Charge"
                                            value="<?= $sq_booking_info['insuarance_service_charge'] ?>" />
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10_xs">
                                        <select name="insuarance_taxation_id" id="insuarance_taxation_id" title="Tax"
                                            placeholder="Tax"
                                            onchange="generic_tax_reflect(this.id, 'insuarance_service_tax', 'calculate_total_tour_cost');">

                                        </select>
                                        <input type="hidden" id="insuarance_service_tax" name="insuarance_service_tax"
                                            value="<?= $sq_booking_info['insuarance_service_tax'] ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10_xs">
                                        <input type="text" id="insuarance_service_tax_subtotal"
                                            name="insuarance_service_tax_subtotal"
                                            value="<?= $sq_booking_info['insuarance_service_tax_subtotal'] ?>"
                                            placeholder="Tax Amount" title="Tax Amount" readonly>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10_xs">
                                        <input type="text" id="insuarance_total_amount"
                                            class="amount_feild_highlight text-right" name="insuarance_total_amount"
                                            placeholder="Total Amount" title="Total"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()" readonly
                                            value="<?= $sq_booking_info['insuarance_total_amount'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default panel-body main_block bg_light mg_bt_30 hidden">
                            <legend>Total Tour Costing</legend>
                            <div class="bg_white main_block panel-default-inner">
                                <div class="main_block text-center">
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs hidden">
                                        <label for="visa_total_amount1">Visa</label>
                                        <input type="text" id="visa_total_amount1" name="visa_total_amount1"
                                            placeholder="Total Amount" title="Visa Amount"
                                            onchange="validate_balance(this.id); calculate_total_tour_cost()"
                                            value="<?= $sq_booking_info['visa_total_amount'] ?>" readonly>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs hidden">
                                        <label for="insuarance_total_amount">Insurance</label>
                                        <input type="text" id="insuarance_total_amount1" name="insuarance_total_amount1"
                                            placeholder="Insurance Amount" title="  Insurance Amount" readonly
                                            value="<?= $sq_booking_info['insuarance_total_amount'] ?>">
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
                                        <label for="txt_actual_tour_cost2">Tour</label>
                                        <input type="text" id="txt_actual_tour_cost2" name="txt_actual_tour_cost2"
                                            placeholder="Tour Cost" title="Tour Amount" readonly
                                            value="<?= $sq_booking_info['net_total'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
                            <legend>Booking Summary</legend>
                            <div class="bg_white main_block panel-default-inner">
                                <div class="main_block">
                                    <div class="col-xs-12">
                                        <textarea id="txt_special_request" name="txt_special_request"
                                            placeholder="Enter your special request E.g(Veg Food)"
                                            onchange="validate_address(this.id)"
                                            title="Enter your special request E.g(Veg Food)"><?php echo $sq_booking_info['special_request'] ?></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="booking_date" id="booking_date"
                                    value="<?= get_date_user($sq_booking_info['booking_date']) ?>">
                            </div>
                        </div>
                </div>
                <div class="panel panel-default main_block bg_light pad_8 text-center mg_bt_150" style="background-color: #fff; border: none;">
                    <div class="text-center">
                        <div class="col-xs-12">
                            <button class="btn btn-sm btn-info ico_left" type="button" onclick="back_to_tab_3()"><i
                                    class="fa fa-arrow-left"></i>&nbsp;&nbsp;Previous</button>&nbsp;&nbsp;&nbsp;
                            <button class="btn btn-sm btn-success" id="btn_package_tour_master_update"><i
                                    class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</form>
<?= end_panel() ?>
<script src="../js/tab_4.js"></script>
<script src="../js/booking_update.js"></script>
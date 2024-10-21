<tr>
        <td><input class="css-checkbox" id="chk_exc<?= $offset ?>1" type="checkbox"
                onchange="excursion_amount_calculate('exc_date<?= $offset ?>-1');calculate_exc_expense('tbl_dynamic_exc_booking')"
                checked><label class="css-label" for="chk_visa<?= $offset ?>1"> <label></td>
        <td><input maxlength="15" value="1" type="text" name="username" placeholder="Sr. No." class="form-control"
                disabled /></td>
        <td><input type="text" style="width:200px" id="exc_date-<?= $offset ?>1" name="exc_date-<?= $offset ?>1"
                placeholder="Activity Date & Time" title="Activity Date & Time" class="app_datetimepicker form-control"
                value="<?= date('d-m-Y H:i') ?>" style="width:110px" onchange="get_excursion_amount();" data-toggle="tooltip"></td>
        <td><select id="city_name-<?= $offset ?>1" class="form-control city_name_exc" name="city_name-" title="City Name"
                onchange="get_excursion_list(this.id);" style="width:150px">
                </select>
        </td>
        <td><select id="excursion-<?= $offset ?>1" class="app_select2 form-control" title="Activity Name"
                name="excursion-<?= $offset ?>1" onchange="get_excursion_amount();" style="width:160px" data-toggle="tooltip">
                <option value="">*Activity Name</option>
                </select></td>
        <td><select name="transfer_option-<?= $offset ?>1" id="transfer_option-<?= $offset ?>1" data-toggle="tooltip"
                class="form-control app_select2" title="Transfer Option" style="width:163px"
                onchange="get_excursion_amount('calculate_exc_expense');">
                <option value="">*Transfer Option</option>
                <option value="Without Transfer">Without Transfer</option>
                <option value="Sharing Transfer">Sharing Transfer</option>
                <option value="Private Transfer">Private Transfer</option>
                <option value="SIC">SIC</option>
                </select></td>
        <td><input class="form-control" type="text" id="total_adult-<?= $offset ?>1" name="total_adult-<?= $offset ?>1"
                placeholder="*Total Adult" title="Total Adult"
                onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id);get_auto_values('balance_date','exc_issue_amount','payment_mode','service_charge','markup','save','true','service_charge');" style="width:110px"></td>
        <td><input class="form-control" type="text" id="total_children-<?= $offset ?>1" name="total_children-<?= $offset ?>1" placeholder="*Total Child" title="Total Child" onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id);get_auto_values('balance_date','exc_issue_amount','payment_mode','service_charge','markup','save','true','service_charge');" style="width:110px"></td>
        <td><input class="form-control" type="text" id="total_infant-<?= $offset ?>1" name="total_infant-<?= $offset ?>1" placeholder="Total Infant" title="Total Infant" onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id);get_auto_values('balance_date','exc_issue_amount','payment_mode','service_charge','markup','save','true','service_charge');" style="width:110px"></td>
        <td><input class="form-control" type="text" id="adult_cost-<?= $offset ?>1" name="adult_cost-<?= $offset ?>1" placeholder="Adult Ticket Amount" title="Adult Ticket Amount" onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id)" style="width:142px"></td>
        <td><input class="form-control" type="text" id="child_cost-<?= $offset ?>1" name="child_cost-<?= $offset ?>1" placeholder="Child Ticket Amount" title="Child Ticket Amount" onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id)" style="width:142px"></td>
        <td><input class="form-control" type="text" id="infant_cost-<?= $offset ?>1" name="infant_cost-<?= $offset ?>1" placeholder="Infant Ticket Amount" title="Infant Ticket Amount" onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id)" style="width:144px"></td>
        <td><input class="form-control" type="text" id="total_vehicle-<?= $offset ?>1" name="total_vehicle-<?= $offset ?>1" placeholder="Total Vehicle" title="Total Vehicle" onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id)" style="width:125px"></td>
        <td><input class="form-control" type="text" id="transfer_cost-<?= $offset ?>1" name="transfer_cost-<?= $offset ?>1" placeholder="Transfer Amount" title="Transfer Amount" onchange="excursion_amount_calculate(this.id);calculate_exc_expense('tbl_dynamic_exc_booking');validate_balance(this.id)" style="width:125px"></td>
        <td><input class="form-control" type="text" id="total_amount-<?= $offset ?>1" name="total_amount-<?= $offset ?>1"
                placeholder="Activity Amount" title="Activity Amount" onchange="validate_balance(this.id)" style="width:120px">
        </td>
</tr>

<script>
city_lzloading('.city_name_exc');
$("transfer_option-<?= $offset ?>1").on("click", function() {
        get_excursion_amount(calculate_exc_expense)
});
$('#exc_date-<?= $offset ?>1').datetimepicker({ format: 'd-m-Y H:i' });
</script>
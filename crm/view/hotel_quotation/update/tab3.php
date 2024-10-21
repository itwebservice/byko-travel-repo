<?php
$bsmValues = json_decode($sq_quotation['bsmValues']);
?>
<form id="frm_tab3">
    <div class="app_panel">
        <div class="container" id="table_data">
            <?php
            foreach($costDetails as $values){
                $option = $values['option'];
                ?>
                <div class="row mg_tp_10">
                    <div class="col-xs-12">
                        <h3 class="editor_title">Costing for option- <?= $option?></h3>
                        <div class="panel panel-default panel-body app_panel_style">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="table-responsive">
                                        <table id="dynamic_quotation_costing_h_<?= $option ?>">
                                            <?php                                            
                                            $data = $values['costing'];
                                            $basic_cost1 = $data['hotel_cost'];
                                            $service_charge = $data['service_charge'];
                                            $markup = $data['markup_cost'];

                                            $service_tax_amount = 0;
                                            if ($data['tax_amount'] !== 0.00 && ($data['tax_amount']) !== '') {
                                                $service_tax_subtotal1 = explode(',', $data['tax_amount']);
                                                for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
                                                    $service_tax = explode(':', $service_tax_subtotal1[$i]);
                                                    $service_tax_amount = $service_tax_amount + floatval($service_tax[2]);
                                                }
                                            }
                                            
                                            $markupservice_tax_amount = 0;
                                            if ($data['markup_tax'] !== 0.00 && $data['markup_tax'] !== "") {
                                                $service_tax_markup1 = explode(',', $data['markup_tax']);
                                                for ($i = 0; $i < sizeof($service_tax_markup1); $i++) {
                                                    $service_tax = explode(':', $service_tax_markup1[$i]);
                                                    $markupservice_tax_amount = $markupservice_tax_amount + floatval($service_tax[2]);
                                                }
                                            }
                                            if($data['tax_apply_on']== '1') { 
                                                $tax_apply_on = 'Basic Amount';
                                            }
                                            else if($data['tax_apply_on'] == '2') { 
                                                $tax_apply_on = 'Service Charge';
                                            }
                                            else if($data['tax_apply_on'] == '3') { 
                                                $tax_apply_on = 'Total';
                                            }else{
                                                $tax_apply_on = '';
                                            }
                                            $inclusive_b = '';
                                            $inclusive_s = '';
                                            $inclusive_m = '';
                                            ?>
                                            <tr>
                                                <td class="header_btn header_btn" style="padding:4px"><small id="basic_show-u_<?= $option ?>"style="color:red"><?= ($inclusive_b == '') ? '&nbsp;' : ' : <span>' . $inclusive_b ?></span></small><input type="number" id="basic_cost-u_<?= $option ?>" name="basic_cost-u_<?= $option ?>" placeholder="Basic Amount" title="Basic Amount" value="<?= $basic_cost1 ?>" onchange="validate_balance(this.id);get_auto_values('quotation_date1','basic_cost-u_<?= $option ?>','payment_mode','service_charge-u_<?= $option ?>','markup_cost-u_<?= $option ?>','update','true','service_charge');">
                                                </td>
                                                <td class="header_btn header_btn" style="padding:4px"><small id="service_show-u_<?= $option ?>" style="color:red"><?= ($inclusive_s == '') ? '&nbsp;' : 'Inclusive Amount : <span>' . $inclusive_s ?></span></small><input type="number" id="service_charge-u_<?= $option ?>" name="service_charge-u_<?= $option ?>" placeholder="Service Charge" title="Service Charge" value="<?= $service_charge ?>" onchange="validate_balance(this.id);get_auto_values('quotation_date1','basic_cost-u_<?= $option ?>','payment_mode','service_charge-u_<?= $option ?>','markup_cost-u_<?= $option ?>','update','false','service_charge');"></td>
                                                <td class="header_btn header_btn" style="padding:4px"><small>&nbsp;</small><input type="text" id="tax_amount-u_<?= $option ?>" name="tax_amount-u_<?= $option ?>" placeholder="Tax Amount" title="Tax Amount" value="<?= $data['tax_amount'] ?>" onchange="validate_balance(this.id)" readonly> </td>
                                                <td class="header_btn header_btn" style="padding:4px"><small>&nbsp;</small><input type="number" id="markup_cost-u_<?= $option ?>" name="markup_cost-u_<?= $option ?>" placeholder="Markup Amount" title="Markup Amount" style="width:160px" value="<?= $markup ?>" onchange="validate_balance(this.id);get_auto_values('quotation_date1','basic_cost-u_<?= $option ?>','payment_mode','service_charge-u_<?= $option ?>','markup_cost-u_<?= $option ?>','update','false','service_charge');">
                                                </td>
                                                <td class="header_btn header_btn" style="padding:4px"><small id="markup_show-u_<?= $option ?>" style="color:red"><?= ($inclusive_m == '') ? '&nbsp;' : 'Inclusive Amount : <span>' . $inclusive_m ?></span></small><input type="text" id="tax_markup-u_<?= $option ?>" name="tax_markup-u_<?= $option ?>" placeholder="Markup Tax" title="Markup Tax" value="<?= $data['markup_tax'] ?>" onchange="validate_balance(this.id)" readonly> </td>
                                                <td class="header_btn header_btn" style="padding:4px"><small>&nbsp;</small><input type="text" id="roundoff-u_<?= $option ?>" name="roundoff-u_<?= $option ?>" placeholder="Round Off" title="Round Off" value="<?= !empty($data['roundoff']) ? $data['roundoff'] : 0.00  ?>" onchange="validate_balance(this.id)" readonly> </td>
                                                <td class="header_btn header_btn" style="padding:4px"><small>&nbsp;</small><input type="text" id="tcs-u_<?= $option ?>" name="tcs-<?= $option ?>" placeholder="TCS" title="TCS" value="<?= !empty($data['tcs_amnt']) ? $data['tcs_amnt'] : 0.00  ?>"  readonly> </td>
                                                <td class="header_btn header_btn" style="padding:4px; display:none;"><small>&nbsp;</small><input type="text" id="tds-u_<?= $option ?>" name="tds-<?= $option ?>" placeholder="TDS" title="TDS" value="<?= !empty($data['tds_amnt']) ? $data['tds_amnt'] : 0.00  ?>"  readonly> </td>
                                                <td class="header_btn header_btn" style="padding:4px"><small>&nbsp;</small><input type="number" id="total_amount-u_<?= $option ?>" class="amount_feild_highlight text-right form-control" name="total_amount-u_<?= $option ?>" placeholder="Total Amount" title="Total Amount" value="<?= !empty($data['total_amount']) ? $data['total_amount'] : 0.00 ?>" onchange="validate_balance(this.id)" readonly> </td>
                                            </tr>
                                            <input type="hidden" id="tcs_tax-u_<?= $option ?>"  name="tcs_tax-<?= $option ?>" value="<?= !empty($data['tcs_tax']) ? $data['tcs_tax'] : 0  ?>">
                                            
                                            <input type="hidden" id="tax_apply_on-u_<?= $option ?>" name="tax_apply_on" value="<?php echo $tax_apply_on ?>">
                                            <input type="hidden" id="atax_apply_on-u_<?= $option ?>" name="atax_apply_on" value="<?php echo $data['tax_apply_on'] ?>">
                                            <input type="hidden" id="tax_value1-u_<?= $option ?>" name="tax_value1" value="<?php echo $data['tax_value'] ?>">
                                            <input type="hidden" id="markup_tax_value1-u_<?= $option ?>" name="markup_tax_value1" value="<?php echo $data['markup_tax_value'] ?>">
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
                <select name="currency_code1" id="currency_code1" title="Currency" style="width:100%"
                    data-toggle="tooltip" required>
                    <?php
                    $sq_currencyd = mysqli_fetch_assoc(mysqlQuery("SELECT `id`,`currency_code` FROM `currency_name_master` WHERE id=" . $sq_quotation['currency_code']));
                    ?>
                    <option value="<?= $sq_currencyd['id'] ?>"><?= $sq_currencyd['currency_code'] ?></option>
                    <?php
                    $sq_currency = mysqlQuery("select * from currency_name_master order by currency_code");
                    while ($row_currency = mysqli_fetch_assoc($sq_currency)) {
                    ?>
                    <option value="<?= $row_currency['id'] ?>"><?= $row_currency['currency_code'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row text-center mg_tp_20">
            <div class="col-xs-12">
                <button class="btn btn-info btn-sm ico_left" type="button" onclick="switch_to_tab2()"><i
                        class="fa fa-arrow-left"></i>&nbsp;&nbsp Previous</button>
                &nbsp;&nbsp;
                <button id="btn_quotation_update" class="btn btn-info btn-sm ico_right">Update&nbsp;&nbsp;<i
                        class="fa fa-floppy-o"></i></button>
            </div>
        </div>
    </div>
</form>

<script>
$('#currency_code1').select2();

function switch_to_tab2() {
    $('#tab3_head').addClass('done');
    $('#tab2_head').addClass('active');
    $('.bk_tab').removeClass('active');
    $('#tab2').addClass('active');
    $('html, body').animate({
        scrollTop: $('.bk_tab_head').offset().top
    }, 200);
}

$('#frm_tab3').validate({

    submitHandler: function(form, e) {
        e.preventDefault();
        var quotation_id = $('#quotation_id').val();
        var quotation_date = $('#quotation_date1').val();
        var currency_code = $('#currency_code1').val();
        var hotel_requirements = $('#hotel_requirements1').val()
        var active_flag = $('#active_flag1').val();
        var base_url = $('#base_url').val();

        var enquiryDetails = {
            enquiry_id: $('#enquiry_id1').val(),
            customer_name: $('#customer_name1').val(),
            email_id: $('#email_id1').val(),
            country_code: $('#country_code1').val(),
            whatsapp_no: $('#whatsapp_no1').val(),
            total_adult: $('#total_adult1').val(),
            children_without_bed: $('#children_without_bed1').val(),
            children_with_bed: $('#children_with_bed1').val(),
            total_infant: $('#total_infant1').val(),
            total_members: $('#total_members1').val()
        };

        var nofquotation = $('#nofquotation').val();
		var optionJson = [];
        for (var quot = 1; quot <= Number(nofquotation); quot++) {

            var table = document.getElementById("dynamic_table_list_h_" + quot);
            var rowCount = table.rows.length;
            var hotelDetails = [];
            for (var i = 0; i < rowCount; i++) {

                var row = table.rows[i];
                if (row.cells[0].childNodes[0].checked) {

                    hotelDetails.push({
                        tour_type: row.cells[2].childNodes[0].value,
                        city_id: row.cells[3].childNodes[0].value,
                        hotel_id: row.cells[4].childNodes[0].value,
                        hotel_cat: row.cells[5].childNodes[0].value,
                        meal_plan: row.cells[6].childNodes[0].value,
                        checkin: row.cells[7].childNodes[0].value,
                        checkout: row.cells[8].childNodes[0].value,
                        hotel_type: row.cells[9].childNodes[0].value,
                        hotel_stay_days: row.cells[10].childNodes[0].value,
                        total_rooms: row.cells[11].childNodes[0].value,
                        extra_bed: row.cells[12].childNodes[0].value,
                        hotel_cost: row.cells[13].childNodes[0].value
                    });
                }
            }
			optionJson.push({
				'option':quot,
				'data':hotelDetails});
        }

		var costingJson = [];
        for (var quot = 1; quot <= Number(nofquotation); quot++) {
            
            var table = document.getElementById("dynamic_quotation_costing_h_" + quot);
            var rowCount = table.rows.length;
            var costingDetails = [];

            var tax_apply_on = $('#atax_apply_on-u_'+quot).val();
            var tax_value = $('#tax_value1-u_'+quot).val();
            var markup_tax_value = $('#markup_tax_value1-u_'+quot).val();
            var tcs_tax = $('#tcs_tax-u_'+quot).val();
            var row = table.rows[0];
            costingDetails = {
                hotel_cost: row.cells[0].childNodes[1].value,
                service_charge: row.cells[1].childNodes[1].value,
                tax_amount: row.cells[2].childNodes[1].value,
                markup_cost: row.cells[3].childNodes[1].value,
                markup_tax: row.cells[4].childNodes[1].value,
                roundoff: row.cells[5].childNodes[1].value,
                tcs_amnt: row.cells[6].childNodes[1].value,
                tds_amnt: row.cells[7].childNodes[1].value,
                total_amount: row.cells[8].childNodes[1].value,
                'tcs_tax':tcs_tax,
                'tax_apply_on':tax_apply_on,
                'tax_value':tax_value,
                'markup_tax_value':markup_tax_value
            };
			costingJson.push({
				'option':quot,
				'costing':costingDetails});
            var bsmValues = {
                "basic": $(row.cells[0].childNodes[0]).find('span').text(),
                "service": $(row.cells[1].childNodes[0]).find('span').text(),
                "markup": $(row.cells[4].childNodes[0]).find('span').text()
            }
        }
        $.post(base_url + '/controller/hotel/quotation/quotation_update.php', {
            hotelDetails: optionJson,
            costingDetails: costingJson,
            enquiryDetails: enquiryDetails,
            quotation_id: quotation_id,
            bsmValues: bsmValues,
            quotation_date: quotation_date,
            currency_code: currency_code,
            active_flag: active_flag,hotel_requirements:hotel_requirements
        }, function(message) {

            $('#btn_quotation_update').button('reset');
            var msg = message.split('--');

            if (msg[0] == "error") {
                error_msg_alert(msg[1]);
                return false;
            } else {
                $('#vi_confirm_box').vi_confirm_box({
                    false_btn: false,
                    message: message,
                    true_btn_text: 'Ok',
                    callback: function(data1) {
                        if (data1 == "yes") {
                            $('#btn_quotation_update').button('reset');
                            window.location.href = base_url + 'view/hotel_quotation/index.php';
                            quotation_list_reflect();
                        }
                    }
                });
            }
        });
    }
});
</script>
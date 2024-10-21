<?php
include "../../model/model.php";

$customer_id_u = $_POST['customer_id'];
$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id_u'"));
$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);
$email_id = $encrypt_decrypt->fnDecrypt($sq_customer['email_id'], $secret_key);
?>
<div class="modal fade" id="customer_update_modal" data-backdrop="static" data-keyboard="false" role="dialog"
    aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Update Customer</h4>
            </div>
            <div class="modal-body">

                <form id="frm_customer_update">
                    <input type="hidden" id="customer_id_u" name="customer_id_u" value="<?= $customer_id_u ?>">
                    <div class="row mg_bt_20">
                        <div class="col-md-4 col-md-offset-2">
                            <select name="cust_type1" id="cust_type1" onchange="corporate_fields_reflect('update');"
                                title="Customer Type" disabled>
                                <option value="<?= $sq_customer['type'] ?>"><?= $sq_customer['type'] ?></option>
                                <?php get_customer_type_dropdown(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="cust_source" id="cust_source" class="form-control" data-toggle="tooltip" title="Customer Source">
                                <?php if ($sq_customer['source'] != '') { ?>
                                <option value="<?= $sq_customer['source'] ?>"><?= $sq_customer['source'] ?></option>
                                <?php } ?>
                                <?php get_customer_source_dropdown(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mg_bt_10">
                        <div id="corporate_fields1"></div>
                    </div>
                    <div class="panel panel-default panel-body app_panel_style mg_tp_30 feildset-panel">
                        <legend>Personal Information</legend>
                        <div class="row mg_bt_10">
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <input type="text" id="first_name1" name="first_name1" placeholder="*First Name"
                                    onchange="fname_validate(this.id);" title="First Name"
                                    value="<?= $sq_customer['first_name'] ?>">
                            </div>
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <input type="text" id="middle_name1" name="middle_name1"
                                    onchange="fname_validate(this.id);" placeholder="Middle Name" title="Middle Name"
                                    value="<?= $sq_customer['middle_name'] ?>">
                            </div>
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <input type="text" id="last_name1" name="last_name1" onchange="fname_validate(this.id);"
                                    placeholder="Last Name" title="Last Name" value="<?= $sq_customer['last_name'] ?>">
                            </div>
                        </div>
                        <div class="row mg_bt_10">
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <select name="cmb_gender" id="cmb_gender" class="form-control" title="Select Gender">
                                    <?php if ($sq_customer['gender'] != '') { ?>
                                    <option value="<?= $sq_customer['gender'] ?>"><?= $sq_customer['gender'] ?></option>
                                    <?php } ?>
                                    <option value="">Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <input type="text" id="birth_date1" name="birth_date1" placeholder="Birth Date"
                                    title="Birth Date" value="<?php echo get_date_user($sq_customer['birth_date']); ?>"
                                    onchange="calculate_age_generic('birth_date1', 'age1')">
                            </div>
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <input type="text" id="age1" name="age1" placeholder="Age" title="Age"
                                    onchange="validate_balance(this.id);" value="<?= $sq_customer['age'] ?>" readonly>
                            </div>
                        </div>
                        <div class="row mg_bt_10">
                            <div class="col-sm-2 col-xs-12">
                                <input type="hidden" name="country_code_val" id="country_code_val"
                                    value="<?= $sq_customer['country_code'] ?>">
                                <select name="country_code1" id="country_code1" style="width:100px;">
                                    <?= get_country_code() ?>
                                </select>
                            </div>
                            <div class="col-sm-2 mg_bt_10_sm_xs">
                                <input type="text" id="contact_no1" name="contact_no1"
                                    onchange="mobile_validate(this.id)" placeholder="*Mobile No" title="Mobile No"
                                    value="<?= str_replace($sq_customer['country_code'], "", $contact_no); ?>">
                            </div>
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <input type="text" id="email_id1" name="email_id1" placeholder="Email ID"
                                    title="Email ID" value="<?= $email_id ?>">
                            </div>
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <input type="text" id="service_tax_no1" name="service_tax_no1"
                                    style="text-transform: uppercase;" placeholder="Tax No" title="Tax No"
                                    onchange="validate_alphanumeric(this.id);"
                                    value="<?= $sq_customer['service_tax_no'] ?>" style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 col-xs-12">
                                <input type="text" id="cust_pan1" name="cust_pan1"
                                    onchange="validate_alphanumeric(this.id);" value="<?= $sq_customer['pan_no']  ?>"
                                    placeholder="Personal Identification No(PIN)" title="Personal Identification No(PIN)" style="text-transform: uppercase;">
                            </div>
                            <div class="col-sm-4 col-xs-12">
                                <input class="form-control" type="number" id="op_balance1" name="op_balance1"
                                    placeholder="*Opening Balance" title="Opening Balance"
                                    value="<?= $sq_customer['op_balance']  ?>">
                            </div>
                            <div class="col-sm-4 col-xs-12">
                                <select class="form-control" id="balance_side1" name="balance_side1"
                                    title="Balance Side" style="width:100%;">
                                    <option value="<?= $sq_customer['balance_side'] ?>">
                                        <?= $sq_customer['balance_side'] ?></option>
                                    <?php if ($sq_customer['balance_side'] != 'Debit') { ?>
                                    <option value="Debit">Debit</option>
                                    <?php }
                                    if ($sq_customer['balance_side'] != 'Credit') { ?>
                                    <option value="Credit">Credit</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default panel-body app_panel_style mg_tp_30 feildset-panel">
                        <legend>Address Information</legend>
                        <div class="row">
                            <div class="col-sm-4 col-xs-12 mg_bt_10">
                                <input type="text" name="cust_address1" id="cust_address1"
                                    onchange="validate_address(this.id);" placeholder="Address-1" title="Address 1"
                                    value="<?= $sq_customer['address'] ?>" />
                            </div>
                            <div class="col-sm-4 col-xs-12 mg_bt_10">
                                <input type="text" name="cust_address2" id="cust_address2"
                                    onchange="validate_address(this.id);" placeholder="Address-2" title="Address 2"
                                    value="<?= $sq_customer['address2'] ?>" />
                            </div>
                            <div class="col-sm-4 col-xs-12 mg_bt_10">
                                <input type="text" name="city" id="city" placeholder="City"
                                    onchange="validate_city(this.id);" title="City"
                                    value="<?= $sq_customer['city'] ?>" />
                            </div>
                            <div class="col-sm-4 col-xs-12 mg_bt_10_sm_xs">
                                <select name="cust_state1" id="cust_state1" title="State/Country Name"
                                    style="width : 100%" required>
                                    <?php if ($sq_customer['state_id'] != '') {
                                        $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_customer[state_id]'"));
                                        ?>
                                        <option value="<?= $sq_customer['state_id'] ?>"><?= $sq_state['state_name'] ?>
                                        </option>
                                    <?php } ?>
                                    <?php get_states_dropdown() ?>
                                </select>
                            </div>
                            <div class="col-sm-4 mg_bt_10_sm_xs">
                                <select name="active_flag1" id="active_flag1" title="Status">
                                    <option value="<?= $sq_customer['active_flag'] ?>">
                                        <?= $sq_customer['active_flag'] ?></option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="users_div_update"></div>

                    <div class="row text-center">
                        <div class="col-md-12">
                            <button class="btn btn-sm btn-success" id="btn_update"><i
                                    class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
$('#gl_id1,#cust_state1,#country_code1').select2();
$('#customer_update_modal').modal('show');
$('#birth_date1').datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});
corporate_fields_reflect('update');
customer_users_reflect('update');
$(function() {
    $('#country_code1').val($('#country_code_val').val());
    $('#country_code1').trigger('change');
    $('#frm_customer_update').validate({
        rules: {
            first_name1: {
                required: true
            },
            active_flag1: {
                required: true
            },
            company_name: {
                required: true
            },
            cust_type1: {
                required: true
            },
            contact_no1: {
                required: true
            },
            country_code1: {
                required: true
            },
            cust_state: {
                required: true
            },
            op_balance1: {
                required: true
            },
            balance_side1: {
                required: true
            }
        },
        errorPlacement: function(error, element) {
            if (element.attr('id') == 'country_code1') {
                error.insertAfter(element.next('span')); // select2
            } else {
                error.insertAfter(element); // default
            }
        },
        submitHandler: function(form) {

            var customer_id = $('#customer_id_u').val();
            var first_name = $('#first_name1').val();
            var middle_name = $('#middle_name1').val();
            var last_name = $('#last_name1').val();
            var gender = $('#cmb_gender').val();
            var birth_date = $('#birth_date1').val();
            var age = $('#age1').val();
            var contact_no = $('#contact_no1').val();
            var country_code = $('#country_code1').val();
            var cust_email_id = $('#email_id1').val();
            var address = $('#cust_address1').val();
            var address2 = $('#cust_address2').val();
            var city = $('#city').val();
            var active_flag = $('#active_flag1').val();
            var service_tax_no1 = $('#service_tax_no1').val();
            var landline_no = $('#cust_landline_no').val();
            var alt_email_id = $('#cust_alt_email_id').val();
            var company_name = $('#corpo_company_name').val();
            var cust_type = $('#cust_type1').val();
            var cust_state = $('#cust_state1').val();
            var cust_pan = $('#cust_pan1').val();
            var op_balance = $('#op_balance1').val();
            var balance_side = $('#balance_side1').val();
            var base_url = $('#base_url').val();
            var cust_source = $('#cust_source').val();
            
            var checkbox_array = [];
            var user_name_array = [];
            var mobile_no_array = [];
            var email_id_array = [];
            var status_array = [];
            var entry_id_array = [];
            if(cust_type == 'B2B' || cust_type == 'Corporate'){

                var table = document.getElementById("tbl_cust_users1");
                var rowCount = table.rows.length;
                for(var i=0; i<rowCount; i++){

                    var row = table.rows[i];
                    var check_status = row.cells[0].childNodes[0].checked;
                    var user_name = row.cells[2].childNodes[0].value;
                    var mobile_no = row.cells[3].childNodes[0].value;
                    var email_id = row.cells[4].childNodes[0].value;
                    var status = row.cells[5].childNodes[0].value;
                    if(row.cells[6]){
                        var entry_id = row.cells[6].childNodes[0].value;
                    }else{
                        var entry_id = '';
                    }

                    if(row.cells[0].childNodes[0].checked){  

                        if(user_name==""){
                            error_msg_alert("User name is required in row : "+(i+1));
                            $('#btn_csave').prop('disabled', false);
                            return false;
                        }
                    }
                    checkbox_array.push(check_status);
                    user_name_array.push(user_name);
                    mobile_no_array.push(mobile_no);
                    email_id_array.push(email_id);
                    status_array.push(status);
                    entry_id_array.push(entry_id);
                }
            }

            $('#btn_update').button('loading');
            $.ajax({
                type: 'post',
                url: base_url + 'controller/customer_master/customer_update.php',
                data: {
                    customer_id: customer_id,
                    first_name: first_name,
                    middle_name: middle_name,
                    last_name: last_name,
                    gender: gender,
                    birth_date: birth_date,
                    age: age,
                    contact_no: contact_no,
                    email_id: cust_email_id,
                    address: address,
                    address2: address2,
                    city: city,
                    active_flag: active_flag,
                    service_tax_no1: service_tax_no1,
                    landline_no: landline_no,
                    alt_email_id: alt_email_id,
                    company_name: company_name,
                    cust_type: cust_type,
                    cust_state: cust_state,
                    cust_pan: cust_pan,
                    op_balance: op_balance,
                    balance_side: balance_side,
                    cust_source: cust_source,
                    country_code: country_code,user_name_array:user_name_array,mobile_no_array:mobile_no_array,email_id_array:email_id_array,status_array:status_array,entry_id_array:entry_id_array,checkbox_array:checkbox_array
                },
                success: function(result) {
                    var msg = result.split('--');
                    if (msg[0] == 'error') {
                        error_msg_alert(msg[1]);
                        $('#btn_update').button('reset');
                        return false;
                    } else {
                        success_msg_alert(result);
                        $('#customer_update_modal').modal('hide');
                        $('#btn_update').button('reset');
                        customer_list_reflect();
                    }
                }
            });
        }
    });

});
</script>
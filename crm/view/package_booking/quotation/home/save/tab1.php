<?php
global $show_entries_switch;
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$role_id = $_SESSION['role_id'];
?>
<form id="frm_tab1">

    <div class="app_panel">

        <div class="container">

            <input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>">
            <input type="hidden" id="emp_id" name="emp_id" value="<?= $emp_id ?>">
            <input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>">
            <input type="hidden" id="financial_year_id" name="financial_year_id" value="<?= $financial_year_id ?>">
            <input type="hidden" id="unique_timestamp" name="unique_timestamp" value="<?= md5(time()) ?>">
            <input type="hidden" id="hotel_sc" name="hotel_sc">
            <input type="hidden" id="hotel_markup" name="hotel_markup">
            <input type="hidden" id="hotel_taxes" name="hotel_taxes">
            <input type="hidden" id="hotel_markup_taxes" name="hotel_markup_taxes">
            <input type="hidden" id="hotel_tds" name="hotel_tds">
            <input type="hidden" id="branch_admin_id1" name="branch_admin_id1" value="<?= $branch_admin_id ?>">
            <div class="row mg_tp_10">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <select name="enquiry_id" id="enquiry_id" style="width:100%" onchange="get_enquiry_details()">
                        <option value="">*Select Enquiry</option>
                        <option value="0"><?= "New Enquiry" ?></option>
                        <?php
						if ($role == 'Admin') {
							$sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Package Booking') and status!='Disabled' order by enquiry_id desc");
						} 
                        else if ($branch_status == 'yes') {

                            if ($role == 'Branch Admin' || $role == 'Accountant' || $role_id > '7') {
                                $q = "select * from enquiry_master where enquiry_type in('Package Booking') and status!='Disabled' and branch_admin_id='$branch_admin_id' order by enquiry_id desc";
                            }
                            elseif ($role != 'Admin' && $role != 'Branch Admin' && $role_id != '7' && $role_id < '7') {

                                if($show_entries_switch == 'No'){
                                    $q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
                                }
                                else{
                                    if($role == 'Backoffice'){
                                        $q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id in(select emp_id from emp_master where branch_id='$branch_admin_id') and status!='Disabled' order by enquiry_id desc";
                                    }else{
                                        $q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
                                    }
                                }
                            }
                            $sq_enq = mysqlQuery($q);
                        }
                        elseif ($branch_status != 'yes' && ($role == 'Branch Admin' || $role_id == '7' || $role_id > '7')) {

                            $sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Package Booking') and status!='Disabled' order by enquiry_id desc");
                        }
                        elseif ($role != 'Admin' && $role != 'Branch Admin' && $role_id != '7' && $role_id < '7') {
                            
                            if($show_entries_switch == 'No'){
                                $q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
                            }else{

                                if($role == 'Backoffice'){
                                    $q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id in(select emp_id from emp_master where branch_id='$branch_admin_id') and status!='Disabled' order by enquiry_id desc";
                                }else{
                                    $q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
                                }
                            }
                            $sq_enq = mysqlQuery($q);
                        }
						while ($row_enq = mysqli_fetch_assoc($sq_enq)) {

							$sq_enq1 = mysqli_fetch_assoc(mysqlQuery("SELECT followup_status FROM `enquiry_master_entries` WHERE `enquiry_id` = '$row_enq[enquiry_id]' ORDER BY `entry_id` DESC"));
							if($sq_enq1['followup_status'] != 'Dropped'){
                                ?>
                                <option value="<?php echo $row_enq['enquiry_id']; ?>">Enq<?= $row_enq['enquiry_id'] ?> : <?= $row_enq['name'] ?></option>
                                <?php
                            }
						}
						?>
                    </select>
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" id="tour_name" name="tour_name" placeholder="Tour Name" title="Tour Name">
                    <input type="hidden" id="destinations" name="destinations" placeholder="destinations"
                        value='<?= get_destinations() ?>'>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <select name="booking_type" id="booking_type" title="Tour Type">
                        <option value="Domestic">Domestic</option>
                        <option value="International">International</option>
                    </select>
                </div>
            </div>
            <div class="row mg_tp_10">
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="customer_name" name="customer_name" onchange="fname_validate(this.id)"
                        placeholder="*Customer Name" title="Customer Name" required>
                    <input type="hidden" id="cust_data" name="cust_data" value='<?= get_customer_hint($branch_status) ?>'>
                </div>
                <div class="col-md-4 col-sm-6 mg_bt_10 hidden" id="user_dropdown">
                </div>
                <div class="col-md-4 col-sm-6 mg_bt_10">
                    <div class="col-md-4" style="padding-left:0px;">
                        <select style="width:125px !important;" class="form-control" name="country_code" id="country_code" title="Country code">
                            <?= get_country_code(); ?>
                        </select>
                    </div>
                    <div class="col-md-8" style="padding-left:12px;padding-right:0px;">
                        <input type="text" class="form-control" id="mobile_no" onchange="mobile_validate(this.id);"
                            name="mobile_no" placeholder="*WhatsApp No" title="WhatsApp No">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mg_bt_10">
                    <input type="text" id="email_id" name="email_id" placeholder="Email ID" title="Email ID">
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="from_date" name="from_date" placeholder="*From Date" title="From Date"
                        onchange="validate_validDate('from_date','to_date');get_to_date(this.id,'to_date');total_days_reflect();"
                        required>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="to_date" name="to_date" placeholder="*To Date" title="To Date"
                        onchange="validate_validDate('from_date','to_date');total_days_reflect()" required>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_days" name="total_days" placeholder="Total Night(s)" title="Total Night(s)" disabled>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_adult" name="total_adult" placeholder="Total Adult(s)" title="Total Adult(s)"
                        onchange="total_passangers_calculate(); validate_balance(this.id)" required>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_infant" name="total_infant" placeholder="Total Infant(s)"
                        title="Total Infant(s)" onchange="total_passangers_calculate(); validate_balance(this.id);"
                        required>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" class="form-control" id="children_without_bed" name="children_without_bed"
                        onchange="validate_balance(this.id);total_passangers_calculate();"
                        placeholder="Child Without Bed(s)" title="Child Without Bed(s)" required>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" class="form-control" id="children_with_bed" name="children_with_bed"
                        onchange="validate_balance(this.id);total_passangers_calculate();" placeholder="Child With Bed(s)"
                        title="Child With Bed(s)" required>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_passangers" name="total_passangers" value="0"
                        placeholder="Total Members" title="Total Members" disabled>
                </div>
            </div>
            <div class="row hidden">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" class="form-control" id="quotation_date" name="quotation_date"
                        placeholder="Quotation Date" title="Quotation Date" value="<?= date('d-m-Y') ?>">
                </div>
            </div>
            <br><br>
            <div class="row mg_tp_10 mg_bt_150 text-center">

                <div class="col-xs-12">

                    <button class="btn btn-info btn-sm ico_right">Next&nbsp;&nbsp;<i
                            class="fa fa-arrow-right"></i></button>

                </div>

            </div>
</form>
<?= end_panel() ?>

<script>
$('#country_code').select2();
$("#customer_name").autocomplete({
    source: JSON.parse($('#cust_data').val()),
    select: function(event, ui) {

        var base_url = $('#base_url').val();
        $("#customer_name").val(ui.item.label);
        $('#mobile_no').val(ui.item.contact_no);
        $('#country_code').val(ui.item.country_id);
		$('#country_code').trigger('change');
        $('#email_id').val(ui.item.email_id);
        
        if(ui.item.type == 'B2B' || ui.item.type == 'Corporate'){

            $.post(base_url+'view/load_data/customer_users_reflect.php', {
                customer_id: ui.item.customer_id
            }, function(data) {
                $('#user_dropdown').removeClass('hidden');
                $('#user_dropdown').html(data);
            });
        } else {
            $('#user_dropdown').html('');
            $('#user_dropdown').addClass('hidden');
        }

    },
    open: function(event, ui) {
        $(this).autocomplete("widget").css({
            "width": document.getElementById("customer_name").offsetWidth
        });
    }
}).data("ui-autocomplete")._renderItem = function(ul, item) {
    return $("<li disabled>")
        .append("<a>" + item.label + "</a>")
        .appendTo(ul);
};
$("#tour_name").autocomplete({

    source: JSON.parse($('#destinations').val()),
    select: function (event, ui) {
		$("#tour_name").val(ui.item.label);
        var newOption = $("<option selected='selected'></option>").val(ui.item.dest_id).text(ui.item.label);
        $('#dest_name').append(newOption).trigger('change.select2');
        // $('#dest_name').prepend('<option value="' + ui.item.dest_id + '">' +ui.item.label +'</option>');
        // $('#dest_name').select2().trigger("change");
        package_dynamic_reflect('dest_name');
    },
    open: function(event, ui) {
		$(this).autocomplete("widget").css({
            "width": document.getElementById("tour_name").offsetWidth
        });
    }
}).data("ui-autocomplete")._renderItem = function(ul, item) {
    	return $("<li disabled>")
        .append("<a>" + item.label +"</a>")
        .appendTo(ul);
	
};

// New Customization ----start
$(document).ready(function() {
    let searchParams = new URLSearchParams(window.location.search);
    if (searchParams.get('enquiry_id')) {
        $('#enquiry_id').val(searchParams.get('enquiry_id'));
        $('#enquiry_id').trigger('change');
    }
});
// New Customization ----end
$('#frm_tab1').validate({

    rules: {
        enquiry_id: {
            required: true
        },
        country_code: {
            required: true
        },
        mobile_no: {
            required: true
        },
    },
    submitHandler: function(form) {
        if ($('#enquiry_id').val() == '0') {

            var from_date = $('#from_date').val();
            $('#train_departure_date').val(from_date + ' 00:00');
            $('#txt_dapart1').val(from_date + ' 00:00');
            $('#cruise_departure_date').val(from_date + ' 00:00');

            $('#train_dept_date_hidde').val(from_date + ' 00:00');
            $('#cruise_dept_date_hidde').val(from_date + ' 00:00');
            $('#plane_dept_date_hidde').val(from_date + ' 00:00');

            $('#train_arrival_date').val(from_date + ' 00:00');
            $('#txt_arrval1').val(from_date + ' 00:00');
            $('#cruise_arrival_date').val(from_date + ' 00:00');
            $('#exc_date-1').val(from_date + ' 00:00');
        }

        var adult = $('#total_adult').val();
        var infant = $('#total_infant').val();
        var chwob = $('#children_without_bed').val();
        var chwb = $('#children_with_bed').val();

        if (adult == '') {
            adult = 0;
        } else {
            adult = parseInt(adult);
        }
        if (infant == '') {
            infant = 0;
        } else {
            infant = parseInt(infant);
        }
        if (chwob == '') {
            chwob = 0;
        } else {
            chwob = parseInt(chwob);
        }
        if (chwb == '') {
            chwb = 0;
        } else {
            chwb = parseInt(chwb);
        }

        if (adult === 0 && infant === 0 && chwob === 0 && chwb === 0) {
            error_msg_alert("Enter atleast adult count!");
            return false;
        }
        $('#tab1_head').addClass('done');
        $('#tab2_head').addClass('active');
        $('.bk_tab').removeClass('active');
        $('#tab2').addClass('active');
        $('html, body').animate({
            scrollTop: $('.bk_tab_head').offset().top
        }, 200);
    }
});
</script>
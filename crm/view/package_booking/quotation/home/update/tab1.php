<form id="frm_tab_u_1">

    <div class="app_panel">



        <div class="container">

            <div class="row">

                <input type="hidden" id="quotation_id1" name="quotation_id1" value="<?= $quotation_id ?>">

                <input type="hidden" id="package_id1" name="package_id1" value="<?= $package_id ?>">

                <div class="col-md-4 col-sm-6 col-xs-12">

                    <select name="enquiry_id12" id="enquiry_id12" style="width:100%"
                        onchange="get_enquiry_details('12')">

                        <?php

						$sq_enq = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master where enquiry_id='$sq_quotation[enquiry_id]' and enquiry_type='Package Booking'"));

						?>

                        <option value="<?= $sq_enq['enquiry_id'] ?>">Enq<?= $sq_enq['enquiry_id'] ?> :
                            <?= $sq_enq['name'] ?></option>
                        <option value="0"><?= "New Enquiry" ?></option>
                        <?php
						if ($role == 'Admin') {
							$sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Package Booking') and status!='Disabled' order by enquiry_id desc");
						} else {
							if ($branch_status == 'yes') {
								if ($role == 'Branch Admin' || $role == 'Accountant' || $role_id > '7') {
									$sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Package Booking') and status!='Disabled' and branch_admin_id='$branch_admin_id' order by enquiry_id desc");
								} elseif ($role != 'Admin' && $role != 'Branch Admin' && $role_id != '7' && $role_id < '7') {
									$q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
									$sq_enq = mysqlQuery($q);
								}
							} elseif ($branch_status != 'yes' && ($role == 'Branch Admin' || $role_id == '7')) {

								$sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Package Booking') and status!='Disabled' order by enquiry_id desc");
							} elseif ($role != 'Admin' && $role != 'Branch Admin' && $role_id != '7' && $role_id < '7') {
								$q = "select * from enquiry_master where enquiry_type in('Package Booking') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
								$sq_enq = mysqlQuery($q);
							}
						}

						while ($row_enq = mysqli_fetch_assoc($sq_enq)) {
							$sq_enq1 = mysqli_fetch_assoc(mysqlQuery("SELECT followup_status FROM `enquiry_master_entries` WHERE `enquiry_id` = '$row_enq[enquiry_id]' ORDER BY `entry_id` DESC"));
							if ($sq_enq1['followup_status'] != 'Dropped') {
						?>
                        <option value="<?= $row_enq['enquiry_id'] ?>">Enq<?= $row_enq['enquiry_id'] ?> :
                            <?= $row_enq['name'] ?></option>
                        <?php }
						} ?>

                    </select>

                </div>

                <div class="col-md-4 col-sm-6 col-xs-12">

                    <input type="text" id="tour_name12" name="tour_name12" placeholder="Tour Name" title="Tour Name"
                        value="<?= $sq_quotation['tour_name'] ?>">

                </div>

                <div class="col-md-4 col-sm-6 col-xs-12">

                    <select name="booking_type2" id="booking_type2" title="Booking Type">

                        <option value="<?= $sq_quotation['booking_type'] ?>"><?= $sq_quotation['booking_type'] ?>
                        </option>

                        <option value="Domestic">Domestic</option>

                        <option value="International">International</option>

                    </select>

                </div>
            </div>
            <div class="row mg_tp_10">

                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">

                    <input type="text" id="customer_name12" name="customer_name12" onchange="validate_customer(this.id)"
                        placeholder="Customer Name" title="Customer Name" value="<?= $sq_quotation['customer_name'] ?>">

                </div>
                <input type="hidden" id="s_user_id" value="<?= $sq_quotation['user_id'] ?> "/>
                <?php if($sq_quotation['user_id'] != 0){
                        $row_user = mysqli_fetch_assoc(mysqlQuery("Select name,user_id from customer_users where user_id =" . $sq_quotation['user_id'])); ?>
                        <div class="col-md-4 col-sm-6 mg_bt_10">
                            <select id="user_id_u" name="user_id_u" title="User" class="form-control">
                                <option value="<?= $row_user['user_id'] ?>"><?= $row_user['name'] ?></option>
                                <option value="">Select User</option>
                            </select>
                        </div>
                <?php } ?>
                <div class="col-md-4 col-sm-6 mg_bt_10">
                    <div class="col-md-4" style="padding-left:0px;">
                        <input type="hidden" id="cc_value" value="<?= $sq_quotation['country_code'] ?>">
                        <select name="country_code1" id="country_code1" title="Country code">
                            <?= get_country_code(); ?>
                        </select>
                    </div>
                    <div class="col-md-8" style="padding-left:12px;padding-right:0px;">
                        <input type="text" class="form-control" id="mobile_no12" onchange="mobile_validate(this.id);"
                            name="mobile_no12" placeholder="WhatsApp No" title="WhatsApp No"
                            value="<?= $sq_quotation['whatsapp_no'] ?>">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="email_id12" name="email_id12" placeholder="Email ID" title="Email ID"
                        value="<?= $sq_quotation['email_id'] ?>">
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="from_date12" name="from_date12" placeholder="From Date" title="From Date"
                        onchange="get_to_date(this.id,'to_date12');total_days_reflect('12');"
                        value="<?= date('d-m-Y', strtotime($sq_quotation['from_date'])) ?>">
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="to_date12" name="to_date12" placeholder="To Date" title="To Date"
                        onchange=" total_days_reflect('12');validate_validDate('from_date12' , 'to_date12');"
                        value="<?= date('d-m-Y', strtotime($sq_quotation['to_date'])) ?>">
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_days12" name="total_days12" placeholder="Total Night(s)" title="Total Night(s)"
                        value="<?= $sq_quotation['total_days'] ?>" disabled>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_adult12" name="total_adult12" placeholder="*Total Adult(s)"
                        title="Total Adult(s)" title="Total Infant"
                        onchange="total_passangers_calculate('12'); validate_balance(this.id)"
                        value="<?= $sq_quotation['total_adult'] ?>">
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_infant12" name="total_infant12" placeholder="*Total Infant(s)"
                        title="Total Infant(s)" onchange="total_passangers_calculate('12'); validate_balance(this.id)"
                        value="<?= $sq_quotation['total_infant'] ?>">
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" class="form-control" id="children_without_bed12" name="children_without_bed12"
                        onchange="validate_balance(this.id);total_passangers_calculate('12');"
                        placeholder="*Child Without Bed(s)" title="Child Without Bed(s)"
                        value="<?= $sq_quotation['children_without_bed'] ?>">
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" class="form-control" id="children_with_bed12" name="children_with_bed12"
                        onchange="validate_balance(this.id);total_passangers_calculate('12');"
                        placeholder="Child With Bed(s)" title="Child With Bed(s)"
                        value="<?= $sq_quotation['children_with_bed'] ?>">
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">
                    <input type="text" id="total_passangers12" name="total_passangers12" placeholder="Total Member(s)"
                        title="Total Member(s)" disabled value="<?= $sq_quotation['total_passangers'] ?>">
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12 mg_bt_10">

                    <input type="text" class="form-control" id="quotation_date" name="quotation_date"
                        placeholder="Quotation Date" title="Quotation Date"
                        value="<?= date('d-m-Y', strtotime($sq_quotation['quotation_date'])) ?>">

                </div>
                <div class="col-md-4 col-sm-6 mg_bt_10">
                    <?php
					$status = ($sq_quotation['status'] == '1') ? 'Active' : 'Inactive';
					?>
                    <select class="<?= $active_inactive_flag ?>" name="active_flag1" id="active_flag1" title="Status">
                        <option value="<?php echo $sq_quotation['status']; ?>"><?php echo $status; ?></option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <br><br>

            <div class="row mg_tp_20 mg_bt_150 text-center">
                <div class="col-xs-12">
                    <button class="btn btn-info btn-sm ico_right">Next&nbsp;&nbsp;<i
                            class="fa fa-arrow-right"></i></button>
                </div>
            </div>
</form>
<?= end_panel() ?>

<script>
$('#enquiry_id12,#country_code1').select2();
$('#country_code1').val($('#cc_value').val()).select2();
$('#frm_tab_u_1').validate({
    rules: {

        enquiry_id12: {
            required: true
        },
        country_code1: {
            required: true
        },
        customer_name12: {
            required: true
        }
    },
    submitHandler: function(form) {

        var adult = $('#total_adult12').val();
        var infant = $('#total_infant12').val();
        var chwob = $('#children_without_bed12').val();
        var chwb = $('#children_with_bed12').val();

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
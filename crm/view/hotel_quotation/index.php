<?php
include "../../model/model.php";
/*======******Header******=======*/
require_once('../layouts/admin_header.php');
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$sq = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='hotel_quotation/index.php'"));
$branch_status = $sq['branch_status'];
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>">
<?= begin_panel('Hotel Quotation') ?>

<!--=======Header panel end======-->

<div class="app_panel_content">
    <div class="row">
        <div class="col-md-12">
            <div id="div_id_proof_content">
                <div class="row mg_bt_20">
                    <div class="col-md-8">
                    </div>
                    <div class="col-md-4 text-right">
                        <form action="save/index.php" method="POST">
                            <button class="btn btn-info btn-sm ico_left" id="quot_save"><i
                                    class="fa fa-plus"></i>&nbsp;&nbsp;Quotation</button>
                        </form>
                    </div>
                </div>

                <div class="app_panel_content Filter-panel">
                    <div class="row">
                        <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_xs">
                            <input type="text" id="from_date_filter" name="from_date_filter" placeholder="From Date"
                                title="From Date"
                                onchange="validate_validDate('from_date_filter','to_date_filter');get_to_date(this.id,'to_date_filter');">
                        </div>
                        <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_xs">
                            <input type="text" id="to_date_filter" name="to_date_filter" placeholder="To Date"
                                title="To Date"
                                onchange="validate_validDate('from_date_filter', 'to_date_filter')">
                        </div>
                        <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_xs">
                            <select name="quotation_id" id="quotation_id" title="Select Quotation" style="width:100%">
                                <option value="">Select Quotation</option>
                                <?php
                                $query = "select * from hotel_quotation_master where 1 and status='1'";
                                if ($role == 'Sales' || $role == 'Backoffice') {
                                    $query .= " and emp_id='$emp_id'";
                                }
                                if ($branch_status == 'yes' && $role != 'Admin') {
                                    $query .= " and branch_admin_id = '$branch_admin_id'";
                                }
                                if ($branch_status == 'yes' && $role == 'Branch Admin') {
                                    $query .= " and branch_admin_id='$branch_admin_id'";
                                }

                                $query .= " order by quotation_id desc";
                                $sq_quotation = mysqlQuery($query);
                                while ($row_quotation = mysqli_fetch_assoc($sq_quotation)) {
                                    $enqDetails = json_decode($row_quotation['enquiry_details']);
                                    $quotation_date = $row_quotation['quotation_date'];
                                    $yr = explode("-", $quotation_date);
                                    $year = $yr[0];
                                ?>
                                <option value="<?= $row_quotation['quotation_id'] ?>">
                                    <?= get_quotation_id($row_quotation['quotation_id'], $year) . ': ' . $enqDetails->customer_name ?>
                                </option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <select name="status" id="status" title="Status" style="width:100%">
                                <option value="">Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mg_tp_10">
                            <select name="financial_year_id_filter" id="financial_year_id_filter" title="Select Financial Year">
                                <?php
                                $sq_fina = mysqli_fetch_assoc(mysqlQuery("select * from financial_year where financial_year_id='$financial_year_id'"));
                                $financial_year = get_date_user($sq_fina['from_date']).'&nbsp;&nbsp;&nbsp;To&nbsp;&nbsp;&nbsp;'.get_date_user($sq_fina['to_date']);
                                ?>
                                <option value="<?= $sq_fina['financial_year_id'] ?>"><?= $financial_year  ?></option>
                                <?php echo get_financial_year_dropdown_filter($financial_year_id); ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12 mg_tp_10">
                            <button class="btn btn-sm btn-info ico_right" onclick="quotation_list_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>

                <div id="div_quotation_list_reflect" class="main_block">
                    <div class="row mg_tp_20">
                        <div class="col-md-12 no-pad">
                            <div class="table-responsive">
                                <table id="hotel_quotation_table" class="table table-hover"
                                    style="width:100%;margin: 20px 0 !important;">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="div_quotation_form"></div>
                <div id="div_quotation_update"></div>
                <div id="div_modal_content"></div>

            </div>
        </div>
    </div>
</div>

<?= end_panel() ?>
<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>
<script>
$('#quotation_id').select2();
$('#from_date_filter,#to_date_filter').datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});

var columns = [{
        title: "S_No"
    },
    {
        title: "Quotation_ID"
    },
    {
        title: "quotation_date"
    },
    {
        title: "customer_name"
    },
    {
        title: "Created_by"
    },
    {
        title: "Actions",
        className: "text-center action_width"
    }
]

function quotation_list_reflect() {
    $('#div_quotation_list_reflect').append('<div class="loader"></div>');
    var from_date = $('#from_date_filter').val();
    var to_date = $('#to_date_filter').val();
    var quotation_id = $('#quotation_id').val();
    var branch_status = $('#branch_status').val();
    var status = $('#status').val();
    var financial_year_id_filter = $('#financial_year_id_filter').val();

    $.post('list_reflect.php', {
        from_date: from_date,
        to_date: to_date,
        quotation_id: quotation_id,
        branch_status: branch_status,
        status: status,
        financial_year_id:financial_year_id_filter
    }, function(data) {
        pagination_load(data, columns, true, false, 20, 'hotel_quotation_table');
        $('.loader').remove();
    })
}
quotation_list_reflect();

function save_modal() {
    var branch_status = $('#branch_status').val();
    $('#quot_save').button('loading');
    $.post('save/index.php', {
        branch_status: branch_status
    }, function(data) {
        $('#div_quotation_form').html(data);
        $('#quot_save').button('reset');
    });
}

function update_modal(quotation_id) {

	$('#edit-'+quotation_id).prop('disabled',true);
    var branch_status = $('#branch_status').val();
	$('#edit-'+quotation_id).button('loading');
    $.post('update/index.php', {
        quotation_id: quotation_id,
        branch_status: branch_status
    }, function(data) {
        $('#div_quotation_form').html(data);
	    $('#edit-'+quotation_id).prop('disabled',false);
	    $('#edit-'+quotation_id).button('reset');
    });
}

function quotation_whatsapp(quotation_id) {
	$('#whatsapp-'+quotation_id).prop('disabled',true);
    var base_url = $('#base_url').val();
	$('#whatsapp-'+quotation_id).button('loading');
    $.post(base_url + 'controller/hotel/quotation/quotation_whatsapp.php', {
        quotation_id: quotation_id
    }, function(data) {
	    $('#whatsapp-'+quotation_id).prop('disabled',false);
	    $('#whatsapp-'+quotation_id).button('reset');
        window.open(data)
    });
}

function send_mail(email_id,quotation_id) {
	$('#email-'+quotation_id).prop('disabled',true);
    var base_url = $('#base_url').val();
    var branch_status = $('#branch_status').val();
	$('#email-'+quotation_id).button('loading');
    $.get(base_url + 'view/hotel_quotation/send_quotation.php', {
        branch_status: branch_status,
        email_id: email_id
    }, function(data) {
        $('#div_quotation_form').html(data);
	    $('#email-'+quotation_id).prop('disabled',false);
	    $('#email-'+quotation_id).button('reset');
    });
}

function quotation_clone(quotation_id) {
    var base_url = $('#base_url').val();
    $.post(base_url + 'controller/hotel/quotation/quotation_clone.php', {
        quotation_id: quotation_id
    }, function(data) {
        msg_alert(data);
        quotation_list_reflect();
    });
}
</script>
<style>
.action_width {
    width: 250px;
    display: flex;
}
</style>
<?php
/*======******Footer******=======*/
require_once('../layouts/admin_footer.php');
?>
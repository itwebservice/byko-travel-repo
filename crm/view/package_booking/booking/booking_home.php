<?php
include "../../../model/model.php";
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$emp_id = $_SESSION['emp_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$q = "select * from branch_assign where link='package_booking/booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>">
<input type="hidden" id="whatsapp_switch" value="<?= $whatsapp_switch ?>">

<div class="row text-right mg_bt_10">
    <div class="col-xs-12">
        <button class="btn btn-excel btn-sm" style="margin-right: 8px;" onclick="excel_report()" data-toggle="tooltip"
            title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
        <form action="booking_save/package_booking_master_save.php" class="no-marg pull-right" method="POST">
            <input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>">
            <button class="btn btn-info btn-sm ico_left"><i
                    class="fa fa-plus"></i>&nbsp;&nbsp;Booking</button>&nbsp;&nbsp;
        </form>

    </div>
</div>
<div class="app_panel_content Filter-panel">
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <select name="cust_type_filter" class="form-control" id="cust_type_filter"
                onchange="dynamic_customer_load(this.value,'company_filter'); company_name_reflect();"
                title="Customer Type" style="width: 100%;">
                <?php get_customer_type_dropdown(); ?>
            </select>
        </div>
        <div id="company_div" class="hidden">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10" id="customer_div">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <select id="booking_id_filter" name="booking_id_filter" style="width:100%" title="Booking ID">
                <option value="">*Select Booking</option>
                <?php
                $query = "select * from package_tour_booking_master where financial_year_id ='$financial_year_id' and delete_status='0'  ";
                include "../../../model/app_settings/branchwise_filteration.php";
                $query .= " order by booking_id desc";
                $sq_booking = mysqlQuery($query);
                while ($row_booking = mysqli_fetch_assoc($sq_booking)) {
                    $date = $row_booking['booking_date'];
                    $yr = explode("-", $date);
                    $year = $yr[0];
                    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_booking[customer_id]'"));
                    if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                ?>
                <option value="<?php echo $row_booking['booking_id'] ?>">
                    <?php echo get_package_booking_id($row_booking['booking_id'], $year) . "-" . " " . $sq_customer['company_name']; ?>
                </option>
                <?php } else { ?>
                <option value="<?php echo $row_booking['booking_id'] ?>">
                    <?php echo get_package_booking_id($row_booking['booking_id'], $year) . "-" . " " . $sq_customer['first_name'] . " " . $sq_customer['last_name']; ?>
                </option>
                <?php
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" id="from_date_filter" name="from_date_filter"
                onchange="get_to_date(this.id,'to_date_filter');" placeholder="From Date" title="From Date">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" id="to_date_filter" onchange="validate_validDate('from_date_filter','to_date_filter');"
                name="to_date_filter" placeholder="To Date" title="To Date">
        </div>
        <div class="col-md-3 col-sm-6">
            <select name="financial_year_id_filter" id="financial_year_id_filter" title="Select Financial Year">
                <?php
                $sq_fina = mysqli_fetch_assoc(mysqlQuery("select * from financial_year where financial_year_id='$financial_year_id'"));
                $financial_year = get_date_user($sq_fina['from_date']).'&nbsp;&nbsp;&nbsp;To&nbsp;&nbsp;&nbsp;'.get_date_user($sq_fina['to_date']);
                ?>
                <option value="<?= $sq_fina['financial_year_id'] ?>"><?= $financial_year  ?></option>
                <?php echo get_financial_year_dropdown_filter($financial_year_id); ?>
            </select>
        </div>
        <div class="col-md-3 col-sm-12 col-xs-12 mg_bt_10">
            <button class="btn btn-sm btn-info ico_right" onclick="list_reflect()">Proceed&nbsp;&nbsp;<i
                    class="fa fa-arrow-right"></i></button>
        </div>
    </div>
</div>

<div id="div_list" class="main_block loader_parent mg_tp_10">
    <div class="table-responsive mg_tp_10">
        <table id="packageb_table" class="table table-hover" style="margin: 20px 0 !important;">
        </table>
    </div>
</div>

<div id="div_package_content_display"></div>
<div id="voucher_modal"></div>
<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>
<script>
$('#from_date_filter, #to_date_filter').datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});
$('#booking_id_filter,#customer_id_filter,#cust_type_filter').select2();
dynamic_customer_load('', '');

var columns = [{
        title: "INVOICE_NO"
    },
    {
        title: "Booking_ID"
    },
    {
        title: "Customer_Name"
    },
    {
        title: "Tour"
    },
    {
        title: "Amount",
        className: "info"
    },
    {
        title: "CNCL_Amount",
        className: "danger"
    },
    {
        title: "Total",
        className: "success"
    },
    {
        title: "Created_by "
    },
    {
        title: "Booking_Date"
    },
    {
        title: "Actions",
        className: "text-center action_width",
        "bSortable": false
    }
];

function list_reflect() {
    $('#div_list').append('<div class="loader"></div>');
    var customer_id = $('#customer_id_filter').val();
    var booking_id = $('#booking_id_filter').val();
    var from_date = $('#from_date_filter').val();
    var to_date = $('#to_date_filter').val();
    var cust_type = $('#cust_type_filter').val();
    var company_name = $('#company_filter').val();
    var branch_status = $('#branch_status').val();
    var financial_year_id_filter = $('#financial_year_id_filter').val();

    $.post('list_reflect.php', {
        customer_id: customer_id,
        booking_id: booking_id,
        from_date: from_date,
        to_date: to_date,
        cust_type: cust_type,
        company_name: company_name,
        branch_status: branch_status,
        financial_year_id:financial_year_id_filter
    }, function(data) {
        pagination_load(data, columns, true, true, 20, 'packageb_table', true);
        $('.loader').remove();
    });
}
list_reflect();
$(document).ready(function() {
    $("[data-toggle='tooltip']").tooltip({
        placement: 'bottom'
    });
    $("[data-toggle='tooltip']").click(function() {
        $('.tooltip').remove()
    })
});

function generate_booking_pdfs(offset, booking_id) {

    var pdf_type = $('#booking_pdf' + offset).val();

    if (pdf_type == "Booking Form") {
        var url = 'pdf/registration_form_pdf.php?booking_id=' + booking_id;
    }
    window.open(url);

}

function customer_booking_dropdown_load() {
    var customer_id = $('#customer_id_filter').val();
    var branch_status = $('#branch_status').val();
    $.post('inc/customer_booking_dropdown_load.php', {
        customer_id: customer_id,
        branch_status: branch_status
    }, function(data) {
        $('#booking_id_filter').html(data);
    });
}

function excel_report() {
    var customer_id = $('#customer_id_filter').val();
    var booking_id = $('#booking_id_filter').val();
    var from_date = $('#from_date_filter').val();
    var to_date = $('#to_date_filter').val();
    var cust_type = $('#cust_type_filter').val();
    var company_name = $('#company_filter').val();
    var branch_status = $('#branch_status').val();
    window.location = 'excel_report.php?booking_id=' + booking_id + '&from_date=' + from_date + '&to_date=' + to_date +
        '&customer_id=' + customer_id + '&cust_type=' + cust_type + '&company_name=' + company_name +
        '&branch_status=' + branch_status;
}

function company_name_reflect() {
    var cust_type = $('#cust_type_filter').val();
    var branch_status = $('#branch_status').val();
    $.post('company_name_load.php', {
        cust_type: cust_type,
        branch_status: branch_status
    }, function(data) {
        if (cust_type == 'Corporate' || cust_type == 'B2B') {
            $('#company_div').addClass('company_class');
        } else {
            $('#company_div').removeClass('company_class');
        }
        $('#company_div').html(data);

    });
}

function package_view_modal(booking_id) {
	$('#package_view_modal_btn-'+booking_id).prop('disabled',true);
	$('#package_view_modal_btn-'+booking_id).button('loading');
    $.post('view/index.php', {
        booking_id: booking_id
    }, function(data) {
        $('#div_package_content_display').html(data);
        $('#package_view_modal_btn-'+booking_id).prop('disabled',false);
        $('#package_view_modal_btn-'+booking_id).button('reset');
    });
}
//*******************Get Dynamic Customer Name Dropdown**********************//

function dynamic_customer_load(cust_type, company_name) {
    var cust_type = $('#cust_type_filter').val();
    var company_name = $('#company_filter').val();
    var branch_status = $('#branch_status').val();
    $.get("inc/get_customer_dropdown.php", {
        cust_type: cust_type,
        company_name: company_name,
        branch_status: branch_status
    }, function(data) {
        $('#customer_div').html(data);
    });
}

function voucher_modal(booking_id) {

	$('#servoucher_btn-'+booking_id).prop('disabled',true);
	$('#servoucher_btn-'+booking_id).button('loading');
    $.get("voucher_modal.php", {
        booking_id: booking_id
    }, function(data) {
        $('#voucher_modal').html(data);
        $('#servoucher_btn-'+booking_id).prop('disabled',false);
        $('#servoucher_btn-'+booking_id).button('reset');
    });
}

function delete_entry(booking_id) {
    $('#vi_confirm_box').vi_confirm_box({
        callback: function(data1) {
            if (data1 == "yes") {
                var branch_status = $('#branch_status').val();
                var base_url = $('#base_url').val();
                $.post(base_url + 'controller/package_tour/booking/package_tour_delete.php', {
                    booking_id: booking_id
                }, function(data) {
                    success_msg_alert(data);
                    list_reflect();
                });
            }
        }
    });
}
</script>
<style>
.action_width {
    display: flex;
}
</style>
<?= end_panel() ?>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
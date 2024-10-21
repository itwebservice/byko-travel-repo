<?php
include "../../../../model/model.php";
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$emp_id = $_SESSION['emp_id'];
$branch_status = $_POST['branch_status'];
?>
<div id="markup_confirm"></div>
<input type="hidden" id="whatsapp_switch" value="<?= $whatsapp_switch ?>">
<div class="row text-right mg_bt_20">
    <div class="col-xs-12">
        <button class="btn btn-excel btn-sm" onclick="excel_report()" data-toggle="tooltip" title="Generate Excel"><i
                class="fa fa-file-excel-o"></i></button>
        <!-- <button class="btn btn-info btn-sm ico_left" onclick="save_modal_airfile()" id="pnr_invoice_btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;PNR Invoice</button> -->
        <form action="home/save/index.php" class="no-marg pull-right" method="POST">
            <input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>">
            &nbsp;&nbsp;<button class="btn btn-info btn-sm ico_left" id="btn_save_modal"><i
                    class="fa fa-plus"></i>&nbsp;&nbsp;Ticket</button>&nbsp;&nbsp;
        </form>
    </div>
</div>

<div class="app_panel_content Filter-panel">
    <div class="row">
        <input type="hidden" value="<?= $emp_id ?>" id="emp_id" />
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <select name="cust_type_filter" id="cust_type_filter" style="width:100%"
                onchange="dynamic_customer_load(this.value,'company_filter');company_name_reflect();"
                title="Customer Type">
                <?php get_customer_type_dropdown(); ?>
            </select>
        </div>
        <div id="company_div" class="hidden">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10" id="customer_div">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <select name="ticket_id_filter" id="ticket_id_filter" style="width:100%" title="Booking ID">
                <option value="">Booking ID</option>
                <?php
				$query = "select * from ticket_master where 1 and delete_status='0' ";
				include "../../../../model/app_settings/branchwise_filteration.php";
				$query .= "and financial_year_id = '" . $_SESSION['financial_year_id'] . "' order by ticket_id desc ";
				$sq_ticket = mysqlQuery($query);
				while ($row_ticket = mysqli_fetch_assoc($sq_ticket)) {

					$date = $row_ticket['created_at'];
					$yr = explode("-", $date);
					$year = $yr[0];
					$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
					if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
						$customer_name = $sq_customer['company_name'];
					} else {
						$customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
					}
				?>
                <option value="<?= $row_ticket['ticket_id'] ?>">
                    <?= get_ticket_booking_id($row_ticket['ticket_id'], $year) . ' : ' . $customer_name ?>
                </option>
                <?php
				}
				?>
            </select>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" id="from_date" name="from_date" class="form-control" placeholder="From Date"
                title="From Date" onchange="get_to_date(this.id,'to_date');">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" id="to_date" name="to_date" class="form-control" placeholder="To Date" title="To Date"
                onchange="validate_validDate('from_date','to_date')">
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
        <div class="col-md-2 col-sm-6 col-xs-12 mg_bt_10">
            <button class="btn btn-sm btn-info ico_right" onclick="ticket_customer_list_reflect()">Proceed&nbsp;&nbsp;<i
                    class="fa fa-arrow-right"></i></button>
        </div>
    </div>
</div>

<div id="div_ticket_customer_list_reflect" class="main_block loader_parent mg_tp_10">
    <div class="table-responsive mg_tp_10">
        <table id="flight_book" class="table table-hover" style="margin: 20px 0 !important;">
        </table>
    </div>
</div>

<div id="div_ticket_modal"></div>

<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>
<script>
$('#customer_id_filter, #ticket_id_filter,#cust_type_filter').select2();
$('#from_date, #to_date').datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});
dynamic_customer_load('', '');

function business_rule_load() {
    alert('alert');
    get_auto_values('booking_date', 'basic_cost', 'payment_mode', 'service_charge', 'markup', 'save', 'true', 'basic',
        'discount');
}

function calculate_total_amount(id) {

    var adult_fair = $('#adult_fair').val();
    var children_fair = $('#children_fair').val();
    var infant_fair = $('#infant_fair').val();

    var adults = $('#adults').val();
    var childrens = $('#childrens').val();
    var infant = $('#infant').val();

    if (adult_fair == "") {
        adult_fair = 0;
    }
    if (children_fair == "") {
        children_fair = 0;
    }
    if (infant_fair == "") {
        infant_fair = 0;
    }

    var basic_cost = parseFloat(adult_fair) + parseFloat(children_fair) + parseFloat(infant_fair);

    if (id != 'basic_cost') {
        $('#basic_cost').val(basic_cost);
        $('#basic_cost').trigger('change');
    }

    var markup = $('#markup').val();
    var discount = $('#discount').val();
    var yq_tax = $('#yq_tax').val();
    var other_taxes = $('#other_taxes').val();
    var service_charge = $('#service_charge').val();
    var service_tax_subtotal = $('#service_tax_subtotal').val();
    var service_tax_markup = $('#service_tax_markup').val();
    var tds = $('#tds').val();

    if (markup == "") {
        markup = 0;
    }
    if (discount == "") {
        discount = 0;
    }
    if (yq_tax == "") {
        yq_tax = 0;
    }
    if (other_taxes == "") {
        other_taxes = 0;
    }
    if (tds == "") {
        tds = 0;
    }
    if (basic_cost == "") {
        basic_cost = 0;
    }

    if (adults == 0) {
        if ($('#adult_fair').val() == '') {
            $('#adult_fair').val(0);
        }
        $('#adult_fair').prop('readonly', true);
    } else {
        $('#adult_fair').prop('disabled', false);
        $('#adult_fair').prop('readonly', false);
    }

    if (childrens == 0) {
        if ($('#children_fair').val() == '') {
            $('#children_fair').val(0);
        }
        $('#children_fair').prop('readonly', true);
    } else {
        $('#children_fair').prop('disabled', false);
        $('#children_fair').prop('readonly', false);
    }

    if (infant == 0) {
        $('#infant_fair').val(0);
        $('#infant_fair').prop('readonly', true);
    } else {
        $('#infant_fair').prop('disabled', false);
        $('#infant_fair').prop('readonly', false);
    }

    var service_tax_amount = 0;
    if (parseFloat(service_tax_subtotal) !== 0.00 && (service_tax_subtotal) !== '') {
        var service_tax_subtotal1 = service_tax_subtotal.split(",");
        for (var i = 0; i < service_tax_subtotal1.length; i++) {
            var service_tax = service_tax_subtotal1[i].split(':');
            service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
        }
    }

    var markupservice_tax_amount = 0;
    if (parseFloat(service_tax_markup) !== 0.00 && (service_tax_markup) !== "") {
        var service_tax_markup1 = service_tax_markup.split(",");
        for (var i = 0; i < service_tax_markup1.length; i++) {
            var service_tax = service_tax_markup1[i].split(':');
            markupservice_tax_amount = parseFloat(markupservice_tax_amount) + parseFloat(service_tax[2]);
        }
    }

    basic_cost = ($('#basic_show').html() == '&nbsp;') ? basic_cost : parseFloat($('#basic_show').text().split(' : ')[
        1]);
    service_charge = ($('#service_show').html() == '&nbsp;') ? service_charge : parseFloat($('#service_show').text()
        .split(' : ')[1]);
    markup = ($('#markup_show').html() == '&nbsp;') ? markup : parseFloat($('#markup_show').text().split(' : ')[1]);
    discount = ($('#discount_show').html() == '&nbsp;') ? discount : parseFloat($('#discount_show').text().split(' : ')[
        1]);

    var ticket_total_cost = parseFloat(basic_cost) + parseFloat(markup) + parseFloat(markupservice_tax_amount) -
        parseFloat(discount) + parseFloat(yq_tax) + parseFloat(other_taxes) + parseFloat(service_charge) + parseFloat(
            service_tax_amount) - parseFloat(tds);


    ticket_total_cost = ticket_total_cost.toFixed(2);
    var roundoff = Math.round(ticket_total_cost) - ticket_total_cost;
    $('#roundoff').val(roundoff.toFixed(2));
    $('#ticket_total_cost').val(parseFloat(ticket_total_cost) + parseFloat(roundoff));
    $('#ticket_total_cost').trigger('change');

}
var columns = [{
        title: "Invoice_No"
    },
    {
        title: "Booking_id"
    },
    {
        title: "Customer_Name"
    },
    {
        title: "Mobile"
    },
    {
        title: "Amount",
        className: "text-right info"
    },
    {
        title: "Cncl_Amount",
        className: "text-right danger"
    },
    {
        title: "Total",
        className: "text-right success"
    },
    {
        title: "Created_By"
    },
    {
        title: "Booking_Date"
    },
    {
        title: "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Actions&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",
        className: "text-center action-width"
    },
];

function ticket_customer_list_reflect() {
    $('#div_ticket_customer_list_reflect').append('<div class="loader"></div>');
    var customer_id = $('#customer_id_filter').val()
    var from_date = $('#from_date').val();
    var to_date = $('#to_date').val();
    var ticket_id_filter = $('#ticket_id_filter').val();
    var cust_type = $('#cust_type_filter').val();
    var company_name = $('#company_filter').val();
    var branch_status = $('#branch_status').val();
    var base_url = $('#base_url').val();
    var financial_year_id_filter = $('#financial_year_id_filter').val();

    $.post(base_url + 'view/visa_passport_ticket/ticket/home/ticket_list_reflect.php', {
        customer_id: customer_id,
        ticket_id_filter: ticket_id_filter,
        from_date: from_date,
        to_date: to_date,
        cust_type: cust_type,
        company_name: company_name,
        branch_status: branch_status,financial_year_id:financial_year_id_filter
    }, function(data) {
        pagination_load(data, columns, true, true, 20, 'flight_book', true);
        $('.loader').remove();
    });
}
ticket_customer_list_reflect();

function excel_report() {
    var customer_id = $('#customer_id_filter').val()
    var from_date = $('#from_date').val();
    var to_date = $('#to_date').val();
    var ticket_id = $('#ticket_id_filter').val();
    var cust_type = $('#cust_type_filter').val();
    var company_name = $('#company_filter').val();
    var branch_status = $('#branch_status').val();
    var base_url = $('#base_url').val();
    window.location = base_url + 'view/visa_passport_ticket/ticket/home/excel_report.php?customer_id=' + customer_id +
        '&ticket_id=' + ticket_id + '&from_date=' + from_date + '&to_date=' + to_date + '&cust_type=' + cust_type +
        '&company_name=' + company_name + '&branch_status=' + branch_status;
}

function delete_entry(booking_id) {

    $('#vi_confirm_box').vi_confirm_box({
        callback: function(data1) {
            if (data1 == "yes") {
                var branch_status = $('#branch_status').val();
                var base_url = $('#base_url').val();
                $.post(base_url + 'controller/visa_passport_ticket/ticket/ticket_master_delete.php', {
                    booking_id: booking_id
                }, function(data) {
                    success_msg_alert(data);
                    ticket_customer_list_reflect();
                });
            }
        }
    });
}

function save_modal_airfile() {
    var base_url = $('#base_url').val();
    var branch_status = $('#branch_status').val();
    $('#pnr_invoice_btn').button('loading');
    $.post(base_url + 'view/visa_passport_ticket/ticket/home/pnr_invoice/index.php', {
        branch_status: branch_status
    }, function(data) {
        $('#div_ticket_modal').html(data);
        $('#pnr_invoice_btn').button('reset');
    });
}

function ticket_update_modal(ticket_id) {
    $('#update_ticket-'+ticket_id).prop('disabled',true);
    $('#update_ticket-'+ticket_id).button('loading');
    var branch_status = $('#branch_status').val();
    $.post(base_url + 'view/visa_passport_ticket/ticket/home/update/index.php', {
        ticket_id: ticket_id,
        branch_status: branch_status
    }, function(data) {
        $('#div_ticket_modal').html(data);
        $('#update_ticket-'+ticket_id).prop('disabled',false);
        $('#update_ticket-'+ticket_id).button('reset');
    });
}

function ticket_display_modal(ticket_id) {
    $('#display_ticket-'+ticket_id).prop('disabled',true);
    $('#display_ticket-'+ticket_id).button('loading');
    var base_url = $('#base_url').val();
    $.post(base_url + 'view/visa_passport_ticket/ticket/home/view/index.php', {
        ticket_id: ticket_id
    }, function(data) {
        $('#div_ticket_modal').html(data);
        $('#display_ticket-'+ticket_id).prop('disabled',false);
        $('#display_ticket-'+ticket_id).button('reset');
    });
}
</script>
<style>
.action-width {
    display: flex;
    text-align: left;
}
</style>
<script src="../js/ticket_calculation.js"></script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
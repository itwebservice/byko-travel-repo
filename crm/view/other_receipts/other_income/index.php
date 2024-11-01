<?php
include "../../../model/model.php";
$financial_year_id = $_SESSION['financial_year_id'];
?>

<div class="row text-right mg_bt_20">
    <div class="col-xs-12">
        <button class="btn btn-excel btn-sm" onclick="excel_report()" data-toggle="tooltip" title="Generate Excel"><i
                class="fa fa-file-excel-o"></i></button>
        <button class="btn btn-info ico_left btn-sm" id="btn_new_income" onclick="income_save_modal()"><i
                class="fa fa-plus"></i>&nbsp;&nbsp;Income</button>
    </div>
</div>

<div class="app_panel_content Filter-panel">
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <select id="income_type_id_filter" name="income_type_id_filter" title="Income Type" style="width: 100%"
                class="form-control">
                <option value="">Income Type</option>
                <?php
				$sq_expense = mysqlQuery("select * from ledger_master where group_sub_id in ('63','41','86','52','5','50','6')");
				while ($row_expense = mysqli_fetch_assoc($sq_expense)) {
				?>
                <option value="<?= $row_expense['ledger_id'] ?>"><?= $row_expense['ledger_name'] ?></option>
                <?php
				}
				?>
            </select>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" name="from_date_filter" id="from_date_filter" placeholder="From Date" title="From Date"
                class="form-control" onchange="get_to_date(this.id,'to_date_filter');">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
            <input type="text" name="to_date_filter" id="to_date_filter" placeholder="To Date" title="To Date"
                class="form-control" onchange="validate_validDate('from_date_filter','to_date_filter');">
        </div>
        <div class="col-md-3 col-sm-6 mg_bt_10">
            <select name="financial_year_id_filter" id="financial_year_id_filter" title="Select Financial Year" class="form-control">
                <?php
                $sq_fina = mysqli_fetch_assoc(mysqlQuery("select * from financial_year where financial_year_id='$financial_year_id'"));
                $financial_year = get_date_user($sq_fina['from_date']).'&nbsp;&nbsp;&nbsp;To&nbsp;&nbsp;&nbsp;'.get_date_user($sq_fina['to_date']);
                ?>
                <option value="<?= $sq_fina['financial_year_id'] ?>"><?= $financial_year  ?></option>
                <?php echo get_financial_year_dropdown_filter($financial_year_id); ?>
            </select>
        </div>
        <div class="col-md-3 col-xs-12">
            <button class="btn btn-sm btn-info ico_right" onclick="income_list_reflect()">Proceed&nbsp;&nbsp;<i
                    class="fa fa-arrow-right"></i></button>
        </div>
    </div>
</div>

<div id="div_list" class="main_block loader_parent mg_tp_20">
    <div class="table-responsive">
        <table id="other_r_book" class="table table-hover" style="margin: 20px 0 !important;">
        </table>
    </div>
</div>

<div id="div_crud_content"></div>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>

<script type="text/javascript">
$('#income_type_id_filter').select2();
$('#from_date_filter, #to_date_filter').datetimepicker({
    timepicker: false,
    format: 'd-m-Y'
});

var columns = [{
        title: "S_No"
    },{
        title: "INVOICE_No"
    },
    {
        title: "Income_type"
    },
    {
        title: "Receipt_from"
    },
    {
        title: "receipt_date"
    },
    {
        title: "Mode"
    },
    {
        title: "narration"
    },
    {
        title: "paid_amount",
        className: "success"
    },
    {
        title: "Actions",
        className: "text-center action_width"
    }
];

function income_save_modal() {
    $('#btn_new_income').button('loading');
    $.post('other_income/save_modal.php', {}, function(data) {
        $('#div_crud_content').html(data);
        $('#btn_new_income').button('reset');
    });
}

function income_list_reflect() {
    $('#div_list').append('<div class="loader"></div>');
    var from_date = $('#from_date_filter').val();
    var to_date = $('#to_date_filter').val();
    var income_type_id = $('#income_type_id_filter').val();
    var financial_year_id = $('#financial_year_id_filter').val();

    $.post('other_income/list_reflect.php', {
        from_date: from_date,
        to_date: to_date,
        income_type_id: income_type_id,
        financial_year_id: financial_year_id
    }, function(data) {
        // $('#div_list').html(data);
        pagination_load(data, columns, true, true, 10, 'other_r_book');
        $('.loader').remove();
    });
}

income_list_reflect();
$(document).ready(function() {
    $("[data-toggle='tooltip']").tooltip({
        placement: 'bottom'
    });
    $("[data-toggle='tooltip']").click(function() {
        $('.tooltip').remove()
    })
});

function update_income_modal(payment_id) {

    $('#updateo_btn-'+payment_id).prop('disabled',true);
    $('#updateo_btn-'+payment_id).button('loading');
    $.post('other_income/update_modal.php', {
        payment_id: payment_id
    }, function(data) {
        $('#div_crud_content').html(data);
        $('#updateo_btn-'+payment_id).prop('disabled',false);
        $('#updateo_btn-'+payment_id).button('reset');
    });
}

function excel_report() {
    var from_date = $('#from_date_filter').val();
    var to_date = $('#to_date_filter').val();
    var income_type_id = $('#income_type_id_filter').val();
    var financial_year_id = $('#financial_year_id_filter').val();

    window.location = 'other_income/excel_report.php?income_type_id=' + income_type_id + '&from_date=' + from_date +
        '&to_date=' + to_date + '&financial_year_id=' + financial_year_id;
}

function entry_display_modal(entry_id) {
    $('#viewo_btn-'+entry_id).prop('disabled',true);
    $('#viewo_btn-'+entry_id).button('loading');
    var base_url = $('#base_url').val();
    $.post('other_income/income_details.php', {
        income_type_id: entry_id
    }, function(data) {
        $('#div_crud_content').html(data);
        $('#viewo_btn-'+entry_id).prop('disabled',false);
        $('#viewo_btn-'+entry_id).button('reset');
    });
}

function delete_entry(entry_id) {
    $('#vi_confirm_box').vi_confirm_box({
        callback: function(data1) {
            if (data1 == "yes") {
                var branch_status = $('#branch_status').val();
                var base_url = $('#base_url').val();
                $.post(base_url + 'controller/tour_estimate/other_income/income_delete.php', {
                    entry_id: entry_id
                }, function(data) {
                    success_msg_alert(data);
                    income_list_reflect();
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
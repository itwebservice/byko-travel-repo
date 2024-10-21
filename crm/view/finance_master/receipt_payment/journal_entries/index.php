<?php
include "../../../../model/model.php";
$financial_year_id = $_SESSION['financial_year_id'];
/*======******Header******=======*/
// require_once('../../layouts/admin_header.php');
?>
<div class="row text-right mg_tp_20 mg_bt_20">
	<div class="col-md-12">
		<button class="btn btn-excel btn-sm" onclick="excel_report()" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>&nbsp;&nbsp;
		<button class="btn btn-info btn-sm ico_left" onclick="save_modal()" id="btn_jv"><i class="fa fa-plus"></i>&nbsp;&nbsp;Journal Entry</button>
	</div>
</div>
<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-3 col-sm-6 col-xs-12">
			<input type="text" id="payment_from_date_filter" name="payment_from_date_filter" placeholder="From Date" title="From Date" onchange="get_to_date(this.id,'payment_to_date_filter')" class="form-control">
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<input type="text" id="payment_to_date_filter" name="payment_to_date_filter" placeholder="To Date" title="To Date" onchange="validate_validDate('payment_from_date_filter','payment_to_date_filter')" class="form-control">
		</div>
		<div class="col-md-3 col-sm-6">
			<select name="financial_year_id_filter" id="financial_year_id_filter" title="Select Financial Year" class="form-control">
				<?php
				$sq_fina = mysqli_fetch_assoc(mysqlQuery("select * from financial_year where financial_year_id='$financial_year_id'"));
				$financial_year = get_date_user($sq_fina['from_date']) . '&nbsp;&nbsp;&nbsp;To&nbsp;&nbsp;&nbsp;' . get_date_user($sq_fina['to_date']);
				?>
				<option value="<?= $sq_fina['financial_year_id'] ?>"><?= $financial_year  ?></option>
				<?php echo get_financial_year_dropdown_filter($financial_year_id); ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<button class="btn btn-sm btn-info ico_right" onclick="list_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
		</div>
	</div>
</div>

<div id="div_modal" class="main_block"></div>

<div id="div_list_content" class="main_block loader_parent mg_tp_20">
	<div class="table-responsive">
		<table id="tbl_list" class="table table-hover" style="margin: 20px 0 !important;">
		</table>
	</div>
</div>

<div id="journal_modal_display"></div>

<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>
<script>
	$('#payment_from_date_filter, #payment_to_date_filter').datetimepicker({
		timepicker: false,
		format: 'd-m-Y'
	});
	var columns = [{
			title: "S_No."
		},
		{
			title: "Transaction_ID"
		},
		{
			title: "Date"
		},
		{
			title: "Particulars"
		},
		{
			title: "dr_cr"
		},
		{
			title: "Narration"
		},
		{
			title: "Debit_Amount",
			className: 'success text-right'
		},
		{
			title: "Actions",
			className: "text-center nowrap"
		}
	]

	function list_reflect() {
		$('#div_list_content').append('<div class="loader"></div>');
		var from_date = $('#payment_from_date_filter').val();
		var to_date = $('#payment_to_date_filter').val();
		var financial_year_id = $('#financial_year_id_filter').val();
		$.post('journal_entries/list_reflect.php', {
			from_date: from_date,
			to_date: to_date,
			financial_year_id: financial_year_id
		}, function(data) {
			pagination_load(data, columns, false, true, 20, 'tbl_list');
			$('.loader').remove();
		});
	}
	list_reflect();

	function save_modal() {
		$('#btn_jv').button('loading');
		$.post('journal_entries/save_modal.php', {}, function(data) {
			$('#btn_jv').button('reset');
			$('#div_modal').html(data);
		});
	}

	function update_modal(entry_id) {
		$('#editj-' + entry_id).button('loading');
		$('#editj-' + entry_id).prop('disabled', true);
		$.post('journal_entries/update_modal.php', {
			entry_id: entry_id
		}, function(data) {
			$('#div_modal').html(data);
			$('#editj-' + entry_id).button('reset');
			$('#editj-' + entry_id).prop('disabled', false);
		});
	}

	function excel_report() {
		var from_date = $('#payment_from_date_filter').val();
		var to_date = $('#payment_to_date_filter').val();

		window.location = 'journal_entries/excel_report.php?from_date=' + from_date + '&to_date=' + to_date;
	}

	function entry_display_modal(entry_id) {
		$('#view-' + entry_id).button('loading');
		$('#view-' + entry_id).prop('disabled', true);
		var base_url = $('#base_url').val();
		$.post(base_url + 'view/finance_master/receipt_payment/journal_entries/view/index.php', {
			entry_id: entry_id
		}, function(data) {
			$('#journal_modal_display').html(data);
			$('#view-' + entry_id).button('reset');
			$('#view-' + entry_id).prop('disabled', false);
		});
	}

	function delete_entry(entry_id) {
		$('#vi_confirm_box').vi_confirm_box({
			callback: function(data1) {
				if (data1 == "yes") {
					var branch_status = $('#branch_status').val();
					var base_url = $('#base_url').val();
					$.post(base_url + 'controller/finance_master/journal_entry/journal_master_delete.php', {
						entry_id: entry_id
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
	.nowrap {
		white-space: nowrap;
	}
</style>
<?php
/*======******Footer******=======*/
// require_once('../../layouts/admin_footer.php'); 
?>
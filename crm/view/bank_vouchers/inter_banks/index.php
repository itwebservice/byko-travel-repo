<?php
include "../../../model/model.php";
$role= $_SESSION['role'];
$emp_id= $_SESSION['emp_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$q = "select * from branch_assign where link='bank_vouchers/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >
<div class="row text-right mg_bt_20">
	<div class="col-xs-12">
		<button class="btn btn-excel btn-sm" onclick="excel_report()" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
		<button class="btn btn-info ico_left btn-sm" id="btn_new_cash" onclick="inter_banks_modal()"><i class="fa fa-plus"></i>&nbsp;&nbsp;Inter Bank Transfers</button>
	</div>
</div>

<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
			<select id="bank_id_filter" name="bank_id_filter" style="width:100%" title="Creditor Bank" class="form-control">
			<?php get_bank_dropdown(); ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
			<select id="bank_id_filter1" name="bank_id_filter1" style="width:100%" title="Debitor Bank" class="form-control">
			<?php get_bank_dropdown('Debitor Bank'); ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
			<input type="text" name="from_date_filter" id="from_date_filter" placeholder="From Date" title="From Date" class="form-control" onchange="get_to_date(this.id,'to_date_filter');" >
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
			<input type="text" name="to_date_filter" id="to_date_filter" placeholder="To Date" title="To Date" class="form-control" onchange="validate_validDate('from_date_filter','to_date_filter')">
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
			<select name="financial_year_id_filter" id="financial_year_id_filter" title="Financial Year" class="form-control">
				<?php
				$sq_fina = mysqli_fetch_assoc(mysqlQuery("select * from financial_year where financial_year_id='$financial_year_id'"));
				$financial_year = get_date_user($sq_fina['from_date']).'&nbsp;&nbsp;&nbsp;To&nbsp;&nbsp;&nbsp;'.get_date_user($sq_fina['to_date']);
				?>
				<option value="<?= $sq_fina['financial_year_id'] ?>"><?= $financial_year  ?></option>
				<?php echo get_financial_year_dropdown_filter($financial_year_id); ?>
			</select>
		</div>
		<div class="col-xs-3">
			<button class="btn btn-sm btn-info ico_right" onclick="list_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
		</div>
	</div>
</div>


<div id="div_list" class="main_block"></div>

<div id="div_crud_content"></div>

<script type="text/javascript">
$('#bank_id_filter').select2();
$('#from_date_filter, #to_date_filter').datetimepicker({ timepicker:false, format:'d-m-Y' });

function inter_banks_modal()
{
	var branch_status = $('#branch_status').val();
	$('#btn_new_cash').button('loading');
	$.post('inter_banks/save_modal.php',{branch_status : branch_status}, function(data){
		$('#div_crud_content').html(data);
		$('#btn_new_cash').button('reset');
	});
}
function list_reflect()
{
	var from_date = $('#from_date_filter').val();
	var to_date = $('#to_date_filter').val();
	var bank_id = $('#bank_id_filter').val();
	var dbank_id = $('#bank_id_filter1').val();
	var financial_year_id = $('#financial_year_id_filter').val();
	var branch_status = $('#branch_status').val();
	$.post('inter_banks/list_reflect.php',{ from_date : from_date, to_date : to_date, bank_id : bank_id,dbank_id:dbank_id, financial_year_id : financial_year_id , branch_status : branch_status}, function(data){
		$('#div_list').html(data);
	});
}
list_reflect();

function update_bank_modal(entry_id)
{
	$('#iedit-'+entry_id).button('loading');
	$('#iedit-'+entry_id).prop('disabled',true);
	var branch_status = $('#branch_status').val();
	$.post('inter_banks/update_modal.php',{ entry_id : entry_id , branch_status : branch_status}, function(data){
		$('#div_crud_content').html(data);
		$('#iedit-'+entry_id).button('reset');
		$('#iedit-'+entry_id).prop('disabled',false);
	});
}

function excel_report()
{
    var from_date = $('#from_date_filter').val();
	var to_date = $('#to_date_filter').val();
	var bank_id = $('#bank_id_filter').val();
	var dbank_id = $('#bank_id_filter1').val();
	var financial_year_id = $('#financial_year_id_filter').val();
    var branch_status = $('#branch_status').val();
    window.location = 'inter_banks/excel_report.php?bank_id='+bank_id+'&dbank_id='+dbank_id+'&from_date='+from_date+'&to_date='+to_date+'&financial_year_id='+financial_year_id+'&branch_status='+branch_status;
}

function get_lapse_date(trans_type,ins_date,offset='')
{
	var trans_typeq = $('#'+trans_type).val();
	var ins_dateq = $('#'+ins_date).val();
	$.post('inter_banks/get_lapse_date.php',{ trans_type : trans_typeq, ins_date : ins_dateq}, function(data){
		$('#lapse_date'+offset).val(data);
	});
}
function inter_delete_entry(entry_id)
{
	$('#vi_confirm_box').vi_confirm_box({
		callback: function(data1){
			if(data1=="yes"){
				var branch_status = $('#branch_status').val();
				var base_url = $('#base_url').val();
				$.post(base_url+'controller/bank_vouchers/bank_transfer_delete.php',{ entry_id : entry_id }, function(data){
					success_msg_alert(data);
					list_reflect();
				});
			}
		}
	});
}

</script>
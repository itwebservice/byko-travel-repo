<?php
include "../../../../../model/model.php";
$financial_year_id = $_SESSION['financial_year_id'];
?>

<div class="app_panel_content Filter-panel">
	<div class="row">
        <div class="col-md-3 col-sm-6">
			<small>Select Financial Year</small>
            <select name="financial_year_id_filter" id="financial_year_id_filter" title="Select Financial Year" class="form-control" onchange="report_reflect();">
                <?php
                $sq_fina = mysqli_fetch_assoc(mysqlQuery("select * from financial_year where financial_year_id='$financial_year_id'"));
                $financial_year = get_date_user($sq_fina['from_date']).'&nbsp;&nbsp;&nbsp;To&nbsp;&nbsp;&nbsp;'.get_date_user($sq_fina['to_date']);
                ?>
                <option value="<?= $sq_fina['financial_year_id'] ?>"><?= $financial_year  ?></option>
                <?php echo get_financial_year_dropdown_filter($financial_year_id); ?>
            </select>
        </div>
	</div>
</div>

<div id="div_list" class="main_block">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="updated_entries" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>

</div>
<script>
$("#financial_year_id_filter" ).select2();

function report_reflect(){

	var column = [
		{ title : "Sr.No"},
		{ title : "Service_Type"},
		{ title : "Transaction_ID"},
		{ title : "Updated_Date/Time"},
		{ title : "Updated_By"},
		{ title : "Amount"},
	];
	var financial_year_id = $('#financial_year_id_filter').val();
	$.post('report_reflect/updated_entries/get_report.php', { financial_year_id : financial_year_id }, function(data){
		pagination_load(data, column, true, false, 20, 'updated_entries');
	});
}
report_reflect();
</script>

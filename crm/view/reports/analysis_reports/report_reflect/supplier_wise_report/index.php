<?php
include '../../../../../model/model.php';
?>
<div class="col-md-12 text-right">
		<button class="btn btn-excel btn-sm" onclick="exportToExcel('supplier_wise_report1')" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
	</div>

<div class="app_panel_content Filter-panel">
		<div class="row">
			<div class="col-md-3 col-sm-4 mg_bt_10_sm_xs">
			    <input type="text" id="from_date" onchange="get_to_date(this.id,'to_date');" name="from_date" class="form-control" placeholder="From Date" title="From Date">
			</div>
			<div class="col-md-3 col-sm-4 mg_bt_10_sm_xs">
			    <input type="text" id="to_date" name="to_date" onchange="validate_validDate('from_date','to_date');" class="form-control" placeholder="To Date" title="To Date">
			</div>	
			<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
				<select name="" id="supplier_type" class="form-control">
					<option value="">Select Supplier Type</option>
					<?php
					$sq_vendor = mysqlQuery("select * from vendor_type_master order by vendor_type");
					while($row_vendor = mysqli_fetch_assoc($sq_vendor)){
						?>
						<option value="<?= $row_vendor['vendor_type'] ?>"><?= $row_vendor['vendor_type'] ?></option>
						<?php
					}
					?>
				</select>
			</div>
			<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
				<button class="btn btn-sm btn-info ico_right" onclick="report_reflect(true)">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
			</div>
			
		
		</div>
	</div>
	

<div id="div_list" class="main_block mg_tp_20">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="supplier_wise_report1" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<div id="other_supp_wise_display">

</div>
<script>
$( "#from_date, #to_date" ).datetimepicker({ timepicker:false, format:'d-m-Y' });        

	
	function report_reflect(data){

		var id = 1;
		if(data != true){
			var fromdate = null;
			var todate = null;
			var supplier_type = null;
		}
		else{
			var fromdate = $('#from_date').val();
			var todate = $('#to_date').val();
			var supplier_type = $('#supplier_type').val();
		}
		var column = [
		{ title : "Sr.No."},
		{ title : "Supplier Name"},
		{ title : "Supplier Type"},
		{ title : "Total Purchase"},
		{ title : "Total Amount"},
		{ title : "Actions" , class : "no-export"}];
		$.post('report_reflect/supplier_wise_report/get_report.php', { fromdate : fromdate, todate : todate,supplier_type: supplier_type}, function(data){
		pagination_load(data, column, true, true, 20, 'supplier_wise_report1');
	});
	}
	
	report_reflect(false);


// 	function view_supp_wise_modal(hotel_id)
// {
// 	var base_url = $('#base_url').val();
// 	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_supp_wise_modal.php', { hotel_id : hotel_id}, function(data){
// 		$('#other_supp_wise_display').html(data);
// 	});
// }

function view_transport(transport_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_transport_modal.php', { transport_id : transport_id,vendor_name : 'Transport Vendor'}, function(data){
 	$('#other_supp_wise_display').html(data);
		});
}
function allModal(vendor_type_id,vendor_type,estimate_id,estimate_type)
{
	$('#view_btn-'+vendor_type_id).prop('disabled',true);
	var base_url = $('#base_url').val();
	$('#view_btn-'+vendor_type_id).button('loading');
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/all_modal_file.php',{ vendor_type_id : vendor_type_id,vendor_type : vendor_type,estimate_id:estimate_id,estimate_type:estimate_type}, function(data){
		$('#other_supp_wise_display').html(data);
		$('#view_btn-'+vendor_type_id).prop('disabled',false);
		$('#view_btn-'+vendor_type_id).button('reset');
	});
}
function ticket_Vendor(ticketvendor_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/all_modal_file.php',{ vendor_type_id : '',vendor_type : '',estimate_id:'',estimate_type:''}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}
function cruise_msater(cruise_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_cruisemaster_modal.php',{ cruise_id : cruise_id,company_name : 'Cruise Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}
function site_seeing(siteseeing_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_siteseeing_modal.php',{ siteseeing_id : siteseeing_id,vendor_name : 'Excursion Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}
function dmc_master(dmc_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_dmcmaster_modal.php',{ dmc_id : dmc_id,company_name : 'Dmc Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}
function visa_vendor(visa_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_visavendor_modal.php',{ visa_id : visa_id,vendor_name : 'Visa Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}
function insurance_vendor(insurance_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_insurancevendor_modal.php',{ insurance_id : insurance_id,vendor_name : 'Insurance Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}
function other_vendor(other_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_othervendor_modal.php',{ other_id : other_id,vendor_name : 'Other Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}

function carrental_vendor(car_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_carrantelvendor_modal.php',{ car_id : car_id,vendor_name : 'Car Rental Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}

function hotel_vendor(hotel_id)
{
	var base_url = $('#base_url').val();
	$.post(base_url+'view/reports/analysis_reports/report_reflect/supplier_wise_report/view_hotelvendor_modal.php',{ hotel_id : hotel_id,hotel_name : 'Hotel Vendor'}, function(data){
		$('#other_supp_wise_display').html(data);
	});
}
</script>
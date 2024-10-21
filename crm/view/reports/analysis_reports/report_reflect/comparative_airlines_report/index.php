<?php
include "../../../../../model/model.php";
$airline = 'select * from airline_master order by airline_name';
$airline1 = 'select * from ticket_master group by ticket_master.tour_type';

$airlinetype = mysqlQuery($airline1);
$airlineres = mysqlQuery($airline);
?>

<div class="col-md-12 text-right">
		<button class="btn btn-excel btn-sm" onclick="exportToExcel('comparative_airlines_report')" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
	</div>
<div class="app_panel_content Filter-panel">
		<div class="row">
		<div class="col-md-2 col-sm-4 mg_bt_10_sm_xs">
			<select class="form-control" name="booking_id_filter" id="select_airlinetype"  style="width:100%" title="Travelling Type" >
			<option value="">Travelling Type</option>
					<option value="Domestic">Domestic</option>
					<option value="International">International</option>
		    </select>
		</div>
		<div class="col-md-2 col-sm-4 mg_bt_10_sm_xs">
			<select class="form-control" name="booking_id_filter" id="select_airline"  style="width:100%" title="Select Airline" >
					
					<?php 
					if(mysqli_num_rows($airlineres))
						{
							echo '<option value="">Select Airline</option>'; 
						while($db= mysqli_fetch_assoc($airlineres))
						{
							?>
						<option value="<?php echo $db['airline_id']; ?>"><?php echo $db['airline_name']; ?></option>		

					<?php
						}
					}
					?>
		    </select>
		</div>
		
			<div class="col-md-2 col-sm-4 mg_bt_10_sm_xs">
			    <input type="text" id="from_date" onchange="get_to_date(this.id,'to_date');" name="from_date" class="form-control" placeholder="From Date" title="From Date">
			</div>
			<div class="col-md-2 col-sm-4 mg_bt_10_sm_xs">
			    <input type="text" id="to_date" name="to_date" onchange="validate_validDate('from_date','to_date');" class="form-control" placeholder="To Date" title="To Date">
			</div>	
			<div class="col-md-2 col-sm-6 col-xs-12 mg_bt_10">
				<button class="btn btn-sm btn-info ico_right" onclick="report_reflect(true)">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
			</div>
			
		</div>
	</div>
	

<div id="div_list" class="main_block mg_tp_20">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="comparative_airlines_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<div id="other_airline_display">

</div>
<script>
$( "#from_date, #to_date" ).datetimepicker({ timepicker:false, format:'d-m-Y' });        

	
	function report_reflect(data){
		
		var id = 1;
		if(data != true)
		{
	
		var fromdate = null;
			var todate = null;
			
	}
		else
		{
	
		var airlinetype = $("#select_airlinetype").val();
		var airlineid = $("#select_airline").val();
		var fromdate = $('#from_date').val();
		var todate = $('#to_date').val();
				
		}
		var column = [
	{ title : "Airline Name"},
	{ title : "Total Seats"},
	{ title : "Total Amount"},
	{ title : "Actions" , class : "no-export"},
];
		$.post('report_reflect/comparative_airlines_report/get_report.php', {airlinetype : airlinetype, airlineid : airlineid , fromdate : fromdate, todate : todate}, function(data){
		// console.log(data);
		pagination_load(data, column, true, true, 20, 'comparative_airlines_report');
	});
	}
	
	report_reflect(false);


	function view_com_airlines_modal(airline_id)
{
	$('#view_btn-'+airline_id).prop('disabled',true);
	var base_url = $('#base_url').val();
	$('#view_btn-'+airline_id).button('loading');
	$.post(base_url+'view/reports/analysis_reports/report_reflect/comparative_airlines_report/view_com_airlines_modal.php', { airline_id : airline_id}, function(data){
		$('#other_airline_display').html(data);
		$('#view_btn-'+airline_id).prop('disabled',false);
		$('#view_btn-'+airline_id).button('reset');
	});
}
</script>


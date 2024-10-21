<?php 
include "../../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$q = "select * from branch_assign where link='b2b_sale/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_statusr = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_statusr" name="branch_status" value="<?= $branch_statusr ?>" >
<div class="row mg_bt_10">
	<div class="col-md-12 text-right">
		<button class="btn btn-excel btn-sm" onclick="excel_report()" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
	</div>
</div>

<div class="app_panel_content Filter-panel">
<div class="row">
        <div class="col-md-3 col-sm-6 mg_bt_10_xs">
            <select name="cust_filter" id="cust_filter" onchange="booking_reflect();" style="width:100%" data-toggle="tooltip" title="Select Agent">
            <?php
            $sq_rc = mysqlQuery("select * from customer_master where type='B2B' and active_flag='Active'"); ?>
            <option value="">Select Agent</option>
            <?php
            while($row_rc = mysqli_fetch_assoc($sq_rc)){
              ?>
              <option value="<?= $row_rc['customer_id'] ?>"><?=  $row_rc['company_name'] ?></option>
              <?php } ?>     
            </select>
        </div>
        <div class="col-md-3 col-sm-6 mg_bt_10_xs">
            <select name="b2b_booking_master" id="b2b_booking_master" style="width:100%" title="Select Booking ID" data-toggle="tooltip">
              <option value="">Select Booking</option></select>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
          <input type="text" id="from_date_filter" name="from_date_filter" placeholder="From Date"  data-toggle="tooltip" title="From Date" onchange="get_to_date(this.id,'to_date_filter');">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
          <input type="text" id="to_date_filter" name="to_date_filter" placeholder="To Date" data-toggle="tooltip" title="To Date" onchange="validate_validDate('from_date_filter','to_date_filter');">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <button class="btn btn-sm btn-info ico_right" onclick="list_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
        </div>
    </div>
 </div>	
	

</div>	
<div id="div_list" class="main_block">
<div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="package_tour_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<div id="div_package_content_display"></div>

<script>

$('#from_date_filter,#to_date_filter').datetimepicker({ timepicker:false, format:'d-m-Y' });
// dynamic_customer_load('','');
$('#cust_filter,#b2b_booking_master').select2();
var column = [
	{ title : "S_No."},
	{ title:"Booking_ID"},
	{ title : "Agent_Name"},
	{ title : "Mobile"},
	{ title : "EMAIL_ID"},
	{ title : "Booking_Date"},
	{ title : "View"},
	{ title : "Sale", className:"info text-right"},
	{ title : "Cancel", className:"danger text-right"},
	{ title : "Total", className:"info text-right"},
	{ title : "Paid", className:"success text-right"},
	{ title : "View"},
	{ title : "Outstanding_Balance", className:"warning text-right"},
	{ title : "Purchase"},
	{ title : "Purchased_From"},
	{ title : "Purchased_History"},
	{ title : "Booked_By"}	
];
function list_reflect()
{
	var base_url = $('#base_url').val();
	var customer_id = $('#cust_filter').val();
	var b2b_booking_master = $('#b2b_booking_master').val();
	var from_date = $('#from_date_filter').val();
  var to_date = $('#to_date_filter').val();
	var branch_status = $('#branch_statusr').val();

	$.post(base_url+'view/b2b_sale/summary/list_reflect.php', { customer_id : customer_id, b2b_booking_master : b2b_booking_master, from_date : from_date, to_date : to_date,branch_status:branch_status}, function(data){
		// alert(data);
		pagination_load(data, column, true, true, 20, 'package_tour_report',true);
	});
}
list_reflect();

function group_view_modal(id)
  {
	$('#packagev_btn-'+id).prop('disabled',true);
	var base_url = $('#base_url').val();
    $('#packagev_btn-'+id).button('loading');
    var base_url = $('#base_url').val();
    $.post(base_url+'view/b2b_sale/summary/view/index.php', { booking_id : id }, function(data){
      console.log(data);
    	$('#div_package_content_display').html(data);
		  $('#packagev_btn-'+id).prop('disabled',false);
    	$('#packagev_btn-'+id).button('reset');
    });
  }
function excel_report()
{
	var customer_id = $('#cust_filter').val();
	var b2b_booking_master = $('#b2b_booking_master').val();
	var from_date = $('#from_date_filter').val();
    var to_date = $('#to_date_filter').val();
	var base_url = $('#base_url').val();
	var branch_status = $('#branch_statusr').val();
	window.location = base_url+'view/b2b_sale/summary/excel_report.php?customer_id='+customer_id+'&b2b_booking_master='+b2b_booking_master+'&from_date='+from_date+'&to_date='+to_date+'&branch_status='+branch_status;
}
function booking_reflect()
{  
	var base_url = $('#base_url').val();
	var customer_id = $('#cust_filter').val();
  $.post( base_url+'view/b2b_sale/booking_reflect.php', { customer_id : customer_id}, function(data){
    $('#b2b_booking_master').html(data);
  });
}
booking_reflect();
function package_view_modal(booking_id)
  {
    $('#packagev_btn-'+booking_id).prop('disabled',true);
    var base_url = $('#base_url').val();
    $('#packagev_btn-'+booking_id).button('loading');
    $.post(base_url+'view/b2b_sale/summary/view/index.php', { booking_id : booking_id }, function(data){
      $('#div_package_content_display').html(data);
      $('#packagev_btn-'+booking_id).prop('disabled',false);
      $('#packagev_btn-'+booking_id).button('reset');
    });
  }
  function supplier_view_modal(booking_id)
  {
    $('#supplierv_btn-'+booking_id).prop('disabled',true);
    var base_url = $('#base_url').val();
    $('#supplierv_btn-'+booking_id).button('loading');
    $.post(base_url+'view/b2b_sale/summary/view/supplier_view_modal.php', { booking_id : booking_id }, function(data){
      $('#div_package_content_display').html(data);
		  $('#supplierv_btn-'+booking_id).prop('disabled',false);
    	$('#supplierv_btn-'+booking_id).button('reset');
    });
  }
function payment_view_modal(booking_id)
{
	$('#paymentv_btn-'+booking_id).prop('disabled',true);
	var base_url = $('#base_url').val();
    $('#paymentv_btn-'+booking_id).button('loading');
	$.post(base_url+'view/b2b_sale/summary/view/payment_view_modal.php', { booking_id : booking_id }, function(data){	
	  $('#div_package_content_display').html(data);
		$('#paymentv_btn-'+booking_id).prop('disabled',false);
    	$('#paymentv_btn-'+booking_id).button('reset');
	});
}
$(function () {
    $("[data-toggle='tooltip']").tooltip({placement: 'bottom'});
});
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
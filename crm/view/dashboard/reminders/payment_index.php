<?php
include "../../../model/model.php";
?>
<input type="hidden" id="date" value="<?= $date ?>"/>
<div id="div_list" class="main_block mg_tp_20">
	<div class="dashboard_table dashboard_table_panel main_block">
        <div class="row mg_tp_10">
			<div class="col-md-7 col-sm-6 mg_bt_10"></div>
            <div class="col-md-2 col-sm-6 mg_bt_10">
                <input class="form-control" type="text" id="rfrom_date_filter" name="rfrom_date_filter" placeholder="*From Date" title="From Date" value="<?= date('d-m-Y') ?>" onchange="get_to_date(this.id,'rto_date_filter');">
            </div>
            <div class="col-md-2 col-sm-6 mg_bt_10">
                <input class="form-control" type="text" id="rto_date_filter" name="rto_date_filter" placeholder="*To Date" title="To Date" value="<?= date('d-m-Y') ?>" onchange="validate_validDate('rfrom_date_filter','rto_date_filter');">
            </div>
            <div class="col-md-1 text-left col-sm-6 mg_bt_10">
                <button class="btn btn-excel btn-sm" onclick="get_payment_reminders()" data-toggle="tooltip" title="" data-original-title="Proceed"><i class="fa fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="row text-left mg_tp_10" id="reminder_report_data">
        </div>
    </div>
</div>

<script>
$('#rfrom_date_filter,#rto_date_filter').datetimepicker({format: 'd-m-Y',timepicker:false});
function get_payment_reminders(){
	
	var from_date = $('#rfrom_date_filter').val();
	var to_date = $('#rto_date_filter').val();
    if(from_date==''){
        error_msg_alert('Select From Date');
        return false;
    }
    if(to_date==''){
        error_msg_alert('Select To Date');
        return false;
    }
	$.post('reminders/payment.php', { from_date :from_date,to_date:to_date }, function(data){
        $('#reminder_report_data').html(data);
    });
}
get_payment_reminders();
function whatsapp_reminder(service,cust_name,sale,paid,balance,mobile_no,booking_id){

    var app_name = $('#app_name').val();
    var app_contact_no = $('#app_contact_no').val();

    var msg = encodeURI("Dear " + cust_name + ",\n\nWe would like to thank you for booking a tour with us. Hereby we request you to release the outstanding payment as per the due date.\n\n*Booking ID* : "+booking_id+"\n*Total Amount* : "+sale+"\n*Paid Amount* : "+paid+"\n*Balance Amount* : "+balance+"\nPlease contact for more details : "+app_name+" ")
    msg += encodeURIComponent(app_contact_no);
    msg += encodeURI("\nThank you.");
	window.open('https://web.whatsapp.com/send?phone=' + mobile_no + '&text=' + msg);
}
</script>
<?php
include "../../../model/model.php";
?>
<input type="hidden" id="date" value="<?= $date ?>"/>
<div id="div_list" class="main_block mg_tp_20">
	<div class="dashboard_table dashboard_table_panel main_block">
        <div class="row mg_tp_10">
			<div class="col-md-9 col-sm-6 mg_bt_10"></div>
            <div class="col-md-2 col-sm-6 mg_bt_10">
                <input class="form-control" type="text" id="cfrom_date_filter" name="cfrom_date_filter" placeholder="*Date" title="Date" value="<?= date('d-m-Y') ?>" >
            </div>
            <div class="col-md-1 text-left col-sm-6 mg_bt_10">
                <button class="btn btn-excel btn-sm" onclick="get_common_reminders()" data-toggle="tooltip" title="" data-original-title="Proceed"><i class="fa fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="row text-left mg_tp_10" id="reminder_report_data">
        </div>
    </div>
</div>
<script>
$('#cfrom_date_filter').datetimepicker({format: 'd-m-Y',timepicker:false});
function get_common_reminders(){
	
	var from_date = $('#cfrom_date_filter').val();
    if(from_date==''){
        error_msg_alert('Select From Date');
        return false;
    }
	$.post('reminders/common.php', { from_date :from_date }, function(data){
        $('#reminder_report_data').html(data);
    });
}
get_common_reminders();
function whatsapp_reminder(service,cust_name,sale,paid,balance,mobile_no,booking_id){

    var app_name = $('#app_name').val();
    var app_contact_no = $('#app_contact_no').val();
    var msg = encodeURI("Dear " + cust_name + ",\n\nWe would like to thank you for booking a tour with us. Hereby we request you to release the outstanding payment as per the due date.\n\n*Booking ID* : "+booking_id+"\n*Total Amount* : "+sale+"\n*Paid Amount* : "+paid+"\n*Balance Amount* : "+balance+"\nPlease contact for more details : "+app_name+" ")
    msg += encodeURIComponent(app_contact_no);
    msg += encodeURI("\nThank you.");
	window.open('https://web.whatsapp.com/send?phone=' + mobile_no + '&text=' + msg);
}
</script>
<script src="js/common_reminders.js"></script>
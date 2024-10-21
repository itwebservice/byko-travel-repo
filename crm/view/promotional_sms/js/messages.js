function sms_message_edit_modal(sms_message_id) {

    $('#smse_btn-'+sms_message_id).button('loading');
    $('#smse_btn-'+sms_message_id).prop('disabled',true);
	$.post('messages/message_edit_modal.php', { sms_message_id: sms_message_id }, function (data) {
		$('#div_sms_message_edit_content').html(data);
		$('#smse_btn-'+sms_message_id).button('reset');
		$('#smse_btn-'+sms_message_id).prop('disabled',false);
	});

}

function sms_message_send(sms_message_id, offset) {

	$('#send-'+offset).prop('disabled',true);
	var sms_group_id = $('#sms_group_id_' + offset).val();
	var base_url = $('#base_url').val();
	$('#send-'+offset).button('loading');
	$.ajax({
		type: 'post',
		url: base_url + 'controller/promotional_sms/messages/sms_message_send.php',
		data: { sms_message_id: sms_message_id, sms_group_id: sms_group_id },
		success: function (result) {
			msg_alert(result);
			$('#send-'+offset).prop('disabled',false);
			$('#send-'+offset).button('reset');
			sms_message_list_reflect();
		}
	});
}

function sms_message_log_modal(sms_message_id) {
    $('#smsv_btn-'+sms_message_id).button('loading');
    $('#smsv_btn-'+sms_message_id).prop('disabled',true);
	$.post('messages/sms_message_log_modal.php', { sms_message_id: sms_message_id }, function (data) {
		$('#div_sms_message_log_content').html(data);
		$('#smsv_btn-'+sms_message_id).button('reset');
		$('#smsv_btn-'+sms_message_id).prop('disabled',false);
	});
}
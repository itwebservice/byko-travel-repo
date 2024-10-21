function email_group_edit_modal(email_group_id){

    $('#email_groupe_btn-'+email_group_id).button('loading');
    $('#email_groupe_btn-'+email_group_id).prop('disabled',true);
	$.post('email_group/email_group_edit_modal.php', { email_group_id : email_group_id }, function(data){
		$('#div_sms_group_edit_content').html(data);
		$('#email_groupe_btn-'+email_group_id).button('reset');
		$('#email_groupe_btn-'+email_group_id).prop('disabled',false);
	});

}
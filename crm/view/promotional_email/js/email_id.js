function email_id_edit_modal(email_id_id){

    $('#emaile_btn-'+email_id_id).button('loading');
    $('#emaile_btn-'+email_id_id).prop('disabled',true);
	$.post('email_id/email_id_edit_modal.php', { email_id_id : email_id_id }, function(data){
		$('#div_mobile_no_edit_content').html(data);
		$('#emaile_btn-'+email_id_id).button('reset');
		$('#emaile_btn-'+email_id_id).prop('disabled',false);
	});

}
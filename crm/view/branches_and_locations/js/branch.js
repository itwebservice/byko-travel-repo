function branches_list_reflect(branch_id='')
{	console.log(branch_id);
	$.post('branches/branches_list_reflect.php', { location_id : branch_id }, function(data){
		$('#branch_list_div').html(data);
	});
}
branches_list_reflect();

function branch_edit_modal(branch_id)
{
	$('#branchEdit'+branch_id).prop('disabled',true);
	$('#branchEdit'+branch_id).button('loading');
	$.post('branches/branch_edit_modal.php', { branch_id : branch_id }, function(data){
		$('#div_branch_edit_modal').html(data);
		$('#branchEdit'+branch_id).prop('disabled',false);
		$('#branchEdit'+branch_id).button('reset');
	});
}


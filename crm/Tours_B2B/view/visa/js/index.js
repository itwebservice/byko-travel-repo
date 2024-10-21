// Off display dummy data in inputs/fields
$(function () {
	$('form').attr('autocomplete', 'off');
	$('input').attr('autocomplete', 'off');
});
//Visa Search From submit
$(function () {
	$('#frm_visa_search').validate({
		rules         : {},
		submitHandler : function (form) {
			var base_url = $('#base_url').val();
			var country_id = $('#visa_country_filter').val();
			var visa_type = $('#visa_type_filter').val();
			var passengers = $('#passengers').val();

			var visa_array = [];
			visa_array.push({
				'country_id': country_id,
				'visa_type':visa_type,
				'pax':passengers
			});
			$.post(base_url+'controller/visa_master/b2b_visa_save.php', { visa_array: visa_array }, function (data) {
				window.location.href = base_url + 'Tours_B2B/view/visa/visa-listing.php';
			});
		}
	});
});

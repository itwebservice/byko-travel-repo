
function dynamic_customer_load(cust_type, company_name)
{
	var cust_type = $('#cust_type_filter').val();
	var company_name = $('#company_filter').val();
	var branch_status = $('#branch_status').val();
	var base_url = $('#base_url').val();
		$.get(base_url+"view/visa_passport_ticket/ticket/home/get_customer_dropdown.php", { cust_type : cust_type , company_name : company_name, branch_status : branch_status}, function(data){
		$('#customer_div').html(data);
	});
}
function company_name_reflect()
{  
	var cust_type = $('#cust_type_filter').val();
	var branch_status = $('#branch_status').val();
	var base_url = $('#base_url').val();
	$.post(base_url+'view/visa_passport_ticket/ticket/home/company_name_load.php', { cust_type : cust_type, branch_status : branch_status }, function(data){
		if(cust_type=='Corporate'||cust_type=='B2B'){
			$('#company_div').addClass('company_class');	
		}
		else
		{
			$('#company_div').removeClass('company_class');		
		}
		$('#company_div').html(data);
	});
}
company_name_reflect();
function customer_info_load(offset='')
{
	var customer_id = $('#customer_id'+offset).val();
	var base_url = $('#base_url').val();
	if(customer_id==0 && customer_id!='')
	{
		$('#cust_details').addClass('hidden');
		$('#new_cust_div').removeClass('hidden');

		$.ajax({
		type:'post',
		url:base_url+'view/load_data/new_customer_info.php',
		data:{},
		success:function(result){
			$('#new_cust_div').html(result);
		}
		});
	}
else{
	if(customer_id!=''){
		$('#new_cust_div').addClass('hidden');
		$('#cust_details').removeClass('hidden');
		$.ajax({
			type:'post',
			url:base_url+'view/load_data/customer_info_load.php',
			data:{ customer_id : customer_id },
			success:function(result){
				result = JSON.parse(result);
				$('#mobile_no'+offset).val(result.contact_no);
				$('#email_id'+offset).val(result.email_id);
				if(result.company_name != ''){
					$('#company_name'+offset).removeClass('hidden');
					$('#company_div'+offset).removeClass('hidden');
					$('#company_name'+offset).val(result.company_name);	
				}
				else
				{
					$('#company_name'+offset).addClass('hidden');
					$('#company_div'+offset).addClass('hidden');
				}
				if(result.payment_amount != '' || result.payment_amount != '0'){
					$('#credit_amount'+offset).removeClass('hidden');
					$('#credit_div'+offset).removeClass('hidden');
					$('#credit_amount'+offset).val(result.payment_amount);	
				}
				else{
					$('#credit_amount'+offset).addClass('hidden');
					$('#credit_div'+offset).addClass('hidden');
					$('#credit_amount'+offset).val(0);
				}
			}
		});
	}
}
}

function airport_load_main_sale(ids){

	var base_url = $('#base_url').val();
	ids.forEach(function (id){
		var object_id = Object.values(id)[0];
		$("#"+object_id).autocomplete({
			source: function(request, response){
				$.ajax({
					method:'get',
					url : base_url+'view/visa_passport_ticket/ticket/home/airport_list.php',
					dataType : 'json',
					data : {request : request.term},
					success : function(data){
						response(data);
					}
				});
			},
			select: function (event, ui) {
				var substr_id =  object_id.substr(6);
				if(Object.keys(id)[0] == 'dep'){
					$('#from_city-'+substr_id).val(ui.item.city_id);
					$('#departure_city-'+substr_id).val(ui.item.value.split(" - ")[1]);
				}
				else{
					$('#to_city-'+substr_id).val(ui.item.city_id);
					$('#arrival_city-'+substr_id).val(ui.item.value.split(" - ")[1]);
				}
			},
			open: function(event, ui) {
				$(this).autocomplete("widget").css({
					"width": document.getElementById(object_id).offsetWidth
				});
			},
			minLength: 2,
			change: function(event,ui){
				var substr_id =  object_id.substr(6);
				if(!ui.item) {
					$(this).val('');
					$('#from_city-'+substr_id).val("");
					$('#departure_city-'+substr_id).val("");
					error_msg_alert('Please select Airport from the list!!');
					$(this).css('border','1px solid red;');
					return;
				}
				if($('#'+ids[0].dep).val() == $("#"+ids[1].arr).val()){
					$(this).val('');
					$(this).css('border','1px solid red;');
					$('#from_city-'+substr_id).val("");
					$('#departure_city-'+substr_id).val("");
					error_msg_alert('Same Arrival and Boarding Airport Not Allowed!!');
				}
	
			}
		}).data("ui-autocomplete")._renderItem = function(ul, item) {
				return $("<li disabled>")
				.append("<a>" + item.value.split(" -")[1] + "<br><b>" + item.value.split(" -")[0] + "<b></a>")
				.appendTo(ul);
			}
	});	
}

function calculate_total_amount(id){

	var adult_fair = $('#adult_fair').val();
	var children_fair = $('#children_fair').val();
	var infant_fair = $('#infant_fair').val();

	var adults = $('#adults').val();
	var childrens = $('#childrens').val();
	var infant = $('#infant').val();

	if(adult_fair==""){ adult_fair = 0; }
	if(children_fair==""){ children_fair = 0; }
	if(infant_fair==""){ infant_fair = 0; }

	var basic_cost = parseFloat(adult_fair) + parseFloat(children_fair) + parseFloat(infant_fair);

	if(id != 'basic_cost'){
		$('#basic_cost').val(basic_cost);
		$('#basic_cost').trigger('change');
	}
		
	var markup = $('#markup').val();
	var discount = $('#discount').val();
	var yq_tax = $('#yq_tax').val();		
	var other_taxes = $('#other_taxes').val();
	var service_charge = $('#service_charge').val();
	var service_tax_subtotal = $('#service_tax_subtotal').val();
	var service_tax_markup = $('#service_tax_markup').val();
	var tds = $('#tds').val();

	if(markup==""){ markup = 0; }
	if(discount==""){ discount = 0; }
	if(yq_tax==""){ yq_tax = 0; }
	if(other_taxes==""){ other_taxes = 0; }
	// if(service_charge==""){ service_charge = 0; }
	if(tds==""){ tds = 0; }
	if(basic_cost==""){ basic_cost = 0; }

	if(adults==0){
		if($('#adult_fair').val() == ''){
			$('#adult_fair').val(0);
		} 
		$('#adult_fair').prop('readonly', true); 
		}
	else{ $('#adult_fair').prop('disabled', false); 
		$('#adult_fair').prop('readonly', false);
	}

	if(childrens==0){  
		if($('#children_fair').val() == ''){
			$('#children_fair').val(0);
		} 
		$('#children_fair').prop('readonly', true); }
	else{  $('#children_fair').prop('disabled', false); 
		$('#children_fair').prop('readonly', false);	
		}

	if(infant==0){
		$('#infant_fair').val(0); $('#infant_fair').prop('readonly', true); }
	else{ $('#infant_fair').prop('disabled', false);
		$('#infant_fair').prop('readonly', false);
		}

	var basic_cost = parseFloat(adult_fair) + parseFloat(children_fair) + parseFloat(infant_fair);

	$('#basic_cost').val(basic_cost);
	var service_tax_amount = 0;
	if(parseFloat(service_tax_subtotal) !== 0.00 && (service_tax_subtotal) !== ''){
		var service_tax_subtotal1 = service_tax_subtotal.split(",");
		for(var i=0;i<service_tax_subtotal1.length;i++){
			var service_tax = service_tax_subtotal1[i].split(':');
			service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
		}
	}
		
	var markupservice_tax_amount = 0;
	if(parseFloat(service_tax_markup) !== 0.00 && (service_tax_markup) !== ""){
		var service_tax_markup1 = service_tax_markup.split(",");
		for(var i=0;i<service_tax_markup1.length;i++){
			var service_tax = service_tax_markup1[i].split(':');
			markupservice_tax_amount = parseFloat(markupservice_tax_amount) + parseFloat(service_tax[2]);
		}
	}

	basic_cost = ($('#basic_show').html() == '&nbsp;') ? basic_cost : parseFloat($('#basic_show').text().split(' : ')[1]);
	service_charge = ($('#service_show').html() == '&nbsp;') ? service_charge : parseFloat($('#service_show').text().split(' : ')[1]);
	markup = ($('#markup_show').html() == '&nbsp;') ? markup : parseFloat($('#markup_show').text().split(' : ')[1]);
	discount =($('#discount_show').html() == '&nbsp;') ? discount : parseFloat($('#discount_show').text().split(' : ')[1]);

	var ticket_total_cost = parseFloat(basic_cost) + parseFloat(markup) + parseFloat(markupservice_tax_amount) - parseFloat(discount) + parseFloat(yq_tax) + parseFloat(other_taxes) + parseFloat(service_charge) + parseFloat(service_tax_amount) - parseFloat(tds);


	ticket_total_cost = ticket_total_cost.toFixed(2);
	var roundoff = Math.round(ticket_total_cost)-ticket_total_cost;
	$('#roundoff').val(roundoff.toFixed(2));
	$('#ticket_total_cost').val(parseFloat(ticket_total_cost) + parseFloat(roundoff));
	$('#ticket_total_cost').trigger('change');

}
function get_quotation_details(quot){
		
	var base_url = $('#base_url').val();
	$('.dynform-btn').trigger('click');
	$.get(base_url+'view/visa_passport_ticket/ticket/home/get_flight_quotation.php', {quotation_id : quot.value}, function(data){
		data = JSON.parse(data);
		let iterator = 1;
		let length = data.length;
		for(var i = 0; i < length; i++){
			$('#departure_datetime-' + iterator).val(data[0].dapart_time);
			$('#arrival_datetime-' + iterator).val(data[0].arraval_time);
			if(data[0].airline_name != ''&&data[0].airline_name != ' ()'){
				$('#airlines_name-' + iterator).val(data[0].airline_name);
				$('#airlines_name-' + iterator).trigger('change');
			}
			$('#class-' + iterator).val(data[0].class);
			$('#airpf-' + iterator).val(data[0].from_city_show + ' - ' + data[0].from_location);
			$('#airpt-' + iterator).val(data[0].to_city_show + ' - ' + data[0].to_location);
			$('#from_city-' + iterator).val(data[0].from_city);
			$('#departure_city-' + iterator).val(data[0].from_location);
			$('#to_city-' + iterator).val(data[0].to_city);
			$('#arrival_city-' + iterator).val(data[0].to_location);
			data.splice(0, 1);
			if(data.length > 0){
				addDyn('div_dynamic_ticket_info'); event_airport_s();
			}
			iterator = parseInt($('#div_dynamic_ticket_info').attr('data-counter'));
		}
	});
}
function ticket_id_dropdown_load(customer_id_filter, ticket_id_filter)
{
	var customer_id = $('#'+customer_id_filter).val();
	var branch_status = $('#branch_status').val();
	$.post('ticket_id_dropdown_load.php', { customer_id : customer_id  , branch_status : branch_status}, function(data){
		$('#'+ticket_id_filter).html(data);
		$('#'+ticket_id_filter).val('');
        $('#'+ticket_id_filter).trigger('change');
	});
}

function generate_ticket_payment_receipt(payment_id)
{
	url = 'payment/payment_receipt.php?payment_id='+payment_id;
	window.open(url, '_blank');
}

function cash_bank_receipt_generate()
{
	var bank_name_reciept = $('#bank_name_reciept').val();
	var payment_id_arr = new Array();

	$('input[name="chk_ticket_payment"]:checked').each(function(){

		payment_id_arr.push($(this).val());

	});

	if(payment_id_arr.length==0){
		error_msg_alert('Please select at least one payment to generate receipt!');
		return false;
	}

	var base_url = $('#base_url').val();

	var url = base_url+"view/bank_receipts/ticket_payment/cash_bank_receipt.php?payment_id_arr="+payment_id_arr+'&bank_name_reciept='+bank_name_reciept;
	window.open(url, '_blank');
}

function cheque_bank_receipt_generate()
{
	var bank_name_reciept = $('#bank_name_reciept').val();
	var payment_id_arr = new Array();
	var branch_name_arr = new Array();

	$('input[name="chk_ticket_payment"]:checked').each(function(){

		var id = $(this).attr('id');
		var offset = id.substring(19);
		var branch_name = $('#branch_name_'+offset).val();

		payment_id_arr.push($(this).val());
		branch_name_arr.push(branch_name);		

	});

	if(payment_id_arr.length==0){
		error_msg_alert('Please select at least one payment to generate receipt!');
		return false;
	}

	$('input[name="chk_ticket_payment"]:checked').each(function(){

		var id = $(this).attr('id');
		var offset = id.substring(19);
		var branch_name = $('#branch_name_'+offset).val();

		if(branch_name==""){
			error_msg_alert("Please enter branch name for selected payments!");				
			exit(0);
		}
	});

	var base_url = $('#base_url').val();

	var url = base_url+"view/bank_receipts/ticket_payment/cheque_bank_receipt.php?payment_id_arr="+payment_id_arr+'&branch_name_arr='+branch_name_arr+'&bank_name_reciept='+bank_name_reciept;
	window.open(url, '_blank');
}

function get_arrival_dateid(departure_date){
	var offset = departure_date.split('-');
	get_to_datetime(departure_date,'arrival_datetime-'+offset[1]);
}
function whatsapp_send(emp_id, customer_id, booking_date, base_url,contact_no,email_id){
	
	$.post(base_url+'controller/visa_passport_ticket/ticket/whatsapp_send.php',{emp_id:emp_id,booking_date:booking_date ,customer_id:customer_id,booking_date:booking_date,contact_no:contact_no,email_id:email_id}, function(data){
		console.log(data);
		window.open(data);
	});
}
function validate_validDatetimeFlight(id){

	var offset = id.split('-')[1];
	var from_date = $('#departure_datetime-' + offset).val();
	var to_date = $('#' + id).val();

	from_date = from_date.split(' ')[0];
	var edate = from_date.split('-');
	e_date = new Date(edate[2], edate[1] - 1, edate[0]).getTime();
	to_date = to_date.split(' ')[0];
	var edate1 = to_date.split('-');
	e_date1 = new Date(edate1[2], edate1[1] - 1, edate1[0]).getTime();

	var from_date_ms = new Date(e_date).getTime();
	var to_date_ms = new Date(e_date1).getTime();

	if (from_date_ms > to_date_ms) {
		error_msg_alert('From date should not be greater than valid To date');
		$('#departure_datetime-' + offset).css({ border: '1px solid red' });
		document.getElementById('departure_datetime-' + offset).value = '';
		g_validate_status = false;
		return false;
	}
	else {
		$('#departure_datetime-' + offset).css({ border: '1px solid #ddd' });
		return true;
	}
}
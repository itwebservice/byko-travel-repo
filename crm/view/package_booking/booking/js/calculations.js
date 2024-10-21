function calculate_tour_cost(id) {
   //alert('hii');
	var hotel_expenses = $('#txt_hotel_expenses').val();
	var travel_expenses = $('#txt_travel_total_expense1').val();
	var travel_expenses_up = $('#txt_travel_total_expense').val();
	var tour_cost = $('#service_charge').val();
	var tour_service_tax = $('#txt_tour_service_tax').val();
	var actual_tour_cost = $('#txt_actual_tour_cost').val();

	if (travel_expenses_up == '' || travel_expenses_up == 'NaN') {
		travel_expenses_up = 0;
	}
	if (hotel_expenses == '') {
		hotel_expenses = 0;
	}
	if (travel_expenses == '' || travel_expenses == 'NaN') 
	{
		travel_expenses = 0;
	}
	if (tour_cost == '') 
	{
		tour_cost = 0;
	}
	if (tour_service_tax == '') 
	{
		tour_service_tax = 0;
	}
	if (actual_tour_cost == '') 
	{
		actual_tour_cost = 0;
	}
	var basic_total = parseFloat(hotel_expenses) + parseFloat(travel_expenses);
	if (id != 'total_basic_amt') {
		$('#total_basic_amt').val(basic_total.toFixed(2));
		// $('#total_basic_amt').trigger('change');
	}

	calculate_total_tour_cost();
}
function calculate_total_tour_cost() 
{
     //alert('hii2');
	var basic_total = $('#total_basic_amt').val();
	var rue_cost = $('#rue_cost').val();
	var service_tax_subtotal = $('#tour_service_tax_subtotal').val();
	var service_charge = $('#service_charge').val();
	var basic_amount = $('#total_basic_amt').val();
	var discount_in = $('#discount_in').val();
	var tds=$("#tds").val();
	var discount_amt = $('#discount_amt').val();

  //TCS Tax impl
	var tour_type = $('#tour_type').val();
	var tcs_apply = $('#tcs_apply').val();
	var tcs_calc = $('#tcs_calc').val();

	if (basic_total == '') {
		basic_total = 1;
	}
	if (rue_cost == '') {
		rue_cost = 1;
	}
	if (service_tax_subtotal == '') {
		service_tax_subtotal = 0;
	}
	if (service_charge == '') {
		service_charge = 0;
	}
	if (basic_amount == '') {
		basic_amount = 0;
	}
	if (discount_amt == '') {
		discount_amt = 0;
	}
    
	if (tds == "") {
        tds = 0;
    }
	var discountable_amt = parseFloat(service_charge);
	if(discount_in == 'Percentage'){
		var discount = parseFloat(discountable_amt) * parseFloat(discount_amt) / 100;
	}
	else{
		var discount = (service_charge != 0) ? parseFloat(discount_amt) : 0;
	}
	service_charge = parseFloat(discountable_amt) - parseFloat(discount);
	$('#act_discount').val(discount);
	
	var total = parseFloat(basic_amount) + parseFloat(service_charge);
	$('#subtotal').val(total.toFixed(2));
	var total = $('#subtotal').val();
	total = parseFloat(rue_cost) * parseFloat(total);
	$('#subtotal_with_rue').val(total);

	basic_total = ($('#basic_show').html() == '&nbsp;') ? basic_total : parseFloat($('#basic_show').text().split(' : ')[1]);
	service_charge = ($('#service_show').html() == '&nbsp;') ? service_charge : parseFloat($('#service_show').text().split(' : ')[1]);
	markup = ($('#markup_show').html() == '&nbsp;') ? markup : parseFloat($('#markup_show').text().split(' : ')[1]);
	discount = ($('#discount_show').html() == '&nbsp;') ? discount : parseFloat($('#discount_show').text().split(' : ')[1]);

	var service_tax_amount = 0;
	if (parseFloat(service_tax_subtotal) !== 0.0 && service_tax_subtotal !== '') {
		var service_tax_subtotal1 = service_tax_subtotal.split(',');
		for (var i = 0; i < service_tax_subtotal1.length; i++) {
			var service_tax = service_tax_subtotal1[i].split(':');
			service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
		}
	}

	var total_tour_cost = parseFloat(basic_total) + parseFloat(service_tax_amount) + parseFloat(service_charge);
	var total = total_tour_cost.toFixed(2);

	if(tour_type == 'International' && parseInt(tcs_apply) == 1){
		if(parseInt(tcs_calc) == 0){
			var net_total = (parseFloat(total_tour_cost));
			var tsc_tax = parseFloat(net_total) * (parseFloat(tcs) / 100 );
    		//$('#tcs_tax').val('');
    		//$("#tcs1").val('');
		}else{
		   //$('#tcs_tax').val('');
		   //$("#tcs1").val('');
		}
	}
	else if(tour_type == 'Domestic' || parseInt(tcs_apply) == 0){
		var tsc_tax = 0;
		//$('#tcs_tax').val('');
		//$("#tcs1").val('');
	}
	// add tcs in total amount
	customTcsTax();
	var tcs1 = $('#tcs1').val();
	if (tcs1 == "") {
        tcs1 = 0;
    }
	total = parseFloat(total)+parseFloat(tcs1)-parseFloat(tds);
	
	var roundoff = Math.round(total) - total;

	$('#roundoff').val(roundoff.toFixed(2));
	$('#txt_actual_tour_cost1').val((parseFloat(total) + parseFloat(roundoff)).toFixed(2));
	$('#txt_actual_tour_cost2').val((parseFloat(total) + parseFloat(roundoff)).toFixed(2));
}

$(document).on("change","#tcs_tax",function() {
    customTcsTax();
});

function customTcsTax()
{
    var tcs_tax=$("#tcs_tax").val();
    if(tcs_tax!=='')
    {
       var  subtotal=$("#subtotal").val();
       var txt_actual_tour_cost1=$("#txt_actual_tour_cost1").val();
       var  service_tax_amount=0;
       var  tax_subtotal=$("#tour_service_tax_subtotal").val();
       var service_tax_subtotal1 = tax_subtotal.split(',');
	   for (var i = 0; i < service_tax_subtotal1.length; i++) {
		    var service_tax = service_tax_subtotal1[i].split(':');
		    service_tax_amount = parseFloat(service_tax_amount) + parseFloat(service_tax[2]);
	   }
	   
       var tcsamount=parseFloat(parseFloat(service_tax_amount)+parseFloat(subtotal))*parseFloat(tcs_tax)/100;
       var totalTcs=$("#tcs1").val();
       if(totalTcs=='')
       {
        totalTcs=0;   
       }
       console.log(tcsamount);
       $("#tcs1").val(tcsamount.toFixed(2));
        $("#tcs").val(tcsamount.toFixed(2));

       txt_actual_tour_cost1=parseFloat(txt_actual_tour_cost1)-parseFloat(totalTcs);
       var txt_actual_tour_cost1total=parseFloat(tcsamount)+parseFloat(txt_actual_tour_cost1);
       $("#txt_actual_tour_cost1").val(txt_actual_tour_cost1total.toFixed(2));
    }
    else
    {
        var totalTcs=$("#tcs1").val();
        $("#tcs1").val(0.00);
         $("#tcs").val(0.00);

        var txt_actual_tour_cost1=$("#txt_actual_tour_cost1").val();
        var txt_actual_tour_cost1total=parseFloat(txt_actual_tour_cost1)-parseFloat(totalTcs);
        $("#txt_actual_tour_cost1").val(txt_actual_tour_cost1total.toFixed(2));
    }    

}
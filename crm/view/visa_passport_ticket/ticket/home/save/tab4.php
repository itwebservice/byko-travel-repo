<form id="frm_tab4">
<div class="app_panel">

<div class="">
<div class="container-fluid">
	<div class="app_panel_content Filter-panel">
		<div class="row mg_tp_20">
			<div class="col-md-2 col-md-offset-3 col-sm-4 col-xs-12 mg_bt_10">
				<input type="text" id="payment_date" name="payment_date" placeholder="Date" title="Date" value="<?= date('d-m-Y')?>" onchange="check_valid_date(this.id)">
			</div>
			<div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
				<select name="payment_mode" id="payment_mode" title="Payment" onchange="payment_master_toggles(this.id, 'bank_name', 'transaction_id', 'bank_id');get_identifier_block('identifier','payment_mode','credit_card_details','credit_charges');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges')">
					<?php get_payment_mode_dropdown(); ?>
				</select>
			</div>	
			<div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
				<input type="text" id="payment_amount" name="payment_amount" placeholder="*Amount" title="Amount" onchange="validate_balance(this.id);payment_amount_validate(this.id,'payment_mode','transaction_id','bank_name','bank_id');get_credit_card_charges('identifier','payment_mode','payment_amount','credit_card_details','credit_charges');">
			</div>
		</div>
		<div class="row mg_bt_10">
			<div class="col-md-2 col-md-offset-3 col-sm-6 col-xs-12">
				<input class="hidden" type="text" id="credit_charges" name="credit_charges" title="Credit card charges" disabled>
			</div>
			<div class="col-md-2 col-sm-6 col-xs-12">
				<select class="hidden" id="identifier" onchange="get_credit_card_data('identifier','payment_mode','credit_card_details')" title="Identifier(4 digit)" required
				><option value=''>Select Identifier</option></select>
			</div>
			<div class="col-md-2 col-sm-6 col-xs-12">
				<input class="hidden" type="text" id="credit_card_details" name="credit_card_details" title="Credit card details" disabled>
			</div>
		</div>
		<div class="row">	
			<div class="col-md-2 col-md-offset-3 col-sm-4 col-xs-12 mg_bt_10">
				<input type="text" id="bank_name" name="bank_name" class="bank_suggest" placeholder="Bank Name" title="Bank Name" disabled>
			</div>
			<div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
				<input type="number" id="transaction_id" name="transaction_id" onchange="validate_specialChar(this.id);" placeholder="Cheque No/ID" title="Cheque No/ID" disabled>
			</div>
			<div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10">
		        <select name="bank_id" id="bank_id" title="Select Bank" disabled>
		          <?php get_bank_dropdown(); ?>
		        </select>
		    </div>
		</div>
	    <div class="row">
	      <div class="col-md-9 col-md-offset-3 col-sm-9">
	       <span style="color: red;line-height: 35px;" data-original-title="" title="" class="note"><?= $txn_feild_note ?></span>
	     </div>
	    </div>
	<br>

		<div class="row mg_tp_20 text-center">
			<div class="col-xs-12">		
				<button class="btn btn-sm btn-info btn-sm ico_left" type="button" onclick="switch_to_tab3()"><i class="fa fa-arrow-left"></i>&nbsp;&nbsp;Previous</button>

				&nbsp;&nbsp;
				<button class="btn btn-sm btn-success" id="btn_ticket_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
			</div>
</div>
</div>
</div>
</div>
</div>
</form>
<script>

function switch_to_tab3(){ 
	$('#tab_4_head').addClass('done');
	$('#tab_3_head').addClass('active');
	$('.bk_tab').removeClass('active');
	$('#tab3').addClass('active');
	$('html, body').animate({ scrollTop: $('.bk_tab_head').offset().top }, 200);
}

$('#frm_tab4').validate({

	rules:{

		payment_date : { required : true },

		payment_amount : { required : true, number: true },

		payment_mode : { required : true },  

        bank_id : { required : function(){  if($('#payment_mode').val()!="Cash"){ return true; }else{ return false; }  }  },  

	},

	submitHandler:function(form){

		$('#btn_ticket_save').prop('disabled', true);
		var base_url = $('#base_url').val();
		var customer_id = $('#customer_id').val();
		var cust_first_name = $('#cust_first_name').val();
	    var cust_middle_name = $('#cust_middle_name').val();
	    var cust_last_name = $('#cust_last_name').val();
	    var gender = $('#cust_gender').val();
	    var cust_birth_date = $('#cust_birth_date').val();
	    var age = $('#cust_age').val();
	    var contact_no = $('#cust_contact_no').val();
	    var email_id = $('#cust_email_id').val();
	    var address = $('#cust_address1').val();
	    var address2 = $('#cust_address2').val();
	    var city = $('#city').val();
	    var service_tax_no = $('#cust_service_tax_no').val();  
	    var landline_no = $('#cust_landline_no').val();
	    var alt_email_id = $('#cust_alt_email_id').val();
	    var company_name = $('#corpo_company_name').val();
	    var credit_amount = $('#credit_amount').val();
	    var cust_type = $('#cust_type').val();
		var country_code = $('#country_code').val();
	    var state = $('#cust_state').val();
		var active_flag = 'Active';
		var branch_admin_id = $('#branch_admin_id1').val();
		var financial_year_id = $('#financial_year_id').val();
		var credit_charges = $('#credit_charges').val();
		var credit_card_details = $('#credit_card_details').val();

		//Flight Save
		var emp_id = $('#emp_id').val();
		var tour_type = $('#tour_type').val();
		// var type_of_tour = $('input[name="type_of_tour"]:checked').val();

		var adults = $('#adults').val();

		var childrens = $('#childrens').val();

		var infant = $('#infant').val();

		var adult_fair = $('#adult_fair').val();

		var children_fair = $('#children_fair').val();

		var infant_fair = $('#infant_fair').val();

		var basic_cost = $('#basic_cost').val();

		var discount = $('#discount').val();

		var yq_tax = $('#yq_tax').val();

		var other_taxes = $('#other_taxes').val();

		var service_charge = $('#service_charge').val();

		var service_tax_subtotal = $('#service_tax_subtotal').val();

		var markup = $('#markup').val();

		var service_tax_markup = $('#service_tax_markup').val();

		var tds = $('#tds').val();

		var due_date = $('#due_date').val();

		var booking_date = $('#booking_date').val();

		var ticket_total_cost = $('#ticket_total_cost').val();

		var payment_date = $('#payment_date').val();

		var payment_amount = $('#payment_amount').val();

		var payment_mode = $('#payment_mode').val();

		var bank_name = $('#bank_name').val();

		var transaction_id = $('#transaction_id').val();	

		var bank_id = $('#bank_id').val();
		
		var ticket_reissue = $('#reissue_check1:checked').length;

		var first_name_arr = []; 

		var middle_name_arr = []; 

		var last_name_arr = []; 

		var adolescence_arr = []; 

		var ticket_no_arr = []; 

		var gds_pnr_arr = []; 	

		var baggage_info_arr = [];
		
		var main_ticket_arr = [];
		var seat_no_arr = [];
		var meal_plan_arr = [];
		var trip_details_arr1 = [];
		
        if(payment_mode=="Advance"){
			error_msg_alert("Please select another payment mode.");
			$('#btn_ticket_save').prop('disabled',false);
			return false;
        }

		if(payment_mode=="Credit Note" && credit_amount != ''){
	        if(parseFloat(payment_amount) > parseFloat(credit_amount)) {
				error_msg_alert('Credit Note Balance is not available');
				$('#btn_ticket_save').prop('disabled',false);
				return false;
			}
	    }
		else if(payment_mode=="Credit Note" && credit_amount == ''){
			error_msg_alert("Credit Note Balance is not available"); $('#btn_ticket_save').prop('disabled',false); return false;
		}
	    if(parseFloat(payment_amount)>parseFloat(ticket_total_cost)){
			error_msg_alert("Payment amount cannot be greater than selling amount.");
			$('#btn_ticket_save').prop('disabled',false);
			return false;
		}

        var table = document.getElementById("tbl_dynamic_ticket_master");
        var rowCount = table.rows.length;

        for(var i=0; i<rowCount; i++){

			var row = table.rows[i];
			if(row.cells[0].childNodes[0].checked){

				var first_name = row.cells[2].childNodes[0].value;
				var middle_name = row.cells[3].childNodes[0].value;
				var last_name = row.cells[4].childNodes[0].value;
				var adolescence = row.cells[6].childNodes[0].value;
				var ticket_no = row.cells[7].childNodes[0].value;
				var gds_pnr = row.cells[8].childNodes[0].value;
				var baggage_info = row.cells[9].childNodes[0].value;
				var seat_no = row.cells[10].childNodes[0].value;
				var meal_plan = row.cells[11].childNodes[0].value;
				var main_ticket =($('#reissue_check1:checked').length > 0) ? row.cells[12].childNodes[0].value : '';
				var trip_details = JSON.stringify($('#flight_details'+(i+1)).html());

				first_name_arr.push(first_name);
				middle_name_arr.push(middle_name);
				last_name_arr.push(last_name);
				adolescence_arr.push(adolescence);
				ticket_no_arr.push(ticket_no);
				gds_pnr_arr.push(gds_pnr);
				baggage_info_arr.push(baggage_info);
				main_ticket_arr.push(main_ticket);
				seat_no_arr.push(seat_no);
				meal_plan_arr.push(meal_plan);
				trip_details_arr1.push(trip_details);
			}
		}
		trip_details_arr = JSON.parse(trip_details_arr1[0]);
		var type_of_tour = trip_details_arr[0]['type_of_tour'];


		var payment_date = $('#payment_date').val();
		var flight_sc = $('#flight_sc').val();
        var flight_markup = $('#flight_markup').val();
        var flight_taxes = $('#flight_taxes').val();
        var flight_markup_taxes = $('#flight_markup_taxes').val();
        var flight_tds = $('#flight_tds').val();
		var quotation_id = 0;
		var guest_name = $('#guest_name').val();
		var canc_policy = $('#canc_policy').val();
		var tax_apply_on = $('#tax_apply_on').val();
		var tax_value = $('#tax_value').val();
		var markup_tax_value = $('#markup_tax_value').val();
        var reflections = [];
        reflections.push({
			'flight_sc':flight_sc,
			'flight_markup':flight_markup,
			'flight_taxes':flight_taxes,
			'flight_markup_taxes':flight_markup_taxes,
			'flight_tds':flight_tds,
			'tax_apply_on':tax_apply_on,
			'tax_value':tax_value,
			'markup_tax_value':markup_tax_value
		});
		var bsmValues = [];
		bsmValues.push({
			"basic" : $('#basic_show').find('span').text(),
			"service" : $('#service_show').find('span').text(),
			"markup" : $('#markup_show').find('span').text(),
			"discount" : $('#discount_show').find('span').text(),
		});
		var roundoff = $('#roundoff').val();

		$('#btn_ticket_save').button('loading');
		$('#btn_ticket_save').prop('disabled', true);

		$('#btn_ticket_save').prop('disabled',true);
		$.post(base_url+'view/load_data/finance_date_validation.php', { check_date: payment_date }, function(data){
			if(data !== 'valid'){
				error_msg_alert("The Payment date does not match between selected Financial year.");
				$('#btn_ticket_save').prop('disabled', false);
                $('#btn_ticket_save').button('reset');
				return false;
			}
			else{
				if(customer_id == '0'){
					$.ajax({
						type : 'post',
						url : base_url+'controller/customer_master/customer_save.php',
						data :{ first_name : cust_first_name, middle_name : cust_middle_name, last_name : cust_last_name, gender : gender, birth_date : cust_birth_date, age : age, contact_no : contact_no, email_id : email_id, address : address,address2 : address2,city:city,  active_flag : active_flag ,service_tax_no : service_tax_no, landline_no : landline_no, alt_email_id : alt_email_id,company_name : company_name, cust_type : cust_type,state : state, branch_admin_id : branch_admin_id, country_code : country_code},
						success: function(result){
							var error_arr = result.split('--');
							if(error_arr[0]=='error'){
								error_msg_alert(error_arr[1]);
								$('#btn_ticket_save').button('reset');
								$('#btn_ticket_save').prop('disabled',false);
								return false;
							}
							else{
								saveData();
							}
						}
					});
				}
				else{
					saveData();
				}
			}
		});
		function saveData(){
			$.ajax({
				type:'post',
				url: base_url+'controller/visa_passport_ticket/ticket/ticket_master_save.php',

				data:{ emp_id : emp_id,customer_id : customer_id, tour_type : tour_type, type_of_tour : type_of_tour, adults : adults, childrens : childrens, infant : infant, adult_fair : adult_fair, children_fair : children_fair, infant_fair : infant_fair, basic_cost : basic_cost, markup : markup, discount : discount, yq_tax : yq_tax, other_taxes : other_taxes,service_charge : service_charge, service_tax_subtotal : service_tax_subtotal, service_tax_markup : service_tax_markup, tds : tds, due_date : due_date, ticket_total_cost : ticket_total_cost, payment_date : payment_date, payment_amount : payment_amount, payment_mode : payment_mode, bank_name : bank_name, transaction_id : transaction_id, bank_id : bank_id, first_name_arr : first_name_arr, middle_name_arr : middle_name_arr, last_name_arr : last_name_arr, adolescence_arr : adolescence_arr, seat_no_arr : seat_no_arr,meal_plan_arr:meal_plan_arr,ticket_no_arr : ticket_no_arr, gds_pnr_arr : gds_pnr_arr, baggage_info_arr : baggage_info_arr, branch_admin_id : branch_admin_id,financial_year_id : financial_year_id, reflections : reflections,main_ticket_arr : main_ticket_arr, bsmValues : bsmValues, roundoff : roundoff,credit_charges:credit_charges,credit_card_details:credit_card_details,ticket_reissue : ticket_reissue,control : 'Sale' , quotation_id : quotation_id, canc_policy : canc_policy ,guest_name:guest_name,trip_details_arr1:trip_details_arr1 ,booking_date:booking_date},
				success:function(result){
					$('#btn_ticket_save').button('reset');
					var msg = result.split('--');
					if(msg[0]=="error"){
						error_msg_alert(result);
						$('#btn_ticket_save').prop('disabled', false);
						return false;
					}
					else{
						var msg1 = result.split('-');
						booking_save_message(msg1[0]);
						window.open(base_url+'view/vendor/dashboard/estimate/estimate_save_modal.php?type=Flight&amount='+basic_cost+'&booking_id='+msg1[1]);
						setTimeout(() => {
							if($('#whatsapp_switch').val() == "on") whatsapp_send(emp_id, customer_id, booking_date, base_url);
						}, 1000);
					}
				}
			});
		}
	}
});
function booking_save_message(data) {
	
	var base_url = $('#base_url').val();
	$('#vi_confirm_box').vi_confirm_box({
		false_btn: false,
		message: data,
		true_btn_text: 'Ok',
		callback: function (data1) {
			if (data1 == 'yes') {
				window.location.href = base_url+'view/visa_passport_ticket/ticket/index.php';
			}
		}
	});
}
</script>
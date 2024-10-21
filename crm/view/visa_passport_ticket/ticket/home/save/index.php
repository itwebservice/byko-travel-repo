<?php
include "../../../../../model/model.php";
include_once('../../../../layouts/fullwidth_app_header.php'); 
$branch_status = $_POST['branch_status'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$financial_year_id = $_SESSION['financial_year_id'];
?>
<input type="hidden" id="unique_timestamp" name="unique_timestamp" value="<?= md5(time()) ?>">
<input type="hidden" id="flight_sc" name="flight_sc">
<input type="hidden" id="flight_markup" name="flight_markup">
<input type="hidden" id="flight_taxes" name="flight_taxes">
<input type="hidden" id="flight_markup_taxes" name="flight_markup_taxes">
<input type="hidden" id="flight_tds" name="flight_tds">
<input type="hidden" id="whatsapp_switch" value="<?= $whatsapp_switch ?>" >

<div class="bk_tab_head bg_light">
    <ul> 
        <li>
            <a href="javascript:void(0)" id="tab_1_head" class="active">
                <span class="num" title="Customer Details">1<i class="fa fa-check"></i></span><br>
                <span class="text">Customer Details</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" id="tab_3_head">
                <span class="num" title="Costing">2<i class="fa fa-check"></i></span><br>
                <span class="text">Costing</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" id="tab_4_head">
                <span class="num" title="Advance Receipt">3<i class="fa fa-check"></i></span><br>
                <span class="text">Advance Receipt</span>
            </a>
        </li>
    </ul>
</div>

<div class="bk_tabs">
    <div id="tab1" class="bk_tab active">
        <?php include_once("tab1.php"); ?>  
    </div>
    <div id="tab3" class="bk_tab">
        <?php include_once("tab3.php"); ?>   
    </div>
    <div id="tab4" class="bk_tab">
        <?php include_once("tab4.php"); ?>   
    </div>
</div>

<script src="<?php echo BASE_URL ?>view/visa_passport_ticket/js/ticket.js"></script>
<script src="<?php echo BASE_URL ?>view/visa_passport_ticket/js/ticket_calculation.js"></script>
<script>
$('#customer_id').select2();
$('#ticket_date1').datetimepicker({ timepicker:false, format:'d-m-Y' });
$('#payment_date, #due_date, #birth_date1,#booking_date').datetimepicker({ timepicker:false, format:'d-m-Y' });
$('#ticket_save_modal').modal({backdrop: 'static', keyboard: false});
function business_rule_load(){
	get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','basic','discount');
}


function copy_details(){
	if(document.getElementById("copy_details1").checked){
		var customer_id = $('#customer_id').val();
		var base_url = $('#base_url').val();
		
		if(customer_id == 0){				
			var first_name = $('#cust_first_name').val();
			var middle_name = $('#cust_middle_name').val();
			var last_name = $('#cust_last_name').val();
			var birthdate = $('#cust_birth_date').val();

			if(typeof first_name === 'undefined'){ first_name = '';}
			if(typeof middle_name === 'undefined'){ middle_name = '';}
			if(typeof last_name === 'undefined'){ last_name = '';}
			if(typeof birthdate === 'undefined'){ birthdate = '';}

			var table = document.getElementById("tbl_dynamic_ticket_master");
			var rowCount = table.rows.length;
			var row = table.rows[0];
			if(row.cells[0].childNodes[0].checked){

				row.cells[2].childNodes[0].value = first_name;
				row.cells[3].childNodes[0].value = middle_name;
				row.cells[4].childNodes[0].value = last_name;
				row.cells[5].childNodes[0].value = birthdate;
				adolescence_reflect('birth_date1');
			}
		}
		else{
			$.ajax({
			type:'post',
			url:base_url+'view/load_data/customer_info_load.php',
			data:{customer_id : customer_id},
			success:function(result){
				result = JSON.parse(result);
				var table = document.getElementById("tbl_dynamic_ticket_master");
				var rowCount = table.rows.length;
				var row = table.rows[0];
				if(row.cells[0].childNodes[0].checked)
				{
					row.cells[2].childNodes[0].value = result.first_name;
					row.cells[3].childNodes[0].value = result.middle_name;
					row.cells[4].childNodes[0].value = result.last_name;
					row.cells[5].childNodes[0].value = result.birth_date;
					adolescence_reflect('birth_date1');
				}
			}
			});	
		}
	}
	else{
		var table = document.getElementById("tbl_dynamic_ticket_master");
		var rowCount = table.rows.length;
		var row = table.rows[0];
		row.cells[2].childNodes[0].value = '';
		row.cells[3].childNodes[0].value = '';
		row.cells[4].childNodes[0].value = '';
		row.cells[5].childNodes[0].value = '';
		row.cells[6].childNodes[0].value = '';
	}
}
	function adolescence_reflect(id) 
	{
		var dateString1=$("#"+id).val();
		var today = new Date(); 
		var birthDate = php_to_js_date_converter(dateString1);
		var age = today.getFullYear() - birthDate.getFullYear();
		var m = today.getMonth() - birthDate.getMonth();
		if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
			age--;
		} 

		var millisecondsPerDay = 1000 * 60 * 60 * 24;
		var millisBetween = today.getTime() - birthDate.getTime();
		var days = millisBetween / millisecondsPerDay;

		var count=id.substr(10);	  
		var adl = "";
		var no_days = Math.floor(days);
		
		if(no_days<=730 && no_days>0){ adl = "Infant"; }
		if(no_days>730 && no_days<=4384){ adl = "Children"; }
		if(no_days>4384){ adl = "Adult"; } 
		$('#adolescence'+count).val(adl);
	}
    
	//*******************Get Dynamic Customer Name Dropdown**********************//

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
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>
<?php
include_once('../../../../layouts/fullwidth_app_footer.php');
?> 
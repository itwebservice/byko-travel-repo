<form id="frm_tab3">

<div class="app_panel">

    <div class="">
    <div class="container-fluid">
        <div class="app_panel_content Filter-panel">
				<div class="row mg_tp_20">



				<div class="col-md-12 col-sm-12 col-xs-12 mg_bt_10_xs">

					<div class="panel panel-default panel-body app_panel_style feildset-panel">
						<legend>Basic Amount</legend> 
						

						<div class="row">

							<div class="col-sm-4 col-xs-12 mg_bt_10">
								<span data-original-title="" title="">Adult(s)</span>
								<input type="text" id="adults" name="adults" placeholder="*Adults" title="Adults" onchange="get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','service_charge','discount');" readonly>

							</div>

							<div class="col-sm-4 col-xs-12 mg_bt_10">
								<span data-original-title="" title="">Child(ren)</span>
								<input type="text" id="childrens" name="childrens" placeholder="*Child" onchange="get_auto_values('booking_date','basic_amount','payment_mode','service_charge','markup','save','true','service_charge','discount');" title="Childrens" readonly>

							</div>	

							<div class="col-sm-4 col-xs-12 mg_bt_10">
								<span data-original-title="" title="">Infant(s)</span>
								<input type="text" id="infant" name="infant" placeholder="*Infants" title="Infant" readonly>

							</div>	
							<div class="col-sm-4 col-xs-12 mg_bt_10">
								<span data-original-title="" title="">Adult Amount</span>
								<input type="text" id="adult_fair" name="adult_fair" placeholder="*Adult Fare" title="Adult Fare" onchange="calculate_total_amount(this.id);validate_balance(this.id)">

							</div>	
							<div class="col-sm-4 col-xs-12 mg_bt_10">
								<span data-original-title="" title="">Child Amount</span>
								<input type="text" id="children_fair" name="children_fair" placeholder="*Children Fare" title="Children Fare" onchange="calculate_total_amount(this.id);validate_balance(this.id)">

							</div>				

							<div class="col-sm-4 col-xs-12">
								<span data-original-title="" title="">Infant Amount</span>
								<input type="text" id="infant_fair" name="infant_fair" placeholder="*Infant Fare" title="Infant Fare" onchange="calculate_total_amount(this.id);validate_balance(this.id)">

							</div>

						</div>



					</div>

				</div>



				<div class="col-md-12 col-sm-12 col-xs-12 mg_bt_10_xs">

					<div class="panel panel-default panel-body app_panel_style feildset-panel">
						<legend>Other Calculations</legend> 


						<div class="row">

							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small id="basic_show">&nbsp;</small>
								<input type="text" id="basic_cost" name="basic_cost" placeholder="*Basic Amount" title="Basic Amount" onchange="get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','basic','discount');" readonly>

							</div>
							

							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input type="text" id="yq_tax" name="yq_tax" placeholder="YQ Tax" title="YQ Tax" onchange="calculate_total_amount(this.id);validate_balance(this.id)">

							</div>

							<div class="col-sm-3 col-xs-12 mg_bt_10">
							<small>&nbsp;</small>
								<input type="text" id="other_taxes" name="other_taxes" placeholder="Other Taxes" title="Other Taxes" onchange="calculate_total_amount(this.id);validate_balance(this.id)">

							</div>

							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small id="discount_show">&nbsp;</small>
								<input type="text" id="discount" name="discount" placeholder="Discount" title="Discount" onchange="calculate_total_amount(this.id);validate_balance(this.id);get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','false','discount','discount');">

							</div>
							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small id="service_show">&nbsp;</small>
								<input type="text" id="service_charge" name="service_charge" placeholder="Service Charge" title="Service Charge" onchange="validate_balance(this.id);get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','service_charge','discount')">

							</div>
							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<select title="Tax Apply On" id="tax_apply_on" name="tax_apply_on" class="form-control" onchange="get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','false','discount','discount');">
									<option value="">*Tax Apply On</option>
									<option value="1">Basic Amount</option>
									<option value="2">Service Charge</option>
									<option value="3">Total</option>
								</select>
							</div>
							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<select title="Select Tax" id="tax_value" name="tax_value" class="form-control" onchange="get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','false','discount','discount');">
									<option value="">*Select Tax</option>
									<?php get_tax_dropdown('Income') ?>
								</select>
							</div>

							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input type="text" id="service_tax_subtotal" name="service_tax_subtotal" placeholder="Service Tax" title="Service Tax" onchange="validate_balance(this.id)" readonly>

							</div>		
							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small id="markup_show">&nbsp;</small>
								<input type="text" id="markup" name="markup" placeholder="Markup Amount" title="Markup Amount" onchange="get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','markup','discount');validate_balance(this.id);">
							</div>
							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<select title="Select Markup Tax" id="markup_tax_value" name="markup_tax_value" class="form-control" onchange="get_auto_values('booking_date','visa_issue_amount','payment_mode','service_charge','markup','save','false','service_charge','basic');">
									<option value="">*Select Markup Tax</option>
									<?php get_tax_dropdown('Income') ?>
								</select>
							</div>
							<div class="col-sm-3 col-xs-12 mg_bt_10">
								<small>&nbsp;</small>
								<input type="text" id="service_tax_markup" name="service_tax_markup" placeholder="Tax on Markup" title="Tax on Markup" onchange="validate_balance(this.id)" readonly>

							</div>
							<div class="col-sm-3 col-xs-12 mg_bt_10">
							<small>&nbsp;</small>
								<input type="text" id="tds" name="tds" placeholder="TDS" title="TDS" onchange="validate_balance(this.id);calculate_total_amount(this.id)">

							</div>			 
							<div class="col-sm-3 col-xs-12 mg_bt_10">
							<small>&nbsp;</small>
								<input type="text" name="roundoff" id="roundoff" class="text-right" placeholder="Round Off" title="RoundOff" readonly>
							</div> 
							<div class="col-sm-3 col-xs-12 mg_bt_10">
							<small>&nbsp;</small>
								<input type="text" name="ticket_total_cost" id="ticket_total_cost" placeholder="Net Total" onchange="validate_balance(this.id);" title="Net Amount" class="amount_feild_highlight text-right" readonly>

							</div>

							<div class="col-sm-3 col-xs-12 mg_bt_10">
							<small>&nbsp;</small>
								<input type="text" name="due_date" id="due_date" placeholder="Due Date" title="Due Date" value="<?= date('d-m-Y') ?>" >
							</div>
							<div class="col-sm-3 col-xs-12 mg_bt_10">
							<small>&nbsp;</small>
								<input type="text" name="booking_date" id="booking_date" placeholder="Booking Date" value="<?= date('d-m-Y') ?>" title="Booking Date" onchange="check_valid_date(this.id);get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','service_charge','discount',true);">
							</div>

						</div>



					</div>

				</div>



			</div>

			<div class="row">
				<div class="col-md-12 col-sm-4 col-xs-12 mg_bt_10">
					<h3 class="editor_title">Cancellation Policy</h3>
					<textarea id="canc_policy" name="canc_policy" class="feature_editor"></textarea>
				</div>
			</div>
			<div class="row text-center mg_tp_20">
				<div class="col-xs-12">
					<button class="btn btn-info btn-sm ico_left" type="button" onclick="switch_to_tab2()"><i class="fa fa-arrow-left"></i>&nbsp;&nbsp;Previous</button>
					&nbsp;&nbsp;
					<button class="btn btn-info btn-sm ico_right">Next&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
				</div>
			</div>
        </div>
    </div>
	</div>
</div>
</form>



<script>
$(function(){

	$('#frm_tab3').validate({

		rules:{

				adults: { required : true, number : true },
				childrens: { required : true, number : true },
				infant: { required : true, number : true },
				adult_fair: { required : true, number : true },
				children_fair: { required : true, number : true },
				infant_fair: { required : true, number : true },
				basic_cost: { required : true, number : true },
				basic_cost_markup: {number : true },
				markup : { required : true },
				ticket_total_cost: { required : true, number : true },
				booking_date : { required : true},
				tax_apply_on : { required:true},
				tax_value : { required:true},
				markup_tax_value : { required:true}
		},

		submitHandler:function(form){
			var base_url = $('#base_url').val();
			//Validation for booking and payment date in login financial year
			var check_date1 = $('#booking_date').val();
				$.post(base_url+'view/load_data/finance_date_validation.php', { check_date: check_date1 }, function(data){
					if(data !== 'valid'){
						error_msg_alert("The Booking date does not match between selected Financial year.");
						return false;
					}else{
						$('#tab_3_head').addClass('done');
						$('#tab_4_head').addClass('active');
						$('.bk_tab').removeClass('active');
						$('#tab4').addClass('active');
						$('html, body').animate({ scrollTop: $('.bk_tab_head').offset().top }, 200);
					}
				});

		}

	});

});
function switch_to_tab2(){ 
	$('#tab_3_head').addClass('done');
	$('#tab_1_head').addClass('active');
	$('.bk_tab').removeClass('active');
	$('#tab1').addClass('active');
	$('html, body').animate({ scrollTop: $('.bk_tab_head').offset().top }, 200);
}
</script>




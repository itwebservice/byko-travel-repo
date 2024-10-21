<?php
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_POST['branch_status'];
?>
<form id="frm_tab1">
<div class="app_panel">

<div class="">
	<div class="container-fluid">
	<div class="app_panel_content Filter-panel">
		<div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
		<legend>Customer Details</legend>
			<div class="row">
				<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
					<select name="customer_id" id="customer_id" class="customer_dropdown" title="Customer Name" style="width:100%" disabled>
						<?php 
						$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_ticket[customer_id]'"));
						if($sq_customer['type']=='Corporate'){
						?>
							<option value="<?= $sq_customer['customer_id'] ?>"><?= $sq_customer['company_name'] ?></option>
						<?php }  else{ ?>
							<option value="<?= $sq_customer['customer_id'] ?>"><?= $sq_customer['first_name'].' '.$sq_customer['last_name'] ?></option>
						<?php } ?>
						<?php get_customer_dropdown($role,$branch_admin_id,$branch_status); ?>
					</select>
					<script>
						customer_info_load();
					</script>
				</div>	
				<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
				<input type="text" id="mobile_no" name="mobile_no" placeholder="Mobile No" title="Mobile No" readonly>
				</div>
				<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
				<input type="text" id="email_id" name="email_id" placeholder="Email ID" title="Email ID" readonly>
				</div>
				<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
				<input type="text" id="company_name" class="hidden" name="company_name" title="Company Name" placeholder="Company Name" title="Company Name" readonly>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-sm-6 col-xs-12">
				<select name="tour_type" id="tour_type" title="Travelling Type" disabled>
					<option value="<?= $sq_ticket['tour_type'] ?>"><?= $sq_ticket['tour_type'] ?></option>
					<option value="">Travelling Type</option>
					<option value="Domestic">Domestic</option>
					<option value="International">International</option>
				</select>
				</div>	
				<div class="col-md-6 col-sm-6 col-xs-12 mg_bt_10">
				<input type="text" id="guest_name" name="guest_name" title="Guest Name and contact number" placeholder="Guest Name and contact number" value="<?= $sq_ticket['guest_name'] ?>">
				</div>
			</div>
			<div class="row mg_tp_20">
				<div class="col-md-3">
					<?php
					$checked = ($sq_ticket['ticket_reissue'] == 1) ? 'checked' : ''; ?>
					<input id="reissue_check1" name="reissue_check1" type="checkbox" onClick="ticket_reissue();" <?= $checked ?>>
					&nbsp;&nbsp;<label for="reissue_check1">Reissue Ticket</label>
				</div>
			</div>
		</div>
		<h3 class="editor_title">Passenger Details</h3>
		<div class="panel panel-default panel-body app_panel_style">
			<div class="row mg_bt_10">
				<div class="col-sm-4 col-xs-12 mg_bt_10_xs">
				</div>
				<div class="col-sm-8 col-xs-12 text-right">
                <span style="color: red;line-height: 35px;" data-original-title="" title="" class="note">Please add multiple seat number of multi trip using '/' in between like S1/D3/NA. And similar for Meal plan field.</span>
                    <button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_dynamic_ticket_master')"><i class="fa fa-plus"></i></button>
				</div>
			</div>    

			<div class="row">
				<div class="col-xs-12">
					<div class="table-responsive">
					<?php $offset = "_u"; ?>
					<table id="tbl_dynamic_ticket_master" name="tbl_dynamic_ticket_master" class="table no-marg">
					<?php include_once('ticket_master_tbl.php'); ?>                        
					</table>
					</div>
				</div>
			</div>
		</div>   
		
		<div class="row text-center mg_pt_20">
			<div class="col-xs-12">
				<button class="btn btn-info btn-sm ico_right">Next&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
			</div>
		</div>
        <div id="div_flightd_modal"></div>
    </div>
	</div>
</div>
</div>
</form>

<script>
function ticket_reissue(){

	var checkedValue = document.getElementById("reissue_check1").checked
	var main_ticket = document.getElementsByClassName("main_ticket");
	if(checkedValue === false){
		$('.main_ticket').attr('type','hidden');
	}
	else {
		$('.main_ticket').attr('type','text');
	}
}
function add_flight_details(passenger_id,type){

	var count = passenger_id.substring(9);
	$('#add_trips'+count).prop('disabled',true);

	var base_url = $('#base_url').val();
	var first_name = $('#first_name'+count).val();
	var last_name = $('#last_name'+count).val();
	var flight_details = $('#flight_details'+count).html();
	var entry_id = $('#entry_id'+count).val();
	if(first_name == ''){
		error_msg_alert("Enter passenger's First name!");
		$('#add_trips'+count).prop('disabled',false);
		return false;
	}
	$('#add_trips'+count).button('loading');
	$.post(base_url+'view/visa_passport_ticket/ticket/home/add_flight_details.php', { first_name:first_name, last_name:last_name, flight_details:flight_details,count:count,type:type,entry_id:entry_id }, function(data){
		$('#div_flightd_modal').html(data);
		$('#add_trips'+count).button('reset');		
		$('#add_trips'+count).prop('disabled',false);
	});
}
$('#frm_tab1').validate({
	rules:{
		customer_id : { required : true },
		tour_type : { required : true },
	},
	submitHandler:function(form,e){

		e.preventDefault();
        var adults = 0;
        var childrens = 0;
        var infant = 0;
        var msg = "";     
        var adult_total = 0;
        var child_total = 0;
        var infant_total = 0;
        var table = document.getElementById("tbl_dynamic_ticket_master");
        var rowCount = table.rows.length;
        
		let checkedRowCount = 0;
        for(var i=0; i<rowCount; i++)
        {
			var row = table.rows[i];
			if(row.cells[0].childNodes[0].checked)
			{
				var basic_fare_total = 0;
				var basic_fare_arr = [];
				var trip_data_check_arr = [];
				var cancel_status_arr = [];
				var first_name = row.cells[2].childNodes[0].value;
				var middle_name = row.cells[3].childNodes[0].value;
				var last_name = row.cells[4].childNodes[0].value;
				var adolescence = row.cells[6].childNodes[0].value;
				var ticket_no = row.cells[7].childNodes[0].value;
				var gds_pnr = row.cells[8].childNodes[0].value;
            	var main_ticket = row.cells[12].childNodes[0].value;

				var first_name_id = row.cells[2].childNodes[0].id;
				var count = first_name_id.substring(10);
    			var trip_details = $('#flight_details'+count).html();

				if(first_name==""){ msg +="First name is required in row:"+(i+1)+"<br>"; }
				if(adolescence==""){ msg +="Adolescence is required in row:"+(i+1)+"<br>"; }
				if(trip_details == "" || trip_details == null){ msg +="Flight Ticket Details required in row:"+(i+1)+"<br>"; }
				if(row.cells[12].childNodes[0].getAttribute('type')=="text" && main_ticket==""){ 
					error_msg_alert("Main Ticket Number is required in row:"+(i+1));
					return false;
				}

				var flight_arr = JSON.parse(trip_details)[0];
				basic_fare_arr = flight_arr['basic_fare_arr'];
				trip_data_check_arr = flight_arr['trip_data_check_arr'];
				cancel_status_arr = (flight_arr['cancel_status_arr'] === undefined) ? [] : flight_arr['cancel_status_arr'];
				for(var t = 0; t < (basic_fare_arr).length ; t++){

					if(basic_fare_arr[t]==''){basic_fare_arr[t] = 0;}
					if(cancel_status_arr[t]==''){cancel_status_arr[t] = 0;}
					if(trip_data_check_arr[t] == true && (cancel_status_arr[t] == '' || cancel_status_arr[t] == '0')){
						
						basic_fare_total += parseFloat(basic_fare_arr[t]);
					}
				}
				if(adolescence=="Adult"){
					adults = adults + 1;
					adult_total += basic_fare_total;
				}
				if(adolescence=="Child"){
					childrens = childrens + 1;
					child_total += basic_fare_total;
				}
				if(adolescence=="Infant"){
					infant = infant + 1;
					infant_total += basic_fare_total;
				}
				checkedRowCount++;
			}
        }
		if (checkedRowCount < 1) {
			error_msg_alert('Atleast one passenger is required!');
			return false;
		}

        if(msg!=""){
        	error_msg_alert(msg);
        	return false;
        }

		$('#adults').val(adults);
		$('#childrens').val(childrens);
		$('#infant').val(infant);

		$('#adult_fair').val(adult_total);
		$('#children_fair').val(child_total);
		$('#infant_fair').val(infant_total);
	
		calculate_total_amount('basic_cost');

		$('#tab_1_head').addClass('done');
		$('#tab_3_head').addClass('active');
		$('.bk_tab').removeClass('active');
		$('#tab3').addClass('active');
		$('html, body').animate({ scrollTop: $('.bk_tab_head').offset().top }, 200);
	}
});
</script>
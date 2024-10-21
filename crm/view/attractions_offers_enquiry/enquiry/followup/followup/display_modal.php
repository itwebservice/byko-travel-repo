<?php
include "../../../../../model/model.php";
$login_id = $_SESSION['login_id'];
$role = $_SESSION['role'];
$financial_year_id = $_SESSION['financial_year_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$emp_id = $_SESSION['emp_id'];
$enquiry_id = $_POST['enquiry_id'];

$q = "select * from branch_assign where link='attractions_offers_enquiry/enquiry/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';

$sq_enquiry = mysqli_fetch_assoc(mysqlQuery("Select * from enquiry_master where enquiry_id =". $enquiry_id));
?>
<input type="hidden" id="enquiry_id" name="enquiry_id" value="<?= $enquiry_id ?>"> 
<form id="frm_followup_reply">
<div class="modal fade" id="followup_save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document" style="width:60%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Followup</h4>
      </div>
      <div class="modal-body">
      <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">
    <legend>New Followup</legend>
		<div class="row">
        <div class="col-md-3 col-sm-6 mg_bt_10">
          <select name="followup_status" id="followup_status" title="Followup Status" class="form-control" onchange="followup_type_reflect(this.value)">
            <option value="">*Status</option>
            <!-- <option value="Active">Active</option> -->
						<option value="In-Followup">In-Followup</option>
            <option value="Dropped">Dropped</option>
            <option value="Converted">Converted</option>
          </select>
        </div>
        <div class="col-md-3 col-sm-6 mg_bt_10">
          <select id="followup_type" name="followup_type" title="Followup Type" class="form-control">
              <option value="">*Type</option>
          </select>
        </div>
        <div class="col-md-3 col-sm-6 mg_bt_10">
            <select name="cust_state" id="cust_state" title="State/Country Name" style="width : 100%" class="form-control">
              <?php get_states_dropdown() ?>
            </select>
        </div>
        <div class="col-md-3 col-sm-6 mg_bt_10">
            <select name="quotation_id" id="quotation_id" title="Quotation ID" style="width : 100%" class="form-control <?= $qtn_class ?>">
					<option value="">Select Quotation</option>
					<?php
					$query = "select * from package_tour_quotation_master where status='1' and enquiry_id = '$enquiry_id' order by quotation_id desc";
					if ($branch_status == 'yes') {
						if ($role == 'Branch Admin' || $role == 'Accountant' || $role_id > '7') {
							$query = "select * from package_tour_quotation_master where status='1' and branch_admin_id='$branch_admin_id' order by quotation_id desc";
						} elseif ($role != 'Admin' && $role != 'Branch Admin' && $role_id != '7' && $role_id < '7') {
							$query = "select * from package_tour_quotation_master where status='1' and emp_id='$emp_id' and branch_admin_id='$branch_admin_id' order by quotation_id desc";
						}
					} elseif ($role != 'Admin' && $role != 'Branch Admin' && $role_id != '7' && $role_id < '7') {
						$query = "select * from package_tour_quotation_master where status='1' and emp_id='$emp_id' order by quotation_id desc";
					}
					$sq_quotation = mysqlQuery($query);
					while ($row_quotation = mysqli_fetch_assoc($sq_quotation)) {

						$sq_cost =  mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_costing_entries where quotation_id = '$row_quotation[quotation_id]'"));
						$basic_cost = $sq_cost['basic_amount'];
						$service_charge = $sq_cost['service_charge'];
						$tour_cost = $basic_cost + $service_charge;
						$service_tax_amount = 0;
						$tax_show = '';
						$bsmValues = json_decode($sq_cost['bsmValues']);
						$discount_in = $sq_cost['discount_in'];
						$discount = $sq_cost['discount'];
						if($discount_in == 'Percentage'){
							$act_discount = floatval($service_charge) * floatval($discount) / 100;
						}else{
							$act_discount = $discount;
						}
						$service_charge = $service_charge - floatval($act_discount);

						if ($sq_cost['service_tax_subtotal'] !== 0.00 && ($sq_cost['service_tax_subtotal']) !== '') {
							$service_tax_subtotal1 = explode(',', $sq_cost['service_tax_subtotal']);
							for ($i = 0; $i < sizeof($service_tax_subtotal1); $i++) {
								$service_tax = explode(':', $service_tax_subtotal1[$i]);
								$service_tax_amount +=  $service_tax[2];
								$name .= $service_tax[0] . ' ';
								$percent = $service_tax[1];
							}
						}
						if ($bsmValues[0]->service != '') {   //inclusive service charge
							$newBasic = $tour_cost + $service_tax_amount;
							$tax_show = '';
						} else {
							$tax_show =  $name . $percent . ($service_tax_amount);
							$newBasic = $tour_cost;
						}
						////////////Basic Amount Rules
						if ($bsmValues[0]->basic != '') { //inclusive markup
							$newBasic = $tour_cost + $service_tax_amount;
							$tax_show = '';
						}
						$quotation_cost = $basic_cost + $service_charge + $service_tax_amount + $row_quotation['train_cost'] + $row_quotation['cruise_cost'] + $row_quotation['flight_cost'] + $row_quotation['visa_cost'] + $row_quotation['guide_cost'] + $row_quotation['misc_cost'];

						//Currency conversion
						$currency_amount1 = currency_conversion($currency, $row_quotation['currency_code'], $quotation_cost);
						if ($row_quotation['currency_code'] != '0' && $currency != $row_quotation['currency_code']) {
							$currency_amount = ' (' . $currency_amount1 . ')';
						} else {
							$currency_amount = '';
						}
						$yr = explode("-", $row_quotation['quotation_date']);
						$year = $yr[0];
					?>
					<option value="<?= $row_quotation['quotation_id'] ?>"><?php echo get_quotation_id($row_quotation['quotation_id'],$year) . ' : ' . $row_quotation['customer_name'] . ' : ' . $quotation_cost . ' /-' . $currency_amount ?>
					</option>
					<?php
					}
					?>
				</select>
        	</div>
        </div>
		<div class="row">
			<div class="col-md-3 col-sm-6 mg_bt_10">
				<input type="text" id="followup_date" name="followup_date" placeholder="Next Followup Date" title="Next Followup Date" value="<?= date('d-m-Y H:i')?>" style="min-width:136px;" class="form-control">
			</div>        
			<div class="col-md-3 col-sm-6 mg_bt_10">
				<select name="followup_stage" id="followup_stage" title="Stage" data-toggle="tooltip" class="form-control">
				<option value="">Stage</option>
				<option value="<?= "Strong" ?>">Strong</option>
				<option value="<?= "Hot" ?>">Hot</option>
				<option value="<?= "Cold" ?>">Cold</option>
				</select>
			</div>
		</div>
		<div class="row mg_bt_10">
			<div class="col-md-12">
			<textarea id="followup_reply" name="followup_reply" onchange="validate_spaces(this.id);" placeholder="*Followup Description" class="form-control"></textarea>
			</div>
		</div>
		<div class="row text-center mg_bt_20">
			<div class="col-md-12">
				<button class="btn btn-sm btn-success" id="btn_followup_reply"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
			</div>
		</div>
    </div>
    <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">
    <legend>Followup History</legend>

    <div class="row mg_bt_20 text-right">
		<div class="col-md-3 col-sm-6 col-md-offset-5">
				<select name="enquiry_type" id="enquiry_type" title="Enquiry For" class="form-control form-control-visible" disabled>
						<option value="<?= $sq_enquiry['enquiry_type'] ?>"><?= $sq_enquiry['enquiry_type'] ?></option>
				</select>
		</div>
		<div class="col-md-3 col-sm-6">
				<input type="text" class="form-control form-control-visible" id="txt_name" name="txt_name" onchange="name_validate(this.id)" placeholder="Customer Name" title="Customer Name" value="<?= $sq_enquiry['name'] ?>" readonly>
		</div>
</div>
<div class="row"> 
	<div class="col-md-12"> 
		<ul class="followup_entries main_block mg_tp_20 mg_bt_0">
				<?php
				$count = 0;
				$sq_followup_entries = mysqlQuery("select * from enquiry_master_entries where enquiry_id='$enquiry_id'");
				while($row_entry = mysqli_fetch_assoc($sq_followup_entries)){
					$bg = $row_entry['followup_stage'];
					$sq_enq = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master where enquiry_id='$row_entry[enquiry_id]'"));
					$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$sq_enq[assigned_emp_id]'"));
					?>

					<li class="main_block <?= $bg ?>">
						<div class="single_folloup_entry main_block mg_bt_20">
							<div class="col-sm-3 entry_detail"><?= date('d-m-Y H:i', strtotime($row_entry['created_at'])) ?></div>
							<div class="col-sm-2 entry_detail"><?= $row_entry['followup_type'] ?></div>
							<div class="col-sm-2 entry_detail"><?= $row_entry['followup_status'] ?></div>
							<div class="col-sm-3 entry_detail"><?= date('d-m-Y H:i', strtotime($row_entry['followup_date'])) ?></div>
							<div class="col-sm-2 entry_detail"><?= $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></div>
							<div class="col-sm-12 entry_discussion">
								<p><?= $row_entry['followup_reply'] ?></p>
							</div>
						</div>
					</li>
					<?php } ?>
		</ul>
		<div class="col-md-12 no-pad text-right">
			<ul class="color_identity no-pad no-marg">
				<li>
					<span class="identity_color cold"></span>
					<span class="identity_name">Cold</span>
				</li>
				<li>
					<span class="identity_color hot"></span>
					<span class="identity_name">Hot</span>
				</li>
				<li>
					<span class="identity_color strong"></span>
					<span class="identity_name">Strong</span>
				</li>
			</ul>
		</div>
	</div>
</div>
</div>
	 </div>
    </div>
  </div>
</div>
    </form>
 <script>
    $('#followup_save_modal').modal( {keyboard: false,  backdrop: 'static'});
	$('#followup_date').datetimepicker({ format:'d-m-Y H:i' });
	$(function(){
  	$('#frm_followup_reply').validate({
    rules:{
      followup_reply: "required",
      followup_date:"required",
      followup_type: "required",
      followup_status: "required",
	  cust_state: "required"
    },
    submitHandler:function(form,event){
    event.preventDefault();
	$('#btn_followup_reply').prop('disabled', true);
    var enquiry_id = $("#enquiry_id").val(); 
    var followup_reply = $("#followup_reply").val(); 
    var followup_date = $('#followup_date').val();
    var followup_type = $('#followup_type').val();
    var followup_status = $('#followup_status').val();
    var followup_stage = $('#followup_stage').val();
    var cust_state = $('#cust_state').val();
    var quotation_id = $('#quotation_id').val();
	var base_url = $('#base_url').val();
	
	if(followup_status=='Converted'){
		if(cust_state=='' || cust_state==undefined){
			error_msg_alert("Please select state/Country");
			$('#btn_followup_reply').prop('disabled', false);
			return false;
		}
		if(enquiry_type == 'Package Booking' && quotation_id == '' || quotation_id == undefined){
			error_msg_alert("Please select Quotation ID");
			$('#btn_followup_reply').prop('disabled', false);
        	return false;
		}
		$.ajax({
			type: 'post',
			url: 'followup/followup/enquiry_info_load.php',
			data: { enquiry_id: enquiry_id },
			success: function (result) {
				var response = JSON.parse(result);
				
				var first_name = response.first_name;
				var middle_name = response.middle_name;
				var last_name = response.last_name;
			 	var gender = response.gender;
				var birth_date = response.birth_date;
			  	var age = response.age;
			   	var contact_no = response.landline_no;
				var email_id = response.email_id;
				var address = response.address;
				var address2 = response.address2;
				var city = response.city;
				var active_flag = response.active_flag;
				var service_tax_no = response.service_tax_no;
				var landline_no = response.contact_no;
				var alt_email_id = response.alt_email_id;
				var company_name = response.company_name;
				var cust_type = response.type;
				var state = cust_state;
				var branch_admin_id = response.branch_admin_id;
				var country_code = response.country_code;
				$('#btn_followup_reply').button('loading');
				$.ajax({
				type: 'post',
				url: base_url + 'controller/customer_master/customer_save.php',
				data: {
					first_name: first_name, middle_name: middle_name,
					last_name: last_name, gender: gender, birth_date: birth_date,age: age, contact_no: contact_no,email_id: email_id,address: address,address2: address2,city: city, active_flag: active_flag, service_tax_no: service_tax_no,landline_no: landline_no,alt_email_id: alt_email_id, company_name: company_name,cust_type: cust_type, state: state, branch_admin_id: branch_admin_id,country_code: country_code
				},
				success: function(result) {
					$.post( 
					base_url+"controller/attractions_offers_enquiry/followup_reply_save.php",
					{ enquiry_id : enquiry_id, followup_reply : followup_reply, followup_date : followup_date, followup_type : followup_type, followup_status : followup_status, followup_stage : followup_stage ,cust_state:cust_state,quotation_id:quotation_id},
					function(data) {  
							msg_alert(data);
							$('#followup_save_modal').modal('hide');
							enquiry_proceed_reflect();
						});
				}
				});
			}
		});
	}else{
		$.post( 
               base_url+"controller/attractions_offers_enquiry/followup_reply_save.php",
               { enquiry_id : enquiry_id, followup_reply : followup_reply, followup_date : followup_date, followup_type : followup_type, followup_status : followup_status, followup_stage : followup_stage ,cust_state:cust_state,quotation_id:quotation_id},
               function(data) {  
                      msg_alert(data);
                      $('#followup_save_modal').modal('hide');
                      enquiry_proceed_reflect();
        		});
	}
    
    
        
    }
  });
});

</script>

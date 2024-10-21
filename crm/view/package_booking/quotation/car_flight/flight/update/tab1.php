<form id="frm_tab11">

	<div class="row">

		<input type="hidden" id="quotation_id1" name="quotation_id1" value="<?= $quotation_id ?>">

		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

			<input type="hidden" id="login_id" name="login_id" value="<?= $login_id ?>">

			<select name="enquiry_id1" id="enquiry_id1" style="width:100%" onchange="get_flight_enquiry_details('1')">

				<?php 

				$sq_enq = mysqli_fetch_assoc(mysqlQuery("select * from enquiry_master where enquiry_id='$sq_quotation[enquiry_id]' and enquiry_type='Flight Ticket'"));

					?>

					<option value="<?= $sq_enq['enquiry_id'] ?>">Enq<?= $sq_enq['enquiry_id'] ?> : <?= $sq_enq['name'] ?></option>
					<option value="0"><?= "New Enquiry" ?></option>
					<?php
				if($role=='Admin'){
					$sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Flight Ticket') and status!='Disabled' order by enquiry_id desc");
				}else{
					if($branch_status=='yes'){
						if($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
							$sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Flight Ticket') and status!='Disabled' and branch_admin_id='$branch_admin_id' order by enquiry_id desc");
						}
						elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
							$q = "select * from enquiry_master where enquiry_type in('Flight Ticket') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
							$sq_enq = mysqlQuery($q);
						}
					}
					elseif($branch_status!='yes' && ($role=='Branch Admin' || $role_id=='7')){
						
						$sq_enq = mysqlQuery("select * from enquiry_master where enquiry_type in('Flight Ticket') and status!='Disabled' order by enquiry_id desc");
					}
					elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
						$q = "select * from enquiry_master where enquiry_type in('Flight Ticket') and assigned_emp_id='$emp_id' and status!='Disabled' order by enquiry_id desc";
						$sq_enq = mysqlQuery($q);
					}
				}

				while($row_enq = mysqli_fetch_assoc($sq_enq)){

					$sq_enq1 = mysqli_fetch_assoc(mysqlQuery("SELECT followup_status FROM `enquiry_master_entries` WHERE `enquiry_id` = '$row_enq[enquiry_id]' ORDER BY `entry_id` DESC"));
					if($sq_enq1['followup_status'] != 'Dropped'){
					?>

					<option value="<?= $row_enq['enquiry_id'] ?>">Enq<?= $row_enq['enquiry_id'] ?> : <?= $row_enq['name'] ?></option>

				<?php
					}
				}

				?>

			</select>

		</div>	

		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

	      <input type="text" class="form-control" id="customer_name1" name="customer_name1" onchange="fname_validate(this.id);"  placeholder="Customer Name" title="Customer Name" value="<?= $sq_quotation['customer_name'] ?>"> 

	    </div>	 
		<div class="col-md-3 col-sm-6">
			<div class="col-md-4" style="padding-left:0px;">
				<input type="hidden" id="cc_value" value="<?= $sq_quotation['country_code'] ?>">
				<select style="width:125px !important;" class="form-control" name="country_code1" id="country_code1" title="Country code">
					<?= get_country_code(); ?>
				</select>
			</div>
			<div class="col-md-8" style="padding-left:12px;padding-right:0px;">
				<input type="text" class="form-control" id="mobile_no1" onchange="mobile_validate(this.id);"
					name="mobile_no1" placeholder="WhatsApp No" title="WhatsApp No"
					value="<?= $sq_quotation['whatsapp_no'] ?>">
			</div>
		</div>       		                			        		        	        		


		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

			<input type="text" id="email_id1" name="email_id1" placeholder="Email ID" title="Email ID" value="<?= $sq_quotation['email_id'] ?>">

		</div>	

		<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

	    	<input type="text" class="form-control" id="quotation_date1" name="quotation_date1" placeholder="Quotation Date" title="Quotation Date" value="<?= get_date_user($sq_quotation['quotation_date']) ?>" onchange="get_auto_values('quotation_date1','subtotal1','payment_mode','service_charge1','markup_cost1','update','true','service_charge', true);"> 

	    </div>
		<div class="col-md-3 col-sm-6">
		<?php
		$status = ($sq_quotation['status'] == '1') ? 'Active' : 'Inactive';
		?>
		<select class="<?= $active_inactive_flag ?>" name="active_flag1" id="active_flag1" title="Status">
		<option  value="<?php echo $sq_quotation['status']; ?>"><?php echo $status; ?></option>
			<option value="1">Active</option>
			<option value="0">Inactive</option>
		</select>
		</div>
	</div>	

	<div class="row text-center mg_tp_20">

		<div class="col-xs-12">

			<button class="btn btn-info btn-sm ico_right">Next&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>

		</div>

	</div>

</form>



<script>
$('#travel_datetime1').datetimepicker({format:'d-m-Y H:i' });
$('#country_code1').val($('#cc_value').val()).select2();

 

$('#frm_tab11').validate({

	rules:{

		country_code1 : { required : true}		 

	},

	submitHandler:function(form){

		var customer_name = $('#customer_name1').val();
		if(customer_name == ''){
			error_msg_alert("Please Enter Customer Name");
			return false;
		}

		$('a[href="#tab_2"]').tab('show');



	}

});

</script>


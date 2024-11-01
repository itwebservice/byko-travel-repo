<?php
include "../../../model/model.php";
?>

<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-12">
			<select name="booking_id" id="booking_id" style="width:100%" title="Select Booking" onchange="content_reflect()">
		        <option value="">Select Booking</option>
		        <?php 
		        $sq_b2b = mysqlQuery("select * from b2b_booking_master order by booking_id desc");
		        while($row = mysqli_fetch_assoc($sq_b2b)){

		          $date = $row['created_at'];
		          $yr = explode("-", $date);
		          $year =$yr[0];
		          $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row[customer_id]'"));
				  if($row['agent_flag'] == '0'){
					  $sq_agent = mysqlI_fetch_assoc(mysqlQuery("select full_name from b2b_users where id='$row[user_id]'"));
					  $emp_name = ' ('.$sq_agent['full_name'].')';
				  }else{
					  $emp_name = '';
				  }
		          ?>
		          <option value="<?= $row['booking_id'] ?>"><?= get_b2b_booking_id($row['booking_id'],$year).' : '.$sq_customer['company_name'].$emp_name ?></option>
		          <?php
		        }
		        ?>
		    </select>
		</div>
	</div>
</div>


<div id="div_cancel_b2b" class="main_block"></div>


<script>
$('#booking_id').select2();
function content_reflect()
{
	var booking_id = $('#booking_id').val();
	if(booking_id != ''){
		$.post('cancel/content_reflect.php', { booking_id : booking_id }, function(data){
			$('#div_cancel_b2b').html(data);
		});
	}
	else{
		$('#div_cancel_b2b').html('');
	}
}
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
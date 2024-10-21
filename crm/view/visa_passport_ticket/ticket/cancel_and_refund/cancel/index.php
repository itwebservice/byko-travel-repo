<?php
include "../../../../../model/model.php";
?>
<div class="app_panel_content Filter-panel">
	<div class="row">
		<div class="col-md-3 col-md-offset-3 col-sm-4 col-sm-offset-4 col-xs-12">
			<select name="ticket_id" id="ticket_id" style="width:100%" onchange="get_cancel_status();" title="Select Booking">
		        <option value="">*Select Booking</option>
		        <?php 
		        $sq_ticket = mysqlQuery("select * from ticket_master where delete_status='0' order by ticket_id desc");
		        while($row_ticket = mysqli_fetch_assoc($sq_ticket)){

		        $date = $row_ticket['created_at'];
				$yr = explode("-", $date);
				$year =$yr[0];
				$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_ticket[customer_id]'"));
				if($sq_customer['type'] == 'Corporate'||$sq_customer['type']=='B2B'){
					$cust_name = $sq_customer['company_name'];
				}else{
					$cust_name = $sq_customer['first_name'].' '.$sq_customer['last_name'];
				}
				?>
				<option value="<?= $row_ticket['ticket_id'] ?>"><?= get_ticket_booking_id($row_ticket['ticket_id'],$year).' : '.$cust_name ?></option>
				<?php
		        }
		        ?>
		    </select>
		</div>
		<div class="col-md-3 col-sm-4 col-xs-12">
			<select name="sales_return" id="sales_return" style="width:100%" onchange="content_reflect()" title="Sales Return" data-toggle="tooltip">
		        <option value="">*Sales Return</option>
		        <option value="1">Full</option>
		        <option value="2">Passenger wise</option>
		        <option value="3">Sector wise</option>
		    </select>
		</div>
	</div>
</div>

<div id="div_cancel_ticket" class="main_block"></div>


<script>
$('#ticket_id').select2();
function content_reflect()
{
	var ticket_id = $('#ticket_id').val();
	var sales_return = $('#sales_return').val();
	if(ticket_id != '' && sales_return != ''){
		$.post('cancel/content_reflect.php', { ticket_id : ticket_id, sales_return : sales_return }, function(data){
			
			$('#div_cancel_ticket').html(data);
		});
	}else{
		$('#div_cancel_ticket').html('');
	}
}
function get_cancel_status(){
	
	var base_url = $('#base_url').val();
	var ticket_id = $('#ticket_id').val();
	var html = '<option value="">*Sales Return</option><option value="1">Full</option><option value="2">Passenger wise</option><option value="3">Sector wise</option>';
	if(ticket_id != ''){
		$.post(base_url+'view/load_data/get_sale_cancel_status.php', { booking_id : ticket_id, table_name : 'ticket_master',col_name : 'ticket_id' }, function(data){
			if(data != 0){
				$('#sales_return').html(data);
			}else{
				$('#sales_return').html(html);
			}
		});
	}else{
		$('#sales_return').html(html);
	}
	setTimeout(()=>{ content_reflect(); } ,700);
}
</script>
<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
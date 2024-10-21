<?php
include "../../model/model.php";
$branch_admin_id = $_SESSION['branch_admin_id'];
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$branch_status = $_GET['branch_status'];
$email_id = $_GET['email_id'];

$query = "SELECT * from `hotel_quotation_master` where 1 and status='1'";
if($role != 'Admin' && $role!='Branch Admin'){
	$query .= " and emp_id='$emp_id'";
}
if($branch_status=='yes' && $role=='Branch Admin'){
	$query .= " and branch_admin_id = '$branch_admin_id'";
}
if($branch_admin_id != '' && $role=='Branch Admin'){
	$query .= " and branch_admin_id = '$branch_admin_id'";
}
$query .= ' ORDER BY `quotation_id` DESC';
$sq_query = mysqlQuery($query);
?>
<div class="modal fade" id="quotation_send_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-lg"  role="document">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">Send Quotation</h4>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-xs-12">
					<input type="checkbox" id="check_all" name="check_all" onClick="select_all_check(this.id,'custom_package')">&nbsp;&nbsp;&nbsp;<span style="text-transform: initial;">Check All</span>
				</div>
			</div>
		<div class="row">
		<div class="col-xs-12">
			<div class="table-responsive">
			<table class="table table-hover table-bordered no-marg" id="tbl_tour_list">
		    <tr class="table-heading-row">
				<th></th>
				<th>SR No.</th>
				<th>Quotation ID</th>
				<th>Customer Name</th>
				<th>Quotation Cost</th>
		    </tr> 
		    <?php 
		    $quotation_cost = 0;  $count  = 1;
		    while($row_tours = mysqli_fetch_assoc($sq_query)){
		    	$costDetails = isset($row_tours['costing_details']) ? json_decode($row_tours['costing_details'],true) : [];
                $enqDetails = isset($row_tours['enquiry_details']) ? json_decode($row_tours['enquiry_details']) : [];
                $hotelDetails = isset($row_tours['hotelDetails']) ? json_decode($row_tours['hotelDetails'],true) : [];
				$quotation_date = $row_tours['quotation_date'];
				$yr = explode("-", $quotation_date);
				$year = $yr[0];
				$total_cost = '';
				if($email_id == $enqDetails->email_id){

					for($index = 0; $index<sizeof($costDetails); $index++){

						$data = isset($costDetails[$index]) ? $costDetails[$index]['costing'] : [];
						$option = isset($costDetails[$index]['option']) ? $costDetails[$index]['option'] : [] ;
						if(!$data){ $total_cost = 'NA'; break; }
						else{
							$total_cost .= ' <b>Option-'.$option.'</b> : '.number_format($data['total_amount'],2).',';
						}
					}
					$total_cost = rtrim($total_cost,',');
					?>
					<tr>
						<td><input type="checkbox" value="<?php echo $row_tours['quotation_id']; ?>" id="<?php echo $row_tours['quotation_id']; ?>" name="custom_package" class="custom_package"/></td> 
						<td><?php echo $count; ?></td>
						<td><?php echo get_quotation_id($row_tours['quotation_id'],$year); ?></td>
						<td><?php echo $enqDetails->customer_name ?></td>
						<td><?= $total_cost ?></td>
					</tr>
					<?php
					$count++;
				}
			}
		    ?>
			</table>
			</div>
			</div>
			</div>
			<div class="row text-center">
				<div class="col-md-12 mg_tp_20">
					<button class="btn btn-sm btn-success" id="btn_quotation_send" onclick="multiple_quotation_mail();"><i class="fa fa-paper-plane-o"></i>&nbsp;&nbsp;<?php echo "Send" ?></button>
				</div>
			</div>
		</div>  
		</div>
	</div>
</div>
<script>
$('#quotation_send_modal').modal('show');
function select_all_check(id,custom_package){
	var checked = $('#'+id).is(':checked');
	// Select all
	if(checked){
		$('.custom_package1').each(function() {
			$(this).prop("checked",true);
		});
	}
	else{
		// Deselect All
		$('.custom_package1').each(function() {
			$(this).prop("checked",false);
		});
	}
}

function multiple_quotation_mail()
{
	 var quotation_id_arr = new Array();
	 var base_url = $('#base_url').val();
		$('input[name="custom_package"]:checked').each(function(){
			quotation_id_arr.push($(this).val());
		});
		if(quotation_id_arr.length==0){
			error_msg_alert('Please select at least one quotation!');
			return false;
		}
	$('#btn_quotation_send').button('loading'); 
	$.ajax({
			type:'post',
			url: base_url+'controller/hotel/quotation/quotation_email.php',
			data:{ quotation_id_arr : quotation_id_arr},
			success: function(message){
					msg_alert(message);
					$('#btn_quotation_send').button('reset'); 
					$('#quotation_send_modal').modal('hide');             	
                }  
		});	
}
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
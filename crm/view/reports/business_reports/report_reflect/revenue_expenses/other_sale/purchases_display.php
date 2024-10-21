<?php
include_once "../../../../../../model/model.php";
$estimate_type = $_POST['estimate_type'];
$estimate_type_id = $_POST['estimate_type_id'];

$sq_pount = mysqli_num_rows(mysqlQuery("select * from vendor_estimate where estimate_type='$estimate_type' and estimate_type_id ='$estimate_type_id' and delete_status='0'"));
?>
<div class="modal fade" id="purchase_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  	<div class="modal-dialog modal-lg" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
	        	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        	<h4 class="modal-title text-left" id="myModalLabel">Purchase/Expenses Details</h4>
	        </div>
      		<div class="modal-body profile_box_padding">
            <?php
            if($sq_pount!=0){ ?>
                <div class="row"> <div class="col-md-12"> <div class="table-responsive">
                <h3 class="editor_title">Purchase History</h3>
                    <table class="table table-bordered no-marg">
                        <thead>
                            <tr class="active table-heading-row">
                                <th>S_No.</th>
                                <th>Purchase_date</th>
                                <th>Supplier_type</th>
                                <th>Supplier_name</th>
                                <th>Purchase_amount</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $count = 1;
                        $sq_query = mysqlQuery("select * from vendor_estimate where estimate_type='$estimate_type' and estimate_type_id ='$estimate_type_id' and delete_status='0'");
                        while($row_query = mysqli_fetch_assoc($sq_query)){	
                            if($row_query['net_total'] != '0'){
                                $vendor_name = get_vendor_name_report($row_query['vendor_type'],$row_query['vendor_type_id']);
                                //Service Tax 
                                $service_tax_amount = 0;
                                if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
                                    $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
                                    for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
                                        $service_tax = explode(':',$service_tax_subtotal1[$i]);
                                        $service_tax_amount +=  $service_tax[2];
                                    }
                                }
                                if($row_query['purchase_return'] == 0 || $row_query['purchase_return'] == 1){
                                    $total_purchase = $row_query['net_total'];
                                }
                                else if($row_query['purchase_return'] == 2){
                                    $cancel_estimate = json_decode($row_query['cancel_estimate']);
                                    $p_purchase = ($row_query['net_total'] - floatval($cancel_estimate[0]->net_total) - floatval($cancel_estimate[0]->service_tax_subtotal));
                                    $total_purchase = $p_purchase;
                                }
                            ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= get_date_user($row_query['purchase_date']) ?></td>
                                    <td><?= $row_query['vendor_type'] ?></td>
                                    <td><?= $vendor_name ?></td>
                                    <td><?= number_format($total_purchase-$service_tax_amount,2) ?></td>
                                </tr>
                            <?php }
                        } ?>
                        </tbody>
                    </table>
                </div></div></div>
                <?php
            }else{
                echo '<h4>No purchase taken for this booking</h4>';
            } ?>

	    	</div>
		</div>
	</div>
</div>
<script type="text/javascript">
    $('#purchase_modal').modal('show');
</script>
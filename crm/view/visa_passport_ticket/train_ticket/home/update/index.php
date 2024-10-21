<?php
include "../../../../../model/model.php";
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_POST['branch_status'];
$train_ticket_id = $_POST['train_ticket_id'];

$sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$train_ticket_id' and delete_status='0'"));
$reflections = isset($sq_booking['reflections']) ? json_decode($sq_booking['reflections']) : [];
$train_sc = '';
$train_markup = '';
$train_taxes = '';
$tax_apply_on = '';
$tax_value = '';
$train_markup_taxes = '';
$hotel_tds = '';
if(isset($reflections[0])){
    if($reflections[0]->tax_apply_on == '1') { 
        $tax_apply_on = 'Basic Amount';
    }
    else if($reflections[0]->tax_apply_on == '2') { 
        $tax_apply_on = 'Service Charge';
    }
    else if($reflections[0]->tax_apply_on == '3') {
        $tax_apply_on = 'Total';
    }
    $train_sc = isset($reflections[0]->train_sc) ? $reflections[0]->train_sc : '';
    $train_markup = isset($reflections[0]->train_markup) ? $reflections[0]->train_markup : '';
    $train_taxes = isset($reflections[0]->train_taxes) ? $reflections[0]->train_taxes : '';
    $tax_value = isset($reflections[0]->tax_value) ? $reflections[0]->tax_value : '';
    $train_markup_taxes = isset($reflections[0]->train_markup_taxes) ? $reflections[0]->train_markup_taxes : '';
    $hotel_tds = isset($reflections[0]->hotel_tds) ? $reflections[0]->hotel_tds : '';
}
?>
<input type="hidden" id="train_ticket_id" name="train_ticket_id" value="<?= $train_ticket_id ?>">
<input type="hidden" id="hotel_sc" name="hotel_sc" value="<?php echo $train_sc ?>">
<input type="hidden" id="hotel_markup" name="hotel_markup" value="<?php echo $train_markup ?>">
<input type="hidden" id="hotel_taxes" name="hotel_taxes" value="<?php echo $train_taxes ?>">
<input type="hidden" id="tax_apply_on" name="tax_apply_on" value="<?php echo $tax_apply_on ?>">
<input type="hidden" id="atax_apply_on" name="atax_apply_on" value="<?php echo $tax_apply_on ?>">
<input type="hidden" id="tax_value1" name="tax_value1" value="<?php echo $tax_value ?>">

<input type="hidden" id="hotel_markup_taxes" name="hotel_markup_taxes" value="<?php echo $train_markup_taxes ?>">
<input type="hidden" id="hotel_tds" name="hotel_tds" value="<?php echo $hotel_tds ?>">
<div class="modal fade" id="update_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static"
    data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width:96% !important;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Update Train Ticket Booking</h4>
            </div>
            <div class="modal-body">

                <section id="sec_ticket_save" name="frm_ticket_save">

                    <div>

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#tab1" aria-controls="tab1" role="tab"
                                    data-toggle="tab">Customer Details</a></li>
                            <li role="presentation"><a href="#tab2" aria-controls="tab2" role="tab"
                                    data-toggle="tab">Train Ticket</a></li>
                            <li role="presentation"><a href="#tab3" aria-controls="tab3" role="tab"
                                    data-toggle="tab">Costing</a></li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content" style="padding:20px 10px;">
                            <div role="tabpanel" class="tab-pane active" id="tab1">
                                <?php include_once('tab1.php'); ?>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab2">
                                <?php include_once('tab2.php'); ?>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab3">
                                <?php include_once('tab3.php'); ?>
                            </div>
                        </div>

                    </div>


                </section>


            </div>
        </div>
    </div>
</div>


<script>
$('#update_modal').modal('show');
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
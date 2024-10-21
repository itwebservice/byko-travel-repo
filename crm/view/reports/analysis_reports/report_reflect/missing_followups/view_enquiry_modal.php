<?php
include "../../../../../model/model.php";
$enquiry_id = $_POST['enquiry_id'];
?>
<div class="modal fade" id="branch_wise_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Followup History</h4>
            </div>
            <div class="modal-body profile_box_padding">
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
<script type="text/javascript">
    $('#branch_wise_modal').modal('show');
</script>
<?php
include "../../../../../model/model.php"; 

$transportId = $_POST['transport_id'];
$vendorName = $_POST['vendor_name'];
$qry = mysqlQuery("select * from vendor_estimate where vendor_type_id='$transportId' and vendor_type='$vendorName' and delete_status='0'");


?>

<div class="modal fade" id="supp_wise_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Enquiry Details</h4>

            </div>
            <div class="modal-body profile_box_padding">

            <!-- print work -->
                

                <?php 
                    if(mysqli_num_rows($qry)>0)
                        {
                            while($db = mysqli_fetch_array($qry))
                            {
                                echo 'test';
                            }
                        }                
                ?>
            <!-- print work -->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#supp_wise_modal').modal('show');
</script>
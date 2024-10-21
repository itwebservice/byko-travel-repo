<?php
include "../../../../../model/model.php";

$hotelId = $_POST['hotel_id'];
$hotelName = $_POST['hotel_name'];
$qry = mysqlQuery("select * from vendor_estimate inner join hotel_booking_master on hotel_booking_master.booking_id=vendor_estimate.estimate_type_id inner join customer_master on hotel_booking_master.customer_id=customer_master.customer_id where vendor_estimate.vendor_type_id='$hotelId' and vendor_estimate.vendor_type='$hotelName' and vendor_estimate.estimate_type='Hotel' and vendor_estimate.delete_status='0'") or die('error');
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
                <table>
                <tr>
                    <th>Booking Id</th>
                    <th>Customer Name</th>
                    <th>Purchase Amount</th>
                    
                </tr>
                <?php
                if (mysqli_num_rows($qry) > 0) {
                    while ($db = mysqli_fetch_array($qry)) {

                        echo '<tr><td>'.$db['booking_id'].'</td>';
                        echo '<td>'.$db['first_name'].'</td>';
                        echo '<td>'.$db['net_total'].'</td></tr>';

                        ?>

                
                <?php
            }
                }
        ?>
        </table>
                <!-- print work -->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#supp_wise_modal').modal('show');
</script>
<?php
include "../../../../../model/model.php";

$trainticketId = $_POST['trainticket_id'];
$vendorName = $_POST['vendor_name'];

$qry = mysqlQuery("select * from vendor_estimate inner join train_ticket_master on vendor_estimate.vendor_type_id = train_ticket_master.train_ticket_id 
inner join customer_master on train_ticket_master.customer_id=customer_master.customer_id where vendor_type_id='$trainticketId' and vendor_type='$vendorName' and vendor_estimate.delete_status='0'") or die('error');
//package
$qry2 = mysqlQuery("select * from vendor_estimate inner join package_tour_booking_master on vendor_estimate.vendor_type_id = package_tour_booking_master.booking_id 
inner join customer_master on package_tour_booking_master.customer_id=customer_master.customer_id
where vendor_estimate.vendor_type_id='$trainticketId' and vendor_estimate.vendor_type='$vendorName' and estimate_type='Package Tour' and vendor_estimate.delete_status='0'")or die('error');
//group
$qry3 = mysqlQuery("select * from vendor_estimate inner join tourwise_traveler_details on vendor_estimate.vendor_type_id = tourwise_traveler_details.id 
inner join customer_master on tourwise_traveler_details.customer_id=customer_master.customer_id
where vendor_estimate.vendor_type_id='$trainticketId' and vendor_estimate.vendor_type='$vendorName' and estimate_type='Group Tour' and vendor_estimate.delete_status='0'")or die('error');



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
                <table class="table">
                    <tr>
                        <th>Booking Id</th>
                        <th>Customer Name</th>
                        <th>Purchase Amount</th>
                    </tr>
                    <?php
                    if (mysqli_num_rows($qry) > 0) {
                        while ($db = mysqli_fetch_array($qry)) {
                            //    var_dump($db);
                    ?>
                            <tr>
                                <td><?php echo $db['customer_id']; ?></td>
                                <td><?php echo $db['first_name'] . $db['last_name'];; ?></td>
                                <td><?php echo $db['net_total']; ?></td>

                            </tr>
                    <?php
                        }
                    }
                    ?>
                     <?php
                    if (mysqli_num_rows($qry2) > 0) {
                        while ($db = mysqli_fetch_array($qry2)) {
                            //    var_dump($db);
                    ?>
                            <tr>
                                <td><?php echo $db['customer_id']; ?></td>
                                <td><?php echo $db['first_name'] . $db['last_name'];; ?></td>
                                <td><?php echo $db['net_total']; ?></td>

                            </tr>
                    <?php
                        }
                    }
                    ?>

<?php
                    if (mysqli_num_rows($qry3) > 0) {
                        while ($db = mysqli_fetch_array($qry3)) {
                            //    var_dump($db);
                    ?>
                            <tr>
                                <td><?php echo $db['customer_id']; ?></td>
                                <td><?php echo $db['first_name'] . $db['last_name'];; ?></td>
                                <td><?php echo $db['net_total']; ?></td>

                            </tr>
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
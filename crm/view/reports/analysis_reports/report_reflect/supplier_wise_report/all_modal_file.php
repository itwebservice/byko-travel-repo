<?php
include "../../../../../model/model.php";

$vendorTypeId = $_POST['vendor_type_id'];
$vendorType = $_POST['vendor_type'];
$estimateId = $_POST['estimate_id'];
$estimateType = $_POST['estimate_type'];
$dateqry =  $_SESSION['dateqry'];
$totalAll = 0;
//package
if($estimateType == 'Package Tour')
{
    $query = "select customer_master.customer_id,customer_master.first_name,customer_master.last_name,vendor_estimate.net_total,vendor_estimate.basic_cost
    from vendor_estimate inner join package_tour_booking_master on vendor_estimate.estimate_type_id = package_tour_booking_master.booking_id 
    left join customer_master on package_tour_booking_master.customer_id=customer_master.customer_id
    where vendor_estimate.vendor_type_id='$vendorTypeId' and vendor_estimate.vendor_type='$vendorType' and 
    estimate_type='Package Tour' and vendor_estimate.status!='Cancel'";
$qryPackage = mysqlQuery($query . $dateqry) or die('error');
}
//group
if($estimateType == 'Group Tour')
{
    $query = "select customer_master.customer_id,customer_master.first_name,customer_master.last_name,vendor_estimate.net_total,vendor_estimate.basic_cost from vendor_estimate left join tourwise_traveler_details on vendor_estimate.estimate_type_id = tourwise_traveler_details.id 
    inner join customer_master on tourwise_traveler_details.customer_id=customer_master.customer_id 
    where vendor_estimate.vendor_type_id='$vendorTypeId' and vendor_estimate.vendor_type='$vendorType' and  
    estimate_type='Group Tour' and vendor_estimate.status!='Cancel'";
    $qryGroup = mysqlQuery($query . $dateqry) or die('error');
}

//gen
if ($estimateType != 'Package Tour'  && $estimateType != 'Group Tour'  && $vendorType != 'Other Vendor' && $vendorType != 'DMC Vendor' ) {
    $genQry = "select customer_master.customer_id,customer_master.first_name,customer_master.last_name,vendor_estimate.net_total,vendor_estimate.basic_cost from vendor_estimate";
    if ($vendorType == 'Car Rental Vendor' || $vendorType == 'Transport Vendor') {
        $genQry .= " inner join car_rental_booking on vendor_estimate.estimate_type_id = car_rental_booking.booking_id 
        inner join customer_master on car_rental_booking.booking_id=customer_master.customer_id";
    }
    if ($vendorType == 'Train Ticket Vendor') {
        $genQry .= " inner join train_ticket_master on vendor_estimate.estimate_type_id = train_ticket_master.train_ticket_id 
        inner join customer_master on train_ticket_master.customer_id=customer_master.customer_id";
    }
    if ($vendorType == 'Visa Vendor') {
        $genQry .= " inner join visa_master on vendor_estimate.estimate_type_id = visa_master.visa_id 
        inner join customer_master on visa_master.vendor_id=customer_master.customer_id";
    }
    if ($vendorType == 'Ticket Vendor') {
        $genQry .= " inner join ticket_master on vendor_estimate.estimate_type_id = ticket_master.ticket_id 
        inner join customer_master on ticket_master.customer_id=customer_master.customer_id";
    }
    if ($vendorType == 'Hotel Vendor') {
        $genQry .= " inner join hotel_booking_master on vendor_estimate.estimate_type_id = hotel_booking_master.booking_id
        inner join customer_master on hotel_booking_master.customer_id=customer_master.customer_id";
    }
    if ($vendorType == 'Excursion Vendor') 
    {
        $genQry .= " inner join excursion_master on vendor_estimate.estimate_type_id = excursion_master.exc_id 
        inner join customer_master on excursion_master.customer_id=customer_master.customer_id";
    }
    if($estimateType == "Train" && $vendorType != 'DMC Vendor')
    {
        $genQry .= " inner join train_ticket_master on vendor_estimate.estimate_type_id = train_ticket_master.train_ticket_id 
        inner join customer_master on train_ticket_master.customer_id=customer_master.customer_id";

    }

    $genQry .= " where vendor_estimate.vendor_type_id='$vendorTypeId' and vendor_estimate.vendor_type='$vendorType' and vendor_estimate.status!='Cancel'" . $dateqry;
    $runGenQry = mysqlQuery($genQry);
}
?>
<div class="modal fade" id="supp_wise_modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Details</h4>
            </div>
            <div class="modal-body profile_box_padding">
                <!-- print work -->
                <table class="table">
                    <tr>
                        <th>Sr.No</th>
                        <th>Customer Name</th>
                        <th>Purchase Amount</th>
                    </tr>
                    <?php
                    $count = 1;
                    if (!empty($runGenQry)) {
                        if (mysqli_num_rows($runGenQry) > 0) {
                            while ($db = mysqli_fetch_assoc($runGenQry)) {
                    ?>
                                <tr>
                                    <td><?= $count++; ?></td>
                                    <td><?php
                                    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                    if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                        $customer_name = $sq_customer['company_name'];
                                    } else {
                                        $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                    }

                                    echo  $customer_name; ?> </td>
                                    <td><?php 
                                    $totalAll += (int)$db['net_total'];
                                    echo $db['net_total']; ?> </td>
                                </tr>
                            <?php
                            }
                        }
                    }
                    //package
                    if (!empty($qryPackage)) {
                        if (mysqli_num_rows($qryPackage) > 0) {
                            while ($db = mysqli_fetch_assoc($qryPackage)) {
                            ?>
                                <tr>
                                    <td><?= $count++; ?></td>
                                    <td><?php
                                        $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                        if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                            $customer_name = $sq_customer['company_name'];
                                        } else {
                                            $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                        }

                                        echo  $customer_name;
                                        ?> </td>
                                    <td><?php
                                    $totalAll += (int)$db['net_total'];
                                    echo $db['net_total']; ?> </td>
                                </tr>
                            <?php
                            }
                        }
                    }
                    //tour
                    if (!empty($qryGroup)) {
                        if (mysqli_num_rows($qryGroup) > 0) {
                            while ($db = mysqli_fetch_assoc($qryGroup)) {
                            ?>
                                <tr>
                                    <td><?= $count++; ?></td>
                                    <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                    <td><?php
                                    $totalAll += (int)$db['net_total'];
                                    echo $db['net_total']; ?> </td>
                                </tr>
                    <?php
                            }
                        }
                    }
                    ?>
                    <tr class="bg-success">
                        <td colspan="2"><b> Total: </b></td>
                        <td> <b> <?= number_format(floatval($totalAll),2) ?> </b></td>
                    </tr>
                </table>
                <!-- print work -->

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#supp_wise_modal').modal('show');
</script>
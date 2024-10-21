<?php
include "../../../../../model/model.php";

$dest_id = $_POST['dest_id'];
$qry = "select * from package_tour_booking_master inner join destination_master on package_tour_booking_master.dest_id=destination_master.dest_id inner join customer_master on package_tour_booking_master.customer_id=customer_master.customer_id where destination_master.dest_id=" . $dest_id . " and package_tour_booking_master.delete_status=0 " . $_SESSION['package'];

$qry2 = "select * from tourwise_traveler_details inner join tour_master on tourwise_traveler_details.tour_id=tour_master.tour_id inner join destination_master on tour_master.dest_id=destination_master.dest_id inner join customer_master on tourwise_traveler_details.customer_id=customer_master.customer_id where destination_master.dest_id=" . $dest_id . " and tourwise_traveler_details.delete_status=0 and tourwise_traveler_details.tour_group_status=''" . $_SESSION['tourwise'];


$sq_query = mysqlQuery($qry);
$sq_query1 = mysqlQuery($qry2);
$sq_count = mysqli_num_rows($sq_query);
$sq_count1 = mysqli_num_rows($sq_query1);

?>

<div class="modal fade" id="des_wise_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Booking Details</h4>

            </div>
            <div class="modal-body profile_box_padding">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>

                                    <th scope="col">Booking Id </th>
                                    <th scope="col">Customer Name </th>
                                    <th scope="col">Travel Date </th>
                                    <th scope="col">Booking Amount</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                if ($sq_count > 0) {
                                    $count = 1;


                                    while ($db = mysqli_fetch_assoc($sq_query)) {
                                        $pass_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$db[booking_id]'"));
                                        $cancle_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$db[booking_id]' and status='Cancel'"));
                                        if ($pass_count == $cancle_count) {
                                            continue;
                                        }

                                ?>

                                        <tr>
                                            <td><?php

                                                $date = $db['created_at'];
                                                $yr = explode("-", $date);
                                                $year = $yr[0];
                                                echo get_package_booking_id($db['booking_id'], $year);
                                                ?></td>
                                            <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                            <td><?php
                                                $source = $db['tour_from_date'];
                                                $date = new DateTime($source);
                                                echo $date->format('d-m-Y');
                                                ?></td>
                                            <td><?php echo $db['net_total']; ?></td>

                                        </tr>
                                <?php  }
                                }
                                ?>


                                <?php

                                if ($sq_count1 > 0) {



                                    while ($db = mysqli_fetch_assoc($sq_query1)) {
                                        $pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$db[traveler_group_id]'"));
                                        $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$db[traveler_group_id]' and status='Cancel'"));
                                        if($pass_count == $cancelpass_count)
                                        {
                                            continue;
                                        }
                                        if ($db['tour_group_status'] == "Cancel") {
                                        continue;
                                        }
                                ?>

                                        <tr>
                                            <td><?php
                                                $date = $db['form_date'];
                                                $yr = explode("-", $date);
                                                $year = $yr[0];
                                                echo get_group_booking_id($db['id'], $year);
                                                ?></td>
                                            <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                            <td><?php
                                                $source = $db['form_date'];
                                                $date = new DateTime($source);
                                                echo $date->format('d-m-Y');
                                                ?></td>
                                            <td><?php echo $db['net_total']; ?></td>
                                        </tr>
                                <?php  }
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#des_wise_modal').modal('show');
</script>
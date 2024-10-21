<?php
include "../../../../../model/model.php";

$all_total = 0;
$from_location = $_POST['from_location'];
$to_location = $_POST['to_location'];
$qry = "SELECT ticket_trip_entries.departure_datetime,ticket_trip_entries.ticket_id,ticket_master.created_at,airline_master.airline_id,count(*)as sector,sum(ticket_master.ticket_total_cost) as purchase,ticket_trip_entries.ticket_id,ticket_master.created_at,ticket_master.ticket_total_cost,customer_master.first_name,customer_master.last_name,customer_master.customer_id,ticket_master.adults,ticket_master.childrens 
FROM airline_master 
INNER JOIN ticket_trip_entries on airline_master.airline_id = ticket_trip_entries.airline_id
 inner join ticket_master on ticket_trip_entries.ticket_id = ticket_master.ticket_id 
 INNER JOIN customer_master on ticket_master.customer_id = customer_master.customer_id where ticket_trip_entries.departure_city='" . $from_location . "' AND ticket_trip_entries.arrival_city='" . $to_location . "' " . $_SESSION['dateqry'] . " Group by ticket_master.ticket_id ";
$sq_query = mysqlQuery($qry);

$qry1 = "SELECT plane_master.seats,plane_master.date,plane_master.plane_id,count(*)as sector ,sum(tourwise_traveler_details.plane_expense) as purchase, plane_master.plane_id,customer_master.first_name,customer_master.last_name, tourwise_traveler_details.form_date,customer_master.customer_id,tourwise_traveler_details.form_date,tourwise_traveler_details.id 
FROM plane_master 
INNER JOIN tourwise_traveler_details on plane_master.tourwise_traveler_id = tourwise_traveler_details.traveler_group_id 
INNER JOIN airline_master on plane_master.company = airline_master.airline_id 
INNER JOIN customer_master on tourwise_traveler_details.customer_id = customer_master.customer_id 
INNER JOIN tour_master on tourwise_traveler_details.tour_id= tour_master.tour_id  where plane_master.from_location='" . $from_location . "'  AND plane_master.to_location='" . $to_location . "' " . $_SESSION['group_qry'] . " Group by plane_master.plane_id ";
$sq_query1 = mysqlQuery($qry1);

$qry2 = "SELECT package_plane_master.date,package_plane_master.seats,package_plane_master.plane_id,count(*)as sector ,sum(package_plane_master.amount) as purchase, package_plane_master.plane_id,customer_master.first_name,customer_master.last_name, package_tour_booking_master.tour_from_date,customer_master.customer_id,package_plane_master.booking_id,package_plane_master.date  
FROM package_plane_master 
INNER JOIN airline_master ON package_plane_master.company = airline_master.airline_id 
INNER JOIN package_tour_booking_master on package_plane_master.booking_id = package_tour_booking_master.booking_id
 INNER JOIN customer_master on package_tour_booking_master.customer_id = customer_master.customer_id 
 where package_plane_master.from_location='" . $from_location . "'  AND package_plane_master.to_location='" . $to_location . "' " . $_SESSION['package_qry'] . "  Group by package_plane_master.plane_id";
$sq_query2 = mysqlQuery($qry2);

$sq_count = mysqli_num_rows($sq_query);
$sq_count1 = mysqli_num_rows($sq_query1);
$sq_count2 = mysqli_num_rows($sq_query2);

?>

<div class="modal fade" id="com_sector_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Sector Details</h4>

            </div>
            <div class="modal-body profile_box_padding">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Booking Id </th>
                                    <th scope="col">Travel Date </th>
                                    <th scope="col">Guest Name</th>
                                    <th scope="col">Total Pax</th>
                                    <th scope="col">Booking Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                if ($sq_count > 0) {
                                    $count = 1;

                                    while ($db = mysqli_fetch_assoc($sq_query)) {
                                        $total_pax = (int)$db['adults'] + (int)$db['childrens'];
                                ?>

                                        <tr>
                                            <td><?php
                                                $date = $db['created_at'];
                                                $yr = explode("-", $date);
                                                $year = $yr[0];
                                                echo get_ticket_booking_id($db['ticket_id'], $year);

                                                ?></td>
                                            <td><?php
                                                $source = $db['departure_datetime'];
                                                $date = new DateTime($source);
                                                echo $date->format('d-m-Y');
                                                ?> </td>
                                            <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                            <td><?php echo (int)$total_pax; ?></td>
                                            <td><?php
                                                $all_total +=  $db['purchase'];
                                                echo $db['purchase']; ?></td>
                                        </tr>
                                <?php  }
                                }
                                ?>

                                <?php

                                if ($sq_count1 > 0) {
                                    $count = 1;

                                    while ($db = mysqli_fetch_assoc($sq_query1)) {
                                ?>
                                        <tr>
                                            <td><?php
                                                $date = $db['form_date'];
                                                $yr = explode("-", $date);
                                                $year = $yr[0];
                                                echo get_group_booking_id($db['id'], $year);
                                                ?></td>
                                            <td><?php
                                                $source = $db['date'];
                                                $date = new DateTime($source);
                                                echo $date->format('d-m-Y');
                                                ?> </td>
                                            <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                            <td><?php echo (int)$db['seats']; ?></td>
                                            <td><?php
                                                $all_total +=  $db['purchase'];

                                                echo $db['purchase']; ?></td>
                                        </tr>
                                <?php  }
                                }
                                ?>

                                <?php

                                if ($sq_count2 > 0) {
                                    $count = 1;

                                    while ($db = mysqli_fetch_assoc($sq_query2)) {
                                ?>

                                        <tr>
                                            <td><?php
                                                $date = $db['date'];
                                                $yr = explode("-", $date);
                                                $year = $yr[0];
                                                echo get_package_booking_id($db['booking_id'], $year);
                                                ?></td>
                                            <td><?php
                                                $source = $db['date'];
                                                $date = new DateTime($source);
                                                echo $date->format('d-m-Y');
                                                ?> </td>
                                            <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                            <td><?php echo (int)$db['seats']; ?></td>
                                            <td><?php
                                                $all_total +=  $db['purchase'];
                                                echo $db['purchase']; ?></td>
                                        </tr>
                                <?php  }
                                }
                                ?>

                                <tr class="bg-success">
                                    <td colspan="4" class="text-start"><b>Total</b></td>
                                    <td><b><?= number_format(floatval($all_total),2) ?></b></td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#com_sector_modal').modal('show');
</script>
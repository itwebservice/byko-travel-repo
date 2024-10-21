<?php
include "../../../../../model/model.php";

$hotel_id = $_POST['hotel_id'];
$fromdate = !empty($_POST['fromdate']) ? get_date_db($_POST['fromdate']) :null;
$todate = !empty($_POST['todate']) ? get_date_db($_POST['todate']) :null;
$sql = "SELECT * FROM hotel_master INNER JOIN city_master on hotel_master.city_id = city_master.city_id INNER JOIN hotel_booking_entries on hotel_master.hotel_id = hotel_booking_entries.hotel_id INNER JOIN hotel_booking_master on hotel_booking_entries.booking_id = hotel_booking_master.booking_id INNER JOIN customer_master on hotel_booking_master.customer_id = customer_master.customer_id where 1 and cancel_flag!=1 and hotel_master.hotel_id = '" . $hotel_id . "'";
if(!empty($fromdate) && !empty($todate))
{
    $sql .= "and hotel_booking_master.created_at between '".$fromdate."' and '".$todate."' ";   
}

$sq_query = mysqlQuery($sql);
$sql2 = "SELECT * FROM hotel_master INNER JOIN city_master on hotel_master.city_id = city_master.city_id INNER Join package_hotel_accomodation_master on hotel_master.hotel_id = package_hotel_accomodation_master.hotel_id INNER JOIN package_tour_booking_master on package_hotel_accomodation_master.booking_id = package_tour_booking_master.booking_id INNER JOIN customer_master on package_tour_booking_master.customer_id = customer_master.customer_id inner join vendor_estimate on package_tour_booking_master.booking_id = vendor_estimate.estimate_type_id where 1 and status!='Cancel' and hotel_master.hotel_id = '" . $hotel_id . "'";
if(!empty($fromdate) && !empty($todate)){
    $sql2 .= "and package_tour_booking_master.booking_date between '".$fromdate."' and '".$todate."'";
}
$sql2 .= " GROUP BY package_hotel_accomodation_master.id";
$sq_query1 = mysqlQuery($sql2);
$sq_count = mysqli_num_rows($sq_query);
$sq_count1 = mysqli_num_rows($sq_query1);

?>

<div class="modal fade" id="com_hotel_modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Hotel Details</h4>

            </div>
            <div class="modal-body profile_box_padding">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Sr.No </th>
                                    <th scope="col">Booking_Id </th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Check In Date/Time</th>
                                    <th scope="col">Check Out Date/Time</th>
                                    <th scope="col">Total Rooms</th>
                                    <th scope="col">Total Nights</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counting = 1;
                                $count = 1;
                                if ($sq_count > 0) {


                                    while ($db = mysqli_fetch_assoc($sq_query)) {

                                ?>

                                        <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php 
                                            $date = $db['created_at'];
                                            $yr = explode("-", $date);
                                            $year =$yr[0];
                                            echo get_hotel_booking_id($db['booking_id'],$year); ?></td>
                                            <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }
                                                echo  $customer_name; ?> </td>
                                            <td><?php
                                                $source = get_datetime_user($db['check_in']);
                                                $date = new DateTime($source);
                                                echo $source;

                                                ?></td>
                                            <td><?php
                                                $source = get_datetime_user($db['check_out']);
                                                $date = new DateTime($source);
                                                echo $source;
                                                ?></td>
                                            <td><?php echo $db['rooms']; ?></td>
                                            <td><?php echo $db['no_of_nights']; ?></td>
                                        </tr>
                                <?php $count++; }
                                }
                                if ($sq_count1 > 0) {

                                    while ($db = mysqli_fetch_assoc($sq_query1)) {

                                ?>

                                        <tr>
                                            <td><?php echo $count; ?></td>
                                            <td><?php 
                                            $date = $db['created_at'];
                                            $yr = explode("-", $date);
                                            $year =$yr[0];
                                            echo get_package_booking_id($db['booking_id'],$year); ?></td>
                                            <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                            <td><?php
                                                $source = get_datetime_user($db['from_date']);
                                                $date = new DateTime($source);
                                                echo $source;

                                                ?></td>
                                            <td><?php
                                                $source = get_datetime_user($db['to_date']);
                                                $date = new DateTime($source);
                                                echo $source;
                                                ?></td>
                                            <td><?php echo $db['rooms']; ?></td>
                                            <td><?php
                                                $total_nights = new datetime($db['from_date']);
                                                $total_nights2 = new datetime($db['to_date']);
                                                echo  $total_nightss = $total_nights->diff($total_nights2)->format("%r%a");
                                                ?></td>
                                        </tr>
                                <?php $count++; }
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
    $('#com_hotel_modal').modal('show');
</script>
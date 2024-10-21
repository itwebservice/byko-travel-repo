<?php
include "../../../../../model/model.php"; 
$all_total = 0;
$airline_id = $_POST['airline_id'];
$qry = "SELECT airline_master.airline_id,count(*)as sector,ticket_trip_entries.ticket_id,ticket_master.adults,ticket_master.childrens,ticket_master.infant,ticket_master.ticket_total_cost,cancel_amount,cancel_type,cancel_estimate,customer_master.first_name,customer_master.last_name,customer_master.customer_id,ticket_master.created_at FROM airline_master INNER JOIN ticket_trip_entries on airline_master.airline_id = ticket_trip_entries.airline_id inner join ticket_master on ticket_trip_entries.ticket_id = ticket_master.ticket_id INNER JOIN customer_master on ticket_master.customer_id = customer_master.customer_id where 1=1 ".$_SESSION['dateqry']." AND airline_master.airline_id='".$airline_id."' GROUP BY ticket_master.ticket_id";

$sq_query = mysqlQuery($qry);

$qry1 = "SELECT airline_master.airline_id,plane_master.plane_id,count(*)as sector ,sum(tourwise_traveler_details.plane_expense) as purchase,tourwise_traveler_details.id, plane_master.plane_id,customer_master.first_name,customer_master.last_name, plane_master.seats,customer_master.customer_id,tourwise_traveler_details.form_date
 FROM plane_master 
INNER JOIN tourwise_traveler_details on plane_master.tourwise_traveler_id = tourwise_traveler_details.traveler_group_id INNER JOIN airline_master on plane_master.company = airline_master.airline_id 
INNER JOIN customer_master on tourwise_traveler_details.customer_id = customer_master.customer_id 
INNER JOIN tour_master on tourwise_traveler_details.tour_id= tour_master.tour_id where airline_master.airline_id = '".$airline_id."' ".$_SESSION['group_qry']." Group by plane_master.plane_id ";
$sq_query1 = mysqlQuery($qry1);

$qry2 = "SELECT package_plane_master.booking_id ,package_plane_master.plane_id,count(*)as sector ,sum(package_plane_master.amount) as purchase, package_plane_master.plane_id,customer_master.first_name,customer_master.last_name, package_plane_master.seats,customer_master.customer_id,package_tour_booking_master.booking_date FROM package_plane_master INNER JOIN airline_master ON package_plane_master.company = airline_master.airline_id INNER JOIN package_tour_booking_master on package_plane_master.booking_id = package_tour_booking_master.booking_id INNER JOIN customer_master on package_tour_booking_master.customer_id = customer_master.customer_id where airline_master.airline_id = '".$airline_id."' ".$_SESSION['package_qry']."  Group by package_plane_master.plane_id";
$sq_query2 = mysqlQuery($qry2);

$sq_count = mysqli_num_rows($sq_query);
$sq_count1 = mysqli_num_rows($sq_query1);
$sq_count2 = mysqli_num_rows($sq_query2);

?>

<div class="modal fade" id="com_airline_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Airline Details</h4>
                  
            </div>
            <div class="modal-body profile_box_padding">
                <div class="row">
                    <div class="col-md-12">
                      <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Booking_Id </th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Total Pax</th>
                                    <th scope="col">Total Sectors</th>
                                    <th scope="col">Booking Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if($sq_count > 0)
                                    {
                                        $count =1;
                                        while($db = mysqli_fetch_assoc($sq_query))
                                        {
                                            if($db['cancel_type'] == '0'||$db['cancel_type'] == '2'||$db['cancel_type'] == '3'){
                                                $sale_amount = $db['ticket_total_cost'];
                                                $cancel_amt = $db['cancel_amount'];
                                                if($cancel_amt==""){ $cancel_amt = 0;}
                                                if($db['cancel_type'] == '2'||$db['cancel_type'] == '3'){
                                                    $cancel_estimate_data = json_decode($db['cancel_estimate']);
                                                    $cancel_estimate = (!isset($cancel_estimate_data)) ? 0 : $cancel_estimate_data[0]->ticket_total_cost;
                                                    $sale_amount = $db['ticket_total_cost'] - floatval($cancel_estimate) + floatval($cancel_amt);
                                                }
                                                ?>
                                                <tr>
                                                    <td><?php
                                                    $date = $db['created_at'];
                                                    $yr = explode("-", $date);
                                                    $year =$yr[0];
                                                    echo get_ticket_booking_id($db['ticket_id'],$year);
                                                    ?></td>
                                                    <td><?php
                                                    $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                    if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                        $customer_name = $sq_customer['company_name'];
                                                    } else {
                                                        $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                    }
                                                    echo  $customer_name; ?> </td>
                                                    <td><?php   echo $db['adults'] + $db['childrens'] + $db['infant']; ?> </td>
                                                    <td><?php   echo $db['sector'];?></td>
                                                    <td><?php   
                                                    $all_total += floatval($sale_amount);
                                                    echo number_format(floatval($sale_amount),2);?></td>
                                                </tr>
                                            <?php  }
                                        }
                                    }
                                ?>

                            <?php
                               if($sq_count1 > 0)
                               {
                                   $count =1;
                         
                                   while($db = mysqli_fetch_assoc($sq_query1))
                                   {
                                   ?>
                                 
                           <tr>
                               <td><?php
                               $date = $db['form_date'];
                               $yr = explode("-", $date);
                               $year =$yr[0];
                               echo get_group_booking_id($db['id'],$year);
                            
                                
                                 ?></td>
                               <td><?php
                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                    $customer_name = $sq_customer['company_name'];
                                } else {
                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                }

                                echo  $customer_name; ?> </td>
                               <td><?php   echo $db['seats']?> </td>
                               <td><?php   echo $db['sector'];?></td>
                               <td><?php   
                                    $all_total += floatval($db['purchase']);
                               
                               echo $db['purchase'];?></td>
                           </tr>
                           <?php  }
                               }
                           ?>

                            <?php
                               
                                    if($sq_count2 > 0)
                                    {
                                        $count =1;
                              
                                        while($db = mysqli_fetch_assoc($sq_query2))
                                        {
                                        ?>
                                      
                                <tr>
                                    <td><?php 
                                    $date = $db['booking_date'];
                                    $yr = explode("-", $date);
                                    $year =$yr[0];
                                    echo get_package_booking_id($db['booking_id'],$year);
                                         
                                    
                                    ?></td>
                                    <td><?php
                                                $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$db[customer_id]'"));
                                                if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
                                                    $customer_name = $sq_customer['company_name'];
                                                } else {
                                                    $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
                                                }

                                                echo  $customer_name; ?> </td>
                                    <td><?php   echo $db['seats'] ?> </td>
                                    <td><?php   echo $db['sector'];?></td>
                                    <td><?php   
                                    $all_total += floatval($db['purchase']);
                                    echo $db['purchase'];?></td>
                                </tr>
                                <?php  }
                                    }
                                ?>
                                <tr class="bg-success">
                                    <td colspan=4>Total:</td>
                                    <td><?php echo number_format(floatval($all_total),2); ?></td>
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
    $('#com_airline_modal').modal('show');
</script>
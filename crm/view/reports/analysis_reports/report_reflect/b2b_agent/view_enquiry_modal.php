<?php
include "../../../../../model/model.php";
global $currency;
$fromdate = !empty($_POST['fromdate']) ? get_date_db($_POST['fromdate']) : null;
$todate = !empty($_POST['todate']) ? get_date_db($_POST['todate']) : null;
$customer_id = $_POST['customer_id'];
$row_cust = mysqli_fetch_assoc(mysqlQuery("select company_name,first_name,middle_name,last_name from customer_master where customer_id='$customer_id'"));
$cust_name = $row_cust['company_name'].' ('.$row_cust['first_name'].' '.$row_cust['middle_name'].' '.$row_cust['last_name'].')';

$sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
$to_currency_rate = $sq_to['currency_rate'];
$count = 1;
?>
<div class="modal fade" id="branch_wise_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Details for <?php echo $cust_name; ?></h4>
            </div>
            <div class="modal-body profile_box_padding">
			<div>
				<!-- Nav tabs -->
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active"><a href="#basic_information" aria-controls="basic_information" role="tab" data-toggle="tab" class="tab_name">Quotation</a></li>
					<li role="presentation"><a href="#booking_information" aria-controls="booking_information" role="tab" data-toggle="tab" class="tab_name">Booking</a></li>
                </ul>
                
				<div class="panel panel-default panel-body fieldset profile_background">

                    <div class="tab-content">
                        <!-- *****TAb1 start -->
                        <div role="tabpanel" class="tab-pane active" id="basic_information">   
                            <?php
                            $sq_booking = mysqli_fetch_assoc(mysqlQuery("select register_id from b2b_registration where customer_id='$customer_id'"));
                            $quot_query_count = "select register_id from b2b_quotations where register_id='$sq_booking[register_id]'";
                            if (!empty($fromdate) && !empty($todate)) {
                                $quot_query_count .= " and DATE(created_at) between '" . $fromdate . "' and '" . $todate . "'";
                            }
                            $sq_quot_count = mysqli_num_rows(mysqlQuery($quot_query_count));
                            if($sq_quot_count > 0){
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Sr.No </th>
                                                    <th scope="col">Guest_Name</th>
                                                    <th scope="col">Quotation_Date</th>
                                                    <th scope="col">Quotation_Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $quot_query = "select * from b2b_quotations where register_id='$sq_booking[register_id]'";
                                                if (!empty($fromdate) && !empty($todate)) {
                                                    $quot_query .= " and DATE(created_at) between '" . $fromdate . "' and '" . $todate . "'";
                                                }
                                                $sq_quot = mysqlQuery($quot_query);
                                                while ($row_quotation = mysqli_fetch_assoc($sq_quot)){
                                                    $cart_list_arr = $row_quotation['cart_list_arr'];
                                                    $pdf_data_array = json_decode($row_quotation['pdf_data_array']);
                                                    $cust_name = $pdf_data_array[0]->cust_name;
                                                    
                                                    $markup_in = $pdf_data_array[0]->markup_in;
                                                    $markup_amount = $pdf_data_array[0]->markup_amount;
                                                    $tax_in = $pdf_data_array[0]->tax_in;
                                                    $tax_amount = $pdf_data_array[0]->tax_amount;
                                                    $grand_total = $pdf_data_array[0]->grand_total;
                                                    if($markup_in == 'Percentage'){
                                                        $markup = $grand_total*($markup_amount/100);
                                                    }
                                                    else{
                                                        $markup = $markup_amount;
                                                    }
                                                    $grand_total += $markup;
                                                    if($tax_in == 'Percentage'){
                                                        $tax_amt = ($grand_total*($tax_amount/100));
                                                    }
                                                    else{
                                                        $tax_amt = $tax_amount;
                                                    }
                                                    $quotation_cost = $grand_total + $tax_amt;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $count; ?></td>
                                                        <td><?php echo $cust_name; ?></td>
                                                        <td><?php echo get_date_user($row_quotation['created_at']); ?></td>
                                                        <td><?php echo number_format($quotation_cost,2); ?></td>
                                                    </tr>
                                                    <?php $count++; 
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php }else{
                                echo 'No Quotations....';
                            } ?>
                        </div>
                        <!-- *****TAb1 start -->
                        <div role="tabpanel" class="tab-pane" id="booking_information">
                            <?php
                            $count = 1;
                            $booking_query_count = "select * from b2b_booking_master where customer_id='$customer_id' and status=''";
                            if (!empty($fromdate) && !empty($todate)) {
                                $booking_query_count .= " and DATE(created_at) between '" . $fromdate . "' and '" . $todate . "'";
                            }
                            $sq_booking_count = mysqli_num_rows(mysqlQuery($booking_query_count));
                            if($sq_booking_count > 0){
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Sr.No </th>
                                                    <th scope="col">Booking_Id</th>
                                                    <th scope="col">Booking_Date</th>
                                                    <th scope="col">Booking_Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $booking_query = "select * from b2b_booking_master where customer_id='$customer_id' and status=''";
                                                if (!empty($fromdate) && !empty($todate)) {
                                                    $booking_query .= " and DATE(created_at) between '" . $fromdate . "' and '" . $todate . "'";
                                                }
                                                $sq_booking = mysqlQuery($booking_query);
                                                $servie_total = 0;
                                                while ($row_booking = mysqli_fetch_assoc($sq_booking)) {
                                                    
                                                    $hotel_total = 0;
                                                    $transfer_total = 0;
                                                    $activity_total = 0;
                                                    $tours_total = 0;
                                                    $ferry_total = 0;
                                                    $gtours_total = 0;
                                                    $servie_total = 0;
                                                    $date = $row_booking['created_at'];
                                                    $yr = explode("-", $date);
                                                    $cart_checkout_data = ($row_booking['cart_checkout_data'] != '' && $row_booking['cart_checkout_data'] != 'null') ? json_decode($row_booking['cart_checkout_data']) : [];
                                                    
                                                    for($i=0;$i<sizeof($cart_checkout_data);$i++){
                                                        if($cart_checkout_data[$i]->service->name == 'Hotel'){
                                                            $hotel_flag = 1;
                                                            $tax_arr = explode(',',$cart_checkout_data[$i]->service->hotel_arr->tax);
                                                            for($j=0;$j<sizeof($cart_checkout_data[$i]->service->item_arr);$j++){
                                                                $room_types = explode('-',$cart_checkout_data[$i]->service->item_arr[$j]);
                                                                $room_cost = $room_types[2];
                                                                $h_currency_id = $room_types[3];
                                                                $tax_amount = 0;
                                                                
                                                                $tax_arr1 = explode('+',$tax_arr[0]);
                                                                for($t=0;$t<sizeof($tax_arr1);$t++){
                                                                    if($tax_arr1[$t]!=''){
                                                                        $tax_arr2 = explode(':',$tax_arr1[$t]);
                                                                        if($tax_arr2[2] == "Percentage"){
                                                                            $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                                                                        }else{
                                                                            $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
                                                                        }
                                                                    }
                                                                }
                                                                $total_amount = $room_cost + $tax_amount;
                                                
                                                                //Convert into default currency
                                                                $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
                                                                $from_currency_rate = $sq_from['currency_rate'];
                                                                $total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
                                                            
                                                                $hotel_total += $total_amount;
                                                            }
                                                        }
                                                        if($cart_checkout_data[$i]->service->name == 'Transfer'){
                                                
                                                            $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
                                                            for($j=0;$j<count(array($services));$j++){
                                                                $tax_amount = 0;
                                                                $tax_arr = explode(',',$services->service_arr[$j]->taxation);
                                                                $transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
                                                                $room_cost = $transfer_cost[0];
                                                                $h_currency_id = $transfer_cost[1];
                                                                
                                                                $tax_arr1 = explode('+',$tax_arr[0]);
                                                                for($t=0;$t<sizeof($tax_arr1);$t++){
                                                                    if($tax_arr1[$t]!=''){
                                                                        $tax_arr2 = explode(':',$tax_arr1[$t]);
                                                                        if($tax_arr2[2] == "Percentage"){
                                                                            $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                                                                        }else{
                                                                            $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
                                                                        }
                                                                    }
                                                                }
                                                                $total_amount = $room_cost + $tax_amount;
                                                
                                                                //Convert into default currency
                                                                $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
                                                                $from_currency_rate = $sq_from['currency_rate'];
                                                                $total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
                                                            
                                                                $transfer_total += $total_amount;
                                                            }
                                                        }
                                                        if($cart_checkout_data[$i]->service->name == 'Activity'){
                                                            $activity_flag = 1;
                                                            $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
                                                            for($j=0;$j<count(array($services));$j++){
                                                            
                                                                $tax_amount = 0;
                                                                $tax_arr = explode(',',$services->service_arr[$j]->taxation);
                                                                $transfer_cost = explode('-',$services->service_arr[$j]->transfer_type);
                                                                $room_cost = $transfer_cost[1];
                                                                $h_currency_id = $transfer_cost[2];
                                                                
                                                                $tax_arr1 = explode('+',$tax_arr[0]);
                                                                for($t=0;$t<sizeof($tax_arr1);$t++){
                                                                    if($tax_arr1[$t]!=''){
                                                                        $tax_arr2 = explode(':',$tax_arr1[$t]);
                                                                        if($tax_arr2[2] === "Percentage"){
                                                                            $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                                                                        }else{
                                                                            $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
                                                                        }
                                                                    }
                                                                }
                                                                $total_amount = $room_cost + $tax_amount;
                                                
                                                                //Convert into default currency
                                                                $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
                                                                $from_currency_rate = $sq_from['currency_rate'];
                                                                $total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
                                                            
                                                                $activity_total += $total_amount;
                                                            }
                                                        }
                                                        if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
                                                            $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
                                                            for($j=0;$j<count(array($services));$j++){
                                                            
                                                                $tax_amount = 0;
                                                                $tax_arr = explode(',',$services->service_arr[$j]->taxation);
                                                                $package_item = explode('-',$services->service_arr[$j]->package_type);
                                                                $room_cost = $package_item[1];
                                                                $h_currency_id = $package_item[2];
                                                                
                                                                $tax_arr1 = explode('+',$tax_arr[0]);
                                                                for($t=0;$t<sizeof($tax_arr1);$t++){
                                                                    if($tax_arr1[$t]!=''){
                                                                        $tax_arr2 = explode(':',$tax_arr1[$t]);
                                                                        if($tax_arr2[2] == "Percentage"){
                                                                            $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                                                                        }else{
                                                                            $tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
                                                                        }
                                                                    }
                                                                }
                                                                $total_amount = $room_cost + $tax_amount;
                                                
                                                                //Convert into default currency
                                                                $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
                                                                $from_currency_rate = $sq_from['currency_rate'];
                                                                $total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
                                                            
                                                                $tours_total += $total_amount;
                                                            }
                                                        }
                                                        if($cart_checkout_data[$i]->service->name == 'Ferry'){
                                                            $services = ($cart_checkout_data[$i]->service!='') ? $cart_checkout_data[$i]->service : [];
                                                            for($j=0;$j<count(array($services));$j++){
                                                            
                                                                $tax_amount = 0;
                                                                $tax_arr = explode(',',$services->service_arr[$j]->taxation);
                                                                $package_item = explode('-',$services->service_arr[$j]->total_cost);
                                                                $room_cost = $package_item[0];
                                                                $h_currency_id = $package_item[1];
                                                                
                                                                $tax_arr1 = explode('+',$tax_arr[0]);
                                                                for($t=0;$t<sizeof($tax_arr1);$t++){
                                                                    if($tax_arr1[$t]!=''){
                                                                        $tax_arr2 = explode(':',$tax_arr1[$t]);
                                                                        if($tax_arr2[2] == "Percentage"){
                                                                            $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                                                                        }else{
                                                                            $tax_amount = $tax_amount + ($room_cost + $tax_arr2[1]);
                                                                        }
                                                                    }
                                                                }
                                                                $total_amount = $room_cost + $tax_amount;
                                                
                                                                //Convert into default currency
                                                                $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
                                                                $from_currency_rate = $sq_from['currency_rate'];
                                                                $total_amount = ($to_currency_rate!='') ? ($from_currency_rate / $to_currency_rate * $total_amount) : 0;
                                                            
                                                                $ferry_total += $total_amount;
                                                            }
                                                        }
                                                        if($cart_checkout_data[$i]->service->name == 'Group Tours'){
                                                            $services = isset($cart_checkout_data[$i]->service) ? $cart_checkout_data[$i]->service : [];
                                                            for($j=0;$j<count(array($services));$j++){
                                                            
                                                                $tax_arr = explode(',',$cart_checkout_data[$i]->service->service_arr[$j]->taxation);
                                                                $room_cost = $cart_checkout_data[$i]->service->service_arr[$j]->total_cost;
                                                                $h_currency_id = $cart_checkout_data[$i]->service->service_arr[$j]->currency_id;
                                                                $tax_amount = 0;
                                                                $tax_arr1 = explode('+',$tax_arr[0]);
                                                                for($t=0;$t<sizeof($tax_arr);$t++){
                                                                    if($tax_arr1[$t]!=''){
                                                                        $tax_arr2 = explode(':',$tax_arr1[$t]);
                                                                        if($tax_arr2[2] == "Percentage"){
                                                                            $tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
                                                                        }else{
                                                                            $tax_amount = $tax_amount + $tax_arr2[1];
                                                                        }
                                                                    }
                                                                }
                                                                $total_amount = $room_cost + $tax_amount;
                                                
                                                                //Convert into default currency
                                                                $sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
                                                                $from_currency_rate = $sq_from['currency_rate'];
                                                                $total_amount = ($from_currency_rate / $to_currency_rate * $total_amount);
                                                            
                                                                $gtours_total += $total_amount;
                                                            }
                                                        }
                                                    }
                                                
                                                    $servie_total = $hotel_total + $transfer_total + $activity_total + $tours_total + $ferry_total + $gtours_total;
                                                
                                                    if($row_customer['coupon_code'] != ''){
                                                        $sq_hotel_count = mysqli_num_rows(mysqlQuery("select offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_customer[coupon_code]'"));
                                                        $sq_exc_count = mysqli_num_rows(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_customer[coupon_code]'"));
                                                        if($sq_hotel_count > 0){
                                                            $sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer as offer,offer_amount from hotel_offers_tarrif where coupon_code='$row_customer[coupon_code]'"));
                                                        }else if($sq_exc_count > 0){
                                                            $sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from excursion_master_offers where coupon_code='$row_customer[coupon_code]'"));
                                                        }else{
                                                            $sq_coupon = mysqli_fetch_assoc(mysqlQuery("select offer_in as offer,offer_amount from custom_package_offers where coupon_code='$row_customer[coupon_code]'"));
                                                        }
                                                        if($sq_coupon['offer']=="Flat"){
                                                            $servie_total = $servie_total - $sq_coupon['offer_amount'];
                                                        }else{
                                                            $servie_total = $servie_total - ($servie_total*$sq_coupon['offer_amount']/100);
                                                        }
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $count; ?></td>
                                                        <td><?php echo get_b2b_booking_id($row_booking['booking_id'],$yr[0]); ?></td>
                                                        <td><?php echo get_date_user($date); ?></td>
                                                        <td><?php echo number_format($servie_total,2); ?></td>
                                                    </tr>
                                                <?php $count++; } ?>
                                            </tbody>
                                        </table>
                                </div>
                                </div>
                            <?php }else{
                                echo 'No Bookings....';
                            } ?>
                        </div>
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
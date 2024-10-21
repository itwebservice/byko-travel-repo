<?php
include '../../../model/model.php';
global $encrypt_decrypt,$secret_key,$currency;
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$from_date = get_date_db($_POST['from_date']);
$to_date = get_date_db($_POST['to_date']);
$count = 0;
$total_balance_amount = 0;
$today_date = date('Y-m-d');
?>
<div class="col-md-12">
    <div class="col-md-12 no-pad table_verflow"> 
        <div class="row"> <div class="col-md-12"> <div class="table-responsive">
            <table class="table table-hover" style="border: 0;" id="reminder_report">    
                <thead>
                    <tr class="table-heading-row">
                        <th>S_No.</th>
                        <th>Tour_Type</th>
                        <th>Booking_ID</th>
                        <th>Customer/Supplier_Name</th>
                        <th>Due_Date</th>
                        <th>Total_Amount</th>
                        <th>Paid_Amount</th>
                        <th>Balance_Amount</th>
                        <th >Actions&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </thead>
                    <tbody>
                        <!-- Package Tour -->
                        <?php
                        $q = "select branch_status from branch_assign where link='package_booking/booking/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from package_tour_booking_master where due_date between '$from_date' and '$to_date' and tour_status!='cancel' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_tour_details= mysqli_fetch_assoc($sq_tour_details)){

                            $booking_id = $row_tour_details['booking_id'];
                            $date = $row_tour_details['booking_date'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $package_id = get_package_booking_id($booking_id,$year);
                            $customer_id = $row_tour_details['customer_id'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum,sum(credit_charges) as sumc from package_payment_master where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount =  $sq_total_paid['sumc'];
                            $total_amount = $row_tour_details['net_total'] + $credit_card_amount;

                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            $package_cancel = "select cancel_amount from package_refund_traveler_estimate where booking_id='$row_tour_details[booking_id]'";
                            $cancel_est_count = mysqli_num_rows(mysqlQuery($package_cancel));
                            $cancel_est = mysqli_fetch_assoc(mysqlQuery($package_cancel));
                            $cancel_amount = ($cancel_est_count > 0) ? $cancel_est['cancel_amount'] : 0;
                            if ($cancel_est_count > 0) {
                                if ($cancel_amount <= $paid_amount) {
                                    $balance_amount = 0;
                                } else {
                                    $balance_amount =  $cancel_amount - $paid_amount + $credit_card_amount;
                                }
                            } else {
                                $cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
                                $balance_amount = $total_amount - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_tour_details['due_date'])) ? 'danger' : '';
                            
                            $total_amount1 = currency_conversion($currency,$row_tour_details['currency_code'],$total_amount);
                            $paid_amount1 = currency_conversion($currency,$row_tour_details['currency_code'],$paid_amount);
                            $balance_amount1 = currency_conversion($currency,$row_tour_details['currency_code'],$balance_amount);
                            
                            $quotation_id = $row_tour_details['quotation_id'];
                            $cust_user_name = '';
                            $sq_quo = mysqli_fetch_assoc(mysqlQuery("select user_id from package_tour_quotation_master where quotation_id='$quotation_id'"));
                            if($sq_quo['user_id'] != 0){ 
                                $row_user = mysqli_fetch_assoc(mysqlQuery("Select name from customer_users where user_id ='$sq_quo[user_id]'"));
                                $cust_user_name = ' ('.$row_user['name'].')';
                            }
                            if($balance_amount>0){
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Package Tour' ?></td>
                                    <td><?= $package_id ?></td>
                                    <td><?= $customer_name1.$cust_user_name ?></td>
                                    <td><?= get_date_user($row_tour_details['due_date']) ?></td>
                                    <td class="text-right"><?= number_format($total_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('package','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $row_tour_details['mobile_no'] ?>','<?= $package_id ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Group Tour -->
                        <?php
                        $q = "select branch_status from branch_assign where link='booking/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from tourwise_traveler_details where balance_due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_tour_details = mysqli_fetch_assoc($sq_tour_details)){

                            $booking_id = $row_tour_details['id'];
                            $date = $row_tour_details['form_date'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $booking_id1 = get_group_booking_id($booking_id,$year);
                            $customer_id = $row_tour_details['customer_id'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum,sum(credit_charges) as sumc from payment_master where tourwise_traveler_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount =  $sq_total_paid['sumc'];
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select type,first_name,last_name,company_name,contact_no from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            $total_amount = $row_tour_details['net_total'] + $credit_card_amount;
                            $pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_tour_details[traveler_group_id]'"));
                            $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_tour_details[traveler_group_id]' and status='Cancel'"));    
                            
                            if($row_tour_details['tour_group_status'] == 'Cancel'){
                                //Group Tour cancel
                                $cancel_tour_count2=mysqli_num_rows(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row_tour_details[id]'"));
                                if($cancel_tour_count2 >= '1'){
                                    $cancel_tour=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$row_tour_details[id]'"));
                                    $cancel_amount = $cancel_tour['cancel_amount'];
                                }
                                else{ $cancel_amount = 0; }
                            }
                            else{
                                // Group booking cancel
                                $cancel_esti_count1=mysqli_num_rows(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row_tour_details[id]'"));
                                if($pass_count==$cancelpass_count){
                                    $sq_group_cancel= "SELECT * from refund_traveler_estimate where tourwise_traveler_id='$row_tour_details[id]'";
                                    $cancel_esti_count = mysqli_num_rows(mysqlQuery($sq_group_cancel));
                                    $cancel_esti1 = mysqli_fetch_assoc(mysqlQuery($sq_group_cancel));
                                    $cancel_amount = ($cancel_esti_count > 0) ? $cancel_esti1['cancel_amount'] : 0;
                                }
                                else{ $cancel_amount = 0; }
                            }
                            
                            $cancel_amount = ($cancel_amount == '')?'0':$cancel_amount;
                            if($row_tour_details['tour_group_status'] == 'Cancel'){
                                if($cancel_amount > $paid_amount){
                                    $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                }
                                else{
                                    $balance_amount = 0;
                                }
                            }else{
                                if($pass_count == $cancelpass_count){
                                    if($cancel_amount > $paid_amount){
                                        $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                    }
                                    else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $total_amount - $paid_amount;
                                }
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_tour_details['balance_due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                
                                $total_amount1 = currency_conversion($currency,$row_tour_details['currency_code'],$total_amount);
                                $paid_amount1 = currency_conversion($currency,$row_tour_details['currency_code'],$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$row_tour_details['currency_code'],$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Group Tour' ?></td>
                                    <td><?= $booking_id1 ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_tour_details['balance_due_date']) ?></td>
                                    <td class="text-right"><?= number_format($total_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('group','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $booking_id1 ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Hotel -->
                        <?php
                        $q = "select branch_status from branch_assign where link='hotels/booking/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from hotel_booking_master where due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_hotel= mysqli_fetch_assoc($sq_tour_details)){

                            $booking_id = $row_hotel['booking_id'];
                            $date = $row_hotel['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $booking_id1 = get_hotel_booking_id($booking_id,$year);
                            $customer_id = $row_hotel['customer_id'];
                            $cancel_amount = $row_hotel['cancel_amount'];
                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from hotel_booking_payment where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount = $sq_total_paid['sumc'];
                            $total_amount = $row_hotel['total_fee'] + $credit_card_amount;
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $pass_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$row_hotel[booking_id]'"));
                            $cancel_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$row_hotel[booking_id]' and status='Cancel'"));
                            if($pass_count == $cancel_count){
                                if($paid_amount > 0){
                                    if($cancel_amount >0){
                                        if($paid_amount > $cancel_amount){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_amount;
                                }
                            }
                            else{
                                $balance_amount = $total_amount - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_hotel['due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$row_hotel['currency_code'],$total_amount);
                                $paid_amount1 = currency_conversion($currency,$row_hotel['currency_code'],$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$row_hotel['currency_code'],$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Hotel Booking' ?></td>
                                    <td><?= $booking_id1 ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_hotel['due_date']) ?></td>
                                    <td class="text-right"><?= number_format($total_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('hotel','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $booking_id1 ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Flight -->
                        <?php
                        $q = "select branch_status from branch_assign where link='visa_passport_ticket/ticket/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from ticket_master where due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_air= mysqli_fetch_assoc($sq_tour_details)){

                            $air_id = $row_air['ticket_id'];
                            $date = $row_air['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $ticket_id = get_ticket_booking_id($air_id,$year);
                            $customer_id = $row_air['customer_id'];
                            $cancel_amount = $row_air['cancel_amount'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from ticket_payment_master where ticket_id='$air_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount = $sq_total_paid['sumc'];
                            $air_total_cost = $row_air['ticket_total_cost'] + $credit_card_amount;
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            if($row_air['cancel_type'] == '1'){
                                if($paid_amount > 0){
                                    if($cancel_amount >0){
                                        if($paid_amount > $cancel_amount){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_amount;
                                }
                            }else if($row_air['cancel_type'] == '2'||$row_air['cancel_type'] == '3'){
                                $cancel_estimate_data = json_decode($row_air['cancel_estimate']);
                                $cancel_estimate = (!isset($cancel_estimate_data)) ? 0 : $cancel_estimate_data[0]->ticket_total_cost;
                                $balance_amount = (($air_total_cost - floatval($cancel_estimate)) + $cancel_amount) - $paid_amount;
                            }
                            else{
                                $balance_amount = $air_total_cost - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_air['due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$currency,$air_total_cost);
                                $paid_amount1 = currency_conversion($currency,$currency,$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$currency,$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Flight Booking' ?></td>
                                    <td><?= $ticket_id ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_air['due_date']) ?></td>
                                    <td class="text-right"><?= number_format($air_total_cost,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('flight','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $ticket_id ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Train -->
                        <?php
                        $q = "select branch_status from branch_assign where link='visa_passport_ticket/train_ticket/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from train_ticket_master where payment_due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_air= mysqli_fetch_assoc($sq_tour_details)){

                            $train_ticket_id = $row_air['train_ticket_id'];
                            $date = $row_air['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $ticket_id = get_train_ticket_booking_id($train_ticket_id,$year);
                            $customer_id = $row_air['customer_id'];
                            $cancel_amount = $row_air['cancel_amount'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from train_ticket_payment_master where train_ticket_id='$train_ticket_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount = $sq_total_paid['sumc'];
                            $air_total_cost = $row_air['net_total'] + $credit_card_amount;
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            $pass_count = mysqli_num_rows(mysqlQuery("select * from train_ticket_master_entries where train_ticket_id='$train_ticket_id'"));
                            $cancel_count = mysqli_num_rows(mysqlQuery("select * from train_ticket_master_entries where train_ticket_id='$train_ticket_id' and status='Cancel'"));
                            if($pass_count == $cancel_count){
                                if($paid_amount > 0){
                                    if($cancel_amount >0){
                                        if($paid_amount > $cancel_amount){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_amount;
                                }
                            }
                            else{
                                $balance_amount = $air_total_cost - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_air['payment_due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$currency,$air_total_cost);
                                $paid_amount1 = currency_conversion($currency,$currency,$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$currency,$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Train Booking' ?></td>
                                    <td><?= $ticket_id ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_air['payment_due_date']) ?></td>
                                    <td class="text-right"><?= number_format($air_total_cost,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('train','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $ticket_id ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Visa -->
                        <?php
                        $q = "select branch_status from branch_assign where link='visa_passport_ticket/visa/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from visa_master where due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_air= mysqli_fetch_assoc($sq_tour_details)){

                            $visa_id = $row_air['visa_id'];
                            $date = $row_air['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $ticket_id = get_visa_booking_id($visa_id,$year);
                            $cancel_amount = $row_air['cancel_amount'];
                            $customer_id = $row_air['customer_id'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from visa_payment_master where visa_id='$visa_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount = $sq_total_paid['sumc'];
                            $air_total_cost = $row_air['visa_total_cost'] + $credit_card_amount;
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            $pass_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$visa_id'"));
                            $cancel_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$visa_id' and status='Cancel'"));
                            if($pass_count == $cancel_count){
                                if($paid_amount > 0){
                                    if($cancel_amount >0){
                                        if($paid_amount > $cancel_amount){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_amount;
                                }
                            }
                            else{
                                $balance_amount = $air_total_cost - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_air['due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$row_air['currency_code'],$air_total_cost);
                                $paid_amount1 = currency_conversion($currency,$row_air['currency_code'],$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$row_air['currency_code'],$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Visa Booking' ?></td>
                                    <td><?= $ticket_id ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_air['due_date']) ?></td>
                                    <td class="text-right"><?= number_format($air_total_cost,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('visa','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $ticket_id ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Car -->
                        <?php
                        $q = "select branch_status from branch_assign where link='car_rental/booking/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from car_rental_booking where due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_air= mysqli_fetch_assoc($sq_tour_details)){

                            $booking_id = $row_air['booking_id'];
                            $date = $row_air['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $ticket_id = get_car_rental_booking_id($booking_id,$year);
                            $customer_id = $row_air['customer_id'];
                            $cancel_amount = $row_air['cancel_amount'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from car_rental_payment where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount = $sq_total_paid['sumc'];
                            $air_total_cost = $row_air['total_fees'] + $credit_card_amount;
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            if($row_air['status'] == 'Cancel'){
                                if($paid_amount > 0){
                                    if($cancel_amount >0){
                                        if($paid_amount > $cancel_amount){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_amount;
                                }
                            }
                            else{
                                $balance_amount = $air_total_cost - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_air['due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$currency,$air_total_cost);
                                $paid_amount1 = currency_conversion($currency,$currency,$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$currency,$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Car Rental Booking' ?></td>
                                    <td><?= $ticket_id ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_air['due_date']) ?></td>
                                    <td class="text-right"><?= number_format($air_total_cost,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('car','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $ticket_id ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Activity -->
                        <?php
                        $q = "select branch_status from branch_assign where link='excursion/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from excursion_master where due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_air= mysqli_fetch_assoc($sq_tour_details)){

                            $exc_id = $row_air['exc_id'];
                            $date = $row_air['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $ticket_id = get_exc_booking_id($exc_id,$year);
                            $customer_id = $row_air['customer_id'];
                            $cancel_amount = $row_air['cancel_amount'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from exc_payment_master where exc_id='$exc_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $credit_card_amount = $sq_total_paid['sumc'];
                            $air_total_cost = $row_air['exc_total_cost'] + $credit_card_amount;
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            $pass_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$exc_id'"));
                            $cancel_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$exc_id' and status='Cancel'"));
                            if($pass_count == $cancel_count){
                                if($paid_amount > 0){
                                    if($cancel_amount >0){
                                        if($paid_amount > $cancel_amount){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_amount;
                                }
                            }
                            else{
                                $balance_amount = $air_total_cost - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_air['due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$row_air['currency_code'],$air_total_cost);
                                $paid_amount1 = currency_conversion($currency,$row_air['currency_code'],$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$row_air['currency_code'],$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Activity Booking' ?></td>
                                    <td><?= $ticket_id ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_air['due_date']) ?></td>
                                    <td class="text-right"><?= number_format($air_total_cost,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('activity','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $ticket_id ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Misc -->
                        <?php
                        $q = "select branch_status from branch_assign where link='miscellaneous/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from miscellaneous_master where due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_air= mysqli_fetch_assoc($sq_tour_details)){

                            $misc_id = $row_air['misc_id'];
                            $date = $row_air['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $ticket_id = get_misc_booking_id($misc_id,$year);
                            
                            $customer_id = $row_air['customer_id'];
                            $cancel_amount = $row_air['cancel_amount'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from miscellaneous_payment_master where misc_id='$misc_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $customer_name = mysqli_fetch_assoc(mysqlQuery("select contact_no,type,first_name,last_name,company_name from customer_master where customer_id='$customer_id'"));
                            $customer_name1 = ($customer_name['type'] == 'Corporate'||$customer_name['type'] == 'B2B') ? $customer_name['company_name'] : $customer_name1 = $customer_name['first_name'].' '.$customer_name['last_name'];
                            $contact_no = $encrypt_decrypt->fnDecrypt($customer_name['contact_no'], $secret_key);
                            $credit_card_amount = $sq_total_paid['sumc'];
                            $paid_amount = $sq_total_paid['sum'] + $credit_card_amount;
                            $air_total_cost = $row_air['misc_total_cost'] + $credit_card_amount;
                            $pass_count = mysqli_num_rows(mysqlQuery("select * from miscellaneous_master_entries where misc_id = '$misc_id'"));
                            $cancel_count = mysqli_num_rows(mysqlQuery("select * from miscellaneous_master_entries where misc_id='$misc_id' and status='Cancel'"));
                            if($pass_count == $cancel_count){
                                if($paid_amount > 0){
                                    if($cancel_amount >0){
                                        if($paid_amount > $cancel_amount){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_amount - $paid_amount + $credit_card_amount;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_amount;
                                }
                            }
                            else{
                                $balance_amount = $air_total_cost - $paid_amount;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_air['due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$currency,$air_total_cost);
                                $paid_amount1 = currency_conversion($currency,$currency,$paid_amount);
                                $balance_amount1 = currency_conversion($currency,$currency,$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= 'Miscellaneous Booking' ?></td>
                                    <td><?= $ticket_id ?></td>
                                    <td><?= $customer_name1 ?></td>
                                    <td><?= get_date_user($row_air['due_date']) ?></td>
                                    <td class="text-right"><?= number_format($air_total_cost,2) ?></td>
                                    <td class="text-right"><?= number_format($paid_amount,2) ?></td>
                                    <td class="text-right"><?= number_format($balance_amount,2) ?></td>
                                    <td><button class="btn btn-info btn-sm" onclick="whatsapp_reminder('misc','<?= $customer_name1 ?>','<?= $total_amount1 ?>','<?= $paid_amount1 ?>','<?= $balance_amount1 ?>','<?= $contact_no ?>','<?= $ticket_id ?>')" data-toggle="tooltip" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!-- Vendor -->
                        <?php
                        $q = "select branch_status from branch_assign where link='vendor/dashboard/index.php'";
                        $sq_count = mysqli_num_rows(mysqlQuery($q));
                        $sq = mysqli_fetch_assoc(mysqlQuery($q));
                        $branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
                        $query = "select * from vendor_estimate where due_date between '$from_date' and '$to_date' and delete_status='0'";
                        include "../../../model/app_settings/branchwise_filteration.php";
                        $sq_tour_details = mysqlQuery($query);
                        while($row_air= mysqli_fetch_assoc($sq_tour_details)){

                            $estimate_id = $row_air['estimate_id'];
                            $date = $row_air['created_at'];
                            $yr = explode("-", $date);
                            $year = $yr[0];
                            $ticket_id = get_vendor_estimate_id($estimate_id,$year);
                            
                            $air_total_cost = $row_air['net_total'];
                            $cancel_est = $row_air['cancel_amount'];

                            $sq_total_paid =  mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum from vendor_payment_master where estimate_id='$estimate_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                            $total_paid = $sq_total_paid['sum'];
                            $vendor_type = $row_air['vendor_type'];
                            $estimate_type = $row_air['estimate_type'];
                            $vendor_type_id = $row_air['vendor_type_id'];
                            if($vendor_type=="Hotel Vendor"){
                                $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$vendor_type_id'"));
                                $vendor_name = $sq_hotel['hotel_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_hotel['mobile_no'], $secret_key);
                            }	
                            if($vendor_type=="Transport Vendor"){
                                $sq_transport = mysqli_fetch_assoc(mysqlQuery("select * from transport_agency_master where transport_agency_id='$vendor_type_id'"));
                                $vendor_name = $sq_transport['transport_agency_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_transport['mobile_no'], $secret_key);
                            }	
                            if($vendor_type=="Car Rental Vendor"){
                                $sq_cra_rental_vendor = mysqli_fetch_assoc(mysqlQuery("select * from car_rental_vendor where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_cra_rental_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_cra_rental_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="DMC Vendor"){
                                $sq_dmc_vendor = mysqli_fetch_assoc(mysqlQuery("select * from dmc_master where dmc_id='$vendor_type_id'"));
                                $vendor_name = $sq_dmc_vendor['company_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_dmc_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="Visa Vendor"){
                                $sq_visa_vendor = mysqli_fetch_assoc(mysqlQuery("select * from visa_vendor where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_visa_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_visa_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="Ticket Vendor"){
                                $sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from ticket_vendor where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="Train Ticket Vendor"){
                                $sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_vendor where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="Itinerary Vendor"){
                                $sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from site_seeing_vendor where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="Insurance Vendor"){
                                $sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from insuarance_vendor where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="Other Vendor"){
                                $sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from other_vendors where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_vendor['mobile_no'], $secret_key);
                            }
                            if($vendor_type=="Excursion Vendor"){
                                  $sq_vendor = mysqli_fetch_assoc(mysqlQuery("select * from site_seeing_vendor where vendor_id='$vendor_type_id'"));
                                $vendor_name = $sq_vendor['vendor_name'];
                                $mobile_no = $encrypt_decrypt->fnDecrypt($sq_vendor['mobile_no'], $secret_key);
                            }
                            
                            if($row_air['purchase_return'] == '1'){
                                $status = 'cancel';
                                if($total_paid > 0){
                                    if($cancel_est >0){
                                        if($total_paid > $cancel_est){
                                            $balance_amount = 0;
                                        }else{
                                            $balance_amount = $cancel_est - $total_paid;
                                        }
                                    }else{
                                        $balance_amount = 0;
                                    }
                                }
                                else{
                                    $balance_amount = $cancel_est;
                                }
                            }else if($row_air['purchase_return'] == '2'){
                                $status = 'cancel';
                                $cancel_estimate = json_decode($row_air['cancel_estimate']);
                                $balance_amount = (($air_total_cost - floatval($cancel_estimate[0]->net_total)) + $cancel_est) - $total_paid;
                            }
                            else{
                                $status = '';
                                $balance_amount = $air_total_cost - $total_paid;
                            }
                            $total_balance_amount += floatval($balance_amount);
                            $bg = ($today_date > get_date_db($row_air['due_date'])) ? 'danger' : '';
                            if($balance_amount>0){
                                $total_amount1 = currency_conversion($currency,$row_air['currency_code'],$air_total_cost);
                                $paid_amount1 = currency_conversion($currency,$row_air['currency_code'],$total_paid);
                                $balance_amount1 = currency_conversion($currency,$row_air['currency_code'],$balance_amount);
                                ?>
                                <tr class="<?= $bg ?>">
                                    <td><?= ++$count ?></td>
                                    <td><?= $vendor_type ?></td>
                                    <td><?= $ticket_id ?></td>
                                    <td><?= $vendor_name ?></td>
                                    <td><?= get_date_user($row_air['due_date']) ?></td>
                                    <td class="text-right"><?= $air_total_cost ?></td>
                                    <td class="text-right"><?= $total_paid ?></td>
                                    <td class="text-right"><?= $balance_amount ?></td>
                                    <td>NA</td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-right">Total Balance: </th>
                            <th class="<?= 'success' ?> text-right"><?= number_format($total_balance_amount,2) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
    </div></div>
    </div>
</div>
<script>
$('#reminder_report').dataTable({
    "pagingType": "full_numbers"
});
</script>

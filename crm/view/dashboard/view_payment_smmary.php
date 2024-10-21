<?php
include '../../model/model.php';
$booking_id = $_POST['booking_id'];
$tour_type = $_POST['tour_type'];
$count = $_POST['count'];
?>

<div class="modal fade profile_box_modal" id="view_summary_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="text-align:center!important;">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?= 'Payment Summary' ?></h4>
        </div>
        <div class="modal-body profile_box_padding">
            <?php
            if($tour_type == 'Package Tour'){
                $sq_package_info = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));
                $date = $sq_package_info['booking_date'];
                $yr = explode("-", $date);
                $year =$yr[0];
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum,sum(`credit_charges`) as sumc from package_payment_master where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $credit_card_amount = $query['sumc'];
                $paid_amount = $query['sum'] + $credit_card_amount;
                $paid_amount = ($paid_amount == '') ? '0' : $paid_amount;
                $sale_total_amount = $sq_package_info['net_total'] + $credit_card_amount;
                if ($sale_total_amount == "") {
                    $sale_total_amount = 0;
                }
                $q = "select * from package_refund_traveler_estimate where booking_id='$sq_package_info[booking_id]'";
                $cancel_est_count = mysqli_num_rows(mysqlQuery($q));
                $cancel_est = mysqli_fetch_assoc(mysqlQuery($q));
                $cancel_amount = ($cancel_est_count > 0) ? $cancel_est['cancel_amount'] : 0;
                if ($cancel_amount != '') {
                    if ($cancel_amount <= $paid_amount) {
                        $balance_amount = 0;
                    } else {
                        $balance_amount =  $cancel_amount - $paid_amount + $credit_card_amount;
                    }
                } else {
                    $cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
                    $balance_amount = $sale_total_amount - $paid_amount;
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_package_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";

                ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="profile_box main_block" style="margin-top: 25px">
                            <div class="table-responsive">
                                <table class="table table-bordered no-marg">
                                    <thead>
                                        <tr class="table-heading-row">
                                            <th>S_No.</th>
                                            <th>Date</th>
                                            <th>Mode</th>
                                            <th>Bank_Name</th>
                                            <th>Cheque_No/ID</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 0;
                                        $query2 = "SELECT * from package_payment_master where booking_id='$booking_id' and amount != '0'";
                                        $sq_package_payment = mysqlQuery($query2);
                                        $bg = "";

                                        while ($row_package_payment = mysqli_fetch_assoc($sq_package_payment)) {

                                            $count++;
                                            $bg = '';
                                            if ($row_package_payment['clearance_status'] == "Pending") {
                                                $bg = "warning";
                                            } else if ($row_package_payment['clearance_status'] == "Cancelled") {
                                                $bg = "danger";
                                            }else if ($row_package_payment['clearance_status'] == "Cleared") {
                                                $bg = "success";
                                            }else{
                                                $bg = "";
                                            }
                                            
                                        ?>
                                            <tr class="<?php echo $bg; ?>">
                                                <td><?php echo $count; ?></td>
                                                <td><?php echo get_date_user($row_package_payment['date']); ?></td>
                                                <td><?php echo $row_package_payment['payment_mode']; ?></td>
                                                <td><?php echo $row_package_payment['bank_name']; ?></td>
                                                <td><?php echo $row_package_payment['transaction_id']; ?></td>
                                                <td class="text-right"><?php echo number_format($row_package_payment['amount'] + $row_package_payment['credit_charges'], 2); ?></td>
                                            </tr>
                                        <?php }  ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
            if($tour_type == 'B2C'){
                $sq_package_info = mysqli_fetch_assoc(mysqlQuery("select * from b2c_sale where booking_id='$booking_id'"));
                $date = $sq_package_info['created_at'];
                $yr = explode("-", $date);
                $year = $yr[0];
                $costing_data = json_decode($sq_package_info['costing_data']);
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(`credit_charges`) as sumc from b2c_payment_master where booking_id='$sq_package_info[booking_id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $credit_card_amount = $query['sumc'];
                $paid_amount = $query['sum'];
                $paid_amount = ($paid_amount == '') ? '0' : $paid_amount;
                $sale_total_amount = $costing_data[0]->net_total;
                if ($sale_total_amount == "") {
                    $sale_total_amount = 0;
                }
                $cancel_amount = $sq_package_info['cancel_amount'];
                if ($cancel_amount != 0) {
                    if ($cancel_amount <= $paid_amount) {
                        $balance_amount = 0;
                    } else {
                        $balance_amount =  $cancel_amount - $paid_amount;
                    }
                } else {
                    $cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
                    $balance_amount = $sale_total_amount - $paid_amount;
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_b2c_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="profile_box main_block" style="margin-top: 25px">
                            <div class="table-responsive">
                                <table class="table table-bordered no-marg">
                                    <thead>
                                        <tr class="table-heading-row">
                                            <th>S_No.</th>
                                            <th>Date</th>
                                            <th>Mode</th>
                                            <th>Receipt_ID</th>
                                            <th>Bank_Name</th>
                                            <th>Cheque_No/ID</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 0;
                                        $query2 = "SELECT * from b2c_payment_master where booking_id='$sq_package_info[booking_id]' and payment_amount != '0'";
                                        $sq_package_payment = mysqlQuery($query2);
                                        $bg = "";
                                        while ($row_package_payment = mysqli_fetch_assoc($sq_package_payment)) {
                
                                            $count++;
                                            $bg = '';
                                            if ($row_package_payment['clearance_status'] == "Pending") {
                                                $bg = "warning";
                                            } else if ($row_package_payment['clearance_status'] == "Cancelled") {
                                                $bg = "danger";
                                            } else if ($row_package_payment['clearance_status'] == "Cleared") {
                                                $bg = "success";
                                            }else{
                                                $bg = "";
                                            }
                                        ?>
                                            <tr class="<?php echo $bg; ?>">
                                                <td><?php echo $count; ?></td>
                                                <td><?php echo get_date_user($row_package_payment['payment_date']); ?></td>
                                                <td><?php echo $row_package_payment['payment_mode']; ?></td>
                                                <td><?php echo ($row_package_payment['payment_mode'] == 'Online') ? $row_package_payment['payment_id'] : 'NA'; ?></td>
                                                <td><?php echo $row_package_payment['bank_name']; ?></td>
                                                <td><?php echo $row_package_payment['transaction_id']; ?></td>
                                                <td class="text-right"><?php echo number_format($row_package_payment['payment_amount'] + $row_package_payment['credit_charges'], 2); ?></td>
                                            </tr>
                                        <?php }  ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } 
            if($tour_type == 'Hotel Booking'){
                $sq_hotel_info = mysqli_fetch_assoc(mysqlQuery("select * from hotel_booking_master where booking_id='$booking_id' and delete_status='0'"));
                $date = $sq_hotel_info['created_at'];
                $yr = explode("-", $date);
                $year =$yr[0];
                //sale 
                $sale_total_amount=$sq_hotel_info['total_fee'];
                if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
                
                //Cancel
                $cancel_amount=$sq_hotel_info['cancel_amount'];
                $pass_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$sq_hotel_info[booking_id]'"));
                $cancel_count = mysqli_num_rows(mysqlQuery("select * from hotel_booking_entries where booking_id='$sq_hotel_info[booking_id]' and status='Cancel'"));
                
                //Paid
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum ,sum(credit_charges) as sumc from hotel_booking_payment where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $paid_amount = $query['sum']+ $query['sumc'];
                $paid_amount = ($paid_amount == '')?'0':$paid_amount;
                $sale_total_amount = $sale_total_amount + $query['sumc'];
                
                if($pass_count == $cancel_count){
                    if($paid_amount > 0){
                        if($cancel_amount >0){
                            if($paid_amount > $cancel_amount){
                                $balance_amount = 0;
                            }else{
                                $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
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
                    $balance_amount = $sale_total_amount - $paid_amount;
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_hotel_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                    $count = 0;
                                    $query = "select * from hotel_booking_payment where booking_id='$booking_id' and delete_status='0' ";
                                    $sq_payment = mysqlQuery($query);
                                    while($row_payment = mysqli_fetch_assoc($sq_payment))
                                    {
                                        if($row_payment['payment_amount'] != '0'){
                                            $count++;
                                            $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from hotel_booking_master where booking_id='$row_payment[booking_id]'"));
                                            $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_booking[customer_id]'"));
                                            
                                            $bg="";
                
                                            if($row_payment['clearance_status']=="Pending"){ 
                                                $bg='warning';
                                            }
                                            else if($row_payment['clearance_status']=="Cancelled"){ 
                                                $bg='danger';
                                            }
                                            else if($row_payment['clearance_status']=="Cleared"){ 
                                                $bg='success';
                                            }
                                            else{ $bg = '';}
                                
                
                                        ?>
                                        <tr class="<?= $bg;?>">				
                                            <td><?= $count ?></td>
                                            <td><?= get_date_user($row_payment['payment_date']) ?></td>
                                            <td><?= $row_payment['payment_mode'] ?></td>
                                            <td><?= $row_payment['bank_name'] ?></td>
                                            <td><?= $row_payment['transaction_id'] ?></td>
                                            <td><?= number_format($row_payment['payment_amount'] + $row_payment['credit_charges'] , 2) ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            <?php }
            if($tour_type == 'Flight Booking'){

                $sq_visa_info = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$booking_id' and delete_status='0'"));
                $date = $sq_visa_info['created_at'];
                $yr = explode("-", $date);
                $year =$yr[0];
                
                //Cancel
                $cancel_amount=$sq_visa_info['cancel_amount'];
                $pass_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$sq_visa_info[ticket_id]'"));
                $cancel_count = mysqli_num_rows(mysqlQuery("select * from ticket_master_entries where ticket_id='$sq_visa_info[ticket_id]' and status='Cancel'"));
                
                //Paid
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from ticket_payment_master where ticket_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $charge = $query['sumc'];
                $paid_amount = $query['sum']+$charge;
                $paid_amount = ($paid_amount == '')?'0':$paid_amount;
                
                //Sale
                $sale_total_amount=$sq_visa_info['ticket_total_cost']+$charge;
                if($sale_total_amount==""){  $sale_total_amount = 0 ;  }

                if($sq_visa_info['cancel_type'] == '1'){
                    if($paid_amount > 0){
                        if($cancel_amount >0){
                            if($paid_amount > $cancel_amount){
                                $balance_amount = 0;
                            }else{
                                $balance_amount = $cancel_amount - $paid_amount + $charge;
                            }
                        }else{
                            $balance_amount = 0;
                        }
                    }
                    else{
                        $balance_amount = $cancel_amount;
                    }
                }else if($sq_visa_info['cancel_type'] == '2'||$sq_visa_info['cancel_type'] == '3'){
                    $cancel_estimate = json_decode($sq_visa_info['cancel_estimate']);
                    $balance_amount = (($sale_total_amount - floatval($cancel_estimate[0]->ticket_total_cost)) + $cancel_amount) - $paid_amount;
                }
                else{
                    $balance_amount = $sale_total_amount - $paid_amount;
                }
                $balance_amount = ($balance_amount < 0) ? 0 : $balance_amount;
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_ticket_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query = "select * from ticket_payment_master where ticket_id='$booking_id' and payment_amount!='0'";
                                $count = 0;
                                $sq_pending_amount=0;
                                $sq_cancel_amount=0;
                                $sq_paid_amount=0;
                                $total_payment=0;
                                $sq_ticket_payment = mysqlQuery($query);
                                while($row_ticket_payment = mysqli_fetch_assoc($sq_ticket_payment)){

                                    $count++;
                                    $sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$row_ticket_payment[ticket_id]' and delete_status='0'"));
                                    $bg='';
                                    $sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_ticket_info[customer_id]'"));

                                    if($row_ticket_payment['clearance_status']=="Pending"){ 
                                        $bg='warning';
                                    }

                                    else if($row_ticket_payment['clearance_status']=="Cancelled"){ 
                                        $bg='danger';
                                    }
                                    else if($row_ticket_payment['clearance_status']=="Cleared"){ 
                                        $bg='success';
                                    }

                                    else{ $bg=''; }
                                    ?>
                                    <tr class="<?= $bg?>">
                                        <td><?= $count ?></td>
                                        <td><?= get_date_user($row_ticket_payment['payment_date']) ?></td>
                                        <td><?= $row_ticket_payment['payment_mode'] ?></td>
                                        <td><?= $row_ticket_payment['bank_name'] ?></td>
                                        <td><?= $row_ticket_payment['transaction_id'] ?></td>
                                        <td class="text-right"><?= number_format($row_ticket_payment['payment_amount']+ $row_ticket_payment['credit_charges'],2) ?></td>
                                    </tr>
                                    <?php
                                }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            <?php }
            if($tour_type == 'Train Booking'){

                $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$booking_id' and delete_status='0'"));
                $date = $sq_booking['created_at'];
                $yr = explode("-", $date);
                $year =$yr[0];
                //Cancel
                $cancel_amount = $sq_booking['cancel_amount'];
                $pass_count = mysqli_num_rows(mysqlQuery("select * from  train_ticket_master_entries where train_ticket_id='$sq_booking[train_ticket_id]'"));
                $cancel_count = mysqli_num_rows(mysqlQuery("select * from  train_ticket_master_entries where train_ticket_id='$sq_booking[train_ticket_id]' and status='Cancel'"));

                //Paid
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from train_ticket_payment_master where train_ticket_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $paid_amount = $query['sum'] + $query['sumc'];
                $paid_amount = ($paid_amount == '') ? '0' : $paid_amount;

                //sale
                $sale_total_amount = $sq_booking['net_total'] + $query['sumc'];
                if ($sale_total_amount == "") {
                    $sale_total_amount = 0;
                }
                if ($pass_count == $cancel_count) {
                    if ($paid_amount > 0) {
                        if ($cancel_amount > 0) {
                            if ($paid_amount > $cancel_amount) {
                                $balance_amount = 0;
                            } else {
                                $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
                            }
                        } else {
                            $balance_amount = 0;
                        }
                    } else {
                        $balance_amount = $cancel_amount;
                    }
                } else {
                    $balance_amount = $sale_total_amount - $paid_amount;
                }?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_train_ticket_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query = "select * from train_ticket_payment_master where 1";
                                if ($financial_year_id != "") {
                                    $query .= " and financial_year_id='$financial_year_id'";
                                }
                                if ($booking_id != "") {
                                    $query .= " and train_ticket_id='$booking_id'";
                                }
                                if ($payment_mode != "") {
                                    $query .= " and payment_mode='$payment_mode'";
                                }
                                if ($customer_id != "") {
                                    $query .= " and train_ticket_id in (select train_ticket_id from train_ticket_master where customer_id='$customer_id')";
                                }
                                if ($payment_from_date != '' && $payment_to_date != '') {
                                    $payment_from_date = get_date_db($payment_from_date);
                                    $payment_to_date = get_date_db($payment_to_date);
                                    $query .= " and payment_date between '$payment_from_date' and '$payment_to_date'";
                                }

                                $bg;
                                $sq_train_ticket_payment = mysqlQuery($query);
                                $sq_pending_amount = 0;
                                $sq_cancel_amount = 0;
                                $sq_paid_amount = 0;
                                $count = 0;

                                while ($row_train_ticket_payment = mysqli_fetch_assoc($sq_train_ticket_payment)) {
                                    if ($row_train_ticket_payment['payment_amount'] != '0') {
                                        $count++;

                                        if ($row_train_ticket_payment['clearance_status'] == "Pending") {
                                            $bg = 'warning';
                                        } else if ($row_train_ticket_payment['clearance_status'] == "Cancelled") {
                                            $bg = 'danger';
                                        } else if ($row_train_ticket_payment['clearance_status'] == "Cleared") {
                                            $bg = "success";
                                        }else {
                                            $bg = '';
                                        }

                                        ?>
                                        <tr class="<?= $bg; ?>">
                                            <td><?= $count ?></td>
                                            <td><?= get_date_user($row_train_ticket_payment['payment_date']) ?></td>
                                            <td><?= $row_train_ticket_payment['payment_mode'] ?></td>
                                            <td><?= $row_train_ticket_payment['bank_name'] ?></td>
                                            <td><?= $row_train_ticket_payment['transaction_id'] ?></td>
                                            <td class="text-right"><?= number_format($row_train_ticket_payment['payment_amount'] + $row_train_ticket_payment['credit_charges'], 2) ?></td>
                                        </tr>
                                    <?php
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            <?php }
            if($tour_type == 'Bus Booking'){

                $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id='$booking_id' and delete_status='0'"));
                $date = $sq_booking['created_at'];
                $yr = explode("-", $date);
                $year =$yr[0];
                
                //paid
                $cancel_amount=$sq_booking['cancel_amount'];
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from bus_booking_payment_master where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $paid_amount = $query['sum'] + $query['sumc'];
                $paid_amount = ($paid_amount == '')?'0':$paid_amount;
                //sale 
                $sale_total_amount=$sq_booking['net_total'] + $query['sumc'];
                if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
                
                //Cancel
                $cancel_amount=$sq_booking['cancel_amount'];
                $pass_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$sq_booking[booking_id]'"));
                $cancel_count = mysqli_num_rows(mysqlQuery("select * from bus_booking_entries where booking_id='$sq_booking[booking_id]' and status='Cancel'"));
                
                if($pass_count == $cancel_count){
                    if($paid_amount > 0){
                        if($cancel_amount >0){
                            if($paid_amount > $cancel_amount){
                                $balance_amount = 0;
                            }else{
                                $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
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
                    $balance_amount = $sale_total_amount - $paid_amount;
                }?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_bus_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $query = "SELECT * from bus_booking_payment_master where 1";		
                                    if($financial_year_id!=""){
                                        $query .= " and financial_year_id='$financial_year_id'";
                                    }
                                    if($booking_id!=""){
                                        $query .= " and booking_id='$booking_id'";
                                    }
                                    if($payment_mode!=""){
                                        $query .= " and payment_mode='$payment_mode'";
                                    }
                                    if($customer_id!=""){
                                        $query .= " and booking_id in (select booking_id from bus_booking_master where customer_id='$customer_id')";
                                    }
                                    if($payment_from_date!='' && $payment_to_date!=''){
                                        $payment_from_date = get_date_db($payment_from_date);
                                        $payment_to_date = get_date_db($payment_to_date);
                                        $query .=" and payment_date between '$payment_from_date' and '$payment_to_date'";
                                    }
                                    $bg;
                                    $count = 0;
                                    $total_paid_amt=0;

                                    $sq_pending_amount=0;
                                    $sq_cancel_amount=0;
                                    $sq_paid_amount=0;
                                    $Total_payment=0;
                                
                                    $sq_payment = mysqlQuery($query);

                                    while($row_payment = mysqli_fetch_assoc($sq_payment)){
                                        if($row_payment['payment_amount'] != '0'){
                                            $count++;

                                            $sq_bus_info = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id='$row_payment[booking_id]'"));
                                            $sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_bus_info[customer_id]'"));
                                            
                                            $bg='';
                                            $sq_paid_amount = $sq_paid_amount + $row_payment['payment_amount'];
                                            if($row_payment['clearance_status']=="Pending"){ 
                                                $bg='warning';
                                                $sq_pending_amount = $sq_pending_amount + $row_payment['payment_amount'];
                                            }
                                            else if($row_payment['clearance_status']=="Cancelled"){ 
                                                $bg='danger';
                                                $sq_cancel_amount = $sq_cancel_amount + $row_payment['payment_amount'];
                                            }
                                            else if($row_payment['clearance_status']=="Cleared"){ 
                                                $bg='success';
                                            }

                                            ?>
                                            <tr class="<?= $bg;?>">				
                                                <td><?= $count ?></td>
                                                <td><?= get_date_user($row_payment['payment_date']) ?></td>
                                                <td><?= $row_payment['payment_mode'] ?></td>
                                                <td><?= $row_payment['bank_name'] ?></td>
                                                <td><?= $row_payment['transaction_id'] ?></td>
                                                <td class="text-right"><?= number_format($row_payment['payment_amount']+$row_payment['credit_charges'],2) ?></td>
                                            </tr>
                                        <?php
                                    }	
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            <?php }
            if($tour_type == 'Activity Booking'){

                $sq_exc_info = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master where exc_id='$booking_id' and delete_status='0'"));
                $date = $sq_exc_info['created_at'];
                $yr = explode("-", $date);
                $year = $yr[0];
                // sale
                $sale_total_amount=$sq_exc_info['exc_total_cost'];
                if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
                
                //Cancel
                $cancel_amount=$sq_exc_info['cancel_amount'];
                $pass_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$sq_exc_info[exc_id]'"));
                $cancel_count = mysqli_num_rows(mysqlQuery("select * from excursion_master_entries where exc_id='$sq_exc_info[exc_id]' and status='Cancel'"));
                
                // Paid
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum ,sum(credit_charges) as sumc from exc_payment_master where exc_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $paid_amount = $query['sum'] + $query['sumc'];
                $paid_amount = ($paid_amount == '')?'0':$paid_amount;
                
                $sale_total_amount = $sale_total_amount + $query['sumc'];
                
                if($pass_count == $cancel_count){
                    if($paid_amount > 0){
                        if($cancel_amount >0){
                            if($paid_amount > $cancel_amount){
                                $balance_amount = 0;
                            }else{
                                $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
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
                    $balance_amount = $sale_total_amount - $paid_amount;
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_exc_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table id="tbl_dynamic_exc_update" name="tbl_dynamic_exc_update" class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $count = 0;
                                $query = "SELECT * from exc_payment_master where exc_id='$booking_id'";		
                                $sq_exc_payment = mysqlQuery($query);	
                                $bg="";
            
                                while($row_exc_payment = mysqli_fetch_assoc($sq_exc_payment)){
                                    if($row_exc_payment['payment_amount'] != '0'){
                                        $count++;
                                        $bg = '';
                                        if($row_exc_payment['clearance_status']=="Pending"){ $bg="warning";}
                                        else if($row_exc_payment['clearance_status']=="Cancelled"){ $bg="danger";} 
                                        else if($row_exc_payment['clearance_status']=="Cleared"){ $bg="success";} 
                                        else { $bg = '';}
                                        ?>
                
                                        <tr class="<?php echo $bg; ?>">
                                            <td><?php echo $count; ?></td>
                                            <td><?php echo get_date_user($row_exc_payment['payment_date']); ?></td>
                                            <td><?php echo $row_exc_payment['payment_mode']; ?></td>
                                            <td><?php echo $row_exc_payment['bank_name']; ?></td>
                                            <td><?php echo $row_exc_payment['transaction_id']; ?></td>
                                            <td class="text-right"><?php echo number_format($row_exc_payment['payment_amount'] + $row_exc_payment['credit_charges'],2); ?></td>
                                        </tr>
                                    <?php   } 
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            <?php } 
            if($tour_type == 'Car Rental Booking'){

                $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from car_rental_booking where booking_id='$booking_id' and delete_status='0'"));
                $date = $sq_booking['created_at'];
                $yr = explode("-", $date);
                $year =$yr[0];
                //Sale
                $sale_total_amount=$sq_booking['total_fees'];
                if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
                
                //Cacnel
                $cancel_amount=$sq_booking['cancel_amount'];
                
                //Paid
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum ,sum(credit_charges) as sumc from car_rental_payment where booking_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $paid_amount = $query['sum'] + $query['sumc'];
                $paid_amount = ($paid_amount == '')?'0':$paid_amount;
                $sale_total_amount = $sale_total_amount + $query['sumc'];
                
                if($sq_booking['status'] == 'Cancel'){
                    if($paid_amount > 0){
                        if($cancel_amount >0){
                            if($paid_amount > $cancel_amount){
                                $balance_amount = 0;
                            }else{
                                $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
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
                    $balance_amount = $sale_total_amount - $paid_amount;
                }
                
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_car_rental_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table id="tbl_dynamic_visa_update" name="tbl_dynamic_visa_update" class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $count = 0;
                                $query = "SELECT * from car_rental_payment where booking_id='$booking_id'";		
                                $sq_visa_payment = mysqlQuery($query);	
                                $bg = "";
            
                                while($row_visa_payment = mysqli_fetch_assoc($sq_visa_payment)){
                                    if($row_visa_payment['payment_amount'] != '0'){
                                        $count++;
                                        $bg = '';
                                        if($row_visa_payment['clearance_status']=="Pending"){ $bg="warning";}
                                        else if($row_visa_payment['clearance_status']=="Cancelled"){ $bg="danger";} 
                                        else if($row_visa_payment['clearance_status']=="Cleared"){ $bg="success";} 
                                        ?>
                
                                        <tr class="<?php echo $bg; ?>">
                                            <td><?php echo $count; ?></td>
                                            <td><?php echo get_date_user($row_visa_payment['payment_date']); ?></td>
                                            <td><?php echo $row_visa_payment['payment_mode']; ?></td>
                                            <td><?php echo $row_visa_payment['bank_name']; ?></td>
                                            <td><?php echo $row_visa_payment['transaction_id']; ?></td>
                                            <td class="text-right"><?php echo number_format($row_visa_payment['payment_amount'] + $row_visa_payment['credit_charges'],2); ?></td>
                                        </tr>
                                    <?php   } 
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php }
            if($tour_type == 'Group Booking'){

                $sq_group_info = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$booking_id' and delete_status='0'"));
                $date = $sq_group_info['form_date'];
                $yr = explode("-", $date);
                $year = $yr[0];
                //paid
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(amount) as sum,sum(credit_charges) as sumc from payment_master where tourwise_traveler_id='$sq_group_info[id]' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $paid_amount = $query['sum']+$query['sumc'];
                $paid_amount = ($paid_amount == '')?'0':$paid_amount;
                $sale_total_amount=$sq_group_info['net_total'] + $query['sumc'];
                if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
                
                $pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$sq_group_info[traveler_group_id]'"));
                $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$sq_group_info[traveler_group_id]' and status='Cancel'"));    
                
                if($sq_group_info['tour_group_status'] == 'Cancel'){
                    //Group Tour cancel
                    $cancel_tour_count2=mysqli_num_rows(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$sq_group_info[id]'"));
                    if($cancel_tour_count2 >= '1'){
                        $cancel_tour=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_tour_estimate where tourwise_traveler_id='$sq_group_info[id]'"));
                        $cancel_amount = $cancel_tour['cancel_amount'];
                    }
                    else{ $cancel_amount = 0; }
                }
                else{
                    // Group booking cancel
                    $cancel_esti_count1=mysqli_num_rows(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$sq_group_info[id]'"));
                    if($pass_count==$cancelpass_count){
                        $cancel_esti1=mysqli_fetch_assoc(mysqlQuery("SELECT * from refund_traveler_estimate where tourwise_traveler_id='$sq_group_info[id]'"));
                        $cancel_amount = $cancel_esti1['cancel_amount'];
                    }
                    else{ $cancel_amount = 0; }
                }
                
                $cancel_amount = ($cancel_amount == '')?'0':$cancel_amount;
                if($sq_group_info['tour_group_status'] == 'Cancel'){
                    if($cancel_amount > $paid_amount){
                        $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
                    }
                    else{
                        $balance_amount = 0;
                    }
                }else{
                    if($pass_count==$cancelpass_count){
                        if($cancel_amount > $paid_amount){
                            $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
                        }
                        else{
                            $balance_amount = 0;
                        }
                    }
                    else{
                        $balance_amount = $sale_total_amount - $paid_amount;
                    }
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_group_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table id="tbl_dynamic_visa_update" name="tbl_dynamic_visa_update" class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $count = 0;
                                $bg="";
                                $sq_group_payment = mysqlQuery("SELECT * from payment_master where tourwise_traveler_id='$booking_id'");	
                                while($row_group_payment = mysqli_fetch_assoc($sq_group_payment)){

                                    if($row_group_payment['amount'] != '0'){

                                    $count++;
                                    if($row_group_payment['clearance_status']=="Pending"){ $bg="warning";}
                                    else if($row_group_payment['clearance_status']=="Cancelled"){ $bg="danger";} 
                                    else if($row_group_payment['clearance_status']=="Cleared"){ $bg="success";}
                                    else{
                                        $bg = '';
                                    }
                                    ?>
                                    <tr class="<?php echo $bg; ?>">
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo get_date_user($row_group_payment['date']); ?></td>
                                        <td><?php echo $row_group_payment['payment_mode']; ?></td>
                                        <td><?php echo $row_group_payment['bank_name']; ?></td>
                                        <td><?php echo $row_group_payment['transaction_id']; ?></td>
                                        <td class="text-right"><?php echo number_format($row_group_payment['amount']+$row_group_payment['credit_charges'],2); ?></td>
                                    </tr>
                                    <?php } 
                                }	 ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php }
            if($tour_type == 'Visa Booking'){

                $sq_visa_info = mysqli_fetch_assoc(mysqlQuery("select * from visa_master where visa_id='$booking_id' and delete_status='0'"));
                $booking_date = $sq_visa_info['created_at'];
                $yr = explode("-", $booking_date);
                $year =$yr[0];
                //Sale
                $sale_total_amount=$sq_visa_info['visa_total_cost'];
                if($sale_total_amount==""){  $sale_total_amount = 0 ;  }
                
                //Cancel
                $cancel_amount=$sq_visa_info['cancel_amount'];
                $pass_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$sq_visa_info[visa_id]'"));
                $cancel_count = mysqli_num_rows(mysqlQuery("select * from visa_master_entries where visa_id='$sq_visa_info[visa_id]' and status='Cancel'"));
                
                //Paid
                $query = mysqli_fetch_assoc(mysqlQuery("SELECT sum(payment_amount) as sum,sum(credit_charges) as sumc from visa_payment_master where visa_id='$booking_id' and clearance_status != 'Pending' and clearance_status != 'Cancelled'"));
                $paid_amount = $query['sum'] + $query['sumc'];
                $paid_amount = ($paid_amount == '')?'0':$paid_amount;
                
                $sale_total_amount = $sale_total_amount + $query['sumc'];
                
                if($pass_count == $cancel_count){
                    if($paid_amount > 0){
                        if($cancel_amount >0){
                            if($paid_amount > $cancel_amount){
                                $balance_amount = 0;
                            }else{
                                $balance_amount = $cancel_amount - $paid_amount + $query['sumc'];
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
                    $balance_amount = $sale_total_amount - $paid_amount;
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h5><?php echo get_visa_booking_id($booking_id,$year); ?></h5>
                    </div>
                </div>
                <?php
                include "../../model/app_settings/generic_sale_widget.php";
                ?>
                <div class="row">
                <div class="col-xs-12">
                    <div class="profile_box main_block" style="margin-top: 25px">
                        <div class="table-responsive">
                            <table id="tbl_dynamic_visa_update" name="tbl_dynamic_visa_update" class="table table-bordered no-marg">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Bank_Name</th>
                                        <th>Cheque_No/ID</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $count = 0;
                                $query = "SELECT * from visa_payment_master where visa_id='$booking_id'";		
                                $sq_visa_payment = mysqlQuery($query);	
                                $bg="";

                                while($row_visa_payment = mysqli_fetch_assoc($sq_visa_payment)){
                                if($row_visa_payment['payment_amount'] != '0'){
                                    $count++;
                                    $bg = '';
                                    if($row_visa_payment['clearance_status']=="Pending"){ $bg="warning";}
                                    else if($row_visa_payment['clearance_status']=="Cancelled"){ $bg="danger";} 
                                    else if($row_visa_payment['clearance_status']=="Cleared"){ $bg="success";} 
                                    else { $bg = '';}
                                    ?>

                                    <tr class="<?php echo $bg; ?>">
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo get_date_user($row_visa_payment['payment_date']); ?></td>
                                        <td><?php echo $row_visa_payment['payment_mode']; ?></td>
                                        <td><?php echo $row_visa_payment['bank_name']; ?></td>
                                        <td><?php echo $row_visa_payment['transaction_id']; ?></td>
                                        <td class="text-right"><?php echo number_format($row_visa_payment['payment_amount']+$row_visa_payment['credit_charges'],2); ?></td>
                                    </tr>
                                <?php   } 
                                } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        <div>
    </div>
    </div>
</div>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
$('#view_summary_modal').modal('show');
</script>
<?php
include '../../model/model.php';
global $encrypt_decrypt, $secret_key;
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$count = 1;
$today = date('Y-m-d');
$today1 = date('Y-m-d H:i');
?>
<div class="dashboard_table dashboard_table_panel main_block">
    <div class="row text-left mg_tp_10">
        <div class="col-md-12">
            <div class="col-md-12 no-pad table_verflow">
                <div class="row mg_tp_20">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover" style="border: 0;width:100%!important;" id="tbl_otours_list">
                                <thead>
                                    <tr class="table-heading-row">
                                        <th>S_No.</th>
                                        <th>Tour_Type</th>
                                        <th>Tour_Name</th>
                                        <th>Tour_Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>Customer_Name</th>
                                        <th>Mobile</th>
                                        <th>Owned&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>Checklist_Status</th>
                                        <th style="display:flex;">Actions&nbsp;&nbsp;&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='package_booking/booking/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query = "select * from package_tour_booking_master where tour_status!='Disabled' and delete_status='0'";
                                    if ($from_date != '' && $to_date != '') {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query .= " and date(tour_from_date) between '$from_date' and '$to_date'";
                                    } else {
                                        $query .= " and tour_from_date <= '$today' and tour_to_date >= '$today' ";
                                    }
                                    include "../../model/app_settings/branchwise_filteration.php";
                                    $sq_query = mysqlQuery($query);
                                    while ($row_query = mysqli_fetch_assoc($sq_query)) {

                                        $date = $row_query['booking_date'];
                                        $yr = explode("-", $date);
                                        $year = $yr[0];
                                        $invoice_no = get_package_booking_id($row_query['booking_id'], $year);
                                        $sq_cancel_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_query[booking_id]' and status='Cancel'"));
                                        $sq_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_query[booking_id]'"));
                                        if ($sq_cancel_count != $sq_count) {
                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query[customer_id]'"));
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Package Tour' "));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Package Tour' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Package Tour' and status='Not Updated'"));
                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query[emp_id]'"));
                                            $name = ($row_query['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
                                            $cust_user_name = '';
                                            $quotation_id = $row_query['quotation_id'];
                                            $sq_quo = mysqli_fetch_assoc(mysqlQuery("select user_id from package_tour_quotation_master where quotation_id='$quotation_id'"));
                                            if($sq_quo['user_id'] != 0){ 
                                                $row_user = mysqli_fetch_assoc(mysqlQuery("Select name from customer_users where user_id ='$sq_quo[user_id]'"));
                                                $cust_user_name = ' ('.$row_user['name'].')';
                                            }
                                    ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td>Package Booking <?= '(' . $invoice_no . ')' ?></td>
                                                <td><?php echo $row_query['tour_name']; ?></td>
                                                <td><?= get_date_user($row_query['tour_from_date']) . ' To ' . get_date_user($row_query['tour_to_date']); ?></td>
                                                <td><?php echo $customer_name.$cust_user_name; ?></td>
                                                <td><?php echo $row_query['mobile_no']; ?></td>
                                                <td><?= ($row_query['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td style="white-space:nowrap;"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query['booking_id']; ?>','Package Tour','<?php echo $row_query['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $row_query['mobile_no'] ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $row_query['booking_id']; ?>','Package Tour')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                    <!-- //B2C Booking -->
                                    <?php
                                    $query = "select * from b2c_sale where status!='Cancel' ";
                                    // include "../../model/app_settings/branchwise_filteration.php";
                                    $sq_query = mysqlQuery($query);
                                    $cond = true;
                                    while ($row_query = mysqli_fetch_assoc($sq_query)) {

                                        $enq_data = json_decode($row_query['enq_data']);
                                        $package_id = $enq_data[0]->package_id;
                                        $service = 'B2C-' . $row_query['service'];

                                        $sq_package = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id='$package_id'"));
                                        $dest_id = $sq_package['dest_id'];
                                        $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='$service' "));
                                        $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='$service' and status='Completed'"));
                                        $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='$service' and status='Not Updated'"));
                                        if ($sq_total == $sq_notupdated) {

                                            $bg_color = 'rgba(244,106,106,.18)';
                                            $status = 'Not Updated';
                                            $text_color = '#f46a6a';
                                        } else if ($sq_total == $sq_completed) {

                                            $bg_color = 'rgba(52,195,143,.18);';
                                            $status = 'Completed';
                                            $text_color = '#34c38f;';
                                        } else if ($sq_total == 0) {

                                            $bg_color = '';
                                            $status = '';
                                            $text_color = '';
                                        } else {

                                            $bg_color = 'rgba(241,180,76,.18)';
                                            $status = 'Ongoing';
                                            $text_color = '#f1b44c';
                                        }

                                        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query[customer_id]'"));
                                        if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                            $customer_name = $sq_cust['company_name'];
                                        } else {
                                            $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                        }
                                        $travel_from = get_date_db($enq_data[0]->travel_from);
                                        $travel_to = get_date_db($enq_data[0]->travel_to);
                                        $from_date1 = get_date_db($from_date);
                                        $to_date1 = get_date_db($to_date);

                                        if ($from_date == '' && $to_date == '') {
                                            $cond = ($travel_from <= $today && $travel_to >= $today) ? true : false;
                                        } else {
                                            $cond = ($travel_from <= $to_date1 && $travel_from >= $from_date1) ? true : false;
                                        }
                                        if ($cond) {
                                    ?>
                                            <tr class="<?= $bg ?>">
                                                <td><?php echo $count++; ?></td>
                                                <td><?= 'B2C Booking(' . $row_query['service'] . ')' ?></td>
                                                <td><?php echo ($enq_data[0]->package_name == '') ? 'NA' : $enq_data[0]->package_name; ?></td>
                                                <td><?= $enq_data[0]->travel_from . ' To ' . $enq_data[0]->travel_to ?></td>
                                                <td><?php echo $customer_name; ?></td>
                                                <td><?php echo $row_query['phone_no']; ?></td>
                                                <td><?= "Admin" ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query['booking_id']; ?>','<?= 'B2C-' . $row_query['service'] ?>','<?php echo $row_query['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $row_query['phone_no'] ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $row_query['booking_id']; ?>','B2C')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                    <!-- Hotel Booking -->
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='hotels/booking/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query1 = "select *	from hotel_booking_entries where status!='Cancel'";
                                    if ($from_date == '' && $to_date == '') {
                                        $query1 .= " and DATE(check_in) <= '$today' and DATE(check_out) >= '$today'";
                                    } else {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query1 .= " and date(check_in) between '$from_date' and '$to_date'";
                                    }
                                    $sq_query = mysqlQuery($query1);
                                    while ($row_query = mysqli_fetch_assoc($sq_query)) {

                                        $query = "select * from hotel_booking_master where booking_id = '$row_query[booking_id]' and delete_status='0'";
                                        include "../../model/app_settings/branchwise_filteration.php";

                                        $sq_hotel_c = mysqli_num_rows(mysqlQuery($query));
                                        if ($sq_hotel_c != 0) {
                                            $query = "select * from hotel_booking_master where booking_id = '$row_query[booking_id]' and delete_status='0'";
                                            include "../../model/app_settings/branchwise_filteration.php";
                                            $sq_hotel = mysqli_fetch_assoc(mysqlQuery($query));
                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                                            $date = $sq_hotel['created_at'];
                                            $yr = explode("-", $date);
                                            $year = $yr[0];
                                            $invoice_no = get_hotel_booking_id($sq_hotel['booking_id'], $year);
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Hotel Booking'"));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Hotel Booking' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Hotel Booking' and status='Not Updated'"));

                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                    ?>
                                            <tr class="<?= $bg ?>">
                                                <td><?php echo $count++; ?></td>
                                                <td>Hotel Booking <?= '(' . $invoice_no . ')' ?></td>
                                                <td><?= 'NA' ?></td>
                                                <td><?= get_date_user($row_query['check_in']) . ' To ' . get_date_user($row_query['check_out']) ?></td>
                                                <td><?php echo $customer_name; ?></td>
                                                <td><?php echo $contact_no; ?></td>
                                                <td><?= ($sq_hotel['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query['booking_id']; ?>','Hotel Booking','<?php echo $sq_hotel['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $row_query['booking_id']; ?>','Hotel Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>

                                    <!-- flight Booking -->
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='visa_passport_ticket/ticket/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query_train = "select * from ticket_trip_entries where ticket_id in (select ticket_id from ticket_master_entries where status!='Cancel') and status!='Cancel'";
                                    if ($from_date == '' && $to_date == '') {
                                        $query_train .= " and DATE(departure_datetime)<= '$today' and DATE(arrival_datetime)>= '$today'";
                                    } else {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query_train .= " and date(departure_datetime) between '$from_date' and '$to_date'";
                                    }
                                    $sq_query1 = mysqlQuery($query_train);
                                    while ($row_query1 = mysqli_fetch_assoc($sq_query1)) {

                                        $query = "select * from ticket_master where ticket_id = '$row_query1[ticket_id]' and delete_status='0'";
                                        include "../../model/app_settings/branchwise_filteration.php";
                                        $sq_hotel_c = mysqli_num_rows(mysqlQuery($query));
                                        if ($sq_hotel_c != 0) {
                                            $query = "select * from ticket_master where ticket_id = '$row_query1[ticket_id]' and delete_status='0'";
                                            include "../../model/app_settings/branchwise_filteration.php";
                                            $sq_hotel = mysqli_fetch_assoc(mysqlQuery($query));
                                            $date = $sq_hotel['created_at'];
                                            $yr = explode("-", $date);
                                            $year = $yr[0];
                                            $invoice_no = get_ticket_booking_id($sq_hotel['ticket_id'], $year);
                                            $bg = '';
                                            $cancel_type = $sq_hotel['cancel_type'];
                                            if ($cancel_type == 2 || $cancel_type == 3) {
                                                $bg = "warning";
                                            }
                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                                            $sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$row_query1[passenger_id]'"));
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[ticket_id]' and tour_type='Flight Booking'"));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[ticket_id]' and tour_type='Flight Booking' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[ticket_id]' and tour_type='Flight Booking' and status='Not Updated'"));

                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                    ?>
                                            <tr class="<?= $bg ?>">
                                                <td><?php echo $count++; ?></td>
                                                <td>Flight Booking <?= '(' . $invoice_no . ')' ?></td>
                                                <td><?= ($row_query1['arrival_city'] == '') ? 'NA' : $row_query1['arrival_city'] ?></td>
                                                <td><?= get_date_user($row_query1['departure_datetime']) . ' To ' . get_date_user($row_query1['arrival_datetime']) ?></td>
                                                <td><?php echo $customer_name . ' (' . $sq_pass['first_name'] . ' ' . $sq_pass['last_name'] . ')'; ?></td>
                                                <td><?php echo $contact_no; ?></td>
                                                <td><?= ($sq_hotel['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query1['ticket_id']; ?>','Flight Booking','<?php echo $sq_hotel['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $sq_hotel['ticket_id']; ?>','Flight Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                    <!-- Train Booking -->
                                    <?php
                                    $bg = '';
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='visa_passport_ticket/train_ticket/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query_train = "select * from train_ticket_master_trip_entries where train_ticket_id in (select train_ticket_id from train_ticket_master_entries where status!='Cancel') and train_ticket_id in (select train_ticket_id from train_ticket_master where delete_status='0')";
                                    if ($from_date == '' && $to_date == '') {
                                        $query_train .= " and DATE(travel_datetime)<= '$today' and DATE(arriving_datetime) >= '$today'";
                                    } else {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query_train .= " and date(travel_datetime) between '$from_date' and '$to_date'";
                                    }
                                    $sq_query_train = mysqlQuery($query_train);
                                    while ($row_query1 = mysqli_fetch_assoc($sq_query_train)) {

                                        $query = "select * from train_ticket_master where train_ticket_id = '$row_query1[train_ticket_id]' and delete_status='0'";
                                        include "../../model/app_settings/branchwise_filteration.php";
                                        $sq_train_c = mysqli_num_rows(mysqlQuery($query));
                                        if ($sq_train_c != 0) {

                                            $query = "select * from train_ticket_master where train_ticket_id = '$row_query1[train_ticket_id]' and delete_status='0'";
                                            include "../../model/app_settings/branchwise_filteration.php";
                                            $sq_train = mysqli_fetch_assoc(mysqlQuery($query));
                                            $date = $sq_train['created_at'];
                                            $yr = explode("-", $date);
                                            $year = $yr[0];
                                            $invoice_no = get_train_ticket_booking_id($sq_train['train_ticket_id'], $year);
                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_train[customer_id]'"));
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_train[emp_id]'"));
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[train_ticket_id]' and tour_type='Train Booking'"));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[train_ticket_id]' and tour_type='Train Booking' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[train_ticket_id]' and tour_type='Train Booking' and status='Not Updated'"));

                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                    ?>
                                            <tr class="<?= $bg ?>">
                                                <td><?php echo $count++; ?></td>
                                                <td>Train Booking <?= '(' . $invoice_no . ')' ?></td>
                                                <td><?= ($row_query1['travel_to'] == '') ? 'NA' : $row_query1['travel_to'] ?></td>
                                                <td><?= get_date_user($row_query1['travel_datetime']) ?></td>
                                                <td><?php echo $customer_name; ?></td>
                                                <td><?php echo $contact_no; ?></td>
                                                <td><?= ($sq_train['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query1['train_ticket_id']; ?>','Train Booking','<?php echo $sq_train['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $sq_train['train_ticket_id']; ?>','Train Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>

                                    <!-- Bus Booking -->
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='bus_booking/booking/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query_bus = "select * from bus_booking_entries where status!='Cancel' ";
                                    if ($from_date == '' && $to_date == '') {
                                        $query_bus .= " and DATE(date_of_journey) = '$today'";
                                    }
                                    if ($from_date != '' && $to_date != '') {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query_bus .= " and date(date_of_journey) between '$from_date' and '$to_date'";
                                    }
                                    $sq_query_bus = mysqlQuery($query_bus);
                                    while ($row_query1 = mysqli_fetch_assoc($sq_query_bus)) {

                                        $query = "select * from bus_booking_master where booking_id = '$row_query1[booking_id]' and delete_status='0'";
                                        include "../../model/app_settings/branchwise_filteration.php";
                                        $sq_hotel_c = mysqli_num_rows(mysqlQuery($query));
                                        if ($sq_hotel_c != 0) {
                                            $query = "select * from bus_booking_master where booking_id = '$row_query1[booking_id]' and delete_status='0'";
                                            include "../../model/app_settings/branchwise_filteration.php";
                                            $sq_hotel = mysqli_fetch_assoc(mysqlQuery($query));
                                            $date = $sq_hotel['created_at'];
                                            $yr = explode("-", $date);
                                            $year = $yr[0];
                                            $invoice_no = get_bus_booking_id($sq_hotel['booking_id'], $year);

                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Bus Booking'"));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Bus Booking' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Bus Booking' and status='Not Updated'"));

                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                    ?>
                                            <tr class="<?= $bg ?>">
                                                <td><?php echo $count++; ?></td>
                                                <td>Bus Booking <?= '(' . $invoice_no . ')' ?></td>
                                                <td><?= ($row_query1['destination'] == '') ? 'NA' : $row_query1['destination'] ?></td>
                                                <td><?= get_date_user($row_query1['date_of_journey']) ?></td>
                                                <td><?php echo $customer_name; ?></td>
                                                <td><?php echo $contact_no; ?></td>
                                                <td><?= ($sq_hotel['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query1['booking_id']; ?>','Bus Booking','<?php echo $sq_hotel['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $sq_hotel['booking_id']; ?>','Bus Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                    <!-- Activity Booking -->
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='excursion/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query_exc = "select * from excursion_master_entries where status!='Cancel'";
                                    if ($from_date == '' && $to_date == '') {
                                        $query_exc .= " and DATE(exc_date) ='$today'";
                                    } else {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query_exc .= " and date(exc_date) between '$from_date' and '$to_date'";
                                    }
                                    $sq_query_exc = mysqlQuery($query_exc);
                                    while ($row_query1 = mysqli_fetch_assoc($sq_query_exc)) {

                                        $query = "select * from excursion_master where exc_id = '$row_query1[exc_id]' and delete_status='0'";
                                        include "../../model/app_settings/branchwise_filteration.php";
                                        $sq_hotel_c = mysqli_num_rows(mysqlQuery($query));
                                        if ($sq_hotel_c != 0) {
                                            $query = "select * from excursion_master where exc_id = '$row_query1[exc_id]' and delete_status='0'";
                                            include "../../model/app_settings/branchwise_filteration.php";
                                            $sq_hotel = mysqli_fetch_assoc(mysqlQuery($query));
                                            $date = $sq_hotel['created_at'];
                                            $yr = explode("-", $date);
                                            $year = $yr[0];
                                            $invoice_no = get_exc_booking_id($sq_hotel['exc_id'], $year);

                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id = '$row_query1[city_id]'"));
                                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[exc_id]' and tour_type='Excursion Booking'"));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[exc_id]' and tour_type='Excursion Booking' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[exc_id]' and tour_type='Excursion Booking' and status='Not Updated'"));

                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                    ?>
                                            <tr class="<?= $bg ?>">
                                                <td><?php echo $count++; ?></td>
                                                <td>Activity Booking <?= '(' . $invoice_no . ')' ?></td>
                                                <td><?php echo $sq_city['city_name']; ?></td>
                                                <td><?= get_date_user($row_query1['exc_date']) ?></td>
                                                <td><?php echo $customer_name; ?></td>
                                                <td><?php echo $contact_no; ?></td>
                                                <td><?= ($sq_hotel['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query1['exc_id']; ?>','Excursion Booking','<?php echo $sq_hotel['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $sq_hotel['exc_id']; ?>','Activity Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                    <!-- Car Rental Booking -->
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='car_rental/booking/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query = "select * from car_rental_booking where travel_type ='Local' and status!='Cancel' and delete_status='0'";
                                    if ($from_date != '' && $to_date != '') {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query .= " and date(from_date) between '$from_date' and '$to_date'";
                                    } else {
                                        $query .= " and DATE(from_date)='$today'";
                                    }
                                    include "../../model/app_settings/branchwise_filteration.php";
                                    $sq_query_car = mysqlQuery($query);

                                    while ($row_query1 = mysqli_fetch_assoc($sq_query_car)) {

                                        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query1[customer_id]'"));
                                        if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                            $customer_name = $sq_cust['company_name'];
                                        } else {
                                            $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                        }
                                        $date = $row_query1['created_at'];
                                        $yr = explode("-", $date);
                                        $year = $yr[0];
                                        $invoice_no = get_car_rental_booking_id($row_query1['booking_id'], $year);
                                        $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                        $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query1[emp_id]'"));
                                        $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking'"));
                                        $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Completed'"));
                                        $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Not Updated'"));

                                        if ($sq_total == $sq_notupdated) {

                                            $bg_color = 'rgba(244,106,106,.18)';
                                            $status = 'Not Updated';
                                            $text_color = '#f46a6a';
                                        } else if ($sq_total == $sq_completed) {

                                            $bg_color = 'rgba(52,195,143,.18);';
                                            $status = 'Completed';
                                            $text_color = '#34c38f;';
                                        } else if ($sq_total == 0) {

                                            $bg_color = '';
                                            $status = '';
                                            $text_color = '';
                                        } else {

                                            $bg_color = 'rgba(241,180,76,.18)';
                                            $status = 'Ongoing';
                                            $text_color = '#f1b44c';
                                        }
                                    ?>
                                        <tr class="<?= $bg ?>">
                                            <td><?php echo $count++; ?></td>
                                            <td>Car Rental Booking <?= '(' . $invoice_no . ')' ?></td>
                                            <td><?= 'NA' ?></td>
                                            <td><?= get_date_user($row_query1['from_date']) . ' To ' . get_date_user($row_query1['to_date']) ?></td>
                                            <td><?php echo $customer_name; ?></td>
                                            <td><?php echo $contact_no; ?></td>
                                            <td><?= ($row_query1['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                            <td class="text-center">
                                                <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                            </td>
                                            <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query1['booking_id']; ?>','Car Rental Booking','<?php echo $row_query1['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $row_query1['booking_id']; ?>','Car Rental Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <!-- Car Rental Booking -->
                                    <?php
                                    $query = "select * from car_rental_booking where travel_type ='Outstation' and status!='Cancel' and delete_status='0'";
                                    if ($from_date != '' && $to_date != '') {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query .= " and date(traveling_date) between '$from_date' and '$to_date'";
                                    } else {
                                        $query .= " and DATE(traveling_date)='$today'";
                                    }
                                    include "../../model/app_settings/branchwise_filteration.php";
                                    $sq_query_car = mysqlQuery($query);
                                    while ($row_query1 = mysqli_fetch_assoc($sq_query_car)) {

                                        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query1[customer_id]'"));
                                        if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                            $customer_name = $sq_cust['company_name'];
                                        } else {
                                            $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                        }
                                        $date = $row_query1['created_at'];
                                        $yr = explode("-", $date);
                                        $year = $yr[0];
                                        $invoice_no = get_car_rental_booking_id($row_query1['booking_id'], $year);
                                        $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                        $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query1[emp_id]'"));
                                        $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking'"));
                                        $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Completed'"));
                                        $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Not Updated'"));

                                        if ($sq_total == $sq_notupdated) {

                                            $bg_color = 'rgba(244,106,106,.18)';
                                            $status = 'Not Updated';
                                            $text_color = '#f46a6a';
                                        } else if ($sq_total == $sq_completed) {

                                            $bg_color = 'rgba(52,195,143,.18);';
                                            $status = 'Completed';
                                            $text_color = '#34c38f;';
                                        } else if ($sq_total == 0) {

                                            $bg_color = '';
                                            $status = '';
                                            $text_color = '';
                                        } else {

                                            $bg_color = 'rgba(241,180,76,.18)';
                                            $status = 'Ongoing';
                                            $text_color = '#f1b44c';
                                        }
                                    ?>
                                        <tr class="<?= $bg ?>">
                                            <td><?php echo $count++; ?></td>
                                            <td>Car Rental Booking <?= '(' . $invoice_no . ')' ?></td>
                                            <td><?= 'NA' ?></td>
                                            <td><?= get_date_user($row_query1['traveling_date']) ?></td>
                                            <td><?php echo $customer_name; ?></td>
                                            <td><?php echo $contact_no; ?></td>
                                            <td><?= ($row_query1['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                            <td class="text-center">
                                                <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                            </td>
                                            <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query1['booking_id']; ?>','Car Rental Booking','<?php echo $row_query1['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $row_query1['booking_id']; ?>','Car Rental Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <!-- Group Booking -->
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='booking/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query_grp = "select * from tour_groups where 1";
                                    if ($from_date == '' && $to_date == '') {
                                        $query_grp .= " and from_date<='$today' and to_date>='$today'";
                                    } else {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query_grp .= " and date(from_date) between '$from_date' and '$to_date'";
                                    }
                                    $sq_query_grp = mysqlQuery($query_grp);
                                    while ($row_query1 = mysqli_fetch_assoc($sq_query_grp)) {

                                        $query = "select * from tourwise_traveler_details where tour_id='$row_query1[tour_id]' and tour_group_id='$row_query1[group_id]' and tour_group_status!='Cancel' and delete_status='0'";
                                        include "../../model/app_settings/branchwise_filteration.php";
                                        $sq = mysqlQuery($query);
                                        while ($row_query = mysqli_fetch_assoc($sq)) {

                                            $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where tour_id = '$row_query[tour_id]' and group_id='$row_query[tour_group_id]'"));
                                            $sq_booking1 = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id = '$row_query[tour_id]'"));
                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query[customer_id]'"));
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query[emp_id]'"));
                                            $date1 = $row_query['form_date'];
                                            $yr1 = explode("-", $date1);
                                            $year1 = $yr1[0];
                                            $pass_count = mysqli_num_rows(mysqlQuery("select * from  travelers_details where traveler_group_id='$row_query[id]'"));
                                            $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_query[id]' and status='Cancel'"));
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[id]' and tour_type='Group Tour'"));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[id]' and tour_type='Group Tour' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[id]' and tour_type='Group Tour' and status='Not Updated'"));

                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                            if ($pass_count != $cancelpass_count) {
                                    ?>
                                                <tr class="<?= $bg ?>">
                                                    <td><?php echo $count++; ?></td>
                                                    <td>Group Booking(<?= get_group_booking_id($row_query['id'], $year1) ?>)</td>
                                                    <td><?php echo $sq_booking1['tour_name']; ?></td>
                                                    <td><?= get_date_user($sq_booking['from_date']) . ' To ' . get_date_user($sq_booking['to_date']) ?></td>
                                                    <td><?php echo $customer_name; ?></td>
                                                    <td><?php echo $contact_no; ?></td>
                                                    <td><?= ($row_query['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                    <td class="text-center">
                                                        <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                    </td>
                                                    <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query['id']; ?>','Group Tour','<?php echo $row_query['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                        <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                        <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $row_query['id']; ?>','Group Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                    </td>
                                                </tr>
                                    <?php
                                            }
                                        }
                                    } ?>
                                    <!-- Visa Booking -->
                                    <?php
                                    $sq_branch = mysqli_fetch_assoc(mysqlQuery("select branch_status from branch_assign where link='visa_passport_ticket/visa/index.php'"));
                                    $branch_status = $sq_branch['branch_status'];
                                    $query_visa = "select *	from visa_master_entries where status!='Cancel' ";
                                    if ($from_date == '' && $to_date == '') {
                                        $query_visa .= " and appointment_date='$today'";
                                    } else {
                                        $from_date = get_date_db($from_date);
                                        $to_date = get_date_db($to_date);
                                        $query_visa .= " and date(appointment_date) between '$from_date' and '$to_date'";
                                    }
                                    $sq_query_visa = mysqlQuery($query_visa);
                                    while ($row_query_visa = mysqli_fetch_assoc($sq_query_visa)) {

                                        $query = "select * from visa_master where visa_id = '$row_query_visa[visa_id]' and delete_status='0'";
                                        include "../../model/app_settings/branchwise_filteration.php";
                                        $sq_visa_c = mysqli_num_rows(mysqlQuery($query));
                                        if ($sq_visa_c != 0) {
                                            $query = "select * from visa_master where visa_id = '$row_query_visa[visa_id]' and delete_status='0'";
                                            include "../../model/app_settings/branchwise_filteration.php";
                                            $sq_visa = mysqli_fetch_assoc(mysqlQuery($query));
                                            $date = $sq_visa['created_at'];
                                            $yr = explode("-", $date);
                                            $year = $yr[0];
                                            $invoice_no = get_visa_booking_id($sq_visa['visa_id'], $year);
                                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_visa[customer_id]'"));
                                            if ($sq_cust['type'] == 'Corporate' || $sq_cust['type'] == 'B2B') {
                                                $customer_name = $sq_cust['company_name'];
                                            } else {
                                                $customer_name = $sq_cust['first_name'] . ' ' . $sq_cust['last_name'];
                                            }
                                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_visa[emp_id]'"));
                                            $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query_visa[visa_id]' and tour_type='Visa Booking'"));
                                            $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query_visa[visa_id]' and tour_type='Visa Booking' and status='Completed'"));
                                            $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query_visa[visa_id]' and tour_type='Visa Booking' and status='Not Updated'"));

                                            if ($sq_total == $sq_notupdated) {

                                                $bg_color = 'rgba(244,106,106,.18)';
                                                $status = 'Not Updated';
                                                $text_color = '#f46a6a';
                                            } else if ($sq_total == $sq_completed) {

                                                $bg_color = 'rgba(52,195,143,.18);';
                                                $status = 'Completed';
                                                $text_color = '#34c38f;';
                                            } else if ($sq_total == 0) {

                                                $bg_color = '';
                                                $status = '';
                                                $text_color = '';
                                            } else {

                                                $bg_color = 'rgba(241,180,76,.18)';
                                                $status = 'Ongoing';
                                                $text_color = '#f1b44c';
                                            }
                                    ?>
                                            <tr class="<?= $bg ?>">
                                                <td><?php echo $count++; ?></td>
                                                <td>Visa Booking <?= '(' . $invoice_no . ')' ?></td>
                                                <td><?php echo $row_query_visa['visa_country_name']; ?></td>
                                                <td><?= get_date_user($row_query_visa['appointment_date']) ?></td>
                                                <td><?php echo $customer_name; ?></td>
                                                <td><?php echo $contact_no; ?></td>
                                                <td><?= ($sq_visa['emp_id'] == '0') ? "Admin" : $sq_emp['first_name'] . ' ' . $sq_emp['last_name'] ?></td>
                                                <td class="text-center">
                                                    <h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color ?>"><?= $status ?></h6>
                                                </td>
                                                <td><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count ?>','<?php echo $row_query_visa['visa_id']; ?>','Visa Booking','<?php echo $sq_visa['emp_id']; ?>');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count ?>"><i class="fa fa-plus"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button>
                                                    <button class="btn btn-info btn-sm" onclick="view_payment_summary('<?= $count ?>','<?php echo $sq_visa['visa_id']; ?>','Visa Booking')" data-toggle="tooltip" title="View Payment Summary" id="payment-<?= $count ?>"><i class="fa fa-eye"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#tbl_otours_list').dataTable({
        "pagingType": "full_numbers"
    });
</script>
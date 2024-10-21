<?php
include '../../../model/model.php';
global $encrypt_decrypt,$secret_key;
$login_id = $_SESSION['login_id'];
$financial_year_id = $_SESSION['financial_year_id'];
$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$count = 1;
$today = date('Y-m-d-h-i-s');
$today1 = date('Y-m-d');
?>
<div class="dashboard_table dashboard_table_panel main_block">
<div class="row text-left mg_tp_10">
    <div class="col-md-12">
        <div class="col-md-12 no-pad table_verflow"> 
            <div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
            
            <table class="table table-hover" style="border: 0;" id="tbl_utours_list">
                <thead>
                    <tr class="table-heading-row">
                    <th>S_No.</th>
                    <th>Tour_Type</th>
                    <th>Tour_Name</th>
                    <th>Tour_Dates&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    <th>Customer_Name</th>
                    <th>Mobile</th>
                    <th>Owned&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    <th>Checklist</th>
                    <th>Checklist_Status</th>
                    <th>Send_Wishes</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "select * from package_tour_booking_master where tour_status!='Disabled' and financial_year_id='$financial_year_id' and tour_from_date > '$today1' and emp_id='$emp_id' and delete_status='0'";
                $sq_query = mysqlQuery($query);
                while($row_query=mysqli_fetch_assoc($sq_query)){
                    $sq_cancel_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_query[booking_id]' and status='Cancel'"));
                    $sq_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_query[booking_id]'"));

                    if($row_query['dest_id']=='0'){
                        $sq_package = mysqli_fetch_assoc(mysqlQuery("select * from custom_package_master where package_id='$row_query[package_id]'"));
                        $dest_id = $sq_package['dest_id'];
                    }else{
                        $dest_id = $row_query['dest_id'];
                    }

                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Package Tour' "));
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Package Tour' and status='Completed'"));
                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Package Tour' and status='Not Updated'"));
                    if($sq_total == $sq_notupdated){

                        $bg_color = 'rgba(244,106,106,.18)';
                        $status = 'Not Updated';
                        $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                        $bg_color = 'rgba(52,195,143,.18);';
                        $status = 'Completed';
                        $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                        $bg_color = '';
                        $status = '';
                        $text_color = '';
                    }else{

                        $bg_color = 'rgba(241,180,76,.18)';
                        $status = 'Ongoing';
                        $text_color = '#f1b44c';
                    }
                        
                    if($sq_cancel_count != $sq_count){
                        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query[customer_id]'"));
                        if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                        $customer_name = $sq_cust['company_name'];
                        }else{
                        $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                        }
                        $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query[emp_id]'"));
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Package Tour</td>
                    <td><?= ($row_query['tour_name']=='')?'NA':$row_query['tour_name'] ?></td>
                    <td><?= get_date_user($row_query['tour_from_date']).' To '.get_date_user($row_query['tour_to_date']) ?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $row_query['mobile_no']; ?></td>
                    <td><?= ($row_query['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query['booking_id']; ?>','Package Tour');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>
                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $row_query['mobile_no'] ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                    <?php } } ?>
                <!-- Hotel Booking -->
                <?php
                $query1 = "select * from hotel_booking_entries where status!='Cancel' and DATE(check_in) > '$today' and booking_id in(select booking_id from hotel_booking_master where emp_id='$emp_id' and delete_status='0')";
                $sq_query = mysqlQuery($query1);
                while($row_query=mysqli_fetch_assoc($sq_query)){
                    
                    $sq = mysqlQuery("select * from hotel_booking_master where booking_id = '$row_query[booking_id]' and emp_id='$emp_id'");
                    while($sq_hotel = mysqli_fetch_assoc($sq)){
                    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                    $customer_name = $sq_cust['company_name'];
                    }else{
                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));

                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Hotel Booking'"));								
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Hotel Booking' and status='Completed'"));

                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[booking_id]' and tour_type='Hotel Booking' and status='Not Updated'"));

                    if($sq_total == $sq_notupdated){

                    $bg_color = 'rgba(244,106,106,.18)';
                    $status = 'Not Updated';
                    $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                    $bg_color = 'rgba(52,195,143,.18);';
                    $status = 'Completed';
                    $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                    $bg_color = '';
                    $status = '';
                    $text_color = '';
                    }else{

                    $bg_color = 'rgba(241,180,76,.18)';
                    $status = 'Ongoing';
                    $text_color = '#f1b44c';
                    }
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Hotel Booking</td>
                    <td><?= ($row_query['tour_name']=='')?'NA':$row_query['tour_name'] ?></td>
                    <td><?= get_date_user($row_query['check_in']).' To '.get_date_user($row_query['check_out']) ?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>

                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query['booking_id']; ?>','Hotel Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></i></button></td>
                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                    <?php } } ?>
                <!-- Flight Booking -->
                <?php
                $query_flight = "select * from  ticket_trip_entries where DATE(departure_datetime) > '$today' and ticket_id in(select ticket_id from ticket_master where emp_id='$emp_id' and delete_status='0') and ticket_id in (select ticket_id from ticket_master_entries where status!='Cancel') and status!='Cancel'";
                    $sq_query1 = mysqlQuery($query_flight);
                    while($row_query1=mysqli_fetch_assoc($sq_query1)){
                    
                    $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id = '$row_query1[ticket_id]' and delete_status='0'"));
                    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                    $customer_name = $sq_cust['company_name'];
                    }else{
                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));

                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[ticket_id]' and tour_type='Flight Booking'"));								
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[ticket_id]' and tour_type='Flight Booking' and status='Completed'"));

                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[ticket_id]' and tour_type='Flight Booking' and status='Not Updated'"));

                    if($sq_total == $sq_notupdated){

                    $bg_color = 'rgba(244,106,106,.18)';
                    $status = 'Not Updated';
                    $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                    $bg_color = 'rgba(52,195,143,.18);';
                    $status = 'Completed';
                    $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                    $bg_color = '';
                    $status = '';
                    $text_color = '';
                    }else{

                    $bg_color = 'rgba(241,180,76,.18)';
                    $status = 'Ongoing';
                    $text_color = '#f1b44c';
                    }
                    $sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$row_query1[passenger_id]'"));
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Flight Booking</td>
                    <td><?= $row_query1['arrival_city'] ?></td>
                    <td><?= get_date_user($row_query1['departure_datetime']).' To '.get_date_user($row_query1['arrival_datetime']) ?></td>
                    <td><?php echo $customer_name.' ('.$sq_pass['first_name'].' '.$sq_pass['last_name'].')'; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query1['ticket_id']; ?>','Flight Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>
                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                    <?php } ?>
                <!-- Train Booking -->
                <?php
                $query_train = "select * from train_ticket_master_trip_entries where DATE(travel_datetime) > '$today' and train_ticket_id in (select train_ticket_id from train_ticket_master where emp_id='$emp_id' and delete_status='0') and train_ticket_id in (select train_ticket_id from train_ticket_master_entries where status!='Cancel')";
                $sq_query_train = mysqlQuery($query_train);
                while($row_query1=mysqli_fetch_assoc($sq_query_train)){
                    
                    $sq_train = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id = '$row_query1[train_ticket_id]' and delete_status='0'"));
                    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_train[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                    $customer_name = $sq_cust['company_name'];
                    }else{
                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_train[emp_id]'"));

                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[train_ticket_id]' and tour_type='Train Booking'"));								
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[train_ticket_id]' and tour_type='Train Booking' and status='Completed'"));

                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[train_ticket_id]' and tour_type='Train Booking' and status='Not Updated'"));

                    if($sq_total == $sq_notupdated){

                    $bg_color = 'rgba(244,106,106,.18)';
                    $status = 'Not Updated';
                    $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                    $bg_color = 'rgba(52,195,143,.18);';
                    $status = 'Completed';
                    $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                    $bg_color = '';
                    $status = '';
                    $text_color = '';
                    }else{

                    $bg_color = 'rgba(241,180,76,.18)';
                    $status = 'Ongoing';
                    $text_color = '#f1b44c';
                    }
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Train Booking</td>
                    <td><?= ($row_query1['travel_to']=='')?'NA':$row_query1['travel_to'] ?></td>
                    <td><?= get_date_user($row_query1['travel_datetime'])?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($sq_train['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query1['train_ticket_id']; ?>','Train Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>
                    
                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>

                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                    <?php } ?>
                <!-- Bus Booking -->
                <?php
                $query_bus = "select * from bus_booking_entries where DATE(date_of_journey) > '$today' and status!='Cancel' and booking_id in(select booking_id from bus_booking_master where emp_id='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0') ";
                $sq_query_bus = mysqlQuery($query_bus);
                while($row_query1=mysqli_fetch_assoc($sq_query_bus)){
                    $sq_hotelc = mysqli_num_rows(mysqlQuery("select * from bus_booking_master where booking_id = '$row_query1[booking_id]' and emp_id='$emp_id' and delete_status='0'"));
                    if($sq_hotelc!=0){
                        $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id = '$row_query1[booking_id]' and delete_status='0'"));
                        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                        if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                        $customer_name = $sq_cust['company_name'];
                        }else{
                        $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                        }
                        $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                        $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));

                        $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Bus Booking'"));								
                        $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Bus Booking' and status='Completed'"));

                        $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Bus Booking' and status='Not Updated'"));

                        if($sq_total == $sq_notupdated){

                        $bg_color = 'rgba(244,106,106,.18)';
                        $status = 'Not Updated';
                        $text_color = '#f46a6a';
                        }else if($sq_total == $sq_completed){

                        $bg_color = 'rgba(52,195,143,.18);';
                        $status = 'Completed';
                        $text_color = '#34c38f;';
                        }else if($sq_total == 0){

                        $bg_color = '';
                        $status = '';
                        $text_color = '';
                        }else{

                        $bg_color = 'rgba(241,180,76,.18)';
                        $status = 'Ongoing';
                        $text_color = '#f1b44c';
                        }
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Bus Booking</td>
                    <td><?= ($row_query1['destination']=='')?'NA':$row_query1['destination'] ?></td>
                    <td><?= get_date_user($row_query1['date_of_journey']) ?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query1['booking_id']; ?>','Bus Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>

                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>

                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                    <?php }  } ?>
                <!-- Excursion Booking -->
                <?php
                $today1 = date('Y-m-d');
                $query_exc = "select * from excursion_master_entries where DATE(exc_date) > '$today1' and status!='Cancel' and exc_id in(select exc_id from excursion_master where emp_id='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0')";
                $sq_query_exc = mysqlQuery($query_exc);
                while($row_query1=mysqli_fetch_assoc($sq_query_exc)){
                    
                    $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master where exc_id = '$row_query1[exc_id]'"));
                    $sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id = '$row_query1[city_id]'"));
                    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                    $customer_name = $sq_cust['company_name'];
                    }else{
                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));

                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[exc_id]' and tour_type='Excursion Booking'"));								
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[exc_id]' and tour_type='Excursion Booking' and status='Completed'"));

                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[exc_id]' and tour_type='Excursion Booking' and status='Not Updated'"));

                    if($sq_total == $sq_notupdated){

                    $bg_color = 'rgba(244,106,106,.18)';
                    $status = 'Not Updated';
                    $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                    $bg_color = 'rgba(52,195,143,.18);';
                    $status = 'Completed';
                    $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                    $bg_color = '';
                    $status = '';
                    $text_color = '';
                    }else{

                    $bg_color = 'rgba(241,180,76,.18)';
                    $status = 'Ongoing';
                    $text_color = '#f1b44c';
                    }
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Activity Booking</td>
                    <td><?php echo $sq_city['city_name']; ?></td>
                    <td><?= get_date_user($row_query1['exc_date']) ?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query1['exc_id']; ?>','Excursion Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>

                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>

                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                    <?php } ?>
                    <!-- Car Rental Booking -->
                <?php
                $query_car = "select * from car_rental_booking where DATE(from_date) > '$today1' and travel_type ='Local' and emp_id='$emp_id' and financial_year_id='$financial_year_id' and status != 'Cancel' and delete_status='0'";

                $sq_query_car = mysqlQuery($query_car);
                while($row_query1=mysqli_fetch_assoc($sq_query_car)){
                    
                    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query1[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                    $customer_name = $sq_cust['company_name'];
                    }else{
                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query1[emp_id]'"));
                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking'"));								
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Completed'"));

                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Not Updated'"));

                    if($sq_total == $sq_notupdated){

                    $bg_color = 'rgba(244,106,106,.18)';
                    $status = 'Not Updated';
                    $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                    $bg_color = 'rgba(52,195,143,.18);';
                    $status = 'Completed';
                    $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                    $bg_color = '';
                    $status = '';
                    $text_color = '';
                    }else{

                    $bg_color = 'rgba(241,180,76,.18)';
                    $status = 'Ongoing';
                    $text_color = '#f1b44c';
                    }
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Car Rental Booking</td>
                    <td><?= ($row_query1['tour_name']=='')?'NA':$row_query1['tour_name'] ?></td>
                    <td><?= get_date_user($row_query1['from_date']).' To '.get_date_user($row_query1['to_date']) ?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($row_query1['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query1['booking_id']; ?>','Car Rental Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>

                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>

                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                <?php } ?>
                <?php
                $query_car = "select * from car_rental_booking where traveling_date  > '$today1' and travel_type ='Outstation' and emp_id='$emp_id' and financial_year_id='$financial_year_id' and status != 'Cancel' and delete_status='0'";
                $sq_query_car = mysqlQuery($query_car);
                while($row_query1=mysqli_fetch_assoc($sq_query_car)){
                    
                    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query1[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                    $customer_name = $sq_cust['company_name'];
                    }else{
                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query1[emp_id]'"));

                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking'"));								
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Completed'"));

                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query1[booking_id]' and tour_type='Car Rental Booking' and status='Not Updated'"));

                    if($sq_total == $sq_notupdated){

                    $bg_color = 'rgba(244,106,106,.18)';
                    $status = 'Not Updated';
                    $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                    $bg_color = 'rgba(52,195,143,.18);';
                    $status = 'Completed';
                    $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                    $bg_color = '';
                    $status = '';
                    $text_color = '';
                    }else{

                    $bg_color = 'rgba(241,180,76,.18)';
                    $status = 'Ongoing';
                    $text_color = '#f1b44c';
                    }
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Car Rental Booking</td>
                    <td><?= ($row_query1['tour_name']=='')?'NA':$row_query1['tour_name'] ?></td>
                    <td><?= get_date_user($row_query1['traveling_date']) ?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($row_query1['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query1['booking_id']; ?>','Car Rental Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>

                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>

                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                <?php } ?>
                <!-- Group Booking -->
                <?php
                $sq = mysqlQuery("select * from tourwise_traveler_details where 1 and emp_id='$emp_id' and financial_year_id='$financial_year_id' and tour_group_status!='Cancel' and delete_status='0'");
                while($row_query = mysqli_fetch_assoc($sq)){
                    $sq_trcount = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_query[traveler_group_id]' and status='Cancel'"));
                    if($sq_trcount == 0){
                    $sq_booking1 = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id = '$row_query[tour_id]'"));
                    $row_grps_count = mysqli_num_rows(mysqlQuery("select * from tour_groups where tour_id = '$row_query[tour_id]' and group_id='$row_query[tour_group_id]' and from_date > '$today'"));
                    if($row_grps_count > 0){
                        $row_grps1 = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where tour_id = '$row_query[tour_id]' and group_id='$row_query[tour_group_id]' and from_date > '$today'"));
                        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query[customer_id]'"));
                        if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                        $customer_name = $sq_cust['company_name'];
                        }else{
                        $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                        }
                        $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query[emp_id]'"));
                        $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                        $tour_id = $sq_booking1['tour_id'];
                        $dest_id = $sq_booking1['dest_id'];

                        $date1 = $row_query['form_date'];
                        $yr1 = explode("-", $date1);
                        $year1 = $yr1[0];

                        $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[id]' and tour_type='Group Tour'"));							
                        $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[id]' and tour_type='Group Tour' and status='Completed'"));
                        
                        $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query[id]' and tour_type='Group Tour' and status='Not Updated'"));
                        if($sq_total == $sq_notupdated){

                        $bg_color = 'rgba(244,106,106,.18)';
                        $status = 'Not Updated';
                        $text_color = '#f46a6a';
                        }else if($sq_total == $sq_completed){

                        $bg_color = 'rgba(52,195,143,.18);';
                        $status = 'Completed';
                        $text_color = '#34c38f;';
                        }else if($sq_total == 0){

                        $bg_color = '';
                        $status = '';
                        $text_color = '';
                        }else{

                        $bg_color = 'rgba(241,180,76,.18)';
                        $status = 'Ongoing';
                        $text_color = '#f1b44c';
                        }
                        ?>
                        <tr class="<?= $bg ?>">
                        <td><?php echo $count++; ?></td>
                        <td>Group Booking(<?=get_group_booking_id($row_query['id'],$year1)?>)</td>
                        <td><?php echo $sq_booking1['tour_name']; ?></td>
                        <td><?= get_date_user($row_grps1['from_date']).' To '.get_date_user($row_grps1['to_date']) ?></td>
                        <td><?php echo $customer_name; ?></td>
                        <td><?php echo $contact_no; ?></td>
                        <td><?= ($row_query['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                        <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query['id']; ?>','Group Tour');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>

                        <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>

                        <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                        </tr>
                    <?php
                    }
                    }
                }
                ?>
                <!-- Visa Booking -->
                <?php                                
                $query_visa = "select * from visa_master_entries where appointment_date > '$today' and status!='Cancel' and visa_id in(select visa_id from visa_master where emp_id='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0')";
                $sq_query_visa = mysqlQuery($query_visa);
                while($row_query_visa=mysqli_fetch_assoc($sq_query_visa)){

                    $sq_visac = mysqli_num_rows(mysqlQuery("select * from visa_master where visa_id = '$row_query_visa[visa_id]' and delete_status='0'"));
                    if($sq_visac!=0){
                    $sq_visa = mysqli_fetch_assoc(mysqlQuery("select * from visa_master where visa_id = '$row_query_visa[visa_id]' and delete_status='0'"));
                    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_visa[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                    $customer_name = $sq_cust['company_name'];
                    }else{
                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_visa[emp_id]'"));
                    $sq_total = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query_visa[visa_id]' and tour_type='Visa Booking'"));								
                    $sq_completed = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query_visa[visa_id]' and tour_type='Visa Booking' and status='Completed'"));

                    $sq_notupdated = mysqli_num_rows(mysqlQuery("select * from checklist_package_tour where booking_id='$row_query_visa[visa_id]' and tour_type='Visa Booking' and status='Not Updated'"));

                    if($sq_total == $sq_notupdated){

                    $bg_color = 'rgba(244,106,106,.18)';
                    $status = 'Not Updated';
                    $text_color = '#f46a6a';
                    }else if($sq_total == $sq_completed){

                    $bg_color = 'rgba(52,195,143,.18);';
                    $status = 'Completed';
                    $text_color = '#34c38f;';
                    }else if($sq_total == 0){

                    $bg_color = '';
                    $status = '';
                    $text_color = '';
                    }else{

                    $bg_color = 'rgba(241,180,76,.18)';
                    $status = 'Ongoing';
                    $text_color = '#f1b44c';
                    }
                    ?>
                    <tr class="<?= $bg ?>">
                    <td><?php echo $count++; ?></td>
                    <td>Visa Booking</td>
                    <td><?php echo $row_query_visa['visa_country_name']; ?></td>
                    <td><?= get_date_user($row_query_visa['appointment_date']) ?></td>
                    <td><?php echo $customer_name; ?></td>
                    <td><?php echo $contact_no; ?></td>
                    <td><?= ($sq_visa['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="checklist_update('<?= $count?>','<?php echo $row_query_visa['visa_id']; ?>','Visa Booking');" data-toggle="tooltip" title="Update Checklist" target="_blank" id="checklist-<?= $count?>"><i class="fa fa-plus"></i></button></td>

                    <td class="text-center"><h6 style="width: 90px;height: 30px;border-radius: 20px;font-size: 12px;line-height: 21px;text-align: center;background:<?= $bg_color ?>;padding:5px;color:<?= $text_color?>"><?= $status ?></h6></td>

                    <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="Whatsapp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
                    </tr>
                    <?php } } ?>
                    <!-- Miscellaneous Booking -->
                <?php     
                $query_pass = "select * from miscellaneous_master where created_at > '$today1' and emp_id='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0' order by misc_id desc";
                    $sq_query_pass = mysqlQuery($query_pass);
                    while($row_query_visa=mysqli_fetch_assoc($sq_query_pass)){
                    
                    $sq_mcount = mysqli_fetch_assoc(mysqlQuery("select * from  miscellaneous_master_entries where misc_id = '$row_query_visa[misc_id]' and status!='Cancel'"));
                    if($sq_mcount != 0) {
                        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query_visa[customer_id]'"));
                    if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                        $customer_name = $sq_cust['company_name'];
                    }else{
                        $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                    }
                    $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                    $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query_visa[emp_id]'"));
                    ?>
                        <tr class="<?= $bg ?>">
                        <td><?php echo $count++; ?></td>
                        <td>Miscellaneous Booking</td>
                        <td><?= ($row_query1['tour_name']=='')?'NA':$row_query1['tour_name'] ?></td>
                        <td><?= get_date_user($row_query_visa['created_at']) ?></td>
                        <td><?php echo $customer_name; ?></td>
                        <td><?php echo $contact_no; ?></td>
                        <td><?= ($row_query_visa['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                        <td class="text-center">NA</td>

                        <td class="text-center">NA</td>

                        <td class="text-center"><button class="btn btn-info btn-sm" onclick="whatsapp_wishes('<?= $contact_no ?>','<?= $customer_name ?>')" data-toggle="tooltip" title="WhatsApp wishes to customer"><i class="fa fa-whatsapp"></i></button></td>
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
</div></div>
<script>
$('#tbl_utours_list').dataTable({
	"pagingType": "full_numbers"
});
</script>
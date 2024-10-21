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
$today = date('Y-m-d');
$today1 = date('Y-m-d H:i');
?>
<div class="dashboard_table dashboard_table_panel main_block">
<div class="row text-left mg_tp_10">
    <div class="col-md-12">
        <div class="col-md-12 no-pad table_verflow"> 
            <div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
                <table class="table table-hover" style="border: 0;" id="tbl_otours_list">
                    <thead>
                    <tr class="table-heading-row">
                        <th>S_No.</th>
                        <th>Tour_Type</th>
                        <th>Tour_Name</th>
                        <th>Tour_Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <th>Customer_Name</th>
                        <th>Mobile</th>
                        <th>Owned&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <th>Client_Feedback</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query1 = "select * from package_tour_booking_master where tour_status!='Disabled' and financial_year_id='$financial_year_id' and emp_id = '$emp_id' and tour_from_date <= '$today' and tour_to_date >= '$today' and delete_status='0'";
                            $sq_query = mysqlQuery($query1);
                            while($row_query=mysqli_fetch_assoc($sq_query))
                            {
                            $sq_cancel_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_query[booking_id]' and status='Cancel'"));
                            $sq_count = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_query[booking_id]'"));
                            if($sq_cancel_count != $sq_count){
                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query[customer_id]'"));
                            if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                                $customer_name = $sq_cust['company_name'];
                            }else{
                                $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                            }
                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query[emp_id]'"));
                            ?>
                            <tr>
                            <td><?php echo $count++; ?></td>
                            <td>Package Booking</td>
                            <td><?php echo $row_query['tour_name']; ?></td>
                            <td><?= get_date_user($row_query['tour_from_date']).' To '.get_date_user($row_query['tour_to_date']); ?></td>
                            <td><?php echo $customer_name; ?></td>
                            <td><?php echo $row_query['mobile_no']; ?></td>
                            <td><?= ($row_query['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                            <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $row_query['booking_id'] ?>,'Package Booking',<?= $row_query['emp_id']?>,<?= $row_query['mobile_no']?>, '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                            </tr>
                        <?php } } ?>
                        <?php
                        // Hotel Booking
                        $query1 = "select *	from hotel_booking_entries where status!='Cancel' and DATE(check_in)<= '$today' and DATE(check_out) >= '$today' and booking_id in(select booking_id from hotel_booking_master where emp_id='$emp_id' and delete_status='0')";
                        $sq_query = mysqlQuery($query1);
                        while($row_query=mysqli_fetch_assoc($sq_query)){
                        
                        $sql_hotel = mysqlQuery("select * from hotel_booking_master where booking_id = '$row_query[booking_id]' and emp_id = '$emp_id' and delete_status='0'");
                        while($sq_hotel = mysqli_fetch_assoc($sql_hotel)){

                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                            if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                            $customer_name = $sq_cust['company_name'];
                            }else{
                            $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                            }
                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                            ?>
                            <tr class="<?= $bg ?>">
                            <td><?php echo $count++; ?></td>
                            <td>Hotel Booking</td>
                            <td><?php echo 'NA'; ?></td>
                            <td><?= get_date_user($row_query['check_in']).' To '.get_date_user($row_query['check_out']) ?></td>
                            <td><?php echo $customer_name; ?></td>
                            <td><?= $contact_no; ?></td>
                            <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                            <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $sq_hotel['booking_id'] ?>,'Hotel Booking',<?= $sq_hotel['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>	
                            </tr>
                            <?php } } ?>
                            <!-- flight Booking -->
                            <?php
                            $query_train = "select * from  ticket_trip_entries where DATE(departure_datetime)<= '$today' and DATE(arrival_datetime) >= '$today' and ticket_id in (select ticket_id from ticket_master where emp_id='$emp_id' and delete_status='0') and ticket_id in (select ticket_id from ticket_master_entries where status!='Cancel') and status!='Cancel'";
                            
                            $sq_query1 = mysqlQuery($query_train);
                            while($row_query1=mysqli_fetch_assoc($sq_query1)){
                            
                            $sql_flight = mysqlQuery("select * from ticket_master where ticket_id = '$row_query1[ticket_id]' and emp_id = '$emp_id' and delete_status='0'");
                            while($sq_hotel = mysqli_fetch_assoc($sql_flight)){
                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                            if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                                $customer_name = $sq_cust['company_name'];
                            }else{
                                $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                            }
                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                            $sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$row_query1[passenger_id]'"));
                            ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Flight Booking</td>
                                <td><?php echo $row_query1['arrival_city']; ?></td>
                                <td><?= get_date_user($row_query1['departure_datetime']).' To '.get_date_user($row_query1['arrival_datetime']) ?></td>
                                <td><?php echo $customer_name.' ('.$sq_pass['first_name'].' '.$sq_pass['last_name'].')'; ?></td>
                                <td><?= $contact_no; ?></td>
                                <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $sq_hotel['ticket_id'] ?>,'Flight Booking',<?= $sq_hotel['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                            <?php } } ?>
                            <!-- Train Booking -->
                            <?php
                            $query_train = "select * from train_ticket_master_trip_entries where DATE(travel_datetime) <= '$today' and train_ticket_id in (select train_ticket_id from train_ticket_master where emp_id='$emp_id' and delete_status='0') and train_ticket_id in (select train_ticket_id from train_ticket_master_entries where status!='Cancel')";
                            $sq_query_train = mysqlQuery($query_train);
                            while($row_query1 = mysqli_fetch_assoc($sq_query_train)){
                            
                            $sql_train = mysqlQuery("select * from train_ticket_master where train_ticket_id = '$row_query1[train_ticket_id]' and emp_id = '$emp_id' and delete_status='0'");
                            while($sq_train = mysqli_fetch_assoc($sql_train)){
                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_train[customer_id]'"));
                            if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                                $customer_name = $sq_cust['company_name'];
                            }else{
                                $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                            }
                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_train[emp_id]'"));
                            ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Train Booking</td>
                                <td><?php echo $row_query1['travel_to']; ?></td>
                                <td><?= get_date_user($row_query1['travel_datetime'])?></td>
                                <td><?php echo $customer_name; ?></td>
                                <td><?= $contact_no; ?></td>
                                <td><?= ($sq_train['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $sq_train['train_ticket_id'] ?>,'Train Booking',<?= $sq_train['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                            <?php } } ?>
                            
                            <!-- Bus Booking -->
                            <?php
                            $query_bus = "select * from bus_booking_entries where DATE(date_of_journey)	= '$today' and booking_id in(select booking_id from bus_booking_master where emp_id='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0') and status!='Cancel' ";

                            $sq_query_bus = mysqlQuery($query_bus);
                            while($row_query1=mysqli_fetch_assoc($sq_query_bus)){
                            
                            $sql_bus = mysqlQuery("select * from bus_booking_master where booking_id = '$row_query1[booking_id]' and emp_id = '$emp_id' and delete_status='0'");
                            while($sq_hotel = mysqli_fetch_assoc($sql_bus)){
                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                            if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                                $customer_name = $sq_cust['company_name'];
                            }else{
                                $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                            }
                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                            ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Bus Booking</td>
                                <td><?php echo $row_query1['destination']; ?></td>
                                <td><?= get_date_user($row_query1['date_of_journey']) ?></td>
                                <td><?php echo $customer_name; ?></td>
                                <td><?= $contact_no; ?></td>
                                <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $sq_hotel['booking_id'] ?>,'Bus Booking',<?= $sq_hotel['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                            <?php } }?>
                            <!-- Excursion Booking -->
                            <?php
                            $query_exc = "select *	from  excursion_master_entries where DATE(exc_date) ='$today' and exc_id in(select exc_id from excursion_master where emp_id='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0') and status!='Cancel' ";

                            $sq_query_exc = mysqlQuery($query_exc);
                            while($row_query1=mysqli_fetch_assoc($sq_query_exc)){
                            
                            $sql_exc = mysqlQuery("select * from excursion_master where exc_id = '$row_query1[exc_id]' and emp_id = '$emp_id' and delete_status='0'");

                            while( $sq_hotel = mysqli_fetch_assoc($sql_exc)){

                            $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_hotel[customer_id]'"));
                            if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                                $customer_name = $sq_cust['company_name'];
                            }else{
                                $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                            }
                            $sq_city = mysqli_fetch_assoc(mysqlQuery("select * from 	city_master where city_id = '$row_query1[city_id]'"));
                            $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                            $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
                            ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Activity Booking</td>
                                <td><?php echo $sq_city['city_name']; ?></td>
                                <td><?= get_date_user($row_query1['exc_date']) ?></td>
                                <td><?php echo $customer_name; ?></td>
                                <td><?php echo $contact_no; ?></td>
                                <td><?= ($sq_hotel['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $sq_hotel['exc_id'] ?>,'Excursion Booking',<?= $sq_hotel['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                            <?php } }?>
                            <?php
                            $query_car = "select * from car_rental_booking where DATE(from_date)='$today' and travel_type ='Local' and emp_id='$emp_id' and status!='Cancel' and financial_year_id='$financial_year_id' and delete_status='0'";
                            
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
                            ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Car Rental Booking</td>
                                <td><?= ($row_query1['tour_name']=='')?'NA':$row_query1['tour_name'] ?></td>
                                <td><?= get_date_user($row_query1['from_date']).' To '.get_date_user($row_query1['to_date']) ?></td>
                                <td><?php echo $customer_name; ?></td>
                                <td><?php echo $contact_no; ?></td>
                                <td><?= ($row_query1['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $row_query1['booking_id'] ?>,'Car Rental Booking',<?= $row_query1['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                            <?php } ?>
                            <!-- Car Rental Booking -->
                            <?php
                            $query_car = "select * from car_rental_booking  where DATE(traveling_date)='$today' and travel_type ='Outstation' and emp_id='$emp_id' and status!='Cancel' and financial_year_id='$financial_year_id' and delete_status='0' ";
                            
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
                            ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Car Rental Booking</td>
                                <td><?= ($row_query1['tour_name']=='')?'NA':$row_query1['tour_name'] ?></td>
                                <td><?= get_date_user($row_query1['traveling_date']) ?></td>
                                <td><?php echo $customer_name; ?></td>
                                <td><?php echo $contact_no; ?></td>
                                <td><?= ($row_query1['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $row_query1['booking_id'] ?>,'Car Rental Booking',<?= $row_query1['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                            <?php } ?>
                            <!-- Group Booking -->
                            <?php
                            $sq = mysqlQuery("select * from tourwise_traveler_details where 1 and emp_id='$emp_id' and tour_group_status!='Cancel' and delete_status='0'");
                            while($row_query = mysqli_fetch_assoc($sq)){
                            
                                $sq_trcount = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_query[traveler_group_id]' and status='Cancel'"));
                                if($sq_trcount == 0){

                                $sq_booking1 = mysqli_fetch_assoc(mysqlQuery("select * from tour_master where tour_id = '$row_query[tour_id]'"));
                                $row_grps_count = mysqli_num_rows(mysqlQuery("select * from tour_groups where tour_id = '$row_query[tour_id]' and group_id = '$row_query[tour_group_id]' and from_date<='$today' and to_date>='$today'"));
                                if($row_grps_count > 0){
                                $row_grps = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where tour_id = '$row_query[tour_id]' and group_id = '$row_query[tour_group_id]' and from_date<='$today' and to_date>='$today'")); 

                                $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$row_query[customer_id]'"));
                                if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                                    $customer_name = $sq_cust['company_name'];
                                }else{
                                    $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                                }
                                $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$row_query[emp_id]'"));
                                $date1 = $row_query['form_date'];
                                $yr1 = explode("-", $date1);
                                $year1 = $yr1[0];
                                ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Group Booking(<?=get_group_booking_id($row_query['id'],$year1)?>)</td>
                                <td><?php echo $sq_booking1['tour_name']; ?></td>
                                <td><?= get_date_user($row_grps['from_date']).' To '.get_date_user($row_grps['to_date']) ?></td>
                                <td><?php echo $customer_name; ?></td>
                                <td><?php echo $contact_no; ?></td>
                                <td><?= ($row_query['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $row_query['id'] ?>,'Group Booking',<?= $row_query['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                                <?php
                                } } } ?>
                            <!-- Visa Booking -->
                            <?php
                            $query_visa = "select *	from visa_master_entries where appointment_date='$today' and visa_id in(select visa_id from visa_master where emp_id='$emp_id' and financial_year_id='$financial_year_id' and delete_status='0') and status!='Cancel' ";

                            $sq_query_visa = mysqlQuery($query_visa);
                            while($row_query_visa=mysqli_fetch_assoc($sq_query_visa)){
                            $sq_visac = mysqli_num_rows(mysqlQuery("select * from visa_master where visa_id = '$row_query_visa[visa_id]' and delete_status='0'"));
                            if($sq_visac!=0){

                                $sq_visa = mysqli_fetch_assoc(mysqlQuery("select * from visa_master where visa_id = '$row_query_visa[visa_id]' and delete_status='0' "));

                                $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id = '$sq_visa[customer_id]'"));
                                if($sq_cust['type']=='Corporate'||$sq_cust['type'] == 'B2B'){
                                $customer_name = $sq_cust['company_name'];
                                }else{
                                $customer_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
                                }
                                $contact_no = $encrypt_decrypt->fnDecrypt($sq_cust['contact_no'], $secret_key);
                                $sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_visa[emp_id]'"));
                                ?>
                                <tr class="<?= $bg ?>">
                                <td><?php echo $count++; ?></td>
                                <td>Visa Booking</td>
                                <td><?php echo $row_query_visa['visa_country_name']; ?></td>
                                <td><?= get_date_user($row_query_visa['appointment_date']) ?></td>
                                <td><?php echo $customer_name; ?></td>
                                <td><?= $contact_no; ?></td>
                                <td><?= ($sq_visa['emp_id']=='0') ? "Admin" : $sq_emp['first_name'].' '.$sq_emp['last_name'] ?></td>
                                <td><button class="btn btn-info btn-sm" onclick="send_sms(<?= $sq_visa['visa_id'] ?>,'Visa Booking',<?= $sq_visa['emp_id']?>,'<?= $contact_no?>', '<?= $customer_name ?>')" title="Send Message"><i class="fa fa-paper-plane-o"></i></button></td>
                                </tr>
                                <?php }
                            }?>
                    </tbody>
                </table>
            </div> 
            </div>
        </div>
    </div>
</div>
</div></div>
<script>
$('#tbl_otours_list').dataTable({
	"pagingType": "full_numbers"
});
</script>
<?php
include "../../../../model/model.php";

$booking_id = $_POST['booking_id'];

$sq_package_info = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id' and delete_status='0'"));
$date = $sq_package_info['booking_date'];
$yr = explode("-", $date);
$year =$yr[0];
?>
<div class="modal fade profile_box_modal" id="package_display_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Booking Information(<?= get_package_booking_id($booking_id,$year) ?>)</h4>
      </div>
      <div class="modal-body profile_box_padding">
	     <div class="row">    
		  	<div class="col-xs-12">
		  		<div class="profile_box">
		           	<h3 class="editor_title">Passenger Information</h3>
		                <div class="table-responsive">
		                    <table class="table table-bordered no-marg">
			                    <thead>
			                        <tr class="table-heading-row">
				                       	<th>S_No.</th>
				                       	<th>Honorific</th>
				                       	<th>Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
				                       	<th>Gender</th>
				                       	<th>Date_of_birth</th>
				                       	<th>Age</th>
				                       	<th>Adole</th>
				                       	<th>Passport_No.</th>
				                       	<th>Issue_Date</th>
				                       	<th>Expiry_Date</th>
			                        </tr>
			                    </thead>
		                        <tbody>
		                       <?php 
		                       		$count = 0;
		                       		$sq_entry = mysqlQuery("select * from package_travelers_details where booking_id='$booking_id'");
		                            $bg="";
		                       		while($row_entry = mysqli_fetch_assoc($sq_entry)){
		                       			if($row_entry['status']=="Cancel"){
		                       				$bg="danger";
		                       			}
		                       			else {
		                       				$bg="#fff";
		                       			}
		                       			$count++;
		                       			?>
									<tr class="<?php echo $bg; ?>">
									    <td><?php echo $count; ?></td>
									    <td><?php echo $row_entry['m_honorific'] ?></td>
									    <td><?php echo $row_entry['first_name']." ".$row_entry['last_name']; ?></td>
										<td><?php echo $row_entry['gender']; ?></td>
									    <td><?php echo get_date_user($row_entry['birth_date']); ?></td>
									    <td><?php echo $row_entry['age']; ?></td>
									    <td><?php echo $row_entry['adolescence']; ?> </td>
									    <?php 
									    if($row_entry['passport_no']==''){ ?>
									    <td><?php echo "N/A"; ?> </td>
									    <?php } else { ?>
									    <td><?php echo $row_entry['passport_no']; ?> </td>
									    <?php } ?>
									    <?php 
									    if($row_entry['passport_issue_date']=='1970-01-01'){ ?>
									    <td><?php echo "N/A"; ?> </td>
									    <?php } else { ?>
									    <td><?php echo get_date_user($row_entry['passport_issue_date']); ?></td>
									    <?php }  ?>
									    <?php
									    if($row_entry['passport_expiry_date']=='1970-01-01'){ ?>
									    <td><?php echo "N/A"; ?> </td>
									    <?php } else { ?>
									    <td><?php echo get_date_user($row_entry['passport_expiry_date']); ?></td>
									    <?php }  ?>
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

	<!--  Train   -->
	<?php $sq_train_count = mysqli_num_rows(mysqlQuery("select * from package_train_master where booking_id='$booking_id'")); 
	if($sq_train_count != '0' ){?>
	<div class="row">    
		  	<div class="col-xs-12 mg_bt_20">
		  		<div class="profile_box">
		           	<h3 class="editor_title">Train Information</h3>
		                <div class="table-responsive">
		                    <table  class="table table-bordered no-marg">
			                    <thead>
                       		<tr class="table-heading-row">
		                       	<th>S_No.</th>
		                       	<th>Departure&nbsp;</th>
		                       	<th>Location_From</th>
		                       	<th>Location_To</th>
		                       	<th>Train_Name_No</th>
		                       	<th>Total_Seats</th>
		                       	<th>Class</th>
		                       	<th>Priority</th>
                       		</tr>
                    	</thead>
                   		<tbody>
                       <?php 
                       		$count = 0;
                       		$sq_entry = mysqlQuery("select * from package_train_master where booking_id='$booking_id'");
                       		while($row_entry = mysqli_fetch_assoc($sq_entry)){
                       			$count++;
                       	?>
							<tr class="<?php echo $bg; ?>">
							    <td><?php echo $count; ?></td>
							    <td><?php echo date("d-m-Y H:i", strtotime($row_entry['date'])) ?></td>
							    <td><?php echo $row_entry['from_location'] ?></td>
								<td><?php echo $row_entry['to_location']; ?></td>
							    <td><?php echo $row_entry['train_no']; ?></td>
							    <td><?php echo $row_entry['seats']; ?> </td>
							    <td><?php echo $row_entry['train_class']; ?> </td>
							    <td><?php echo $row_entry['train_priority']; ?></td>
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
		<?php } ?>
		<!--  Flight   -->
	<?php $sq_plane_count = mysqli_num_rows(mysqlQuery("select * from package_plane_master where booking_id='$booking_id'")); 
	if($sq_plane_count != '0' ){?>
	<div class="row">    
		  	<div class="col-xs-12 mg_bt_20">
		  		<div class="profile_box">
		           	<h3 class="editor_title">Flight Information</h3>
		                <div class="table-responsive">
		                    <table  class="table table-bordered no-marg">
			                   <thead>
	                       	<tr class="table-heading-row">
		                       	<th>S_No.</th>
		                       	<th>Departure_D/T</th>
		                       	<th>Arrival_D/T</th>
		                       	<th>From_City</th>
								<th>Sector_From</th>
								<th>To_City</th>
		                       	<th>Sector_To</th>
		                       	<th>Airline_Name</th>
		                       	<th>Class</th>
		                       	<th>Total_Seats</th>
	                       </tr>
	                    </thead>
	                    <tbody>
	                       <?php 
	                       		$count = 0;
	                       		$sq_entry = mysqlQuery("select * from package_plane_master where booking_id='$booking_id'");
	                       		while($row_entry = mysqli_fetch_assoc($sq_entry)){
	                       			$count++;
	                       			$sq_airline = mysqli_fetch_assoc(mysqlQuery("select * from airline_master where airline_id='$row_entry[company]'"));

	                       			$sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$row_entry[from_city]'"));
		                            $sq_city1 = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$row_entry[to_city]'"));
	                       	?>
							<tr class="<?php echo $bg; ?>">
							    <td><?php echo $count; ?></td>
							    <td><?php echo date("d-m-Y H:i", strtotime($row_entry['date'])) ?></td>
							    <td><?php echo date("d-m-Y H:i", strtotime($row_entry['arraval_time'])); ?> </td>
							    <td><?php echo $sq_city['city_name']; ?></td>
								<td><?php echo $row_entry['from_location']; ?></td>
								<td><?php echo $sq_city1['city_name']; ?></td>
								<td><?php echo $row_entry['to_location']; ?></td>
							    <td><?php echo $sq_airline['airline_name'].' ('.$sq_airline['airline_code'].')'; ?></td>
							    <td><?php echo $row_entry['class']; ?> </td>
							    <td><?php echo $row_entry['seats']; ?> </td>
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
		<?php } ?>			
		
<!-- Cruise Info -->
<?php $sq_c_count = mysqli_num_rows(mysqlQuery("select * from package_cruise_master where booking_id='$booking_id'")); 
    if($sq_c_count != '0'){?>
	<div class="row">

	<div class="col-xs-12 mg_bt_20">

		<div class="profile_box main_block">

        	 	<h3 class="editor_title">Cruise Information</h3>
				<div class="table-responsive">
                    <table class="table table-bordered no-marg">
	                    <thead>
	                       	<tr class="table-heading-row">
		                       	<th>S_No.</th>
		                       	<th>Departure_D/T</th>
		                       	<th>Arrival_D/T</th>
		                       	<th>Route</th>
		                       	<th>Cabin</th>
		                       	<th>Sharing</th>
		                       	<th>Seats</th>
	                       </tr>
	                    </thead>
	                    <tbody>
	                       <?php 
	                       		$count = 0;
	                       		$sq_entry = mysqlQuery("select * from package_cruise_master where booking_id='$booking_id'");
	                       		while($row_entry = mysqli_fetch_assoc($sq_entry)){
	                       			$count++;
	                       	?>
							<tr class="<?php echo $bg; ?>">
							    <td><?php echo $count; ?></td>
							    <td><?php echo get_datetime_user($row_entry['dept_datetime']) ?></td>
							    <td><?php echo get_datetime_user($row_entry['arrival_datetime']) ?></td>
								<td><?php echo $row_entry['route']; ?></td>
							    <td><?php echo $row_entry['cabin']; ?></td>
							    <td><?php echo $row_entry['sharing']; ?></td>
							    <td><?php echo $row_entry['seats']; ?> </td>
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
<?php } ?>
<?php
$sq_c_hotel = mysqli_num_rows(mysqlQuery("select * from package_hotel_accomodation_master where booking_id='$booking_id'"));
if($sq_c_hotel != '0'){
?>
<div class="row mg_tp_20 mg_bt_20">
	<div class="col-xs-12">
		<div class="profile_box main_block">
			<h3 class="editor_title">Accommodation Details</h3>
			<div class="table-responsive">
				<table class="table table-bordered no-marg">
					<thead>
						<tr class="table-heading-row">
							<th>City</th>
							<th>Hotel_Name</th>
							<th>Check_In_DateTime</th>
							<th>Check_Out_DateTime</th>
							<th>Room</th>
							<th>Category</th>
							<th>Meal_Plan</th>
							<th>Extra_Bed</th>
							<th>Confirmation_No</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$count = 0;
						$sq_entry = mysqlQuery("select * from package_hotel_accomodation_master where booking_id='$booking_id'");
						while($row_entry = mysqli_fetch_assoc($sq_entry)){
							
							$city_id = $row_entry['city_id'];
							$hotel_id = $row_entry['hotel_id'];

							$sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$city_id'"));
							$sq_hotel_name = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$hotel_id'"));
							$count++;
						?>
						<tr class="<?php echo $bg; ?>">
							<td><?php echo $sq_city['city_name'] ?></td>
							<td><?php echo $sq_hotel_name['hotel_name'].$similar_text; ?></td>
							<td><?php echo date("d-m-Y H:i", strtotime($row_entry['from_date'])); ?></td>
							<td><?php echo date("d-m-Y H:i", strtotime($row_entry['to_date'])); ?></td>
							<td><?php echo $row_entry['rooms']; ?> </td>
							<td><?php echo $row_entry['catagory']; ?> </td>
							<td><?php echo $row_entry['meal_plan']; ?></td>
							<td><?php echo $row_entry['room_type']; ?></td>
							<td><?php echo $row_entry['confirmation_no']; ?></td>
						</tr>  
						<?php } ?>
				</tbody>
			</table>
			</div>
		</div> 
	</div>
</div>
<?php } ?>

<?php 
$sq_c_count = mysqli_num_rows(mysqlQuery("select * from package_tour_transport_master where booking_id='$booking_id'"));
if($sq_c_count > 0){
?>
<div class="row mg_bt_20">
	<div class="col-md-12">
		<div class="profile_box main_block">
			<h3 class="editor_title">Transport Details</h3>
			<div class="table-responsive">
				<table class="table table-bordered no-marg">
					<thead>
						<tr class="table-heading-row">
							<th>Vehicle_name</th>
							<th>Start_Date</th>
							<th>End_Date</th>
							<th>Pickup_Location</th>
							<th>Drop_Location</th>
							<th>Service_duration</th>
							<th>Total_Vehicles</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$sq_entry = mysqlQuery("select * from package_tour_transport_master where booking_id='$booking_id'");
						while($row_entry = mysqli_fetch_assoc($sq_entry)){

							$q_transport = mysqli_fetch_assoc(mysqlQuery("select * from b2b_transfer_master where entry_id='$row_entry[transport_bus_id]'"));
                            // Pickup
                            if($row_entry['pickup_type'] == 'city'){
                                $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_entry[pickup]'"));
                                $pickup = $row['city_name'];
                            }
                            else if($row_entry['pickup_type'] == 'hotel'){
                                $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_entry[pickup]'"));
                                $pickup = $row['hotel_name'];
                            }
                            else{
                                $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_entry[pickup]'"));
                                $airport_nam = clean($row['airport_name']);
                                $airport_code = clean($row['airport_code']);
                                $pickup = $airport_nam." (".$airport_code.")";
                            }
                            //Drop-off
                            if($row_entry['drop_type'] == 'city'){
                                $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$row_entry[drop]'"));
                                $drop = $row['city_name'];
                            }
                            else if($row_entry['drop_type'] == 'hotel'){
                                $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$row_entry[drop]'"));
                                $drop = $row['hotel_name'];
                            }
                            else{
                                $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$row_entry[drop]'"));
                                $airport_nam = clean($row['airport_name']);
                                $airport_code = clean($row['airport_code']);
                                $drop = $airport_nam." (".$airport_code.")";
                            }
							?>
							<tr class="<?php echo $bg; ?>">
								<td><?= $q_transport['vehicle_name'].$similar_text ?></td>
								<td><?= get_datetime_user($row_entry['transport_from_date']) ?></td>
								<td><?= get_datetime_user($row_entry['transport_end_date']) ?></td>
								<td><?= $pickup ?></td>
								<td><?= $drop ?></td>
								<td><?= $row_entry['service_duration'] ?></td>
								<td><?= $row_entry['vehicle_count'] ?></td>
							</tr>
					<?php } ?>
					</tbody>
				</table>
            </div>
	    </div> 
	</div>
</div>
<?php } ?>

<?php
$sq_act_count = mysqli_num_rows(mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'"));
if($sq_act_count > 0){
?>
<div class="row mg_bt_20">
	<div class="col-md-12">
		<div class="profile_box main_block">
			<h3 class="editor_title">Activity Details</h3>
			<div class="table-responsive">
				<table class="table table-bordered no-marg">
					<thead>
						<tr class="table-heading-row">
							<th>Activity_date</th>
							<th>City_Name</th>
							<th>Activity_name</th>
							<th>Transfer_option</th>
							<th>Adult(s)</th>
							<th>CWB</th>
							<th>CWOB</th>
							<th>Infant(s)</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$sq_entry = mysqlQuery("select * from package_tour_excursion_master where booking_id='$booking_id'");
					while($row_entry = mysqli_fetch_assoc($sq_entry)){
						$q_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$row_entry[city_id]'"));
						$sq_ex = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master_tariff where entry_id='$row_entry[exc_id]'"));
						?>
						<tr class="<?php echo $bg; ?>">
							<td><?php echo date("d-m-Y H:i", strtotime($row_entry['exc_date'])) ?></td>
							<td><?= $q_city['city_name'] ?></td>
							<td><?= $sq_ex['excursion_name'] ?></td>
							<td><?= $row_entry['transfer_option'] ?> </td>
							<td><?= $row_entry['adult'] ?> </td>
							<td><?= $row_entry['chwb'] ?> </td>
							<td><?= $row_entry['chwob'] ?> </td>
							<td><?= $row_entry['infant'] ?> </td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
	    </div> 
	</div>
</div>
<?php } ?>
	</div>
</div>
</div>
</div>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
$('#package_display_modal').modal('show');
</script>
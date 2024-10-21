<?php
$sq_entry_count = mysqli_num_rows(mysqlQuery("select entry_id from ticket_master_entries where ticket_id='$ticket_id'"));
if($sq_entry_count>0){

	$count = 0;
	$sq_entry = mysqlQuery("select * from ticket_master_entries where ticket_id='$ticket_id'");
	while($row_entry = mysqli_fetch_assoc($sq_entry)){

		$count++;
		$bg = ($row_entry['status']=='Cancel') ? 'danger' : '';
		$trip_arr = array();
		$departure_datetime_arr = array();
		$arrival_datetime_arr = array();
		$airlines_name_arr = array();
		$class_arr = array();
		$sub_category_arr = array();
		$flight_no_arr = array();
		$airlin_pnr_arr = array();
		$from_city_id_arr = array();
		$departure_city_arr = array();
		$departure_terminal_arr = array();
		$to_city_id_arr = array();
		$arrival_city_arr = array();
		$arrival_city_arr = array();
		$arrival_terminal_arr = array();
		$luggage_arr = array();
		$no_of_pieces_arr = array();
		$special_note_arr = array();
		$aircraft_type_arr = array();
		$operating_carrier_arr = array();
		$frequent_flyer_arr = array();
		$ticket_status_arr = array();
		$basic_fare_arr = array();
		$flight_duration_arr = array();
		$layover_time_arr = array();
		$refund_type_arr = array();
		$trip_entry_id_arr = array();
		$trip_data_check_arr = array();
		$trip_cancel_status_arr = array();
		$type_of_tour = $row_entry['type_of_tour'];
		$sq_trip_ticket = mysqlQuery("select * from ticket_trip_entries where passenger_id='$row_entry[entry_id]'");
		while($row_ticket = mysqli_fetch_assoc($sq_trip_ticket)){
			
			array_push($departure_datetime_arr, get_datetime_user($row_ticket['departure_datetime']));
			array_push($arrival_datetime_arr, get_datetime_user($row_ticket['arrival_datetime']));
			array_push($airlines_name_arr, $row_ticket['airlines_name']);
			array_push($class_arr, $row_ticket['class']);
			array_push($sub_category_arr, $row_ticket['sub_category']);
			array_push($flight_no_arr, $row_ticket['flight_no']);
			array_push($airlin_pnr_arr, $row_ticket['airlin_pnr']);
			array_push($from_city_id_arr, $row_ticket['from_city']);
			array_push($departure_city_arr, $row_ticket['departure_city']);
			array_push($departure_terminal_arr, $row_ticket['departure_terminal']);
			array_push($to_city_id_arr, $row_ticket['to_city']);
			array_push($arrival_city_arr, $row_ticket['arrival_city']);
			array_push($arrival_terminal_arr, $row_ticket['arrival_terminal']);
			array_push($luggage_arr, $row_ticket['luggage']);
			array_push($no_of_pieces_arr, $row_ticket['no_of_pieces']);
			array_push($special_note_arr, $row_ticket['special_note']);
			array_push($aircraft_type_arr, $row_ticket['aircraft_type']);
			array_push($operating_carrier_arr, $row_ticket['operating_carrier']);
			array_push($frequent_flyer_arr, $row_ticket['frequent_flyer']);
			array_push($ticket_status_arr, $row_ticket['ticket_status']);
			array_push($basic_fare_arr, $row_ticket['basic_fare']);
			array_push($flight_duration_arr, $row_ticket['flight_duration']);
			array_push($layover_time_arr, $row_ticket['layover_time']);
			array_push($refund_type_arr, $row_ticket['refund_type']);
			array_push($trip_entry_id_arr, $row_ticket['entry_id']);
			array_push($trip_data_check_arr, boolval(true));
			array_push($trip_cancel_status_arr, $row_ticket['status']);
		}
		array_push($trip_arr,array(
			'type_of_tour'=>$type_of_tour,
			'departure_datetime_arr'=>$departure_datetime_arr,
			'arrival_datetime_arr'=>$arrival_datetime_arr,
			'airlines_name_arr'=>$airlines_name_arr,
			'class_arr'=>$class_arr,
			'sub_category_arr'=>$sub_category_arr,
			'flight_no_arr'=>$flight_no_arr,
			'airlin_pnr_arr'=>$airlin_pnr_arr,
			'from_city_id_arr'=>$from_city_id_arr,
			'departure_city_arr'=>$departure_city_arr,
			'departure_terminal_arr'=>$departure_terminal_arr,
			'to_city_id_arr'=>$to_city_id_arr,
			'arrival_city_arr'=>$arrival_city_arr,
			'arrival_terminal_arr'=>$arrival_terminal_arr,
			'luggage_arr'=>$luggage_arr,
			'no_of_pieces_arr'=>$no_of_pieces_arr,
			'special_note_arr'=>$special_note_arr,
			'aircraft_type_arr'=>$aircraft_type_arr,
			'operating_carrier_arr'=>$operating_carrier_arr,
			'frequent_flyer_arr'=>$frequent_flyer_arr,
			'ticket_status_arr'=>$ticket_status_arr,
			'basic_fare_arr'=>$basic_fare_arr,
			'flight_duration_arr'=>$flight_duration_arr,
			'layover_time_arr'=>$layover_time_arr,
			'refund_type_arr'=>$refund_type_arr,
			'trip_entry_id_arr'=>$trip_entry_id_arr,
			'trip_data_check_arr'=>$trip_data_check_arr,
			'cancel_status_arr'=>$trip_cancel_status_arr
		));
		$trip_arr = json_encode($trip_arr);
		$disabled = ($bg == '') ? '' : 'disabled';
		?>
		<tr class="<?= $bg ?>">
		    <td><input class="css-checkbox" id="chk_ticket<?= $count ?>_u" type="checkbox" checked <?= $disabled ?>><label class="css-label" for="chk_ticket<?= $count ?>_u"> <label></td>
		    <td><input maxlength="15" value="<?= $count ?>" type="text" name="username" placeholder="Sr. No." class="form-control" disabled /></td>
		    <td><input type="text" id="first_name<?= $count ?>_u" name="first_name"  onchange="fname_validate(this.id)"  placeholder="*First Name" title="First Name" value="<?= $row_entry['first_name'] ?>" style="width:120px;" /></td>
		    <td><input type="text" id="middle_name<?= $count ?>_u" name="middle_name"  onchange="fname_validate(this.id)"  placeholder="Middle Name" title="Middle Name" value="<?= $row_entry['middle_name'] ?>" style="width:120px;"/></td>
		    <td><input type="text" id="last_name<?= $count ?>_u" name="last_name"  onchange="fname_validate(this.id)"  placeholder="Last Name" title="Last Name" value="<?= $row_entry['last_name'] ?>" style="width:120px;"/></td>    
		    <td class="hidden"><input type="text" id="birth_date<?= $count ?>_u" name="birth_date" placeholder="Birth Date" title="Birth Date" class="app_datepicker" onchange="adolescence_reflect(this.id)" value="<?= get_date_user($row_entry['birth_date']) ?>"/></td>
			<td><select id="adolescence<?= $count ?>_u" name="adolescence" placeholder="*Adolescence" title="Adolescence" style="width:125px;" disabled>
					<option value="">Select Adolescence</option>
					<option <?php echo ($row_entry['adolescence'] == 'Adult')?"selected":"" ?> >Adult</option>
					<option <?php echo ($row_entry['adolescence'] == 'Child')?"selected":"" ?> >Child</option>
					<option <?php echo ($row_entry['adolescence'] == 'Infant')?"selected":"" ?> >Infant</option>
    			</select>
			</td>
		    <td><input type="text" id="ticket_no<?= $count ?>_u" style="text-transform: uppercase;width:120px;" name="ticket_no" placeholder="Ticket No" onchange="validate_spaces(this.id)" title="Ticket No" value="<?= $row_entry['ticket_no'] ?>"/></td>
		    <td><input type="text" id="gds_pnr<?= $count ?>_u" style="text-transform: uppercase;width:120px;" name="gds_pnr" placeholder="Airline PNR" onchange="validate_spaces(this.id)" title="Airline PNR" value="<?= $row_entry['gds_pnr'] ?>"></td>
			<td><input type="text" id="baggage_info<?= $count ?>_u"  name="baggage_info" placeholder="Check-In & Cabin Baggage" onchange="validate_spaces(this.id)" title="Check-In & Cabin Baggage" value="<?= $row_entry['baggage_info'] ?>" style="width:218px;"></td>
			<td><input type="text" id="seat_no<?= $count ?>_u" name="seat_no" onchange="validate_spaces(this.id)" placeholder="Seat No." title="Seat No." value="<?= $row_entry['seat_no'] ?>" style="width:120px;"></td>
			<td><input type="text" id="meal_plan<?= $count ?>_u" name="meal_plan" placeholder="Meal Plan" title="Meal Plan" value="<?= $row_entry['meal_plan'] ?>" style="width:120px;"></td>
			<td><input type="<?= ($sq_ticket['ticket_reissue'] != 1) ? 'hidden' : 'text' ?>" class="form-control main_ticket"  id="main_ticket<?= $count ?>_u" style="text-transform: uppercase;width:170px;" name="main_ticket" placeholder="*Main Ticket Number" onchange="validate_spaces(this.id)" title="Main Ticket Number" value="<?= $row_entry['main_ticket'] ?>"></td>
			<td class="hidden"><textarea id="flight_details<?= $count ?>_u" name="flight_details1" class="form-control hidden"><?= $trip_arr ?></textarea></td>
			<td><button type="button" class="btn btn-info btn-iti btn-sm" id="add_trips<?= $count ?>_u" title="Add Flight Ticket Details" onclick="add_flight_details(this.id,'update')" data-toggle="tooltip"><i class="fa fa-plus"></i></button></td>
		    <td class="hidden"><input type="text" id="entry_id<?= $count ?>_u" value="<?= $row_entry['entry_id'] ?>"></td>
		</tr>
		<script>
			$('#birth_date<?= $count ?>_u').datetimepicker({ timepicker:false, format:'d-m-Y' });
		</script>
		<?php
	}
}
else{
	include_once('../save/ticket_master_tbl.php');
}

?>

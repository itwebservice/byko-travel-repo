<?php 

$flag = true;
class vendor_master{
public function vendor_save()
{
	
    $tour_type = $_POST['tour_type'];
    $vehicle_name_arr = isset($_POST['vehicle_name_arr']) ? $_POST['vehicle_name_arr'] : [];
    $seating_capacity_arr = isset($_POST['seating_capacity_arr']) ? $_POST['seating_capacity_arr'] : [];
    $route_arr = isset($_POST['route_arr']) ? $_POST['route_arr'] : [];
    $total_days_arr = isset($_POST['total_days_arr']) ? $_POST['total_days_arr'] : [];
    $total_max_km_arr = isset($_POST['total_max_km_arr']) ? $_POST['total_max_km_arr'] : [];
    $rate_arr = isset($_POST['rate_arr']) ? $_POST['rate_arr'] : [];
    $extra_hrs_rate_arr = isset($_POST['extra_hrs_rate_arr']) ? $_POST['extra_hrs_rate_arr'] : [];
    $extra_km_rate_arr = isset($_POST['extra_km_rate_arr']) ? $_POST['extra_km_rate_arr'] : [];
    $driver_allowance_arr = isset($_POST['driver_allowance_arr']) ? $_POST['driver_allowance_arr'] : [];
    $permit_charges_arr = isset($_POST['permit_charges_arr']) ? $_POST['permit_charges_arr'] : [];
    $toll_parking_arr = isset($_POST['toll_parking_arr']) ? $_POST['toll_parking_arr'] : [];
    $state_entry_pass_arr = isset($_POST['state_entry_pass_arr']) ? $_POST['state_entry_pass_arr'] : [];
    $other_charges_arr = isset($_POST['other_charges_arr']) ? $_POST['other_charges_arr'] : [];
    $vehicle_name_local_arr = isset($_POST['vehicle_name_local_arr']) ? $_POST['vehicle_name_local_arr'] : [];
    $seating_capacity_local_arr = isset($_POST['seating_capacity_local_arr']) ? $_POST['seating_capacity_local_arr'] : [];
    $total_hrs_arr = isset($_POST['total_hrs_arr']) ? $_POST['total_hrs_arr'] : [];
    $total_km_arr= isset($_POST['total_km_arr']) ? $_POST['total_km_arr'] : [];
    $extra_hrs_rate_local_arr = isset($_POST['extra_hrs_rate_local_arr']) ? $_POST['extra_hrs_rate_local_arr'] : [];
    $extra_km_rate_local_arr = isset($_POST['extra_km_rate_local_arr']) ? $_POST['extra_km_rate_local_arr'] : [];
    $rate_local_arr = isset($_POST['rate_local_arr']) ? $_POST['rate_local_arr'] : [];
	begin_t();
	
	
	
		    if($tour_type=='Local'){

            for($i=0; $i<sizeof($vehicle_name_local_arr); $i++){

                $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from car_rental_tariff_entries"));
                $vehicle_id = $sq_max['max'] + 1;
                $query = "insert into car_rental_tariff_entries(entry_id, vehicle_name, seating_capacity, total_hrs,total_km, extra_hrs_rate, extra_km_rate, route, total_days,total_max_km,rate,driver_allowance,permit_charges,toll_parking,state_entry_pass,other_charges,tour_type,status) values ('$vehicle_id', '$vehicle_name_local_arr[$i]', '$seating_capacity_local_arr[$i]', '$total_hrs_arr[$i]', '$total_km_arr[$i]', '$extra_hrs_rate_local_arr[$i]','$extra_km_rate_local_arr[$i]','','','','$rate_local_arr[$i]','','','','','','Local','Active')";
                $sq_vendor = mysqlQuery($query);
                if(!$sq_vendor){
                    $GLOBALS['flag'] = false;
                    echo "error--Some tariff not added!";
                    //exit;
                }
            }
            if($GLOBALS['flag']){
                commit_t();
                echo "Vehicle Tariff has been successfully saved.";
                exit;
              }
              else{
                rollback_t();
                exit;
              }
        }
        else{

            for($i=0; $i<sizeof($vehicle_name_arr); $i++){
                $vehicle_name_arr = $_POST['vehicle_name_arr'];
                $seating_capacity_arr = $_POST['seating_capacity_arr'];
                $route_arr = $_POST['route_arr'];
                $total_days_arr = $_POST['total_days_arr'];
                $total_max_km_arr = $_POST['total_max_km_arr'];
                $rate_arr = $_POST['rate_arr'];
                $extra_hrs_rate_arr = $_POST['extra_hrs_rate_arr'];
                $extra_km_rate_arr = $_POST['extra_km_rate_arr'];
                $driver_allowance_arr = $_POST['driver_allowance_arr'];
                $permit_charges_arr = $_POST['permit_charges_arr'];
                $toll_parking_arr = $_POST['toll_parking_arr'];
                $state_entry_pass_arr = $_POST['state_entry_pass_arr'];
                $other_charges_arr = $_POST['other_charges_arr'];

                $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from car_rental_tariff_entries"));
                $vehicle_id = $sq_max['max'] + 1;
                $query = "insert into car_rental_tariff_entries(entry_id, vehicle_name, seating_capacity, total_hrs, total_km, extra_hrs_rate, extra_km_rate, route, total_days,total_max_km,rate,driver_allowance,permit_charges,toll_parking,state_entry_pass,other_charges,tour_type,status) values ('$vehicle_id', '$vehicle_name_arr[$i]', '$seating_capacity_arr[$i]', '','', '$extra_hrs_rate_arr[$i]', '$extra_km_rate_arr[$i]','$route_arr[$i]','$total_days_arr[$i]','$total_max_km_arr[$i]','$rate_arr[$i]','$driver_allowance_arr[$i]','$permit_charges_arr[$i]','$toll_parking_arr[$i]','$state_entry_pass_arr[$i]','$other_charges_arr[$i]','Outstation','Active')";
                $sq_vendor = mysqlQuery($query);
                if(!$sq_vendor){
                    $GLOBALS['flag'] = false;
                    echo "error--Some Tarrif not added!";
                }
            }
            if($GLOBALS['flag']){
                commit_t();
                echo "Car Rental Tarrif has been successfully saved.";
                exit;
              }
              else{
                rollback_t();
                exit;
              }
        }
	
}


public function vendor_update()
{
	$tour_type = $_POST['tour_type'];
	$entry_id = $_POST['entry_id']; 
	$local_vehicle_name = isset($_POST['local_vehicle_name']) ? $_POST['local_vehicle_name'] : null ;
	$local_total_hrs = isset($_POST['local_total_hrs']) ? $_POST['local_total_hrs'] : null ; 
	$local_total_km = isset($_POST['local_total_km']) ? $_POST['local_total_km'] : null ; 
	$local_extra_hrs_rate = isset($_POST['local_extra_hrs_rate']) ? $_POST['local_extra_hrs_rate'] : null ; 
	$local_extra_km = isset($_POST['local_extra_km']) ? $_POST['local_extra_km'] : null ;
	$local_rate = isset($_POST['local_rate']) ? $_POST['local_rate'] : null ;
	$local_status = isset($_POST['local_status']) ? $_POST['local_status'] : null ;

	$vehicle_name = isset($_POST['vehicle_name']) ? $_POST['vehicle_name'] : null; 
	$extra_hrs_rate = isset($_POST['extra_hrs_rate']) ? $_POST['extra_hrs_rate'] : null; 
	$extra_km = isset( $_POST['extra_km']) ?  $_POST['extra_km'] : null; 
	$route = isset( $_POST['route']) ?  $_POST['route'] : null; 
	$total_days = isset( $_POST['total_days']) ?  $_POST['total_days'] : null;
	$total_max_km = isset( $_POST['total_max_km']) ?  $_POST['total_max_km'] : null;
	$rate = isset( $_POST['rate']) ?  $_POST['rate'] : null;
	$driver_allowance = isset( $_POST['driver_allowance']) ?  $_POST['driver_allowance'] : null;
	$permit_charges = isset( $_POST['permit_charges']) ?  $_POST['permit_charges'] : null;
	$toll_parking = isset( $_POST['toll_parking']) ?  $_POST['toll_parking'] : null;
	$state_entry_pass = isset($_POST['state_entry_pass']) ? $_POST['state_entry_pass'] : null;
	$other_charges = isset($_POST['other_charges']) ? $_POST['other_charges'] : null;
	$status = isset($_POST['status']) ? $_POST['status'] : null;
	
	begin_t();
	if($tour_type=="Local"){
		$sq_vendor = mysqlQuery("update car_rental_tariff_entries set vehicle_name	='$local_vehicle_name', total_hrs='$local_total_hrs', total_km='$local_total_km', extra_hrs_rate='$local_extra_hrs_rate', extra_km_rate='$local_extra_km', rate='$local_rate',status='$local_status' where entry_id='$entry_id'");
	}else{
		$sq_vendor = mysqlQuery("update car_rental_tariff_entries set vehicle_name	='$vehicle_name', extra_hrs_rate='$extra_hrs_rate', extra_km_rate='$extra_km',route='$route', total_days='$total_days', total_max_km='$total_max_km', rate='$rate',driver_allowance='$driver_allowance',permit_charges='$permit_charges',toll_parking='$toll_parking',state_entry_pass='$state_entry_pass',other_charges='$other_charges',status='$status' where entry_id='$entry_id'");
	}

	sundry_creditor_balance_update();
	
	if($sq_vendor)
	{
	    if($GLOBALS['flag']){
	      commit_t();
	      echo "Vehicle Tariff has been successfully updated.";
	      exit;
	    }
	    else{
	      rollback_t();
	      exit;
	    }
	}
	else{
		rollback_t();
		echo "error--Sorry, Tariff not updated!";
		exit;
	}
}
}

?>
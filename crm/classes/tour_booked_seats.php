<?php
$bk_seats = new total_booked_seats();
class total_booked_seats{

	public function booked_seats($tour_id, $tour_group_id)
	{
		// CRM Group Tour sale
	    $traveler_group=array();
        $sq_1 = mysqlQuery("select * from tourwise_traveler_details where tour_id='$tour_id' and tour_group_id = '$tour_group_id' and delete_status='0'");
        while($row_1 = mysqli_fetch_assoc($sq_1))
        {
            $status = '';
            $pass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_1[traveler_group_id]'"));
            $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from travelers_details where traveler_group_id='$row_1[traveler_group_id]' and status='Cancel'"));
            if($row_1['tour_group_status']=="Cancel"){
                $status = "cancel";
            }
            else{
                if($pass_count==$cancelpass_count){
                    $status = "cancel";
                }
            }
            if($status == ''){
                array_push($traveler_group,$row_1['traveler_group_id']);
            }
        }
        $query = "select * from travelers_details where 1 ";
        for($i=0; $i<sizeof($traveler_group); $i++)
        {   
            if($i>0){
                $query = $query." or traveler_group_id= '$traveler_group[$i]'";
            }
            else{    
                $query = $query." and (traveler_group_id= '$traveler_group[$i]')";
            }
        }
        $query .= " and status='Active'";
        $booked_seats = (sizeof($traveler_group) > 0) ? mysqli_num_rows(mysqlQuery($query)) : 0;  
        return $booked_seats;
	}

    function b2c_booked_seats($tour_id, $tour_group_id)
    {	
        $booked_seats = 0;
        $sq_group = mysqli_fetch_assoc(mysqlQuery("select from_date,to_date from tour_groups where group_id='$tour_group_id'"));
        $from_date = $sq_group['from_date'];
        $to_date = $sq_group['to_date'];

        $sq_1 = mysqlQuery("select * from b2c_sale where service = 'Group Tour' and status!='Cancel'");
        while($row_1 = mysqli_fetch_assoc($sq_1)){

            $enq_data = json_decode($row_1['enq_data']);
            
            $efrom_date = date('Y-m-d', strtotime($enq_data[0]->travel_from));
            $eto_date = date('Y-m-d', strtotime($enq_data[0]->travel_to));

            if($tour_id == $enq_data[0]->package_id && $efrom_date == $from_date && $eto_date == $to_date){
                $total_pax = intval($enq_data[0]->adults)+intval($enq_data[0]->chwob)+intval($enq_data[0]->chwb);
                $booked_seats += $total_pax;
            }
        }
        return $booked_seats;
    }
	public function b2b_booked_seats($tour_id, $tour_group_id)
	{	
		$booked_seats = 0;
		$sq_1 = mysqlQuery("select * from b2b_booking_master where status!='Cancel'");
	    while($row_1 = mysqli_fetch_assoc($sq_1)){

			$cart_checkout_data = json_decode($row_1['cart_checkout_data']);
			for($i=0;$i<count(array($cart_checkout_data));$i++){
				if($cart_checkout_data[$i]->service->name == 'Group Tours'){
                    $services = isset($cart_checkout_data[$i]->service) ? $cart_checkout_data[$i]->service : [];
					for($j=0;$j<count(array($services));$j++){

                        $group_id = explode('=',$cart_checkout_data[$i]->service->service_arr[0]->tour_group);
						if($tour_id == $cart_checkout_data[$i]->service->service_arr[0]->tour_id && $tour_group_id == $group_id[0]){
							$booked_seats += intval($cart_checkout_data[$i]->service->service_arr[0]->adult) + intval($cart_checkout_data[$i]->service->service_arr[0]->childwo) + intval($cart_checkout_data[$i]->service->service_arr[0]->childwi);
						}
					}
				}
			}
		}
	    return $booked_seats;
	}
}
?>
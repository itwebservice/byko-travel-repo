<?php
include "../../../../../model/model.php";
$selectedDate = !empty($_POST['exp_date']) ? get_date_db($_POST['exp_date']) : date('Y-m-d');
if(!empty($_POST['exp_date'])){
    $date_query = " and to_date = '$selectedDate'";
}else{
	$end_date = date('Y-m-d', strtotime('30 days', strtotime($selectedDate)));
    $date_query = " and to_date between '$selectedDate' and '$end_date'";
}
$array_s = array();
$count = 1;
//Hotel
$sq_hotel = mysqlQuery("select hotel_id,hotel_name from hotel_master where active_flag='Active'");
while($row_hotel = mysqli_fetch_assoc($sq_hotel)){
    
    $tariff_data = array();
    $sq_tariff_count = mysqli_num_rows(mysqlQuery("select pricing_id from hotel_vendor_price_master where hotel_id='$row_hotel[hotel_id]'"));
    if($sq_tariff_count > 0){

        $sq_tariff = mysqlQuery("select pricing_id from hotel_vendor_price_master where hotel_id='$row_hotel[hotel_id]'");
        while($row_tariff = mysqli_fetch_assoc($sq_tariff)){
            
            // hotel_contracted_tarrif start
            $cc_t_query = "select entry_id from hotel_contracted_tarrif where pricing_id='$row_tariff[pricing_id]'";
            $cc_t_query .= $date_query;
            $sq_ctariff_count = mysqli_num_rows(mysqlQuery($cc_t_query));
            if($sq_ctariff_count > 0){

                $c_t_query = "select entry_id from hotel_contracted_tarrif where pricing_id='$row_tariff[pricing_id]'";
                $c_t_query .= $date_query;
                $sq_ctariff = mysqlQuery($c_t_query);
                while($row_ctariff = mysqli_fetch_assoc($sq_ctariff)){
                    
                    $myObject = new stdClass(); // Create a new object
                    $myObject->table = 'hotel_contracted_tarrif';
                    $myObject->column = 'entry_id';
                    $myObject->entry_id = $row_ctariff['entry_id'];
                    array_push($tariff_data,$myObject);
                }
            } // hotel_contracted_tarrif end
            // hotel_blackdated_tarrif start
            $cc_t_query = "select entry_id from hotel_blackdated_tarrif where pricing_id='$row_tariff[pricing_id]'";
            $cc_t_query .= $date_query;
            $sq_ctariff_count = mysqli_num_rows(mysqlQuery($cc_t_query));
            if($sq_ctariff_count > 0){

                $c_t_query = "select entry_id from hotel_blackdated_tarrif where pricing_id='$row_tariff[pricing_id]'";
                $c_t_query .= $date_query;
                $sq_ctariff = mysqlQuery($c_t_query);
                while($row_ctariff = mysqli_fetch_assoc($sq_ctariff)){
                    
                    $myObject = new stdClass(); // Create a new object
                    $myObject->table = 'hotel_blackdated_tarrif';
                    $myObject->column = 'entry_id';
                    $myObject->entry_id = $row_ctariff['entry_id'];
                    array_push($tariff_data,$myObject);
                }
            }// hotel_blackdated_tarrif end
        }
    }
    if (!empty($tariff_data)) {
        
        $tariff_data = json_encode($tariff_data,true);
        $view_btn = "<button class='btn btn-info btn-sm' onclick='view_details_modal(this.id," .$tariff_data . ")' data-toggle='tooltip' title='View Details' id='".$row_hotel['hotel_name']."'><i class='fa fa-eye'></i></button>";
        array_push($array_s, array("data"=>array(
            $count++,
            'Hotel',
            $row_hotel['hotel_name'],
            $view_btn
        )));
    }
}

//Transfer
$sq_hotel = mysqlQuery("select entry_id,vehicle_name from b2b_transfer_master where status='Active'");
while($row_hotel = mysqli_fetch_assoc($sq_hotel)){

    $tariff_data = array();
    $sq_tariff_count = mysqli_num_rows(mysqlQuery("select tariff_id from b2b_transfer_tariff where vehicle_id='$row_hotel[entry_id]'"));
    if($sq_tariff_count > 0){

        $sq_tariff = mysqlQuery("select tariff_id from b2b_transfer_tariff where vehicle_id='$row_hotel[entry_id]'");
        while($row_tariff = mysqli_fetch_assoc($sq_tariff)){
            
            // b2b_transfer_tariff_entries start
            $cc_t_query = "select tariff_entries_id from b2b_transfer_tariff_entries where tariff_id='$row_tariff[tariff_id]'";
            $cc_t_query .= $date_query;
            $sq_ctariff_count = mysqli_num_rows(mysqlQuery($cc_t_query));
            if($sq_ctariff_count > 0){

                $c_t_query = "select tariff_entries_id from b2b_transfer_tariff_entries where tariff_id='$row_tariff[tariff_id]'";
                $c_t_query .= $date_query;
                $sq_ctariff = mysqlQuery($c_t_query);
                while($row_ctariff = mysqli_fetch_assoc($sq_ctariff)){
                    
                    $myObject = new stdClass(); // Create a new object
                    $myObject->table = 'b2b_transfer_tariff_entries';
                    $myObject->column = 'tariff_entries_id';
                    $myObject->entry_id = $row_ctariff['tariff_entries_id'];
                    array_push($tariff_data,$myObject);
                }
            } // b2b_transfer_tariff_entries end
        }
    }
    if (!empty($tariff_data)) {
        
        $tariff_data = json_encode($tariff_data,true);
        $view_btn = "<button class='btn btn-info btn-sm' onclick='view_details_modal(this.id," .$tariff_data . ")' data-toggle='tooltip' title='View Details' id='".$row_hotel['vehicle_name']."'><i class='fa fa-eye'></i></button>";
        array_push($array_s, array("data"=>array(
            $count++,
            'Transfer',
            $row_hotel['vehicle_name'],
            $view_btn
        )));
    }
}

//Activity
$sq_hotel = mysqlQuery("select entry_id,excursion_name from excursion_master_tariff where active_flag='Active'");
while($row_hotel = mysqli_fetch_assoc($sq_hotel)){

    $tariff_data = array();
    // excursion_master_tariff_basics start
    $cc_t_query = "select entry_id from excursion_master_tariff_basics where exc_id='$row_hotel[entry_id]'";
    $cc_t_query .= $date_query;
    // echo $cc_t_query;
    $sq_ctariff_count = mysqli_num_rows(mysqlQuery($cc_t_query));
    if($sq_ctariff_count > 0){

        $c_t_query = "select entry_id from excursion_master_tariff_basics where exc_id='$row_hotel[entry_id]'";
        $c_t_query .= $date_query;
        $sq_ctariff = mysqlQuery($c_t_query);
        while($row_ctariff = mysqli_fetch_assoc($sq_ctariff)){
            
            $myObject = new stdClass(); // Create a new object
            $myObject->table = 'excursion_master_tariff_basics';
            $myObject->column = 'entry_id';
            $myObject->entry_id = $row_ctariff['entry_id'];
            array_push($tariff_data,$myObject);
        }
    } // excursion_master_tariff_basics end
    if (!empty($tariff_data)) {
        
        $tariff_data = json_encode($tariff_data,true);
        $view_btn = "<button class='btn btn-info btn-sm' onclick='view_details_modal(this.id," .$tariff_data . ")' data-toggle='tooltip' title='View Details' id='".$row_hotel['excursion_name']."'><i class='fa fa-eye'></i></button>";
        array_push($array_s, array("data"=>array(
            $count++,
            'Activity',
            $row_hotel['excursion_name'],
            $view_btn
        )));
    }
}


// array_push($array_s, $footer_data);
echo json_encode($array_s);

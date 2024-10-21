<?php
include "../../../model/model.php";
$register_id = $_POST['register_id'];
$user_id = $_SESSION['user_id'];
$agent_flag = $_SESSION['agent_flag'];

$sq_reg = mysqli_fetch_assoc(mysqlQuery("select request_id,cart_data from b2b_login where user_id='$user_id' and agent_flag='$agent_flag'"));
$request_id = $sq_reg['request_id'];
$agent_cart_data = ($sq_reg['cart_data'] != '' && $sq_reg['cart_data'] != 'null') ? json_decode($sq_reg['cart_data']) : array();
$count = 0;

for($j=0;$j<sizeof($agent_cart_data);$j++){
    if($agent_cart_data[$j]->service->name == 'Hotel'){
        $count++;
        break;
    }
}
if($count>0){
    $sq_req_count = mysqli_num_rows(mysqlQuery("select request_id from hotel_availability_request where request_id='$request_id'"));
    if($sq_req_count > 0 && $request_id != 0){

        $sq_response = mysqli_fetch_assoc(mysqlQuery("select cart_data, response from hotel_availability_request where request_id='$request_id'"));
        $cart_data = ($sq_response['cart_data'] != '' && $sq_response['cart_data'] != 'null') ? json_decode($sq_response['cart_data']) : array();
        $response = ($sq_response['response'] != '' && $sq_response['response'] != 'null') ? json_decode($sq_response['response']) : array();

        if(sizeof($response) == sizeof($cart_data)){
            $flag = 1;
            for($i=0;$i<sizeof($response);$i++){
                if($response[$i]->status == 'Not Available'){
                    $flag = 0;
                    break;
                }
            }
            if($flag == 0){
                echo 'error--The requested hotel is not available on Check In Date. Please search for another hotel';
            }
        }
        else{

            echo 'error--Your hotels are under availability review. Please proceed once received availability confirmation.';
        }
    }
    else{
        echo 'error--Please click to Check Availability of hotel before Checkout.';
    }
}else{
    echo '';
}
?>
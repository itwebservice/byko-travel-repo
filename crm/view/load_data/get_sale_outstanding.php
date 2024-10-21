<?php include_once("../../model/model.php");
$booking_type = $_POST['booking_type'];
$booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if($booking_type=="visa"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from visa_payment_master where clearance_status!='Cancelled' and clearance_status!='Pending' and visa_id='$booking_id'"));
    $sq_visa = mysqli_fetch_assoc(mysqlQuery("select * from visa_master where visa_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_visa['visa_total_cost'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $canc_amount = $sq_visa['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="flight"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from ticket_payment_master where clearance_status!='Cancelled' and clearance_status!='Pending' and ticket_id='$booking_id'"));
    $sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_ticket_info['ticket_total_cost'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    $canc_amount = $sq_ticket_info['cancel_amount'];    
    if($sq_ticket_info['cancel_type'] == '1'){
        if($total_pay_amt > 0){
            if($canc_amount >0){
                if($total_pay_amt > $canc_amount){
                    $outstanding = 0;
                }else{
                    $outstanding = $canc_amount - $total_pay_amt  + $sq_pay['sumc'];
                }
            }else{
                $outstanding = 0;
            }
        }
        else{
            $outstanding = $canc_amount;
        }
    }else if($sq_ticket_info['cancel_type'] == '2'||$sq_ticket_info['cancel_type'] == '3'){
        $cancel_estimate = json_decode($sq_ticket_info['cancel_estimate']);
        $outstanding = (($total_sale - floatval($cancel_estimate[0]->ticket_total_cost)) + $canc_amount) - $total_pay_amt;
    }
    else{
        $outstanding = $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="train"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from train_ticket_payment_master where clearance_status!='Cancelled' and clearance_status!='Pending' and train_ticket_id='$booking_id'"));
    $sq_train_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_train_ticket_info['net_total'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $canc_amount = $sq_train_ticket_info['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="hotel"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from hotel_booking_payment where clearance_status!='Cancelled' and clearance_status!='Pending' and booking_id='$booking_id'"));
    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from hotel_booking_master where booking_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_booking['total_fee'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $canc_amount = $sq_booking['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="bus"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from bus_booking_payment_master where clearance_status!='Cancelled' and clearance_status!='Pending' and booking_id='$booking_id'"));
    $sq_bus_info = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_bus_info['net_total'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $canc_amount = $sq_bus_info['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="car"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from car_rental_payment where clearance_status!='Cancelled' and clearance_status!='Pending' and booking_id='$booking_id'"));
    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from car_rental_booking where booking_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_booking['total_fees'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $canc_amount = $sq_booking['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="excursion"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from exc_payment_master where clearance_status!='Cancelled' and clearance_status!='Pending' and exc_id='$booking_id'"));
    $sq_exc_info = mysqli_fetch_assoc(mysqlQuery("select * from excursion_master where exc_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_exc_info['exc_total_cost'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $canc_amount = $sq_exc_info['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="miscellaneous"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum,sum(credit_charges) as sumc from miscellaneous_payment_master where clearance_status!='Cancelled' and clearance_status!='Pending' and misc_id='$booking_id'"));
    $sq_visa_info = mysqli_fetch_assoc(mysqlQuery("select * from miscellaneous_master where misc_id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_visa_info['misc_total_cost'] + $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $canc_amount = $sq_visa_info['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="package"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum,sum(credit_charges) as sumc from package_payment_master where clearance_status!='Cancelled' and clearance_status!='Pending'  and booking_id='$booking_id'"));
    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id' and delete_status='0'"));

    $total_sale = $sq_booking['net_total']+$sq_pay['sumc'];;
    $total_pay_amt = $sq_pay['sum']+$sq_pay['sumc'];
    
    if($status != ''){
        $sq_esti = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$sq_booking[booking_id]'"));
        $canc_amount = $sq_esti['cancel_amount'];
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else if($booking_type=="group"){
    $sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum,sum(credit_charges) as sumc from payment_master where clearance_status!='Cancelled' and clearance_status!='Pending'  and tourwise_traveler_id='$booking_id'"));
    $sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where id='$booking_id' and delete_status='0'"));
    $total_sale = $sq_booking['net_total']+ $sq_pay['sumc'];
    $total_pay_amt = $sq_pay['sum']  + $sq_pay['sumc'];
    if($status != ''){
        $sq_esti = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$sq_booking[booking_id]'"));
        $sq_est_count = mysqli_num_rows(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$sq_booking[id]'"));
        if($sq_est_count!='0'){
            $sq_tour_refund = mysqli_fetch_assoc(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$sq_booking[id]'"));
            $canc_amount = $sq_tour_refund['cancel_amount'];
        }
        else{
            $sq_tour_refund = mysqli_fetch_assoc(mysqlQuery("select * from refund_traveler_estimate where tourwise_traveler_id='$sq_booking[id]'"));
            $canc_amount = $sq_tour_refund['cancel_amount'];
        }
        $outstanding = ($total_pay_amt > $canc_amount) ? 0 : floatval($canc_amount) - floatval($total_pay_amt) + $sq_pay['sumc'];
    }else{
        $outstanding =  $total_sale - $total_pay_amt;
    }
}
else{
    $total_sale = 0;
    $total_pay_amt = 0;
}
echo $outstanding.'-'.$status;
?>
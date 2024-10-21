<?php
class quotation_master{

    public function quotation_save(){

        $optionJson = $_POST['optionJson'];
        $costingJson = $_POST['costingJson'];
        $bsmValues = $_POST['bsmValues'];
        $enquiryDetails = $_POST['enquiryDetails'];
        $currency_code = $_POST['currency_code'];
        $hotel_requirements = addslashes($_POST['hotel_requirements']);

        $login_id = $_SESSION['login_id'];
        $emp_id = $_SESSION['emp_id'];
        $branch_admin_id = $_SESSION['branch_admin_id'];
        $financial_year_id = $_SESSION['financial_year_id'];
        $quotation_date = date('Y-m-d',strtotime($_POST['quotation_date']));
        $created_at = date("Y-m-d");
        $costingDetails = json_decode(json_encode($costingJson), true);
        $bsmValues_val = json_decode(json_encode($bsmValues));
        $enquiryDetails = json_encode($enquiryDetails);
        
        $errorCont = array();
        begin_t();

        $hotelDetails_ins = json_encode($optionJson);
        $bsmValues_ins = json_encode($bsmValues_val);
        $costingDetails_ins = json_encode($costingDetails);

        $sq_max = mysqli_fetch_assoc(mysqlQuery("SELECT max(`quotation_id`) as max from `hotel_quotation_master`"));
        $quotationId = $sq_max['max'] + 1;
        
        $sq_ins = mysqlQuery("INSERT INTO `hotel_quotation_master`(`quotation_id`,`login_id`, `emp_id`, `branch_admin_id`, `financial_year_id`, `hotel_details`,`costing_details`,`enquiry_details`, `quotation_date`,`created_at`,`bsmValues`,`currency_code`,`status`,`hotel_req`) VALUES('$quotationId','$login_id','$emp_id','$branch_admin_id', '$financial_year_id','$hotelDetails_ins', '$costingDetails_ins', '$enquiryDetails', '$quotation_date', '$created_at', '$bsmValues_ins','$currency_code','1','$hotel_requirements')");
        array_push($errorCont, ($sq_ins) ? true : false);

        if(in_array(false, $errorCont)){
            rollback_t();
            echo "error--Sorry Hotel Quotation not saved successfully!";
            exit;
        }
        else{
            commit_t();
            ////////////Enquiry Save///////////
            $enquiryDetails = json_decode($enquiryDetails,true);
            $whatsapp_no = $enquiryDetails['country_code'].$enquiryDetails['whatsapp_no'];
            $hotel_requirements = isset($enquiryDetails['hotel_requirements']) ? $enquiryDetails['hotel_requirements'] : '';
            $total_adult = $enquiryDetails['total_adult'];
            $total_cwb = $enquiryDetails['children_without_bed'];
            $total_cwob = $enquiryDetails['children_with_bed'];
            $total_infant = $enquiryDetails['total_infant'];
            $total_members = $enquiryDetails['total_members'];

            $enquiry_content = '[{"name":"hotel_requirements","value":"'.$hotel_requirements.'"},{"name":"total_adult","value":"'.$total_adult.'"},{"name":"total_cwb","value":"'.$total_cwb.'"},{"name":"total_cwob","value":"'.$total_cwob.'"},{"name":"total_infant","value":"'. $total_infant.'"},{"name":"total_members","value":"'.$total_members.'"},{"name":"budget","value":"0"}]';

            if($enquiryDetails['enquiry_id'] == '0'){
                $customer_name = $enquiryDetails['customer_name'];
                $whatsapp_no = $enquiryDetails['whatsapp_no'];
                $country_code = $enquiryDetails['country_code'];
                $landline_no = $country_code.$whatsapp_no ;
                $email_id = $enquiryDetails['email_id'];
                $sq_max_id = mysqli_fetch_assoc(mysqlQuery("select max(enquiry_id) as max from enquiry_master"));
                $enquiry_id1 = $sq_max_id['max']+1;
                $sq_enquiry = mysqlQuery("insert into enquiry_master (enquiry_id, login_id,branch_admin_id,financial_year_id, enquiry_type,enquiry, name, mobile_no, country_code,landline_no, email_id,location, assigned_emp_id, enquiry_specification, enquiry_date, followup_date, reference_id, enquiry_content ) values ('$enquiry_id1', '$login_id', '$branch_admin_id','$financial_year_id', 'Hotel','Strong', '$customer_name', '$whatsapp_no', '$country_code','$landline_no', '$email_id','', '$emp_id','', '$quotation_date', '$quotation_date', '', '$enquiry_content')");

                $enquiryDetails['enquiry_id'] = "$enquiry_id1";
                $enquiryDetails = json_encode($enquiryDetails);
                
                if($sq_enquiry){
                    $sq_quot_update = mysqlQuery("update hotel_quotation_master set enquiry_details='$enquiryDetails' where quotation_id='$quotationId'");
                }

                $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from enquiry_master_entries"));
                $entry_id = $sq_max['max'] + 1;
                $sq_followup = mysqlQuery("insert into enquiry_master_entries(entry_id, enquiry_id, followup_reply,  followup_status,  followup_type, followup_date, followup_stage, created_at) values('$entry_id', '$enquiry_id1', '', 'Active','', '$quotation_date','Strong', '$quotation_date')");
                
                $sq_entryid = mysqlQuery("update enquiry_master set entry_id='$entry_id' where enquiry_id='$enquiry_id1'");
            }

            echo "Hotel Quotation is saved successfully!";
            exit;
        }
    }
    
    public function quotation_update(){
        
        $quotationId = $_POST['quotation_id'];
        $quotation_date = get_date_db($_POST['quotation_date']);
        $currency_code = $_POST['currency_code'];
        $hotel_requirements = addslashes($_POST['hotel_requirements']);
        $hotelDetails = json_encode($_POST['hotelDetails']);
        $costingDetails = $_POST['costingDetails'];
        $bsmValues_val = json_decode(json_encode($_POST['bsmValues']));
        $enquiryDetails = json_encode($_POST['enquiryDetails']);
        $active_flag = $_POST['active_flag'];

        begin_t();

        $bsmValues_ins = json_encode($bsmValues_val);
        $costingDetails_ins = json_encode($costingDetails, true);
        $sq_upd = mysqlQuery("UPDATE `hotel_quotation_master` SET  `hotel_details` = '$hotelDetails',`costing_details` = '$costingDetails_ins', `enquiry_details` = '$enquiryDetails', `bsmValues` = '$bsmValues_ins',quotation_date='$quotation_date',`currency_code`='$currency_code',status='$active_flag',hotel_req='$hotel_requirements' WHERE `quotation_id` =".$quotationId);
        
        if(!$sq_upd){
            rollback_t();
            echo "error--Sorry Hotel Quotation not updated successfully!";
            exit;
        }
        else{
            commit_t();
            echo "Hotel Quotation is updated successfully!";
        }
    }

    public function quotation_clone(){
        $quotation_id = $_POST['quotation_id'];
        $branch_admin_id = $_SESSION['branch_admin_id'];
        $login_id = $_SESSION['login_id'];
        $emp_id = $_SESSION['emp_id'];
        $financial_year_id = $_SESSION['financial_year_id'];
        $created_at = date("Y-m-d");

        $quotationValues = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM `hotel_quotation_master` WHERE `quotation_id`=".$quotation_id));
        $hotel_req = addslashes($quotationValues['hotel_req']);

        $sq_max = mysqli_fetch_assoc(mysqlQuery("SELECT max(`quotation_id`) as max from `hotel_quotation_master`"));
        $quotationId = $sq_max['max'] + 1;

        $sq_ins = mysqlQuery("INSERT INTO `hotel_quotation_master`(`quotation_id`,`login_id`, `emp_id`, `branch_admin_id`, `financial_year_id`, `hotel_details`,`costing_details`,`enquiry_details`, `quotation_date`,`created_at`,`bsmValues`,`clone`,`currency_code`,`status`,`hotel_req`) VALUES('$quotationId','$login_id','$emp_id','$branch_admin_id', '$financial_year_id','$quotationValues[hotel_details]', '$quotationValues[costing_details]', '$quotationValues[enquiry_details]', '$created_at', '$created_at','$quotationValues[bsmValues]','1','$quotationValues[currency_code]','1','$hotel_req')");

        if(!$sq_ins){
            echo "error--Sorry Hotel Quotation not cloned successfully!";
            exit;
        }
        else{
            echo "Hotel Quotation is Cloned Successfully";
            exit;
        }
    }

    public function quotation_email(){
        
        global $model, $app_name,$currency;
        $quotation_id_arr = $_POST['quotation_id_arr'];
        $content = '';
        $yr = explode("-", date('Y-m-d'));
        $year =$yr[0];
        for($j = 0; $j < sizeof($quotation_id_arr);  $j++){

            $sq_hotel = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM `hotel_quotation_master` WHERE `quotation_id`='$quotation_id_arr[$j]'"));
            $sq_emp =  mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$sq_hotel[emp_id]'"));
            $emp_name = ($sq_hotel['emp_id'] != 0) ? $sq_emp['first_name'].' '.$sq_emp['last_name'] : 'Admin';

            $content .= '<tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation"> <tr><td style="text-align:left;width:50%">Quotation ID:- '.get_quotation_id($quotation_id_arr[$j], $year).'</td></tr></table></tr>';

            $enquiryDetails = json_decode($sq_hotel['enquiry_details'], true);
            $hotelDetails = json_decode($sq_hotel['hotel_details'], true);
            $costDetails = json_decode($sq_hotel['costing_details'], true);
            for($index = 0; $index<sizeof($hotelDetails); $index++){
                $option = $hotelDetails[$index]['option'];
                $content .= '<tr>
                <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
                <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Option</td>   <td style="text-align:left;border: 1px solid #888888;">'.$option.'</td></tr>
                </table>
                </tr>';

                $data = $hotelDetails[$index]['data'];
                for($i = 0;$i<sizeof($data);$i++){

                        $hotelName = mysqli_fetch_assoc(mysqlQuery("SELECT `hotel_name` FROM `hotel_master` WHERE `hotel_id`=".$data[$i]['hotel_id']));
                        $cityName = mysqli_fetch_assoc(mysqlQuery("SELECT `city_name` FROM `city_master` WHERE `city_id`=".$data[$i]['city_id']));
                        if($data[$i]['total_rooms'] == ''){
                            $data[$i]['total_rooms'] = 0;
                        }
                        if($data[$i]['extra_bed'] == ''){
                            $data[$i]['extra_bed'] = 0;
                        }
                    $content .= '<tr>
                    <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Tour Type</td>   <td style="text-align:left;border: 1px solid #888888;">'.$data[$i]['tour_type'].'</td></tr>
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">City Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.$cityName['city_name'].'</td></tr>
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Hotel Name(Option-'.($i+1).')</td>   <td style="text-align:left;border: 1px solid #888888;">'.$hotelName['hotel_name'].'</td></tr>
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Check In Date</td>   <td style="text-align:left;border: 1px solid #888888;">'.get_date_user($data[$i]['checkin']).'</td></tr>
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Check Out Date</td>   <td style="text-align:left;border: 1px solid #888888;">'.get_date_user($data[$i]['checkout']).'</td></tr>
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Total Nights</td>   <td style="text-align:left;border: 1px solid #888888;">'.$data[$i]['hotel_stay_days'].'</td></tr>
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Total Rooms</td>   <td style="text-align:left;border: 1px solid #888888;">'.$data[$i]['total_rooms'].' Room(s)</td></tr>
                    <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Extra Bed</td>   <td style="text-align:left;border: 1px solid #888888;">'.$data[$i]['extra_bed'].' Bed(s)</td></tr>
                    </table>
                    </tr>
                    ';
                }
                $hotel_cost = 0;
                $tax_amount = 0;
                $total_cost = 0;
                $option = $costDetails[$index]['option'];
                $data = $costDetails[$index]['costing'];

                $service_tax_amount = 0;
                if($data['tax_amount'] !== 0.00 && ($data['tax_amount']) !== ''){
                    $service_tax_subtotal1 = explode(',',$data['tax_amount']);
                    for($j=0;$j<sizeof($service_tax_subtotal1);$j++){
                        $service_tax = explode(':',$service_tax_subtotal1[$j]);
                        $service_tax_amount += floatval($service_tax[2]);
                    }
                }
                ////////////////////Markup Rules
                $markupservice_tax_amount = 0;
                if($data['markup_tax'] !== 0.00 && $data['markup_tax'] !== ""){
                    $service_tax_markup1 = explode(',',$data['markup_tax']);
                    for($j=0;$j<sizeof($service_tax_markup1);$j++){
                        $service_tax = explode(':',$service_tax_markup1[$j]);
                        $markupservice_tax_amount += floatval($service_tax[2]);
                    }
                }
                $hotel_cost += $data['hotel_cost']+$data['service_charge']+$data['markup_cost']+$data['roundoff'];
                $tax_amount += $service_tax_amount + $markupservice_tax_amount;
                $total_cost += $data['total_amount'];
                $tcs_amnt= $data['tcs_amnt'];
    
                //Currency conversion
                $hotel_cost_s = currency_conversion($currency,$sq_hotel['currency_code'],$hotel_cost);
                $total_cost_s = currency_conversion($currency,$sq_hotel['currency_code'],$total_cost);
                $tax_amount_s = currency_conversion($currency,$sq_hotel['currency_code'],$tax_amount);
                $tcs_amount = currency_conversion($currency,$sq_hotel['currency_code'],$tcs_amnt);

                $content .= '<tr>
                <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:10px; min-width: 100%;" role="presentation">
                <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Total Cost</td>   <td style="text-align:left;border: 1px solid #888888;">'.$hotel_cost_s.'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Tax Amount</td>   <td style="text-align:left;border: 1px solid #888888;">'.$tax_amount_s.'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888; width:50%">TCS Amount</td>   <td style="text-align:left;border: 1px solid #888888;">'.$tcs_amount.'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888; color:#1a73e8;width:50%">Option'.$option.' Cost</td>   <td style="text-align:left;border: 1px solid #888888;">'.$total_cost_s.'</td></tr>
                </table>
                </tr>
                ';
            }
        }
        $content .= '<tr>
        <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:10px; min-width: 100%;" role="presentation">
        <tr><td style="text-align:left;border: 1px solid #888888; width:50%">Created By</td>   <td style="text-align:left;border: 1px solid #888888;">'.$emp_name.'</td></tr>
        </table>
        </tr>';
        
        $subject = 'New Quotation : ('.$app_name.' )';
		$model->app_email_send('8',$enquiryDetails['customer_name'],$enquiryDetails['email_id'], $content,$subject,'1');
        echo "Quotation Email Sent!";
        exit;
    }

    public function whatsapp_send(){
        global $app_contact_no,$currency,$app_name;
        $emp_id = $_SESSION['emp_id'];
        $quotation_id = $_POST['quotation_id'];
        
        $sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id = '$emp_id'"));
        if($emp_id == 0){
            $contact = $app_contact_no;
        }
        else{
            $contact = $sq_emp_info['mobile_no'];
        }
        $sq_hotel = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM `hotel_quotation_master` WHERE `quotation_id`=".$quotation_id));

        $enquiryDetails = json_decode($sq_hotel['enquiry_details'], true);
        $hotelDetails = json_decode($sq_hotel['hotel_details'], true);
        $costDetails = json_decode($sq_hotel['costing_details'], true);
        
        $whatsapp_msg = rawurlencode('Dear '.$enquiryDetails['customer_name'].',
Hope you are doing great. Following are the hotel quotation details.');
        
        for($index = 0; $index<sizeof($hotelDetails); $index++){

            $option = $hotelDetails[$index]['option'];
            $whatsapp_msg .= rawurlencode('
*Option* : '.$option);

            $data = $hotelDetails[$index]['data'];
            for($i = 0;$i<sizeof($data);$i++){

                $hotelName = mysqli_fetch_assoc(mysqlQuery("SELECT `hotel_name` FROM `hotel_master` WHERE `hotel_id`=".$data[$i]['hotel_id']));
                $cityName = mysqli_fetch_assoc(mysqlQuery("SELECT `city_name` FROM `city_master` WHERE `city_id`=".$data[$i]['city_id']));
                if($data[$i]['total_rooms'] == ''){
                    $data[$i]['total_rooms'] = 0;
                }
                if($data[$i]['extra_bed'] == ''){
                    $data[$i]['extra_bed'] = 0;
                }
                $whatsapp_msg .= rawurlencode('
*Tour Type* : '.($data[$i]['tour_type']).'
*City Name* : '.($cityName['city_name']).'
*Hotel Name* : '.($hotelName['hotel_name']).'
*Check In Date* : '.get_date_user($data[$i]['checkin']).'
*Check Out Date* : '.get_date_user($data[$i]['checkout']).'
*Total Nights* : '.($data[$i]['hotel_stay_days']).'
*Total Rooms* : '.($data[$i]['total_rooms']).' Room(s)
*Extra Bed* : '.($data[$i]['extra_bed']).' Bed(s)'
.' 
');
            }

            $hotel_cost = 0;
            $tax_amount = 0;
            $total_cost = 0;
            $option = $costDetails[$index]['option'];
            $data = $costDetails[$index]['costing'];

            $service_tax_amount = 0;
            if($data['tax_amount'] !== 0.00 && ($data['tax_amount']) !== ''){
                $service_tax_subtotal1 = explode(',',$data['tax_amount']);
                for($j=0;$j<sizeof($service_tax_subtotal1);$j++){
                    $service_tax = explode(':',$service_tax_subtotal1[$j]);
                    $service_tax_amount += floatval($service_tax[2]);
                }
            }
            ////////////////////Markup Rules
            $markupservice_tax_amount = 0;
            if($data['markup_tax'] !== 0.00 && $data['markup_tax'] !== ""){
                $service_tax_markup1 = explode(',',$data['markup_tax']);
                for($j=0;$j<sizeof($service_tax_markup1);$j++){
                    $service_tax = explode(':',$service_tax_markup1[$j]);
                    $markupservice_tax_amount += floatval($service_tax[2]);
                }
            }
            $hotel_cost += $data['hotel_cost']+$data['service_charge']+$data['markup_cost']+$data['roundoff'];
            $tax_amount += $service_tax_amount + $markupservice_tax_amount;
            $total_cost += $data['total_amount'];

            //Currency conversion
            $hotel_cost_s = currency_conversion($currency,$sq_hotel['currency_code'],$hotel_cost);
            $total_cost_s = currency_conversion($currency,$sq_hotel['currency_code'],$total_cost);
            $tax_amount_s = currency_conversion($currency,$sq_hotel['currency_code'],$tax_amount);
            $tcs_amount = currency_conversion($currency,$sq_hotel['currency_code'],$data['tcs_amnt']);

$whatsapp_msg .= rawurlencode('
*Costing for Option* : '.$option.' 
*Total Cost* : '.$hotel_cost_s.'
*Tax Amount* : '.$tax_amount_s.'
*TCS* : '.$tcs_amount.' 
*Quotation Cost* : '.$total_cost_s
.'

');
        }

    $whatsapp_msg .= rawurlencode('Please contact for more details : '.$app_name.' '.$contact.'
Thank you.');
    $link = 'https://web.whatsapp.com/send?phone='.$enquiryDetails['country_code'].$enquiryDetails['whatsapp_no'].'&text='.$whatsapp_msg;
    echo $link;
}

}
?>
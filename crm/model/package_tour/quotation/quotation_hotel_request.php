<?php
class quotation_hotel_request{

    public function send_common(){
        
        $hotel_status_arr1 = isset($_POST['hotel_status_arr']) ? json_encode($_POST['hotel_status_arr']) : [];
        $hotel_entry_arr = isset($_POST['hotel_entry_arr']) ? $_POST['hotel_entry_arr'] : [];
        $hotel_status_arr = json_decode($hotel_status_arr1);
        $hotel_status_arr1 = json_decode($hotel_status_arr1);

        for($i=0;$i<sizeof($hotel_entry_arr);$i++){
            
            $id = $hotel_entry_arr[$i];

            $hotel_status_arr1[$i] = json_encode($hotel_status_arr1[$i]);
            $mail_sent = $hotel_status_arr[$i]->mail_sent;
            $email_id = $hotel_status_arr[$i]->email_id;
            $availability = $hotel_status_arr[$i]->availability;

            $sq_request = mysqli_fetch_assoc(mysqlQuery("select request_sent,mail_sent from package_tour_quotation_hotel_entries where id='$id'"));
            $request_sent = ($sq_request['request_sent'] == 0 && $mail_sent == 'true') ? 1 : $sq_request['request_sent'];
            
            $sq_quotation = mysqlQuery("update package_tour_quotation_hotel_entries set availability = '$hotel_status_arr1[$i]',request_sent='$request_sent' where id='$id'");
            if($sq_request['mail_sent'] == 0 && $mail_sent == 'true' && $availability == ''){
                
                $this->request_mail_toSupplier($id,$email_id);
                $sq_quotation = mysqlQuery("update package_tour_quotation_hotel_entries set mail_sent='1' where id='$id'");
            }
            // For optional hotels
            $option_hotel_arr = isset($hotel_status_arr[$i]->option_hotel_arr) ? $hotel_status_arr[$i]->option_hotel_arr : [];
            
            for($j=0;$j<sizeof($option_hotel_arr);$j++){
                $availability_op = $option_hotel_arr[$j]->availability;
                $mail_sent_op = $option_hotel_arr[$j]->mail_sent;
                $email_id_op = $option_hotel_arr[$j]->email_id;
                $hotel_id = $option_hotel_arr[$j]->hotel_id;
                if($mail_sent_op == 'true' && $availability_op == ''){
                    $this->request_mail_toSupplier($id,$email_id_op,$hotel_id);
                }
            }

            if(!$sq_quotation){
                echo 'Hotel Availability Request to supplier(s) not sent!';
                exit;
            }
        }
        echo 'Hotel Availability Request to supplier(s) sent successfully!';

    }
    function request_mail_toSupplier($id,$email_id,$hotel_id = ''){

        global $theme_color,$model,$app_name;

        $sq_hotel_qout = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_hotel_entries where id='$id'"));

        $sq_pacakge_qout = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$sq_hotel_qout[quotation_id]'"));
        $quotation_date = $sq_pacakge_qout['quotation_date'];
        $yr = explode("-", $quotation_date);
        $year = $yr[0];
        $quotation_id = get_quotation_id($sq_pacakge_qout['quotation_id'],$year);
        $supplier_id = ($hotel_id != '') ? $hotel_id : $sq_hotel_qout['hotel_name'];
        $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$supplier_id'"));

        $sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$sq_hotel_qout[city_name]'"));
        $decode_id = base64_encode($id);

        $content = '
            <tr>
                <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
                <tr><td style="text-align:left;border: 1px solid #888888;">Hotel Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.urldecode($sq_hotel['hotel_name']).'('.$sq_city['city_name'].')'.'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> CheckIn Date</td>   <td style="text-align:left;border: 1px solid #888888;" >'.date('d-m-Y', strtotime($sq_hotel_qout['check_in'])).'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> CheckOut Date</td>   <td style="text-align:left;border: 1px solid #888888;">'.date('d-m-Y', strtotime($sq_hotel_qout['check_out'])).'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Room Category</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_hotel_qout['room_category'].'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Total Rooms(Extra Bed)</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_hotel_qout['total_rooms'].'('.$sq_hotel_qout['extra_bed'].')'.'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Adult(s)</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_pacakge_qout['total_adult'].'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Child(ren)</td>   <td style="text-align:left;border: 1px solid #888888;">'.($sq_pacakge_qout['children_without_bed']+$sq_pacakge_qout['children_with_bed']).'</td></tr>
                </table>
            </tr>
            <tr>
                <td>
                <table style="padding:0 30px; margin:0px auto; margin-top:10px">
                    <tr>
                        <td colspan="2">
                        <a style="font-weight:500;font-size:14px;display:block;color:#ffffff;background:'.$theme_color.';text-decoration:none;padding:5px 10px;border-radius:25px;width:120px;text-align:center" href="'.BASE_URL.'view/package_booking/quotation/home/hotel_availability/supplier_response.php?request_id='.$decode_id.'&hotel_flag='.$hotel_id.'&hotel_id='.$supplier_id.'" target="_blank">Check Availability</a>
                        </td>
                    </tr> 
                </table>
                </td>
            </tr>';
        $subject = "Hotel Availability Check Request: ".$app_name." (Quotation ID: ".$quotation_id.")";
        $model->app_email_send('117',urldecode($sq_hotel['hotel_name']),$email_id, $content,$subject,'1');
    }
    function supplier_response(){

        $request_id = $_POST['request_id'];
        $response_arr = json_encode($_POST['response_arr']);

        $sq_res = mysqlQuery("update package_tour_quotation_hotel_entries set availability = '$response_arr' where id='$request_id'");
        if($sq_res){
            $this->supplier_response_toAdmin($request_id);
            echo 'Response saved successfully.Thank you!';
        }else{
            echo 'error--Response not saved successfully!';
        }
    }

    function supplier_response_toAdmin($request_id){

        $hotel_flag = $_POST['hotel_flag'];
        global $theme_color,$model;
        $content = '';
        $sq_hotel_qout = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_hotel_entries where id='$request_id'"));

        $sq_pacakge_qout = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_quotation_master where quotation_id='$sq_hotel_qout[quotation_id]'"));
        $quotation_date = $sq_pacakge_qout['quotation_date'];
        $yr = explode("-", $quotation_date);
        $year = $yr[0];
        $quotation_id = get_quotation_id($sq_pacakge_qout['quotation_id'],$year);
        
        $supplier_id = ($hotel_flag != '') ? $hotel_flag : $sq_hotel_qout['hotel_name'];
        $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$supplier_id'"));

        $availability_detailss1 = !isset($sq_hotel_qout['availability']) ? [] : $sq_hotel_qout['availability'];
        $availability_details = isset($availability_detailss1) && ($availability_detailss1 !== null) ? json_decode($availability_detailss1) : [];
        if($hotel_flag != ''){
            $option_hotel_arr = $availability_details->option_hotel_arr;
            for($i = 0;$i< sizeof($option_hotel_arr);$i++){
                if($hotel_flag == $option_hotel_arr[$i]->hotel_id){
                    $h_availability = $option_hotel_arr[$i]->availability;
                    $spec = ($option_hotel_arr[$i]->spec != '') ? $option_hotel_arr[$i]->spec : 'NA';
                }
            }
        }else{
            $h_availability = isset($availability_details->availability) ? $availability_details->availability : '';
            $spec = isset($availability_details->spec) ? $availability_details->spec : 'NA';
        }

        $emp_id = isset($availability_details->emp_id) ? $availability_details->emp_id : 1;
        $sq_city = mysqli_fetch_assoc(mysqlQuery("select * from city_master where city_id='$sq_hotel_qout[city_name]'"));
        $sq_emp = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name,email_id from emp_master where emp_id='$emp_id'"));
        $content .= '
        <tr>
            <td>
            <table style="padding:0 30px; margin:0px auto; margin-top:10px">
                <tr>
                    <td colspan="2">
                        '.$h_availability.'- '.$spec.'</tr> 
                </table>
                </td>
            </tr>';
        $content .= '
            <tr>
                <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
                <tr><td style="text-align:left;border: 1px solid #888888;">Hotel Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.urldecode($sq_hotel['hotel_name']).'('.$sq_city['city_name'].')'.'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> CheckIn Date</td>   <td style="text-align:left;border: 1px solid #888888;" >'.date('d-m-Y', strtotime($sq_hotel_qout['check_in'])).'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> CheckOut Date</td>   <td style="text-align:left;border: 1px solid #888888;">'.date('d-m-Y', strtotime($sq_hotel_qout['check_out'])).'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Room Category</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_hotel_qout['room_category'].'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Total Rooms(Extra Bed)</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_hotel_qout['total_rooms'].'('.$sq_hotel_qout['extra_bed'].')'.'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Adult(s)</td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_pacakge_qout['total_adult'].'</td></tr>
                <tr><td style="text-align:left;border: 1px solid #888888;"> Child(ren)</td>   <td style="text-align:left;border: 1px solid #888888;">'.($sq_pacakge_qout['children_without_bed']+$sq_pacakge_qout['children_with_bed']).'</td></tr>
                </table>
            </tr>';
            $content .= '
                <tr>
                    <td>
                    <table style="padding:0 30px; margin:0px auto; margin-top:10px">
                        <tr>
                            <td colspan="2">
                            <a style="font-weight:500;font-size:14px;display:block;color:#ffffff;background:'.$theme_color.';text-decoration:none;padding:5px 10px;border-radius:25px;width:120px;text-align:center" href="'.BASE_URL.'index.php" target="_blank">Login</a>
                            </td>
                        </tr> 
                    </table>
                    </td>
                </tr>
            ';
    $subject = "Status : Hotel Availability: ".$sq_hotel['hotel_name']."(Request ID: ".$request_id." & Quotation ID: ".$quotation_id.")";
    $model->app_email_send('119',$sq_emp['first_name'].' '.$sq_emp['last_name'],$sq_emp['email_id'], $content,$subject,'1');
}
}
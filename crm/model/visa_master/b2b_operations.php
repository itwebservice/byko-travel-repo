<?php
class b2b_operations{

    function search_session_save(){
        $_SESSION['visa_array'] = json_encode($_POST['visa_array']);
    }

    function enquiry_save(){

        global $app_name,$app_email_id,$model;
        $enq_data = $_POST['enq_data'];
        $country_name = $enq_data[0]['country_name'];
        $pax = $enq_data[0]['pax'];
        $visa_type = $enq_data[0]['visa_type'];
        $agent_flag = $enq_data[0]['agent_flag'];
        $user_id = $enq_data[0]['user_id'];
        $register_id = $enq_data[0]['register_id'];

        $sq_query = mysqli_fetch_assoc(mysqlQuery("SELECT company_name,cp_first_name,cp_last_name,email_id,mobile_no,city FROM `b2b_registration` where register_id='$register_id'"));
        $company_name = $sq_query['company_name'];
        if($agent_flag == '1'){
            $profile_user_name = $sq_query['company_name'].' ('.$sq_query['cp_first_name'].' '.$sq_query['cp_last_name'].')';
            $profile_email_id = $sq_query['email_id'];
            $profile_mobile_no = $sq_query['mobile_no'];

            $sq_city = mysqli_fetch_assoc(mysqlQuery("SELECT city_name FROM `city_master` where city_id='$sq_query[city]'"));
            $city = $sq_city['city_name'];
        }else{
            $sq_user = mysqli_fetch_assoc(mysqlQuery("SELECT full_name,email_id,mobile_no FROM `b2b_users` where id='$user_id'"));
            $profile_user_name = $sq_query['company_name'].' ('.$sq_user['full_name'].')';
            $profile_email_id = $sq_user['email_id'];
            $profile_mobile_no = $sq_user['mobile_no'];
            $city = '';
        }

        //Enquiry save to crm
        $sq_fin = mysqli_fetch_assoc(mysqlQuery("select financial_year_id from financial_year where active_flag = 'Active' order by financial_year_id desc"));
        $financial_year_id = $sq_fin['financial_year_id'];
        $enquiry_date = date("Y-m-d");
        $followup_date = date("Y-m-d H:i");

        $enquiry_content = array();
        array_push($enquiry_content,array("name"=>"visa_country_name","value"=>$country_name),array("name"=>"visa_type","value"=>"$visa_type"),array("name"=>"total_adult","value"=>$pax),array("name"=>"total_children","value"=>0),array("name"=>"total_infant","value"=>0),array("name"=>"total_members","value"=>$pax),array("name"=>"budget","value"=>""));

        $enquiry_content = json_encode($enquiry_content);
        //Save
        $sq_max_id = mysqli_fetch_assoc(mysqlQuery("select max(enquiry_id) as max from enquiry_master"));
        $enquiry_id = $sq_max_id['max']+1;
        $qq = "insert into enquiry_master (enquiry_id, login_id,branch_admin_id,financial_year_id, enquiry_type,enquiry, name, mobile_no, landline_no, country_code,email_id,location, assigned_emp_id, enquiry_specification, enquiry_date, followup_date, reference_id, enquiry_content,customer_name ) values ('$enquiry_id', '1', '1','$financial_year_id','Visa', 'Strong', '$company_name', '$profile_mobile_no', '$profile_mobile_no', '','$profile_email_id','$city', '1', '', '$enquiry_date', '$followup_date', '2', '$enquiry_content','')";
        mysqlQuery($qq);
        //Followup save 
        $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from enquiry_master_entries"));
        $entry_id = $sq_max['max'] + 1;
        mysqlQuery("insert into enquiry_master_entries(entry_id, enquiry_id, followup_reply,  followup_status,  followup_type, followup_date, followup_stage, created_at) values('$entry_id', '$enquiry_id', '', 'Active','', '$followup_date','Strong', '$enquiry_date')");
        mysqlQuery("update enquiry_master set entry_id='$entry_id' where enquiry_id='$enquiry_id'");

        $content = '
            <tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
                <tr>
                    <td style="text-align:left;" colspan=2><b>Dear Admin,</b></td> 
                </tr>
                <tr>
                    <td style="text-align:left;" colspan=2><b>New enquiry is generated from b2b portal with below details.</b></td> 
                </tr>
            </table>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
            <tr><td style="text-align:left;border: 1px solid #888888;">Enquiry For</td>   <td style="text-align:left;border: 1px solid #888888;">Visa</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;">Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.$profile_user_name.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;">Email ID</td>   <td style="text-align:left;border: 1px solid #888888;">'.$profile_email_id.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;">Mobile No</td>   <td style="text-align:left;border: 1px solid #888888;" >'.$profile_mobile_no.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;">Country Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.$country_name.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;">Visa Type</td>   <td style="text-align:left;border: 1px solid #888888;">'.$visa_type.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;">Passenger(s)</td>   <td style="text-align:left;border: 1px solid #888888;">'.$pax.'</td></tr>
            </table>
            </tr>';

        //Mail to admin
        $subject = 'New B2B Agent Enquiry for Visa ('.date('d-m-Y').')'. ' : '.$profile_user_name;
        global $model;
        $model->app_email_master($app_email_id, $content, $subject,'1');
        echo 'Enquiry has been sent successfully!';
        exit;
    }
}
?>
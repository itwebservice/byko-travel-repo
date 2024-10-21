<?php
class enquiry_master{

    public function form_send(){
        
        global $app_name,$app_email_id,$model;
        $enq_first_name = $_POST['enq_first_name'];
        $enq_last_name = $_POST['enq_last_name'];
        $enq_email = $_POST['enq_email'];
        $enq_contact = ($_POST['enq_contact'] != '') ? $_POST['enq_contact'] : 'NA';
        $enq_message = $_POST['enq_message'];
        
        $content = '
            <tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;margin: 0px auto;margin-top:20px; min-width: 100%;text-align: left;" role="presentation">
            <tr>
                <td colspan=2><b>Dear Admin,</b></td> 
            </tr>
            <tr>
                <td colspan=2><b>New enquiry is generated from b2b portal with below details.</b></td> 
            </tr>
            </table>
            <tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
            <tr><td style="text-align:left;border: 1px solid #888888;">Name</td>   <td style="text-align:left;border: 1px solid #888888;">'.$enq_first_name.' '.$enq_last_name.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;"> Email ID</td>   <td style="text-align:left;border: 1px solid #888888;">'.$enq_email.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;"> Contact</td>   <td style="text-align:left;border: 1px solid #888888;" >'.$enq_contact.'</td></tr>
            <tr><td style="text-align:left;border: 1px solid #888888;"> Message</td>   <td style="text-align:left;border: 1px solid #888888;" >'.$enq_message.'</td></tr>
            </table>
            </tr>';

        //To Admin
        $subject = 'Message from B2B Portal ('.$enq_first_name.' '.$enq_last_name.')' ;
        $model->app_email_send('','Admin',$app_email_id, $content,$subject,'1');

        //To customer
        $subject = 'Enquiry Acknowledgment from '.$app_name;
        $model->app_email_send('104',$enq_first_name,$enq_email, '',$subject,'1');
        echo 'Mail sent successfully!';
    }

}
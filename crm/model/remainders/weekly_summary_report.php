<?php
include_once('../model.php');
global $app_email_id, $app_name, $app_contact_no, $admin_logo_url, $app_website;
global $mail_em_style, $mail_font_family, $mail_strong_style, $mail_color,$theme_color;

$cur_date = date('Y-m-d');
$cur_date1 = date('Y-m-d H:i');
$time = date("H", strtotime($cur_date1));

if($time=='21'){
    $email_content = '
    <tr>
        <td>
            <table style="padding:0 30px; margin:0px auto; margin-top:10px">
                <tr>
                <td colspan="2">
                    <a style="background: '.$theme_color.';color: #fff; border:aliceblue;width:auto;text-decoration: none;  display: block;text-transform: uppercase;padding: 0 10px;font-weight: 600;" href="'.BASE_URL.'model/remainders/weekly_summary_html.php?cur_date='.$cur_date.'">Click here to view daily summary report</a> 
                </td>
                </tr>
            </table>
        </td>
    </tr>';
    global $model;
    $sq_count = mysqli_num_rows(mysqlQuery("SELECT * from remainder_status where remainder_name = 'week_sum_remainder' and date='$cur_date' and status='Done'"));
    if($sq_count == 0){
        $subject = 'Daily report (Date : '.get_date_user($cur_date).' ).';
        $model->app_email_send('93',"Admin",$app_email_id, $email_content,$subject);

        $row=mysqlQuery("SELECT max(id) as max from remainder_status");
        $value=mysqli_fetch_assoc($row);
        $max = $value['max']+1;
        $sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','week_sum_remainder','$cur_date','Done')");
    }
}
?>
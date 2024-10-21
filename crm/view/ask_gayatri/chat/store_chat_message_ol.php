<?php
include "../../../model/model.php";

$client_id = $_POST['client_id'];
$user_side = $_POST['user_side'];
$user_side_id = $_POST['user_side_id'];
$message = mysqlREString($_POST['message']);
$baseimage = $_POST['baseimage'];
$reply_id = $_POST['reply_id'];
$is_url = !empty($_POST['is_url']) ? mysqlREString($_POST['is_url']) : 0;
$url = !empty($_POST['url']) ? $_POST['url'] : "";
function check_dir($current_dir, $type)
{	 	
	if(!is_dir($current_dir."/".$type))
	{
		mkdir($current_dir."/".$type);		
	}	
	$current_dir = $current_dir."/".$type;
		return $current_dir;	
}

if(!empty($_POST['baseimage']))
{
    $year = date("Y");
 $month = date("M");
 $day = date("d");
 $timestamp = date('U');
 $year_status = false;
 $month_status = false;
 $day_status = false;
    $current_dir = '../../../uploads';
    $current_dir = check_dir($current_dir ,'Chat_Gayatri');
    $current_dir = check_dir($current_dir , $year);
    $current_dir = check_dir($current_dir , $month);
    $current_dir = check_dir($current_dir , $day);
    $current_dir = check_dir($current_dir , $timestamp);

        $img =$_POST['baseimage'];
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $filename=$current_dir.'/'.uniqid().".png";
        file_put_contents($filename, $data);
        $newUrl = str_replace('../', '', $filename);
        $url=$newUrl;
         $ext="png";
}
else
{

$path_info = pathinfo($url);

$ext = !empty($_POST['url']) ? $path_info['extension'] : "";
}

//$path_info = pathinfo($url);

//$ext = !empty($_POST['url']) ? $path_info['extension'] : "";
$client_name = !empty($_POST['client_name']) ? mysqlREString($_POST['client_name']) : "";

$sessionDetail = getSessionDetails($client_id);

if(empty($sessionDetail))
{
    mysqlQuery("INSERT INTO `chat_sessions`(`client_id`, `status`, `created_at`) VALUES ('$client_id','0',CURRENT_TIMESTAMP)");
}

$sessionDetail = getSessionDetails($client_id);

// if(!empty($reply_id))
// {
//     // $message = "<small style='color:#fff;'>Reply</small> <br>";
//     // $message .= dbQuerySingle("select message from chat_messages where id=$reply_id")['message'];
// }


$message = mysqlREString($message);

$add = mysqlQuery("INSERT INTO `chat_messages`(`chat_id`, `user_side`, `message`, `client_id`, `user_side_id`, `is_read`, `reply_id`, `created_at`, `is_deleted`,`is_url`,`url`,`client_name`,`extension`) VALUES ('$sessionDetail[id]','$user_side','$message','$client_id','$user_side_id','0','$reply_id',CURRENT_TIMESTAMP,'0',$is_url,'$url','$client_name','$ext')");

$data = mysqli_fetch_array(mysqlQuery("select * from questions where question='$message'"));

if($data)
{
    $answer=$data['answer'];
    
    if($user_side=='Support')
    {
        $answer_user_side="Client";
    }
    else
    {
        $answer_user_side='Support';
    }
    
    $add = mysqlQuery("INSERT INTO `chat_messages`(`chat_id`, `user_side`, `message`, `client_id`, `user_side_id`, `is_read`, `reply_id`, `created_at`, `is_deleted`,`is_url`,`url`,`client_name`,`extension`) VALUES ('$sessionDetail[id]','$answer_user_side','$answer','$client_id','$user_side_id','0','$reply_id',CURRENT_TIMESTAMP,'0',$is_url,'$url','$client_name','$ext')");
}

if($user_side=="Client")
{
    $app_settings=dbQuery("SELECT * FROM `app_settings` where support_active=0");
    date_default_timezone_set('Asia/Calcutta');
    $t=date('D, d-M-y');
    $day=date("D",strtotime($t));
    if($day=='Sun')
    {
        $user_side="Support";
        $message="Today is the weekend off. Please Contact After Sunday.";
        $add = mysqlQuery("INSERT INTO `chat_messages`(`chat_id`, `user_side`, `message`, `client_id`, `user_side_id`, `is_read`, `reply_id`, `created_at`, `is_deleted`,`is_url`,`url`,`client_name`,`extension`) VALUES ('$sessionDetail[id]','$user_side','$message','$client_id','$user_side_id','0','$reply_id',CURRENT_TIMESTAMP,'0',$is_url,'$url','$client_name','$ext')");
    }
    elseif($app_settings)
    {
        $user_side="Support";
        $message="Support on Holiday Today. Please Contact After Holiday.";
        $add = mysqlQuery("INSERT INTO `chat_messages`(`chat_id`, `user_side`, `message`, `client_id`, `user_side_id`, `is_read`, `reply_id`, `created_at`, `is_deleted`,`is_url`,`url`,`client_name`,`extension`) VALUES ('$sessionDetail[id]','$user_side','$message','$client_id','$user_side_id','0','$reply_id',CURRENT_TIMESTAMP,'0',$is_url,'$url','$client_name','$ext')");
    }
}

$lastId = getLastInsertedId('id','chat_messages');
$members = dbQuery("SELECT emp_id FROM `emp_master` where role_id IN(1,16)");

foreach ($members as $item) {
    mysqlQuery("INSERT INTO `chat_message_read`(`message_id`, `emp_id`, `is_read_message`) VALUES ('$lastId','$item[emp_id]',0)");
}
mysqlQuery("INSERT INTO `chat_message_read`(`message_id`, `emp_id`, `is_read_message`) VALUES ('$lastId','0',0)");



function getSessionDetails($client_id)
{
    $queryChat = mysqlQuery("select * from chat_sessions where client_id='$client_id' and status=0");
    $chatSession = mysqli_num_rows($queryChat);
    if(empty($chatSession))
    {
        return false;
    }
    $chatSession = mysqli_fetch_array($queryChat);
    return $chatSession;
}
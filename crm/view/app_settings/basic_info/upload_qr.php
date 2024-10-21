<?php

 $year = date("Y");

 $month = date("M");

 $day = date("d");

 $timestamp = date('U');

 $year_status = false;

 $month_status = false;

 $day_status = false;

 

 function check_dir($current_dir, $type)

{	 	

	if(!is_dir($current_dir."/".$type))

	{

		mkdir($current_dir."/".$type);		

	}	

	$current_dir = $current_dir."/".$type."/";

		return $current_dir;	

}



$current_dir = '../../../uploads/';

$current_dir = check_dir($current_dir ,'company_QR');

$current_dir = check_dir($current_dir , $year);

$current_dir = check_dir($current_dir , $month);

$current_dir = check_dir($current_dir , $day);

$current_dir = check_dir($current_dir , $timestamp);

$file_name = str_replace(' ','_',basename($_FILES['uploadfileQR']['name']));
$file = $current_dir.$file_name; 

if($_FILES['uploadfileQR']['size']<=100000){
	if (move_uploaded_file($_FILES['uploadfileQR']['tmp_name'], $file)) { 
	echo $file; 
	} else{
		echo "error";
	}
}
else{
	echo "error1";
}

?>
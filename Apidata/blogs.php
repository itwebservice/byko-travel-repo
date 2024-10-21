<?php 
error_reporting(E_ALL);
include '../config.php'; 

 function filterImgUrl($imgUrlMain)
    {
        if(empty($imgUrlMain))
        {
            return 0;        }
        $url = $imgUrlMain;
        $pos = strstr($url, 'uploads');
        if ($pos != false) {
            $newUrl1 = preg_replace('/(\/+)/', '/', $imgUrlMain);
            $newUrl = str_replace('../', '', $newUrl1);
        } else {
            $newUrl =  $imgUrlMain;
        }
        return $newUrl;
    }
 
 
        
        $query = mysqlQuery("SELECT * FROM b2c_blogs where active_flag=0");
      $blogs=array();
	while($data = mysqli_fetch_array($query)){
	    $blogs[]= array(
	        'entry_id'=>$data['entry_id'],
	        'title'=>$data['title'],
	        'description'=>$data['description'],
	        'image'=>$data['image'],
	         'active_flag' =>$data['active_flag'],
	        'img_filter'=>filterImgUrl($data['image'])
	        
	      
	    );
	}


    //$activities = ExcursionMasterTariff::all();
     
        
       echo json_encode($blogs);    
?>
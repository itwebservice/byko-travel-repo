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
 
 
        
        $query = mysqlQuery("SELECT * FROM b2c_testimonials");
      $testimonials=array();
	while($data = mysqli_fetch_array($query)){
	    $testimonials[]= array(
	        'entry_id'=>$data['entry_id'],
	        'name'=>$data['name'],
	        'designation'=>$data['designation'],
	        'testm'=>$data['testm'],
	        'image'=>$data['image'],
	        'filter_img'=>filterImgUrl($data['image'])
	        
	      
	    );
	}


    //$activities = ExcursionMasterTariff::all();
     
        
       echo json_encode($testimonials);    
?>
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
 
 $setData = mysqli_fetch_assoc(mysqlQuery("SELECT popular_activities FROM b2c_settings LIMIT 1"));
 
 $popularAct = json_decode($setData['popular_activities']);
 
  if (empty($popularAct)) {
           echo json_encode(array()); 
        }
        
        $query = mysqlQuery("SELECT * FROM excursion_master_tariff where active_flag='Active'");
      $activities=array();
	while($data = $query->fetch_assoc()){
	    $activities[]=$data;
	}


    //$activities = ExcursionMasterTariff::all();
        $selectedActivities = array();
        $imagesdata=array();
        foreach ($activities as $main) {
            foreach ($popularAct as $act) {

                if ($act->exc_id == $main['entry_id']) {
                    //$basic = ExcursionMasterTariffBasics::where('exc_id', $main['entry_id'])->first();
                    
                      $basic = mysqli_fetch_object(mysqlQuery("SELECT * FROM excursion_master_tariff_basics where exc_id='".$main['entry_id']."' ORDER BY entry_id DESC LIMIT 1"));
                    
                    //$images = ExcursionMasterImages::where('exc_id', $main['entry_id'])->get();
                    
                     
                     
                     
                     $resultimage=mysqlQuery("SELECT * FROM  excursion_master_images where exc_id ='".$main['entry_id']."'");
	while($dataactivity = mysqli_fetch_array($resultimage)){
	   
	    $imagesdata[]=array(
	        'entry_id'=>$dataactivity['entry_id'],
	        'exc_id'=>$dataactivity['exc_id'],
	        'image_url'=>$dataactivity['image_url'],
	        
	        ); }
	        
	        //print_r($imagesdata); die;
	        
	         $getqueryimage = mysqli_fetch_object(mysqlQuery("SELECT image_url FROM excursion_master_images where exc_id='".$main['entry_id']."' ORDER BY entry_id DESC LIMIT 1"));
                   
                    
                    $main['basics'] = $basic;
                    $main['images'] = $imagesdata;
                    $imgUrl = (!empty($getqueryimage->image_url)) ? $getqueryimage->image_url : 'images/activity_default.png';
                    $main['main_img_url'] = filterImgUrl($imgUrl);
                    
                    array_push($selectedActivities, $main);
                }
            }
        }
        
       echo json_encode($selectedActivities);    
?>
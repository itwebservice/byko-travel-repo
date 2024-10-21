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

 $bchotels = mysqli_fetch_assoc(mysqlQuery("SELECT popular_hotels FROM b2c_settings LIMIT 1"));
 
 $b2chotels = json_decode($bchotels['popular_hotels']);
 
  if (empty($b2chotels)) {
           echo json_encode(array()); 
        }
        
        
        $query = mysqlQuery("SELECT * FROM hotel_master where active_flag='Active'");
        
       
	$popularHotels=array();	
	while($data = $query->fetch_assoc()){
	    $popularHotels[]=$data;
	}
      
        
        $selectedHotel = array();
        $hoteldata=array();
        $citydata=array();
        foreach ($popularHotels as $hotel) {
            foreach ($b2chotels as $b2chotel) {
                if ($b2chotel->hotel_id == $hotel['hotel_id']) {
                     
                     $getqueryimage = mysqli_fetch_object(mysqlQuery("SELECT hotel_pic_url FROM hotel_vendor_images_entries where hotel_id='".$hotel['hotel_id']."' ORDER BY id DESC LIMIT 1"));
                    $hotel['main_img'] = (!empty($getqueryimage->hotel_pic_url)) ? filterImgUrl($getqueryimage->hotel_pic_url) : 'images/hotel_general.png';
                   
            
            
            	/// get  hotel_master data
			//echo "SELECT * FROM  hotel_vendor_images_entries where hotel_id ='".$hotel['hotel_id']."'";
$resulthotel=mysqlQuery("SELECT * FROM  hotel_vendor_images_entries where hotel_id ='".$hotel['hotel_id']."'");
	while($datahotel = mysqli_fetch_array($resulthotel)){
	   
	    $hoteldata=array(
	        'id'=>$datahotel['id'],
	        'hotel_id'=>$datahotel['hotel_id'],
	        'hotel_pic_url'=>$datahotel['hotel_pic_url'],
	        
	        ); }
	        
	        $hotel['hotel_image'] = $hoteldata;
	        
					// end hotel_master data
				
		
			/// get  city_master data
			//echo "SELECT * FROM  hotel_vendor_images_entries where hotel_id ='".$hotel['hotel_id']."'";
$resulthotel=mysqlQuery("SELECT * FROM  city_master where city_id ='".$hotel['city_id']."'");
	while($datacity = mysqli_fetch_array($resulthotel)){
	   
	    $citydata=array(
	        'city_id'=>$datacity['city_id'],
	        'city_name'=>$datacity['city_name'],
	        'active_flag'=>$datacity['active_flag'],
	        
	        ); }
	        
	        $hotel['hotel_city'] = $citydata;
	        
					// end city_master data		
				
            array_push($selectedHotel, $hotel);
                }
            } 
        }
       echo json_encode($selectedHotel);  
?>
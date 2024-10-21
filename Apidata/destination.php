<?php 
error_reporting(E_ALL);
include '../config.php'; 

$result=mysqlQuery("SELECT custom_package_master.*,destination_master.dest_name,gallary_master.entry_id,gallary_master.description,gallary_master.image_url,custom_package_images.image_entry_id,custom_package_images.image_url,custom_package_tariff.hotel_type,custom_package_tariff.min_pax,custom_package_tariff.max_pax,custom_package_tariff.from_date,custom_package_tariff.to_date,custom_package_tariff.badult,custom_package_tariff.bcwb,custom_package_tariff.bcwob,custom_package_tariff.binfant,custom_package_tariff.cadult,custom_package_tariff.ccwb,custom_package_tariff.ccwob,custom_package_tariff.cinfant,custom_package_tariff.bextra,custom_package_tariff.cextra  FROM custom_package_master LEFT JOIN destination_master ON custom_package_master.dest_id = destination_master.dest_id LEFT JOIN gallary_master ON destination_master.dest_id = gallary_master.dest_id LEFT JOIN custom_package_images ON custom_package_master.package_id = custom_package_images.package_id LEFT JOIN custom_package_tariff ON custom_package_master.package_id = custom_package_tariff.package_id GROUP BY custom_package_master.package_id");
	$packageData=array();	
	while($data = mysqli_fetch_array($result)){
	    $packageData[]=array('package_id'=>$data['package_id'],
	        'dest_id'=>$data['dest_id'],
	        'package_code'=>$data['package_code'],
	        'package_name'=>$data['package_name'],
	        'total_days'=>$data['total_days'],
	        'total_nights'=>$data['total_nights'],
	        'adult_cost'=>$data['adult_cost'],
	        'child_cost'=>$data['child_cost'],
	        'infant_cost'=>$data['infant_cost'],
	        'child_with'=>$data['child_with'],
	        'child_without'=>$data['child_without'],
	        'extra_bed'=>$data['extra_bed'],
	        'tour_cost'=>$data['tour_cost'],
	        'markup_cost'=>$data['markup_cost'],
	        'taxation_id'=>$data['taxation_id'],
	        'service_tax'=>$data['service_tax'],
	        'service_tax_subtotal'=>$data['service_tax_subtotal'],
	        'total_tour_cost'=>$data['total_tour_cost'],
	        'inclusions'=>$data['inclusions'],
	        'exclusions'=>$data['exclusions'],
	        'status'=>$data['status'],
	        'created_at'=>$data['created_at'],
	        'clone'=>$data['clone'],
	        'tour_type'=>$data['tour_type'],
	        'currency_id'=>$data['currency_id'],
	        'taxation'=>$data['taxation'],
	        'update_flag'=>$data['update_flag'],
	        'note'=>$data['note'],
	        'dest_image'=>$data['dest_image'],
	        
	    );
			
	
	}	
	//echo json_encode($packageData);
//die;	
    
		  $setData =mysqli_fetch_object(mysqlQuery("SELECT * FROM b2c_settings LIMIT 1"));
        $popularDest = json_decode($setData->popular_dest);
	//echo	json_encode($packageData);die;
		//print_r($packageData); die;	
		
        if (empty($popularDest)) {
             echo json_encode(array());
        } 
        
			$popularDestData = array();
			$destinationdata=array();
			  $verifyDest = array();
			  $gallerydata=array();
			  foreach ($packageData as $data1) {
              	/// get destiantion data
			
$resultdest=mysqlQuery("SELECT * FROM destination_master where dest_id='".$data1['dest_id']."'");
	while($datadest = mysqli_fetch_array($resultdest)){
	    
	    /*gallery data*/
	      	/// get destiantion data
			
$resultgallery=mysqlQuery("SELECT * FROM gallary_master where dest_id='".$datadest['dest_id']."'");
	while($datagall = mysqli_fetch_array($resultgallery)){
	    $gallerydata[]=array(
	        'entry_id'=>$datagall['entry_id'],
	        'dest_id'=>$datagall['dest_id'],
	        'description'=>$datagall['description'],
	        'image_url'=>$datagall['image_url']
	        ); } 
	    /*end gallery data*/
	    
	    $destinationdata=array(
	        'dest_id'=>$datadest['dest_id'],
	        'dest_name'=>$datadest['dest_name'],
	        'status'=>$datadest['status'],
	        'gallery_images'=>$gallerydata
	        ); }
	        
	        $data1['destination'] = $destinationdata;
	        
					// end detiantion data
 
            $temp = $data1['destination'];
            
           
            // MySQL query
         //echo $temp['dest_id'].'<br>';
$result1 = mysqlQuery("SELECT COUNT(*) AS package_count
        FROM destination_master AS d
        LEFT JOIN custom_package_master AS cpm ON d.dest_id = cpm.dest_id
        WHERE d.dest_id = '".$temp['dest_id']."' ORDER BY d.dest_id ASC LIMIT 1");
        
        
  
    $row = $result1->fetch_assoc(); 
   //echo $row['package_count']; die;
            $temp['total_packages'] = $row['package_count'];
   //echo $temp['dest_id'].'<br>';
 if (!in_array($temp['dest_id'], $verifyDest)) {
      
                array_push($verifyDest, $temp['dest_id']);
                array_push($popularDestData, $temp);
            }
//die;
 //echo json_encode($packageData); die;
}
//print_r($verifyDest);
//die;
      echo json_encode($popularDestData); 

?>
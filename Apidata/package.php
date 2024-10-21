<?php 
error_reporting(E_ALL);
include '../config.php'; 

$result=mysqlQuery("SELECT custom_package_master.*,destination_master.dest_name,gallary_master.entry_id,gallary_master.description,gallary_master.image_url,custom_package_images.image_entry_id,custom_package_images.image_url,custom_package_tariff.hotel_type,custom_package_tariff.min_pax,custom_package_tariff.max_pax,custom_package_tariff.from_date,custom_package_tariff.to_date,custom_package_tariff.badult,custom_package_tariff.bcwb,custom_package_tariff.bcwob,custom_package_tariff.binfant,custom_package_tariff.cadult,custom_package_tariff.ccwb,custom_package_tariff.ccwob,custom_package_tariff.cinfant,custom_package_tariff.bextra,custom_package_tariff.cextra  FROM custom_package_master LEFT JOIN destination_master ON custom_package_master.dest_id = destination_master.dest_id LEFT JOIN gallary_master ON destination_master.dest_id = gallary_master.dest_id LEFT JOIN custom_package_images ON custom_package_master.package_id = custom_package_images.package_id LEFT JOIN custom_package_tariff ON custom_package_master.package_id = custom_package_tariff.package_id where custom_package_master.status='Active' GROUP BY custom_package_master.package_id");
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
//die;	
    
		  $setData =mysqli_fetch_object(mysqlQuery("SELECT * FROM b2c_settings LIMIT 1"));
        $popularDest = json_decode($setData->popular_dest);
	//echo	json_encode($packageData);die;
		//print_r($packageData); die;	
		
        if (empty($popularDest)) {
             echo json_encode(array());
        } 
        
			$popularDestData = array(); 
        foreach($popularDest as $destination) {
		
		
			//while($data = mysqli_fetch_array($result)){
			$destinationdata=array();
			$gallerydata=array();
				$imagedata=array();
				
				$tariffdata=array();
			
            foreach ($packageData as  $data) {
				//echo $destination->package_id.'<br>';
                if ($destination->package_id == $data['package_id']) {
                  //echo $destination->package_id;
                    $package_name = $data['package_name'];
                    $currency_id = $data['currency_id'];
				    $destid = $data['dest_id'];
                    $currency_logo_d = mysqli_fetch_object(mysqlQuery("SELECT default_currency, currency_code FROM currency_name_master WHERE id = $currency_id LIMIT 1"));
                    $currency_code = $currency_logo_d->currency_code;
                    $package_fname = str_replace(' ', '_', $package_name);
                     $file_name = 'package_tours/' . $package_fname . '-' . $data['package_id'] . '.php';

                    $data['file_name_url'] = $file_name;
                    $data['main_img_url'] = $destination->url;
                    $data['currency'] = $currency_logo_d;
					
					
					/// get destiantion data
			
$resultdest=mysqlQuery("SELECT * FROM destination_master where dest_id=$destid");
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
	        
	        $data['destination'] = $destinationdata;
	        
					// end detiantion data
					
		
			/// get image data
			
$resultimage=mysqlQuery("SELECT * FROM custom_package_images where package_id='".$data['package_id']."'");
	while($dataimage = mysqli_fetch_array($resultimage)){
	  
	    
	    $imagedata[]=array(
	        'image_entry_id'=>$dataimage['image_entry_id'],
	        'image_url'=>$dataimage['image_url'],
	        'package_id'=>$dataimage['package_id'],
	       
	        ); }
		
		$data['images']=$imagedata;
			/// end image data
			
			
			
				/// get tarrif data
			
$resulttariff=mysqlQuery("SELECT * FROM custom_package_tariff where package_id='".$data['package_id']."' ORDER BY entry_id ASC LIMIT 1");
	while($datatariff = mysqli_fetch_array($resulttariff)){
	  
	    
	    $tariffdata=array(
	        'entry_id'=>$datatariff['entry_id'],
	        'package_id'=>$datatariff['package_id'],
	        'hotel_type'=>$datatariff['hotel_type'],
	        'min_pax'=>$datatariff['min_pax'],
	        'from_date'=>$datatariff['from_date'],
	        'to_date'=>$datatariff['to_date'],
	        'badult'=>$datatariff['badult'],
	        'bcwob'=>$datatariff['bcwob'],
	        'binfant'=>$datatariff['binfant'],
	         'cadult'=>$datatariff['cadult'],
	         'ccwb'=>$datatariff['ccwb'],
	         'cinfant'=>$datatariff['cinfant'],
	         'bextra'=>$datatariff['bextra'],
	         'cextra'=>$datatariff['cextra'],
	       
	        ); }
		
		$data['tariff']=$tariffdata;
			/// end tarrif data
			
			
			
					
					
					//echo json_encode($data);
                    array_push($popularDestData, $data);
                }
            }
			
        }
		//die;

        echo json_encode($popularDestData); 

?>
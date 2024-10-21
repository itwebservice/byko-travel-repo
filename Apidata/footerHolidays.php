<?php 
error_reporting(E_ALL);
include '../config.php';

          
          $footer = json_decode(mysqli_fetch_object(mysqlQuery("SELECT footer_holidays FROM b2c_settings LIMIT 1"))->footer_holidays);
        
         
        if (empty($footer)) {
            
              echo json_encode(array());
           
        }
        
        $packageData=array();
        
        // MySQL query
$sql = mysqlQuery("SELECT cpm.*, dm.*, gm.* 
        FROM custom_package_master cpm
        LEFT JOIN destination_master dm ON cpm.package_id = dm.dest_id
        LEFT JOIN gallary_master gm ON dm.dest_id = gm.dest_id GROUP BY cpm.package_id");
        
        
        	while($data1 = mysqli_fetch_array($sql)){
        	    $packageData[]=array(
        	        'package_id'=>$data1['package_id'],
	        'dest_id'=>$data1['dest_id'],
	        'package_code'=>$data1['package_code'],
	        'package_name'=>$data1['package_name'],
	        'total_days'=>$data1['total_days'],
	        'total_nights'=>$data1['total_nights'],
	        'adult_cost'=>$data1['adult_cost'],
	        'child_cost'=>$data1['child_cost'],
	        'infant_cost'=>$data1['infant_cost'],
	        'child_with'=>$data1['child_with'],
	        'child_without'=>$data1['child_without'],
	        'extra_bed'=>$data1['extra_bed'],
	        'tour_cost'=>$data1['tour_cost'],
	        'markup_cost'=>$data1['markup_cost'],
	        'taxation_id'=>$data1['taxation_id'],
	        'service_tax'=>$data1['service_tax'],
	        'service_tax_subtotal'=>$data1['service_tax_subtotal'],
	        'total_tour_cost'=>$data1['total_tour_cost'],
	        'inclusions'=>$data1['inclusions'],
	        'exclusions'=>$data1['exclusions'],
	        'status'=>$data1['status'],
	        'created_at'=>$data1['created_at'],
	        'clone'=>$data1['clone'],
	        'tour_type'=>$data1['tour_type'],
	        'currency_id'=>$data1['currency_id'],
	        'taxation'=>$data1['taxation'],
	        'update_flag'=>$data1['update_flag'],
	        'note'=>$data1['note'],
	        'dest_image'=>$data1['dest_image'],
	        
	    );
		
        	   
        	}
       // $packageData = CustomPackageMaster::with('destination')->get();
        $allData = array();
        $destinationdata=array();
        foreach ($packageData as $data) {
            
            $destid=$data['dest_id'];
            /// get destiantion data
			
$resultdest=mysqlQuery("SELECT * FROM destination_master where dest_id=$destid");
	while($datadest = mysqli_fetch_array($resultdest)){
	    /*gallery data*/
	      	/// get destiantion data
			

	    
	    $destinationdata=array(
	        'dest_id'=>$datadest['dest_id'],
	        'dest_name'=>$datadest['dest_name'],
	        'status'=>$datadest['status'],
	        ); }
	        
	        $data['destination'] = $destinationdata;
	        
					// end detiantion data
            
            
            foreach ($footer as $f) {
                if ($f->package_id == $data['package_id']) {
                    array_push($allData, $data);
                }
            }
        }
         echo json_encode($allData,JSON_PRETTY_PRINT);
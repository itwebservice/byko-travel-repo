<?php
//Group
$budget_s_g = 0;
$enq_strong_g = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Group Booking'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_g)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_g += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_g = 0;
$enq_hot_g= mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Group Booking'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_g)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_g += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_g = 0;
$enq_cold_g = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Group Booking'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_g)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_g += floatval($enquiry_content_arr2['value']);
        }
    }
}
//Package
$budget_s_p = 0;
$enq_strong_p = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Package Booking'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_p)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_p += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_p = 0;
$enq_hot_p= mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Package Booking'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_p)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_p += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_p = 0;
$enq_cold_p = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Package Booking'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_p)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_p += floatval($enquiry_content_arr2['value']);
        }
    }
}
//Flight
$budget_s_f = 0;
$enq_strong_c1 = mysqli_num_rows(mysqlQuery("select enquiry_content from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Flight Ticket'"));
$enq_strong_f = mysqlQuery("select enquiry_content from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Flight Ticket'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_f)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);

    $budget_s_f += floatval($enquiry_content_arr1[0]['budget']);
}
$budget_h_f = 0;
$enq_hot_f= mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Flight Ticket'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_f)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    $budget_h_f += floatval($enquiry_content_arr1[0]['budget']);
}

$budget_c_f = 0;
$enq_cold_f = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Flight Ticket'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_f)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    $budget_c_f += floatval($enquiry_content_arr1[0]['budget']);
}

//Train
$budget_s_t = 0;
$enq_strong_t = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Train Ticket'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_t)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_t += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_t = 0;
$enq_hot_t= mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Train Ticket'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_t)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_t += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_t = 0;
$enq_cold_t = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Train Ticket'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_t)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_t += floatval($enquiry_content_arr2['value']);
        }
    }
}

//visa
$budget_s_v = 0;
$enq_strong_v = mysqlQuery("select enquiry_content from enquiry_master where (enquiry_date between '$from_date1' and '$to_date1') and enquiry='Strong' and enquiry_type='Visa'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_v)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_v += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_v = 0;
$enq_hot_v= mysqlQuery("select enquiry_content from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Visa'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_v)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_v += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_v = 0;
$enq_cold_v = mysqlQuery("select enquiry_content from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Visa'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_v)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_v += floatval($enquiry_content_arr2['value']);
        }
    }
}


//Hotel
$budget_s_h = 0;
$enq_strong_h = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Hotel'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_h)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_h += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_h = 0;
$enq_hot_h= mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Hotel'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_h)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_h += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_h = 0;
$enq_cold_h = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Hotel'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_h)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_h += floatval($enquiry_content_arr2['value']);
        }
    }
}

//Passport
$budget_s_pp = 0;
$enq_strong_pp = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Passport'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_pp)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_pp += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_pp = 0;
$enq_hot_pp= mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Passport'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_pp)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_pp += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_pp = 0;
$enq_cold_pp = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Passport'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_pp)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_pp += floatval($enquiry_content_arr2['value']);
        }
    }
}

//Car Rental
$budget_s_c = 0;
$enq_strong_c = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Car Rental'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_c)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_c += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_c = 0;
$enq_hot_c = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Car Rental'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_c)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_c += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_c = 0;
$enq_cold_c = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Car Rental'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_c)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_c += floatval($enquiry_content_arr2['value']);
        }
    }
}
//Bus
$budget_s_b = 0;
$enq_strong_b = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Strong' and enquiry_type='Bus'"); 
while($row_s= mysqli_fetch_assoc($enq_strong_b)){
    $enquiry_content = $row_s['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_s_b += floatval($enquiry_content_arr2['value']);
        }
    }
}
$budget_h_b = 0;
$enq_hot_b = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Hot' and enquiry_type='Bus'"); 
while($row_h= mysqli_fetch_assoc($enq_hot_b)){
    $enquiry_content = $row_h['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_h_b += floatval($enquiry_content_arr2['value']);
        }
    }
}

$budget_c_b = 0;
$enq_cold_b = mysqlQuery("select * from enquiry_master where enquiry_date between '$from_date1' and '$to_date1' and enquiry='Cold' and enquiry_type='Bus'"); 
while($row_c= mysqli_fetch_assoc($enq_cold_b)){
    $enquiry_content = $row_c['enquiry_content'];
    $enquiry_content_arr1 = json_decode($enquiry_content, true);
    foreach($enquiry_content_arr1 as $enquiry_content_arr2){
        
        if($enquiry_content_arr2['name']=="budget"){

            $budget_c_b += floatval($enquiry_content_arr2['value']);
        }
    }
}
?>
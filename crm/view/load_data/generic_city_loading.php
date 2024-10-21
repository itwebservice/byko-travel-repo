<?php include "../../model/model.php"; ?>
<?php
  
        $term=!empty($_GET['term']) ? $_GET['term'] : null;
        $offset=(!empty($_GET['page']) ? $_GET['page'] : 0)*100;
        $valueasText = !empty($_GET['valueasText']) ? $_GET['valueasText'] : null;
        $q=mysqlQuery("select * from city_master where active_flag='Active' and city_name LIKE '$term%' LIMIT 100 OFFSET $offset");
        while($row = mysqli_fetch_assoc($q)){
            if($valueasText == "true")
                $array['results'][]=array('id'=>$row['city_name'],'text'=>$row['city_name']);    
            else
                $array['results'][]=array('id'=>$row['city_id'],'text'=>$row['city_name']);
        }
        $rows = mysqli_num_rows($q);
        if($rows == 100){
            $array['pagination']=array("more"=>true);
        }
        else if($rows < 100){
            
            $array['pagination']=array("more"=>false);
        }  

    echo  json_encode($array);
?>
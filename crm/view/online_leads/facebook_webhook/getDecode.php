<?php
include 'crm/model/model.php';
$data = json_decode(file_get_contents('facebook.json'));
echo "<pre>";
var_dump($data);
echo "</pre>";
$valueData = array();
foreach($data as $d)
{
    
    if(!empty($d->entry))
    {
        $entry = $d->entry;
        foreach($entry as $e)
        {
            
            if(!empty($e->changes))
            {
                $changes = $e->changes;
                foreach($changes as $c)
                {
                    $valueData[] = $c->value;
                }        
            }
        }
    }   
}


mysqlQuery("insert into facebook_data values(NULL,'".json_encode($valueData)."',0)");


?>


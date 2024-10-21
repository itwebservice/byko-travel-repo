<?php
$challenge = $_REQUEST['hub_challenge'];
$verify_token = $_REQUEST['hub_verify_token'];

if ($verify_token === 'abc123') {
  echo $challenge;
    exit;
    
}

$input = json_decode(file_get_contents('php://input'), true);
$file = json_decode(file_get_contents('facebook.json'),true);
$file[] = $input;
file_put_contents('facebook.json',json_encode($file));
//error_log(print_r($input, true));

include 'crm/model/model.php';
$d = json_decode(json_encode($input));
error_log(print_r($input, true));

$valueData = array();
  
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

mysqlQuery("insert into facebook_data values(NULL,'".json_encode($valueData)."',0)");



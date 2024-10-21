<?php
function get_qr($type)
{
    $result = mysqli_fetch_assoc(mysqlQuery('select qr_url,sign_url from app_settings'));    

    if($type == 'Landscape Advanced')
    {
    $htmlQR = '<img src="'.BASE_URL.'/'.substr($result['qr_url'],9).'" alt="" width=100   class="img-thumbnail">';
    return $htmlQR;
    }
    // Protrait Advance
    if($type == 'Protrait Advance')
    {
      
    $htmlQR = '<img src="'.BASE_URL.'/'.substr($result['qr_url'],9).'" alt="" width=100  class="img-thumbnail">';
    return $htmlQR;
    }
    if($type == 'Protrait Creative')
    {
      
    $htmlQR = '<img src="'.BASE_URL.'/'.substr($result['qr_url'],9).'" alt="" width=100  class="img-thumbnail">';
    return $htmlQR;
    }
    //Landscape
    if($type == 'Landscape Creative')
    {
      
    $htmlQR = '<img src="'.BASE_URL.'/'.substr($result['qr_url'],9).'" alt="" width=100  class="img-thumbnail">';
    return $htmlQR;
    }
    //Standard
    if($type == 'Landscape Standard')
    {
      
    $htmlQR = '<img src="'.BASE_URL.'/'.substr($result['qr_url'],9).'" alt="" width=100  class="img-thumbnail">';
    return $htmlQR;
    }
    //protrait standard
    if($type == 'Protrait Standard')
    {
      
    $htmlQR = '<img src="'.BASE_URL.'/'.substr($result['qr_url'],9).'" alt="" width=100  class="img-thumbnail">';
    return $htmlQR;
    }
}

?>


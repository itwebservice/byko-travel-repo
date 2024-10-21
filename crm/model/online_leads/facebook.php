<?php

class facebook
{
    public function setApp()
    {
        $appId = $_POST['appId'];
        $appSecret = $_POST['appSecret'];
        $appCallback = $_POST['appCallback'];
        $query = "update app_settings set facebook_appid='".$appId."',facebook_appsecret='".$appSecret."',facebook_callback='".$appCallback."'";
        mysqlQuery($query)or die('Error');
        echo "Success";
    }
    public function fetchData()
    {
        $data = [];
        $query = "SELECT * FROM `facebook_data` where is_done=0";
        $res = mysqlQuery($query)or die('Error');
        if(mysqli_num_rows($res)>0)
        {
            while($db = mysqli_fetch_object($res))
            {
                $decode = json_decode($db->data);
                $decode[0]->unqId = $db->id;
                $data[] = $decode[0];
            }
        }
        echo json_encode($data);
    }
    public function setData()
    {
        $mainData = $_POST['mainData'];
        $dataFilter = array();
        if(!empty($mainData))
        {
            foreach($mainData as $main)
            {
                if($main['name'] == "full_name")
                {
                    $dataFilter['name'] = implode(" ",$main['values']);
                }
                if($main['name'] == "email")
                {
                    $dataFilter['email'] = implode(" ",$main['values']);
                }
                if($main['name'] == "phone_number")
                {
                    $dataFilter['phone'] = implode(" ",$main['values']);
                }
                if($main['name'] == "total_adults")
                {
                    $dataFilter['total_adults'] = implode(" ",$main['values']);
                }
                if($main['name'] == "total_children_with_bed")
                {
                    $dataFilter['total_children_with_bed'] = implode(" ",$main['values']);
                }
                if($main['name'] == "total_children_without_bed")
                {
                    $dataFilter['total_children_without_bed'] = implode(" ",$main['values']);
                }
                if($main['name'] == "total_children_without_bed")
                {
                    $dataFilter['total_children_without_bed'] = implode(" ",$main['values']);
                }
                  if($main['name'] == "total_infants")
                {
                    $dataFilter['total_infants'] = implode(" ",$main['values']);
                }
                
                
                if($main['name'] == "interested_tour")
                {
                    $dataFilter['interested_tour'] = implode(" ",$main['values']);
                }
                    
            }
        }
        $dataMain = array();
        $dataMain['firstName'] = $dataFilter['name'];
        $dataMain['lastName'] = ' ';
        $dataMain['email'] = !empty($dataFilter['email']) ? $dataFilter['email'] : null;
        $dataMain['phoneNumber'] = !empty($dataFilter['phone']) ? $dataFilter['phone'] : null;
        $dataMain['adult'] =  !empty($dataFilter['total_adults']) ? (int)$dataFilter['total_adults'] : 1;
        $dataMain['children'] = !empty($dataFilter['total_children_with_bed']) ? (int)$dataFilter['total_children_with_bed'] : 0;
        $dataMain['children_without_bed'] = !empty($dataFilter['total_children_without_bed']) ? (int)$dataFilter['total_children_without_bed'] : 0;
        $dataMain['total_infants'] = !empty($dataFilter['total_infants']) ? (int)$dataFilter['total_infants'] : 0;
        $dataMain['travelCheckInDate'] = !empty($_POST['from_date']) ? $_POST['from_date'] : '01-01-2022';
        $dataMain['travelCheckOutDate'] = !empty($_POST['to_date']) ? $_POST['to_date'] : '03-01-2022';
        $dataMain['hotelCategory'] = " ";
        $dataMain['requirement'] =  null;
        $dataMain['budget'] = 0;
        $dataMain['location'] = $dataFilter['interested_tour'];
        $dataMain['landline_no'] = !empty($dataFilter['phone']) ? $dataFilter['phone'] : null;

// $json_data_add = json_decode(file_get_contents('response_data.json'));
// $json_data_add[] =  $_POST['customFieldValues'];

// file_put_contents('response_data.json',json_encode($json_data_add,JSON_PRETTY_PRINT));

$contentData = [

    [

      'name' => 'total_adult',

      'value' => $dataMain['adult']

    ],

    [

      'name' => 'children_with_bed',

      'value' => $dataMain['children']

    ],
        [

      'name' => 'children_without_bed',

      'value' => $dataMain['children_without_bed']

    ],
    

    [

      'name' => 'travel_from_date',

      'value' => (new DateTime($dataMain['travelCheckInDate']))->format('d-m-Y')

    ], [

      'name' => 'travel_to_date',

      'value' => (new DateTime($dataMain['travelCheckOutDate']))->format('d-m-Y')

    ],
     [

      'name' => 'tour_name',

      'value' => $dataMain['location']

    ],

    // [

    //   'name' => 'from_date',

    //   'value' => $dataMain['travelCheckOutDate']

    // ],

    [

      'name' => 'total_infant',

      'value' => $dataMain['total_infants']

    ],
    [

      'name' => 'budget',

      'value' => 0

    ],
    ["name"=>"hotel_type",
    "value"=>$dataMain['hotelCategory']
    ]

  ];

     $contents = json_encode($contentData);

    $financial_year = mysqli_fetch_assoc(mysqlQuery("select * from financial_year ORDER BY financial_year_id DESC limit 1"))or die("error1");

    $current_date = date('Y-m-d');

    $current_date_time = date('Y-m-d h:i:s:a');

    $query_entry_id = mysqli_fetch_assoc(mysqlQuery('select * from enquiry_master_entries ORDER BY entry_id DESC limit 1'))or die("error2");

    $entry_id =  (int)$query_entry_id['entry_id'] + 1;

    $query_entry = mysqlQuery("INSERT INTO enquiry_master_entries VALUES('$entry_id','0','Followup','In-Followup','Had Call-Chat','$current_date_time','Hot',' ','$current_date_time')")or die("error3");

    $query_enquiry_id = mysqli_fetch_assoc(mysqlQuery('select * from enquiry_master ORDER BY enquiry_id DESC limit 1'))or die("error4");

    $enquiry_id =  (int)$query_enquiry_id['enquiry_id'] + 1;
    
    $master_query = "INSERT INTO enquiry_master VALUES('$enquiry_id','1','1','" . $financial_year['financial_year_id'] . "','Package Booking','Strong','".$dataMain['firstName']." ".$dataMain['lastName']." ','+91','".$dataMain['phoneNumber']."','".$dataMain['landline_no']."','".$dataMain['email']."','".$dataMain['location']."','".$dataMain['requirement']."',' $current_date',' $current_date ','1','1','$contents','','$entry_id','".$dataMain['firstName']." ".$dataMain['lastName']."')";

    
    $query1 = mysqlQuery($master_query)or die("error5");

    $update_entry =  mysqlQuery("update enquiry_master_entries set enquiry_id='" . $enquiry_id . "' where entry_id='" . $entry_id . "'")or die('error6');
    
    mysqlQuery('UPDATE `facebook_data` SET `is_done`=1 WHERE id="'.$_POST['unqId'].'"');
    
    http_response_code(200);

    echo json_encode('Enquiry Submitted Successfully');

    }
}


?>
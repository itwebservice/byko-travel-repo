<?php
class system_settings{

    function save(){

        $entry_id_arr = $_POST['entry_id_arr'];
        $answer_arr = $_POST['answer_arr'];

        begin_t();
        for($i = 0;$i<sizeof($entry_id_arr);$i++){

            $entry_id = intval($entry_id_arr[$i]);
            $answer = $answer_arr[$i];

            $sq = mysqlQuery("UPDATE `generic_settings` SET `answer`='$answer_arr[$i]' WHERE entry_id='$entry_id'");
            // 5 to 10 questions
            if($entry_id == 5){             // City

                if($answer == 'Yes')
                    $sq_delete = mysqlQuery("UPDATE `city_master` SET `active_flag`='Inactive'");
                else
                    $sq_delete = mysqlQuery("UPDATE `city_master` SET `active_flag`='Active'");
            }
            else if($entry_id == 6){       // Airport

                if($answer == 'Yes')
                    $sq_delete = mysqlQuery("UPDATE `airport_master` SET `flag`='Inactive'");
                else
                    $sq_delete = mysqlQuery("UPDATE `airport_master` SET `flag`='Active'");
            }
            else if($entry_id == 7){       // Hotel
                
                if($answer == 'Yes'){
                    $sq_delete = mysqlQuery("UPDATE `hotel_master` SET `active_flag`='Inactive'");
                    $sq_delete = mysqlQuery("UPDATE `ledger_master` SET `status`='Inactive' where user_type='Hotel Vendor'");
                }else{
                    $sq_delete = mysqlQuery("UPDATE `hotel_master` SET `active_flag`='Active'");
                    $sq_delete = mysqlQuery("UPDATE `ledger_master` SET `status`='Active' where user_type='Hotel Vendor'");
                }
            }
            else if($entry_id == 8){       // Package Tour
                
                if($answer == 'Yes')
                    $sq_delete = mysqlQuery("UPDATE `custom_package_master` SET `status`='Inactive'");
                else
                    $sq_delete = mysqlQuery("UPDATE `custom_package_master` SET `status`='Active'");
            }
            else if($entry_id == 9){       // Destinations
                
                if($answer == 'Yes')
                    $sq_delete = mysqlQuery("UPDATE `destination_master` SET `status`='Inactive'");
                else
                    $sq_delete = mysqlQuery("UPDATE `destination_master` SET `status`='Active'");
            }
            else if($entry_id == 10){      // Itineary
                
                if($answer == 'Yes')
                    $sq_delete = mysqlQuery("delete from `itinerary_master` where 1");
            }
            if(!$sq){
                rollback_t();
                echo "System settings not updated!";
                exit;
            }
        }
        if($sq){
            commit_t();
            echo "System settings saved successfully!";
            exit;
        }
    }
}
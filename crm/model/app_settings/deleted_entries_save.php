<?php
global $delete_master;
$delete_master = new delete_master;
class delete_master{

    public function delete_master_entries($trans_type,$module_name,$booking_id,$long_booking_id,$guest_name,$amount)
    {
        $emp_id = $_SESSION['emp_id'];

        $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from deleted_entries_master"));
        $entry_id = $sq_max['max'] + 1;
        $date = date('Y-m-d H:i:s');

        $sq_register = mysqlQuery("INSERT INTO `deleted_entries_master`(`entry_id`, `deleted_at`, `trans_type`, `module_name`, `id`, `long_id`, `guest_name`, `amount`,`deleted_by`) VALUES ('$entry_id','$date','$trans_type','$module_name','$booking_id','$long_booking_id','$guest_name','$amount','$emp_id')");
        if(!$sq_register){
            $GLOBALS['flag'] = false;
            echo "Deleted entry not saved!";
        }

    }
}
<?php
class taxes_master{
    function save(){

        $reflection_array = $_POST['reflection_array'];
        $tax_name1_array = $_POST['tax_name1_array'];
        $tax_amount1_array = $_POST['tax_amount1_array'];
        $ledger1_array = $_POST['ledger1_array'];
        $tax_name2_array = $_POST['tax_name2_array'];
        $tax_amount2_array = $_POST['tax_amount2_array'];
        $ledger2_array = $_POST['ledger2_array'];
        
        begin_t();
        for($i=0;$i<sizeof($reflection_array);$i++){

            $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(entry_id) as max from tax_master"));
            $entry_id = $sq_max['max'] + 1;
            $sq = mysqlQuery("insert into tax_master (entry_id, reflection,name1, amount1, ledger1,name2, amount2, ledger2, status)values('$entry_id','$reflection_array[$i]','$tax_name1_array[$i]','$tax_amount1_array[$i]','$ledger1_array[$i]','$tax_name2_array[$i]','$tax_amount2_array[$i]','$ledger2_array[$i]','Active')");
        }
        if($sq){
            commit_t();
            echo "Taxes saved successfully!";
            exit;
        }else{
            rollback_t();
            echo "error--Taxes not saved!";
            exit;
        }
    }
    function update(){

        $entry_id = $_POST['entry_id'];
        $reflection = $_POST['reflection'];
        $tax_name1 = $_POST['tax_name1'];
        $tax_amount1 = $_POST['tax_amount1'];
        $ledger1 = $_POST['ledger1'];
        $tax_name2 = $_POST['tax_name2'];
        $tax_amount2 = $_POST['tax_amount2'];
        $ledger2 = $_POST['ledger2'];
        $status = $_POST['status'];

        begin_t();
        $sq = mysqlQuery("update tax_master set reflection='$reflection',name1='$tax_name1', amount1='$tax_amount1', ledger1='$ledger1',name2='$tax_name2', amount2='$tax_amount2', ledger2='$ledger2',status='$status' where entry_id='$entry_id'");
        if($sq){
            commit_t();
            echo "Tax updated successfully!";
            exit;
        }else{
            rollback_t();
            echo "error--Tax not updated!";
            exit;
        }
    }
}
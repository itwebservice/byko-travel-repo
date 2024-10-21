<?php
class customer_master{

public function customer_master_save()
{
	$first_name = isset($_POST['first_name']) ? mysqlREString($_POST['first_name']) : '';
	$middle_name = isset($_POST['middle_name'])? mysqlREString($_POST['middle_name']) : '';
	$last_name = isset($_POST['last_name']) ? mysqlREString($_POST['last_name']) : '';
	$gender = isset($_POST['gender']) ? mysqlREString($_POST['gender']) : '';
	$birth_date = isset($_POST['birth_date'])? $_POST['birth_date'] : '';
  $age = isset($_POST['age']) ? mysqlREString($_POST['age']) : '';
  $country_code = isset($_POST['country_code']) ? mysqlREString($_POST['country_code']) : '';
	$contact_no = isset($_POST['contact_no']) ? mysqlREString($_POST['contact_no']) : '';
	$email_id = isset($_POST['email_id']) ? mysqlREString($_POST['email_id']) : '';
	$address = isset($_POST['address']) ? mysqlREString($_POST['address']) : '';
  $address2 = isset($_POST['address2']) ? mysqlREString($_POST['address2']) : '';
  $city = isset($_POST['city']) ? mysqlREString($_POST['city']) : '';
	$active_flag = $_POST['active_flag'];
	$service_tax_no = isset($_POST['service_tax_no']) ? strtoupper($_POST['service_tax_no']) : '';
	$landline_no = isset($_POST['landline_no']) ? mysqlREString($_POST['landline_no']) : '';
	$alt_email_id = isset($_POST['alt_email_id']) ? mysqlREString($_POST['alt_email_id']) : '';
	$company_name = isset($_POST['company_name']) ? mysqlREString($_POST['company_name']) : '';
	$cust_type = isset($_POST['cust_type']) ? mysqlREString($_POST['cust_type']) : '';
  $state = isset($_POST['state']) ? mysqlREString($_POST['state']) : '';
  $cust_pan = mysqlREString($_POST['cust_pan']);
  $op_balance = isset($_POST['op_balance']) ? $_POST['op_balance'] : '';
  $balance_side = isset($_POST['balance_side']) ? $_POST['balance_side'] : '';
  $branch_admin_id= isset($_POST['branch_admin_id']) ? mysqlREString($_POST['branch_admin_id']) : '';
  $cust_source = isset($_POST['cust_source']) ? mysqlREString($_POST['cust_source']) : '';

  $user_name_array = isset($_POST['user_name_array']) ? $_POST['user_name_array'] : [];
  $mobile_no_array = isset($_POST['mobile_no_array']) ? $_POST['mobile_no_array'] : [];
  $email_id_array = isset($_POST['email_id_array']) ? $_POST['email_id_array'] : [];
  $status_array = isset($_POST['status_array']) ? $_POST['status_array'] : [];

  $contact_no = $country_code.$contact_no;
	$username = $contact_no;
  $password = $email_id;
  $balance_side = ($balance_side == '') ? 'Debit' : $balance_side;
  
  global $encrypt_decrypt, $secret_key;
  $contact_no = $encrypt_decrypt->fnEncrypt($contact_no, $secret_key);
  $email_id = $encrypt_decrypt->fnEncrypt($email_id, $secret_key);

	$birth_date = get_date_db($birth_date);
  $created_at = date("Y-m-d");
  $company_count = 0;
  if($company_name != ''){
    $company_count = mysqli_num_rows(mysqlQuery("select * from customer_master where company_name='$company_name' and type not in('Corporate','B2B')"));
  }
  if($company_count>0){
    echo "error--Sorry, The Company has already been taken.";
    exit;
  }
  $cust_count = mysqli_num_rows(mysqlQuery("select * from customer_master where contact_no='$contact_no'"));
  if($cust_count>0)
  {
    echo "error--Sorry, The Customer already exist.";
    exit;
  }
	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
	$customer_id = $sq_max['max'] + 1;

  $sq_visa = mysqlQuery("insert into customer_master (customer_id,type,first_name, middle_name, last_name, gender, birth_date, age, country_code,contact_no,landline_no, email_id,alt_email,company_name, address, address2, city, active_flag, created_at,service_tax_no,state_id,pan_no, branch_admin_id,source,op_balance,balance_side) values ('$customer_id','$cust_type', '$first_name', '$middle_name', '$last_name', '$gender', '$birth_date', '$age', '$country_code','$contact_no','$landline_no', '$email_id','$alt_email_id','$company_name', '$address','$address2','$city', '$active_flag', '$created_at', '$service_tax_no','$state','$cust_pan','$branch_admin_id','$cust_source','$op_balance','$balance_side')");

  if($cust_type == 'Corporate' || $cust_type == 'B2B') {
    for($i=0; $i<sizeof($user_name_array); $i++){

      $user_name_array[$i] = addslashes($user_name_array[$i]);
      $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(user_id) as max from customer_users"));
      $user_id = $sq_max['max'] + 1;

      $sq_user = mysqlQuery("insert into customer_users (user_id,customer_id,name, mobile_no, email_id,status) values ('$user_id','$customer_id','$user_name_array[$i]', '$mobile_no_array[$i]', '$email_id_array[$i]','$status_array[$i]')");
      if(!$sq_user){
        $GLOBALS['flag'] = false;
        echo "error--Some user entries not saved";
        exit;
      }
    }
  }

  $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(ledger_id) as max from ledger_master"));
  $ledger_id = $sq_max['max'] + 1;
  if($cust_type == 'Corporate' || $cust_type == 'B2B'){
    $ledger_name = $company_name;
  }
  else{
    $ledger_name = $customer_id.'_'.$first_name.' '.$last_name;
  }
  $bal_side = ($balance_side == 'Credit') ? 'Cr' : 'Dr';
  $sq_ledger = mysqlQuery("insert into ledger_master (ledger_id, ledger_name, alias, group_sub_id, balance,balance_side, dr_cr,customer_id,user_type,status) values ('$ledger_id', '$ledger_name', '', '20', '$op_balance','$balance_side','$bal_side','$customer_id','customer','Active')");

	if(!$sq_visa){
		echo "error--Sorry Customer information not saved successfully!";
		exit;
	}
	else{
		$this->employee_sign_up_mail($first_name, $last_name, $username, $password, $email_id,$company_name,$cust_type);
		echo "Customer has been successfully saved.==".$customer_id;
		exit;
	}
}

public function customer_master_update(){

	$customer_id = mysqlREString($_POST['customer_id']);
	$first_name = mysqlREString($_POST['first_name']);
	$middle_name = mysqlREString($_POST['middle_name']);
	$last_name = mysqlREString($_POST['last_name']);
	$gender = mysqlREString($_POST['gender']);
	$birth_date = $_POST['birth_date'];
  $age = mysqlREString($_POST['age']);
  $country_code = $_POST['country_code'];
	$contact_no = mysqlREString($_POST['contact_no']);
	$email_id = $_POST['email_id'];
	$address = mysqlREString($_POST['address']);
  $address2 = mysqlREString($_POST['address2']);
  $city = mysqlREString($_POST['city']);
	$active_flag = $_POST['active_flag'];
	$service_tax_no1 = strtoupper($_POST['service_tax_no1']);
	$landline_no = isset($_POST['landline_no']) ? mysqlREString($_POST['landline_no']) : '';
	$alt_email_id = isset($_POST['alt_email_id']) ? $_POST['alt_email_id'] : '';
	$company_name = isset($_POST['company_name']) ? mysqlREString($_POST['company_name']) : '';
	$cust_type = $_POST['cust_type'];
  $state = isset($_POST['cust_state'])?mysqlREString($_POST['cust_state']):'';
  $cust_pan = $_POST['cust_pan'];
  $op_balance = $_POST['op_balance'];
  $balance_side = $_POST['balance_side'];
  $cust_source = $_POST['cust_source'];

  $user_name_array = isset($_POST['user_name_array']) ? $_POST['user_name_array'] : [];
  $mobile_no_array = isset($_POST['mobile_no_array']) ? $_POST['mobile_no_array'] : [];
  $email_id_array = isset($_POST['email_id_array']) ? $_POST['email_id_array'] : [];
  $status_array = isset($_POST['status_array']) ? $_POST['status_array'] : [];
  $entry_id_array = isset($_POST['entry_id_array']) ? $_POST['entry_id_array'] : [];
  $checkbox_array = isset($_POST['checkbox_array']) ? $_POST['checkbox_array'] : [];

  $contact_no = $country_code.$contact_no;
  global $encrypt_decrypt, $secret_key;
  $contact_no = $encrypt_decrypt->fnEncrypt($contact_no, $secret_key);
  $email_id = $encrypt_decrypt->fnEncrypt($email_id, $secret_key);

	$birth_date = date('Y-m-d', strtotime($birth_date));
  $company_count = 0 ;
  if($company_name != ''){
    $company_count = mysqli_num_rows(mysqlQuery("select * from customer_master where company_name='$company_name' and customer_id!='$customer_id'")); 
  }
  if($company_count>0){
    echo "error--Sorry, The Company has already been taken.";
    exit;
  }
  
	$sq_visa = mysqlQuery("update customer_master set type = '$cust_type',first_name='$first_name', middle_name='$middle_name', last_name='$last_name', gender='$gender', birth_date='$birth_date', age='$age', country_code = '$country_code', contact_no='$contact_no',landline_no = '$landline_no', email_id='$email_id',alt_email = '$alt_email_id',company_name = '$company_name', address='$address', address2='$address2', city='$city', active_flag='$active_flag', service_tax_no='$service_tax_no1', state_id='$state', pan_no ='$cust_pan',source='$cust_source',op_balance='$op_balance',balance_side='$balance_side' where customer_id='$customer_id'");

  if($cust_type == 'Corporate' || $cust_type == 'B2B') {

    for($i=0; $i<sizeof($user_name_array); $i++){

      if($checkbox_array[$i] == 'true'){
        
        $user_name_array[$i] = addslashes($user_name_array[$i]);
        if($entry_id_array[$i] == ''){

          $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(user_id) as max from customer_users"));
          $user_id = $sq_max['max'] + 1;

          $sq_user = mysqlQuery("insert into customer_users (user_id,customer_id,name, mobile_no, email_id,status) values ('$user_id','$customer_id','$user_name_array[$i]', '$mobile_no_array[$i]', '$email_id_array[$i]','$status_array[$i]')");
          if(!$sq_user){
            $GLOBALS['flag'] = false;
            echo "error--Some user entries not saved";
            exit;
          }
        }else{

	        $sq_user = mysqlQuery("update customer_users set name='$user_name_array[$i]',mobile_no='$mobile_no_array[$i]', email_id='$email_id_array[$i]',status='$status_array[$i]' where user_id='$entry_id_array[$i]'");
          if(!$sq_user){
            $GLOBALS['flag'] = false;
            echo "error--Some user entries not saved";
            exit;
          }
        }
      }else{
        $sq_user = mysqlQuery("delete from customer_users where user_id='$entry_id_array[$i]'");
        if(!$sq_user){
          $GLOBALS['flag'] = false;
          echo "error--Some user entries not deleted";
          exit;
        }
      }
    }
  }

	//update customer leder
	if($cust_type == 'Corporate' || $cust_type == 'B2B'){
	  $ledger_name = $company_name;
	}
	else{
	  $ledger_name = $customer_id.'_'.$first_name.'_'.$last_name;
	}
  $bal_side = ($balance_side == 'Credit') ? 'Cr' : 'Dr';
	$sq_visa = mysqlQuery("update ledger_master set ledger_name='$ledger_name',balance='$op_balance',balance_side='$balance_side', dr_cr='$bal_side' where user_type='customer' and customer_id='$customer_id'");

	if(!$sq_visa){
		echo "error--Sorry Customer information not update!";
		exit;
	}
	else{
		echo "Customer has been successfully updated.";
		exit;

	}
}

public function customer_master_csv_save()
{
  global $encrypt_decrypt, $secret_key;
  $cust_csv_dir = $_POST['cust_csv_dir'];
  $branch_admin_id=$_POST['branch_admin_id'];
  $flag = true;

  $cust_csv_dir = explode('uploads', $cust_csv_dir);
  $cust_csv_dir = BASE_URL.'uploads'.$cust_csv_dir[1];

  begin_t();

  $count = 1;
  $validCount=0;
  $invalidCount=0;
  $unprocessedArray=array();
  $arrResult  = array();
  $handle = fopen($cust_csv_dir, "r");
  if(empty($handle) === false) {

      while(($data = fgetcsv($handle, 8000,",")) !== FALSE){

          if($count == 1) { $count++; continue; }
          if($count>0){
              
              $cust_type = $data[0];
              $first_name = $data[1];
              $middle_name = $data[2];
              $last_name = $data[3];
              $gender = $data[4];
              $birth_date = $data[5];
              $age = $data[6];
              $country_code = $data[7];
              $country_code = str_replace("`","",$country_code);
              $contact_no = $data[8];
              $email_id = $data[9];
              $company_name = isset($data[10]) ? $data[10] : '';
              $landline_no = $data[11];
              $address = $data[12];
              $address2 = $data[13];
              $city = $data[14];
              $state_id = $data[15];
              $service_tax_no= $data[16];
              $pan_no = $data[17];
              $source = $data[18];
              $op_balance = $data[19];
              $balance_side = $data[20];
              $created_at = date("Y-m-d");
              $birth_date1 = date('Y-m-d',strtotime($birth_date));
              $username = $contact_no;
              $password = $email_id;
              $company_count = 0;
              if(($cust_type =='Corporate' || $cust_type =='B2B') && $company_name != ''){
                  $company_count = mysqli_num_rows(mysqlQuery("select * from customer_master where company_name='$company_name'")); 
              }
              if(($cust_type =='Corporate' || $cust_type =='B2B') && $company_name == ''){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
              }
              else if($company_count!=0){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
              }
              else{

                if( !empty($cust_type) && preg_match('/^[a-zA-Z0-9 \s]*$/', $cust_type) && !empty($first_name) && preg_match('/^[a-zA-Z \s]*$/', $first_name) && preg_match('/^[a-zA-Z \s]*$/', $last_name) &&  preg_match('/^[a-zA-Z \s]*$/', $gender) && preg_match('/^[0-9 \s]{6,20}+$/', $contact_no) && preg_match('/^[0-9]*$/', $state_id) && !empty($country_code))
                {
                  // && filter_var($email_id, FILTER_VALIDATE_EMAIL) 
                  $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(customer_id) as max from customer_master"));
                  $customer_id = $sq_max['max'] + 1;
                  
                  $sq_max = mysqli_fetch_assoc(mysqlQuery("select max(ledger_id) as max from ledger_master"));
                  $ledger_id = $sq_max['max'] + 1;
                  if($cust_type == 'Corporate' || $cust_type == 'B2B'){
                    $ledger_name = $company_name;
                  }
                  else{
                    $ledger_name = $customer_id.'_'.$first_name.'_'.$last_name;
                  }
                  $balance_side = ($balance_side == '') ? 'Debit' : $balance_side;
                  $bal_side = ($balance_side == 'Credit') ? 'Cr' : 'Dr';

                  $contact_no = $country_code.$contact_no;
                  $contact_no1 = $encrypt_decrypt->fnEncrypt($contact_no, $secret_key);
                  $email_id = $encrypt_decrypt->fnEncrypt($email_id, $secret_key);
                  $cust_count = mysqli_num_rows(mysqlQuery("select * from customer_master where contact_no='$contact_no1'"));
                  if($cust_count>0){
                      $invalidCount++;
                      array_push($unprocessedArray, $data);
                  }
                  else{

                      $validCount++;
                      mysqlQuery("insert into ledger_master (ledger_id, ledger_name, alias, group_sub_id, balance,balance_side, dr_cr,customer_id,user_type) values ('$ledger_id', '$ledger_name', '', '20', '$op_balance','$balance_side','$bal_side','$customer_id','customer')");
    
                      $sq_cust = mysqlQuery("insert into customer_master (customer_id,branch_admin_id, type,first_name, middle_name, last_name, gender, birth_date, age, contact_no,landline_no, email_id,alt_email,company_name, address,address2,city, active_flag, created_at,service_tax_no,state_id,pan_no,source,country_code,op_balance,balance_side) values ('$customer_id', '$branch_admin_id', '$cust_type', '$first_name', '$middle_name', '$last_name', '$gender', '$birth_date1', '$age', '$contact_no1','$landline_no', '$email_id','','$company_name', '$address', '$address2', '$city', 'Active', '$created_at', '$service_tax_no','$state_id','$pan_no','$source','$country_code','$op_balance','$balance_side')");
                    
                      if(!$sq_cust){
                        echo "error--Sorry Customer information not saved successfully!";
                        exit;
                      }
                      else{
                        $this->employee_sign_up_mail($first_name, $last_name, $username, $password, $email_id,$company_name,$cust_type);
                      }
                  }
                }
                else{
                    $invalidCount++;
                    array_push($unprocessedArray, $data);
                }
              }
          }
          $count++;
      }

        fclose($handle);
        $downloadurl1 = '';      
        if(isset($unprocessedArray) && !empty($unprocessedArray))
        {
            $filePath='../../download/unprocessed_customer_records'.$created_at.'.csv';
            $save = preg_replace('/(\/+)/','/',$filePath);
            $downloadurl='../../download/unprocessed_customer_records'.$created_at.'.csv';
            header("Content-type: text/csv ; charset:utf-8");
            header("Content-Disposition: attachment; filename=file.csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            $output = fopen($save, "w");  
            fputcsv($output, array('Customer Type' , 'First Name' , 'Middle Name' , 'Last Name' , 'Gender' , 'Birthdate' , 'Age' , 'Country Code','Contact No' ,'Email Id' , 'Company Name' , 'Landline No' , 'Address' , 'Address2' , 'City_id' , 'State_id' , 'Tax No' , 'PAN No', 'Source', 'Opening Balance', 'Balance Side'));  
          
          foreach($unprocessedArray as $row){
          fputcsv($output, $row);  
          }
          fclose($output); 
          $downloadurl1 =  "<script> window.location ='$downloadurl'; </script>";  
        }
    }

    if($flag){
      commit_t();
      if($validCount>0){
        echo  $validCount." records successfully imported<br>
        ".$invalidCount." records are failed.".$downloadurl1;
      }
      else{
        echo "error--No Customer information imported".$downloadurl1;
      }
      exit;
    }
    else{
      rollback_t();
      exit;
    }

}

public function employee_sign_up_mail($first_name, $last_name, $username, $password, $email_id,$company_name,$cust_type)
{
  global $secret_key,$encrypt_decrypt,$model;
  $link = BASE_URL.'view/customer';
  $email_id = $encrypt_decrypt->fnDecrypt($email_id, $secret_key);
  
  $content = mail_login_box($username, $password, $link);
  $subject = 'Welcome aboard!';

  $cust_name = ($cust_type == 'Corporate' || $cust_type == 'B2B') ? $company_name : $first_name;
  $model->app_email_send('2',$cust_name,$email_id, $content,$subject,'1');
}

public function whatsapp_send(){

  global $app_contact_no,$app_name,$session_emp_id;
  $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
  $username = isset($_POST['contact_no']) ? $_POST['contact_no'] : '';
  $password = isset($_POST['email_id']) ? $_POST['email_id'] : '';
  $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';
  $cust_type = isset($_POST['cust_type']) ? $_POST['cust_type'] : '';

  $customer_name = ($cust_type == 'Corporate' || $cust_type == 'B2B') ? $company_name : $first_name;
  $sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id= '$session_emp_id'"));
  if($session_emp_id == 0){
    $contact = $app_contact_no;
  }
  else{
    $contact = $sq_emp_info['mobile_no'];
  }

  $link = BASE_URL.'view/customer';
  $whatsapp_msg = 'Dear%20'.rawurlencode($customer_name).',%0aHope%20you%20are%20doing%20great.%0aYour%20Login%20Details%20!%0a*Username*%20:%20'.rawurlencode($username).'%0a*Password*%20:%20'.rawurlencode($password).'%0a*Link*%20:%20'.$link.


'%0aWe%20look%20forward%20to%20having%20you%20onboard%20with%20us.%0a%0aPlease%20contact%20for%20more%20details%20:%20'.$app_name.' '.$contact.'%0aThank%20you.%0a';

  $link = 'https://web.whatsapp.com/send?phone='.$username.'&text='.$whatsapp_msg;
  echo $link;
}

}
?>
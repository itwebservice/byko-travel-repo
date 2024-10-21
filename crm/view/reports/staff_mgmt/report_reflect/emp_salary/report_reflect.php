<?php
include "../../../../../model/model.php";
$emp_id = $_POST['emp_id'];
$year = $_POST['year'];
$month = $_POST['month'];
$branch_status =$_POST['branch_status'];
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];

if($emp_id != '' || $year != '' || $month !=''){

    $array_s = array();
    $temp_arr = array();
    $role = $_SESSION['role'];
    $query = "select * from emp_master where 1 and active_flag!='Inactive'";
    if($emp_id!=''){
      $query .= " and emp_id = '$emp_id'";
    }
    if($branch_status=='yes' && $role!='Admin'){
        $query .=" and branch_id='$branch_admin_id'";
    }

    $count = 1;
    $sq_a = mysqlQuery($query);
    while($row_emp = mysqli_fetch_assoc($sq_a)){
      
        $p_count =mysqli_num_rows(mysqlQuery( "select * from employee_attendance_log where emp_id='$row_emp[emp_id]' and month(att_date)= '$month' and year(att_date) = '$year' and status='Present'"));
        $b_url = BASE_URL."model/app_settings/print_html/salary_slip/salary_slip.php?emp_id=$row_emp[emp_id]&year=$year&month=$month";

        $sq_salary = "select * from employee_salary_master where emp_id='$row_emp[emp_id]' ";
        if($year!=''){
          $sq_salary .= " and year = '$year'";
        } 
        if($month!=''){
          $sq_salary .= " and month = '$month'";
        }        
        $sq_sal1 = mysqlQuery($sq_salary);
        while($sq_sal = mysqli_fetch_assoc($sq_sal1)){ 

          $temp_arr = array( "data" => array(
              (int)($count++),
              $row_emp['emp_id'],
              $row_emp['first_name'].' '.$row_emp['last_name'],
              $p_count,
              ($sq_sal['gross_salary']!="") ? $sq_sal['gross_salary'] : number_format(0,2),
              ($sq_sal['salary_advance']!="") ? $sq_sal['salary_advance'] : number_format(0,2),
              
              ($sq_sal['employer_pf']!="") ? $sq_sal['employer_pf'] : number_format(0,2),
              ($sq_sal['employee_pf']!="") ? $sq_sal['employee_pf'] : number_format(0,2),
              ($sq_sal['esic']!="") ? $sq_sal['esic'] : number_format(0,2),
              ($sq_sal['pt']!="") ? $sq_sal['pt']  : number_format(0,2),
              ($sq_sal['labour_all']!="") ?$sq_sal['labour_all'] : number_format(0,2),   
              ($sq_sal['tds']!="") ? $sq_sal['tds'] : number_format(0,2),
              ($sq_sal['surcharge_deduction']!="") ? $sq_sal['surcharge_deduction'] : number_format(0,2),
              ($sq_sal['cess_deduction']!="") ? $sq_sal['cess_deduction'] : number_format(0,2),
              ($sq_sal['leave_deduction']!="") ? $sq_sal['leave_deduction'] : number_format(0,2) ,
              ($sq_sal['deduction']!="") ? $sq_sal['deduction'] : number_format(0,2),
              ($sq_sal['net_salary']!="") ? $sq_sal['net_salary'] : number_format(0,2),
              '<button class="btn btn-info btn-sm" onclick="update_modal('. $sq_sal['salary_id'] .', '.$month .')" data-toggle="tooltip" title="Update Details" id="updates_btn-'. $sq_sal['salary_id'] .'"><i class="fa fa-pencil-square-o"></i></button>

              <a onclick="loadOtherPage(\''.$b_url .'\')" class="btn btn-info btn-sm" data-toggle="tooltip" title="Salary Slip"><i class="fa fa-print"></i></a>'

              ), "bg" =>'');
              array_push($array_s,$temp_arr);
      }
    }
}
echo json_encode($array_s);
?>
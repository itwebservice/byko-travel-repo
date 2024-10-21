<?php
include "../../../../../../model/model.php";
include_once('../gst_sale/sale_generic_functions.php');
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
  die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../../../../../../classes/PHPExcel-1.8/Classes/PHPExcel.php';

//This function generates the background color
function cellColor($cells,$color){
    global $objPHPExcel;

    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array(
        'rgb' => $color
        )
    ));
}

//This array sets the font atrributes
$header_style_Array = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),
        'size'  => 12,
        'name'  => 'Verdana'
    ));
$table_header_style_Array = array(
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 11,
        'name'  => 'Verdana'
    ));
$content_style_Array = array(
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 9,
        'name'  => 'Verdana'
    ));

//This is border array
$borderArray = array(
          'borders' => array(
              'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
              )
          )
      );

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
  ->setLastModifiedBy("Maarten Balliauw")
  ->setTitle("Office 2007 XLSX Test Document")
  ->setSubject("Office 2007 XLSX Test Document")
  ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
  ->setKeywords("office 2007 openxml php")
  ->setCategory("Test result file");

//////////////////////////****************Content start**************////////////////////////////////
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];
$taxation_id='';
$branch_status = $_GET['branch_status'];

if($from_date!="" && $to_date!=""){
    $date_str = $from_date.' to '.$to_date;
}
else{
    $date_str = "";
}
// Add some data
$objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('B2', 'Report Name')
          ->setCellValue('C2', 'GST-R1 Report')
          ->setCellValue('B3', 'From-To-Date')
          ->setCellValue('C3', $date_str);

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);    

$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($borderArray);    

$row_count = 7;      

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Sr.No")
        ->setCellValue('C'.$row_count, "Service Name")
        ->setCellValue('D'.$row_count, "SAC/HSN Code")
        ->setCellValue('E'.$row_count, "Customer Name")
        ->setCellValue('F'.$row_count, "TAX No")
        ->setCellValue('G'.$row_count, "Account State")
        ->setCellValue('H'.$row_count, "Booking ID")
        ->setCellValue('I'.$row_count, "Booking Date")
        ->setCellValue('J'.$row_count, "Type_Of_Supplies")
        ->setCellValue('K'.$row_count, "Place of Supply")
        ->setCellValue('L'.$row_count, "Net Amount")
        ->setCellValue('M'.$row_count, "Taxable Amount")
        ->setCellValue('N'.$row_count, "TAX%")
        ->setCellValue('O'.$row_count, "Tax Amount")
        ->setCellValue('P'.$row_count, "Markup")
        ->setCellValue('Q'.$row_count, "TAX%_On_Markup")
        ->setCellValue('R'.$row_count, "TAX_amount_On_Markup")
        ->setCellValue('S'.$row_count, "Cess%")
        ->setCellValue('T'.$row_count, "Cess Amount")
        ->setCellValue('U'.$row_count, "ITC Eligibility")
        ->setCellValue('V'.$row_count, "Reverse Charge");

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);    

$row_count++;
$count = 1;
$tax_total = 0;
$markup_tax_total = 0;
$sq_setting = mysqli_fetch_assoc(mysqlQuery("select * from app_settings where setting_id='1'"));
$sq_supply = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_setting[state_id]'"));

//GIT Booking
$query = "select * from tourwise_traveler_details where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);
  $query .= " and DATE(form_date) between '$from_date' and '$to_date' ";
}
$query .= " order by id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
  //Total count
	$sq_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as booking_count from travelers_details where traveler_group_id ='$row_query[id]'"));
	//Group cancel or not
	$sq_group = mysqli_fetch_assoc(mysqlQuery("select status from tour_groups where group_id ='$row_query[tour_group_id]'"));

	//Cancelled count
	$sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as cancel_count from travelers_details where traveler_group_id ='$row_query[id]' and status ='Cancel'"));
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
	if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
		$cust_name = $sq_cust['company_name'];
	}else{
		$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
	}

	if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'] && $row_query['tour_group_status']!="Cancel")
	{
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		$hsn_code = get_service_info('Group Tour');

		//Service tax
		$tax_per = 0;
		$service_tax_amount = 0;
		$tax_name = 'NA';
		if($row_query['service_tax'] !== 0.00 && ($row_query['service_tax']) !== ''){
			$service_tax_subtotal1 = explode(',',$row_query['service_tax']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = explode(':',$service_tax_subtotal1[$i]);
				$service_tax_amount +=  $service_tax[2];
				$tax_name .= $service_tax[0] . $service_tax[1].' ';
				$tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
			}
		}
		//Markup Tax
		$markup_tax_amount = 0;
		$markup_tax_name = 'NA';
		$markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $service_tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['form_date']);
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "Group Booking")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_group_booking_id($row_query['id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['form_date']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, $row_query['net_total'])
        ->setCellValue('M'.$row_count, number_format($row_query['net_total']-$row_query['roundoff']-$service_tax_amount,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
        ->setCellValue('P'.$row_count, $markup)
        ->setCellValue('Q'.$row_count, $markup_tax_name)
        ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);    
    $row_count++;
	}
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'] || $row_query['tour_group_status']=="Cancel")
	{
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		$hsn_code = get_service_info('Group Tour');

		$sq_tour_c = mysqli_num_rows(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$row_query[id]'"));
		if($sq_tour_c != 0)
			$sq_tour_info = mysqli_fetch_assoc(mysqlQuery("select * from refund_tour_estimate where tourwise_traveler_id='$row_query[id]'"));
		else
			$sq_tour_info = mysqli_fetch_assoc(mysqlQuery("select * from refund_traveler_estimate where tourwise_traveler_id='$row_query[id]'"));
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($sq_tour_info['tax_amount'] !== 0.00 && ($sq_tour_info['tax_amount']) !== ''){
        $service_tax_subtotal1 = explode(',',$sq_tour_info['tax_amount']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += floatval(str_replace( array('(',')', '%'),'', $service_tax[1]));
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = 'NA';
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;

      $yr = explode("-",$row_query['form_date']);
      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "Group Booking")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_group_booking_id($row_query['id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['form_date']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, $row_query['net_total'])
        ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
        ->setCellValue('P'.$row_count, $markup)
        ->setCellValue('Q'.$row_count, $markup_tax_name)
        ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);    
    $row_count++;
	}
}
//FIT Booking
$query = "select * from package_tour_booking_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and booking_date between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	
  //Total count
  $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as booking_count from package_travelers_details where booking_id ='$row_query[booking_id]'"));
  //Cancelled count
  $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(traveler_id) as cancel_count from package_travelers_details where booking_id ='$row_query[booking_id]' and status ='Cancel'"));

  $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
  if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
    $cust_name = $sq_cust['company_name'];
  }else{
    $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
  }

  if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
  {
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      $hsn_code = get_service_info('Package Tour');  	
    
    //Service tax
    $tax_per = 0;
    $service_tax_amount = 0;
    $tax_name = 'NA';
    if($row_query['tour_service_tax_subtotal'] !== 0.00 && ($row_query['tour_service_tax_subtotal']) !== ''){
      $service_tax_subtotal1 = explode(',',$row_query['tour_service_tax_subtotal']);
      $tax_name = '';
      for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
        $service_tax = explode(':',$service_tax_subtotal1[$i]);
        $service_tax_amount +=  $service_tax[2];
        $tax_name .= $service_tax[0] . $service_tax[1].' ';
        $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
      }
    }
    //Markup Tax
    $markup_tax_amount = 0;
    $markup_tax_name = 'NA';
    $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
    //Taxable amount
    $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
    $tax_total += $service_tax_amount;
    $markup_tax_total += $markup_tax_amount;
		$yr = explode("-",$row_query['booking_date']);

    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('B'.$row_count, $count++)
      ->setCellValue('C'.$row_count, "Package Booking")
      ->setCellValue('D'.$row_count, $hsn_code)
      ->setCellValue('E'.$row_count, $cust_name)
      ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
      ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
      ->setCellValue('H'.$row_count, get_package_booking_id($row_query['booking_id'],$yr[0]))
      ->setCellValue('I'.$row_count, get_date_user($row_query['booking_date']))
      ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
      ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
      ->setCellValue('L'.$row_count, $row_query['net_total'])
      ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
      ->setCellValue('N'.$row_count, $tax_name)
      ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
      ->setCellValue('P'.$row_count, $markup)
      ->setCellValue('Q'.$row_count, $markup_tax_name)
      ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
      ->setCellValue('S'.$row_count,'0.00')
      ->setCellValue('T'.$row_count,'0.00')
      ->setCellValue('U'.$row_count, '')
      ->setCellValue('V'.$row_count, '');

    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);   
    $row_count++;
  }
	if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
			$cust_name = $sq_cust['company_name'];
		}else{
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		}
		$sq_cancel_bookingc = mysqli_num_rows(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_query[booking_id]'"));
		if($sq_cancel_bookingc > 0){

			$sq_cancel_booking = mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_query[booking_id]'"));
			$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
			$hsn_code = get_service_info('Package Tour');
			$tax_per = 0;
			$service_tax_amount = 0; //Service tax
			$tax_name = 'NA';
			if($sq_cancel_booking['tax_amount'] !== 0.00 && ($sq_cancel_booking['tax_amount']) !== ''){
				$service_tax_subtotal1 = explode(',',$sq_cancel_booking['tax_amount']);
				$tax_name = '';
				for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
					$service_tax = explode(':',$service_tax_subtotal1[$i]);
					$service_tax_amount +=  $service_tax[2];
					$tax_name .= $service_tax[0] . $service_tax[1].' ';
					$tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
				}
			}
			//Markup Tax
			$markup_tax_amount = 0;
			$markup_tax_name = 'NA';
			$markup = 'NA';
			//Taxable amount
			$taxable_amount = ($tax_per != 0) ? ($service_tax_amount / $tax_per) * 100:0;
			$tax_total += $service_tax_amount;
			$markup_tax_total += $markup_tax_amount;

			$yr = explode("-",$row_query['booking_date']);
      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "Package Booking")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_package_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['booking_date']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, $row_query['net_total'])
        ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
        ->setCellValue('P'.$row_count, $markup)
        ->setCellValue('Q'.$row_count, $markup_tax_name)
        ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');

      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);   
      $row_count++;
    }
  }
}
//Visa Booking
$query = "select * from visa_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);
  $query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by visa_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
  //Total count
  $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from visa_master_entries where visa_id ='$row_query[visa_id]'"));

  //Cancelled count
  $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from visa_master_entries where visa_id ='$row_query[visa_id]' and status ='Cancel'"));
  if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
  {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Visa');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
      if($row_query['markup_tax'] !== 0.00 && ($row_query['markup_tax']) !== ''){
        $markup_tax_subtotal1 = explode(',',$row_query['markup_tax']);
        $markup_tax_name = '';
        for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
          $markup_tax = explode(':',$markup_tax_subtotal1[$i]);
          $markup_tax_amount +=  $markup_tax[2];
          $markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
        }
      }
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Visa Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_visa_booking_id($row_query['visa_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['visa_total_cost'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      
      $row_count++;   
  }
  if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
  {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Visa');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = 'NA';
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Visa Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_visa_booking_id($row_query['visa_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['visa_total_cost'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      
      $row_count++;   
    }
}

//Bus Booking
    $query = "select * from bus_booking_master where 1 and delete_status='0' ";
    if($from_date !='' && $to_date != ''){
      $from_date = get_date_db($from_date);
      $to_date = get_date_db($to_date);
      $query .= " and created_at between '$from_date' and '$to_date' ";
    }
    $query .= " order by booking_id desc";
    $sq_query = mysqlQuery($query);
      while($row_query = mysqli_fetch_assoc($sq_query))
      {
        //Total count
      $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from bus_booking_entries where booking_id ='$row_query[booking_id]'"));

      //Cancelled count
      $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from bus_booking_entries where booking_id ='$row_query[booking_id]' and status ='Cancel'"));
      if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
      {
          $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
          if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
            $cust_name = $sq_cust['company_name'];
          }else{
            $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
          }
          $hsn_code = get_service_info('Bus');
          $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
          //Service tax
          $tax_per = 0;
          $service_tax_amount = 0;
          $tax_name = 'NA';
          if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
            $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
            $tax_name = '';
            for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
              $service_tax = explode(':',$service_tax_subtotal1[$i]);
              $service_tax_amount +=  $service_tax[2];
              $tax_name .= $service_tax[0] . $service_tax[1].' ';
              $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
            }
          }
          //Markup Tax
          $markup_tax_amount = 0;
          $markup_tax_name = 'NA';
          $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
          if($row_query['markup_tax'] !== 0.00 && ($row_query['markup_tax']) !== ''){
            $markup_tax_subtotal1 = explode(',',$row_query['markup_tax']);
            $markup_tax_name = '';
            for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
              $markup_tax = explode(':',$markup_tax_subtotal1[$i]);
              $markup_tax_amount +=  $markup_tax[2];
              $markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
            }
          }
          //Taxable amount
          $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
          $tax_total += $service_tax_amount;
          $markup_tax_total += $markup_tax_amount;
          $yr = explode("-",$row_query['created_at']);

          $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Bus Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_bus_booking_id($row_query['booking_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['net_total'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);         
      
        $row_count++;   
      }
      if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
      {
          $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
          if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
            $cust_name = $sq_cust['company_name'];
          }else{
            $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
          }
          $hsn_code = get_service_info('Bus');
          $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
          //Service tax
          $tax_per = 0;
          $service_tax_amount = 0;
          $tax_name = 'NA';
          if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
            $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
            $tax_name = '';
            for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
              $service_tax = explode(':',$service_tax_subtotal1[$i]);
              $service_tax_amount +=  $service_tax[2];
              $tax_name .= $service_tax[0] . $service_tax[1].' ';
              $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
            }
          }
          //Markup Tax
          $markup_tax_amount = 0;
          $markup_tax_name = 'NA';
          $markup = 'NA';
          //Taxable amount
          $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
          $tax_total += $service_tax_amount;
          $markup_tax_total += $markup_tax_amount;
          $yr = explode("-",$row_query['created_at']);

          $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Bus Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_bus_booking_id($row_query['booking_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['net_total'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);         
      
        $row_count++;   
      }
}

//Activity Booking
$query = "select * from excursion_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);
  $query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by exc_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
  //Total count
  $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from excursion_master_entries where exc_id ='$row_query[exc_id]'"));

  //Cancelled count
  $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from excursion_master_entries where exc_id ='$row_query[exc_id]' and status ='Cancel'"));
  if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
  {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $taxable_amount = $row_query['exc_issue_amount'] + $row_query['service_charge'];
      $hsn_code = get_service_info('Excursion');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
      if($row_query['service_tax_markup'] !== 0.00 && ($row_query['service_tax_markup']) !== ''){
        $markup_tax_subtotal1 = explode(',',$row_query['service_tax_markup']);
        $markup_tax_name = '';
        for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
          $markup_tax = explode(':',$markup_tax_subtotal1[$i]);
          $markup_tax_amount +=  $markup_tax[2];
          $markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
        }
      }
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Activity Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_exc_booking_id($row_query['exc_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['exc_total_cost'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);     

      $row_count++;   
  }
  if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
  {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $taxable_amount = $row_query['exc_issue_amount'] + $row_query['service_charge'];
      $hsn_code = get_service_info('Excursion');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = 'NA';
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Activity Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_exc_booking_id($row_query['exc_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['exc_total_cost'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);     

      $row_count++;   
  }
}

//Hotel Booking
$query = "select * from hotel_booking_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);
  $query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
  //Total count
  $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from hotel_booking_entries where booking_id ='$row_query[booking_id]'"));

  //Cancelled count
  $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from hotel_booking_entries where booking_id ='$row_query[booking_id]' and status ='Cancel'"));

  if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
  {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Hotel / Accommodation');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
      if($row_query['markup_tax'] !== 0.00 && ($row_query['markup_tax']) !== ''){
        $markup_tax_subtotal1 = explode(',',$row_query['markup_tax']);
        $markup_tax_name = '';
        for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
          $markup_tax = explode(':',$markup_tax_subtotal1[$i]);
          $markup_tax_amount +=  $markup_tax[2];
          $markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
        }
      }
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Hotel Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_hotel_booking_id($row_query['booking_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['total_fee'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);

      $row_count++;   
    }
    if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
    {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Hotel / Accommodation');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = 'NA';
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;

      $yr = explode("-",$row_query['created_at']);
      $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('B'.$row_count, $count++)
          ->setCellValue('C'.$row_count, "Hotel Booking")
          ->setCellValue('D'.$row_count, $hsn_code)
          ->setCellValue('E'.$row_count, $cust_name)
          ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
          ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
          ->setCellValue('H'.$row_count, get_hotel_booking_id($row_query['booking_id'],$yr[0]))
          ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
          ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
          ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
          ->setCellValue('L'.$row_count, $row_query['total_fee'])
          ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
          ->setCellValue('N'.$row_count, $tax_name)
          ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
          ->setCellValue('P'.$row_count, $markup)
          ->setCellValue('Q'.$row_count, $markup_tax_name)
          ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
          ->setCellValue('S'.$row_count,'0.00')
          ->setCellValue('T'.$row_count,'0.00')
          ->setCellValue('U'.$row_count, '')
          ->setCellValue('V'.$row_count, '');

      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);
  
      $row_count++;   
    }
  }

//Car Rental Booking
$query = "select * from car_rental_booking where delete_status='0' ";
if($from_date !='' && $to_date != ''){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);
  $query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	if($row_query['status'] != 'Cancel'){
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
    if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
      $cust_name = $sq_cust['company_name'];
    }else{
      $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
    }
    $hsn_code = get_service_info('Car Rental');
    $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
    
    //Service tax
    $tax_per = 0;
    $service_tax_amount = 0;
    $tax_name = 'NA';
    if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
      $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
      $tax_name = '';
      for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
        $service_tax = explode(':',$service_tax_subtotal1[$i]);
        $service_tax_amount +=  $service_tax[2];
        $tax_name .= $service_tax[0] . $service_tax[1].' ';
        $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
      }
    }
    //Markup Tax
    $markup_tax_amount = 0;
    $markup_tax_name = 'NA';
    $markup = ($row_query['markup_cost'] == '' || $row_query['markup_cost'] == '0') ? 'NA' : number_format($row_query['markup_cost'],2);
    if($row_query['markup_cost_subtotal'] !== 0.00 && ($row_query['markup_cost_subtotal']) !== ''){
      $markup_tax_subtotal1 = explode(',',$row_query['markup_cost_subtotal']);
      $markup_tax_name = '';
      for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
        $markup_tax = explode(':',$markup_tax_subtotal1[$i]);
        $markup_tax_amount +=  $markup_tax[2];
        $markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
      }
    }
    //Taxable amount
    $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
    $tax_total += $service_tax_amount;
    $markup_tax_total += $markup_tax_amount;
    $yr = explode("-",$row_query['created_at']);

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "Car Rental Booking")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_car_rental_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, $row_query['total_fees'])
        ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
        ->setCellValue('P'.$row_count, $markup)
        ->setCellValue('Q'.$row_count, $markup_tax_name)
        ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);    

    $row_count++;
  }
	if($row_query['status'] == 'Cancel'){
    $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
    if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
      $cust_name = $sq_cust['company_name'];
    }else{
      $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
    }
    $hsn_code = get_service_info('Car Rental');
    $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
    
    //Service tax
    $tax_per = 0;
    $service_tax_amount = 0;
    $tax_name = 'NA';
    if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
      $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
      $tax_name = '';
      for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
        $service_tax = explode(':',$service_tax_subtotal1[$i]);
        $service_tax_amount +=  $service_tax[2];
        $tax_name .= $service_tax[0] . $service_tax[1].' ';
        $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
      }
    }
    //Markup Tax
    $markup_tax_amount = 0;
    $markup_tax_name = 'NA';
    $markup = 'NA';
    //Taxable amount
    $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
    $tax_total += $service_tax_amount;
    $markup_tax_total += $markup_tax_amount;
    $yr = explode("-",$row_query['created_at']);

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "Car Rental Booking")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_car_rental_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, $row_query['total_fees'])
        ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
        ->setCellValue('P'.$row_count, $markup)
        ->setCellValue('Q'.$row_count, $markup_tax_name)
        ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);    

    $row_count++;
  }
 }

//Flight Booking
$query = "select * from ticket_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);
  $query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by ticket_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
    $cancel_type = $row_query['cancel_type'];
    //Total count
    $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from ticket_master_entries where ticket_id ='$row_query[ticket_id]'"));
    //Cancelled count
    $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from ticket_master_entries where ticket_id ='$row_query[ticket_id]' and status ='Cancel'"));
    if($cancel_type == 2 || $cancel_type == 3 || $cancel_type == 0)
    {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Flight');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
      if($row_query['service_tax_markup'] !== 0.00 && ($row_query['service_tax_markup']) !== ''){
        $markup_tax_subtotal1 = explode(',',$row_query['service_tax_markup']);
        $markup_tax_name = '';
        for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
          $markup_tax = explode(':',$markup_tax_subtotal1[$i]);
          $markup_tax_amount +=  $markup_tax[2];
          $markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
        }
      }
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Ticket Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_ticket_booking_id($row_query['ticket_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['ticket_total_cost'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);    

      $row_count++;   
    }
    if($row_query['cancel_flag'] == 1 && $cancel_type != 0)
    {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Flight');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = 'NA';
      $bg="";
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

      $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('B'.$row_count, $count++)
          ->setCellValue('C'.$row_count, "Ticket Booking")
          ->setCellValue('D'.$row_count, $hsn_code)
          ->setCellValue('E'.$row_count, $cust_name)
          ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
          ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
          ->setCellValue('H'.$row_count, get_ticket_booking_id($row_query['ticket_id'],$yr[0]))
          ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
          ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
          ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
          ->setCellValue('L'.$row_count, $row_query['ticket_total_cost'])
          ->setCellValue('M'.$row_count, number_format($row_query['cancel_amount'],2))
          ->setCellValue('N'.$row_count, $tax_name)
          ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
          ->setCellValue('P'.$row_count, $markup)
          ->setCellValue('Q'.$row_count, $markup_tax_name)
          ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
          ->setCellValue('S'.$row_count,'0.00')
          ->setCellValue('T'.$row_count,'0.00')
          ->setCellValue('U'.$row_count, '')
          ->setCellValue('V'.$row_count, '');

      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);    
      $row_count++;   
    }
}
  
//Train Booking
$query = "select * from train_ticket_master where 1 and delete_status='0' ";
if($from_date !='' && $to_date != ''){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);
  $query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by train_ticket_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
  //Total count
  $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from train_ticket_master_entries where train_ticket_id ='$row_query[train_ticket_id]'"));

  //Cancelled count
  $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from train_ticket_master_entries where train_ticket_id ='$row_query[train_ticket_id]' and status ='Cancel'"));
  if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
  {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Train');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Train Ticket Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_train_ticket_booking_id($row_query['train_ticket_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['net_total'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);     

      $row_count++;   
  }
  if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
  {
      $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
      if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
        $cust_name = $sq_cust['company_name'];
      }else{
        $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
      }
      $hsn_code = get_service_info('Train');
      $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
      
      //Service tax
      $tax_per = 0;
      $service_tax_amount = 0;
      $tax_name = 'NA';
      if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
        $tax_name = '';
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
          $service_tax = explode(':',$service_tax_subtotal1[$i]);
          $service_tax_amount +=  $service_tax[2];
          $tax_name .= $service_tax[0] . $service_tax[1].' ';
          $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
        }
      }
      //Markup Tax
      $markup_tax_amount = 0;
      $markup_tax_name = 'NA';
      $markup = 'NA';
      //Taxable amount
      $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
      $tax_total += $service_tax_amount;
      $markup_tax_total += $markup_tax_amount;
      $yr = explode("-",$row_query['created_at']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, "Train Ticket Booking")
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $cust_name)
            ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_train_ticket_booking_id($row_query['train_ticket_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
            ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
            ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
            ->setCellValue('L'.$row_count, $row_query['net_total'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');


        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);     

      $row_count++;   
  }
}

    //Miscellaneous Booking
    $query = "select * from miscellaneous_master where 1 and delete_status='0' ";
    if($from_date !='' && $to_date != ''){
      $from_date = get_date_db($from_date);
      $to_date = get_date_db($to_date);
      $query .= " and created_at between '$from_date' and '$to_date' ";
    }
    $query .= " order by misc_id desc";
    $sq_query = mysqlQuery($query);
      while($row_query = mysqli_fetch_assoc($sq_query))
      {
        //Total count
        $sq_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as booking_count from miscellaneous_master_entries where misc_id ='$row_query[misc_id]'"));
  
        //Cancelled count
        $sq_cancel_count = mysqli_fetch_assoc(mysqlQuery("select count(entry_id) as cancel_count from miscellaneous_master_entries where misc_id	 ='$row_query[misc_id]' and status ='Cancel'"));
        if($sq_count['booking_count'] != $sq_cancel_count['cancel_count'])
        {
          $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
          if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
            $cust_name = $sq_cust['company_name'];
          }else{
            $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
          }
          $hsn_code = get_service_info('Miscellaneous');  
          $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
  
          //Service tax
          $tax_per = 0;
          $service_tax_amount = 0;
          $tax_name = 'NA';
          if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
            $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
            $tax_name = '';
            for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
              $service_tax = explode(':',$service_tax_subtotal1[$i]);
              $service_tax_amount +=  $service_tax[2];
              $tax_name .= $service_tax[0] . $service_tax[1].' ';
              $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
            }
          }
          //Markup Tax
          $markup_tax_amount = 0;
          $markup_tax_name = 'NA';
          $markup = ($row_query['markup'] == '' || $row_query['markup'] == '0') ? 'NA' : number_format($row_query['markup'],2);
          if($row_query['service_tax_markup'] !== 0.00 && ($row_query['service_tax_markup']) !== ''){
            $markup_tax_subtotal1 = explode(',',$row_query['service_tax_markup']);
            $markup_tax_name = '';
            for($i=0;$i<sizeof($markup_tax_subtotal1);$i++){
              $markup_tax = explode(':',$markup_tax_subtotal1[$i]);
              $markup_tax_amount +=  $markup_tax[2];
              $markup_tax_name .= $markup_tax[0] . $markup_tax[1].' ';
            }
          }
          //Taxable amount
          $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
          $tax_total += $service_tax_amount;
          $markup_tax_total += $markup_tax_amount;
          $yr = explode("-",$row_query['created_at']);

          $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('B'.$row_count, $count++)
          ->setCellValue('C'.$row_count, "Miscellaneous Booking")
          ->setCellValue('D'.$row_count, $hsn_code)
          ->setCellValue('E'.$row_count, $cust_name)
          ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
          ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
          ->setCellValue('H'.$row_count, get_misc_booking_id($row_query['misc_id'],$yr[0]))
          ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
          ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
          ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
          ->setCellValue('L'.$row_count, $row_query['misc_total_cost'])
          ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
          ->setCellValue('N'.$row_count, $tax_name)
          ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
          ->setCellValue('P'.$row_count, $markup)
          ->setCellValue('Q'.$row_count, $markup_tax_name)
          ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
          ->setCellValue('S'.$row_count,'0.00')
          ->setCellValue('T'.$row_count,'0.00')
          ->setCellValue('U'.$row_count, '')
          ->setCellValue('V'.$row_count, '');


          $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
          $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);     

          $row_count++;   
        }
        if($sq_count['booking_count'] == $sq_cancel_count['cancel_count'])
        {
          $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
          if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B'){
            $cust_name = $sq_cust['company_name'];
          }else{
            $cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
          }
          $hsn_code = get_service_info('Miscellaneous');  
          $sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
  
          //Service tax
          $tax_per = 0;
          $service_tax_amount = 0;
          $tax_name = 'NA';
          if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
            $service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
            $tax_name = '';
            for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
              $service_tax = explode(':',$service_tax_subtotal1[$i]);
              $service_tax_amount +=  $service_tax[2];
              $tax_name .= $service_tax[0] . $service_tax[1].' ';
              $tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
            }
          }
          //Markup Tax
          $markup_tax_amount = 0;
          $markup_tax_name = 'NA';
          $markup = 'NA';
          //Taxable amount
          $taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
          $tax_total += $service_tax_amount;
          $markup_tax_total += $markup_tax_amount;
          $yr = explode("-",$row_query['created_at']);

          $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('B'.$row_count, $count++)
          ->setCellValue('C'.$row_count, "Miscellaneous Booking")
          ->setCellValue('D'.$row_count, $hsn_code)
          ->setCellValue('E'.$row_count, $cust_name)
          ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
          ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
          ->setCellValue('H'.$row_count, get_misc_booking_id($row_query['misc_id'],$yr[0]))
          ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
          ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
          ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
          ->setCellValue('L'.$row_count, $row_query['misc_total_cost'])
          ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
          ->setCellValue('N'.$row_count, $tax_name)
          ->setCellValue('O'.$row_count, number_format($service_tax_amount,2))
          ->setCellValue('P'.$row_count, $markup)
          ->setCellValue('Q'.$row_count, $markup_tax_name)
          ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
          ->setCellValue('S'.$row_count,'0.00')
          ->setCellValue('T'.$row_count,'0.00')
          ->setCellValue('U'.$row_count, '')
          ->setCellValue('V'.$row_count, '');


          $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
          $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);     

          $row_count++;   
        }
}
//Income Booking
$query = "select * from other_income_master where 1 and delete_status='0'";
        if($from_date !='' && $to_date != ''){
          $from_date = get_date_db($from_date);
          $to_date = get_date_db($to_date);
          $query .= " and receipt_date between '$from_date' and '$to_date' ";
        }
        $query .= " order by income_id desc";
        $sq_query = mysqlQuery($query);
          while($row_query = mysqli_fetch_assoc($sq_query))
          {
            $taxable_amount = $row_query['amount'];
            $sq_income_type_info = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$row_query[income_type_id]'"));
            $hsn_code = 'NA';
            
            //Service tax
            $tax_per = 0;
            $service_tax_amount = 0;
            $tax_name = 'NA';
            //Markup Tax
            $markup_tax_amount = 0;
            $markup_tax_name = 'NA';
            $markup = number_format(0,2);
            //Taxable amount
            $tax_total += $row_query['service_tax_subtotal'];
            $yr = explode("-",$row_query['receipt_date']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, $count++)
            ->setCellValue('C'.$row_count, $sq_income_type_info['ledger_name'])
            ->setCellValue('D'.$row_count, $hsn_code)
            ->setCellValue('E'.$row_count, $row_query['receipt_from'])
            ->setCellValue('F'.$row_count, 'NA')
            ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
            ->setCellValue('H'.$row_count, get_other_income_payment_id($row_query['income_id'],$yr[0]))
            ->setCellValue('I'.$row_count, get_date_user($row_query['receipt_date']))
            ->setCellValue('J'.$row_count, 'Unregistered')
            ->setCellValue('K'.$row_count, 'NA')
            ->setCellValue('L'.$row_count, $row_query['total_fee'])
            ->setCellValue('M'.$row_count, number_format($taxable_amount,2))
            ->setCellValue('N'.$row_count, $tax_name)
            ->setCellValue('O'.$row_count, number_format($row_query['service_tax_subtotal'],2))
            ->setCellValue('P'.$row_count, $markup)
            ->setCellValue('Q'.$row_count, $markup_tax_name)
            ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
            ->setCellValue('S'.$row_count,'0.00')
            ->setCellValue('T'.$row_count,'0.00')
            ->setCellValue('U'.$row_count, '')
            ->setCellValue('V'.$row_count, '');
  
  
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      $row_count++;
}
//B2C Booking
$query = "select * from b2c_sale where 1";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and created_at between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	if($row_query['status'] != 'Cancel')
	{
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		if($row_query['service'] == 'Holiday'){
			$service = 'Package Tour';
		}else{
			$service = $row_query['service'];
		}
		$hsn_code = get_service_info($service);
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$costing_data = json_decode($row_query['costing_data']);

		$total_cost = $costing_data[0]->total_cost;
		$total_tax = $costing_data[0]->total_tax;
		$net_total = $costing_data[0]->net_total;
		$taxes = explode(',',$total_tax);
		$tax_amount = 0;
		$tax_string = '';
		for($i=0; $i<sizeof($taxes);$i++){
			$single_tax = explode(':',$taxes[$i]);
			$tax_amount += floatval($single_tax[1]);
			$temp_tax = explode(' ',$single_tax[1]);
			$tax_string .= $single_tax[0].$temp_tax[1];
		}
		$grand_total = $costing_data[0]->grand_total;
		$coupon_amount = $costing_data[0]->coupon_amount;
		$coupon_amount = ($coupon_amount!='')?$coupon_amount:0;
		$net_total = $costing_data[0]->net_total;
		$markup_tax_amount = 0;

		//Taxable amount
		$taxable_amount = ($tax_per!=0)?($service_tax_amount / $tax_per) * 100:0;
		$tax_total += $tax_amount;
		$markup_tax_total += $markup_tax_amount;

		$yr = explode("-",$row_query['created_at']);

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2C Booking (".$row_query['service'].')')
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2c_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, $net_total)
        ->setCellValue('M'.$row_count, number_format($total_cost,2))
        ->setCellValue('N'.$row_count, $tax_string)
        ->setCellValue('O'.$row_count, number_format($tax_amount,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
    $row_count++;
  }
	if($row_query['status'] == 'Cancel')
	{
		$yr = explode("-",$row_query['created_at']);
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		if($row_query['service'] == 'Holiday'){
			$service = 'Package Tour';
		}else{
			$service = $row_query['service'];
		}
		$hsn_code = get_service_info($service);
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$costing_data = json_decode($row_query['costing_data']);

		$total_cost = $row_query['cancel_amount'];
		$net_total = $costing_data[0]->net_total;
		//Service tax
		$tax_per = 0;
		$tax_amount = 0;
		$tax_name = 'NA';
		if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = explode(':',$service_tax_subtotal1[$i]);
				$tax_amount +=  $service_tax[2];
				$tax_name .= $service_tax[0] . $service_tax[1].' ';
				$tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
			}
		}
		$markup_tax_amount = 0;

		$tax_total += $tax_amount;
		$markup_tax_total += $markup_tax_amount;

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2C Booking (".$row_query['service'].')')
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2c_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, $net_total)
        ->setCellValue('M'.$row_count, number_format($total_cost,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($tax_amount,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format($markup_tax_amount,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
    $row_count++;
  }
}

//B2B Booking
global $currency;
$sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
$to_currency_rate = $sq_to['currency_rate'];

$query = "select * from b2b_booking_master where 1";
if($from_date !='' && $to_date != ''){
	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and DATE(created_at) between '$from_date' and '$to_date' ";
}
$query .= " order by booking_id desc";
$sq_query = mysqlQuery($query);
while($row_query = mysqli_fetch_assoc($sq_query))
{
	if($row_query['status'] != 'Cancel')
	{
		$yr = explode("-",$row_query['created_at']);
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$cart_checkout_data = json_decode($row_query['cart_checkout_data']);
		$traveller_details = ($row_query['traveller_details']!='' && $row_query['traveller_details']!='null')?json_decode($row_query['traveller_details']):[];

		$hotel_list_arr = array();
		$transfer_list_arr = array();
		$activity_list_arr = array();
		$tours_list_arr = array();
		$ferry_list_arr = array();
    $group_list_arr = array();
		for($i=0;$i<sizeof($cart_checkout_data);$i++){
			if($cart_checkout_data[$i]->service->name == 'Hotel'){
				array_push($hotel_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Transfer'){
				array_push($transfer_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Activity'){
				array_push($activity_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
				array_push($tours_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Group Tours'){
				array_push($group_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Ferry'){
				array_push($ferry_list_arr,$cart_checkout_data[$i]);
			}
		}
		// Hotel
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($hotel_list_arr)>0){

			for($i=0;$i<sizeof($hotel_list_arr);$i++){
				$hsn_code = get_service_info('Hotel / Accommodation');
				$tax_arr = explode(',',$hotel_list_arr[$i]->service->hotel_arr->tax);
				for($j=0;$j<sizeof($hotel_list_arr[$i]->service->item_arr);$j++){
					
					$room_types = explode('-',$hotel_list_arr[$i]->service->item_arr[$j]);
					$room_no = $room_types[0];
					$room_cost = $room_types[2];
					$h_currency_id = $room_types[3];
          $tax_name = '';
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].')';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
					$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
			$tax_total += $tax_amount1;

      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2B Booking (Hotel)")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2b_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, number_format($total_amount1,2))
        ->setCellValue('M'.$row_count, number_format($room_cost1,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($tax_amount1,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format(0,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      $row_count++;
    }
		// Activity
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($activity_list_arr)>0){

			$hsn_code = get_service_info('Excursion');
			for($i=0;$i<sizeof($activity_list_arr);$i++){

				$tax_amount = 0;	
				$tax_arr = explode(',',$activity_list_arr[$i]->service->service_arr[0]->taxation);
				$transfer_types = explode('-',$activity_list_arr[$i]->service->service_arr[0]->transfer_type);
				$transfer = $transfer_types[0];
				$room_cost = $transfer_types[1];
				$h_currency_id = $transfer_types[2];
				$tax_name = '';
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] === "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							$tax_name .= $tax_arr2[0] .' ('.$tax_arr2[1].')';
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
				$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
			$tax_total += $tax_amount1;

      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2B Booking (Activity)")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2b_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, number_format($total_amount1,2))
        ->setCellValue('M'.$row_count, number_format($room_cost1,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($tax_amount1,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format(0,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      $row_count++;
    }
		// Transfer
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($transfer_list_arr)>0){

			$hsn_code = get_service_info('Car Rental');
			for($i=0;$i<sizeof($transfer_list_arr);$i++){

				$services = ($transfer_list_arr[$i]->service!='') ? $transfer_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){
					$tax_name = '';
					$tax_arr = explode(',',$services->service_arr[$j]->taxation);
					$transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
					$room_cost = $transfer_cost[0];
					$h_currency_id = $transfer_cost[1];
					$tax_amount = 0;
					
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
					$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
			$tax_total += $tax_amount1;

      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2B Booking (Transfer)")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2b_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, number_format($total_amount1,2))
        ->setCellValue('M'.$row_count, number_format($room_cost1,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($tax_amount1,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format(0,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      $row_count++;
    }
		// Holiday
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($tours_list_arr)>0){

			$hsn_code = get_service_info('Package Tour');
			for($i=0;$i<sizeof($tours_list_arr);$i++){
				$tax_name = '';
				$tax_amount = 0;
				$tax_arr = explode(',',$tours_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$tours_list_arr[$i]->service->service_arr[0]->package_type);
				$room_cost = $package_item[1];
				$h_currency_id = $package_item[2];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
				$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
			$tax_total += $tax_amount1;

      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2B Booking (Holiday)")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2b_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, number_format($total_amount1,2))
        ->setCellValue('M'.$row_count, number_format($room_cost1,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($tax_amount1,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format(0,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      $row_count++;
    }
		// Ferry
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($ferry_list_arr)>0){

			$hsn_code = get_service_info('Cruise/Ferry');
			for($i=0;$i<sizeof($ferry_list_arr);$i++){

				$tax_amount = 0;
				$tax_arr = explode(',',$ferry_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$ferry_list_arr[$i]->service->service_arr[0]->total_cost);
				$room_cost = $package_item[0];
				$h_currency_id = $package_item[1];
				$tax_name = '';
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
						$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
						}else{
						$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
				$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
			$tax_total += $tax_amount1;

      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2B Booking (Ferry)")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2b_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, number_format($total_amount1,2))
        ->setCellValue('M'.$row_count, number_format($room_cost1,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($tax_amount1,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format(0,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      $row_count++;
    }
		// Group Tour
		$room_cost1 = 0;
		$tax_amount1 = 0;
		$total_amount1 = 0;
		if(sizeof($group_list_arr)>0){

			$hsn_code = get_service_info('Group Tour');
			for($i=0;$i<sizeof($group_list_arr);$i++){

				$services = isset($group_list_arr[$i]->service) ? $group_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){

					$tax_arr = explode(',',$group_list_arr[$i]->service->service_arr[$j]->taxation);
					$room_cost = $group_list_arr[$i]->service->service_arr[$j]->total_cost;
					$h_currency_id = $group_list_arr[$i]->service->service_arr[$j]->currency_id;
					$tax_name = '';
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + $tax_arr2[1];
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$room_cost1 += ($from_currency_rate / $to_currency_rate * $room_cost);
					$tax_amount1 += ($from_currency_rate / $to_currency_rate * $tax_amount);
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
			$tax_total += $tax_amount1;

      $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, "B2B Booking (Group Tour)")
        ->setCellValue('D'.$row_count, $hsn_code)
        ->setCellValue('E'.$row_count, $cust_name)
        ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
        ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
        ->setCellValue('H'.$row_count, get_b2b_booking_id($row_query['booking_id'],$yr[0]))
        ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
        ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
        ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
        ->setCellValue('L'.$row_count, number_format($total_amount1,2))
        ->setCellValue('M'.$row_count, number_format($room_cost1,2))
        ->setCellValue('N'.$row_count, $tax_name)
        ->setCellValue('O'.$row_count, number_format($tax_amount1,2))
        ->setCellValue('P'.$row_count, 'NA')
        ->setCellValue('Q'.$row_count, 'NA')
        ->setCellValue('R'.$row_count, number_format(0,2))
        ->setCellValue('S'.$row_count,'0.00')
        ->setCellValue('T'.$row_count,'0.00')
        ->setCellValue('U'.$row_count, '')
        ->setCellValue('V'.$row_count, '');


      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
      $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
      $row_count++;
    }
  }
	if($row_query['status'] == 'Cancel')
	{
		$yr = explode("-",$row_query['created_at']);
		$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_query[customer_id]'"));
		if($sq_cust['type'] == 'Corporate'||$sq_cust['type'] == 'B2B')
			$cust_name = $sq_cust['company_name'];
		else
			$cust_name = $sq_cust['first_name'].' '.$sq_cust['last_name'];
		$sq_state = mysqli_fetch_assoc(mysqlQuery("select * from state_master where id='$sq_cust[state_id]'"));
		
		$cart_checkout_data = json_decode($row_query['cart_checkout_data']);
		$traveller_details = ($row_query['traveller_details']!='' && $row_query['traveller_details']!='null')?json_decode($row_query['traveller_details']):[];

		
		//Service tax
		$tax_per = 0;
		$cancel_tax_amount = 0;
		$tax_name = 'NA';
		if($row_query['tax_amount'] !== 0.00 && ($row_query['tax_amount']) !== ''){
			$service_tax_subtotal1 = explode(',',$row_query['tax_amount']);
			$tax_name = '';
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = explode(':',$service_tax_subtotal1[$i]);
				$cancel_tax_amount +=  $service_tax[2];
				$tax_name .= $service_tax[0] . $service_tax[1].' ';
				$tax_per += str_replace( array('(',')', '%'),'', $service_tax[1]);
			}
		}
		$cancel_amount = $row_query['cancel_amount'];
		$markup_tax_amount = 0;
		$tax_total += $cancel_tax_amount;

		$hotel_list_arr = array();
		$transfer_list_arr = array();
		$activity_list_arr = array();
		$tours_list_arr = array();
		$ferry_list_arr = array();
		$group_list_arr = array();
		for($i=0;$i<sizeof($cart_checkout_data);$i++){
			if($cart_checkout_data[$i]->service->name == 'Hotel'){
				array_push($hotel_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Transfer'){
				array_push($transfer_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Activity'){
				array_push($activity_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Combo Tours'){
				array_push($tours_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Group Tours'){
				array_push($group_list_arr,$cart_checkout_data[$i]);
			}
			if($cart_checkout_data[$i]->service->name == 'Ferry'){
				array_push($ferry_list_arr,$cart_checkout_data[$i]);
			}
		}
		$total_amount1 = 0;
		// Hotel
		if(sizeof($hotel_list_arr)>0){

			for($i=0;$i<sizeof($hotel_list_arr);$i++){
				$hsn_code = get_service_info('Hotel / Accommodation');
				$tax_arr = explode(',',$hotel_list_arr[$i]->service->hotel_arr->tax);
				for($j=0;$j<sizeof($hotel_list_arr[$i]->service->item_arr);$j++){
					
					$room_types = explode('-',$hotel_list_arr[$i]->service->item_arr[$j]);
					$room_no = $room_types[0];
					$room_cost = $room_types[2];
					$h_currency_id = $room_types[3];
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
		}
		// Activity
		if(sizeof($activity_list_arr)>0){

			$hsn_code = get_service_info('Excursion');
			for($i=0;$i<sizeof($activity_list_arr);$i++){

				$tax_amount = 0;	
				$tax_arr = explode(',',$activity_list_arr[$i]->service->service_arr[0]->taxation);
				$transfer_types = explode('-',$activity_list_arr[$i]->service->service_arr[0]->transfer_type);
				$transfer = $transfer_types[0];
				$room_cost = $transfer_types[1];
				$h_currency_id = $transfer_types[2];
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] === "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
		}
		// Transfer
		if(sizeof($transfer_list_arr)>0){

			$hsn_code = get_service_info('Car Rental');
			for($i=0;$i<sizeof($transfer_list_arr);$i++){

				$services = ($transfer_list_arr[$i]->service!='') ? $transfer_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){
					$tax_arr = explode(',',$services->service_arr[$j]->taxation);
					$transfer_cost = explode('-',$services->service_arr[$j]->transfer_cost);
					$room_cost = $transfer_cost[0];
					$h_currency_id = $transfer_cost[1];
					$tax_amount = 0;
					
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
							}else{
								$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
		}
		// Combo Tour
		if(sizeof($tours_list_arr)>0){

			$hsn_code = get_service_info('Package Tour');
			for($i=0;$i<sizeof($tours_list_arr);$i++){
				$tax_amount = 0;
				$tax_arr = explode(',',$tours_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$tours_list_arr[$i]->service->service_arr[0]->package_type);
				$room_cost = $package_item[1];
				$h_currency_id = $package_item[2];
				
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
							$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						}else{
							$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
		}
		// Ferry
		if(sizeof($ferry_list_arr)>0){

			$hsn_code = get_service_info('Cruise/Ferry');
			for($i=0;$i<sizeof($ferry_list_arr);$i++){

				$tax_amount = 0;
				$tax_arr = explode(',',$ferry_list_arr[$i]->service->service_arr[0]->taxation);
				$package_item = explode('-',$ferry_list_arr[$i]->service->service_arr[0]->total_cost);
				$room_cost = $package_item[0];
				$h_currency_id = $package_item[1];
				$tax_arr1 = explode('+',$tax_arr[0]);
				for($t=0;$t<sizeof($tax_arr1);$t++){
					if($tax_arr1[$t]!=''){
						$tax_arr2 = explode(':',$tax_arr1[$t]);
						if($tax_arr2[2] == "Percentage"){
						$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
						}else{
						$tax_amount = $tax_amount + ($room_cost +$tax_arr2[1]);
						}
					}
				}
				$total_amount = $room_cost + $tax_amount;
				//Convert into default currency
				$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
				$from_currency_rate = $sq_from['currency_rate'];
				$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
			}
		}
		// Group Tour
		if(sizeof($group_list_arr)>0){

			$hsn_code = get_service_info('Group Tour');
			for($i=0;$i<sizeof($group_list_arr);$i++){

				$services = isset($group_list_arr[$i]->service) ? $group_list_arr[$i]->service : [];
				for($j=0;$j<count(array($services));$j++){

					$tax_arr = explode(',',$group_list_arr[$i]->service->service_arr[$j]->taxation);
					$room_cost = $group_list_arr[$i]->service->service_arr[$j]->total_cost;
					$h_currency_id = $group_list_arr[$i]->service->service_arr[$j]->currency_id;
					$tax_name = '';
					$tax_amount = 0;
					$tax_arr1 = explode('+',$tax_arr[0]);
					for($t=0;$t<sizeof($tax_arr1);$t++){
						if($tax_arr1[$t]!=''){
							$tax_arr2 = explode(':',$tax_arr1[$t]);
							if($tax_arr2[2] == "Percentage"){
								$tax_amount = $tax_amount + ($room_cost * $tax_arr2[1] / 100);
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].'%) ';
							}else{
								$tax_amount = $tax_amount + $tax_arr2[1];
								$tax_name .= $tax_arr2[0] . ' ('.$tax_arr2[1].') ';
							}
						}
					}
					$total_amount = $room_cost + $tax_amount;
					//Convert into default currency
					$sq_from = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$h_currency_id'"));
					$from_currency_rate = $sq_from['currency_rate'];
					$total_amount1 += ($from_currency_rate / $to_currency_rate * $total_amount);
				}
			}
    }
    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('B'.$row_count, $count++)
      ->setCellValue('C'.$row_count, "B2B Booking")
      ->setCellValue('D'.$row_count, $hsn_code)
      ->setCellValue('E'.$row_count, $cust_name)
      ->setCellValue('F'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'NA' : $sq_cust['service_tax_no'])
      ->setCellValue('G'.$row_count, ($sq_supply['state_name'] == '') ? 'NA' : $sq_supply['state_name'])
      ->setCellValue('H'.$row_count, get_b2b_booking_id($row_query['booking_id'],$yr[0]))
      ->setCellValue('I'.$row_count, get_date_user($row_query['created_at']))
      ->setCellValue('J'.$row_count, ($sq_cust['service_tax_no'] == '') ? 'Unregistered' : 'Registered')
      ->setCellValue('K'.$row_count, ($sq_state['state_name'] == '') ? 'NA' : $sq_state['state_name'])
      ->setCellValue('L'.$row_count, number_format($total_amount1,2))
      ->setCellValue('M'.$row_count, number_format($cancel_amount,2))
      ->setCellValue('N'.$row_count, $tax_name)
      ->setCellValue('O'.$row_count, number_format($cancel_tax_amount,2))
      ->setCellValue('P'.$row_count, 'NA')
      ->setCellValue('Q'.$row_count, 'NA')
      ->setCellValue('R'.$row_count, number_format(0,2))
      ->setCellValue('S'.$row_count,'0.00')
      ->setCellValue('T'.$row_count,'0.00')
      ->setCellValue('U'.$row_count, '')
      ->setCellValue('V'.$row_count, '');


    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);       
    $row_count++;
  }
}
  $objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('B'.$row_count,'' )
    ->setCellValue('C'.$row_count, '')
    ->setCellValue('D'.$row_count, '')
    ->setCellValue('E'.$row_count,'' )
    ->setCellValue('F'.$row_count, '')
    ->setCellValue('G'.$row_count,'' )
    ->setCellValue('H'.$row_count,'' )
    ->setCellValue('I'.$row_count,'' )
    ->setCellValue('J'.$row_count,'' )
    ->setCellValue('K'.$row_count,'' )
    ->setCellValue('L'.$row_count, '')
    ->setCellValue('M'.$row_count,'' )
    ->setCellValue('N'.$row_count,'' )
    ->setCellValue('O'.$row_count,'Total TAX :'.number_format($tax_total,2))
    ->setCellValue('P'.$row_count,'' )
    ->setCellValue('Q'.$row_count,'')
    ->setCellValue('R'.$row_count,'Total Markup TAX :'.number_format($markup_tax_total,2))
    ->setCellValue('S'.$row_count,'')
    ->setCellValue('T'.$row_count,'' )
    ->setCellValue('U'.$row_count,'')
    ->setCellValue('V'.$row_count, '');

  $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($header_style_Array);
  $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':V'.$row_count)->applyFromArray($borderArray);

//////////////////////////****************Content End**************////////////////////////////////
  

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');


for($col = 'A'; $col !== 'N'; $col++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="GST-R1 Report('.date('d-m-Y H:i').').xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

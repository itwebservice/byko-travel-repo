<?php
include "../../../../../../model/model.php";
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
//////////////////////////****************Content start**************////////////////////////////////
$tour_id = $_GET['tour_id'];
$tour_group_id = $_GET['tour_group_id'];

$total_sale = 0; $total_purchase = 0;

//Sale
$q1 = mysqlQuery("select * from tourwise_traveler_details where tour_id='$tour_id' and tour_group_id ='$tour_group_id' and delete_status='0' ");
while($tourwise_details = mysqli_fetch_assoc($q1)){

	$pass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from  travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]'"));
	$cancelpass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from  travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]' and status='Cancel'"));

	if($pass_count != $cancelpass_count){

		$sale_amount = $tourwise_details['net_total'];
		$service_tax_amount = 0;
		if($tourwise_details['service_tax'] !== 0.00 && ($tourwise_details['service_tax']) !== ''){
			$service_tax_subtotal1 = explode(',',$tourwise_details['service_tax']);
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = explode(':',$service_tax_subtotal1[$i]);
				$service_tax_amount +=  $service_tax[2];
			}
		}
		$sale_amount -= $service_tax_amount;
		$total_sale += $sale_amount;
	}
}

// Purchase
$sq_purchase = mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='Group Tour' and estimate_type_id ='$tour_group_id' and delete_status='0'");
while($row_purchase = mysqli_fetch_assoc($sq_purchase)){
    //Service Tax 
    $service_tax_amount = 0;
    if($row_purchase['service_tax_subtotal'] !== 0.00 && ($row_purchase['service_tax_subtotal']) !== ''){
        $service_tax_subtotal1 = explode(',',$row_purchase['service_tax_subtotal']);
        for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
        $service_tax = explode(':',$service_tax_subtotal1[$i]);
        $service_tax_amount +=  $service_tax[2];
        }
    }
    if($row_purchase['purchase_return'] == 0){
        $total_purchase += $row_purchase['net_total'];
    }
    else if($row_purchase['purchase_return'] == 2){
        $cancel_estimate = json_decode($row_purchase['cancel_estimate']);
        $p_purchase = ($row_purchase['net_total'] - floatval($cancel_estimate[0]->net_total) - floatval($cancel_estimate[0]->service_tax_subtotal));
        $total_purchase += $p_purchase;
    }
    $total_purchase -= $service_tax_amount;
}

//Other Expense
$sq_other_purchase = mysqli_fetch_assoc(mysqlQuery("select sum(amount) as amount_total from group_tour_estimate_expense where tour_id='$tour_id' and tour_group_id ='$tour_group_id'"));
$total_purchase += $sq_other_purchase['amount_total'];

//Revenue & Expenses
$result = $total_sale - $total_purchase;

if($total_sale > $total_purchase){
  $var = 'Total Profit(%)';
}else{
  $var = 'Total Loss(%)';
}
$profit_loss = $total_sale - $total_purchase;

$profit_loss_per = 0;
$profit_amount = $total_sale - $total_purchase;
$profit_loss_per = ($total_sale>0) ?($profit_amount / $total_sale) * 100 : 0;
$profit_loss_per = round($profit_loss_per, 2);

$sq_pcount = mysqli_num_rows(mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='Group Tour' and estimate_type_id ='$tour_group_id' and status!='Cancel' and delete_status='0'"));
$sq_count = mysqli_num_rows(mysqlQuery("select * from group_tour_estimate_expense where tour_id='$tour_id' and tour_group_id ='$tour_group_id'"));

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B2', 'Report Name')
            ->setCellValue('C2', 'Group Tour Expense');

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);    

$count = 0;
$row_count = 4;

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Total Sale")
        ->setCellValue('C'.$row_count, number_format($total_sale,2));

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($borderArray);    

$row_count++;
$row_count++;

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Total Purchase")
        ->setCellValue('C'.$row_count, number_format($total_purchase,2));

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($borderArray);    

$row_count ++;
$row_count++;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $var)
        ->setCellValue('C'.$row_count, number_format($profit_loss,2).'('.$profit_loss_per.'%)');

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($borderArray);

$row_count++;
$row_count++;


$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Sale History");
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':B'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':B'.$row_count)->applyFromArray($borderArray); 

$row_count++;

//////////Sale Start//////////////////   
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Sr. No")
        ->setCellValue('C'.$row_count, "Booking ID")
        ->setCellValue('D'.$row_count, "Booking Date")
        ->setCellValue('E'.$row_count, "Sale_Amount")
        ->setCellValue('F'.$row_count, "User Name");

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($borderArray);
$row_count++;

$count = 1;
$q1 = mysqlQuery("select * from tourwise_traveler_details where tour_id='$tour_id' and tour_group_id ='$tour_group_id' and delete_status='0' ");
while($tourwise_details = mysqli_fetch_assoc($q1)){

	$pass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]'"));
	$cancelpass_count = mysqli_num_rows(mysqlQuery("select traveler_group_id from travelers_details where traveler_group_id='$tourwise_details[traveler_group_id]' and status='Cancel'"));
	$sq_emp = mysqli_fetch_assoc(mysqlQuery("select first_name,last_name from emp_master where emp_id='$tourwise_details[emp_id]'"));
	$emp_name = $sq_emp['first_name'].' '.$sq_emp['last_name'];

	// if($pass_count != $cancelpass_count && $tourwise_details['tour_group_status'] != 'Cancel'){
		$sale_amount = $tourwise_details['net_total'];
		$service_tax_amount = 0;
		if($tourwise_details['service_tax'] !== 0.00 && ($tourwise_details['service_tax']) !== ''){
			$service_tax_subtotal1 = explode(',',$tourwise_details['service_tax']);
			for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
				$service_tax = explode(':',$service_tax_subtotal1[$i]);
				$service_tax_amount +=  $service_tax[2];
			}
		}
		$sale_amount -= $service_tax_amount;

		$date = $tourwise_details['form_date'];
		$yr = explode("-", $date);
		$year = $yr[0];

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, get_group_booking_id($tourwise_details['id'],$year))
        ->setCellValue('D'.$row_count, get_date_user($tourwise_details['form_date']) )
        ->setCellValue('E'.$row_count, number_format($sale_amount,2))
        ->setCellValue('F'.$row_count, $emp_name);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($borderArray); 
        $row_count++;
    // }
}

//////////Sale End//////////////////   

//////////Purchase Details Start/////////////////
if($sq_pcount!=0){ 
    $row_count++;
    $row_count++;
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, "Purchase History");
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':B'.$row_count)->applyFromArray($header_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':B'.$row_count)->applyFromArray($borderArray); 

    $row_count++;
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, "Sr. No")
            ->setCellValue('C'.$row_count, "Purchase Date")
            ->setCellValue('D'.$row_count, "Supplier Type")
            ->setCellValue('E'.$row_count, "Supplier Name")
            ->setCellValue('F'.$row_count, "Purchase Amount");

    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($header_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($borderArray);          
    $count = 1;
    $sq_query = mysqlQuery("select * from vendor_estimate where estimate_type='Group Tour' and estimate_type_id ='$tour_group_id' and status!='Cancel' and delete_status='0'");
    while($row_query = mysqli_fetch_assoc($sq_query))
    { 
        $vendor_name = get_vendor_name_report($row_query['vendor_type'],$row_query['vendor_type_id']);
        $total_purchase1 = 0;
        //Service Tax 
        $service_tax_amount = 0;
        if($row_query['service_tax_subtotal'] !== 0.00 && ($row_query['service_tax_subtotal']) !== ''){
            $service_tax_subtotal1 = explode(',',$row_query['service_tax_subtotal']);
            for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
                $service_tax = explode(':',$service_tax_subtotal1[$i]);
                $service_tax_amount +=  $service_tax[2];
            }
        }
        if($row_query['purchase_return'] == 0){
            $total_purchase1 += $row_query['net_total'];
        }
        else if($row_query['purchase_return'] == 2){
            $cancel_estimate = json_decode($row_query['cancel_estimate']);
            $p_purchase = ($row_query['net_total'] - floatval($cancel_estimate[0]->net_total) - floatval($cancel_estimate[0]->service_tax_subtotal));
            $total_purchase1 += $p_purchase;
        }
        $row_count++;
        if($row_query['net_total'] != '0'){
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B'.$row_count, $count++)
                ->setCellValue('C'.$row_count, get_date_user($row_query['purchase_date']))
                ->setCellValue('D'.$row_count, $row_query['vendor_type'])
                ->setCellValue('E'.$row_count, $vendor_name)
                ->setCellValue('F'.$row_count, number_format($total_purchase1-$service_tax_amount,2));

            $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($content_style_Array);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':F'.$row_count)->applyFromArray($borderArray);    
        }
    } 
}
//////////Purchase Details End/////////////////

//////////Other Expense Start/////////////////
if($sq_count!=0){
    $row_count++;
    $row_count++;
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, "Other Expense");
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':B'.$row_count)->applyFromArray($header_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':B'.$row_count)->applyFromArray($borderArray); 

    $row_count++;
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$row_count, "Sr. No")
            ->setCellValue('C'.$row_count, "Expense Name")
            ->setCellValue('D'.$row_count, "Amount");

    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($header_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($borderArray);          
    $count = 1;
    $sq_query = mysqlQuery("select * from group_tour_estimate_expense where tour_id='$tour_id' and tour_group_id ='$tour_group_id'");
    while($sq_other_purchase = mysqli_fetch_assoc($sq_query)){	
        if($sq_other_purchase['amount'] != '0'){
            $row_count++;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B'.$row_count, $count++)
                ->setCellValue('C'.$row_count, $sq_other_purchase['expense_name'])
                ->setCellValue('D'.$row_count, number_format($sq_other_purchase['amount'],2));

            $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($content_style_Array);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($borderArray);    
        }
    }
}
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
header('Content-Disposition: attachment;filename="Group Tour Expense('.date('d-m-Y H:i').').xls"');
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


// Purchase
$sq_purchase = mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='Group Tour' and estimate_type_id ='$tour_group_id' and status!='Cancel' and delete_status='0'");
while($row_purchase = mysqli_fetch_assoc($sq_purchase)){
  $total_purchase += $row_purchase['net_total'] ;
}

//Other Expense
$sq_other_purchase = mysqli_fetch_assoc(mysqlQuery("select * from group_tour_estimate_expense where tour_id='$tour_id' and tour_group_id ='$tour_group_id'"));
$total_purchase += $sq_other_purchase['amount'];


//Revenue & Expenses
$result = $total_sale - $total_purchase;

if($total_sale > $total_purchase){
  $var = 'Total Profit(%)';
}else{
  $var = 'Total Loss(%)';
}
$profit_loss = $total_sale - $total_purchase;

$profit_loss_per = 0;
$profit_amount = $total_sale - $total_purchase;
$profit_loss_per = ($profit_amount / $total_sale) * 100;
$profit_loss_per = round($profit_loss_per, 2);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B2', 'Report Name')
            ->setCellValue('C2', 'Group Tour Expense');

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);    

$count = 0;
$row_count = 4;

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Total Sale")
        ->setCellValue('C'.$row_count, number_format($total_sale,2));

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($borderArray);    
  
$row_count++;
$row_count++;

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Total Purchase")
        ->setCellValue('C'.$row_count, number_format($total_purchase,2));

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($borderArray);    
  

$row_count ++;
$row_count++;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $var)
        ->setCellValue('C'.$row_count, number_format($profit_loss,2).'('.$profit_loss_per.'%)');

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':C'.$row_count)->applyFromArray($borderArray);

$row_count++;
$row_count++;


$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('C'.$row_count, "Purchase History");
$objPHPExcel->getActiveSheet()->getStyle('C'.$row_count.':C'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('C'.$row_count.':C'.$row_count)->applyFromArray($borderArray); 

$row_count++;

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Sr. No")
        ->setCellValue('C'.$row_count, "Purchase For")
        ->setCellValue('D'.$row_count, "Purchase Amount");

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($borderArray);                    

$count = 1;
$sq_query = mysqlQuery("select * from vendor_estimate where status!='Cancel' and estimate_type='Group Tour' and estimate_type_id ='$tour_group_id' and status!='Cancel' and delete_status='0'");
while($row_query = mysqli_fetch_assoc($sq_query))
{ 
  $row_count++;
  if($row_query['net_total'] != '0'){
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, $row_query['vendor_type'])
        ->setCellValue('D'.$row_count, number_format($row_query['net_total'],2));

  $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($content_style_Array);
  $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($borderArray);    

  }
}

$other_expense = ($sq_other_purchase['amount'] == '')?'0.00':number_format($sq_other_purchase['amount'],2);

$row_count++;
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('B'.$row_count, $count++)
    ->setCellValue('C'.$row_count, "Other Expenses")
    ->setCellValue('D'.$row_count, $sq_other_purchase['expense_name'].'('.$other_expense.')');

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($content_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':D'.$row_count)->applyFromArray($borderArray);    
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
header('Content-Disposition: attachment;filename="Group Tour Expense('.date('d-m-Y H:i').').xls"');
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

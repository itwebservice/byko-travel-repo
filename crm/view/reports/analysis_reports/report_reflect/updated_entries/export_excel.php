<?php
include "../../../../../model/model.php";


/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../../../../../classes/PHPExcel-1.8/Classes/PHPExcel.php';

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
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];









// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B2', 'Report Name')
            ->setCellValue('C2', 'Deleted Entries');

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);    

$count = 0;
$row_count = 4;







 
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Sr. No")
        ->setCellValue('C'.$row_count, "Transaction Date/Time")
        ->setCellValue('D'.$row_count, "Transaction Type")
        ->setCellValue('E'.$row_count, "Service_Name")
        ->setCellValue('F'.$row_count, "Transaction Id")
        ->setCellValue('G'.$row_count, "Name")
        ->setCellValue('H'.$row_count, "Amount");

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':H'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':H'.$row_count)->applyFromArray($borderArray);
$row_count++;

$count = 1;

$query = "SELECT * FROM `deleted_entries_master` where 1 ";
if($from_date != '' && $to_date != ''){

	$from_date = get_date_db($from_date);
	$to_date = get_date_db($to_date);
	$query .= " and DATE(deleted_at) between '$from_date' and '$to_date'";
}
$query .= " ORDER BY `entry_id` DESC";

$sq_query1 = mysqlQuery($query);
while($sq_query = mysqli_fetch_assoc($sq_query1)){


	

	

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, $count++)
        ->setCellValue('C'.$row_count, get_datetime_user($sq_query['deleted_at']))
        ->setCellValue('D'.$row_count, $sq_query['trans_type'])
        ->setCellValue('E'.$row_count,  $sq_query['module_name'])
        ->setCellValue('F'.$row_count, $sq_query['long_id'])
        ->setCellValue('G'.$row_count, $sq_query['guest_name'])
        ->setCellValue('H'.$row_count, number_format($sq_query['amount'],2));
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':H'.$row_count)->applyFromArray($content_style_Array);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':H'.$row_count)->applyFromArray($borderArray); 
        $row_count++;
    
}




// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');


for($col = 'A'; $col !== 'N'; $col++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Deleted Entries('.date('d-m-Y H:i').').xls"');
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

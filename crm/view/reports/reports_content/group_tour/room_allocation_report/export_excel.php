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

require_once  '../../../../../classes/PHPExcel-1.8/Classes/PHPExcel.php';



//This function generates the background color

function cellColor($cells, $color)
{

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

    )
);

$table_header_style_Array = array(

    'font'  => array(

        'bold'  => false,

        'color' => array('rgb' => '000000'),

        'size'  => 11,

        'name'  => 'Verdana'

    )
);

$content_style_Array = array(

    'font'  => array(

        'bold'  => false,

        'color' => array('rgb' => '000000'),

        'size'  => 9,

        'name'  => 'Verdana'

    )
);



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



//mainQery

$tour_id = $_GET['tourName'];
$group_id = $_GET['tourDate'];
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_GET['branch_status'];
$count=0;

$query = "select * from tourwise_traveler_details where 1";

if($tour_id!="")
{
	$query .= " and tour_id = '$tour_id'";
}
if($group_id!="")
{
	$query .= " and tour_group_id = '$group_id'";
}
if($branch_id!=""){

	$query .= " and branch_admin_id = '$branch_id'";
}
if($branch_status=='yes' && $role=='Branch Admin'){
    $query .= " and  branch_admin_id = '$branch_admin_id'";
}
 
$sq_tourwise_det = mysqlQuery($query);

//maibnQuery

if (!empty($tour_id)) {

    $tour_id_single_data = mysqli_fetch_assoc(mysqlQuery("select tour_id,tour_name from tour_master where active_flag='Active' and tour_id='$tour_id'"));
}
$group_single_from_to_date = null;

if(!empty($group_id))
{
    $group_id_single_data = mysqli_fetch_assoc(mysqlQuery("select * from tour_groups where group_id='$group_id'"));
    $from_date=$group_id_single_data['from_date'];
    $to_date=$group_id_single_data['to_date'];
    $group_single_from_date=date("d-m-Y", strtotime($from_date));  
    $group_single_to_date=date("d-m-Y", strtotime($to_date)); 
    $group_single_from_to_date = $group_single_from_date.' to '.$group_single_to_date;

}

// Add some data

$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('B2', 'Report Name')
    ->setCellValue('C2', 'Room Allocation Report')
    ->setCellValue('B3', 'Tour Name')
    ->setCellValue('C3', $tour_id_single_data['tour_name'])
   
    ->setCellValue('B4', 'Tour Date')
    ->setCellValue('C4', $group_single_from_to_date);

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);

$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($borderArray);

$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($borderArray);

// global $currency;
// $sq_to = mysqli_fetch_assoc(mysqlQuery("select * from roe_master where currency_id='$currency'"));
// $to_currency_rate = $sq_to['currency_rate'];

// $query = "select * from b2b_booking_master where 1 ";
// if ($customer_id != "") {
//     $query .= " and customer_id='$customer_id' ";
// }
// if ($booking_id != "") {
//     $query .= " and booking_id='$booking_id' ";
// }
// if ($from_date != "" && $to_date != "") {
//     $from_date = get_date_db($from_date);
//     $to_date = get_date_db($to_date);
//     $query .= " and (DATE(created_at)>='$from_date' and DATE(created_at)<='$to_date') ";
// }
// $query .= " order by booking_id desc";
// $sq_customer = mysqlQuery($query);

$count = 0;
$net_total = 0;
$row_count = 8;

$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('B' . $row_count, "Sr.No")
    ->setCellValue('C' . $row_count, "Tour Name")
    ->setCellValue('D' . $row_count, "Tour Date")
    ->setCellValue('E' . $row_count, "Booking Id")
    ->setCellValue('F' . $row_count, "Customer Name")
    ->setCellValue('G' . $row_count, "Total Guest")
    ->setCellValue('H' . $row_count, "Single BedRoom")
    ->setCellValue('I' . $row_count, "Double BedRoom")
    ->setCellValue('J' . $row_count, "Extra Bed");

$objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':J' . $row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':J' . $row_count)->applyFromArray($borderArray);

$row_count++;
$cancel_total = 0;
$balance_total = 0;
$net_total = 0;
while ($row_tourwise_det = mysqli_fetch_assoc($sq_tourwise_det)) {
   //qry
   $pass_count = mysqli_num_rows(mysqlQuery("select * from  travelers_details where traveler_group_id='$row_tourwise_det[id]'"));
   $cancelpass_count = mysqli_num_rows(mysqlQuery("select * from  travelers_details where traveler_group_id='$row_tourwise_det[id]' and status='Cancel'"));
   $bg="";
   if($row_tourwise_det['tour_group_status']=="Cancel"){
       $bg="danger";
   }
   else{
       if($pass_count==$cancelpass_count){
           $bg="danger";
       }
   }

   $count++;
   $date = $row_tourwise_det['form_date'];
   $yr = explode("-", $date);
   $year =$yr[0];

   $sq_total_member_count = mysqli_num_rows(mysqlQuery("select traveler_id from travelers_details where traveler_group_id='$row_tourwise_det[traveler_group_id]' and status!='Cancel'"));

   $tour_name1 = mysqli_fetch_assoc(mysqlQuery("select tour_name from tour_master where tour_id= '$row_tourwise_det[tour_id]'"));
   $tour_name = $tour_name1['tour_name'];
   $tour_group1 = mysqli_fetch_assoc(mysqlQuery("select from_date, to_date from tour_groups where group_id= '$row_tourwise_det[tour_group_id]'"));
   $tour_group = date("d-m-Y", strtotime($tour_group1['from_date']))." to ".date("d-m-Y", strtotime($tour_group1['to_date']));
   $sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$row_tourwise_det[customer_id]'"));
   if ($sq_customer['type'] == 'Corporate' || $sq_customer['type'] == 'B2B') {
       $customer_name = $sq_customer['company_name'];
   } else {
       $customer_name = $sq_customer['first_name'] . ' ' . $sq_customer['last_name'];
   }
   $sq_adjust_with = mysqli_fetch_assoc(mysqlQuery("select first_name, last_name from travelers_details where traveler_id='$row_tourwise_det[s_adjust_with]'"));
   $adjust_with = $sq_adjust_with['first_name']." ".$sq_adjust_with['last_name'];



   //qry




    $objPHPExcel->setActiveSheetIndex(0)

    ->setCellValue('B' . $row_count, $count)
    ->setCellValue('C' . $row_count, $tour_name)
    ->setCellValue('D' . $row_count, $tour_group)
    ->setCellValue('E' . $row_count, get_group_booking_id($row_tourwise_det['id'],$year))
    ->setCellValue('F' . $row_count, $customer_name)
    ->setCellValue('G' . $row_count, $sq_total_member_count)
    ->setCellValue('H' . $row_count, $row_tourwise_det['s_single_bed_room'])
    ->setCellValue('I' . $row_count, $row_tourwise_det['s_double_bed_room'])
    ->setCellValue('J' . $row_count, $row_tourwise_det['s_extra_bed']);

    $objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':J' . $row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':J' . $row_count)->applyFromArray($borderArray);


    $row_count++;
}


//////////////////////////****************Content End**************////////////////////////////////

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Sheet 1');
for ($col = 'A'; $col !== 'N'; $col++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Room Allocation Report(' . date('d-m-Y H:i') . ').xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

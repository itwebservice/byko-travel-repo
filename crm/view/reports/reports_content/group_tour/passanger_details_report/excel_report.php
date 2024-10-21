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
$array_s = array();
$temp_arr = array();
$tour_id = $_GET['tourName'];
$group_id = $_GET['tourDate'];
$id = $_GET['tourBooking'];
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
// $branch_status = $_GET['branch_status'];
$branch_status = 'yes';
$count = 0;
$cancel_count = 0;
$query1 = "select * from travelers_details where 1 and traveler_group_id in(select traveler_group_id from tourwise_traveler_details where delete_status='0')";
if ($id != "") {
    $query1 .= " and traveler_group_id in(select traveler_group_id from tourwise_traveler_details where id='$id') ";
}
if ($branch_id != "") {

    $query1 .= " and traveler_group_id in (select traveler_group_id from tourwise_traveler_details where branch_admin_id = '$branch_id')";
}
if ($branch_status == 'yes' && $role == 'Branch Admin') {
    $query1 .= " and traveler_group_id in (select traveler_group_id from tourwise_traveler_details where branch_admin_id = '$branch_admin_id')";
}
if ($tour_id != '') {
    $query1 .= " and traveler_group_id in(select traveler_group_id from tourwise_traveler_details where tour_id='$tour_id') ";
}
if ($group_id != '') {
    $query1 .= " and traveler_group_id in(select traveler_group_id from tourwise_traveler_details where tour_group_id='$group_id') ";
}

$sq_traveler_det = mysqlQuery($query1);

//maibnQuery

if (!empty($tour_id)) {

    $tour_id_single_data = mysqli_fetch_assoc(mysqlQuery("select tour_id,tour_name from tour_master where active_flag='Active' and tour_id='$tour_id'"));
}
if (!empty($id)) {
    $financial_year_id = $_SESSION['financial_year_id'];
    $query = "select * from tourwise_traveler_details where financial_year_id='$financial_year_id' and id='$id' ";
    include "branchwise_filteration.php";
    $query .= " and tour_group_status != 'Cancel'";
    $query .= " order by id desc";
    $id_single_data = mysqli_fetch_assoc(mysqlQuery($query));
    $date = $id_single_data['form_date'];
    $yr = explode("-", $date);
    $year = $yr[0];
   $booking_id_single =  get_group_booking_id($id_single_data['id'], $year);
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
    ->setCellValue('C2', 'Group Passenger Report')
    ->setCellValue('B3', 'Tour Name')
    ->setCellValue('C3', $tour_id_single_data['tour_name'])
    ->setCellValue('B4', 'Booking Id')
    ->setCellValue('C4', $booking_id_single)
    ->setCellValue('B5', 'Tour Date')
    ->setCellValue('C5', $group_single_from_to_date);

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);

$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($borderArray);

$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($borderArray);

$objPHPExcel->getActiveSheet()->getStyle('B5:C5')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B5:C5')->applyFromArray($borderArray);

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
    ->setCellValue('C' . $row_count, "Passenger Name")
    ->setCellValue('D' . $row_count, "Gender")
    ->setCellValue('E' . $row_count, "Birth Date")
    ->setCellValue('F' . $row_count, "Age");

$objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':F' . $row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':F' . $row_count)->applyFromArray($borderArray);

$row_count++;
$cancel_total = 0;
$balance_total = 0;
$net_total = 0;
while ($row_traveler_det = mysqli_fetch_assoc($sq_traveler_det)) {
    $sq_entry1 = mysqli_fetch_assoc(mysqlQuery("select * from tourwise_traveler_details where traveler_group_id='$row_traveler_det[traveler_group_id]'"));
	if($row_traveler_det['status']=="Cancel"|| $sq_entry1['tour_group_status']=='Cancel') {	
		$cancel_count++;
	}	
    if($row_traveler_det['birth_date']=="") { $birth_date=""; }
	else { $birth_date=date("d-m-Y",strtotime($row_traveler_det['birth_date'])); }
    $objPHPExcel->setActiveSheetIndex(0)

        ->setCellValue('B' . $row_count, ++$count)
    ->setCellValue('C' . $row_count, $row_traveler_det['first_name']." ".$row_traveler_det['last_name'])
    ->setCellValue('D' . $row_count, $row_traveler_det['gender'])
    ->setCellValue('E' . $row_count, $birth_date)
    ->setCellValue('F' . $row_count, $row_traveler_det['age']);

    $objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':F' . $row_count)->applyFromArray($content_style_Array);
    $objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':F' . $row_count)->applyFromArray($borderArray);


    $row_count++;
}
$objPHPExcel->setActiveSheetIndex(0)

    ->setCellValue('B' . $row_count, 'Total Cancelled Passengers:')
    ->setCellValue('C' . $row_count, $cancel_count)
   ;

$objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':C' . $row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B' . $row_count . ':C' . $row_count)->applyFromArray($borderArray);

//////////////////////////****************Content End**************////////////////////////////////

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');
for ($col = 'A'; $col !== 'N'; $col++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Group Passenger Report(' . date('d-m-Y H:i') . ').xls"');
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

<?php
include "../../../model/model.php";
 
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simple to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = 'city_master';
 
// Table's primary key
$primaryKey = 'city_id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'city_id', 'dt' => 0 ),
    array( 'db' => 'city_name',  'dt' => 1 ),
    array( 'db' => 'active_flag',   'dt' => 2 ),
    array(
      'db'        => 'city_id',
      'dt'        => 3,
      'formatter' => function( $d, $row ) {
          return '<a href="javascript:void(0)" data-toggle="tooltip" id="update_city-'.$d.'" onclick="city_master_update_modal(\''.$d.'\')" class="btn btn-info btn-sm" title="Update Details"><i class="fa fa-pencil-square-o"></i></a>';
      }
  ),
);




$filterColumn ="";

if(!empty($_GET['active_flag']))
{
    $filterColumn ="active_flag='".$_GET['active_flag']."'";
}

 
$options = array(
    'where' => $filterColumn,
);
require( '../../../classes/ssp.class.php' );


$data = SSP::simple( $_GET, $table, $primaryKey, $columns,$options);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 

echo json_encode(
    $data
);

?>


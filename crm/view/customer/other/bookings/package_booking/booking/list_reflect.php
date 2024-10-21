<?php
global $currency;
include "../../../../../../model/model.php";
$booking_id = $_POST['booking_id'];
$customer_id = $_SESSION['customer_id'];
$query = "select * from package_tour_booking_master where customer_id='$customer_id' and delete_status='0' ";
if($booking_id!=""){
	$query .=" and booking_id = '$booking_id'";
}
?>
<div class="row mg_tp_20"> <div class="col-md-12"> <div class="table-responsive">

<table class="table table-bordered table-hover bg_white cust_table" id="package_table" style="margin: 20px 0 !important;">
  <thead>
    <tr class="table-heading-row">
      <th>S_No.</th>
      <th>Booking_ID</th>
      <th>Tour_Name</th>
      <th>Tour_Date&nbsp;&nbsp;&nbsp;&nbsp;</th>
      <th>View</th>
      <th class="info">Total_Amount</th>
      <th class="success">Paid_Amount</th>
      <th class="danger">Cancel_Amount</th>
      <th class="warning">Balance</th>
      <th class="text-center">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $count = 0;
    $total_amount = 0;
    $total_paid1 = 0;
    $total_cancel = 0;
    $total_balance = 0;
    $link = '';
    $link1 = '';
    $link2 = '';
    $sq_booking = mysqlQuery($query);
    while($row_booking = mysqli_fetch_assoc($sq_booking)){

      $pass_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_booking[booking_id]'"));
      $cancle_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_booking[booking_id]' and status='Cancel'"));
      if($pass_count==$cancle_count){
          $bg="danger";
      }else{
          $bg="";
      }
      $date = $row_booking['booking_date'];
      $yr = explode("-", $date);
      $year = $yr[0];
      $cancel_est=mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_booking[booking_id]'"));
      $total_paid=mysqli_fetch_assoc(mysqlQuery("select sum(amount) as sum,sum(`credit_charges`) as sumc from package_payment_master where booking_id='$row_booking[booking_id]' and clearance_status!='Pending' AND clearance_status!='Cancelled'"));
      $credit_card_charges = $total_paid['sumc'];
      $paid_amount = $total_paid['sum'];
      $paid_amount = ($paid_amount == '')?'0':$paid_amount;

      $sale_total_amount = $row_booking['net_total'];
      $cancel_est_count=mysqli_num_rows(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_booking[booking_id]'"));
      $cancel_est=mysqli_fetch_assoc(mysqlQuery("select * from package_refund_traveler_estimate where booking_id='$row_booking[booking_id]'"));
      $cancel_amount= ($cancel_est_count > 0) ? $cancel_est['cancel_amount'] : 0;
      //balance
      if ($cancel_est_count > 0) {
        if ($cancel_amount <= $paid_amount) {
          $balance_amount = 0;
        } else {
          $balance_amount =  $cancel_amount - $paid_amount;
        }
      } else {
        $cancel_amount = ($cancel_amount == '') ? '0' : $cancel_amount;
        $balance_amount = $sale_total_amount - $paid_amount;
      }

      $net_total1 = currency_conversion($currency,$row_booking['currency_code'],$row_booking['net_total']+$credit_card_charges);
      $paid_amount1 = currency_conversion($currency,$row_booking['currency_code'],$paid_amount+$credit_card_charges);
      $cancel_amount1 = currency_conversion($currency,$row_booking['currency_code'],$cancel_amount);
      $balance_amount1 = currency_conversion($currency,$row_booking['currency_code'],$balance_amount);

      $net_total1_string = explode(' ',$net_total1);
      $footer_net_total = str_replace(',', '', $net_total1_string[1]);
      $paid_amount1_string = explode(' ',$paid_amount1);
      $footer_paid_amount = str_replace(',', '', $paid_amount1_string[1]);
      $cancel_amount1_string = explode(' ',$cancel_amount1);
      $footer_cancel_amount = str_replace(',', '', $cancel_amount1_string[1]);
      $balance_amount1_string = explode(' ',$balance_amount1);
      $footer_balance_amount = str_replace(',', '', $balance_amount1_string[1]);
      
      //Total
      $total_amount += $footer_net_total;
      $total_paid1 += $footer_paid_amount;
      $total_cancel += $footer_cancel_amount;
      $total_balance += $footer_balance_amount;

      $booking_id = $row_booking['booking_id'];
      if(floatval($balance_amount) <= 0 && $bg == ''){

        $service_voucher = '<button data-toggle="tooltip" title="Download Service Voucher" class="btn btn-info btn-sm" onclick="voucher_modal('.$booking_id.')" id="servoucher_btn-'.$booking_id.'" ><i class="fa fa-print" data-toggle="tooltip"></i></button>';
        if($row_booking['train_upload_ticket']!=""){
          $newUrl = preg_replace('/(\/+)/','/',$row_booking['train_upload_ticket']);
          $newUrl = str_replace("../","", $newUrl);
          $newUrl = BASE_URL.$newUrl;
          $link = '<a href="'.$newUrl .'" class="btn btn-info btn-sm" title="Download Train Ticket" download><i class="fa fa-download"></i></a>';
        }
        $link1 = "";
        if($row_booking['plane_upload_ticket']!=""){
          $newUrl = preg_replace('/(\/+)/','/',$row_booking['plane_upload_ticket']);      
          $newUrl = str_replace("../","", $newUrl);  
          $newUrl = BASE_URL.$newUrl;        
          $link1 = '<a href="'.$newUrl.'" class="btn btn-info btn-sm" title="Download Flight Ticket" download><i class="fa fa-download"></i></a>';
        }
        $link2 = "";
        if($row_booking['cruise_upload_ticket']!=""){
          $newUrl = preg_replace('/(\/+)/','/',$row_booking['cruise_upload_ticket']);      
          $newUrl = str_replace("../","", $newUrl);  
          $newUrl = BASE_URL.$newUrl;
          $link2 = '<a href="'. $newUrl .'" class="btn btn-info btn-sm" title="Download Cruise Ticket" download><i class="fa fa-download"></i></a>';
        }
        
        //Passengers
        $adults = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$row_booking[booking_id]' and status!='Cancel' and adolescence='Adult'"));
        $child = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$row_booking[booking_id]' and status!='Cancel' and adolescence='Children'"));
        $infants = mysqli_num_rows(mysqlQuery("select traveler_id from package_travelers_details where booking_id='$row_booking[booking_id]' and status!='Cancel' and adolescence='Infant'"));
        
	      $tour_subtotal = $row_booking['total_hotel_expense'];
        $tour_total_amount= ($row_booking['actual_tour_expense']!="") ? $row_booking['actual_tour_expense']: 0;
        $net_amount  =  $tour_total_amount + $row_booking['total_travel_expense'] - $cancel_amount;
        
        //**Service Tax
        $taxation_type = $row_booking['taxation_type'];
        $train_expense = $row_booking['train_expense'];
        $plane_expense = $row_booking['plane_expense'];
        $cruise_expense = $row_booking['cruise_expense'];
        $visa_amount = $row_booking['visa_amount'];
        $insuarance_amount = $row_booking['insuarance_amount'];
        $tour_subtotal = $row_booking['total_hotel_expense'];
        $basic_cost = $train_expense +$plane_expense +$cruise_expense +$visa_amount +$insuarance_amount +$tour_subtotal;
        //Flights
        $sq_f_count = mysqli_num_rows(mysqlQuery("select * from package_plane_master where booking_id='$row_booking[booking_id]'")); 
        $flights = '';
        $count = 1;
          if($sq_f_count != '0'){
          $sq_entry = mysqlQuery("select * from package_plane_master where booking_id='$row_booking[booking_id]'");
          while($row_entry = mysqli_fetch_assoc($sq_entry)){
            $seperator = ($sq_f_count != $count) ? '/ ' : '';
            $flights .= 'From '.$row_entry['from_location'].' To '.$row_entry['to_location'].$seperator;
            $count++;
          }
        }
        //Train
        $sq_f_count = mysqli_num_rows(mysqlQuery("select * from package_train_master where booking_id='$row_booking[booking_id]'")); 
        $trains = '';
        $count = 1;
          if($sq_f_count != '0'){
          $sq_entry = mysqlQuery("select * from package_train_master where booking_id='$row_booking[booking_id]'");
          while($row_entry = mysqli_fetch_assoc($sq_entry)){
            $seperator = ($sq_f_count != $count) ? '/ ' : '';
            $trains .= 'From '.$row_entry['from_location'].' To '.$row_entry['to_location'].$seperator;
            $count++;
          }
        }
        //Cruise
        $sq_f_count = mysqli_num_rows(mysqlQuery("select * from package_cruise_master where booking_id='$row_booking[booking_id]'")); 
        $cruises = '';
          if($sq_f_count != '0'){
          $count = 0;
          $sq_entry = mysqlQuery("select * from package_cruise_master where booking_id='$row_booking[booking_id]'");
          while($row_entry = mysqli_fetch_assoc($sq_entry)){
            $count++;
            $cruises .= 'Cabin- '.$row_entry['cabin'].', Route- '.$row_entry['route'];
            $cruises .= ($count<$sq_f_count)?' / ':'';
          }
        }
        
        $sq_sac = mysqli_fetch_assoc(mysqlQuery("select * from sac_master where service_name='Package Tour'"));   
        $sac_code = $sq_sac['hsn_sac_code'];
        $tour_date = get_date_user($row_booking['tour_from_date']);
        $tour_to_date = get_date_user($row_booking['tour_to_date']);
        $invoice_no = get_package_booking_id($row_booking['booking_id'],$year);
        $booking_id = $row_booking['booking_id'];
        $invoice_date = date('d-m-Y',strtotime($row_booking['booking_date']));
        $customer_id = $row_booking['customer_id'];
        $quotation_id = $row_booking['quotation_id'];
        $service_name = "Package Invoice";
        $tour_name = $row_booking['tour_name'];
        $url1 = BASE_URL."model/app_settings/print_html/invoice_html/body/git_fit_body_html.php?invoice_no=$invoice_no&invoice_date=$invoice_date&customer_id=$customer_id&quotation_id=$quotation_id&service_name=$service_name&taxation_type=$taxation_type&train_expense=$train_expense&plane_expense=$plane_expense&cruise_expense=$cruise_expense&visa_amount=&insuarance_amount=&tour_subtotal=$tour_subtotal&train_service_charge=&plane_service_charge=&cruise_service_charge=&visa_service_charge=&insuarance_service_charge=&train_service_tax=&plane_service_tax=&cruise_service_tax=&visa_service_tax=&insuarance_service_tax=&tour_service_tax=&train_service_tax_subtotal=&plane_service_tax_subtotal=&cruise_service_tax_subtotal=&visa_service_tax_subtotal=&insuarance_service_tax_subtotal=&tour_service_tax_subtotal=&total_paid=$paid_amount&net_amount=$net_amount&sac_code=$sac_code&branch_status=yes&tour_name=$tour_name&booking_id=$row_booking[booking_id]&credit_card_charges=$credit_card_charges&credit_card_charges=$credit_card_charges&tcs_tax=$row_booking[tcs_tax]&tcs_per=$row_booking[tcs_per]&tour_date=$tour_date&tour_to_date=$tour_to_date&child=$child&adults=$adults&infants=$infants&flights=$flights&trains=$trains&cruises=$cruises&canc_amount=$cancel_amount&bg=$bg&sub_total=$row_booking[subtotal]";
        $invoice_btn = '<a data-toggle="tooltip" onclick="loadOtherPage(\''. $url1 .'\')" class="btn btn-info btn-sm" title="Download Invoice"><i class="fa fa-print" data-toggle="tooltip"></i></a>';

      }
      else{
        $service_voucher = 'NA';
        $link = '';
        $link1 = '';
        $link2 = '';
        $invoice_btn = '';
      }
      ?>
      <tr class="<?=$bg?>">
        <td><?= ++$count ?></td>
        <td><?= get_package_booking_id($row_booking['booking_id'],$year) ?></td>
        <td><?= $row_booking['tour_name'] ?></td>
        <td><?= date('d-m-Y', strtotime($row_booking['tour_from_date'])) ?></td>
        <td><button class="btn btn-info btn-sm" onclick="package_view_modal(<?= $row_booking['booking_id'] ?>)" title="View Details" id="package-<?= $row_booking['booking_id'] ?>"><i class="fa fa-eye" aria-hidden="true"></i></button></td>
        <td class="info"><?= $net_total1 ?></td>
        <td class="success"><?= $paid_amount1 ?></td>
        <td class="danger"><?= $cancel_amount1 ?></td>
        <td class="warning"><?= $balance_amount1 ?></td>
        <td><?= $invoice_btn.' '.$service_voucher.''.$link.''.$link1.''.$link2 ?></td>
      </tr>
      <?php
    }
    ?>
  </tbody>
  <tfoot>
    <tr class="active">
      <th colspan="5" class="text-right">Total</th>
      <th class="info text-right"><?= number_format($total_amount,2) ?></th>
      <th class="success text-right"><?= number_format($total_paid1,2) ?></th>
      <th class="danger text-right"><?= number_format($total_cancel,2) ?></th>
      <th class="warning text-right"><?= number_format($total_balance,2) ?></th>
		  <th class="active"></th>
    </tr>
  </tfoot>
</table>

</div> </div> </div>
<div id="div_package_content_display"></div>
<script type="text/javascript">
$('#package_table').dataTable({
  "pagingType": "full_numbers"
});
  function package_view_modal(booking_id)
  {
    $('#package-'+booking_id).button('loading');
    var base_url = $('#base_url').val();
    $.post(base_url+'view/customer/other/bookings/package_booking/view/index.php', { booking_id : booking_id }, function(data){
      $('#div_package_content_display').html(data);
      $('#package-'+booking_id).button('reset');
    });
  }
</script>
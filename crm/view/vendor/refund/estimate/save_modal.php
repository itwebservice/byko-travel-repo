<?php
include_once('../../../../model/model.php');
include_once('../../inc/vendor_generic_functions.php');

$estimate_id = $_POST['estimate_id'];

$sq_est_info = mysqli_fetch_assoc(mysqlQuery("select * from vendor_estimate where estimate_id='$estimate_id' and delete_status='0'"));
$vendor = $sq_est_info['vendor_type'];
$vendor_id = $sq_est_info['vendor_type_id'];
$estimate_type = $sq_est_info['estimate_type'];
$estimate_type_id = $sq_est_info['estimate_type_id'];

$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from vendor_payment_master where estimate_id='$estimate_id' and clearance_status!='Pending' and clearance_status!='Cancelled'"));
$sq_payment_info['sum'] = ($sq_payment_info['sum']=='')?0:$sq_payment_info['sum'];

$date = $sq_est_info['purchase_date'];
$yr = explode("-", $date);
$year = $yr[0];
$vendor_type_val = get_vendor_name($vendor, $vendor_id);
$estimate_type_val = get_estimate_type_name($estimate_type, $estimate_type_id);

$service_tax_amount = 0;
if($sq_est_info['service_tax_subtotal'] !== 0.00 && ($sq_est_info['service_tax_subtotal']) !== ''){
	$service_tax_subtotal1 = explode(',',$sq_est_info['service_tax_subtotal']);
	for($i=0;$i<sizeof($service_tax_subtotal1);$i++){
		$service_tax = explode(':',$service_tax_subtotal1[$i]);
		$service_tax_amount = $service_tax_amount + $service_tax[2];
	}
}
$details_arr = array();
$bg_arr = array();
if($estimate_type == 'Flight'){
  
	$sq_booking = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master where ticket_id='$estimate_type_id' "));
  if($sq_booking['cancel_type'] == 1)
    $cancel_type = 'Full';
  else if($sq_booking['cancel_type'] == 2)
    $cancel_type = 'Passenger wise';
  else if($sq_booking['cancel_type'] == 3)
    $cancel_type = 'Sector wise';

  if($sq_booking['cancel_type'] == 1 || $sq_booking['cancel_type'] == 3){

    $query = "select * from ticket_trip_entries where ticket_id='$estimate_type_id' ";
		$sq_trip = mysqlQuery($query);
		while($row_trip = mysqli_fetch_assoc($sq_trip)){

			$sq_pass = mysqli_fetch_assoc(mysqlQuery("select * from ticket_master_entries where entry_id='$row_trip[passenger_id]'"));
      array_push($details_arr,$sq_pass['first_name'].' '.$sq_pass['last_name'].' '.$row_trip['departure_city'].' -- '.$row_trip['arrival_city']);
      array_push($bg_arr,$row_trip['status']);
    }
  }
  else if($sq_booking['cancel_type'] == 2){

    $sq_ticket_entries = mysqlQuery("select * from ticket_master_entries where ticket_id='$estimate_type_id'");
    while($row_entry = mysqli_fetch_assoc($sq_ticket_entries)){

      array_push($details_arr,$row_entry['first_name'].' '.$row_entry['last_name']);
      array_push($bg_arr,$row_entry['status']);
    }
  }
}
?>
<form id="frm_estimate">
  <input type="hidden" name="estimate_id" id="estimate_id" value="<?= $estimate_id ?>">

  <div class="modal fade" id="save_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Cancellation Estimation</h4>
        </div>
        <div class="modal-body">
          <?php
          if(sizeof($details_arr) > 0){ ?>
          <div class="row mg_bt_20">
            <div class="col-xs-12">
              <div class="profile_box main_block">
                    <legend><?= $estimate_type_val ?> Invoice details for <?= get_vendor_estimate_id($estimate_id,$year)." : ".$vendor_type_val."(".$vendor.")"." : " ?><span style="color: red !important;font-size:15px"> <?= $cancel_type ?> Return </span></legend>
                      <div class="table-responsive">
                          <table class="table table-hover table-bordered no-marg" id="tbl_ticket_report">
					                  <tbody>
                              <?php
                              for($d=0;$d<sizeof($details_arr);$d++){
                                $bg_crl = ($bg_arr[$d] == 'Cancel') ? 'danger' : '';
                                ?>
                                <tr class="<?= $bg_crl ?>">
                                  <td><?= $d+1 ?></td>
                                  <td><?= $details_arr[$d] ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                          </table>
                      </div>
              </div>
            </div>
          </div>
          <?php } ?>
          <div class="row">
            <div class="col-md-12 col-sm-6 col-xs-12 mg_bt_10_xs">
                  <div class="widget_parent-bg-img bg-img-red">
                      <div class="widget_parent">
                          <div class="stat_content main_block">
                              <span class="main_block content_span" data-original-title="" title="">
                                  <span class="stat_content-tilte pull-left" data-original-title="" title="">Basic Amount</span>
                                  <span class="stat_content-amount pull-right" data-original-title="" title="" id="obasic_cost"><?= $sq_est_info['basic_cost'] ?></span>
                              </span>
                              <span class="main_block content_span" data-original-title="" title="">
                                  <span class="stat_content-tilte pull-left" data-original-title="" title="">Service Charge</span>
                                  <span class="stat_content-amount pull-right" data-original-title="" title="" id="oservice_charge"><?= $sq_est_info['service_charge'] ?></span>
                              </span>  
                              <span class="main_block content_span" data-original-title="" title="">                    
                                  <span class="stat_content-tilte pull-left" data-original-title="" title="">Tax Amount</span>
                                  <span class="stat_content-amount pull-right" data-original-title="" title="" id="oservice_tax_subtotal"><?= floatval($service_tax_amount) ?></span>
                              </span> 
                              <span class="main_block content_span" data-original-title="" title="">
                                  <span class="stat_content-tilte pull-left" data-original-title="" title="">Roundoff</span>
                                  <span class="stat_content-amount pull-right" data-original-title="" title="" id="oroundoff"><?= $sq_est_info['roundoff'] ?></span>
                              </span>  
                              <span class="main_block content_span" data-original-title="" title="">                    
                                  <span class="stat_content-tilte pull-left" data-original-title="" title="">Net Total</span>
                                  <span class="stat_content-amount pull-right" data-original-title="" title="" id="onet_total"><?= $sq_est_info['net_total'] ?></span>
                              </span>
                              <span class="main_block content_span" data-original-title="" title="">
                                  <span class="stat_content-tilte pull-left" data-original-title="" title="">Paid Amount</span>
                                  <span class="stat_content-amount pull-right" data-original-title="" title="" id="opaid_amount"><?= number_format($sq_payment_info['sum'],2) ?></span>
                              </span>          
                          </div> 
                      </div>
                  </div>
              </div>
            </div>
            <hr/>
            <?php
            $sq_cancel_count = mysqli_num_rows(mysqlQuery("select * from vendor_estimate where estimate_id='$estimate_id' and delete_status='0'"));
            if ($sq_cancel_count > 0) {
              $cancel_estimate = (isset($sq_est_info['cancel_estimate']) && $sq_est_info['cancel_estimate']!='') ? json_decode($sq_est_info['cancel_estimate']) : [];
              $basic_cost = ''; $service_charge = ''; $service_tax_subtotal = ''; $roundoff = '';$net_total='';
              if(sizeof($cancel_estimate) >0){
                  $basic_cost = floatval($cancel_estimate[0]->basic_cost);
                  $service_charge = floatval($cancel_estimate[0]->service_charge);
                  $service_tax_subtotal = floatval($cancel_estimate[0]->service_tax_subtotal);
                  $roundoff = floatval($cancel_estimate[0]->roundoff);
                  $net_total = floatval($cancel_estimate[0]->net_total);
              }
            ?>
            <div class="row">
              <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10_xs">
              <small>Purchase Return</small>
                
              <select name="purchase_return" id="purchase_return" style="width:100%" title="Purchase Return" data-toggle="tooltip" required>
              <?php 
              if($sq_est_info['purchase_return'] != '0'){
                $value = ($sq_est_info['purchase_return'] == '1') ? 'Full': 'Partial';
                ?>
                <option value="<?= $sq_est_info['purchase_return'] ?>"><?= $value ?></option>
                <?php } ?>
                    <option value="">*Purchase Return</option>
                    <option value="1">Full</option>
                    <option value="2">Partial</option>
                </select>
              </div>
                <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
                    <small>Basic Amount</small>
                    <input type="number" id="basic_cost" name="basic_cost" placeholder="Basic Amount"
                        title="Basic Amount" onchange="calculate_estimate_amount()" value="<?= floatval($basic_cost) ?>">
                </div>
                <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
                <small>Service Charge</small>
                    <input type="number" id="service_charge" name="service_charge"
                        placeholder="Service Charge" title="Service Charge" onchange="calculate_estimate_amount()" value="<?= floatval($service_charge) ?>">
                </div>
                <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
                <small>Tax Amount</small>
                    <input type="text" id="service_tax_subtotal" name="service_tax_subtotal"
                        placeholder="Tax Amount" title="Tax Amount" onchange="calculate_estimate_amount()" value="<?= floatval($service_tax_subtotal) ?>">
                </div>
                <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
                <small>Round Off</small>
                    <input type="text" id="roundoff" class="form-control" name="roundoff"
                        placeholder="Round Off" title="Round Off" onchange="calculate_estimate_amount()" value="<?= floatval($roundoff) ?>" readonly>
                </div>
                <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
                <small>Net Total</small>
                    <input type="text" id="net_total" class="amount_feild_highlight text-right"
                        name="net_total" placeholder="*Net Total" title="Net Total" onchange="calculate_estimate_amount()" value="<?= floatval($net_total) ?>" readonly>
                </div>
                <div class="col-md-3 col-sm-4 col-xs-12 mg_bt_10">
                    <small>Cancellation Charges</small>
                  <input type="text" name="cancel_amount" id="cancel_amount" class="text-right" placeholder="*Cancellation Charges" title="Cancellation Charges" onchange="validate_balance(this.id);calculate_total_refund()" value="<?= $sq_est_info['cancel_amount'] ?>">
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
                  <?php
                  if ($sq_est_info['cancel_est_flag'] == '0') { ?>
                    <small>Total Refund</small>
                    <input type="text" name="total_refund_amount" id="total_refund_amount" class="amount_feild_highlight text-right" placeholder="Total Refund" title="Total Refund" readonly value="<?= $sq_payment_info['sum'] ?>">
                </div>
              <?php } else {
              ?> <small>Total Refund</small>
                <input type="text" name="total_refund_amount" id="total_refund_amount" class="amount_feild_highlight text-right" placeholder="Total Refund" title="Total Refund" readonly value="<?= $sq_est_info['total_refund_amount'] ?>">
              <?php
                  } ?>
            </div>
            <input type="hidden" id="total_sale" name="total_sale" value="<?= $sq_est_info['net_total'] ?>">
            <input type="hidden" id="total_paid" name="total_paid" value="<?= $sq_payment_info['sum'] ?>">
              <?php
              if ($sq_est_info['cancel_est_flag'] == '0') { ?>
                <div class="row text-center mg_tp_20">
                  <div class="col-md-12">
                    <button id="btn_refund_save" class="btn btn-success" id="btn_est_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
                  </div>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
      </div>
    </div>
</form>

<script>
  $('#save_modal').modal('show');

function calculate_estimate_amount(offset='')
{
	var basic_cost = $('#basic_cost'+offset).val();
	var service_charge = $('#service_charge'+offset).val();
	var service_tax_subtotal = $('#service_tax_subtotal'+offset).val();

	if(basic_cost==""){ basic_cost = 0; }
	if(service_charge==""){ service_charge = 0; }
	if(service_tax_subtotal==""){ service_tax_subtotal = 0; }

	var obasic_cost = $('#obasic_cost'+offset).html();
	var oservice_charge = $('#oservice_charge'+offset).html();
	var oservice_tax_subtotal = $('#oservice_tax_subtotal'+offset).html();

  console.log(basic_cost);
  if(parseFloat(basic_cost) > parseFloat(obasic_cost)){
      error_msg_alert("Basic amount should not be greater than purchase basic amount!");
      return false;
  }
  if(parseFloat(service_charge) > parseFloat(oservice_charge)){
      error_msg_alert("Service charge should not be greater than purchase service charge!");
      return false;
  }
  if(parseFloat(service_tax_subtotal) > parseFloat(oservice_tax_subtotal)){
      error_msg_alert("Tax amount should not be greater than purchase tax amount!");
      return false;
  }

	var net_total = parseFloat(basic_cost) + parseFloat(service_charge) + parseFloat(service_tax_subtotal);
	net_total = parseFloat(net_total.toFixed(2));
	if(net_total < 0) net_total = 0.00;	
	var roundoff = Math.round(net_total)-net_total;
	$('#roundoff'+offset).val(roundoff.toFixed(2));
	$('#net_total'+offset).val(net_total+roundoff);
}
  function calculate_total_refund() {
    var total_refund_amount = 0;
    var cancel_amount = $('#cancel_amount').val();
    var total_sale = $('#total_sale').val();
    var total_paid = $('#total_paid').val();

    if (cancel_amount == "") {
      cancel_amount = 0;
    }
    if (total_paid == "") {
      total_paid = 0;
    }

    if (parseFloat(cancel_amount) > parseFloat(total_sale)) {
      error_msg_alert("Cancel amount can not be greater than Sale amount");
    }
    var total_refund_amount = parseFloat(total_paid) - parseFloat(cancel_amount);

    if (parseFloat(total_refund_amount) < 0) {
      total_refund_amount = 0;
    }
    $('#total_refund_amount').val(total_refund_amount.toFixed(2));
  }

  $(function() {
    $('#frm_estimate').validate({
      rules: {
        cancel_amount: {
          required: true,
          number: true
        },
        total_refund_amount: {
          required: true,
          number: true
        },
      },
      submitHandler: function(form) {

        $('#btn_est_save').prop('disabled', true);
        var estimate_id = $('#estimate_id').val();
        var cancel_amount = $('#cancel_amount').val();
        var total_refund_amount = $('#total_refund_amount').val();
        var total_sale = $('#total_sale').val();
        var total_paid = $('#total_paid').val();
        var purchase_return = $('#purchase_return').val();

        if (parseFloat(cancel_amount) > parseFloat(total_sale)) {
          $('#btn_est_save').prop('disabled', false);
          error_msg_alert("Cancellation charges can not be greater than Purchase Amount");
          return false;
        }

          var basic_cost = $('#basic_cost').val();
          var service_charge = $('#service_charge').val();
          var service_tax_subtotal = $('#service_tax_subtotal').val();
          var roundoff = $('#roundoff').val();
          var net_total = $('#net_total').val();

          if(basic_cost==""){ basic_cost = 0; }
          if(service_charge==""){ service_charge = 0; }
          if(service_tax_subtotal==""){ service_tax_subtotal = 0; }

          var obasic_cost = $('#obasic_cost').html();
          var oservice_charge = $('#oservice_charge').html();
          var oservice_tax_subtotal = $('#oservice_tax_subtotal').html();
          var oroundoff = $('#oroundoff').html();
          var onet_total = $('#onet_total').html();

          if(parseFloat(basic_cost) > parseFloat(obasic_cost)){
              error_msg_alert("Basic amount should not be greater than purchase basic amount!");
              return false;
          }
          if(parseFloat(service_charge) > parseFloat(oservice_charge)){
              error_msg_alert("Service charge should not be greater than purchase service charge!");
              return false;
          }
          if(parseFloat(service_tax_subtotal) > parseFloat(oservice_tax_subtotal)){
              error_msg_alert("Tax amount should not be greater than purchase tax amount!");
              return false;
          }
          var estimate_arr = [];
          estimate_arr.push({
            'basic_cost':basic_cost,
            'service_charge':service_charge,
            'service_tax_subtotal':service_tax_subtotal,
            'roundoff':roundoff,
            'net_total':net_total
          });

          $('#btn_est_save').button('loading');
          $('#vi_confirm_box').vi_confirm_box({

          callback: function(data1) {
            if (data1 == "yes") {
              $.ajax({
                type: 'post',
                url: base_url() + 'controller/vendor/refund/estimate_update.php',
                data: {
                  estimate_id: estimate_id,
                  cancel_amount: cancel_amount,
                  total_refund_amount: total_refund_amount,
                  estimate_arr:estimate_arr,
                  purchase_return:purchase_return
                },
                success: function(result) {
                  $('#btn_est_save').button('reset');
                  var msg = result.split('-');
                  if (msg[0] == 'error') {
                    error_msg_alert(result);
                    $('#btn_est_save').prop('disabled', false);
                    return false;
                  } else {
                    success_msg_alert(result);
                    $('#btn_est_save').prop('disabled', false);
                    $('#save_modal').modal('hide');
                    list_reflect();
                  }
                }
              });
            } else {
              $('#btn_est_save').button('reset');
            }
          }
        });
      }
    });
  });
</script>
<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>
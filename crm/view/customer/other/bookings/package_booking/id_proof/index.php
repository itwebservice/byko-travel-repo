<?php
include "../../../../../../model/model.php";
$customer_id = $_SESSION['customer_id'];
?>
<div class="app_panel_content Filter-panel">
  <div class="row">
    <div class="col-sm-4 col-sm-offset-4">
      <select id="pcmb_traveler_id" name="pcmb_traveler_id" title="Passenger Name" style="width:100%" onchange="traveler_id_proof_info_reflect()" title="Passenger"> 
        <option value="">Passenger Name</option>
        <?php
        $query = "select * from package_tour_booking_master where customer_id='$customer_id' and delete_status='0' order by booking_id desc";
        $sq_booking = mysqlQuery($query);
        while($row_booking = mysqli_fetch_assoc($sq_booking)){

          $yr = explode("-", $row_booking['booking_date']);
          $year = $yr[0];
          $sq_traveler = mysqlQuery("select traveler_id, m_honorific, first_name, last_name from package_travelers_details where booking_id='$row_booking[booking_id]' and status!='Cancel'");
          while($row_traveler = mysqli_fetch_assoc($sq_traveler)){
            ?>
            <option value="<?php echo $row_traveler['traveler_id'] ?>"><?php echo get_package_booking_id($row_booking['booking_id'],$year).":".$row_traveler['m_honorific']." ".$row_traveler['first_name']." ".$row_traveler['last_name']; ?></option>
            <?php
          }
        }
        ?>
      </select>
    </div>
  </div>
</div>

<div id="div_traveler_id_proof_info1" class="main_block mg_tp_20"></div>
<script>
$('#pcmb_traveler_id').select2();
function traveler_id_proof_info_reflect()
{
    var traveler_id = $('#pcmb_traveler_id').val();
    if(traveler_id == ''){
      error_msg_alert("Select Passenger first!");
      $('#div_traveler_id_proof_info1').addClass('hidden'); 
      return false;
    }else{
      $('#div_traveler_id_proof_info1').removeClass('hidden'); }
      
    $.post('bookings/package_booking/id_proof/traveler_id_proof_info_reflect.php', { traveler_id : traveler_id }, function(data){
        $('#div_traveler_id_proof_info1').html(data);
    });
}
</script>
<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>
<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
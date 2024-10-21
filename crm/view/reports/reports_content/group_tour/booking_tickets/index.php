<?php include "../../../../../model/model.php";
$q = "select * from branch_assign where link='booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >
<div class="app_panel_content Filter-panel mg_bt_10">
    <div class="col-md-3 col-sm-3 mg_bt_10_xs">
            <select style="width:100%;" class="form-control" id="cmb_tour_name" name="cmb_tour_name" onchange="cancelled_tour_groups_reflect1(this.id);" title="Tour Name"> 
                <option value="">Tour Name </option>
                <?php
                    $sq=mysqlQuery("select tour_id,tour_name from tour_master order by tour_name");
                    while($row=mysqli_fetch_assoc($sq))
                    {
                      echo "<option value='$row[tour_id]'>".$row['tour_name']."</option>";
                    }    
                ?>
            </select>
        </div>
        <div class="col-md-3 col-sm-3 mg_bt_10_xs">
            <select class="form-control" id="cmb_tour_group" name="cmb_tour_group" onchange="canceled_travelers_reflect1();" title="Tour Date"> 
                <option value="">Tour Date</option>        
            </select>
        </div>
        <div class="col-md-3 col-sm-3">
            <select class="form-control" id="cmb_traveler_group_id" name="cmb_traveler_group_id" title="Booking ID"> 
                <option value="">Booking ID</option>        
            </select>
        </div>
    <div class="col-md-3 col-sm-6 col-xs-12 form-group">
        <button class="btn btn-sm btn-info ico_right" onclick="tour_group_dynamic_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
      </div>
</div>
<div id="div_list" class="main_block mg_tp_20">
<div class="row"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="gtc_tour_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<script>
  $('#cmb_tour_name,#cmb_tour_group,#cmb_traveler_group_id').select2();
</script>
<script type="text/javascript">
 var column = [
	{ title: "S_No." },
	{ title: "Tour_name" },
	{ title: "tour_date" },
	{ title: "booking_id" },
	{ title: "train_ticket" },
  { title: "flight_ticket"},
  { title: "cruise_ticket"}
];
function tour_group_dynamic_reflect()
{

  var tour_id = $('#cmb_tour_name').val();
  var booking_id = $('#cmb_traveler_group_id').val();
  var group_id = $('#cmb_tour_group').val();
  var branch_status = $('#branch_status').val();
  $("#cmb_traveler_group_id").html('<option value="">Select Booking ID</option>');
  $.post('reports_content/group_tour/booking_tickets/booking_ticket_report_filter.php', { tour_id : tour_id, booking_id : booking_id, group_id : group_id,branch_status:branch_status}, function(data){
    pagination_load(data, column, true, false, 20, 'gtc_tour_report',true);
  });
}
tour_group_dynamic_reflect();

function cancelled_tour_groups_reflect1(id)
{
  var tour_id=$('#'+id).val();

  $.get('reports_content/group_tour/booking_tickets/cancelled_tour_groups_reflect.php', { tour_id : tour_id }, function(data){
    $('#cmb_tour_group').html(data);
  }); 

}
function canceled_travelers_reflect1()
{
  var tour_id = document.getElementById("cmb_tour_name").value;
  var tour_group_id = document.getElementById("cmb_tour_group").value;
 
  $.get( "reports_content/group_tour/booking_tickets/cancelled_traveler_reflect.php" , { tour_id : tour_id, tour_group_id : tour_group_id } , function ( data ) {
                $ ("#cmb_traveler_group_id").html( data ) ;
          } ) ; 
}
</script>
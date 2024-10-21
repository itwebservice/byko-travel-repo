<?php
include "../../../../../model/model.php";
$q = "select * from branch_assign where link='booking/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >
<div class="row mg_bt_10">
	<div class="col-md-12 text-right">
		<button class="btn btn-excel btn-sm" onclick="excel_report_new()" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
	</div>
</div>
<div class="app_panel_content Filter-panel mg_bt_10">
	<div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
        <select id="tour_id_filter" name="tour_id_filter" onchange="tour_group_dynamic_reflect(this.id,'group_id_filter');" style="width:100%" title="Tour Name" class="form-control"> 
            <option value="">Tour Name</option>
            <?php
            $sq=mysqlQuery("select tour_id,tour_name from tour_master where active_flag='Active' order by tour_name");
            while($row=mysqli_fetch_assoc($sq))
            {
                echo "<option value='$row[tour_id]'>".$row['tour_name']."</option>";
            }    
            ?>
        </select>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">
        <select class="form-control" id="group_id_filter" name="group_id_filter"  title="Tour Date"> 
            <option value="">Tour Date</option>        
        </select>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12 form-group">
        <button class="btn btn-sm btn-info ico_right" onclick="room_reflect()">Proceed&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
    </div>
</div>
<div id="div_list" class="main_block mg_tp_20">
<div class="row"> <div class="col-md-12 no-pad"> <div class="table-responsive">
<table id="gr_tour_report" class="table table-hover" style="margin: 20px 0 !important;">         
</table>
</div></div></div>
</div>
<script>
    $('#tour_id_filter').select2();
    var column = [
	{ title:"S_No."},
    { title:"Tour_name"},
    { title:"Tour_date"},
    { title:"booking_id"},
    { title:"Customer Name"},
    { title:"Total_Guest"},
	{ title:"Single_Bed_Room"},
    { title:"double_bed_room"},
	{ title:"Extra_bed"}
];
	function room_reflect(){
		var group_id = $('#group_id_filter').val();
		var tour_id = $('#tour_id_filter').val();
		var branch_status = $('#branch_status').val();
		$.post('reports_content/group_tour/room_allocation_report/room_allocation_report.php', {group_id : group_id,tour_id : tour_id,branch_status:branch_status}, function(data){
            pagination_load(data, column, true, false, 20, 'gr_tour_report',true);
	});
	}
	room_reflect();



    function excel_report_new() {
		//tour name tourDate bookingId
		var tourName = $('#tour_id_filter').val();
		var tourDate = $('#group_id_filter').val();
		
		var base_url = $('#base_url').val();
		
		
		 window.location = base_url + 'view/reports/reports_content/group_tour/room_allocation_report/export_excel.php?tourName=' + tourName + '&tourDate=' + tourDate;
	}

</script>
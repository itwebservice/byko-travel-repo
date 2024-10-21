<form id="frm_tab1">

<div class="app_panel">

    <div class="">
      <div class="container-fluid">
        <div class="app_panel_content Filter-panel">
          <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_20">
            <legend>Customer Details</legend>
              <div class="row mg_bt_20">

                <div class="col-md-3 col-sm-6 col-xs-12">

                  <select name="customer_id" id="customer_id" class="customer_dropdown" title="Select Customer Name" style="width:100%" onchange="customer_info_load();get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','service_charge','discount');">
                    <?php get_new_customer_dropdown($role,$branch_admin_id,$branch_status); ?>
                  </select>
                </div>	
                  <div id="cust_details">	  
                    <div class="col-md-3 col-sm-6 col-xs-12">

                      <input type="text" id="mobile_no" name="mobile_no"  placeholder="Mobile No" title="Mobile No" readonly>

                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">

                      <input type="text" id="email_id" name="email_id" placeholder="Email ID" title="Email ID" readonly>

                    </div>
                    
                    <div class="col-md-2 col-sm-6 col-xs-12 hidden" id="company_div">

                      <input type="text" id="company_name" class="hidden" name="company_name" title="Company Name" placeholder="Company Name" title="Company Name" readonly>

                    </div> 
                    <div class="col-md-2 col-sm-4 col-xs-12 mg_bt_10 hidden" id="credit_div">
                      <input type="text" id="credit_amount" class="hidden" name="credit_amount" placeholder="Credit Note Balance" title="Credit Note Balance" readonly>
                    </div>
                    
                  </div>
              </div>
              <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <select name="tour_type" id="tour_type" title="Travelling Type">
                      <option value="">*Travelling Type</option>
                      <option value="Domestic">Domestic</option>
                      <option value="International">International</option>
                    </select>
                  </div>	
                    <div class="col-md-6 col-sm-6 col-xs-12 mg_bt_10">
                      <input type="text" id="guest_name"  name="guest_name" title="Guest Name and contact number" placeholder="Guest Name and contact number" >
                    </div> 
              </div>
              <div class="row mg_tp_20">
                <div class="col-md-3">
                  <input id="copy_details1" name="copy_details1" type="checkbox" onClick="copy_details();">
                  &nbsp;&nbsp;<label for="copy_details1">Passenger Details same as above</label>
                </div>
                <div class="col-md-3">
                  <input id="reissue_check1" name="reissue_check1" type="checkbox" onClick="ticket_reissue();">
                  &nbsp;&nbsp;<label for="reissue_check1">Reissue Ticket</label>
                </div>
              </div>
          </div>
          <div id="new_cust_div" class="mg_tp_10"></div>
          <h3 class="editor_title">Passenger Details</h3>
          <div class="panel panel-default panel-body app_panel_style">
            <div class="row text-right mg_bt_10">
                <div class="col-xs-12">
                    <div class="col-md-3 text-left no-pad">
                    <button type="button" class="btn btn-info btn-sm ico_left pull-left" onclick="display_format_modal();" autocomplete="off" data-original-title="" title=""><i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;CSV Format</button>&nbsp;
                        <div class="div-upload  mg_bt_20" id="div_upload_button">
                            <div id="cust_csv_upload" class="upload-button1"><span>CSV</span></div>
                            <span id="cust_status" ></span>
                            <ul id="files" ></ul>
                            <input type="hidden" id="txt_cust_csv_upload_dir" name="txt_cust_csv_upload_dir">
                        </div>
                    </div>
                    <span style="color: red;line-height: 35px;" data-original-title="" title="" class="note">Please add multiple seat number of multi trip using '/' in between like S1/D3/NA. And similar for Meal plan field.</span>
                    <button type="button" class="btn btn-excel" title="Add Row" onclick="addRow('tbl_dynamic_ticket_master','1')"><i class="fa fa-plus"></i></button>
                    <button type="button" class="btn btn-pdf btn-sm" title="Delete Row" onclick="deleteRow('tbl_dynamic_ticket_master');"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                      <?php $offset = ""; ?>
                      <table id="tbl_dynamic_ticket_master" name="tbl_dynamic_ticket_master" class="table border_0 no-marg" style="padding-bottom: 0 !important;">
                        <?php
                        include_once('ticket_master_tbl.php');
                        ?>
                      </table>
                    </div>
                </div>
            </div>
          </div>
          <div class="row text-center mg_tp_20">
            <div class="col-xs-12">
              <button class="btn btn-info btn-sm ico_right">Next&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></button>
            </div>
          </div>
          <div id="div_flightd_modal"></div>
        </div>
    </div>
  </div>
</div>
</form>



<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script>
$('#quotation_id').select2();
cust_csv_upload();
function cust_csv_upload()
{   
    var base_url = $('#base_url').val();
    var type="passenger_list";
    var btnUpload=$('#cust_csv_upload');
    var status=$('#cust_status');
    new AjaxUpload(btnUpload, {
      action: base_url+'view/visa_passport_ticket/ticket/home/save/upload_passenger_csv.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){
        if(!confirm('Do you want to import this file?')){
          return false;
        }
        if (! (ext && /^(csv)$/.test(ext))){ 
          // extension is not allowed 
          status.text('Only excel sheet files are allowed');
        }
        status.text('Uploading...');
      },
      onComplete: function(file, response){
        //On completion clear the status
        status.text('');
        //Add uploaded file to list
        if(response==="error"){
          alert("File is not uploaded.");
        } else{
          document.getElementById("txt_cust_csv_upload_dir").value = response;
          cust_csv_save();
        }
      }
    });
}

function cust_csv_save(){
    var cust_csv_dir = document.getElementById("txt_cust_csv_upload_dir").value;
    var base_url = $('#base_url').val();
    $.ajax({
        type:'post',
        url: base_url+'controller/visa_passport_ticket/ticket/passenger_csv_save.php',
        data:{cust_csv_dir : cust_csv_dir },
        success:function(result){
          console.log(result);
            var table = document.getElementById("tbl_dynamic_ticket_master");
						var pass_arr = JSON.parse(result);
            for(var i=0; i<pass_arr.length; i++){
                var row = table.rows[i]; 
                row.cells[2].childNodes[0].value = pass_arr[i]['m_first_name'];
                row.cells[3].childNodes[0].value = pass_arr[i]['m_middle_name'];
                row.cells[4].childNodes[0].value = pass_arr[i]['m_last_name'];
                row.cells[6].childNodes[0].value = pass_arr[i]['m_adolescence'];
                row.cells[7].childNodes[0].value = pass_arr[i]['ticket_no'];
                row.cells[8].childNodes[0].value = pass_arr[i]['gds_pnr'];
                row.cells[9].childNodes[0].value = pass_arr[i]['baggage_info'];
                row.cells[10].childNodes[0].value = pass_arr[i]['seat_no'];
                row.cells[11].childNodes[0].value = pass_arr[i]['meal_plan'];
                row.cells[12].childNodes[0].value = pass_arr[i]['main_ticket'];

								if(i!=pass_arr.length-1){
                    if(table.rows[i+1]==undefined){
                        addRow('tbl_dynamic_ticket_master');     
                    }                   
                }
            }
        }
    });
}

function display_format_modal(){
    var base_url = $('#base_url').val();
    window.location = base_url+"images/csv_format/flight_passenger_list.csv";
}
function business_rule_load(){
	get_auto_values('booking_date','basic_cost','payment_mode','service_charge','markup','save','true','service_charge','discount');;
}
function ticket_reissue(){

  var checkedValue = document.getElementById("reissue_check1").checked
  var main_ticket = document.getElementsByClassName("main_ticket");

  if(checkedValue === false){
    $('.main_ticket').attr('type','hidden');
  }
  else {
    $('.main_ticket').attr('type','text');
  }
}
function add_flight_details(passenger_id,type='save'){

  var count = passenger_id.substring(9);
  $('#add_trips'+count).prop('disabled',true);
  
  var base_url = $('#base_url').val();
  var first_name = $('#first_name'+count).val();
  var last_name = $('#last_name'+count).val();
  var flight_details = $('#flight_details'+count).html();
  if(first_name==''){
    error_msg_alert("Enter passenger's First name!");
    $('#add_trips'+count).prop('disabled',false);
    return false;
  }
  $('#add_trips'+count).button('loading');
  $.post(base_url+'view/visa_passport_ticket/ticket/home/add_flight_details.php', { first_name:first_name, last_name:last_name, flight_details:flight_details,count:count,type:type,entry_id:'' }, function(data){
    $('#div_flightd_modal').html(data);
    $('#add_trips'+count).prop('disabled',false);
    $('#add_trips'+count).button('reset');
  });
}

$('#frm_tab1').validate({
	rules:{
			customer_id : { required : true },
			tour_type : { required : true },
	},
	submitHandler:function(form, e){

        e.preventDefault();
        var adults = 0;
        var childrens = 0;
        var infant = 0;
        var msg = "";

        var table = document.getElementById("tbl_dynamic_ticket_master");
        var rowCount = table.rows.length;       
        var adult_total = 0;
        var child_total = 0;
        var infant_total = 0;

        let checkedRowCount = 0;
        for(var i=0; i<rowCount; i++){
          var row = table.rows[i];
          if(row.cells[0].childNodes[0].checked)
          {
            var first_name = row.cells[2].childNodes[0].value;
            var middle_name = row.cells[3].childNodes[0].value;
            var last_name = row.cells[4].childNodes[0].value;
            var adolescence = row.cells[6].childNodes[0].value;
            var ticket_no = row.cells[7].childNodes[0].value;
            var gds_pnr = row.cells[8].childNodes[0].value;
            var baggage_info = row.cells[9].childNodes[0].value;
            var seat_no = row.cells[10].childNodes[0].value;
            var meal_plan = row.cells[11].childNodes[0].value;
            var main_ticket = row.cells[12].childNodes[0].value;
            var trip_details = $('#flight_details'+(i+1)).html();

            var basic_fare_total = 0;
            var basic_fare_arr = [];
            var trip_data_check_arr = [];
            
            if(first_name==""){ 
              error_msg_alert("First name is required in row:"+(i+1));
              return false;
            }
            if(trip_details==""||trip_details==null){ 
              error_msg_alert("Flight Ticket Details required in row:"+(i+1));
              return false;
            }

            basic_fare_arr = trip_details && JSON.parse(trip_details)[0]['basic_fare_arr'];
				    trip_data_check_arr = JSON.parse(trip_details)[0]['trip_data_check_arr'];
            for(var t = 0; t < (basic_fare_arr).length ; t++){
              if(basic_fare_arr[t]==''){basic_fare_arr[t] = 0;}
              if(trip_data_check_arr[t] == true){
                basic_fare_total += parseFloat(basic_fare_arr[t]);
              }
            }
            if(adolescence=="Adult"){
              adults = adults + 1;
              adult_total += basic_fare_total;
            }
            if(adolescence=="Child"){
              childrens = childrens + 1;
              child_total += basic_fare_total;
            }
            if(adolescence=="Infant"){
              infant = infant + 1;
              infant_total += basic_fare_total;
            }
            
            if(adolescence==""){ 
              error_msg_alert("Adolescence is required in row:"+(i+1));
              return false;
            }
            if(row.cells[12].childNodes[0].getAttribute('type')=="text" && main_ticket==""){ 
              error_msg_alert("Main Ticket Number is required in row:"+(i+1));
              return false;
            }
            checkedRowCount++;
          }      

        }
        if (checkedRowCount < 1) {
          error_msg_alert('Atleast one passenger is required!');
          return false;
        }
        if(msg!=""){
        	error_msg_alert(msg);
        	return false;
        }

        $('#adults').val(adults);
        $('#childrens').val(childrens);
        $('#infant').val(infant);
        $('#adult_fair').val(adult_total);
        $('#children_fair').val(child_total);
        $('#infant_fair').val(infant_total);

        calculate_total_amount('abc');

        $('#tab_1_head').addClass('done');
        $('#tab_3_head').addClass('active');
        $('.bk_tab').removeClass('active');
        $('#tab3').addClass('active');
        $('html, body').animate({ scrollTop: $('.bk_tab_head').offset().top }, 200);

	}

});

</script>
<?php
include "../../../../../model/model.php";
$request_id = isset($_GET['request_id']) ? base64_decode($_GET['request_id']) : '';
$hotel_id = isset($_GET['hotel_id']) ? $_GET['hotel_id'] : '';
$hotel_flag = isset($_GET['hotel_flag']) ? $_GET['hotel_flag'] : '';
$sq_req = mysqli_fetch_assoc(mysqlQuery("select availability from package_tour_quotation_hotel_entries where id='$request_id'"));
$response = !isset($sq_req['availability']) ? json_encode([]) : $sq_req['availability'];
$response1 = isset($response) && ($response !== null) ? json_decode($response) : [];
$option_hotel_arr = isset($response1->option_hotel_arr) ? json_encode($response1->option_hotel_arr) : '';
$emp_id = isset($response1->emp_id) ? $response1->emp_id : 0;
$email_id = isset($response1->email_id) ? $response1->email_id : '';
$availability = isset($response1->availability) ? $response1->availability : '';
$reply_by = isset($response1->reply_by) ? $response1->reply_by : '';
$spec = isset($response1->spec) ? $response1->spec : '';
?>
<head>

  <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,500" rel="stylesheet">

  <!--========*****Header Stylsheets*****========-->
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-ui.min.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/select2.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.datetimepicker.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.wysiwyg.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery.mCustomScrollbar.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/owl.carousel.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/jquery-labelauty.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/menu-style.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/btn-style.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/dynforms.vi.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/bootstrap-tagsinput.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/admin.php">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/vi.alert.css">
  <link rel="stylesheet" href="<?php echo BASE_URL ?>css/app/app.php">

  <link href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"></link>
  <script src="<?php echo BASE_URL ?>js/jquery-3.1.0.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/jquery-ui.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/bootstrap.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/jquery.mCustomScrollbar.js"></script>
  <script src="<?php echo BASE_URL ?>js/jquery.datetimepicker.full.js"></script> 
  <script src="<?php echo BASE_URL ?>js/jquery.wysiwyg.js"></script> 
  <script src="<?php echo BASE_URL ?>js/script.js"></script>
  <script src="<?php echo BASE_URL ?>js/select2.full.js"></script> 
  <script src="<?php echo BASE_URL ?>js/owl.carousel.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/jquery-labelauty.js"></script>
  <script src="<?php echo BASE_URL ?>js/responsive-tabs.js"></script>
  <script src="<?php echo BASE_URL ?>js/dynforms.vi.js"></script>
  <script src="<?php echo BASE_URL ?>js/jquery.validate.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/vi.alert.js"></script>
  <script src="<?php echo BASE_URL ?>js/app/data_reflect.js"></script>
  <script src="<?php echo BASE_URL ?>js/app/validation.js"></script> 
  <script src="<?php echo BASE_URL ?>js/jquery.dataTables.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/dataTables.bootstrap.min.js"></script>
  <script src="<?php echo BASE_URL ?>js/bootstrap-tagsinput.min.js"></script>

</head>
<div class="modal fade" id="ressave_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document" style="width:65%">
    <div class="modal-content">
      <div class="modal-header">
	  	  <h4 class="modal-title" id="myModalLabel">Hotel Availability Response</h4>
		  </div>
      <div class="modal-body" style='padding:10px !important;'>
        <section id="sec_ticket_save" name="">

            <form id="formresponse">
                <div class="row mg_tp_10">
                    <div class="col-md-12 col-sm-12 mg_tp_20 text-center">
                        <input class="btn-radio" type="radio" id="available" name="status" value='Available'> <label for="available">Available</label>&nbsp;&nbsp;&nbsp;
                        <input class="btn-radio" type="radio" id="notavailable" name="status" value='Not Available'> <label for="notavailable">Not Available</label>
                    </div>
                    <div class="col-md-4 col-sm-6 mg_tp_20">
                        <input type="text" id="updated_by" name="updated_by" placeholder="*Replied By" title="Replied By" required/>
                    </div>
                    <div class="col-md-8 col-sm-6 mg_tp_20">
                        <textarea class="form-control" type="text" id="note" name="note" placeholder="Specification" title="Specification" data-toggle="tooltip" rows="1"></textarea>
                    </div>
                </div>
                <div class="row mg_tp_20 text-center">
                    <div class="col-xs-12">
                    <button class="btn btn-sm btn-success" id="btn_save"><i class="fa fa-paper-plane-o"></i>&nbsp;&nbsp;Send</button>
                    </div>
                </div>

                <input type="hidden" id="request_id" value="<?= $request_id ?>"/>
                <input type="hidden" id="response" value='<?= $response ?>'/>
                <input type="hidden" id="option_hotel_arr" value='<?= $option_hotel_arr ?>'/>
                <input type="hidden" id="emp_id" value='<?= $emp_id ?>'/>
                <input type="hidden" id="email_id" value='<?= $email_id ?>'/>
                <input type="hidden" id="availability" value='<?= $availability ?>'/>
                <input type="hidden" id="hotel_id" value='<?= $hotel_id ?>'/>
                <input type="hidden" id="hotel_flag" value='<?= $hotel_flag ?>'/>
                <input type="hidden" id="reply_by1" value='<?= $reply_by ?>'/>
                <input type="hidden" id="spec1" value='<?= $spec ?>'/>

            </form>   
		    </section>
      </div>  
    </div>
  </div>
</div>
<div id="site_alert"></div>

<script>
$('#ressave_modal').modal('show');
$(function(){
    $('#formresponse').validate({
      rules:{
      },
      submitHandler:function(form){

        var base_url = $('#base_url').val();
        var check_status = [];
        var response_arr = {};
        var request_id = $('#request_id').val();
        var email_id = $('#email_id').val();
        var emp_id = $('#emp_id').val();
        var availability = $('#availability').val();
        var option_hotel_arr = $('#option_hotel_arr').val();
        var note = $('#note').val();
        var updated_by = $('#updated_by').val();
        var hotel_flag = $('#hotel_flag').val();
        var hotel_id = $('#hotel_id').val();
        var oupdated_by = $('#reply_by1').val();
        var spec1 = $('#spec1').val();

        $('input[name="status"]:checked').each(function () {
            check_status.push($(this).val());
        });
        if(check_status.length==0){
            error_msg_alert('Please select hotel availability status!');
            return false;
        }
        if(hotel_flag == ''){
          if(availability!=''){
            error_msg_alert("Hotel Availability Status already saved. Please email in case any change.");
            return false;
          }
        }
        
        var new_option_hotel_arr = [];
        if(hotel_flag != ''){

          option_hotel_arr = JSON.parse(option_hotel_arr);
          for(var i=0;i<option_hotel_arr.length;i++){

              if(hotel_id == option_hotel_arr[i].hotel_id){
                var hotel_avail = check_status[0];
                var updated_by1 = updated_by;
                var note1 = note;
                var opt_availability = option_hotel_arr[i].availability;
                if(opt_availability!=''){
                  error_msg_alert("Hotel Availability Status already saved. Please email in case any change.");
                  return false;
                }
              }else{
                var hotel_avail = option_hotel_arr[i].availability;
                var updated_by1 = option_hotel_arr[i].reply_by;
                var note1 = option_hotel_arr[i].spec;
              }
              new_option_hotel_arr.push({
              'id' : option_hotel_arr[i].id,
              'availability' : hotel_avail,
              'city_id' : option_hotel_arr[i].city_id,
              'hotel_id' : option_hotel_arr[i].hotel_id,
              'mobile_no' : option_hotel_arr[i].mobile_no,
              'email_id' : option_hotel_arr[i].email_id,
              'reply_by' : updated_by1,
              'spec' : note1,
              'mail_sent' : 'true'
            });
          }
        }
        if(hotel_flag == ''){
          option_hotel_arr = option_hotel_arr;
          var hotel_avail = check_status[0];
          var hotel_updated_by1 = updated_by;
          var hotel_spec1 = note;
        }else{
          option_hotel_arr = new_option_hotel_arr;
          var hotel_avail = availability;
          var hotel_updated_by1 = oupdated_by;
          var hotel_spec1 = spec1;
        }
        response_arr = {
          'emp_id' : emp_id,
          'email_id' : email_id,
          'mail_sent' : "true",
          'availability' : hotel_avail,
          'reply_by' : hotel_updated_by1,
          'spec' : hotel_spec1,
          'option_hotel_arr' : option_hotel_arr
        };
        $('#btn_save').button('loading');
        $.ajax({
        type:'post',
        url: '../../../../../controller/package_tour/quotation/hotel_supplier_response.php',
        data:{ request_id : request_id, response_arr : response_arr,hotel_flag:hotel_flag},
        success: function(message){
          var message1 = message.trim();
          if(message1[0] == 'error'){
            error_msg_alert(message1[1]);
            $('#btn_save').button('reset');
          }
          else{
            success_msg_alert(message);
            $('#ressave_modal').modal('hide');
            setInterval(() => {
              window.close();
            },2500);
          }
        }
        });
      }
    });
});
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
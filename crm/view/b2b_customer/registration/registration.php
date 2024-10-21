<?php
include '../../../model/model.php';
global $app_name;
include_once "../../../Tours_B2B/layouts/login_header.php";
?>
        <a href="<?php echo BASE_URL ?>Tours_B2B/login.php" target="_blank" class="c-button block full-rounded uppercase">
            Login
        </a>
        </div>
    </div>
    </div>
</div>
</header>

    <!-- ********** Component :: Registration Page ********** -->
    <div class="c-coloredWrapper">
      <div class="container-fluid">
        <h2 class="c-heading lg extra-bold uppercase font-2">
          Registration Form
        </h2>
        <div class="loginWindow">
        <form id="frm_tab1">

        <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">

                <h5>Basic Details</h5>                

                <div class="row mg_tp_10">

                    <div class="col-md-3 col-sm-6 mg_bt_10">
                        <input class="form-control" type="text" id="company_name" name="company_name" placeholder="*Company Name" title="Company Name"  required /> 
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10">
                        <input class="form-control" type="text" id="acc_name" name="acc_name" placeholder="Accounting Name" title="Accounting Name" />
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10">
                        <select class="form-control" id='iata_status' title='IATA Status'  name='iata_status'>
                          <option value=''>IATA Status</option>
                          <option value='Approved'>Approved</option>
                          <option value='Not Approved'>Not Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10">
                        <input class="form-control" type="text" id="iata_reg" name="txt_mobile_no1" placeholder="IATA Reg.No" title="IATA Reg.No" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                        <input class="form-control" type="text" id="nature" name="nature" placeholder="Nature Of Business" title="Nature Of Business" />
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                      <select class="form-control" id='currency' title='Preferred Currency' name='currency' style='width:100%;' >
                        <option value=''>Preferred Currency</option>
                        <?php
                        $sq_currency = mysqlQuery("select id,currency_code from currency_name_master where 1");
                        while($row_currency = mysqli_fetch_assoc($sq_currency)){ ?>
                          <option value="<?= $row_currency['id'] ?>"><?= $row_currency['currency_code'] ?></option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                        <input class="form-control" type="number" id="telephone" name="telephone" placeholder="Telephone" title="Telephone"/>
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                        <input class="form-control" type="text" id="latitude" name="latitude" placeholder="Latitude" title="Latitude"/>
                    </div>
                  </div>

                  <div class="row mg_tp_10">
                      <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                        <input class="form-control" type="text" id="turnover_slab" name="turnover_slab" placeholder="Turnover Slab" title="Turnover Slab"/>
                      </div>
                      <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                        <input class="form-control" class="form-control" type="text" id="skype_id" name="skype_id" placeholder="Skype ID" title="Skype ID"/>
                      </div>
                      <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                        <input class="form-control" type="text" id="website" name="website" placeholder="Website" title="Website"/>
                      </div>
                      <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                        <div class="div-upload" role='button' title="Upload Company Logo" >
                          <div id="logo_upload_btn1" class="upload-button1"><span>Company Logo</span></div>
                          <span id="logo_proof_status" ></span>
                          <ul id="files" ></ul>
                          <input type="hidden" id="logo_upload_url" name="logo_upload_url" required>
                        </div>
                        <button type="button" data-toggle="tooltip" class="btn btn-excel" title="Note : Upload Image size below 100KB, resolution : 220X85."><i class="fa fa-question-circle"></i></button>
                      </div>
                  </div>
              </div>

              <div class="panel panel-default panel-body app_panel_style mg_tp_30 feildset-panel">
              <h5>Address Details</h5>

                <div class="row mg_tp_10">
                  <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                    <select id='city' name='city' class='form-control' style='width:100%;' title="City Name" required>
                      <?php get_cities_dropdown();?>
                    </select>
                  </div>
                  <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                    <input class="form-control" type="text" id="address1" name="address1" placeholder="Address1" title="Address1"/>
                  </div>
                  <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                    <input class="form-control" type="text" id="address2" name="address2" placeholder="Address2" title="Address2"/>
                  </div>
                  <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                    <input class="form-control" type="text" id="pincode" name="pincode" placeholder="Pincode" title="Pincode" onkeypress="return blockSpecialChar(event)" />
                  </div>
                </div>
                <div class="row mg_tp_10">
                  
                    <div class="col-md-3 col-xs-12">
                      <select class="form-control" name="cust_state" id="cust_state" title="State/Country Name" style="width : 100%" required>
                        <?php get_states_dropdown() ?>
                      </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                      <input class="form-control" type="text" id="timezone" name="timezone" placeholder="Timezone" title="Timezone"/>
                    </div>
                    <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                      <div class="div-upload" role='button' title="Upload Address Proof" >
                        <div id="address_upload_btn1" class="upload-button1"><span>Address Proof</span></div>
                        <span id="id_proof_status" ></span>
                        <ul id="files" ></ul>
                        <input type="hidden" id="address_upload_url" name="address_upload_url">
                      </div>
                      <button type="button" data-toggle="tooltip" class="btn btn-excel" title="Note : Only PDF,JPG, PNG files are allowed."><i class="fa fa-question-circle"></i></button>
                    </div>
                </div>
            </div>
            <div class="panel panel-default panel-body app_panel_style feildset-panel mg_tp_30">

                <h5>Contact Person Details</h5>
                <div class="row mg_tp_10">
                    <div class="col-md-3 col-sm-6">
                        <input class="form-control" type="text" id="contact_personf" name="contact_personf" placeholder="*First Name" title="First Name" required> 
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input class="form-control" type="text" id="contact_personl" name="contact_personl" placeholder="*Last Name" title="Last Name" required> 
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input class="form-control" type="text" id="email_id" name="email_id" placeholder="*Email ID"  title="Email ID" onchange="validate_email(this.id)" required >
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input class="form-control" type="text" id="mobile_no" name="mobile_no" placeholder="*Mobile No" title="Mobile No" onchange="mobile_validate(this.id);" required>
                    </div>
                </div>
                <div class="row mg_tp_10">
                  <div class="col-md-3 col-sm-6">
                      <input class="form-control" type="text"  id="whatsapp_no" name="whatsapp_no" placeholder="Whatsapp No" title="Whatsapp No">
                  </div>
                  <div class="col-md-3 col-sm-6">
                      <input class="form-control" type="text" id="designation" name="designation" placeholder="Designation" title="Designation">
                  </div>
                  <div class="col-md-3 col-sm-6">
                      <input class="form-control" type="text"  id="pan_card" name="pan_card" placeholder="Personal Identification No(PIN)" title="Personal Identification No(PIN)">
                  </div>
                  <div class="col-md-3 col-sm-6 text-left">
                    <div class="div-upload" role='button' title="Upload ID Proof" >
                      <div id="photo_upload_btn_p" class="upload-button1"><span>ID Proof</span></div>
                      <span id="photo_status" ></span>
                      <ul id="files" ></ul>
                      <input type="hidden" id="photo_upload_url" name="photo_upload_url">
                    </div>
                    <button type="button" data-toggle="tooltip" class="btn btn-excel" title="Note : Only PDF,JPG, PNG files are allowed."><i class="fa fa-question-circle"></i></button>
                  </div>
                </div>
            </div>
            <div class="row text-center mg_tp_20">
              <div class="col-md-12 col-md-offset-4">
              <button class="btn btn-sm btn-success" id="btn_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
              </div>
            </div>
        </form>
        </div>
      </div>
    </div>
    <!-- ********** Component :: Registration Page End ********** -->

    <!-- ********** Component :: Login Page ********** -->
    <div class="c-container aboutUs">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-5 col-md-6 col-sm-12">
            <div class="about-image">
              <img src="<?php echo BASE_URL ?>Tours_B2B/images/about.jpg" alt="about" />
            </div>
          </div>
          <div class="col-lg-7 col-md-6 col-sm-12">
            <div class="subheading uppercase">
              GET AMAZING EXPERIECNED WITH US
            </div>
            <div class="mainheading uppercase">ACROSS THE WORLD</div>
            <div class="staticText">
              We built on this strong foundation aims to provide great customer satisfaction and an exemplary holiday experience. Planning a once to travel with us and get the great experience. Our experts can get you what you want and in the minimal time. 
            </div>
            <div class="staticText">
              We're one of the best travel management companies across the world, it has the experience and infrastructure to handle any customer demand. In addition, we provide customize tours, hotels, flights, visa, transfer, bus, train & activities world wide, We also plans corporate tours, incentive trips, college/school excursions, business travel and much, much more.
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ********** Component :: Login Page ********** -->
    <?php
    $sq_cms_q = mysqli_fetch_assoc(mysqlQuery("SELECT * FROM `b2b_settings`"));
    if($sq_cms_q['why_choose_flag'] != 'Hide'){
    ?>
    <!-- ********** Component :: Info Section ********** -->
    <section class="c-container with-gray-background">
      <div class="container">
        <h2 class="container-heading">Why Choose Us?</h2>

        <!-- *** Component :: Card Pallet ***** -->
        <div class="c-cardPallet">
          <div class="overflow-hidden">
            <div class="cardPalletBox column-5-no-margin">
              <?php
              $images = ($sq_cms_q['why_choose_us']!='')?json_decode($sq_cms_q['why_choose_us']):[];
              for($i=0;$i<sizeof($images);$i++){
                  $url = $images[$i]->image_url;
                  $pos = strstr($url,'uploads');
                  if ($pos != false){
                      $newUrl1 = preg_replace('/(\/+)/','/',$images[$i]->image_url); 
                      $download_url = BASE_URL.str_replace('../', '', $newUrl1);
                  }else{
                      $download_url = $images[$i]->image_url; 
                  }
                  ?>
              <article class="icon-box">
                <div class="imageBox">
                  <img src="<?= $download_url ?>" alt="img" />
                </div>
                <h5 class="boxTitle"><?= $images[$i]->title ?></h5>
                <p class="boxSubTitle">
                <?= $images[$i]->description ?>
                </p>
              </article>
                  <?php } ?>
            </div>
          </div>
        </div>
        <!-- *** Component :: Card Pallet End ***** -->
      </div>
    </section>
    <!-- ********** Component :: Info Section ********** -->
    <?php } ?>

    <!-- ********** Component :: Destination Ideas Section ********** -->
    <div class="c-container with-map">
      <div class="container">
        <div class="container-heading">
          AMAZING TRAVEL SERVICES FOR YOUR END CUSTOMERS
        </div>

        <div class="row">
          <div class="col">
            <div class="overflow-hidden">
              <div class="cardPalletBox column-5-no-margin type-03">
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-users-group icon"></i>
                  </div>
                  <h4 class="boxTitle">Group Tours</h4>
                </article>

                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-airplane icon"></i>
                  </div>
                  <h4 class="boxTitle">Flights</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-hotel icon"></i>
                  </div>
                  <h4 class="boxTitle">Hotels</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-car icon"></i>
                  </div>
                  <h4 class="boxTitle">Transfer</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-hot-air-balloon icon"></i>
                  </div>
                  <h4 class="boxTitle">Activities</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-passport icon"></i>
                  </div>
                  <h4 class="boxTitle">Visa</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-bus icon"></i>
                  </div>
                  <h4 class="boxTitle">Bus</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-train icon"></i>
                  </div>
                  <h4 class="boxTitle">Train</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-ship icon"></i>
                  </div>
                  <h4 class="boxTitle">Cruise</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-change icon"></i>
                  </div>
                  <h4 class="boxTitle">Forex</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-travel-insurance icon"></i>
                  </div>
                  <h4 class="boxTitle">Insurance</h4>
                </article>
                <article class="icon-box">
                  <div class="imageBox">
                    <i class="itours-b2b-guide icon"></i>
                  </div>
                  <h4 class="boxTitle">Guide</h4>
                </article>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ********** Component :: Destination Ideas Section End ********** -->

    <?php if($sq_cms_q['popular_activities_flag'] != 'Hide'){
        $popular_activities = ($sq_cms_q['popular_activities']!='' && $sq_cms_q['popular_activities']!='null')?json_decode($sq_cms_q['popular_activities']):[];
      ?>
    <!-- ********** Component :: Trending ********** -->
    <div class="c-container with-gray-background">
      <div class="container">
        <h2 class="container-heading">Trending Activities</h2>

        <div class="c-popularDestinations">
          <div class="grid-section">
            <?php
            for($i=0;$i<sizeof($popular_activities);$i++){
              $exc_id = $popular_activities[$i]->exc_id;
              //Activity Image
              $sq_exc = mysqli_fetch_assoc(mysqlQuery("select entry_id from excursion_master_tariff where excursion_name='$exc_id'"));
              $sq_dest1 = mysqli_fetch_assoc(mysqlQuery("select image_url from excursion_master_images where exc_id='$sq_exc[entry_id]'"));
              $newUrl1 = preg_replace('/(\/+)/','/',$sq_dest1['image_url']); 
              $newUrl = BASE_URL.str_replace('../', '', $newUrl1);
              $newUrl = ($sq_dest1['image_url'] != '') ? $newUrl : BASE_URL."Tours_B2B/images/activity.png";
              ?>
              <div class="grid" style="background-image: url('<?= $newUrl ?>');">
                <!-- <span>India</span> -->
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    <!-- ********** Component :: Trending End ********** -->
    <?php } ?>
<div id="site_alert"></div>

<!-- <script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script> -->
<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>
<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<?php
include_once "../../../Tours_B2B/layouts/login_footer.php";
?>
<link href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"></link>
<script src="<?php echo BASE_URL ?>js/app/data_reflect.js"></script>
<script src="<?php echo BASE_URL ?>js/app/validation.js"></script> 
<script src="<?php echo BASE_URL ?>js/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL ?>js/dataTables.bootstrap.min.js"></script>
<script src="<?php echo BASE_URL ?>js/bootstrap-tagsinput.min.js"></script>  

<script>
$('#currency,#cust_state').select2();
$('#city').select2({minimumInputLength:1});

upload_logo_proof();
function upload_logo_proof(){
    var btnUpload=$('#logo_upload_btn1');
    $(btnUpload).find('span').text('Company Logo');
    new AjaxUpload(btnUpload, {
      action: '../inc/upload_logo.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){  

        if (! (ext && /^(png|jpg|jpeg)$/.test(ext))){ 
          error_msg_alert('Only PNG,JPG or JPEG files are allowed');
          return false;
        }
        $(btnUpload).find('span').text('Uploading...');
      },
      onComplete: function(file, response){
       // alert(response);
        if(response=="error1"){
            $(btnUpload).find('span').text('Company Logo');
            error_msg_alert('Maximum size exceeds');
            return false;
        }
        else if(response==="error"){
          error_msg_alert("File is not uploaded.");
          $(btnUpload).find('span').text('Upload');
        }
        else{
          $(btnUpload).find('span').text('Uploaded');
          $("#logo_upload_url").val(response);
        }
      }
    });
}

upload_address_proof();
function upload_address_proof(){
    var btnUpload=$('#address_upload_btn1');
    $(btnUpload).find('span').text('Address Proof');    

    new AjaxUpload(btnUpload, {
      action: '../inc/upload_address_proof.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){  

        if (! (ext && /^(jpg|png|jpeg|pdf)$/.test(ext))){ 
          error_msg_alert('Only PDF,JPG, PNG files are allowed');
          return false;
        }
        $(btnUpload).find('span').text('Uploading...');
      },

      onComplete: function(file, response){

        if(response==="error"){          
          error_msg_alert("File is not uploaded.");           
          $(btnUpload).find('span').text('Upload');
        }
        else{
          $(btnUpload).find('span').text('Uploaded');
          $("#address_upload_url").val(response);
        }
      }
    });
}

upload_id_proof();
function upload_id_proof(){

    var btnUpload=$('#photo_upload_btn_p');
    $(btnUpload).find('span').text('ID Proof');
    new AjaxUpload(btnUpload, {
      action: '../inc/upload_photo_proof.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){  
        if (! (ext && /^(pdf|jpg|png|jpeg)$/.test(ext))){ 
          error_msg_alert('Only PDF,JPG, PNG files are allowed');
          return false;
        }
        $(btnUpload).find('span').text('Uploading...');
      },

      onComplete: function(file, response){

        if(response==="error"){          
          error_msg_alert("File is not uploaded.");
          $(btnUpload).find('span').text('Upload');
        }
        else{
          $(btnUpload).find('span').text('Uploaded');
          $("#photo_upload_url").val(response);
        }
      }
    });
}

$(function(){
$('#frm_tab1').validate({
	rules:{
	},
	submitHandler:function(form){
  
  $('#btn_save').prop('disabled',true);
  var base_url = $('#base_url').val();
  var company_logo = $("#logo_upload_url").val();
  if(company_logo==''){
    error_msg_alert('Company logo required!'); 
    $('#btn_save').prop('disabled',false);
    return false;
  }
  //Basic Details
  var company_name = $("#company_name").val();
  var acc_name = $("#acc_name").val();
  var iata_status = $("#iata_status").val();
  var iata_reg = $("#iata_reg").val();
  var nature = $("#nature").val();
  var currency = $("#currency").val();
  var telephone = $('#telephone').val(); 
  var latitude = $("#latitude").val();
  var turnover_slab = $("#turnover_slab").val();
  var skype_id = $("#skype_id").val();
  var website = $("#website").val(); 

  //Address Details
  var city = $("#city").val();
  var address1 = $("#address1").val(); 
  var address2 = $("#address2").val(); 
  var pincode = $("#pincode").val();
  // var country = $('#country').val();
  var cust_state = $('#cust_state').val();
  var timezone = $('#timezone').val(); 
  var address_upload_url = $('#address_upload_url').val();

  //Contact Person Details
  var contact_personf = $('#contact_personf').val();
  var contact_personl = $('#contact_personl').val();
  var email_id = $('#email_id').val();
  var mobile_no = $('#mobile_no').val();
  var whatsapp_no = $('#whatsapp_no').val();
  var designation = $('#designation').val();
  var pan_card = $('#pan_card').val();
  var photo_upload_url = $('#photo_upload_url').val();

  $('#btn_save').button('loading');
  $.ajax({
      type:'post',
      url: '../../../controller/b2b_customer/reg_customer_save.php',
      data:{ company_name : company_name, acc_name : acc_name, iata_status : iata_status, iata_reg : iata_reg, nature : nature, currency : currency, telephone : telephone, latitude : latitude, turnover_slab : turnover_slab, skype_id : skype_id, website : website, 
      address1 : address1,address2 : address2, city : city , pincode : pincode , state:cust_state, timezone : timezone, address_upload_url : address_upload_url,
      contact_personf : contact_personf , contact_personl : contact_personl,email_id:email_id, mobile_no : mobile_no, whatsapp_no : whatsapp_no, designation : designation, pan_card : pan_card, photo_upload_url : photo_upload_url,company_logo:company_logo},
      success: function(message){
        var data = message.split('--');
        if(data[0] == 'error'){
          error_msg_alert(data[1]); 
          $('#btn_save').button('reset');
          $('#btn_save').prop('disabled',false);
          return false;
        }
        else{
          success_msg_alert(message);
          $('#btn_save').prop('disabled',false);
          setInterval(() => {
            window.location.replace('../../../Tours_B2B/login.php');
          },2000);
        }
      }
    });
  }
});
});
</script>

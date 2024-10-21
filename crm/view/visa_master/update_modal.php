<?php
include "../../model/model.php";
$entry_id = $_POST['entry_id'];
$sq_visa = mysqli_fetch_assoc(mysqlQuery("select * from visa_crm_master where entry_id='$entry_id'"));
?>

<div class="modal fade" id="update_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">

  <div class="modal-dialog modal-lg" role="document">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <h4 class="modal-title" id="myModalLabel">Update Visa Information</h4>

      </div>

      <div class="modal-body">

        <form id="frm_visa_update">

          <input type="hidden"  value="<?= $entry_id ?>" id="entry_id" name="entry_id">

          <div class="row">

              <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                  <select name="visa_country_name" id="visa_country_name" class="form-control" title="Visa Country Name" style="width:100%">
                    <option value="<?= $sq_visa['country_id'] ?>"><?= $sq_visa['country_id'] ?></option>
                    <option value="">Visa Country</option>
                    <?php
                    $sq_country = mysqlQuery("select * from country_list_master");
                    while($row_country = mysqli_fetch_assoc($sq_country)){
                      ?>
                      <option value="<?= $row_country['country_name'] ?>"><?= $row_country['country_name'] ?></option>
                      <?php
                    }
                    ?>
                </select>

              </div>

              <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                  <select name="visa_type" id="visa_type" class="form-control" title="Visa Type" style="width:100%">

                    <option value="<?= $sq_visa['visa_type'] ?>"><?= $sq_visa['visa_type'] ?></option>

                    <option value="">Visa Type</option>

                    <?php 

                    $sq_visa_type = mysqlQuery("select * from visa_type_master");

                    while($row_visa_type = mysqli_fetch_assoc($sq_visa_type)){

                        ?>

                        <option value="<?= $row_visa_type['visa_type'] ?>"><?= $row_visa_type['visa_type'] ?></option>

                        <?php

                    }

                    ?>

                </select>

              </div>

              <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                  <input type="text" name="fees"  class="form-control" title="Basic Amount" value="<?= $sq_visa['fees'] ?>" placeholder="Basic Amount" id="fees" onchange="validate_balance(this.id);">

              </div>

              <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                  <input type="text" name="markup" class="form-control" title="Markup cost" placeholder="Markup cost" value="<?= $sq_visa['markup'] ?>" id="markup" onchange="validate_balance(this.id);">

              </div>              

          </div>

          <div class="row">
                          
              <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10">

                  <input type="text" name="time_taken1" class="form-control" onchange="validate_specialChar(this.id);" title="Time Taken" placeholder="Time Taken" value="<?= $sq_visa['time_taken'] ?>" id="time_taken1">

              </div>
              <div class="col-md-3 col-sm-6">
                <?php
                $status = ($sq_visa['status'] == '1') ? 'Active' : 'Inactive';
                ?>
                <select class="<?= $active_inactive_flag ?>" name="active_flag1" id="active_flag1" title="Status">
                <option  value="<?php echo $sq_visa['status']; ?>"><?php echo $status; ?></option>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
              <div class="col-md-3 col-sm-6 col-xs-12 text-left">          

                <div class="div-upload">

                  <div id="photo_upload_visa" class="upload-button1"><span>Upload Form1</span></div>

                  <span id="photo_status" ></span>

                  <ul id="files" ></ul>

                  <input type="hidden" id="photo_upload_url1" name="photo_upload_url1" value="<?= $sq_visa['upload_url']?>">

                </div>

              </div>
              <div class="col-md-3 col-sm-6 col-xs-12 text-left">          

                <div class="div-upload">

                  <div id="photo_upload2" class="upload-button2"><span>Upload Form2</span></div>

                  <span id="photo_status2" ></span>

                  <ul id="files" ></ul>

                  <input type="hidden" id="photo_upload_url2" name="photo_upload_url2" value="<?= $sq_visa['upload_url2']?>">

                </div>

              </div> 
            </div> 
          <div class="row">
              <div class="col-md-3 col-sm-6 col-xs-12 text-left mg_tp_10">

                  <div class="div-upload">

                      <div id="photo_upload3" class="upload-button3"><span>Upload Form3</span></div>

                      <span id="photo_status3"></span>

                      <ul id="files"></ul>

                      <input type="hidden" id="photo_upload_url3" name="photo_upload_url3" value="<?= $sq_visa['upload_url3']?>">

                  </div>

              </div>
              <div class="col-md-3 col-sm-6 col-xs-12 text-left mg_tp_10">

                  <div class="div-upload">

                      <div id="photo_upload4" class="upload-button4"><span>Upload Form4</span></div>

                      <span id="photo_status4"></span>

                      <ul id="files"></ul>

                      <input type="hidden" id="photo_upload_url4" name="photo_upload_url4" value="<?= $sq_visa['upload_url4']?>">

                  </div>

              </div>
              <div class="col-md-3 col-sm-6 col-xs-12 text-left mg_tp_10">

                  <div class="div-upload">

                      <div id="photo_upload5" class="upload-button5"><span>Upload Form5</span></div>

                      <span id="photo_status5"></span>

                      <ul id="files"></ul>

                      <input type="hidden" id="photo_upload_url5" name="photo_upload_url5" value="<?= $sq_visa['upload_url5']?>">

                  </div>
					        <button type="button" data-toggle="tooltip" class="btn btn-excel" title="Note: Upload only PDF or Text files."><i class="fa fa-question-circle"></i></button>

              </div>

          </div>
          <div class="row mg_tp_20">
            <div class="col-xs-12 mg_bt_10">
              <h3 class="editor_title">List of documents</h3>
              <textarea placeholder="List of documents" id="doc_list" title="Documents" name="doc_list" class="feature_editor form-control" rows="2"><?= $sq_visa['list_of_documents'] ?></textarea>
            </div> 
          </div>

          <div class="row text-center mg_tp_20">

              <div class="col-md-12">

                <button class="btn btn-sm btn-success" id="btn_update"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>  

              </div>             

            </div>

        </form>

      </div>

</div>

</div>

</div>

</div>



<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>

<script>
$('#update_modal').modal('show');
$('#visa_country_name,#visa_type').select2();

upload_user_pic_attch();
function upload_user_pic_attch(){

    var btnUpload=$('#photo_upload_visa');
    var file_url = $("#photo_upload_url1").val();
    if(file_url != '') { var file_text = 'Uploaded'; }
    else{ var file_text = 'Upload Form1'; }
    $(btnUpload).find('span').text(file_text);   

    new AjaxUpload(btnUpload, {
      action: 'upload_photo_proof.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){  
        if (! (ext && /^(pdf|txt)$/.test(ext))){
         error_msg_alert('Only pdf,txt files are allowed');
         return false;
        }
        $(btnUpload).find('span').text('Uploading...');
      },
      onComplete: function(file, response){
        if(response==="error"){
          error_msg_alert("File is not uploaded.");      
          $(btnUpload).find('span').text('Upload Form1');
        }
        else{ 
          $(btnUpload).find('span').text('Uploaded');
          $("#photo_upload_url1").val(response);
        }
      }
    });
}

upload_user_pic_attch2();
function upload_user_pic_attch2(){

    var btnUpload=$('#photo_upload2');
    var file_url = $("#photo_upload_url2").val();
    if(file_url != '') { var file_text = 'Uploaded'; }
    else{ var file_text = 'Upload Form2'; }
    $(btnUpload).find('span').text(file_text);   

    new AjaxUpload(btnUpload, {
      action: 'upload_photo_proof.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){  
        if (! (ext && /^(pdf|txt)$/.test(ext))){
          error_msg_alert('Only pdf,txt files are allowed');
          return false;
        }
        $(btnUpload).find('span').text('Uploading...');
      },
      onComplete: function(file, response){
        if(response==="error"){
          error_msg_alert("File is not uploaded.");      
          $(btnUpload).find('span').text('Upload Form2');
        }
        else{ 
          $(btnUpload).find('span').text('Uploaded');
          $("#photo_upload_url2").val(response);
        }
      }
    });
}
upload_user_pic_attch3();
function upload_user_pic_attch3() {

    var btnUpload = $('#photo_upload3');
    var file_url = $("#photo_upload_url3").val();
    if(file_url != '') { var file_text = 'Uploaded'; }
    else{ var file_text = 'Upload Form3'; }
    $(btnUpload).find('span').text(file_text);   

    new AjaxUpload(btnUpload, {
        action: 'upload_photo_proof.php',
        name: 'uploadfile',
        onSubmit: function(file, ext) {
            if (!(ext && /^(pdf|txt)$/.test(ext))) {
                error_msg_alert('Only pdf,txt files are allowed');
                return false;
            }
            $(btnUpload).find('span').text('Uploading...');
        },
        onComplete: function(file, response) {
            if (response === "error") {
                error_msg_alert("File is not uploaded.");
                $(btnUpload).find('span').text('Upload Form3');
            } else {
                $(btnUpload).find('span').text('Uploaded');
                $("#photo_upload_url3").val(response);
            }
        }
    });
}
upload_user_pic_attch4();
function upload_user_pic_attch4() {

    var btnUpload = $('#photo_upload4');
    var file_url = $("#photo_upload_url4").val();
    if(file_url != '') { var file_text = 'Uploaded'; }
    else{ var file_text = 'Upload Form4'; }
    $(btnUpload).find('span').text(file_text);   

    new AjaxUpload(btnUpload, {
        action: 'upload_photo_proof.php',
        name: 'uploadfile',
        onSubmit: function(file, ext) {
            if (!(ext && /^(pdf|txt)$/.test(ext))) {
                error_msg_alert('Only pdf,txt files are allowed');
                return false;
            }
            $(btnUpload).find('span').text('Uploading...');
        },
        onComplete: function(file, response) {
            if (response === "error") {
                error_msg_alert("File is not uploaded.");
                $(btnUpload).find('span').text('Upload Form4');
            } else {
                $(btnUpload).find('span').text('Uploaded');
                $("#photo_upload_url4").val(response);
            }
        }
    });
}
upload_user_pic_attch5();
function upload_user_pic_attch5() {

    var btnUpload = $('#photo_upload5');
    var file_url = $("#photo_upload_url5").val();
    if(file_url != '') { var file_text = 'Uploaded'; }
    else{ var file_text = 'Upload Form5'; }
    $(btnUpload).find('span').text(file_text);   

    new AjaxUpload(btnUpload, {
        action: 'upload_photo_proof.php',
        name: 'uploadfile',
        onSubmit: function(file, ext) {
            if (!(ext && /^(pdf|txt)$/.test(ext))) {
                error_msg_alert('Only pdf,txt files are allowed');
                return false;
            }
            $(btnUpload).find('span').text('Uploading...');
        },
        onComplete: function(file, response) {
            if (response === "error") {
                error_msg_alert("File is not uploaded.");
                $(btnUpload).find('span').text('Upload Form5');
            } else {
                $(btnUpload).find('span').text('Uploaded');
                $("#photo_upload_url5").val(response);
            }
        }
    });
}

$(function(){

  $('#frm_visa_update').validate({

    rules:{
      visa_country_name : { required : true },
      visa_type : { required : true },
      fees : { required : true }
    },

    submitHandler:function(form){

        var base_url = $('#base_url').val();
        var entry_id = $('#entry_id').val();
        var visa_country_name = $("#visa_country_name").val();
        var visa_type = $("#visa_type").val();
        var fees = $("#fees").val();
        var markup = $("#markup").val();
        var time_taken = $("#time_taken1").val();
        var photo_upload_url = $('#photo_upload_url1').val();
        var photo_upload_url2 = $('#photo_upload_url2').val();
        var photo_upload_url3 = $('#photo_upload_url3').val();
        var photo_upload_url4 = $('#photo_upload_url4').val();
        var photo_upload_url5 = $('#photo_upload_url5').val();
        var doc_list = $('#doc_list').val();
        var active_flag = $('#active_flag1').val();

        $('#btn_update').button('loading');
        $.post( 

              base_url+"controller/visa_master/visa_master_update.php",

              { entry_id : entry_id, visa_country_name : visa_country_name, visa_type : visa_type, fees : fees, markup : markup, time_taken : time_taken, photo_upload_url : photo_upload_url,photo_upload_url2:photo_upload_url2,doc_list : doc_list,active_flag:active_flag,photo_upload_url3: photo_upload_url3,photo_upload_url4: photo_upload_url4,photo_upload_url5: photo_upload_url5},

              function(data) {

                  $('#btn_update').button('reset');

                  var msg = data.split('--');

                  if(msg[0]=="error"){

                    error_msg_alert(msg[1]);

                  }else{

                    msg_alert(data);
                    update_b2c_cache();

                    $('#update_modal').modal('hide');  

                    $('#update_modal').on('hidden.bs.modal', function(){

                      visa_list_reflect();

                    });

                  }

              });

      }

  });

});





</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
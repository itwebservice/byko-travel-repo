<?php
include "../../../model/model.php";
$entry_id = $_POST['entry_id'];
$row_update = mysqli_fetch_assoc(mysqlQuery("select * from cms_master_entries where id = '$entry_id'"));
$sq_type = mysqli_fetch_assoc(mysqlQuery("select * from cms_master where id='$row_update[id]'"));

if($sq_type['type_id'] == '1'){ $type = 'Transactional'; }
    elseif($sq_type['type_id'] == '2'){ $type = 'Reminder'; }

$reminder_array = array('68','70','74','78','79');

$name = "Super";
$add_class = "hidden";
if($sq_type['id'] == '68' || $sq_type['id'] == '70' || $sq_type['id'] == '74' || $sq_type['id'] == '78' || $sq_type['id'] == '79'){
  $add_class = "number";
}
$days = ($sq_type['days'] == 0) ? '' : $sq_type['days'];
?>
<div class="modal fade" id="cms_save_modal" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Update Email Draft</h4>
      </div>
      <div class="modal-body">
  		  <form id="frm_cms_save">
            <input type="hidden" id="entry_id" name="entry_id" value="<?= $entry_id ?>">   
            <input type="hidden" id="reminder_array" name="reminder_array" value='<?= json_encode($reminder_array) ?>'>   
            <div class="row"> 
              <div class="col-md-6 col-sm-6 mg_bt_10_xs">
                  <select id="email_for" name="email_for" style="width:100%" title="Email for" disabled>
                      <option value="<?= $sq_type['draft_for'] ?>"><?= $sq_type['draft_for'] ?></option>
                  </select>
              </div>  
              <div class="col-md-6 col-sm-6 mg_bt_10_xs">
                <textarea id="subject" name="subject" placeholder="Subject Title Eg. Visa Booking Confirmation" title="Subject Title" rows="1"><?= $row_update['subject'] ?></textarea>
              </div>    
            </div>
            <div class="row mg_tp_30">
              <div class="col-md-6">
                <h3 class="editor_title">Draft</h3>
                <textarea class="feature_editor" id="draft" name="draft" title="Draft"><?= $row_update['draft'] ?></textarea>
              </div>  
              <div class="col-md-6">
                <h3 class="editor_title">Signature</h3>
                <textarea class="feature_editor" id="signature" name="signature" title="Signature" rows="1"><?= $row_update['signature'] ?></textarea>
              </div>             
            </div>
            <div class="row mg_tp_10">           
              <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                  <select id="active_flag" name="active_flag" style="width:100%" title="Status">
                      <option value="<?= $row_update['active_flag'] ?>"><?= $row_update['active_flag'] ?></option>
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                  </select>
              </div>   
              <div class="col-md-3 col-sm-6 mg_bt_10_xs">
                <input type="<?= $add_class ?>" id="no_of_days" name="no_of_days" placeholder="*No of days to send reminder mail" title="No of days to send reminder mail" value="<?= $days ?>">
              </div>        
            </div>
      			<div class="row mg_tp_20 text-center">
      				<div class="col-md-12">
      					<button class="btn btn-sm btn-success" id="cms_btn_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Update</button>
              </div>    
      			</div>
  	  </form>
      </div>
    </div>
  </div>
</div>

<div id="draft_reader" class="hidden"></div>

<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>

<script type="text/javascript">  
  function display_fraft(){
    var draft = $("#draft").val();
    $('#draft_reader').html(draft);
    $('#draft_reader div, #draft_reader b, #draft_reader span, #draft_reader font').css({"background-color": "fff", "color": "#333", "font-size" : "small"});signature
    var ready_html = $('#draft_reader').html();
    $("#draft").html(ready_html);

    var draft = $("#signature").val();
    $('#draft_reader').html(draft);
    $('#draft_reader div, #draft_reader b, #draft_reader span, #draft_reader font').css({"background-color": "fff", "color": "#333", "font-size" : "small"});
    var ready_html = $('#draft_reader').html();
    $("#signature").html(ready_html);
  }
  // display_fraft();
</script>

<script>
$('#cms_save_modal').modal('show');

///////////////////////***Hotel Master Save start*********//////////////

$(function(){
  $('#frm_cms_save').validate({

    rules:{
            subject : { required: true },
            draft : { required: true },
            signature :  { required : true },
    },

    submitHandler:function(form){

      var base_url = $("#base_url").val();
      var entry_id = $("#entry_id").val();
      var subject = $("#subject").val();

      var draft = $("#draft").val();
      var reminder_array = JSON.parse($('#reminder_array').val());
      var no_of_days = $('#no_of_days').val();
      const exists = reminder_array.includes(entry_id);
      if(exists && no_of_days == ''){
        error_msg_alert("Enter no of days to send reminder mail!");
        return false;
      }
      console.log(no_of_days);
      draft = draft.split('background-color: rgb(255, 255, 255); color: rgb(51, 51, 51);').join('background-color: transparent; color: rgb(255, 255, 255);');
      draft = draft.split('p dir="ltr" style="').join('p dir="ltr" style="display:block!important;');
      draft = draft.split('color:#000000').join('color:#888888');
      draft = draft.split('rgb(0, 0, 0);').join('#888888;');
      var signature = $("#signature").val();
      signature = signature.split('background-color: rgb(255, 255, 255); color: rgb(51, 51, 51);').join('background-color: transparent; color: rgb(255, 255, 255);');
      
      var active_flag = $("#active_flag").val();

      $('#cms_btn_save').button('loading');
      
      $.ajax({
        type:'post',
        url:base_url+'controller/cms/email_cms_master.php',
        data:{ entry_id : entry_id, subject : subject, draft : draft, signature : signature, active_flag : active_flag,no_of_days:no_of_days },
        success:function(result){
           $('#cms_btn_save').button('reset');
           $('#cms_save_modal').modal('hide');
            msg_alert(result);
            list_reflect();
        }
      });
    }
  });
});
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>

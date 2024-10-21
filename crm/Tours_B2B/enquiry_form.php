
<!-- ***** Enquiry Modal ***** -->
<div class="modal fade c-modal" id="enquiryModal"  role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="enquiryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="enquiryModalTitle">Enquire with us</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="frm_enquiry">
            <div class="modal-body">
                <!-- ***** Room 1 Section ***** -->
                <div class="clearfix">
                <div class="row">
                    <div class="col-md-6 col-xs-12">
                    <div class="form-group type-1">
                        <div class="c-select2DD">
                        <input
                            type="text" id="enq_first_name" value=""
                            class="c-textbox rounded"
                            placeholder="*First Name"
                            autocomplete="off"/>
                        </div>
                    </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                    <div class="form-group type-1">
                        <div class="c-select2DD">
                        <input
                            type="text" id="enq_last_name" value=""
                            class="c-textbox rounded"
                            placeholder="*Last Name"
                            autocomplete="off"/>
                        </div>
                    </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                    <div class="form-group type-1">
                        <div class="c-select2DD">
                        <input
                            type="email" id="enq_email" value=""
                            class="c-textbox rounded"
                            placeholder="*Email ID"
                            autocomplete="off"/>
                        </div>
                    </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                    <div class="form-group type-1">
                        <div class="c-select2DD">
                        <input
                            type="text" id="enq_contact" value=""
                            class="c-textbox rounded"
                            placeholder="Contact Number"
                            autocomplete="off"/>
                        </div>
                    </div>
                    </div>
                    <div class="col-md-12 col-xs-12">
                    <div class="form-group type-1">
                        <div class="c-select2DD">
                        <textarea
                            type="text" id="enq_message"
                            class="c-textbox rounded"
                            placeholder="*Enquiry Message"
                            autocomplete="off"
                            rows="3"></textarea>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
                <!-- ***** Room 1 Section End ***** -->
                <div class="row text-center">
                <div class="col-md-12 col-md-offset-4 col-xs-12">
                    <div class="form-group type-1">
                        <button id="btn_enquiry" class="c-button md">Send Enquiry</button>
                    </div>
                </div></div>
            </div>
        </form>
        </div>
    </div>
</div>
<!-- ***** Enquiry Modal End ***** -->
<script>
$('#enquiryModal').modal('show');
$(function(){
    $('#frm_enquiry').validate({
        rules:{
            enq_first_name : { required : true },
            enq_last_name : { required : true },
            enq_email : { required : true },
            enq_message : { required : true },
        },
        submitHandler:function(form){
            $('#btn_enquiry').prop('disabled',true);
            var base_url = $('#base_url').val();
            //Basic Details
            var enq_first_name = $("#enq_first_name").val();
            var enq_last_name = $("#enq_last_name").val();
            var enq_email = $("#enq_email").val();
            var enq_contact = $("#enq_contact").val();
            var enq_message = $("#enq_message").val();
            if(enq_first_name == ''){
                $('#btn_enquiry').prop('disabled',false);
                error_msg_alert("Enter First Name!");
                return false;
            }
            if(enq_last_name == ''){
                $('#btn_enquiry').prop('disabled',false);
                error_msg_alert("Enter Last Name!");
                return false;
            }
            if(enq_email == ''){
                $('#btn_enquiry').prop('disabled',false);
                error_msg_alert("Enter Email ID!");
                return false;
            }
            if(enq_message == ''){
                $('#btn_enquiry').prop('disabled',false);
                error_msg_alert("Enter Enquiry Message!");
                return false;
            }
            $('#btn_enquiry').button('loading');
            $.ajax({
                type:'post',
                url: base_url+'controller/b2b_customer/enquiry_form.php',
                data:{ enq_first_name : enq_first_name, enq_last_name : enq_last_name, enq_email : enq_email, enq_contact : enq_contact, enq_message : enq_message },
                success: function(message){
                    success_msg_alert(message);
                    $('#enquiryModal').modal('hide');
                    $('#btn_enquiry').prop('disabled',false);
                    $('#btn_enquiry').button('reset');
                    return false;
                }
            });
        }
    });
});
</script>
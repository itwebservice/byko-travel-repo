<?php
include "../../../model/model.php";
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '' ;
$sq_query = mysqli_fetch_assoc(mysqlQuery("select * from b2b_users where id='$user_id'"));
global $encrypt_decrypt, $secret_key;
$username = $encrypt_decrypt->fnDecrypt($sq_query['username'], $secret_key);
$password = $encrypt_decrypt->fnDecrypt($sq_query['password'], $secret_key);
?>
<div class="modal fade" id="update_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static"
        data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Edit Details</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <form id="update_user">
                
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-12">
                        <input type="text" class="form-control" name="full_name1" id="full_name1" placeholder="Full Name" title="Full Name" value="<?=$sq_query['full_name']?>" onkeypress="return blockSpecialChar(event);" required />
                    </div>
                    <div class="col-md-4  col-sm-6 col-12">
                        <input type="email" class="form-control" name="u_email_id1" id="u_email_id1" placeholder="Email ID" title="Email ID" value="<?=$sq_query['email_id']?>" required />
                    </div>
                    <div class="col-md-4  col-sm-6 col-12">
                        <input type="number" class="form-control" name="u_mobile_no1" id="u_mobile_no1" placeholder="Mobile No" title="Mobile No" value="<?=$sq_query['mobile_no']?>" required />
                    </div>
                    <div class="col-md-4 col-sm-6 col-12" style="margin-top:10px;">
                        <input type="text" class="form-control" name="u_username1" id="u_username1" placeholder="Username" title="Username" value="<?=$username?>" required />
                    </div>
                    <div class="col-md-4 col-sm-6 col-12" style="margin-top:10px;">
                        <input type="text" class="form-control" name="upassword1" id="upassword1" placeholder="New Password" title="New Password" value="<?=$password?>" required />
                    </div>
                    <div class="col-md-4 col-sm-6 col-12" style="margin-top:10px;">
                        <select class="form-control" id="ustatus">
                            <option value="<?=$sq_query['status']?>"><?=$sq_query['status']?></option>
                            <?php
                            if($sq_query['status'] == 'Active'){ ?>
                                <option value="Inactive">Inactive</option>
                            <?php } else{ ?>
                                <option value="Active">Active</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-top:20px;">
                    <div class="col-12 text-center">
                        <button class="c-button">Edit</button>
                    </div>
                </div>
                <input type="hidden" id="user_id" value='<?= $user_id ?>'/>
            </form>
        </div>
    </div>
</div>
<script>
$('#update_modal').modal('show');
$('#update_user').validate({
    rules:{
        full_name1 : {required:true},
        u_email_id1 : {required:true},
        u_username1 : {required:true},
        urepassword1 : {required:true},
        u_mobile_no1 : {required:true}
    },
    submitHandler:function(form){
        var base_url = $('#base_url').val();
        var user_id = $('#user_id').val();
        //Basic Details
        var full_name = $('#full_name1').val();
        var email_id = $('#u_email_id1').val();
        var mobile_no = $('#u_mobile_no1').val();
        var username = $('#u_username1').val();
        var password = $('#upassword1').val();
        var status = $('#ustatus').val();

        var col_data_array = [];
        col_data_array.push({
            'form':'update_user',
            'full_name':full_name,
            'email_id':email_id,
            'mobile_no':mobile_no,
            'username':username,
            'password':password,
            'status':status
        });
        $('.saveprofile').button('loading');
        $.ajax({
        type:'post',
        url: base_url+'controller/b2b_customer/profile_update.php',
        data:{ user_id:user_id,col_data_array:JSON.stringify(col_data_array)},
        success: function(message){
            $('#update_modal').modal('hide');
            success_msg_alert(message);
            setTimeout(() => {
                user_list_reflect();
                $('#full_name1').val('');
                $('#u_email_id1').val('');
                $('#u_username1').val('');
                $('#upassword1').val('');
                $('#urepassword').val('');
            }, 2000); 
        }  
    });

    }
});
</script>
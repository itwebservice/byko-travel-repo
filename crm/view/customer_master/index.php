<?php
include "../../model/model.php";
/*======******Header******=======*/
require_once('../layouts/admin_header.php');

$role = $_SESSION['role'];
$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$q = "select branch_status from branch_assign where link='customer_master/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<?= begin_panel('Customers Information',8) ?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>" >
<input type="hidden" id="branch_admin_id" name="branch_admin_id" value="<?= $branch_admin_id ?>" >

<div class="row">
    <div class="col-sm-6 text-left">            
        <button class="btn btn-info btn-sm ico_left pull-left mg_bt_20" data-toggle='tooltip' title="Download CSV Format" style="margin-right:10px" onclick="display_format_modal();"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;CSV Format</button>
        <div class="div-upload  mg_bt_20" id="div_upload_button" title="Upload CSV" data-toggle='tooltip'>
            <div id="cust_csv_upload" class="upload-button1"><span>CSV</span></div>
            <span id="cust_status" ></span>
            <ul id="files" ></ul>
            <input type="hidden" id="txt_cust_csv_upload_dir" name="txt_cust_csv_upload_dir">
        </div>
    </div>
    <div class="col-sm-6 text-right text_left_sm_xs">
        <?php if($role == 'Admin' || $role == 'Branch Admin'){?>
        <button class="btn btn-excel btn-sm " onclick="excel_report()" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>&nbsp;&nbsp;
        <?php } ?>
        <span style="display: inline-block;">
            <button class="btn btn-info btn-sm ico_left " data-toggle="tooltip" title="Add Customer" id="customer_save_modal_add_btn" onclick="customer_save_modal('master');btnDisableEnable(this.id)"><i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;Customer</button>
        </span>
    </div>
</div>

<div class="app_panel_content Filter-panel">
    <div class="row">
        <div class="col-md-3 col-sm-6 mg_bt_10_xs">
            <select name="cust_type_filter" id="cust_type_filter" onchange="customer_list_reflect();company_name_reflect();" title="Select Customer Type">
                <?php get_customer_type_dropdown(); ?>                
            </select>
        </div>
        <?php if($role=='Admin'){ ?>
        <div class="col-md-3 col-sm-6 col-xs-12 mg_bt_10_xs">
            <select name="branch_id_filter" id="branch_id_filter1" title="Branch Name" style="width: 100%" onchange="customer_list_reflect()">
            <?php get_branch_dropdown($role, $branch_admin_id, $branch_status) ?>
            </select>
        </div>
        <?php } ?>
        <div class="col-md-3 col-sm-6 mg_bt_10_xs">
            <select name="active_flag_filter" id="active_flag_filter" title="Status" onchange="customer_list_reflect()">
                <option value="">Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3 mg_bt_10_xs" id="company_div">
        </div>
    </div>
</div>

<div id="div_customer_list" class="main_block loader_parent mg_tp_20">
    <div class="table-responsive">
        <table id="tbl_list" class="table table-hover" style="margin: 20px 0 !important;width:100%">         
        </table>
    </div>
</div>

<div id="div_customer_update_modal"></div>
<div id="div_view_modal"></div>
<script src="<?= BASE_URL ?>js/app/field_validation.js"></script>

<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script>
var columns = [
	{ title : "S_No." },
	{ title : "Customer_Name" },
	{ title : "Birth_Date" },
	{ title : "mobile_NO" },
	{ title : "Email_ID" },
	{ title : "Actions", className:"text-center" }
]
function customer_list_reflect(){

  $('#div_customer_list').append('<div class="loader"></div>');
  var active_flag = $('#active_flag_filter').val();
  var cust_type = $('#cust_type_filter').val();
  var company_name = $('#company_filter').val();
  var branch_status = $('#branch_status').val();
  var branch_id = $('#branch_id_filter1').val();

    $.post('customer_list_reflect.php', {active_flag : active_flag, cust_type : cust_type, company_name : company_name, branch_status : branch_status,branch_id : branch_id}, function(data){
        pagination_load(data,columns, true, false, 20, 'tbl_list');
		$('.loader').remove();
	});
}
customer_list_reflect();

function excel_report(){
    var active_flag = $('#active_flag_filter').val();
    var branch_id = $('#branch_id_filter1').val();
    var cust_type = $('#cust_type_filter').val();
    var company_name = $('#company_filter').val();
    var branch_status = $('#branch_status').val();
    
    window.location = 'excel_report.php?active_flag='+active_flag+'&branch_id='+branch_id+'&cust_type='+cust_type+'&company_name='+company_name+'&branch_status='+branch_status;
}
function customer_users_reflect(type) {

    if(type=='save') {
        var cust_type_id = 'cust_type';
        var cust_type_div = 'users_div';
    }else{
        var cust_type_id = 'cust_type1';
        var cust_type_div = 'users_div_update';
    }
	var base_url = $('#base_url').val();
	var cust_type = $('#'+cust_type_id).val();
	var customer_id = $('#customer_id_u').val();
	$.post(
		base_url + 'view/customer_master/customer_users_reflect.php',
		{ cust_type: cust_type, customer_id: customer_id,type:type },
		function (data) {
			$('#'+cust_type_div).html(data);
		}
	);
}

cust_csv_upload();
function cust_csv_upload()
{   
    var type="id_proof";
    var btnUpload=$('#cust_csv_upload');
    var status=$('#cust_status');
    new AjaxUpload(btnUpload, {
        action: 'upload_cust_csv_file.php',
        name: 'uploadfile',
        onSubmit: function(file, ext){

            if(!confirm('Do you want to import this file?')){
                return false;
            }

            if (! (ext && /^(csv)$/.test(ext))){ 
            error_msg_alert('Only CSV files are allowed');
            return false;
            }
            status.text('Uploading...');
        },
        onComplete: function(file, response){
            status.text('');
            if(response==="error"){          
            alert("File is not uploaded.");
            } else{
            document.getElementById("txt_cust_csv_upload_dir").value = response;
            status.text('Uploading...');
            cust_csv_save();
            }
        }
    });
}

function cust_csv_save(){
    var cust_csv_dir = document.getElementById("txt_cust_csv_upload_dir").value;
    var branch_admin_id = $('#branch_admin_id').val();
    var status=$('#cust_status');
    var base_url = $('#base_url').val();

    $.ajax({
        type:'post',
        url: base_url+'controller/customer_master/cust_csv_save.php',
        data:{cust_csv_dir : cust_csv_dir, branch_admin_id : branch_admin_id , base_url : base_url },
        success:function(result){
            var error_arr = result.split('--');
            if(error_arr[0]=='error'){
                error_msg_alert(error_arr[1]);
                status.text('');
            }else{
                success_msg_alert(result);
                status.text('');
            }
            customer_list_reflect();
        }
    });
}

function company_name_reflect(){
	var cust_type = $('#cust_type_filter').val();
    var branch_status = $('#branch_status').val();
    $.post('company_name_load.php', { cust_type : cust_type, branch_status : branch_status }, function(data){
        $('#company_div').html(data);
    });
}
function display_format_modal(){
    var base_url = $('#base_url').val();
    window.location = base_url+"images/csv_format/customer.csv";
}

function showNum(count)
{
	$("#phone-x"+count).removeClass('hidden');
	$("#phone-y"+count).addClass('hidden');
	var emp_id=$("#emp_id").val();
		$.post('send_notification.php', { emp_id : emp_id }, function(data){
	})
}
function showEmail(count)
{
	$("#phone-xe"+count).removeClass('hidden');
	$("#phone-ye"+count).addClass('hidden');
	var emp_id=$("#emp_id").val();
		$.post('send_notification.php', { emp_id : emp_id }, function(data){
	})
}
function customer_update_modal(customer_id)
{
	$('#customer_display_modal_edit_btn-'+customer_id).button('loading');
	$('#customer_display_modal_edit_btn-'+customer_id).prop('disabled',true);
	$.post('customer_update_modal.php', { customer_id : customer_id }, function(data){
		$('#div_customer_update_modal').html(data);
        $('#customer_display_modal_edit_btn-'+customer_id).button('reset');
        $('#customer_display_modal_edit_btn-'+customer_id).prop('disabled',false);
	})
}
function customer_display_modal(customer_id)
{
	$('#customer_display_modal_view_btn-'+customer_id).button('loading');
	$('#customer_display_modal_view_btn-'+customer_id).prop('disabled',true);
	$.post('view/index.php', { customer_id : customer_id }, function(data){
		$('#div_customer_update_modal').html(data);
        $('#customer_display_modal_view_btn-'+customer_id).button('reset');
        $('#customer_display_modal_view_btn-'+customer_id).prop('disabled',false);
	})
}
function customer_history_modal(customer_id)
{
	$.post('customer_history_modal.php', { customer_id : customer_id }, function(data){
		$('#div_customer_update_modal').html(data);
	})
}
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<?= end_panel() ?>
<?php
/*======******Footer******=======*/
require_once('../layouts/admin_footer.php'); 
?>

<?php
include "../../model/model.php";
/*======******Header******=======*/
require_once('../layouts/admin_header.php');
require_once('../../classes/tour_booked_seats.php');

$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$login_id = $_SESSION['login_id'];
$reminder_status = (isset($_SESSION['reminder_status'])) ? "true" : "false";
$getClient =  mysqli_fetch_array(mysqlQuery("select client_id from app_settings"))['client_id'];
$modules = json_decode(file_get_contents("modules.json"),true);

if(empty($modules))
{
    $modules = [];
}
?>
<?= begin_panel('Tickets') ?>
<div class="header_bottom">
    <div class="row mg_bt_10">
        <div class="col-md-12 text-right">
            <button class="btn btn-info btn-sm ico_left" id="btn_far_save" data-toggle="modal" data-target="#addmodal" title="Add New"><i class="fa fa-plus"></i>&nbsp;&nbsp;Ticket</button>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade bd-example-modal-lg" data-backdrop="static" id="addmodal" role="dialog" aria-labelledby="addmodal" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 1200px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">New Ticket</h4>
            </div>
            <form id="addticketform">
                <div class="modal-body">
                    <!-- new -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <input type="hidden" name="client" id="client" value="<?php echo $getClient; ?>">
                                <div class="col-md-6 mg_tp_10">
                                    <label for="main_module" class="form-label">Main Module</label>

                                    <select name="main_module" id="main_module" data-toggle="tooltip" title="Select Type" class="form-control">
                                        <option value="CRM">CRM</option>
                                        <option value="B2B">B2B</option>
                                        <option value="Website">Website</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mg_tp_10">
                                    <label for="module" class="form-label">Module</label>
                                    <input type="text" name="module" id="module" title="Module" class=" form-control" list="module_list" placeholder="*Module" onchange="getSubmodules()"><datalist id="module_list">
                                            <?php
                                        foreach($modules as $module)
                                        {
                                            ?>
                                                <option value="<?= $module['name'] ?>" data-moduleid="<?= $module['id'] ?>">
                                        <?php }
                                    ?>  
                                    </datalist>
                                </div>
                                <div class="col-md-6 mg_tp_10">
                                    <label for="submodule" class="form-label">Sub Module</label>
                                    <input type="text" name="submodule" id="submodule" title="Sub Module" class=" form-control" placeholder="*Sub Module" list="sub_module_list">
                                    <datalist id="sub_module_list">
                                                <option value="Sub Module">
                                    </datalist>
                                </div>
                                <div class="col-md-6 mg_tp_10">
                                    <label for="type" class="form-label">Issue Type</label>

                                    <select name="type" id="type" data-toggle="tooltip" title="Select Issue Type" class="form-control">
                                        <option value="Issue">Issue</option>
                                        <option value="Suggestion">Suggestion</option>
                                        <option value="Customization">Customization</option>

                                    </select>
                                </div>
                                <div class="col-md-12 mg_tp_10">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" class="form-control" id="description" cols="30" rows="10" placeholder="*Description"></textarea>
                                </div>

                                <div class="col-md-6 mg_tp_10">
                                    <label for="sslink" class="form-label">Snapshot Link</label>
                                    <input type="text" name="sslink" id="sslink" title="Snapshot Link" class=" form-control" placeholder="Snapshot Link">
                                </div>
                                <div class="col-md-6 mg_tp_10">
                                    <label for="videolink" class="form-label">Video Link</label>
                                    <input type="text" name="videolink" id="videolink" title="Video Link" class=" form-control" placeholder="Video Link">
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <!-- <button name="add_ticket" type="button" onclick="addTicket()" id="ticketsubmit" class="btn btn-primary">Add Ticket</button> -->
                            <button type="button" name="add_ticket" id="ticketsubmit" onclick="addTicket()" class="btn btn-sm btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="get_tickets_report" id="get_tickets_report">

</div>

<?= end_panel() ?>
<!-- tickets end -->
<?php
/*======******Footer******=======*/
require_once('../layouts/admin_footer.php');
?>



<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script> -->
<script>
    function addTicket() {
        $("#ticketsubmit").html('Loading...');
        $("#ticketsubmit").prop('disabled', true);

        var client = $('#client').val();
        var main_module = $('#main_module').val();
        var module = $('#module').val();
        var submodule = $('#submodule').val();
        var description = $('#description').val();
        var type = $('#type').val();
        var sslink = $('#sslink').val();
        var videolink = $('#videolink').val();


        if (main_module == "") {
            error_msg_alert("Main Module Is Required");
            $("#ticketsubmit").html('Add Ticket');
            $("#ticketsubmit").prop('disabled', false);
            return false;
        }
        if (module == "") {
            error_msg_alert("Module Is Required");
            $("#ticketsubmit").html('Add Ticket');
            $("#ticketsubmit").prop('disabled', false);
            return false;

        }
        if (submodule == "") {
            error_msg_alert("Sub Module Is Required");
            $("#ticketsubmit").html('Add Ticket');
            $("#ticketsubmit").prop('disabled', false);
            return false;

        }
        if (description == "") {
            error_msg_alert("Description Is Required");
            $("#ticketsubmit").html('Add Ticket');
            $("#ticketsubmit").prop('disabled', false);
            return false;

        }
        if (type == "") {
            error_msg_alert("Type Is Required");
            $("#ticketsubmit").html('Add Ticket');
            $("#ticketsubmit").prop('disabled', false);
            return false;

        }

        $.ajax({
            type: 'POST',
            url: 'https://support.itourscloud.com/model/add-ticket-api.php',
            data: {
                client: client,
                main_module: main_module,
                module: module,
                submodule: submodule,
                description: description,
                type: type,
                sslink: sslink,
                videolink: videolink
            },
            success: function() {
                $('#addmodal').modal('hide');
                success_msg_alert('Ticket added successfully!');
                document.getElementById('addticketform').reset();
                $("#ticketsubmit").html('Ticket');
                $("#ticketsubmit").prop('disabled', false);
                clearForm();
                getData();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status == 500) {
                    $("#ticketsubmit").html('Save');
                    $("#ticketsubmit").prop('disabled', false);
                    error_msg_alert('Internal error: ' + jqXHR.responseText);
                } else {
                    $("#ticketsubmit").html('Save');
                    $("#ticketsubmit").prop('disabled', false);
                    error_msg_alert('Unexpected error.' + jqXHR.responseText);
                }
            }
        });

    }

    // $(document).ready(function() {
    getData();

    // });

    function clearForm() {
        $('#module').val('');
        $('#submodule').val('');
        $('#description').val('');
        $('#sslink').val('');
        $('#videolink').val('');
    }

    function getData() {
        $("#get_tickets_report").html(`<div class="center-body"><div class="loader-circle-2"></div></div>`);
        var clientId = $('#client').val();
        $.get('get_data.php', {
            clientId: clientId
        }, function(data) {
            $('#get_tickets_report').html(data);
        });
    }
    getSubmodules();
    function getSubmodules() {
        // var test = $('#module');
        var module_name = $('#module').val();
        $.post('get_submodules.php',{module_name:module_name},function(data)
        {   
            $('#sub_module_list').html(data);
        });
    }
</script>
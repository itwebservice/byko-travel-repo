<?php
include "../../../model/model.php";
$question_count = mysqli_num_rows(mysqlQuery("select * from generic_settings where status='1'"));
$sq_query = mysqlQuery("select * from generic_settings where status='1'");
?>

<form id="frm_settings">
    <input type="hidden" id="question_count" value="<?= $question_count ?>" />
    <div class="row mg_bt_10">
        <div class="col-md-11">
            <small class="note">Note : Save this settings using 'Save' button below!</small>
        </div>
    </div>
    <div class="panel panel-default panel-body mg_bt_10 pad_8">
        <?php
        $count = 0;
        while ($row_query = mysqli_fetch_assoc($sq_query)) {
            $count++;
            $answer1 = ($row_query['answer'] == 'Yes') ? 'checked' : '';
            $answer2 = ($row_query['answer'] == 'No') ? 'checked' : '';
        ?>
            <div class="row mg_bt_10">
                <div class="col-md-1 col-sm-1">
                    <input type="hidden" id="entry_id-<?= $row_query['entry_id'] ?>" value="<?= $row_query['entry_id'] ?>" />
                    <input class="form-control" type="number" value="<?= $count ?>" readonly />
                </div>
                <div class="col-md-9 col-sm-9">
                    <h5><?= $row_query['question'] ?></h5>
                </div>
                <div class="col-md-2 col-sm-2">
                    <label class="app_dual_button" for="<?php echo 'yes-' . $row_query['entry_id']; ?>"><input type="radio" value="Yes" id="<?php echo 'yes-' . $row_query['entry_id']; ?>" name="settings_answer<?= $row_query['entry_id'] ?>" <?= $answer1 ?> />&nbsp;Yes</label>
                    <label class="app_dual_button" for="<?php echo 'no-' . $row_query['entry_id']; ?>"><input type="radio" value="No" id="<?php echo 'no-' . $row_query['entry_id']; ?>" name="settings_answer<?= $row_query['entry_id'] ?>" <?= $answer2 ?> />&nbsp;No</label>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="panel panel-default panel-body mg_bt_10 text-center pad_8">
        <button class="btn btn-sm btn-success" id="btn_settings_save"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
    </div>
</form>
<script>
    $('#frm_settings').validate({

        rules: {

        },

        submitHandler: function(form, e) {
            e.preventDefault();

            $('#btn_settings_save').prop('disabled', true);
            var base_url = $("#base_url").val();
            var question_count = parseInt($("#question_count").val());

            var entry_id_arr = new Array();
            var answer_arr = new Array();

            for (var i = 1; i <= question_count; i++) {

                entry_id_arr.push($('#entry_id-' + i).val());
                var answer_id = 'settings_answer' + i;
                $("input[name=" + answer_id + ']:checked').each(function() {
                    answer_arr.push($(this).val());
                });
            }
            $('#btn_settings_save').button('loading');
            $('#vi_confirm_box').vi_confirm_box({
                message: "Are you sure ?",
                callback: function(answer) {
                    if (answer == "yes") {
                        $.post(
                            base_url + "controller/business_rules/save_settings.php", {
                                entry_id_arr: entry_id_arr,
                                answer_arr: answer_arr
                            },
                            function(data) {
                                success_msg_alert(data);
                                $('#btn_settings_save').button('reset');
                                $('#btn_settings_save').prop('disabled', false);
                            });
                    } else {
                        $('#btn_settings_save').button('reset');
                    }
                }
            });
        }
    });
    /////////////***********User roles save end*****************************************************
</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
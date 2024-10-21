<?php
include "../../model/model.php";

$country_name = $_POST['country_name'];
$visa_type = $_POST['visa_type'];
$status = $_POST['status'];
if ($status != '') {
    $query = "select * from visa_crm_master where status='$status'";
} else {
    $query = "select * from visa_crm_master where status='1'";
}

if ($country_name != "") {
    $query .= " and country_id='$country_name' ";
}
if ($visa_type != "") {
    $visa_type = addslashes($visa_type);
    $query .= " and visa_type='$visa_type' ";
}
?>
<div class="row mg_tp_20">
    <div class="col-md-12 no-pad">
        <div class="table-responsive">
            <table class="table" id="tbl_emp_list" style="margin: 20px 0 !important;">
                <thead>
                    <tr class="table-heading-row">
                        <th>S_No.</th>
                        <th>Country_Name</th>
                        <th>Visa_Type</th>
                        <th>Total_Amount</th>
                        <th>Time Taken</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 0;
                    $total_amt = 0;
                    $sq_emp = mysqlQuery($query);
                    while ($row_emp = mysqli_fetch_assoc($sq_emp)) {

                        $total_amt = $row_emp['fees'] + $row_emp['markup'];
                        $bg = ($row_emp['status'] == '0') ? 'danger' : '';
                    ?>
                    <tr class="<?= $bg ?>">
                        <td><?= ++$count ?></td>
                        <td><?= $row_emp['country_id'] ?></td>
                        <td><?= $row_emp['visa_type'] ?></td>
                        <td><?= number_format($total_amt, 2) ?></td>
                        <td><?= $row_emp['time_taken'] ?></td>
                        <td class="text-center" style="display:flex;">
                            <?php
                                if ($row_emp['status'] != '0') { ?><button class="btn btn-info btn-sm"
                                id="send-<?= $row_emp['entry_id'] ?>" data-toggle="tooltip"
                                onclick="send(<?= $row_emp['entry_id'] ?>)" title="Send via email and whatsapp"><i class="fa fa-paper-plane-o"></i></button>
                            <?php } ?>
                            <button class="btn btn-info btn-sm" onclick="update_modal(<?= $row_emp['entry_id'] ?>)"
                                data-toggle="tooltip" id="updatet_btn-<?= $row_emp['entry_id'] ?>" title="Update Details"><i class="fa fa-pencil-square-o"></i></button>
                            <button class="btn btn-info btn-sm" onclick="display_modal(<?= $row_emp['entry_id'] ?>)" data-toggle="tooltip" id="viewt_btn-<?= $row_emp['entry_id'] ?>" title="View Details"><i class="fa fa-eye"></i></button>
                                <?php
                                $download_url = preg_replace('/(\/+)/', '/', $row_emp['upload_url']);
                                $download_url2 = BASE_URL . str_replace('../', '', $download_url);
                                $download_url1 = preg_replace('/(\/+)/', '/', $row_emp['upload_url2']);
                                $download_url3 = BASE_URL . str_replace('../', '', $download_url1);

                                $download_url_demo3 = preg_replace('/(\/+)/', '/', $row_emp['upload_url3']);
                                $download_url_demo3 = BASE_URL . str_replace('../', '', $download_url_demo3);
                                $download_url_demo4 = preg_replace('/(\/+)/', '/', $row_emp['upload_url4']);
                                $download_url_demo4 = BASE_URL . str_replace('../', '', $download_url_demo4);
                                $download_url_demo5 = preg_replace('/(\/+)/', '/', $row_emp['upload_url5']);
                                $download_url_demo5 = BASE_URL . str_replace('../', '', $download_url_demo5);
                                ?>
                            <?php if ($row_emp['upload_url'] != "") : ?>
                            <a href="<?= $download_url2 ?>" class="btn btn-info btn-sm ico_left" data-toggle="tooltip"
                                style="padding: 15px 15px; border-radius: 4px;" title="Download Form1" download><i
                                    class="fa fa-download"></i></a>
                            <?php endif; ?>
                            <?php if ($row_emp['upload_url2'] != "") : ?>
                            <a href="<?= $download_url3 ?>" class="btn btn-info btn-sm ico_left" data-toggle="tooltip"
                                style="padding: 15px 15px; border-radius: 4px;" title="Download Form2" download><i
                                    class="fa fa-download"></i></a>
                            <?php endif; ?>
                            <?php if ($row_emp['upload_url3'] != "") : ?>
                            <a href="<?= $download_url_demo3 ?>" class="btn btn-info btn-sm ico_left" data-toggle="tooltip"
                                style="padding: 15px 15px; border-radius: 4px;" title="Download Form3" download><i
                                    class="fa fa-download"></i></a>
                            <?php endif; ?>
                            <?php if ($row_emp['upload_url4'] != "") : ?>
                            <a href="<?= $download_url_demo4 ?>" class="btn btn-info btn-sm ico_left" data-toggle="tooltip"
                                style="padding: 15px 15px; border-radius: 4px;" title="Download Form4" download><i
                                    class="fa fa-download"></i></a>
                            <?php endif; ?>
                            <?php if ($row_emp['upload_url5'] != "") : ?>
                            <a href="<?= $download_url_demo5 ?>" class="btn btn-info btn-sm ico_left" data-toggle="tooltip"
                                style="padding: 15px 15px; border-radius: 4px;" title="Download Form5" download><i
                                    class="fa fa-download"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$('#tbl_emp_list').dataTable({
    "pagingType": "full_numbers"
});
</script>
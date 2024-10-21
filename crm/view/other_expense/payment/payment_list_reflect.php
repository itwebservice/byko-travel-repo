<?php
include "../../../model/model.php";

$emp_id = $_SESSION['emp_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_POST['branch_status'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$financial_year_id = $_SESSION['financial_year_id'];

$supplier_type = $_POST['supplier_type'];
$expense_type = $_POST['expense_type'];

$query = "select * from other_expense_payment_master where 1 and delete_status='0'";
if ($financial_year_id != "") {
	$query .= " and financial_year_id='$financial_year_id'";
}
if ($supplier_type != "") {
	$query .= " and supplier_id='$supplier_type'";
}
if ($expense_type != "") {
	$query .= " and expense_type_id='$expense_type'";
}

include "../../../model/app_settings/branchwise_filteration.php";
$query .= " order by payment_id desc ";
$sq_pending_amount = 0;
$sq_cancel_amount = 0;
$total_paid_amt = 0;
?>
<div class="row mg_tp_20">
    <div class="col-md-12 no-pad">
        <div class="table-responsive">

            <table class="table table-hover" id="tbl_payment_expense1" style="margin: 20px 0 !important;">
                <thead>
                    <tr class="table-heading-row">
                        <th>S_No.</th>
                        <th>Payment_ID</th>
                        <th>Expense_ID</th>
                        <th>Payment_Date</th>
                        <th>Expense_Type</th>
                        <th>Supplier_Name</th>
                        <th>P_Evidence</th>
                        <th class="success">Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
					$count = 0;
                    $total_paid_amt = 0;
					$sq_expense = mysqlQuery($query);
					while ($row_expense = mysqli_fetch_assoc($sq_expense)) {
						$sq_supp = mysqli_fetch_assoc(mysqlQuery("select * from other_vendors where vendor_id='$row_expense[supplier_id]'"));
						$sq_ledger = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where ledger_id='$row_expense[expense_type_id]'"));
						$other_expense = mysqli_fetch_assoc(mysqlQuery("select * from other_expense_master where expense_type_id='$row_expense[expense_type_id]'"));
						$date = $other_expense['created_at'];
						$yr = explode("-", $date);
						$year = $yr[0];

						$date1 = $row_expense['payment_date'];
						$yr1 = explode("-", $date1);
						$year1 = $yr1[0];

                        if ($row_expense['clearance_status'] == 'Cancelled') {
                            $sq_cancel_amount = $sq_cancel_amount + $row_expense['payment_amount'];
                            $bg = 'danger';
                        } else if ($row_expense['clearance_status'] == 'Pending') {
                            $sq_pending_amount = $sq_pending_amount + $row_expense['payment_amount'];
                            $bg = 'warning';
                        } else {
                            $bg = '';
                        }

						$sq_paid = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum from other_expense_payment_master where clearance_status!='Cancelled' and delete_status='0' and payment_id='$row_expense[payment_id]'"));
						$sq_canc = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as cancel from other_expense_payment_master where clearance_status='Cancelled' and delete_status='0' and payment_id='$row_expense[payment_id]'"));
						$sq_pend = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as pending from other_expense_payment_master where clearance_status='Pending' and payment_id='$row_expense[payment_id]'"));

						$total_paid_amt = $total_paid_amt + $row_expense['payment_amount'];
						$newUrl = $row_expense['evidance_url'];

						if ($newUrl != "") {
							$newUrl = preg_replace('/(\/+)/', '/', $newUrl);
							$newUrl_arr = explode('uploads/', $newUrl);
							$newUrl = BASE_URL . 'uploads/' . $newUrl_arr[1];
						}
						if ($row_expense['payment_amount'] != '0') {
							$date = $row_expense['payment_date'];
							$yr = explode("-", $date);
							$year = $yr[0];
					?>
                    <tr class="<?= $bg ?>">
                        <td><?= ++$count ?></td>
                        <td><?= get_other_expense_payment_id($row_expense['payment_id'], $year1) ?></td>
                        <td><?= get_other_expense_booking_id($row_expense['expense_id'], $year) ?></td>
                        <td><?= get_date_user($row_expense['payment_date']) ?></td>
                        <td><?= $sq_ledger['ledger_name'] ?></td>
                        <td><?= ($sq_supp['vendor_name'] == '') ? 'NA' : $sq_supp['vendor_name'] ?></td>
                        <?php if ($newUrl != '') { ?>
                        <td>
                            <a class="btn btn-info btn-sm" href="<?php echo $newUrl; ?>" download
                                title="Download Payment Evidence"><i class="fa fa-download"></i></a>
                        </td> <?php } else { ?>
                        <td></td>
                        <?php } ?>
                        <td class="success text-right"><?= $row_expense['payment_amount'] ?></td>
                        <td style="display:flex;">
                            <button class="btn btn-info btn-sm"
                                onclick="payment_update_modal(<?= $row_expense['payment_id'] ?>)"
                                title="Update Details" id="updater_btn-<?= $row_expense['payment_id'] ?>"><i class="fa fa-pencil-square-o"></i></button>
                            <button class="<?= $delete_flag ?> btn btn-danger btn-sm"
                                onclick="p_delete_entry(<?= $row_expense['payment_id'] ?>)" title="Delete Entry"><i
                                    class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php
						}
					} ?>
                </tbody>
                <tfoot>
                    <tr class="active">
                        <th colspan="1" class="text-right"></th>
                        <th colspan="2" class="text-right">Paid Amount : <?= number_format(floatval($total_paid_amt), 2); ?></th>
                        <th colspan="2" class="text-right warning">Pending Clearance :
                            <?= number_format(floatval($sq_pending_amount), 2); ?></th>
                        <th colspan="2" class="text-right danger">Cancelled :
                            <?= number_format(floatval($sq_cancel_amount), 2); ?></th>
                        <th colspan="2" class="text-right success">Total Paid :
                            <?= number_format($total_paid_amt - $sq_pending_amount - $sq_cancel_amount, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div id="div_payment_update_content"></div>
<script>
$('#tbl_payment_expense1').dataTable({
    "pagingType": "full_numbers"
});

function payment_update_modal(payment_id) {
    $('#updater_btn-'+payment_id).prop('disabled',true);
    $('#updater_btn-'+payment_id).button('loading');
    $.post('payment/payment_update_modal.php', {
        payment_id: payment_id
    }, function(data) {
        $('#div_payment_update_content').html(data);
        $('#updater_btn-'+payment_id).prop('disabled',false);
        $('#updater_btn-'+payment_id).button('reset');
    });
}
</script>
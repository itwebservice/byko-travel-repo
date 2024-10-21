<?php
$getClient = $_GET['clientId'];
$dataAll = file_get_contents('https://support.itourscloud.com/model/get-ticket-api.php?cid=' . $getClient);
echo $dataAll;
?>
<script>
$('#ticketTable').DataTable();
</script>
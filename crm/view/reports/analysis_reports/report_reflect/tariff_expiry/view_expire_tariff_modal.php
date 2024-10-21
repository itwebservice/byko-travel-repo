<?php
include "../../../../../model/model.php";
$tariff_data = $_POST['tariff_data'];
$btn_id = $_POST['btn_id'];
?>
<div class="modal fade" id="tariff_wise_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-left" id="myModalLabel">Tariff Expiry Details for <?= $btn_id ?></h4>
            </div>
            <div class="modal-body profile_box_padding">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button class="btn btn-excel btn-sm" onclick="exportToExcel('<?= $btn_id ?>_TariffExpiry')" data-toggle="tooltip" title="Generate Excel"><i class="fa fa-file-excel-o"></i></button>
                    </div>
                    <div class="col-md-12">
                        <table id="<?= $btn_id ?>_TariffExpiry" class="table table-hover">   
                            <thead>
                                <tr>
                                    <th scope="col">SR.No</th>
                                    <th scope="col">Details</th>
                                    <th scope="col">Exipry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $details = '';
                                for($i = 0; $i < sizeof($tariff_data); $i++){

                                    $table_name = $tariff_data[$i]['table'];
                                    $column = $tariff_data[$i]['column'];
                                    $entry_id = $tariff_data[$i]['entry_id'];
                                    $q = "select * from ".$table_name." where ".$column."='$entry_id'";
                                    $sq_query = mysqli_fetch_assoc(mysqlQuery($q));
                                    if($table_name == 'hotel_contracted_tarrif')
                                        $details = $sq_query['room_category'];
                                    if($table_name == 'hotel_blackdated_tarrif')
                                        $details = $sq_query['room_category'];
                                    if($table_name == 'b2b_transfer_tariff_entries'){
                                        // Pickup
                                        if($sq_query['pickup_type'] == 'city'){
                                            $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$sq_query[pickup_location]'"));
                                            $pickup = $row['city_name'];
                                        }
                                        else if($sq_query['pickup_type'] == 'hotel'){
                                            $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$sq_query[pickup_location]'"));
                                            $pickup = $row['hotel_name'];
                                        }
                                        else{
                                            $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$sq_query[pickup_location]'"));
                                            $airport_nam = clean($row['airport_name']);
                                            $airport_code = clean($row['airport_code']);
                                            $pickup = $airport_nam." (".$airport_code.")";
                                        }
                                        //Drop-off
                                        if($sq_query['drop_type'] == 'city'){
                                            $row = mysqli_fetch_assoc(mysqlQuery("select city_id,city_name from city_master where city_id='$sq_query[drop_location]'"));
                                            $drop = $row['city_name'];
                                        }
                                        else if($sq_query['drop_type'] == 'hotel'){
                                            $row = mysqli_fetch_assoc(mysqlQuery("select hotel_id,hotel_name from hotel_master where hotel_id='$sq_query[drop_location]'"));
                                            $drop = $row['hotel_name'];
                                        }
                                        else{
                                            $row = mysqli_fetch_assoc(mysqlQuery("select airport_name, airport_code, airport_id from airport_master where airport_id='$sq_query[drop_location]'"));
                                            $airport_nam = clean($row['airport_name']);
                                            $airport_code = clean($row['airport_code']);
                                            $drop = $airport_nam." (".$airport_code.")";
                                        }
                                        $details = $pickup.'-'.$drop;
                                    }
                                    if($table_name == 'excursion_master_tariff_basics')
                                        $details = $sq_query['transfer_option'];
                                    ?>
                                    <tr>
                                        <td><?php echo $i+1;  ?></td>
                                        <td><?php echo $details; ?> </td>       
                                        <td><?php echo get_date_user($sq_query['to_date']); ?></td>                             
                                    </tr>
                                <?php  } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#tariff_wise_modal').modal('show');
</script>
<?php
include '../../../model/model.php';
include '../../layouts/header2.php';

$agent_flag = $_SESSION['agent_flag'];
$user_id = $_SESSION['user_id'];
$visa_array = json_decode($_SESSION['visa_array']);
$country_id = ($visa_array[0]->country_id);
$visa_type = ($visa_array[0]->visa_type);
$pax = (isset($visa_array[0]->pax) && $visa_array[0]->pax != '') ? $visa_array[0]->pax : 1;

if ($country_id == '') {
    $query = "select * from country_list_master where 1 ";
} else {
    $sq_city = mysqli_fetch_assoc(mysqlQuery("select country_name from country_list_master where country_id='$country_id'"));
    $query = "select * from country_list_master where country_id='$country_id'";
}
$query .= " order by country_name asc";
//Page Title
if ($country_id != '') {
    $page_title = 'Results for ' . $sq_city['country_name'];
} else {
    $page_title = 'Visa';
}
?>
<!-- ********** Component :: Page Title ********** -->
<div class="c-pageTitleSect">
    <div class="container">
        <div class="row">
            <div class="col-md-7 col-12">

                <!-- *** Search Head **** -->
                <div class="searchHeading">
                    <span class="pageTitle"><?= $page_title ?></span>

                    <div class="clearfix">
                        <?php
                        if ($country_id != '') { ?>
                        <div class="sortSection">
                            <span class="sortTitle st-search">
                                <i class="icon it itours-pin-alt"></i>
                                Country Name: <strong><?= $sq_city['country_name'] ?></strong>
                            </span>
                        </div>
                        <?php } ?>
                        <?php
                        if ($visa_type != '') { ?>
                        <div class="sortSection">
                            <span class="sortTitle st-search">
                                <i class="icon it itours-timetable"></i>
                                Visa Type: <strong><?= $visa_type ?></strong>
                            </span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="clearfix">
                        <div class="sortSection">
                            <span class="sortTitle st-search">
                                <i class="icon it itours-search"></i>
                                <span>Showing <span class="results_count"></span></span>
                            </span>
                        </div>
                    </div>
                </div>
                <!-- *** Search Head End **** -->
            </div>

            <div class="col-md-5 col-12 c-breadcrumbs">
                <ul>
                    <li>
                        <a href="<?= $b2b_index_url ?>">Home</a>
                    </li>
                    <li class="st-active">
                        <a href="javascript:void(0)">Visa Search Result</a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</div>
<!-- ********** Component :: Page Title End ********** -->

<!-- ********** Component :: Visa Listing  ********** -->
<div class="c-containerDark">
    <div class="container">
        <!-- ********** Component :: Modify Filter  ********** -->
        <div class="row c-modifyFilter">
            <div class="col">
            <!-- Modified Search Filter -->
            <div class="accordion c-accordion" id="modifySearch_filter">
            <div class="card">

                <div class="card-header" id="headingThree">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#jsModifySearch_filter" aria-expanded="false" aria-controls="jsModifySearch_filter">
                    Modify Search >> <span class="results_count"></span>
                    </button>
                    <input type="hidden" value="<?= $pax ?>" id="total_pax"/>
                </div>
                <div id="jsModifySearch_filter" class="collapse" aria-labelledby="jsModifySearch_filter" data-parent="#modifySearch_filter">
                    <div class="card-body">
                    <form id="frm_visa_search">
                    <div class="row">
                        <input type='hidden' id='page_type' value='search_page' name='search_page' />
                        <!-- *** Country Name *** -->
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Select Country</label>
                                <div class="c-select2DD">
                                <select id='visa_country_filter' title="Select Country" class="full-width js-roomCount">
                                    <?php
                                    if ($country_id != '') { ?>
                                    <option value="<?= $country_id ?>"><?= $sq_city['country_name'] ?></option>
                                    <?php } ?>
                                    <option value="">Visa Country</option>
                                    <?php
                                    $sq_country = mysqlQuery("select * from country_list_master");
                                    while ($row_country = mysqli_fetch_assoc($sq_country)) {
                                    ?>
                                    <option value="<?= $row_country['country_id'] ?>">
                                        <?= $row_country['country_name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>
                        </div>
                        <!-- *** Country End *** -->
                        <!-- *** Visa Type *** -->
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Select Visa Type</label>
                                <div class="selector">
                                <select id='visa_type_filter' title="Visa Type" class="full-width js-roomCount">
                                    <?php
                                    if($visa_type != ''){ ?>
                                        <option value="<?= $visa_type ?>"><?= $visa_type ?></option>
                                    <?php } ?>
                                    <option value="">Visa Type</option>
                                    <?php 
                                    $sq_visa_type = mysqlQuery("select * from visa_type_master");
                                    while($row_visa_type = mysqli_fetch_assoc($sq_visa_type)){
                                        ?>
                                        <option value="<?= $row_visa_type['visa_type'] ?>"><?= $row_visa_type['visa_type'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>
                        </div>
                        <!-- *** Country End *** -->
                        <!-- *** Adult *** -->
                        <div class="col-md-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Total Passenger(s)</label>
                            <input type="number" name="passengers" title="Passengers" class="input-text full-width" id="passengers" placeholder="Passenger(s)" value="<?= $pax ?>"/>
                        </div>
                        </div>
                        <!-- *** Adult End *** -->
                        <!-- *** Search Rooms *** -->
                        <div class="col-md-4 col-sm-6 col-12">
                            <button class="c-button lg colGrn m26-top">
                                <i class="icon itours-search"></i> SEARCH NOW
                            </button>
                        </div>
                        <!-- *** Search Rooms End *** -->
                    </div>
                    </form>
                    <!-- Modified Search Filter End -->
                </div>
            </div>
            </div>
            </div>
        </div>
        </div>
        <hr />
        <div class="row">
            <!-- ***** Visa Listing ***** -->
            <div class="col-md-12 col-sm-12">
                <?php
                $visa_results_array = array();
                $array = array();
                $sq_query = mysqlQuery($query);
                while (($row_query  = mysqli_fetch_assoc($sq_query))) {
                    $visa_info_arr = array();
                    $country_name = addslashes($row_query['country_name']);
                    $country_code = addslashes($row_query['country_code']);
                    $q1 = "SELECT * FROM `visa_crm_master` WHERE `country_id`='$country_name'";
                    if($visa_type != ''){
                        $q1 .= " and visa_type='$visa_type'";
                    }
                    $sq_visa = mysqlQuery($q1);
                    while ($row_visa = mysqli_fetch_assoc($sq_visa)) {

                        array_push($visa_info_arr, array(
                            "visa_type" => $row_visa['visa_type'],
                            "time_taken" => $row_visa['time_taken'],
                            "documents" => $row_visa['list_of_documents'],
                            "upload_url1" => $row_visa['upload_url'],
                            "upload_url2" => $row_visa['upload_url2'],
                            "upload_url3" => $row_visa['upload_url3'],
                            "upload_url4" => $row_visa['upload_url4'],
                            "upload_url5" => $row_visa['upload_url5'],
                        ));
                    }

                    array_push($visa_results_array, array(
                        "country_id" => $row_query['country_id'],
                        "country_name" => $country_name,
                        "country_code" => $country_code,
                        'visa_info' => $visa_info_arr,
                        'pax'=>$pax,
                        'agent_flag'=>$agent_flag,
                        'user_id'=>$user_id
                    ));
                }
                ?>
                <input type='hidden'
                    value='<?= json_encode($visa_results_array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>'
                    id='visa_results_array' name='visa_results_array' />
                <div id='visa_result_block'></div>
            </div>
        </div>
    </div>
</div>
<!-- ********** Component :: Visa Listing End ********** -->
<?php include '../../layouts/footer.php'; ?>
<script type="text/javascript" src="../../js/jquery.range.min.js"></script>
<script type="text/javascript" src="../../js/pagination.min.js"></script>
<script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/view/visa/js/index.js"></script>
<script>
$('#visa_country_filter').select2();

// Get Visa results data
function get_price_filter_data(visa_results_array, type, fromRange_cost, toRange_cost) {
    var base_url = $('#base_url').val();
    var selected_value = document.getElementById(visa_results_array).value;
    var JSONItems = JSON.parse(selected_value);
    get_price_filter_data_result(JSONItems);
}
//Display Visa results data 
function get_price_filter_data_result(final_arr) {
    var base_url = $('#base_url').val();
    $.post(base_url + 'Tours_B2B/view/visa/visa_results.php', {
        final_arr: final_arr
    }, function(data) {
        $('#visa_result_block').html(data);
    });
}
get_price_filter_data('visa_results_array', '3', '0', '0');
</script>
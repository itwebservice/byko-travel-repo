<?php
include "../../../../../model/model.php";
$emp_id = $_SESSION['emp_id'];
$quot_id = $_POST['quot_id'];

$sq_quotation = mysqli_fetch_assoc(mysqlQuery("select quotation_date,customer_name from package_tour_quotation_master where quotation_id='$quot_id'"));
$quotation_date = $sq_quotation['quotation_date'];
$yr = explode("-", $quotation_date);
$year = $yr[0];
$quotation_id = $sq_quotation['customer_name'].': '.get_quotation_id($quot_id,$year);

$sq_hotel_count = mysqli_num_rows(mysqlQuery("select id from package_tour_quotation_hotel_entries where quotation_id='$quot_id'"));
?>
<form id="frm_response_save">
  <div class="modal fade profile_box_modal c-bookingInfo" id="request_details" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel">Hotel Availability Request Status (<?= $quotation_id ?>)</h4>
          </div>
          <div class="modal-body profile_box_padding">
            <input type="hidden" value='<?= $emp_id ?>' id="emp_id"/>
            <input type="hidden" value='<?= $quot_id ?>' id="quot_id"/>
            <input type="hidden" value='<?= $sq_hotel_count ?>' id="hotel_count"/>
            <?php
            $count = 1;
            $query = mysqlQuery("select id,availability,request_sent,hotel_name,city_name,room_category,total_rooms,package_type,check_in,check_out,extra_bed from package_tour_quotation_hotel_entries where quotation_id='$quot_id'");
            while($row_query = mysqli_fetch_assoc($query)){
                
                $hotel_entry_id = $row_query['id'];
                $h_availability = '';

                $availability_details1 = isset($row_query['availability']) ? $row_query['availability'] : '';

                $availability_details = (json_decode(json_encode($availability_details1)));
                $availability_details = isset($availability_details) ? json_decode($availability_details) : [];

                if(is_object($availability_details)){
                  $h_availability = $availability_details->availability;
                }
                if($h_availability == 'Available'){
                  $bg_clr = '#02fb4869';
                }
                else if($h_availability == 'Not Available'){
                  $bg_clr = '#e3a2a3';
                }else{
                  $bg_clr = 'none';
                }
                $optional_hotels = !empty($availability_details->option_hotel_arr) && $availability_details->option_hotel_arr != "null" ? $availability_details->option_hotel_arr : [];
                $bg = ($row_query['request_sent'] == 1) ? 'success' : '';

                $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select hotel_name,city_id,mobile_no,email_id from hotel_master where hotel_id='$row_query[hotel_name]'"));
                $sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$row_query[city_name]'"));
                $mobile_no = isset($sq_hotel['mobile_no']) ? $encrypt_decrypt->fnDecrypt($sq_hotel['mobile_no'], $secret_key) : '';
                $email_id = $encrypt_decrypt->fnDecrypt($sq_hotel['email_id'], $secret_key);
                $hotel_desc = 'Check-In Date: '.get_date_user($row_query['check_in'])."\r\n";
                $hotel_desc .= ', Check-Out Date: '.get_date_user($row_query['check_out'])."\r\n";
                $hotel_desc .= ', Package Type: '.$row_query['package_type']."\r\n";
                $hotel_desc .= ($row_query['room_category'] != '') ? ', Room Category: '.$row_query['room_category']."\r\n":'NA';
                $hotel_desc .= ($row_query['total_rooms'] != 0) ? ', Total Rooms(Extra Bed): '.$row_query['total_rooms'].'('.$row_query['extra_bed'].')' : 'NA';
                $hotel_desc = strip_tags($hotel_desc);
              ?>
              <div style="border: #e9e2e2 1px solid !important;padding: 15px 15px 0px 15px;margin-top:10px!important;">
              <div class="row">
              <div class="col-xs-12">
              <div class="table-responsive">
                <table id="hotel_req_table<?= $count ?>" class="table mg_bt_0 table-bordered" style="margin-top:0px !important;">
                  <thead>
                      <th></th>
                      <th>Availability</th>
                      <th>Hotel_Name(City_Name)</th>
                      <th>Mobile_NO</th>
                      <th>Email_Id</th>
                      <th>More_Details...</th>
                  </thead>
                  <tbody>
                    <tr class="<?= $bg ?>">
                      <td><input class="css-checkbox" id="chk_plan<?= $count ?>" type="checkbox"><label class="css-label" for="chk_plan<?= $count ?>"></label></td>
                      <td><select class="form-control" id="avail<?= $count ?>" style="width:150px !important;background-color:<?=$bg_clr?>" onchange="reflect_options('<?= $row_query['id'] ?>','<?= $count ?>')" title="Status" data-toggle="tooltip">
                        <?php
                        if($h_availability != ''){ ?>
                          <option value="<?= $h_availability ?>"><?= $h_availability ?></option>
                        <?php } ?>
                        <option value="">Status</option>
                        <option value="Available">Available</option>
                        <option value="Not Available">Not Available</option>
                        <option value="NA">NA</option>
                      </select></td>
                      <td id="hotel<?= $count ?>"><?= $sq_hotel['hotel_name'].'('.$sq_city['city_name'].')' ?></td>
                      <td><?= $mobile_no ?></td>
                      <td><input class="form-control" type='text' id="email_id<?= $count ?>" value="<?= $email_id ?>" style="width:200px !important;" placeholder="*Email ID" onchange="validate_email(this.id)" title="Email ID"/></td>
                      <td class="text-center"><a title="<?= $hotel_desc ?>" class="btn btn-info btn-sm" style="cursor: none !important;"><i class="fa fa-eye"></i></a> </td>
                      <td><input type="hidden" value="<?= $row_query['id'] ?>"/></td>
                    </tr>
                  </tbody>
                </table>
              </div></div></div>
              <div class="row mg_tp_10"><div class='col-md-12'>
                Replied By: <span id="reply_by<?= $count ?>"><?php echo is_object($availability_details) ? $availability_details->reply_by : ''; ?></span>  , Specification: <span id="spec<?= $count ?>"><?php echo is_object($availability_details) ? $availability_details->spec : 'NA'; ?></span>
              </div></div>
              <div id="optional_hotel_section<?= $count ?>" class="mg_tp_10">
                <?php
                if(is_object($availability_details) && $availability_details->availability == 'Not Available'){ ?>
                  <div class="row">
                    <div class="col-md-12 app_accordion">
                      <div class="panel-group main_block" id="accordion<?= $hotel_entry_id ?>" role="tablist" aria-multiselectable="true">

                        <div class="accordion_content main_block mg_bt_10">
                          <div class="panel panel-default main_block">
                            <div class="panel-heading main_block" role="tab" id="heading_<?=$hotel_entry_id?>">
                                <div class="Normal main_block" role="button" data-toggle="collapse"
                                    data-parent="#accordion<?= $hotel_entry_id ?>" href="#collapse<?=$hotel_entry_id?>" aria-expanded="true"
                                    aria-controls="collapse<?=$hotel_entry_id?>" id="collapsed<?=$hotel_entry_id?>">
                                    <div class="col-md-12"><span>Select Similar Hotel Options for <?= $sq_hotel['hotel_name'].'('.$sq_city['city_name'].')' ?></span></div>
                                </div>
                            </div>
                            <div id="collapse<?=$hotel_entry_id?>" class="panel-collapse collapse main_block" role="tabpanel"
                                aria-labelledby="heading_<?= $hotel_entry_id ?>">
                              <div class="panel-body">
                                <div class="row"><div class="col-xs-12 text-right mg_bt_20_sm_xs">
                                    <button type="button" class="btn btn-excel btn-sm" onclick="addRow('tbl_similar_package_hotels<?=$hotel_entry_id?>',<?= $hotel_entry_id ?>,'1');city_lzloading('select[name=city_id1]')" title="Add row"><i class="fa fa-plus"></i></button>
                                    <button type="button" class="btn btn-pdf btn-sm" onclick="deleteRow('tbl_similar_package_hotels<?=$hotel_entry_id?>',<?= $hotel_entry_id ?>)" title="Delete row"><i class="fa fa-trash"></i></button>    
                                </div></div>
                                <div class="row">
                                  <div class="table-responsive">
                                    <table id="tbl_similar_package_hotels<?=$hotel_entry_id?>" class="table table-bordered pd_bt_51">
                                          <?php
                                          if(sizeof($optional_hotels) == 0){
                                            $i=0;
                                            ?>
                                            <tr>
                                                <td style="width:5%"><input class="css-checkbox" id="ochk_plan<?= $i.$hotel_entry_id ?>_u" type="checkbox"><label class="css-label" for="ochk_plan<?= $i.$hotel_entry_id ?>"> <label></td>
                                                <td style="width:10%"><input maxlength="15" class="form-control text-center" type="text" name="username"  value="1" placeholder="Sr. No." disabled/></td>
                                                <td><select class="form-control" id="oavail1<?= $i.$hotel_entry_id ?>_u" style="width:200px !important;" title="Status" data-toggle="tooltip">
                                                    <option value="">Status</option>
                                                    <option value="Available">Available</option>
                                                    <option value="Not Available">Not Available</option>
                                                    <option value="NA">NA</option>
                                                </select></td>
                                                <td><select id="city_id1<?= $i.$hotel_entry_id ?>_u" name="city_id1" title="Select City" onchange="hotel_name_list_load(this.id)" class="form-control app_minselect2" style="width:200px!important;">
                                                    </select>
                                                </td>    
                                                <td><select id="hotel_id1<?= $i.$hotel_entry_id ?>_u" name="hotel_id1" title="Select Hotel" class="form-control app_select2" style="width:150px!important;" onchange="hotel_data_load(this.id)">
                                                    <option value="">*Select Hotel</option>
                                                    </select>
                                                </td>
                                                <td><input class="form-control" type='text' id="omobile_no1<?= $i.$hotel_entry_id ?>_u" style="width:140px !important;" placeholder="Mobile No" title="Mobile No" readonly/></td>
                                                <td><input class="form-control" type='text' id="oemail_id1<?= $i.$hotel_entry_id ?>_u" style="width:210px !important;" placeholder="*Email ID" title="Email ID" onchange="validate_email(this.id)"  required/></td>
                                                <td><input class="form-control" type='text' id="oreply_by1<?= $i.$hotel_entry_id ?>_u" style="width:140px !important;" placeholder="Replied By" title="Replied By" readonly/></td>
                                                <td><textarea rows="1" class="form-control" type='text' id="ospec1<?= $i.$hotel_entry_id ?>_u" style="width:200px !important;" placeholder="Specification" title="Specification" readonly ></textarea></td>
                                            </tr>
                                            <script>
                                            city_lzloading('select[name="city_id1"]');
                                            $('#hotel_id1<?= $i.$hotel_entry_id ?>_u').select2();
                                            </script>

                                          <?php }
                                          else{
                                            for($i = 0; $i < sizeof($optional_hotels); $i++){
                                              $availability = $optional_hotels[$i]->availability;
                                              $hotel_id = $optional_hotels[$i]->hotel_id;
                                              $city_id = $optional_hotels[$i]->city_id;
                                              $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select hotel_name,city_id,mobile_no,email_id from hotel_master where hotel_id='$hotel_id'"));
                                              $sq_city = mysqli_fetch_assoc(mysqlQuery("select city_name from city_master where city_id='$city_id'"));
                                              if($availability == 'Available'){
                                                $bg_clr1 = '#02fb4869';
                                              }
                                              else if($availability == 'Not Available'){
                                                $bg_clr1 = '#e3a2a3';
                                              }else{
                                                $bg_clr1 = 'none';
                                              }
                                              ?>
                                              <tr>
                                                  <td style="width:5%"><input class="css-checkbox" id="ochk_plan<?= $i.$hotel_entry_id ?>_u" type="checkbox"><label class="css-label" for="ochk_plan<?= $i.$hotel_entry_id ?>"> <label></td>
                                                  <td style="width:10%"><input maxlength="15" class="form-control text-center" type="text" name="username"  value="1" placeholder="Sr. No." disabled/></td>
                                                  <td><select class="form-control" id="oavail1<?= $i.$hotel_entry_id ?>_u" style="width:200px !important;background-color:<?=$bg_clr1?>" title="Status" data-toggle="tooltip">
                                                      <?php
                                                      if($availability != ''){ ?>
                                                      <option value="<?= $availability ?>"><?= $availability ?></option>
                                                      <?php } ?>
                                                      <option value="">Status</option>
                                                      <option value="Available">Available</option>
                                                      <option value="Not Available">Not Available</option>
                                                      <option value="NA">NA</option>
                                                  </select></td>
                                                  <td><select id="city_id1<?= $i.$hotel_entry_id ?>_u" name="city_id1" title="Select City" onchange="hotel_name_list_load(this.id)" class="form-control app_minselect2" style="width:200px!important;">
                                                      <option value="<?= $city_id ?>"><?= $sq_city['city_name'] ?></option>
                                                      </select>
                                                  </td>    
                                                  <td><select id="hotel_id1<?= $i.$hotel_entry_id ?>_u" name="hotel_id1" title="Select Hotel" class="form-control app_select2" style="width:150px!important;" onchange="hotel_data_load(this.id)">
                                                      <?php
                                                      if($sq_hotel['hotel_name'] != ''){ ?>
                                                      <option value="<?= $hotel_id ?>"><?= $sq_hotel['hotel_name'] ?></option>
                                                      <?php } ?>
                                                      <option value="">*Select Hotel</option>
                                                      </select>
                                                  </td>
                                                  <td><input class="form-control" type='text' id="omobile_no1<?= $i.$hotel_entry_id ?>_u" style="width:140px !important;" placeholder="Mobile No" title="Mobile No" value="<?= $optional_hotels[$i]->mobile_no ?>" readonly/></td>
                                                  <td><input class="form-control" type='text' id="oemail_id1<?= $i.$hotel_entry_id ?>_u" style="width:210px !important;" placeholder="*Email ID" title="Email ID" onchange="validate_email(this.id)" value="<?= $optional_hotels[$i]->email_id ?>" required/></td>
                                                  <td><input class="form-control" type='text' id="oreply_by1<?= $i.$hotel_entry_id ?>_u" style="width:140px !important;" placeholder="Replied By" title="Replied By" value="<?= $optional_hotels[$i]->reply_by ?>" readonly/></td>
                                                  <td><textarea rows="1" class="form-control" type='text' id="ospec1<?= $i.$hotel_entry_id ?>_u" style="width:200px !important;" placeholder="Specification" title="Specification" readonly ><?= $optional_hotels[$i]->spec ?></textarea></td>
                                              </tr>
                                              <script>
                                              city_lzloading('select[name="city_id1"]');
                                              $('#hotel_id1<?= $i.$hotel_entry_id ?>_u').select2();
                                              </script>
                                          <?php }
                                          } ?>
                                        </table>
                                    </div>
                                </div>
                                <div class="row text-center mg_tp_10">
                                  <div class="col-md-12">
                                    <button type="button" class="btn btn-sm btn-success mg_bt_10" id="obtn_send<?=$hotel_entry_id?>" onclick="submit_fun(this.id)"><i class="fa fa-paper-plane-o"></i>&nbsp;&nbsp;Send To Optional Hotels</button><br/><span style="color: red;" class="note" data-original-title="" title="">Use this button to send request only for optional hotels.</span>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
              <?php
              $count++;
            } ?>
          <div class="row text-center mg_tp_20">
            <div class="col-md-12">
              <button type="button" class="btn btn-sm btn-success" id="btn_send" onclick="submit_fun(this.id)"><i class="fa fa-paper-plane-o"></i>&nbsp;&nbsp;Send and Save Request</button>
            </div>
          </div>
          </div>
      </div>
    </div>
  </div>
</form>
<script>
$('#request_details').modal('show');
function reflect_options(hotel_entry_id1,hotel_entry_id){
  
  var status = $('#avail'+hotel_entry_id).val();
  var hotel_name = $('#hotel'+hotel_entry_id).html();
  if(status === 'Not Available')
    $.post('hotel_availability/generate_hotels.php', { hotel_entry_id:hotel_entry_id1,hotel_name:hotel_name}, function(data){
      $('#optional_hotel_section'+hotel_entry_id).html(data);
    })
  else
    $('#optional_hotel_section'+hotel_entry_id).html('');
}
function hotel_name_list_load(id)
{
  var count = id.substring(7);
  var city_id = $("#"+id).val();
  console.log(count);
  console.log(city_id);
  $.get( "hotel_availability/hotel_name_load.php" , { city_id : city_id } , function ( data ) {
        $("#hotel_id"+count).html( data ) ;                            
  } ) ;   
}
function hotel_data_load(id)
{
  var count = id.substring(8);
  var hotel_id = $("#"+id).val();
  $.get( "hotel_availability/hotel_data_load.php" , { hotel_id : hotel_id } , function ( data ) {
    data = data.split('//');
        $("#omobile_no"+count).val( data[0] );
        $("#oemail_id"+count).val( data[1] );
  } ) ;   
}

function submit_fun(btn_id){

  $('#'+btn_id).prop('disabled',true);
  var base_url = $('#base_url').val();
  var emp_id = $('#emp_id').val();
  var quot_id = $('#quot_id').val();
  var hotel_count = $('#hotel_count').val();

  var valid_count = 0;
  var hotel_entry_arr = [];
  var hotel_status_arr = [];
  for (var i = 1; i <= hotel_count; i++){
    var table = document.getElementById("hotel_req_table"+i);
    var rowCount = table.rows.length;
    var row = table.rows[1];
    
    var checkbox = row.cells[0].childNodes[0].checked;
    var status = row.cells[1].childNodes[0].value;
    var email_id = row.cells[4].childNodes[0].value;
    var hotel_entry_id = row.cells[6].childNodes[0].value;
    
    if(btn_id == 'btn_send' && checkbox){
      if(email_id == ''){
        error_msg_alert('Please enter email id at row'+(i));
        $('#'+btn_id).prop('disabled',false);
        return false;
      }
      valid_count++;
    }
    
    var reply_by = $("#reply_by"+i).html();
    var spec = $("#spec"+i).html();

    //For optional hotels
    var option_hotel_count = 0;
    var option_hotel_arr = [];
    if(status == 'Not Available'){
      var table1 = document.getElementById("tbl_similar_package_hotels"+hotel_entry_id);
      var rowCount1 = table1.rows.length;
      for (var j = 0; j < rowCount1; j++){
        var row1 = table1.rows[j];
        var checkbox1 = row1.cells[0].childNodes[0].checked;
        var status1 = row1.cells[2].childNodes[0].value;
        var city_id1 = row1.cells[3].childNodes[0].value;
        var hotel_id1 = row1.cells[4].childNodes[0].value;
        var mobile_no1 = row1.cells[5].childNodes[0].value;
        var email_id1 = row1.cells[6].childNodes[0].value;
        var reply_by1 = row1.cells[7].childNodes[0].value;
        var spec1 = row1.cells[8].childNodes[0].value;

        if((btn_id === ('obtn_send'+hotel_entry_id)) && checkbox1){
          if(city_id1 == ''){
            error_msg_alert('Please select city at row'+(j+1));
            $('#'+btn_id).prop('disabled',false);
            return false;
          }
          if(hotel_id1 == ''){
            error_msg_alert('Please select hotel at row'+(j+1));
            $('#'+btn_id).prop('disabled',false);
            return false;
          }
          if(email_id1 == ''){
            error_msg_alert('Please enter email id at row'+(j+1));
            $('#'+btn_id).prop('disabled',false);
            return false;
          }
          option_hotel_count++;
        }
        option_hotel_arr.push({
          'id' : (j+1),
          'availability' : status1,
          'city_id' : city_id1,
          'hotel_id' : hotel_id1,
          'mobile_no' : mobile_no1,
          'email_id' : email_id1,
          'reply_by' : reply_by1,
          'spec' : spec1,
          'mail_sent' : checkbox1
        });
      }
      if((btn_id == 'obtn_send'+hotel_entry_id) && option_hotel_count === 0){
        error_msg_alert('Please select atleast one hotel option !');
        $('#'+btn_id).prop('disabled',false);
        return false;
      }
    }

    hotel_entry_arr.push(hotel_entry_id);
    hotel_status_arr.push({
      'emp_id' : emp_id,
      'mail_sent' : checkbox,
      'availability' : status,
      'email_id' : email_id,
      'reply_by' : reply_by,
      'spec' : spec,
      'option_hotel_arr' : option_hotel_arr
    });
  }
  if(btn_id == 'btn_send' && valid_count === 0){
    error_msg_alert('Please select atleast one hotel to send request mail!');
    $('#'+btn_id).prop('disabled',false);
    return false;
  }
  $('#'+btn_id).button('loading');
  $.ajax({
      type: 'post',
      url: base_url+'controller/package_tour/quotation/hotel_request.php',
      data:{hotel_status_arr: hotel_status_arr,hotel_entry_arr:hotel_entry_arr},
      success: function(result){
        var msg = result.split('--');
        $('#'+btn_id).button('reset');
        if(msg[0]=='error'){
          error_msg_alert(msg[1]);
          $('#'+btn_id).prop('disabled',false);
          return false;
        }else{
          success_msg_alert(msg[0]);
          $('#'+btn_id).prop('disabled',false);
          $('#request_details').modal('hide');
          quotation_list_reflect();
        }
      }
  });
}

</script>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<?php
include "../../../model/model.php";
?>
<div class="row mg_tp_20 mg_bt_10">
<div class="col-md-12 text-right">
  <button class="btn btn-info btn-sm ico_left" onclick="generic_city_save_modal('master')" id="btn_city_save_modal"><i class="fa fa-plus"></i>&nbsp;&nbsp;City</button>
</div> 
</div> 
<div class="app_panel_content Filter-panel">
  <div class="row">
    <div class="col-md-6">
      <select name="city_master_status" onchange="list_reflect()" id="city_master_status" title="Select Status" class="form-control" style="width: 180px;">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
      </select>
    </div>
  </div>
</div>

<div id="div_list_content" class="loader_parent main_block">
    <div class="row mg_tp_20"> <div class="col-md-12 no-pad"> <div class="table-responsive">
        <table id="city_table" class="table table-hover" style="margin: 20px 0 !important;">   

        <thead>
            <tr>
              <th>Id</th>
              <th>City Name</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
        </thead>
  
        </table>
      </div>
    </div>
  </div>
</div>
<div id="div_city_list_update_modal"></div>
<input type="hidden" id='ajax_data' />
<script>
  var columns = [{
      title: "City Id"
    },
    {
      title: "City Name"
    },
    {
      title: "Status"
    },
    {
      title: "Actions",
      className: "text-center"
    }
  ]

  function list_reflect() {
    $("#city_table").dataTable().fnDestroy();
    var city_master_status = $('#city_master_status').val();
    var url = 'cities/list_reflect-server.php';
    if (city_master_status != "") {
      url += "?active_flag=" + city_master_status;
    }
    $('#city_table').DataTable({
      processing: true,
      serverSide: true,
      ajax: url,
      "createdRow": function(row, data) {
        console.log(data);
        if (data[2] == "Inactive") {
          $(row).addClass('danger');

        }

      }

    });


  }
  list_reflect();

  function city_master_update_modal(city_id) {

    $('#update_city-' + city_id).prop('disabled', true);
    $('#update_city-' + city_id).button('loading');
    $.post('cities/update_modal.php', {
      city_id: city_id
    }, function(data) {
      $('#div_city_list_update_modal').html(data);
      $('#update_city-' + city_id).prop('disabled', false);
      $('#update_city-' + city_id).button('reset');
    });
  }
</script>
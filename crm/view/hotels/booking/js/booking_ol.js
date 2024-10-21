//**Hotel Name load start**//
function hotel_name_list_load(id) {

  var count = id.substring(7);
  var city_id = $("#" + id).val();
  $.get("inc/hotel_name_load.php", { city_id: city_id }, function (data) {
    $("#hotel_id" + count).html(data);
  });
}
// room category
function hotel_type_load_cate(id)
{
  var count = id.substring(8);
  var hotel_id = $("#" + id).val();
  $.get("inc/hotel_category.php", { hotel_id: hotel_id }, function (data) {
    $("#category" + count).html(data);
  });   
}


function calculate_total_nights(from_date1, to_date1, night_id) {

  var from_date = $('#' + from_date1).val();
  from_date = from_date.split(' ')[0];
  var to_date = $('#' + to_date1).val();
  to_date = to_date.split(' ')[0];
  if (from_date != '' && to_date != '') {
    var edate = from_date.split('-');
    e_date = new Date(edate[2], edate[1] - 1, edate[0]).getTime();
    var edate1 = to_date.split('-');
    e_date1 = new Date(edate1[2], edate1[1] - 1, edate1[0]).getTime();

    var one_day = 1000 * 60 * 60 * 24;

    var from_date_ms = new Date(e_date).getTime();
    var to_date_ms = new Date(e_date1).getTime();

    var difference_ms = to_date_ms - from_date_ms;
    var total_days = Math.round(Math.abs(difference_ms) / one_day);

    total_days = parseFloat(total_days);
    $('#' + night_id).val(total_days);
  }
  else {
    $('#' + night_id).val(0);
  }
}
function get_options(element){

  var quotation_id = $(element).val();
  var base_url = $('#base_url').val();
  
  $.get(base_url + 'view/hotels/booking/inc/get_options.php', {quotation_id:quotation_id}, function (data) {
    $('#hotel_options').html(data);
    get_quotation_details();
  });
}
function get_quotation_details(){
  
  var base_url = $('#base_url').val();
  var quotation_id = $('#quotation_id').val();
  var hotel_option = $('#hotel_options').val();
  
  $.get(base_url + 'view/hotels/booking/inc/get_currency_dropdown.php', {quotation_id:quotation_id}, function (data) {
    $('#currency_div').html(data);
  });

  if(quotation_id == ''){
    var table = document.getElementById('tbl_hotel_booking');
    for (var k = 1; k < table.rows.length; k++) {
      document.getElementById("tbl_hotel_booking").deleteRow(k);
    }
    $('#pass_name').val('');
    $('#adults').val('');
    $('#childrens').val('');
    $('#infants').val('');
    $('#sub_total').val(0);
    $('#sub_total').trigger('change');
    $('#service_charge').val(0);
    $('#tax_apply_on').val('');
    $('#tax_apply_on').trigger('change');
    $('#tax_value').val('');
    $('#tax_value').trigger('change');
    $('#markup').val(0);
    $('#markup_tax_value').val('');
    $('#markup_tax_value').trigger('change');
  }
  else{

    $.getJSON(base_url + 'view/hotels/booking/booking/get_quotation_details.php', { quotation_id: quotation_id,hotel_option:hotel_option }, function (data) {
        //Hotels
        var table = document.getElementById('tbl_hotel_booking');
        for (var i = 1; i < table.rows.length; i++) {
          document.getElementById("tbl_hotel_booking").deleteRow(i);
        }
        for (var i = 1; i < table.rows.length; i++) {
          document.getElementById("tbl_hotel_booking").deleteRow(i);
        }
        for (var i = 1; i < table.rows.length; i++) {
          document.getElementById("tbl_hotel_booking").deleteRow(i);
        }
        data.hotel_details = (data.city_hotel_details) ? data.city_hotel_details : [];
        data.costing_details = (data.costing_details) ? data.costing_details : [];
        if (table.rows.length != data.hotel_details.length) {
          for (var i = 1; i < data.hotel_details.length; i++) {
            addRow('tbl_hotel_booking');
          }
        }
        $.each(data.hotel_details, function (index, fields) {
          
          var field = fields['data'];
          var row = table.rows[index];
          row.cells[2].childNodes[0].value = field['tour_type'];
          $(row.cells[3].childNodes[0]).select2('destroy');
          $(row.cells[3].childNodes[0]).append('<option value="' + field['city_id'] + '" selected>' + fields['city_name'] + '</option>');
          city_lzloading('#' + row.cells[3].childNodes[0].id);
          $(row.cells[4].childNodes[0]).append('<option value="' + field['hotel_id'] + '" selected>' + fields['hotel_name'] + '</option>');
          // hotel_type_load_cate(row.cells[4].childNodes[0].id);
          row.cells[5].childNodes[0].value = field['checkin'] + ' 00:00';
          row.cells[6].childNodes[0].value = field['checkout'] + ' 00:00';
          row.cells[7].childNodes[0].value = field['hotel_stay_days'];
          row.cells[8].childNodes[0].value = field['total_rooms'];
          
          $(row.cells[10].childNodes[0]).prepend('<option value="' + field['hotel_cat'] + '">' + field['hotel_cat'] + '</option>');
          row.cells[10].childNodes[0].value = field['hotel_cat'];
          // document.getElementById(row.cells[10].childNodes[0].id).selectedIndex = 0;
          // $(row.cells[10].childNodes[0]).select2('destroy');
          row.cells[12].childNodes[0].value = field['extra_bed'];
          row.cells[13].childNodes[0].value = field['meal_plan'];
          
				  // $('#' + row.cells[10].childNodes[0].id).select2().trigger("change");
        });
        // Enquiry
        $('#pass_name').val(data.enquiry_details.customer_name);
        $('#adults').val(data.enquiry_details.total_adult);
        $('#childrens').val(Number(data.enquiry_details.children_without_bed));
        $('#children_with').val(Number(data.enquiry_details.children_with_bed));
        $('#infants').val(data.enquiry_details.total_infant);
        // Costing
        $('#service_charge').val(data.costing_details[0]['costing']['service_charge']);
        $('#tax_apply_on').val(data.costing_details[0]['costing']['tax_apply_on']);
        $('#tax_value').val(data.costing_details[0]['costing']['tax_value']);
        $('#markup').val(data.costing_details[0]['costing']['markup_cost']);
        $('#markup_tax_value').val(data.costing_details[0]['costing']['markup_tax_value']);
        $('#sub_total').val(data.costing_details[0]['costing']['hotel_cost']);
        $('#sub_total').trigger('change');
    });
  }
}
//**Hotel Name load end**//
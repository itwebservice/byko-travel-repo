<tr>
  <td><input class="css-checkbox" id="chk_visa<?= $offset ?>1"  type="checkbox" checked><label class="css-label" for="chk_visa<?= $offset ?>1"> <label></td>
  <td><input maxlength="15" value="1" type="text" name="username" placeholder="Sr. No." class="form-control" disabled /></td>
  <td><input class="form-control" type="text" id="first_name<?= $offset ?>1" name="first_name<?= $offset ?>1" onchange="fname_validate(this.id)" placeholder="*First Name" title="First Name" style="width:150px;" /></td>
  <td><input class="form-control" type="text" id="middle_name<?= $offset ?>1" onchange="fname_validate(this.id)" name="middle_name<?= $offset ?>1" placeholder="Middle Name" title="Middle Name" style="width:150px;" /></td>
  <td><input class="form-control" type="text" id="last_name<?= $offset ?>1" name="last_name<?= $offset ?>1" onchange="fname_validate(this.id)" placeholder="Last Name" title="Last Name" style="width:150px;" /></td>
  <td><input class="form-control app_datepicker" type="text" id="birth_date<?= $offset ?>1" name="birth_date<?= $offset ?>1" placeholder="Birth Date" title="Birth Date" value="<?= date('d-m-Y',  strtotime(' -1 day')) ?>" onchange="checkFutureDate(this.id);adolescence_reflect(this.id)" style="width:150px;" /></td>
  <td><input class="form-control" type="text" id="adolescence<?= $offset ?>1" name="adolescence<?= $offset ?>1" placeholder="Adolescence" title="Adolescence" style="width:150px;" disabled /></td>
  <td><select name="visa_country_name<?= $offset ?>1" id="visa_country_name<?= $offset ?>1" class="app_select2 form-control" title="Visa Country Name" style="width:150px">
      <option value="">*Visa Country</option>
      <?php
      $sq_country = mysqlQuery("select * from country_list_master");
      while ($row_country = mysqli_fetch_assoc($sq_country)) {
      ?>
        <option value="<?= $row_country['country_name'] ?>"><?= $row_country['country_name'] ?></option>
      <?php
      }
      ?>
    </select>
  </td>
  <td><select class="form-control" name="visa_type<?= $offset ?>1" id="visa_type<?= $offset ?>1" title="Visa Type" style="width:150px;" onchange="reflect_cost(this.id)">
      <option value="">*Visa Type</option>
      <?php
      $sq_visa_type = mysqlQuery("select * from visa_type_master");
      while ($row_visa_type = mysqli_fetch_assoc($sq_visa_type)) {
      ?>
        <option value="<?= $row_visa_type['visa_type'] ?>"><?= $row_visa_type['visa_type'] ?></option>
      <?php
      }
      ?>
    </select>
  </td>
  <td><input class="form-control" type="text" id="passport_id<?= $offset ?>1" name="passport_id<?= $offset ?>1" onchange="validate_passport(this.id)" placeholder="Passport ID" title="Passport ID" style="text-transform: uppercase;width:150px;" /></td>
  <td><input class="form-control app_datepicker" type="text" id="issue_date<?= $offset ?>1" name="issue_date<?= $offset ?>1" placeholder="Issue Date" title="Issue Date" style="width:150px;"></td>
  <td><input class="form-control app_datepicker" type="text" id="expiry_date<?= $offset ?>1" name="expiry_date<?= $offset ?>1" placeholder="Expiry Date" title="Expiry Date" onchange="validate_issueDate('issue_date<?= $offset ?>1',this.id)" style="width:150px;" /></td>
  <td><input class="form-control" type="text" id="nationality<?= $offset ?>1" name="nationality<?= $offset ?>1" placeholder="*Nationality" title="Nationality" style="width:150px;" /></td>
  <td><input class="form-control app_datepicker" type="text" id="appointment<?= $offset ?>1" name="appointment<?= $offset ?>1" value="<?= date('d-m-Y') ?>" placeholder="Appointment Date" title="Appointment Date" style="width:160px;"></td>
  <td><input type="hidden" id="visa_cost<?= $offset ?>1" name="visa_cost<?= $offset ?>1"></td>
</tr>

<script>
  $('#visa_country_name<?= $offset ?>1').select2();

  //check future date
  function checkFutureDate(id) {
    var idate = document.getElementById(id).value;
    var today = new Date().setHours(0, 0, 0, 0),
      idate = idate.split("-");

    idate = new Date(idate[2], idate[1] - 1, idate[0]).setHours(0, 0, 0, 0);

    if (idate >= today) {
      error_msg_alert(" Date cannot be current date or future date");
      $('#' + id).css({
        'border': '1px solid red'
      });
      document.getElementById(id).value = "";
      $('#' + id).focus();
      g_validate_status = false;
      return false;
    }
  }

  function checkPassportDate(id) {
    var date1 = document.getElementById(id).value;
    var dates = date1.split("-");
    dates = new Date(dates[2], dates[1] - 1, dates[0]).getTime();
    if (dates == '') {
      error_msg_alert('Please enter the Date..!!');
      return false;
    }
    else if (!date1.match(/^(0[1-9]|[12][0-9]|3[01])[\- \/.](?:(0[1-9]|1[012])[\- \/.](19|20)[0-9]{2})$/)) {
      error_msg_alert('Date format is wrong');
      return false;
    }

      var today = new Date().getTime()
    date = Date.parse(date);
    if (today < dates) {
          error_msg_alert("Date cannot be future date");
          $('#'+id).css({'border':'1px solid red'});  
          document.getElementById(id).value="";
          $('#'+id).focus();
          g_validate_status = false;
          return false;		
    }
  }

  function validate_issueDate(from, to) {
    
    var from_date = $('#' + from).val();
    var to_date = $('#' + to).val();

    var parts = from_date.split('-');
    var date = new Date();
    var new_month = parseInt(parts[1]) - 1;
    date.setFullYear(parts[2]);
    date.setDate(parts[0]);
    date.setMonth(new_month);

    var parts1 = to_date.split('-');
    var date1 = new Date();
    var new_month1 = parseInt(parts1[1]) - 1;
    date1.setFullYear(parts1[2]);
    date1.setDate(parts1[0]);
    date1.setMonth(new_month1);

    var one_day = 1000 * 60 * 60 * 24;

    var from_date_ms = date.getTime();
    var to_date_ms = date1.getTime();

    if (from_date_ms > to_date_ms) {
      error_msg_alert(" Date should be greater than passport issue date");
      $('#' + to).css({
        'border': '1px solid red'
      });
      document.getElementById(to).value = "";
      $('#' + to).focus();
      g_validate_status = false;
      return false;
    }
  }
</script>
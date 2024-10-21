<h2 class="c-heading">
  Booking Summary
</h2>

<!-- Table -->
<div class="clearfix c-table st-dataTable">
  <div class="clearfix">
    <div class="row">
        <div class="col-md-8">
            <div class="formField">
            <label>Search By Date</label>
            <input type="text" id="from_date_filter" onchange="get_to_date1(this.id,'to_date_filter');list_reflect();" class="txtBox d-inline-block wAuto" placeholder="From Date" />
            <input type="text" id="to_date_filter" onchange="validate_validDate1('from_date_filter','to_date_filter');list_reflect();" class="txtBox d-inline-block wAuto" placeholder="To Date" />
            </div>
        </div>
    </div>
  </div>
  <table class="table" id="tbl_list">
  </table>
</div>
<!-- Table End -->
<?php
include "../../../model/model.php";
// /*======******Header******=======*/
require_once('../../layouts/admin_header.php');
$role = $_SESSION['role'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$q = "select * from branch_assign where link='reports/analysis_reports/index.php'";
$sq_count = mysqli_num_rows(mysqlQuery($q));
$sq = mysqli_fetch_assoc(mysqlQuery($q));
$branch_status = ($sq_count >0 && $sq['branch_status'] !== NULL && isset($sq['branch_status'])) ? $sq['branch_status'] : 'no';
?>
<input type="hidden" id="branch_status" name="branch_status" value="<?= $branch_status ?>">
<?= begin_panel('Analysis Reports', 96) ?> <span style="font-size: 15px;font-weight: 400;color: #006d6d;margin-left: 15px; text-transform:capitalize;" id="span_report_name"></span>
<script src="tabletoexcel.js"></script>
<div class="report_menu main_block">
  <div class="row">
    <div class="col-xs-12">
      <nav class="navbar navbar-default">
        <div class="container-fluid">
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <!-- Menu start -->
            <ul class="nav navbar-nav">
              <!-- <li class="dropdown">
                  <a href="#">Business Reports <span class="caret"></span></a>
                  <ul class="dropdown_menu no-pad">

                  <li><span onclick="show_report_reflect('Group Tour')">Group Tour</span></li>
                  <li class="dropdown_two">
                    <span onclick="show_sub_menu('sub_menu_1')">Gross Sale</span>
	                    <ul class="dropdown_menu_two" id="sub_menu_1">
                          <li><span onclick="show_report_reflect('Gross sale summary')">Summary</span></li>
	                        <li><span onclick="show_report_reflect('Gross sale details')">Details</span></li>
                      </ul>
                    </li> -->
              <!-- <li><span onclick="show_report_reflect('Refund Gross')">Refund Gross</span></li>
                    <li><span onclick="show_report_reflect('Sale Net')">Sale Net</span></li>
                    <li><span onclick="show_report_reflect('Debit Position')">Debit Position</span></li>
                    <li><span onclick="show_report_reflect('Consolidated Report')">Consolidated Report</span></li>
                    <li><span onclick="show_report_reflect('Comparative Hotels')">Comparative Hotels</span></li>
                    <li><span onclick="show_report_reflect('Comparative Income')">Comparative Income</span></li>
                    <li><span onclick="show_report_reflect('Comparative Liabilities')">Comparative Liabilities</span></li>
                    <li><span onclick="show_report_reflect('Comparative Misc.')">Comparative Misc.</span></li>
                  </ul>
                </li> -->
              <!-- Single Menu start -->
              <li class="dropdown">
                <a href="#">Statistic Reports <span class="caret"></span></a>
                <ul class="dropdown_menu no-pad">
                  <li><span onclick="show_report_reflect('Branch Wise')">Branch Wise</span></li>
                  <li><span onclick="show_report_reflect('User Wise')">User Wise</span></li>
                  <li><span onclick="show_report_reflect('Source Wise')">Source Wise</span></li>
                  <li><span onclick="show_report_reflect('Service Wise')">Service Wise</span></li>
                  <li><span onclick="show_report_reflect('Enquiry Wise')">Enquiry Wise</span></li>
                  <li><span onclick="show_report_reflect('Missing Followups')">Missing Followups</span></li>
                  <li><span onclick="show_report_reflect('B2B Agent')">B2B Agent</span></li>
                  <!-- <li><span onclick="show_report_reflect('Userwise_sale')">Userwise Sale Report</span></li> -->
                </ul>
              </li>
              <!-- Single Menu end -->
              <!-- Single Menu start -->
              <li class="dropdown">
                <a href="#">Comparative <span class="caret"></span></a>
                <ul class="dropdown_menu no-pad">
                  <li><span onclick="show_report_reflect('Comparative Hotels')">Comparative Hotels</span></li>
                  <li><span onclick="show_report_reflect('Comparative Airlines')">Comparative Airlines</span></li>
                  <li><span onclick="show_report_reflect('Comparative Sectors')">Comparative Sectors</span></li>
                  <!-- <li><span onclick="show_report_reflect('Repeater Customer')">Repeater Customer</span></li> -->
                  <li><span onclick="show_report_reflect('Destination Wise')">Destination Wise</span></li>
                  <li><span onclick="show_report_reflect('Supplier Wise')">Supplier Wise</span></li>
                  <li><span onclick="show_report_reflect('Tariff Expiry')">Tariff Expiry</span></li>
                  <!-- <li><span onclick="show_report_reflect('Agent Wise Query Sale')">Agent Wise Query Sale</span></li> -->
                </ul>
              </li>
              <!-- Single Menu end -->
              <!-- Single Menu start -->
              <li class="dropdown">
                <a href="#">Other Reports <span class="caret"></span></a>
                <ul class="dropdown_menu no-pad">
                  <li><span onclick="show_report_reflect('Updated Entries')">Updated Entries</span></li>
                  <li><span onclick="show_report_reflect('Deleted Entries')">Deleted Entries</span></li>
                  <li><span onclick="show_report_reflect('Day Wise Itinerary')">Day Wise Itinerary</span></li>
                </ul>
              </li>
              <!-- Single Menu end -->

            </ul>
          </div>
        </div>
      </nav>
    </div>
  </div>
</div>
<!-- Main Menu End -->
<div class="col-xs-12 mg_tp_20">
  <div id="div_report_content" class="main_block">
  </div>

  <div id="exportTableHidden" style="display: none;">
  </div>
</div>

</div>
<?= end_panel() ?>
<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>

<script src="../js/adnary.js"></script>

<script type="text/javascript">
  $(function() {
    $("a").on("click", function() {
      if ($(this).parent('li').attr('class') == "dropdown active") {
        $("li.active").removeClass("active");
      } else {
        $("li.active").removeClass("active");
        $(this).parent('li').addClass("active");
      }
    });
  });

  $(function() {
    $("span").on("click", function() {
      $("li.active").removeClass("active");
      $(this).closest('li.dropdown').addClass("active");
    });
  });

  function show_sub_menu(sub_menu_id) {
    $('.dropdown_menu_two').slideUp('slow');
    $('.dropdown_menu_three').slideUp('slow');
    if ($('#' + sub_menu_id).css('display') == 'none') {
      $('#' + sub_menu_id).slideDown('slow');
    } else {
      $('#' + sub_menu_id).slideUp('slow');
    }
  }

  function show_report_reflect(report_name) {

    $('#span_report_name').html(report_name);

    if (report_name == "Branch Wise") {
      url = 'report_reflect/branchwise_report/index.php';
    }
    if (report_name == "User Wise") {
      url = 'report_reflect/userwise_report/index.php';
    }
    if (report_name == "Source Wise") {
      url = 'report_reflect/sourcewise_report/index.php';
    }
    if (report_name == "Service Wise") {
      url = 'report_reflect/servicewise_report/index.php';
    }
    if (report_name == "Enquiry Wise") {
      url = 'report_reflect/enquirywise_report/index.php';
    }
    if (report_name == "Missing Followups") {
      url = 'report_reflect/missing_followups/index.php';
    }
    if (report_name == "B2B Agent") {
      url = 'report_reflect/b2b_agent/index.php';
    }
    if (report_name == "Userwise_sale") {
      url = 'report_reflect/userwise_sale_report/index.php';
    }
    if (report_name == "Comparative Hotels") {
      url = 'report_reflect/comparative_hotel_report/index.php';
    }
    if (report_name == "Comparative Airlines") {
      url = 'report_reflect/comparative_airlines_report/index.php';
    }
    if (report_name == "Comparative Sectors") {
      url = 'report_reflect/comparative_sector_report/index.php';
    }
    if (report_name == "Repeater Customer") {
      url = 'report_reflect/repeater_customer_report/index.php';
    }
    if (report_name == "Destination Wise") {
      url = 'report_reflect/destination_wise_report/index.php';
    }
    if (report_name == "Supplier Wise") {
      url = 'report_reflect/supplier_wise_report/index.php';
    }
    if (report_name == "Agent Wise Query Sale") {
      url = 'report_reflect/agent_wise_query_sale_report/index.php';
    }
    if (report_name == "Day Wise Itinerary") {
      url = 'report_reflect/itenary report/index.php';
    }
    if (report_name == "Deleted Entries") {
      url = 'report_reflect/deleted_entries/index.php';
    }
    if (report_name == "Tariff Expiry") {
      url = 'report_reflect/tariff_expiry/index.php';
    }
    if (report_name == "Updated Entries") {
      url = 'report_reflect/updated_entries/index.php';
    }
    $.post(url, {}, function(data) {

      $(".dropdown_menu").addClass('hidden');
      $("li.active").removeClass("active");
      $('#div_report_content').html(data);
      setTimeout(
        function() {
          $(".dropdown_menu").removeClass('hidden');
        }, 500);
    });
  }
  show_report_reflect('Branch Wise');
</script>

<script>
  function exportToExcel(tableid) {
   

    $(document).ready(function () {
        var currentdate = new Date();
        tableMain =  document.getElementById(tableid);
        tableHidden =  document.getElementById('exportTableHidden');

        var clone = $(tableMain).clone(true);
        clone.find('.no-export').each(function(){
          	$(this).remove();
          }); 

        clone.appendTo(tableHidden);
       
        TableToExcel.convert(tableHidden, {
              name: tableid+'-'+currentdate.getDate() +'-'+(currentdate.getMonth()+1)+'-'+currentdate.getFullYear()+'-'+ currentdate.getHours() +'_'+ currentdate.getMinutes()+'_'+ currentdate.getSeconds()+".xlsx",
              sheet: {
              name: "Sheet1"
              }

          });

  

          document.getElementById('exportTableHidden').innerHTML = '';
          tableHidden = '';
     });



  }
</script>
<?php
/*======******Footer******=======*/
require_once('../../layouts/admin_footer.php');
?>
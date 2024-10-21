<?php
include "../../model/model.php";
/*======******Header******=======*/
require_once('../layouts/admin_header.php');
global $theme_color, $theme_color_dark, $theme_color_2, $topbar_color, $sidebar_color;
$settings = mysqli_fetch_array(mysqlQuery("select client_id from app_settings"));
$client_id = !empty($settings['client_id']) ? $settings['client_id'] : 0;
$emp_id = $_SESSION['emp_id'];
$sq_emp = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id='$emp_id'"));
if ($sq_emp['first_name'] == '') {
    $emp_name = 'Admin';
} else {
    $emp_name = $sq_emp['first_name'] . ' ' . $sq_emp['last_name'];
}
if (empty($client_id)) {
    echo "Set Client Access Key From App Settings";
    exit;
}
?>
<input type="hidden" id="client_id" value="<?= $client_id ?>">
<input type="hidden" id="base_url_support" value="https://itourssupport.in/">
<input type="hidden" id="client_name" value="<?= $emp_name ?>">


<div class="app_panel">
    <style>
        :root {
            --sys-color: <?= $theme_color ?>;
        }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>view/ask_gayatri/style.css">
    <input type="hidden" id="client_id" value="<?= $client_id ?>">
    <div class="tab-box">
        <nav class="tabs">
            <div class="selector"></div>
             <!--<a href="#" class="active" onclick="showFrame(1)"><i class="fa fa-bolt"></i>Ask</a>-->
            <a href="#" onclick="showFrame(2)" class="Chatsection"><i class="fa fa-globe"></i>Helpdesk</a>
        </nav>
    </div>
    <div id="print_frame"></div>



</div>


<?php
/*======******Footer******=======*/
require_once('../layouts/admin_footer.php');


?>
<script>
    // tabs
    var tabs = $('.tabs');
    var selector = $('.tabs').find('a').length;
    //var selector = $(".tabs").find(".selector");
    var activeItem = tabs.find('.active');
    var activeWidth = activeItem.innerWidth();
    $(".selector").css({
        "left": activeItem.position.left + "px",
        "width": activeWidth + "px"
    });

    $(".tabs").on("click", "a", function(e) {
        e.preventDefault();
        $('.tabs a').removeClass("active");
        $(this).addClass('active');
        var activeWidth = $(this).innerWidth();
        var itemPos = $(this).position();
        $(".selector").css({
            "left": itemPos.left + "px",
            "width": activeWidth + "px"
        });
    });

    

    function showFrame(val) {
        var base_url = $('#base_url').val();
        var url = base_url + 'view/ask_gayatri/ask_gayatri.php'
        if (val == 2) {
            var url = base_url + 'view/ask_gayatri/chat/index.php'
        }
        $.post(url, {}, function(data) {
            $('#print_frame').html(data);
        });

    }
</script>
<?php

if($_GET['notifi'])
{
    ?>
    <script>
        showFrame(2);
        $('.tabs a').removeClass("active");
        $('.Chatsection').addClass('active');
        var activeWidth = $('.Chatsection').innerWidth();
        var itemPos = $('.Chatsection').position();
        $(".selector").css({
            "left": itemPos.left + "px",
            "width": activeWidth + "px"
        });
        
    </script>
    <?php
}
else
{
    ?>
    <script>
        showFrame(2);
    </script>
    <?php
}
?>
<script>
        function showDropdown(id) {
            //document.getElementById("myDropdown").classList.toggle("showm");
            $("#" + id).addClass("showm");
        }
      

        // Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches(".dropbtn")) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains("showm")) {
                        openDropdown.classList.remove("showm");
                    }
                }
            }
        };
    </script>
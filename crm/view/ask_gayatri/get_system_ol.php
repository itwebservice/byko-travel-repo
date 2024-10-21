<?php
include "../../model/model.php";

global $theme_color, $theme_color_dark, $theme_color_2, $topbar_color, $sidebar_color;
$client_id = $_GET['client_id'];
?>

<input type="hidden" id="client_id" value="<?= $client_id ?>">
<input type="hidden" id="user_side_id" value="<?= $_SESSION['emp_id'] ?>">

<div class="right-header">

    <h4>
        <?= getClientNameGeneric($client_id) ?>
</h4>
    <div class="tab-box">
        <nav class="tabs">
            <div class="selector"></div>
            <a href="#" class="active" onclick="showFrame(2)"><i class="fa fa-comments"></i>Chat</a>
            <!--<a href="#" onclick="showFrame(1)"><i class="fa fa-bolt"></i>Ask</a>-->
        </nav>
    </div>
</div>

<div id="print_frame"></div>


<script>
    // for tabs section
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
    showFrame(2);

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
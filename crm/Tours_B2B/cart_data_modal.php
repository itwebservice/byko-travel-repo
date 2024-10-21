<?php include "../model/model.php";
$register_id = $_POST['register_id'];
$user_id = $_SESSION['user_id'];
$agent_flag = $_SESSION['agent_flag'];
$sq_reg = mysqli_fetch_assoc(mysqlQuery("select cart_data from b2b_login where user_id='$user_id' and agent_flag='$agent_flag'"));
$cart_list_arr = isset($sq_reg['cart_data']) ? json_decode($sq_reg['cart_data']) : '';
?>
<div id='cart_items'></div>
<script>
get_cart_items(<?= json_encode($cart_list_arr,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>);
//Remove Item from Cart
function remove_item(id){
    var base_url = $('#base_url').val();
    var register_id = $('#register_id').val();

    var cart_list_arr = JSON.parse(localStorage.getItem('cart_list_arr'));
    for (var i =0; i< cart_list_arr.length; i++) {
        if (cart_list_arr[i]['service']['uuid'] === id) cart_list_arr.splice(i, 1);
    }
    $('#cart_item_count').html(cart_list_arr.length);
    
    $.post(base_url+'controller/b2b_customer/update_cart.php', { register_id : register_id,cart_list_arr:cart_list_arr }, function (data){
        get_cart_items(cart_list_arr);
        localStorage.setItem("cart_list_arr", JSON.stringify(cart_list_arr));
    });
}
//proceed_to_checkout
function proceed_to_checkout(){
    var base_url = $('#base_url').val();
    window.location.href=base_url + 'Tours_B2B/checkout_pages/cartPage.php';
}
</script>
function get_why_choose_images(image_url){
    var btn_id = image_url.split('-');
    $('#image_btn-'+btn_id[1]).prop('disabled',true);
    var cmp_image_url = $('#'+image_url).val();
    $('#image_btn-'+btn_id[1]).button('loading');
    $.ajax({
        type:'post',
        url: 'cms/inc/why_choose_us/get_why_choose_images.php',
        data:{image_url:image_url,cmp_image_url:cmp_image_url},
        success:function(result){
            $('#image_modal').html(result);
            $('#image_btn-'+btn_id[1]).prop('disabled',false);
            $('#image_btn-'+btn_id[1]).button('reset');
        }
    });
}
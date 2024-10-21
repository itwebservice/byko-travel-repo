<?php
include '../../../model/model.php';
$query = mysqli_fetch_assoc(mysqlQuery("SELECT menu_option FROM `app_settings` where setting_id='1'"));
$menu = !empty($query['menu_option']) ? json_decode($query['menu_option']) : []; 

?>
<form id="section_package">
    <legend>Define Header Menu Options</legend>
  
    <div class="row mg_tp_20">
        <div class="col-md-12">
            <table class="table">
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Home</td>
                    <td><input type="checkbox" name="chk_menu[]" value="home" <?= in_array("home",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Group Tours</td>
                    <td><input type="checkbox" name="chk_menu[]" value="group_tours" <?= in_array("group_tours",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Holiday</td>
                    <td><input type="checkbox" name="chk_menu[]" value="holiday" <?= in_array("holiday",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Hotels</td>
                    <td><input type="checkbox" name="chk_menu[]" value="hotels" <?= in_array("hotels",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Activities</td>
                    <td><input type="checkbox" name="chk_menu[]" value="activities" <?= in_array("activities",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Visa</td>
                    <td><input type="checkbox" name="chk_menu[]" value="visa" <?= in_array("visa",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Transfer</td>
                    <td><input type="checkbox" name="chk_menu[]" value="transfer" <?= in_array("transfer",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Cruise</td>
                    <td><input type="checkbox" name="chk_menu[]" value="cruise" <?= in_array("cruise",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Serivces</td>
                    <td><input type="checkbox" name="chk_menu[]" value="services" <?= in_array("services",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Contact Us</td>
                    <td><input type="checkbox" name="chk_menu[]" value="contact_us" <?= in_array("contact_us",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                <tr>
                    <td style="width: 350px; font-size:15px; font-weight:bold;">Offers</td>
                    <td><input type="checkbox" name="chk_menu[]" value="offers" <?= in_array("offers",$menu) ? "checked" : "" ?> id="" class="form-check"></td>
                </tr>
                
            </table>
        </div>
    </div>
  
    <div class="row mg_tp_20">
        <div class="col-xs-12 text-center">
            <button class="btn btn-sm btn-success" type="button" onclick="updateMenu()"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Save</button>
        </div>
    </div>
</form>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>
<script>
    // chk_menu 
    function updateMenu()
    {
        var checkedValues = [];
         $('input[name="chk_menu[]"]:checked').each(function() {
            checkedValues.push($(this).val());
            });
            $.post('menus/save_menu.php',{
                'menu[]' : checkedValues
            },
                function(data)
                {
                    success_msg_alert(data);
                }
            );
    }
</script>
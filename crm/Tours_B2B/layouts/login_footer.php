<?php
?>
    <!-- ********** Component :: Footer ********** -->
    <footer class="c-footerCustom">
        <div class="footer-top">
            <div class="container">
            <div class="row">
                <div class="col-lg-4 col-sm-12">
                <span class="c-heading">About Us</span>
                <div class="info-section no-icon m20-btm">
                We're a DMC company that provides all ground level travel services like hotels, transfer, activities, meals to make your travel amazing.
                </div>
                <div class="c-footerLogos">
                    <ul>
                    <li><img src="<?php echo BASE_URL ?>Tours_B2B/images/svg/american-express.svg" /></li>
                    <li><img src="<?php echo BASE_URL ?>Tours_B2B/images/svg/visa.svg" /></li>
                    <li><img src="<?php echo BASE_URL ?>Tours_B2B/images/svg/mastercard.svg" /></li>
                    </ul>
                </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                <span class="c-heading">Connect With US</span>
                <div class="c-footerSocialIcons m20-btm">
                    <a href="#" target="_blank" class="social-element"><i class="icon itours-whatsapp"></i></a>
                    <button id="form_link" onclick="enquiry_form_open()" class="social-element"><i class="icon itours-letter-mail-1"></i></button>
                </div>
                <span class="c-heading">Follow US</span>
                <div class="c-footerSocialIcons">
                    <a href="#" target="_blank" class="social-element"><i class="icon itours-instagram-logo"></i></a>
                    <a href="#" target="_blank" class="social-element"><i class="icon itours-facebook"></i></a>
                    <a href="#" target="_blank" class="social-element"><i class="icon itours-youtube-play"></i></a>
                </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                <span class="c-heading">Contacts</span>
                <div class="info-section">
                    <i class="icon itours-location-pin"></i>
                    <?= $app_address ?>
                </div>
                <div class="info-section">
                    <i class="icon itours-call-phone"></i>
                    <?= $app_contact_no ?>
                </div>
                <div class="info-section">
                    <i class="icon itours-letter-mail-1"></i>
                    <?= $app_email_id ?>
                </div>
                </div>
            </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-12">
                <ul class="footerLinks">
                    <li>
                    <a
                        href="javascript:void(0);"
                        data-toggle="modal"
                        data-target="#termsModal"
                        >Terms & Conditions</a
                    >
                    </li>
                    <li>
                    <a
                        href="javascript:void(0);"
                        data-toggle="modal"
                        data-target="#privacyModal"
                        >Privacy Policy</a
                    >
                    </li>
                    <li>
                    <a
                        href="javascript:void(0);"
                        data-toggle="modal"
                        data-target="#cancelPolicyModal"
                        >Cancellation Policy
                    </a>
                    </li>
                    <li>
                    <a
                        href="javascript:void(0);"
                        data-toggle="modal"
                        data-target="#refundModal"
                        >Refund Policy</a
                    >
                    </li>
                    <li>
                    <a
                        href="javascript:void(0);"
                        data-toggle="modal"
                        data-target="#careersModal"
                        >Careers</a
                    >
                    </li>
                </ul>
                </div>
                <div class="col-lg-4 col-md-12">
                <div class="footerText">
                    <span><?= $query1['footer_strip'] ?></span>
                </div>
                </div>
            </div>
            </div>
        </div>
    </footer>
    <!-- ********** Component :: Footer End ********** -->
    <div id="enquiry_form_div"></div>

    <!-- ***** terms Modal ***** -->
    <div class="modal fade c-modal" id="termsModal" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="termsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalTitle">Terms & Conditions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="clearfix">
                <div class="row">
                    <div class="col">
                    <div class="clearfix m20-btm">
                        <span class="c-statictext">
                        <?= $query1['terms_cond'] ?>
                        </span>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <!-- ***** Terms Modal End ***** -->
    <!-- ***** privacy Modal ***** -->
    <div class="modal fade c-modal" id="privacyModal" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="privacyModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalTitle">Privacy Policy</h5>
                <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
                >
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="clearfix">
                <div class="row">
                    <div class="col">
                    <div class="clearfix m20-btm">
                        <span class="c-statictext">
                        <?= $query1['privacy_policy'] ?>
                        </span>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <!-- ***** policy Modal End ***** -->
    <!-- ***** cancelPolicy Modal ***** -->
    <div class="modal fade c-modal" id="cancelPolicyModal" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="cancelPolicyModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelPolicyModalTitle">Cancellation Policy</h5>
                <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
                >
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="clearfix">
                <div class="row">
                    <div class="col">
                    <div class="clearfix m20-btm">
                        <span class="c-statictext">
                        <?= $query1['cancellation_policy'] ?>
                        </span>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <!-- ***** cancelPolicy Modal End ***** -->
    <!-- ***** refundPolicy Modal ***** -->
    <div class="modal fade c-modal" id="refundModal" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="refundModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalTitle">Refund Policy</h5>
                <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
                >
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="clearfix">
                <div class="row">
                    <div class="col">
                    <div class="clearfix m20-btm">
                        <span class="c-statictext">
                        <?= $query1['refund_policy'] ?>
                        </span>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <!-- ***** refund Modal End ***** -->
    <!-- ***** careersPolicy Modal ***** -->
    <div class="modal fade c-modal" id="careersModal" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="careersModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="careersModalTitle">Careers Policy</h5>
                <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
                >
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="clearfix">
                <div class="row">
                    <div class="col">
                    <div class="clearfix m20-btm">
                        <span class="c-statictext">
                        <?= $query1['careers_policy'] ?>
                        </span>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <!-- ***** Careers Modal End ***** -->
    <div id="site_alert"></div>
    <!-- Javascript -->
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/jquery-ui.1.10.4.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/popper.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/bootstrap-4.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <!-- <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/jquery.timepicker.js"></script> -->
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/calendar.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/theme-scripts.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL ?>Tours_B2B/js/scripts.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.wysiwyg.js"></script>  
    <script src="<?php echo BASE_URL ?>js/script.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery-labelauty.js"></script>
    <script src="<?php echo BASE_URL ?>js/dynforms.vi.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.validate.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/vi.alert.js"></script>
    <script>
        function enquiry_form_open(){
            $('#form_link').prop('disabled',true);
            $('#form_link').button('loading');
            var base_url = $('#base_url').val();
            $.ajax({
                type:'post',
                url: base_url+'Tours_B2B/enquiry_form.php',
                success: function(data){
                    $('#form_link').prop('disabled',false);
                    $('#enquiry_form_div').html(data);
                }
            });
        }
    </script>
    </body>
</html>
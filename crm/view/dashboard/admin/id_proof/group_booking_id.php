<?php
include "../../../../model/model.php";
$id_proof_url = $_POST['id_proof_url'];
$pan_card_url = $_POST['pan_card_url'];
$pan_card_url3 = $_POST['pan_card_url3'];
$pan_card_url4 = $_POST['pan_card_url4'];
$newUrl = preg_replace('/(\/+)/','/',$id_proof_url);
$download_url = BASE_URL.str_replace('../', '', $newUrl);
$download_urlpan_card = preg_replace('/(\/+)/', '/', $pan_card_url);
$download_urlpan_card = BASE_URL . str_replace('../', '', $download_urlpan_card);
$download_urlpan_card3 = preg_replace('/(\/+)/','/',$pan_card_url3);
$download_urlpan_card3 = BASE_URL.str_replace('../', '', $download_urlpan_card3);
$download_urlpan_card4 = preg_replace('/(\/+)/','/',$pan_card_url4);
$download_urlpan_card4 = BASE_URL.str_replace('../', '', $download_urlpan_card4);
?>
<!-- Modal ID Proof -->
<div class="modal fade" id="id_img1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document" style="max-width: 1100px;">
    <div class="modal-content">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" data-original-title="" title="">×</span></button>   
      <div class="modal-body">
          <div class="id-img">
            <div class="row">
              <?php
                if($id_proof_url == '' && $pan_card_url == '' && $pan_card_url3 == '' && $pan_card_url4 == ''){ ?> 
                  <h3 style="color: #009898 !important; font-weight: 400 !important;">No ID Proof uploaded</h3>
                  <?php
                }
                else{
                  if($id_proof_url != ''){ ?>
                    <div class="col-md-3 col-sm-4">
                      <img src="<?php echo $download_url; ?>" class="img-responsive" style="margin-top: 5px;">
                    </div>
                  <?php
                  }
                  if($pan_card_url != ''){ ?>
                    <div class="col-md-3 col-sm-4">
                      <img src="<?php echo $download_urlpan_card; ?>" class="img-responsive" style="margin-top: 5px;">
                    </div>
                  <?php
                  }
                  if($pan_card_url3 != ''){ ?>
                    <div class="col-md-3 col-sm-4">
                      <img src="<?php echo $download_urlpan_card3; ?>" class="img-responsive" style="margin-top: 5px;">
                    </div>
                  <?php
                  }
                  if($pan_card_url4 != ''){ ?>
                    <div class="col-md-3 col-sm-4">
                      <img src="<?php echo $download_urlpan_card4; ?>" class="img-responsive" style="margin-top: 5px;">
                    </div>
                  <?php }
                }
                ?>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$('#id_img1').modal('show');
</script>
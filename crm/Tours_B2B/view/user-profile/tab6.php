<h2 class="c-heading">
    Markup & Tax
</h2>
<div class="accordion c-userProfileAccord" id="accordionExample">

  <!-- ** Accord 3 ** -->
    <div class="card">
        <div class="card-header" id="headingmarkupInformaiton">
        <button class="btnShow collapse show" type="button" data-toggle="collapse"
            data-target="#collapsemarkupInformaiton" aria-expanded="false"
            aria-controls="collapsemarkupInformaiton">
            <span>Save Markup and Taxes</span>
        </button>
        </div>
        <div id="collapsemarkupInformaiton" class="collapse show" aria-labelledby="headingmarkupInformaiton"
        data-parent="#accordionExample">
        <div class="card-body">
            <form id="create_markup">
            <input type="hidden" id="register_id" value="<?= $sq_query['register_id'] ?>" />
            <div class="row">
                <div class="col-md-6 col-sm-6 col-12">
                <div class="formField">
                    <label>*Markup In</label>
                    <select class="form-control full-width" id="markup_in" name="markup_in" title="Select Markup In" data-toggle="tooltip" required>
                        <?php
                        if($sq_query['markup_in'] != ''){ ?>
                        <option value="<?= $sq_query['markup_in'] ?>"><?= $sq_query['markup_in'] ?></option>
                        <?php } ?>
                        <option value="">Select Markup In</option>
                        <option value="Percentage">Percentage</option>
                        <option value="Flat">Flat</option>
                    <select>
                </div>
                </div>
                <div class="col-md-6 col-sm-6 col-12">
                <div class="formField">
                    <label>*Markup Amount</label>
                    <input type="number" class="form-control" value="<?= $sq_query['markup_amount'] ?>" placeholder="*Markup Amount" id="markup_amount" name="markup_amount" title="Enter Markup Amount" data-toggle="tooltip" required />
                </div>
                </div>
                <div class="col-md-6 col-sm-6 col-12">
                <div class="formField">
                    <label>*Tax In</label>
                    <select class="form-control full-width" id="tax_in" name="tax_in" title="Select Tax In" data-toggle="tooltip" required>
                        <?php
                        if($sq_query['tax_in'] != ''){ ?>
                        <option value="<?= $sq_query['tax_in'] ?>"><?= $sq_query['tax_in'] ?></option>
                        <?php } ?>
                        <option value="">Select Tax In</option>
                        <option value="Percentage">Percentage</option>
                        <option value="Flat">Flat</option>
                    <select>
                </div>
                </div>
                <div class="col-md-6 col-sm-6 col-12">
                <div class="formField">
                    <label>*Tax Amount</label>
                    <input type="number" class="form-control" value="<?= $sq_query['tax_amount'] ?>" placeholder="*Tax Amount" id="tax_amount" name="tax_amount" title="Enter Tax Amount" data-toggle="tooltip" required />
                </div>
                </div>
                <div class="col-12 text-center">
                <button class="c-button" id="markuptax_save">Save</button>
                </div>
            </div>
            </form>
        </div>
        </div>
    </div>
    <!-- ** Accord 3 End ** -->

</div>
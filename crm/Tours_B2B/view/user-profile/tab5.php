<h2 class="c-heading">
  Users
</h2>
<div class="accordion c-userProfileAccord" id="accordionExample">

  <!-- ** Accord 3 ** -->
    <div class="card">
        <div class="card-header" id="headingPersonalInformaiton">
        <button class="btnShow collapsed" type="button" data-toggle="collapse"
            data-target="#collapseuserInformaiton" aria-expanded="false"
            aria-controls="collapseuserInformaiton">
            <span>Create User</span>
        </button>
        </div>
        <div id="collapseuserInformaiton" class="collapse" aria-labelledby="headingPersonalInformaiton"
        data-parent="#accordionExample">
        <div class="card-body">
            <form id="create_user">
            <input type="hidden" id="register_id" value="<?= $sq_query['register_id'] ?>" />
            <div class="row">
                <div class="col-md-4 col-sm-6 col-12">
                <div class="formField">
                    <label>*Full Name</label>
                    <input type="text" class="txtBox" id="full_name" name="full_name" placeholder="Full Name" onkeypress="return blockSpecialChar(event);" required />
                </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                <div class="formField">
                    <label>*Email ID</label>
                    <input type="email" class="txtBox" id="u_email_id" name="u_email_id" placeholder="Email ID" required />
                </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                <div class="formField">
                    <label>*Mobile No</label>
                    <input type="number" class="txtBox" id="u_mobile_no" name="u_mobile_no" placeholder="Mobile No" required />
                </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                <div class="formField">
                    <label>*Username</label>
                    <input type="text" class="txtBox" id="u_username" name="u_username" placeholder="Username" required />
                </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                <div class="formField">
                    <label>*Enter New Password</label>
                    <input type="Password" class="txtBox" id="upassword" name="upassword" placeholder="New Password" required />
                </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                <div class="formField">
                    <label>*Re-enter New Password</label>
                    <input type="Password" class="txtBox" id="urepassword" placeholder="Re-enter Password" required />
                </div>
                </div>
                <div class="col-12 text-center">
                <button class="c-button" id="user_save">Save</button>
                </div>
            </div>
            </form>
        </div>
        </div>
    </div>
    <!-- ** Accord 3 End ** -->

</div>
<div class="clearfix c-table st-dataTable">
    <div class="table-responsive">
    <table id="user_table" class="table table-hover"></table>
    </div>
</div>
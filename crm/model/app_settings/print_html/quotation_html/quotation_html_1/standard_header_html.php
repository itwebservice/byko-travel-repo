<!-- header -->
  <div class="col-md-4 no-pad">
    <div class="print_header_contact text-right">
      <span class="title"><?php echo $app_name; ?></span><br>
      <p><?php echo ($branch_status=='yes' && $role!='Admin') ? $branch_details['address1'].','.$branch_details['address2'].','.$branch_details['city'] : $app_address ?></p>
      <p class="no-marg"><i class="fa fa-phone" style="margin-right: 5px;"></i> <?php echo ($branch_status=='yes' && $role!='Admin') ? $branch_details['contact_no'] : $app_contact_no ?></p>
      <p><i class="fa fa-envelope" style="margin-right: 5px;"></i> <?php echo ($branch_status=='yes' && $role!='Admin' && $branch_details['email_id'] != '') ? $branch_details['email_id'] : $app_email_id; ?></p>
      <?php if($app_website != ''){?><p><i class="fa fa-globe" style="margin-right: 5px;"></i><?php echo $app_website; ?></p><?php }?>
    </div>
  </div>
</section>

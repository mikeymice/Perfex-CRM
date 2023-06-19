<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h3><?php echo _l('sms_title'); ?></h3>
                  <br>
                  <div class="emailsmswrapper">
                  <h5><?php echo _l('sms_select_title'); ?></h5>
                  <form action="<?php print(admin_url('custom_email_and_sms_notifications/email_sms/sendEmailSms')) ?>" enctype='multipart/form-data' method="post">
                  <select class="selectpicker"
		                  data-toggle="<?php echo $this->input->get('allowed_payment_modes'); ?>"
		                  name="allowed_payment_modes[]"
		                  data-actions-box="true"
		                  multiple="true"
		                  data-width="100%"
		                  data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">	      
	                     <?php foreach ($clients as $client) { ?>
							<option value="<?php print($client->userid) ?>"><?php print($client->company) ?></option>
		                 <?php } ?>
	                  </select>
						<br><br>
			        <h5><?php echo _l('template_select_title'); ?></h5>
			        <select class="selectpicker"
		                  name="template"
		                  data-actions-box="true"
		                  data-width="100%" id="tempaltes">
	                     	<option value="">Nothing Selected</option>
	                     <?php foreach ($templates as $template) { ?>
							<option value="<?php print($template['id']) ?>"><?php print($template['template_name']) ?></option>
		                 <?php } ?>
	                  </select>
						<br><br>
					  <h5><?php echo _l('write_your_notification'); ?></h5>
                      <script> function countChars(obj){ document.getElementById("charNum").innerHTML = '<i class="fa fa-calculator" aria-hidden="true"></i> '+obj.value.length; } </script>
	                  <textarea placeholder="<?php echo _l('sms_textarea_placeholder'); ?>" name="message" rows="10" onkeyup="countChars(this);" class="form-control" id="msg_content"></textarea>
	                <p id="charNum"><i class="fa fa-calculator" aria-hidden="true"></i> 0</p>

						<br><br>
	                  <div>
	                  		<h5><?php echo _l('attachment_note'); ?></h5>
		                  <input name="file_mail" value="filemail" class="check_label radio" type="file">
	                  </div>
						
	                  <div class="check_div_mail">
		                  <input name="mail_or_sms" value="mail" class="check_label radio" type="radio" style="display:inline-block"> <span class="mail-or-sms-choice"><?php echo _l('send_as_email'); ?></span>
	                  </div>
					  <div class="check_div_sms">
		                  <input name="mail_or_sms" value="sms" class="check_label radio" type="radio" style="display:inline-block"> <span class="mail-or-sms-choice"><?php echo _l('send_as_sms'); ?></span>
					  </div>
	                  <br>
	                  <button class="btn-tr btn btn-info invoice-form-submit transaction-submit"><?php echo _l('send'); ?></button>
                  </form>
                 </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#tempaltes').change(function(e){
        	var template_info_url = "<?= base_url(CUSTOM_EMAIL_AND_SMS_NOTIFICATIONS_MODULE_NAME.'/template/get_template_data'); ?>";
        	var template_id = $(this).val();
        	if (template_id === "") {
    			return false;
			}
			$.ajax({
				url: template_info_url,
				type: 'POST',
				dataType: 'json',
				data: {template_id:template_id},
				success:function(resJSON){
					$("#msg_content").html(resJSON[0].template_content);
				}
			});	
		});
	});
</script>
</body>
</html>
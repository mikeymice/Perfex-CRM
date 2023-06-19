<?php defined('BASEPATH') or exit('No direct script access allowed'); 
?>
<?php 
$file_header = array();
$file_header[] = _l('job_name');
$file_header[] = _l('description');
$file_header[] = _l('hr_create_job_position_default');

?>

<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div id ="dowload_file_sample">
							
							
						</div>

						<?php if(!isset($simulate)) { ?>
							<ul>
								<li>1. <?php echo _l('hr_import_excel_1'); ?></li>
								<li class="text-danger">2. <?php echo _l('hr_import_xlsx_required'); ?></li>
							</ul>
							<div class="table-responsive no-dt">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<?php
											$total_fields = 0;
											
											for($i=0;$i<count($file_header);$i++){
												if($i == 0 || $i == 1 ||$i == 22){
													?>
													<th class="bold"><span class="text-danger">*</span> <?php echo html_entity_decode($file_header[$i]) ?> </th>
													<?php 
												} else {
													?>
													<th class="bold"><?php echo html_entity_decode($file_header[$i]) ?> </th>
													
													<?php

												} 
												$total_fields++;
											}

											?>

										</tr>
									</thead>
									<tbody>
										<?php for($i = 0; $i<1;$i++){
											echo '<tr>';
											for($x = 0; $x<count($file_header);$x++){
												echo '<td>- </td>';
											}
											echo '</tr>';
										}
										?>
									</tbody>
								</table>
							</div>
							<hr>

						<?php } ?>
						
						<div class="row">
							<div class="col-md-4">
								<?php echo form_open_multipart(admin_url('hrm/import_job_p_excel'),array('id'=>'import_form')) ;?>
								<?php echo form_hidden('leads_import','true'); ?>
								<?php echo render_input('file_csv','choose_excel_file','','file'); ?> 

								<div class="form-group">
									<button id="uploadfile" type="button" class="btn btn-info import" onclick="return uploadfilecsv();" ><?php echo _l('import'); ?></button>
								</div>
								<?php echo form_close(); ?>
							</div>
							<div class="col-md-8">
								<div class="form-group" id="file_upload_response">
									
								</div>
								
							</div>
						</div>
						
					</div>
				</div>
			</div>

			<!-- box loading -->
			<div id="box-loading"></div>

		</div>
	</div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url('assets/plugins/jquery-validation/additional-methods.min.js'); ?>"></script>
<?php require('modules/hr_profile/assets/js/job_position/job/import_excel_js.php'); ?>
</body>
</html>

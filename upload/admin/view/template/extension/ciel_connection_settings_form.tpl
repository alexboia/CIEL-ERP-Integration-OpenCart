<script type="text/javascript">
	window['myc_hasConnectionInfo'] = <?php echo $has_connection ? 'true' : 'false'; ?>;
</script>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_connection_settings_form_heading; ?></h3>
	</div>
	<div class="panel-body">
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_endpoint_url"><?php echo $lbl_connection_endpoint_url_field; ?>:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="text" 
					name="myc_connection_endpoint_url" 
					id="myc_connection_endpoint_url" 
					value="<?php echo $connection_endpoint_url; ?>" 
					placeholder="<?php echo $txt_placeholder_connection_endpoint_url_field; ?>" 
				/>
		</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_username"><?php echo $lbl_connection_username_field; ?>:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="text" 
					name="myc_connection_username" 
					id="myc_connection_username" 
					value="<?php echo $connection_username; ?>" 
					placeholder="<?php echo $txt_placeholder_connection_username_field; ?>" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_password"><?php echo $lbl_connection_password_field; ?>:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="password" 
					name="myc_connection_password" 
					id="myc_connection_password" 
					placeholder="<?php echo $txt_placeholder_connection_password_field; ?>" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_society_code"><?php echo $lbl_connection_society_code_field; ?>:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="text" 
					name="myc_connection_society_code" 
					id="myc_connection_society_code" 
					value="<?php echo $connection_society_code; ?>" 
					placeholder="<?php echo $txt_placeholder_connection_society_code_field; ?>" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_timeout_seconds"><?php echo $lbl_connection_timeout_seconds_field; ?>:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="text" 
					name="myc_connection_timeout_seconds" 
					id="myc_connection_timeout_seconds" 
					value="<?php echo $connection_timeout_seconds; ?>" 
					placeholder="<?php echo $txt_placeholder_connection_timeout_seconds_field; ?>" 
				/>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2"></div>
			<div class="col-sm-10">
				<button id="myc_test_ciel_erp_connection" 
					type="button" 
					data-test-connection-url="<?php echo $url_test_connection_action; ?>"
					class="btn btn-info">
					<?php echo $txt_test_connection_action; ?>
				</button>
			</div>
		</div>
	</div>
</div>
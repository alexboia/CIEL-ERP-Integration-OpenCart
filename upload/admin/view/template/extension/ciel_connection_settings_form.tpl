<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_connection_settings_form_heading; ?></h3>
	</div>
	<div class="panel-body">
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_endpoint_url">URL serviciu web:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="text" 
					name="myc_connection_endpoint_url" 
					id="myc_connection_endpoint_url" 
					value="<?php echo $connection_endpoint_url; ?>" 
					placeholder="URL serviciu web CIEL Server" 
				/>
		</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_username">Nume utilizator:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="text" 
					name="myc_connection_username" 
					id="myc_connection_username" 
					value="<?php echo $connection_username; ?>" 
					placeholder="Nume utilizator serviciu web CIEL Server" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_password">Parola:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="password" 
					name="myc_connection_password" 
					id="myc_connection_password" 
					placeholder="Parola serviciu web CIEL Server" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_connection_society_code">Cod societate:</label>
			<div class="col-sm-10">
				<input class="form-control" 
					type="text" 
					name="myc_connection_society_code" 
					id="myc_connection_society_code" 
					value="<?php echo $connection_society_code; ?>" 
					placeholder="Cod societate" 
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
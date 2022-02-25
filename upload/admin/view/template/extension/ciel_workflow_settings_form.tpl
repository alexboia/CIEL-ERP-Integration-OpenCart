<div class="panel panel-default" 
	id="myc_workflow_settings_container"
	style="<?php echo !$has_connection ? 'display:none;' : ''; ?>">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_workflow_settings_form_heading; ?></h3>
	</div>

	<div class="panel-body">
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_in_stock_status_id">Status stoc pentru produse in stoc:</label>
			<div class="col-sm-10">
				<select name="myc_wf_in_stock_status_id" 
					id="myc_wf_in_stock_status_id"
					class="form-control">
					<?php foreach ($stock_statuses as $id => $name): ?>
						<option value="<?php echo $id; ?>" <?php echo ($id == $wf_in_stock_status_id ? 'selected="selected"' : ''); ?>><?php echo $name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_out_of_stock_status_id">Status stoc pentru produse epuizate din stoc:</label>
			<div class="col-sm-10">
				<select name="myc_wf_out_of_stock_status_id" 
					id="myc_wf_out_of_stock_status_id"
					class="form-control">
					<?php foreach ($stock_statuses as $id => $name): ?>
						<option value="<?php echo $id; ?>" <?php echo ($id == $wf_out_of_stock_status_id ? 'selected="selected"' : ''); ?>><?php echo $name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
</div>
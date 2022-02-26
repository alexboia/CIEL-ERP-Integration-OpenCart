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

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_new_tax_rate_customer_group_id">Grup clienti pentru taxele nou-importate din CIEL:</label>
			<div class="col-sm-10">
				<select name="myc_wf_new_tax_rate_customer_group_id" 
					id="myc_wf_new_tax_rate_customer_group_id"
					class="form-control">
					<?php foreach ($customer_grouops as $cg_id => $cg_name): ?>
						<option value="<?php echo $cg_id; ?>" <?php echo ($cg_id == $wf_new_tax_rate_customer_group_id ? 'selected="selected"' : ''); ?>><?php echo $cg_name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_new_tax_rate_geo_zone_id">Zona geografica pentru taxele nou-importate din CIEL:</label>
			<div class="col-sm-10">
				<select name="myc_wf_new_tax_rate_geo_zone_id" 
					id="myc_wf_new_tax_rate_geo_zone_id"
					class="form-control">
					<?php foreach ($geo_zones as $gz_id => $gz_name): ?>
						<option value="<?php echo $gz_id; ?>" <?php echo ($gz_id == $wf_new_tax_rate_geo_zone_id ? 'selected="selected"' : ''); ?>><?php echo $gz_name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_new_product_weight_class_id">Clasa de greutate pentru produsele nou-importate din CIEL:</label>
			<div class="col-sm-10">
				<select name="myc_wf_new_product_weight_class_id" 
					id="myc_wf_new_product_weight_class_id"
					class="form-control">
					<?php foreach ($weight_classes as $wc_id => $wc_name): ?>
						<option value="<?php echo $wc_id; ?>" <?php echo ($wc_id == $wf_new_product_weight_class_id ? 'selected="selected"' : ''); ?>><?php echo $wc_name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_new_product_length_class_id">Clasa de lungime pentru produsele nou-importate din CIEL:</label>
			<div class="col-sm-10">
				<select name="myc_wf_new_product_length_class_id" 
					id="myc_wf_new_product_length_class_id"
					class="form-control">
					<?php foreach ($length_classes as $lc_id => $lc_name): ?>
						<option value="<?php echo $lc_id; ?>" <?php echo ($lc_id == $wf_new_product_length_class_id ? 'selected="selected"' : ''); ?>><?php echo $lc_name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
</div>
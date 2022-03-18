<script type="text/javascript">
	window['myc_customer_custom_fields'] = eval(<?php echo json_encode($customer_custom_fields, JSON_FORCE_OBJECT); ?>);
</script>

<div class="panel panel-default" 
	id="myc_workflow_settings_container"
	style="<?php echo !$has_connection ? 'display:none;' : ''; ?>">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_workflow_settings_form_heading; ?></h3>
	</div>

	<div class="panel-body">
		<!-- Stock status bindings -->
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

		<!-- Billing information bindings -->

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_pf_customer_group_id">Grupul de clienti pentru persoane fizice:</label>
			<div class="col-sm-10">
				<select name="myc_wf_pf_customer_group_id" 
					id="myc_wf_pf_customer_group_id"
					class="form-control">
					<option value="0">Niciunul</option>
					<?php foreach ($customer_groups as $cg_id => $cg_name): ?>
						<option value="<?php echo $cg_id; ?>" <?php echo $cg_id == $wf_pf_customer_group_id ? 'selected="selected"' : ''; ?>><?php echo $cg_name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_pj_customer_group_id">Grupul de clienti pentru persoane juridice:</label>
			<div class="col-sm-10">
				<select name="myc_wf_pj_customer_group_id" 
					id="myc_wf_pj_customer_group_id"
					class="form-control">
					<option value="0">Niciunul</option>
					<?php foreach ($customer_groups as $cg_id => $cg_name): ?>
						<option value="<?php echo $cg_id; ?>" <?php echo $cg_id == $wf_pj_customer_group_id ? 'selected="selected"' : ''; ?>><?php echo $cg_name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_vat_code_custom_field_id">Campul custom folosit pentru CUI:</label>
			<div class="col-sm-10">
				<select name="myc_wf_vat_code_custom_field_id" 
					id="myc_wf_vat_code_custom_field_id"
					class="form-control">
					<option value="0">Niciunul</option>
					<?php foreach ($customer_custom_fields as $cf_id => $cf_info): ?>
						<option value="<?php echo $cf_id; ?>" <?php echo $cf_id == $wf_vat_code_custom_field_id ? 'selected="selected"' : ''; ?>><?php echo $cf_info['name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_reg_com_number_custom_field_id">Campul custom folosit pentru nr. reg. com.:</label>
			<div class="col-sm-10">
				<select name="myc_wf_reg_com_number_custom_field_id" 
					id="myc_wf_reg_com_number_custom_field_id"
					class="form-control">
					<option value="0">Niciunul</option>
					<?php foreach ($customer_custom_fields as $cf_id => $cf_info): ?>
						<option value="<?php echo $cf_id; ?>" <?php echo $cf_id == $wf_reg_com_number_custom_field_id ? 'selected="selected"' : ''; ?>><?php echo $cf_info['name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_bank_account_custom_field_id">Campul custom folosit pentru contul bancar:</label>
			<div class="col-sm-10">
				<select name="myc_wf_reg_bank_account_custom_field_id" 
					id="myc_wf_reg_bank_account_custom_field_id"
					class="form-control">
					<option value="0">Niciunul</option>
					<?php foreach ($customer_custom_fields as $cf_id => $cf_info): ?>
						<option value="<?php echo $cf_id; ?>" <?php echo $cf_id == $wf_bank_account_custom_field_id ? 'selected="selected"' : ''; ?>><?php echo $cf_info['name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_bank_name_custom_field_id">Campul custom folosit pentru denumirea bancii:</label>
			<div class="col-sm-10">
				<select name="myc_wf_reg_bank_name_custom_field_id" 
					id="myc_wf_reg_bank_name_custom_field_id"
					class="form-control">
					<option value="0">Niciunul</option>
					<?php foreach ($customer_custom_fields as $cf_id => $cf_info): ?>
						<option value="<?php echo $cf_id; ?>" <?php echo $cf_id == $wf_bank_name_custom_field_id ? 'selected="selected"' : ''; ?>><?php echo $cf_info['name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<!-- Other customer-related bindings -->

		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_wf_new_tax_rate_customer_group_id">Grup clienti pentru taxele nou-importate din CIEL:</label>
			<div class="col-sm-10">
				<select name="myc_wf_new_tax_rate_customer_group_id" 
					id="myc_wf_new_tax_rate_customer_group_id"
					class="form-control">
					<?php foreach ($customer_groups as $cg_id => $cg_name): ?>
						<option value="<?php echo $cg_id; ?>" <?php echo ($cg_id == $wf_new_tax_rate_customer_group_id ? 'selected="selected"' : ''); ?>><?php echo $cg_name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<!-- CIEL-imported products bindings -->
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
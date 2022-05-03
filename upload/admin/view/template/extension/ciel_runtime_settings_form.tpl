<style type="text/css">
	.myc_order_status_option {
		margin-right: 5px;
		margin-right: 2px;
	}
</style>
<div class="panel panel-default" 
	id="myc_binding_runtime_settings_container"
	style="<?php echo !$has_connection ? 'display:none;' : ''; ?>">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_runtime_settings_form_heading; ?></h3>
	</div>
	<div class="panel-body">
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_warehouse"><?php echo $lbl_runtime_warehouse_field; ?>:</label>
			<div class="col-sm-10">
				<select class="form-control" 
					name="myc_runtime_warehouse" 
					id="myc_runtime_warehouse" 
					placeholder="<?php echo $txt_placeholder_runtime_warehouse_field; ?>">

					<?php if (!empty($data_warehouses)): ?>
						<?php foreach ($data_warehouses as $id => $w): ?>
							<option value="<?php echo $id; ?>" 
								data-type="<?php echo $w['type']; ?>"
								data-code="<?php echo $w['code']; ?>"
								data-supports-sale-order="<?php echo $w['properties']->supportsSaleOder ? 'true' : 'false'; ?>" 
								data-supports-sale-invoice="<?php echo $w['properties']->supportsSaleInvoice ? 'true' : 'false'; ?>" 
								<?php echo $id == $runtime_warehouse ? 'selected="selected"' : '' ?>>
									<?php echo $w['displayLabel']; ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>

				</select>

				<input type="hidden" 
					id="myc_runtime_warehouse_code" 
					name="myc_runtime_warehouse_code" 
					value="<?php echo $runtime_warehouse_code; ?>" 
				/>
				<input type="hidden" 
					id="myc_runtime_warehouse_type" 
					name="myc_runtime_warehouse_type" 
					value="<?php echo $runtime_warehouse_type; ?>" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_doctype"><?php echo $lbl_runtime_issue_doctype_field; ?>:</label>
			<div class="col-sm-10">
				<select class="form-control" 
					name="myc_runtime_issue_doctype" 
					id="myc_runtime_issue_doctype" 
					placeholder="<?php echo $txt_placeholder_runtime_issue_doctype_field; ?>">
					<?php foreach ($data_available_document_types as $id => $label): ?>
						<option value="<?php echo $id; ?>" <?php echo $id == $runtime_issue_doctype ? ' selected="selected"' : '' ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_auto_order_status"><?php echo $lbl_runtime_issue_auto_order_status_field; ?>:</label>
			<div class="col-sm-10">
				<?php foreach ($data_available_order_statuses as $id => $label): ?>
					<input type="checkbox" 
						name="myc_runtime_issue_auto_order_status[]" 
						id="myc_runtime_issue_auto_order_status-<?php echo $id; ?>"
						value="<?php echo $id; ?>" 
						<?php echo in_array($id, $runtime_issue_auto_order_status) ? 'checked="checked"' : '' ?> /> <label for="myc_runtime_issue_auto_order_status-<?php echo $id; ?>" class="myc_order_status_option"><?php echo $label; ?></label>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="myc_runtime_remove_auto_order_status"><?php echo $lbl_runtime_remove_auto_order_status_field; ?>:</label>
			<div class="col-sm-10">
				<?php foreach ($data_available_order_statuses as $id => $label): ?>
					<input type="checkbox" 
						name="myc_runtime_remove_auto_order_status[]" 
						id="myc_runtime_remove_auto_order_status-<?php echo $id; ?>"
						value="<?php echo $id; ?>" 
						<?php echo in_array($id, $runtime_remove_auto_order_status) ? 'checked="checked"' : '' ?> /> <label for="myc_runtime_remove_auto_order_status-<?php echo $id; ?>" class="myc_order_status_option"><?php echo $label; ?></label>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_doc_status"><?php echo $lbl_runtime_issue_doc_status_field; ?>:</label>
			<div class="col-sm-10">
				<select class="form-control" 
					name="myc_runtime_issue_doc_status" 
					id="myc_runtime_issue_doc_status" 
					placeholder="<?php echo $txt_placeholder_runtime_issue_doc_status_field; ?>">
					<?php foreach ($data_available_document_status_types as $id => $label): ?>
						<option value="<?php echo $id; ?>" 
							<?php echo $runtime_issue_doc_status == $id ? 'selected="selected"' : '' ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_doc_due_days"><?php echo $lbl_runtime_issue_doc_due_days_field; ?>:</label>
			<div class="col-sm-10">
				<input 
					type="text"
					class="form-control" 
					name="myc_runtime_issue_doc_due_days" 
					id="myc_runtime_issue_doc_due_days" 
					placeholder="<?php echo $txt_placeholder_runtime_issue_doc_due_days_field; ?>" 
					value="<?php echo $runtime_issue_doc_due_days; ?>"
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_use_company_billing_fields"><?php echo $lbl_runtime_use_company_billing_fields_field; ?>:</label>
			<div class="col-sm-10">
				<div class="checkbox">
					<label>
						<input type="checkbox" 
							id="myc_runtime_use_company_billing_fields"
							name="myc_runtime_use_company_billing_fields"
							value="1"
							<?php echo $runtime_use_company_billing_fields ? ' checked="checked"' : ''; ?>
						/>
					</label>
				</div>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_shipping_vat_quota_name"><?php echo $lbl_runtime_shipping_vat_quota_field; ?></label>
			<div class="col-sm-10">
				<select 
					type="text"
					class="form-control" 
					name="myc_runtime_shipping_vat_quota_name" 
					id="myc_runtime_shipping_vat_quota_name" 
					placeholder="<?php echo $txt_placeholder_runtime_shipping_vat_quota_field; ?>">
					<?php foreach ($data_vat_quotas as $name => $value): ?>
						<option value="<?php echo $name ?>" 
							data-quota-value="<?php echo $value ?>" 
							<?php echo $runtime_shipping_vat_quota_name == $name ? ' selected="selected"' : ''; ?>>
								<?php echo $name ?>
						</option>
					<?php endforeach; ?>
				</select>

				<input type="hidden" 
					id="myc_runtime_shipping_vat_quota_value" 
					name="myc_runtime_shipping_vat_quota_value" 
					value="<?php echo $runtime_shipping_vat_quota_value; ?>" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_stock_update_mode"><?php echo $lbl_runtime_stock_update_mode_field; ?>:</label>
			<div class="col-sm-10">
				<select 
					type="text"
					class="form-control" 
					name="myc_runtime_stock_update_mode" 
					id="myc_runtime_stock_update_mode">
					<?php foreach ($data_available_stock_update_modes as $id => $label): ?>
						<option value="<?php echo $id; ?>" <?php echo $id == $runtime_stock_update_mode ? ' selected="selected"' : '' ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
</div>
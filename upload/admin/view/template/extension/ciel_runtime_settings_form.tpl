<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_runtime_settings_form_heading; ?></h3>
	</div>
	<div class="panel-body">
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_warehouse">Depozit:</label>
			<div class="col-sm-10">
				<select class="form-control" 
					name="myc_runtime_warehouse" 
					id="myc_runtime_warehouse" 
					placeholder="Depozit">
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_doctype">Ce tip de document se emite atunci când este plasată o comanda:</label>
			<div class="col-sm-10">
				<select class="form-control" 
					name="myc_runtime_issue_doctype" 
					id="myc_runtime_issue_doctype" 
					placeholder="Tip document emis">
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_auto_order_status">Emite documentul pentru statusurile de comanda:</label>
			<div class="col-sm-10">

			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_remove_auto_order_status">Sterge documentul pentru statusurile de comanda:</label>
			<div class="col-sm-10">

			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_doc_status">Emite documentul cu statusul selectat:</label>
			<div class="col-sm-10">
				<select class="form-control" 
					name="myc_runtime_issue_doc_status" 
					id="myc_runtime_issue_doc_status" 
					placeholder="Tip document emis">
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_issue_doc_due_days">Scadenta documentului:</label>
			<div class="col-sm-10">
				<input 
					type="text"
					class="form-control" 
					name="myc_runtime_issue_doc_due_days" 
					id="myc_runtime_issue_doc_due_days" 
					placeholder="Scadenta documentului, in zile" 
				/>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_use_company_billing_fields">Se folosesc câmpuri suplimentare pentru facturare pe persoană juridică:</label>
			<div class="col-sm-10">
				<div class="checkbox">
					<label>
						<input type="checkbox" 
							id="myc_runtime_use_company_billing_fields"
							name="myc_runtime_use_company_billing_fields"
							value="yes"
						/>
					</label>
				</div>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_shipping_vat_quota">Cota TVA transport:</label>
			<div class="col-sm-10">
				<select 
					type="text"
					class="form-control" 
					name="myc_runtime_shipping_vat_quota" 
					id="myc_runtime_shipping_vat_quota" 
					placeholder="Cota TVA folosita pentru transport">
				</select>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="myc_runtime_stock_update_mode">Cum se actualizeaza stocurile de produse:</label>
			<div class="col-sm-10">
				<select 
					type="text"
					class="form-control" 
					name="myc_runtime_stock_update_mode" 
					id="myc_runtime_stock_update_mode">
				</select>
			</div>
		</div>
	</div>
</div>
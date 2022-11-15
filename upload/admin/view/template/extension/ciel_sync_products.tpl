<?php echo $html_header; ?>
<?php echo $html_column_left; ?>

<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<a href="<?php echo $url_cancel_action; ?>" 
					data-toggle="tooltip" 
					title="<?php echo $txt_cancel_action; ?>" 
					class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $ciel_sync_products_title; ?></h1>
			<?php echo $html_breadcrumbs; ?>
		</div>
	</div>

	<div class="container-fluid">
		<div class="alert alert-info" role="alert">
			<span class="myc-tooltip" data-toggle="tooltip" title="<?php echo $ciel_sync_products_explanation; ?>"><?php echo $ciel_sync_products_info; ?></span>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-info"></i> <?php echo $ciel_sync_products_title; ?></h3>
			</div>
			<div class="panel-body">
				<?php if ($is_bound): ?>
					<div id="myc_operation_status_message" 
						style="display: none;"></div>

					<div class="table-responsive">
						<table id="myc-sync-info-stats" 
							class="table table-striped table-bordered table-hover" 
							style="display: none;">
							<thead>
								<th>
									<span class="myc-tooltip" 
										data-toggle="tooltip" 
										title="<?php echo $ciel_sync_products_eligible_count_explanation; ?>"><?php echo $ciel_sync_products_eligible_count_header; ?></span>
								</th>
								<th>
									<?php echo $ciel_sync_products_updated_count_header; ?>
								</th>
							</thead>
							<tbody>
								<tr>
									<td><span id="myc-total-eligible"></span></td>
									<td><span id="myc-total-updated"></span></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div id="myc-sync-info-actions" class="sync-info-actions">
						<select id="myc-ciel-product-sync-mode" 
							name="ciel_product_sync_mode" 
							class="form-control myc-ciel-product-sync-mode">
							<?php foreach ($product_sync_modes as $id => $label): ?>
								<option value="<?php echo $id ?>"><?php echo $label; ?></option>
							<?php endforeach; ?>
						</select>

						<button id="myc-sync-start" 
							type="button" 
							class="btn btn-primary" 
							data-action-url="<?php echo $ciel_update_btn_action; ?>"><?php echo $ciel_update_btn_text; ?></button>
					</div>
				<?php else: ?>
					<div class="alert alert-danger" role="alert">
						<?php echo $err_ciel_sync_products_store_not_bound; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php echo $html_loading_indicator; ?>

	<script type="text/javascript">
		window['ciel_sync_products_success_msg'] = '<?php echo $ciel_sync_products_success_msg; ?>';
		window['ciel_sync_products_error_msg'] = '<?php echo $ciel_sync_products_error_msg; ?>';
	</script>
</div>

<?php echo $html_footer; ?>
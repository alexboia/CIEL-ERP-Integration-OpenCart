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
				<button id="myc-sync-start" type="button" class="btn btn-primary" data-action-url="<?php echo $ciel_update_btn_action; ?>"><?php echo $ciel_update_btn_text; ?></button>
			</div>
		<?php else: ?>
			<div class="alert alert-danger" role="alert">
				<?php echo $err_ciel_sync_products_store_not_bound; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php echo $html_loading_indicator; ?>
</div>

<?php echo $html_footer; ?>
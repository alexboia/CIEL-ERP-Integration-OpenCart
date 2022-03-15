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
			<h1><?php echo $ciel_import_oc_romania_title; ?></h1>
			<?php echo $html_breadcrumbs; ?>
		</div>
	</div>

	<div class="container-fluid">
		<div class="alert alert-info" role="alert">
			<span><?php echo $ciel_import_oc_romania_info; ?></span>
		</div>

		<?php if ($is_bound): ?>
			<div id="myc_operation_status_message" 
				style="display: none;"></div>

			<form id="myc-download-notFoundForm" action="<?php echo $ciel_download_not_found_action; ?>" method="POST">
				<input type="hidden" 
					id="myc-notFound-ids"
					name="not_found_ids" 
					value="" 
				/>

				<div class="table-responsive">
					<table id="myc-migrate-info-stats" 
						class="table table-striped table-bordered table-hover" 
						style="display: none;">
						<thead>
							<th>
								<?php echo $ciel_migrate_products_eligible_count_header; ?>
							</th>
							<th>
								<?php echo $ciel_migrate_products_updated_count_header; ?>
							</th>
							<th>
								<?php echo $ciel_migrate_products_not_found_count_header; ?>
							</th>
						</thead>
						<tbody>
							<tr>
								<td><span id="myc-total-eligible"></span></td>
								<td><span id="myc-total-updated"></span></td>
								<td><span id="myc-total-notFound"></span></td>
							</tr>
						</tbody>
					</table>
				</div>

				<div id="myc-migrate-info-actions" class="migrate-info-actions">
					<button id="myc-migrate-start" type="button" class="btn btn-primary" data-action-url="<?php echo $ciel_migrate_btn_action; ?>"><?php echo $ciel_migrate_btn_text; ?></button>
					<button id="myc-download-notFound" type="submit" class="btn" style="display: none;"><?php echo $ciel_download_not_found_btn_text; ?></button>
				</div>
			</form>
		<?php else: ?>
			<div class="alert alert-danger" role="alert">
				<?php echo $ciel_err_migrate_products_store_not_bound; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php echo $html_loading_indicator; ?>
</div>

<?php echo $html_footer; ?>
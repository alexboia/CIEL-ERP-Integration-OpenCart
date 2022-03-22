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
			<h1><?php echo $ciel_import_new_products_title; ?></h1>
			<?php echo $html_breadcrumbs; ?>
		</div>
	</div>

	<div class="container-fluid">
		<div class="alert alert-info" role="alert">
			<span><?php echo $ciel_import_new_products_info; ?></span>
		</div>

		<?php if ($is_bound): ?>
			<div id="myc_operation_status_message" 
				style="display: none;"></div>

			<form id="myc-importNewProducts-form" action="<?php echo $ciel_import_new_products_action; ?>" method="POST">
				<?php if ($new_remote_products !== null): ?>
					<div class="table-responsive">
						<table id="myc-potential-new-products" 
							class="table table-striped table-bordered table-hover" >
							<thead>
								<th>
									<input type="checkbox"
										name="import_remote_ids_all" 
										id="myc_import_remote_ids_all" 
										value="1" 
										checked="checked" 
									/>
								</th>
								<th>ID</th>
								<th>Code</th>
								<th>CIEL ERP Name</th>
								<th>CIEL ERP Category</th>
							</thead>
							<tbody>
								<?php foreach ($new_remote_products as $p): ?>
									<tr id="myc_remote_product_row-<?php echo $p['id']; ?>">
										<td>
											<input type="checkbox"
												name="import_remote_ids[]" 
												class="myc_import_remote_ids"
												id="myc_import_remote_ids-<?php echo $p['id']; ?>" 
												value="<?php echo $p['id']; ?>" 
												checked="checked" 
											/>
										</td>
										<td><?php echo $p['id']; ?></td>
										<td><?php echo $p['code']; ?></td>
										<td><?php echo $p['name']; ?></td>
										<td><?php echo !empty($p['category']) 
											? $p['category'] 
											: '-'; ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div id="myc-import-products-actions" class="import-products-actions">
						<button id="myc-import-products-start" type="button" class="btn btn-primary"><?php echo $ciel_import_new_products_text; ?></button>
					</div>

					<script type="text/javascript">
						window['myc_new_remote_products_count'] = <?php echo count($new_remote_products); ?>;
					</script>
				<?php else: ?>
					<div class="alert alert-danger" role="alert">
						<?php echo $ciel_err_import_new_products_error_computing_products; ?>
					</div>
				<?php endif; ?>
			</form>
		<?php else: ?>
			<div class="alert alert-danger" role="alert">
				<?php echo $ciel_err_import_new_products_store_not_bound; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php echo $html_footer; ?>
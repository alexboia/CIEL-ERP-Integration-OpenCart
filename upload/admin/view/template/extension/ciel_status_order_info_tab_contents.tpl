<style type="text/css">
	.myc-document-status-warning-item {
		height: auto;
		margin-bottom: 10px;
		color: #856404;
		background-color: #fff3cd;
		border: 1px solid #ffeeba;
		padding: 12px;
	}

	.ciel-erp-order-document-container table tr th,
	.ciel-erp-order-document-container table tr td {
		height: 34px;
		line-height: 34px;
		vertical-align: middle;
	}
</style>

<script type="text/javascript">
	window['myc_is_ciel_document_issued'] = <?php echo $is_ciel_document_issued ? 'true' : 'false'; ?>;
	window['myc_ciel_erp_check_document_issued_action'] = '<?php echo $ciel_erp_check_document_issued_action; ?>';
</script>

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-plug"></i> <?php echo $lbl_tab_order_label; ?></h3>
	</div>
	<div class="panel-body">
		<div class="ciel-erp-order-document-container">
			<div id="myc_order_operation_status_message" style="display: none;"></div>
			<?php if ($is_store_bound): ?>
				<?php if ($ciel_has_warning): ?>
					<?php if (!$ciel_document_issue_configured): ?>
						<div class="myc-document-status-warning-item" role="alert">
							<?php echo $msg_order_cant_issue_not_configured; ?>
						</div>
					<?php endif; ?>

					<?php if (!$ciel_document_prerequisite_status->areAllItemsConnected): ?>
						<div class="myc-document-status-warning-item" role="alert">
							<?php echo $msg_order_cant_issue_not_all_products_connected; ?>
						</div>
					<?php endif; ?>

					<?php if ($ciel_document_prerequisite_status->hasItemsWithBatchTracking 
						&& !$ciel_document_prerequisite_status->isBatchTrackingPossible): ?>
						<div class="myc-document-status-warning-item" role="alert">
							<?php echo $msg_order_cant_issue_batch_tracking_not_posssible; ?>
						</div>
					<?php endif; ?>

					<?php if ($ciel_document_prerequisite_status->hasItemsWithBatchTracking 
						&& !$ciel_document_prerequisite_status->isBatchTrackingSupported): ?>
						<div class="myc-document-status-warning-item" role="alert">
							<?php echo $msg_order_cant_issue_batch_tracking_not_available; ?>
						</div>
					<?php endif; ?>

					<?php if (!empty($ciel_disconnected_local_products)): ?>
						<h4 class="myc-item-intration-status-subsection"><?php echo $lbl_subsection_products_not_connected_title; ?></h4>
						<div id="myc-disconnected-local-products-table" class="table-responsive myc-disconnected-local-products-table">
							<table class="table table-striped table-bordered table-hover">		
								<thead>
									<th class="text-center">SKU</th>
									<th>Name</th>
									<th>Model</th>
								</thead>
								<tbody>
									<?php foreach ($ciel_disconnected_local_products as $dp): ?>
										<tr>
											<td class="text-center"><?php echo !empty($dp['product_sku']) 
												? $dp['product_sku'] 
												: 'N/A'; ?></td>
											<td><?php echo !empty($dp['product_name']) 
												? ('<a href="' . $dp['product_url'] . '" target="_blank">' . $dp['product_name'] . '</a>')
												: ('<i>' . sprintf($lbl_missing_product_placeholder, $dp['product_id']) . '</i>'); ?></td>
											<td><?php echo !empty($dp['product_model']) 
												? $dp['product_model'] 
												: 'N/A'; ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>		
					<?php endif; ?>
				<?php endif; ?>

				<h4 class="myc-item-intration-status-subsection"><?php echo $lbl_subsection_integration_status_title; ?></h4>
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-hover">
						<tr>
							<th scope="row" class="text-right" style="width: 30%;"><?php echo $lbl_order_ciel_erp_document_issued; ?></th>
							<td class="text-left" style="width: 70%;">
								<?php echo $is_ciel_document_issued 
									? $lbl_txt_yes 
									: $lbl_txt_no; ?>
							</td>
						</tr>
						<tr>
							<th scope="row" class="text-right" style="width: 30%;"><?php echo $lbl_order_ciel_erp_document_type; ?></th>
							<td class="text-left" style="width: 70%;">
								<?php echo $is_ciel_document_issued 
									? $ciel_remote_document_data['type_name'] 
									: '-'; ?>
							</td>
						</tr>
						<tr>
							<th scope="row" class="text-right"><?php echo $lbl_order_actions; ?></th>
							<td class="text-left">
								<?php if (!$is_ciel_document_issued): ?>
									<?php if ($ciel_document_issue_enabled): ?>
										<button id="myc_connect_issue_ciel_erp_document" type="button" class="btn btn-info" data-action-url="<?php echo $ciel_erp_issue_order_action; ?>">
											<?php echo $lbl_order_action_issue_document; ?>
										</button>
									<?php else: ?>
										<span><?php echo $msg_order_no_actions_available; ?></span>
									<?php endif; ?>
								<?php else: ?>
									<button id="myc_connect_remove_ciel_erp_document" type="button" class="btn btn-danger" data-action-url="<?php echo $ciel_erp_remove_order_action; ?>">
										<?php echo $lbl_order_action_remove_document; ?>
									</button>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</div>
			<?php else: ?>
				<div class="myc-alert myc-alert-danger" role="alert">
					<?php echo $msg_order_action_store_not_bound; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php echo $html_loading_indicator; ?>

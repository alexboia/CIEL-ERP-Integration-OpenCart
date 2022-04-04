<style type="text/css">
	.ciel-erp-form-status-container table tr th,
	.ciel-erp-form-status-container table tr td {
		height: 34px;
		line-height: 34px;
		vertical-align: middle;
	}
</style>
<div class="ciel-erp-form-status-container">
	<div id="myc_product_operation_status_message" style="display: none;"></div>
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover">
			<tbody>
				<tr>
					<th scope="row" class="text-right" style="width: 30%;"><?php echo $lbl_product_connected_to_ciel_erp; ?></th>
					<td class="text-left" style="width: 70%;">
						<?php echo $is_connected_to_ciel_erp ? 'da' : 'nu'; ?>
					</td>
				</tr>
				<tr>
					<th scope="row" class="text-right"><?php echo $lbl_product_ciel_erp_article_id; ?></th>
					<td class="text-left">
						<?php echo $is_connected_to_ciel_erp ? $remote_article_data['remote_id'] : '-'; ?>
					</td>
				</tr>
				<tr>
					<th scope="row" class="text-right"><?php echo $lbl_product_ciel_erp_vat_option_name; ?></th>
					<td class="text-left">
						<?php echo $is_connected_to_ciel_erp ? $remote_article_data['remote_price_vat_option_name'] : '-'; ?>
					</td>
				</tr>
				<tr>
					<th scope="row" class="text-right"><?php echo $lbl_product_ciel_erp_vat_quota_value; ?></th>
					<td class="text-left">
						<?php echo $is_connected_to_ciel_erp ? $remote_article_data['remote_price_vat_quota_value'] . '%' : '-'; ?>
					</td>
				</tr>
				<tr>
					<th scope="row" class="text-right"><?php echo $lbl_product_ciel_erp_batch_tracking_enabled; ?></th>
					<td class="text-left">
						<?php if ($is_connected_to_ciel_erp): ?>
							<?php echo $remote_article_data['remote_batch_tracking_enabled'] ? 'da' : 'nu'; ?>
						<?php else: ?>
							-
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row" class="text-right"><?php echo $lbl_product_actions; ?></th>
					<td class="text-left">
						<?php if ($is_connected_to_ciel_erp): ?>
							<button id="myc_sync_all_product_information" type="button" class="btn btn-info" data-action-url="<?php echo $ciel_erp_update_all_action_url; ?>">
								<?php echo $lbl_product_action_update_full; ?>
							</button>
							<button id="myc_only_stock_product_information" type="button" class="btn" data-action-url="<?php echo $ciel_erp_update_stocks_action_url; ?>">
								<?php echo $lbl_product_action_update_stocks; ?>
							</button>
						<?php else: ?>
							<button id="myc_connect_product_to_ciel_erp" type="button" class="btn btn-info" data-action-url="<?php echo $ciel_erp_connect_action_url; ?>">
								<?php echo $lbl_product_action_connect; ?>
							</button>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php echo $html_loading_indicator; ?>
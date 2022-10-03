<style type="text/css">
	.ciel-erp-form-status-container table tr th,
	.ciel-erp-form-status-container table tr td {
		height: 34px;
		line-height: 34px;
		vertical-align: middle;
	}
</style>
<div class="ciel-erp-form-status-container">
	<div id="myc_customer_operation_status_message" style="display: none;"></div>
	<?php if ($is_store_bound): ?>
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-hover">
				<tbody>
					<tr>
						<th scope="row" class="text-right" style="width: 30%;"><?php echo $lbl_customer_connected_to_ciel_erp; ?></th>
						<td class="text-left" style="width: 70%;">
							<?php echo $is_connected_to_ciel_erp 
								? $lbl_txt_yes 
								: $lbl_txt_no; ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="text-right"><?php echo $lbl_customer_ciel_erp_partner_code; ?></th>
						<td class="text-left">
							<?php echo $is_connected_to_ciel_erp 
								? $remote_partner_data['remote_partner_code'] 
								: '-'; ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="text-right"><?php echo $lbl_customer_ciel_erp_partner_address_worksite_id; ?></th>
						<td class="text-left">
							<?php echo $is_connected_to_ciel_erp 
								? $remote_partner_data['remote_partner_addr_worksite_id'] 
								: '-'; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<div class="alert alert-danger" role="alert">
			<?php echo $msg_customer_action_store_not_bound; ?>
		</div>
	<?php endif; ?>
</div>
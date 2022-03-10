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
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover">
			<tbody>
				<tr>
					<th scope="row" class="text-right" style="width: 30%;">Conectat la CIEL ERP</th>
					<td class="text-left" style="width: 70%;">
						<?php echo $is_connected_to_ciel_erp ? 'da' : 'nu'; ?>
					</td>
				</tr>
				<tr>
					<th scope="row" class="text-right">Cod partner CIEL ERP</th>
					<td class="text-left">
						<?php echo $is_connected_to_ciel_erp 
							? $remote_partner_data['remote_partner_code'] 
							: '-'; ?>
					</td>
				</tr>
				<tr>
					<th scope="row" class="text-right">Id adresa CIEL ERP</th>
					<td class="text-left">
						<?php echo $is_connected_to_ciel_erp 
							? $remote_partner_data['remote_partner_addr_worksite_id'] 
							: '-'; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
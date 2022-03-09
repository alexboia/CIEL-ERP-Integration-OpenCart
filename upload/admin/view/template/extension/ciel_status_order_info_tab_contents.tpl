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

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><i class="fa fa-plug"></i> Integrare CIEL ERP</h3>
	</div>
	<div class="panel-body">
		<div class="ciel-erp-order-document-container">
			<div id="myc_order_operation_status_message" style="display: none;"></div>
			<?php if ($has_warning): ?>
				<?php if (!$ciel_document_prerequisite_status->areAllItemsConnected): ?>
					<div class="myc-document-status-warning-item" role="alert">
						Documentul nu poate fi emis, deoarece  nu toate articolele sunt conectate la CIEL ERP.
					</div>
				<?php endif; ?>

				<?php if ($ciel_document_prerequisite_status->hasItemsWithBatchTracking 
					&& !$ciel_document_prerequisite_status->isBatchTrackingPossible): ?>
					<div class="myc-document-status-warning-item" role="alert">
						Documentul nu poate fi emis, deoarece comanda contine articole cu urmarire pe loturi, dar depozitul selectat nu este de tip En-Gross.
					</div>
				<?php endif; ?>

				<?php if ($ciel_document_prerequisite_status->hasItemsWithBatchTracking 
					&& !$ciel_document_prerequisite_status->isBatchTrackingSupported): ?>
					<div class="myc-document-status-warning-item" role="alert">
						Documentul nu poate fi emis, deoarece comanda contine articole cu urmarire pe loturi, dar licenta de integrare pe care o detii nu include suport pentru emitere documente ce contin asemenea articole.
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-hover">
					<tr>
						<th scope="row" class="text-right" style="width: 30%;">Document emis in CIEL ERP</th>
						<td class="text-left" style="width: 70%;">
							<?php echo $is_ciel_document_issued ? 'da' : 'nu'; ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="text-right" style="width: 30%;">Tip document emis in CIEL ERP</th>
						<td class="text-left" style="width: 70%;">
							<?php echo $is_ciel_document_issued ? $ciel_remote_document_data['type_name'] : '-'; ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="text-right">Actiuni disponibile</th>
						<td class="text-left">
							<?php if (!$is_ciel_document_issued): ?>
								<?php if ($ciel_document_issue_enabled): ?>
									<button id="myc_connect_issue_ciel_erp_document" type="button" class="btn btn-info" data-action-url="<?php echo $ciel_erp_issue_order_action; ?>">
										Emite document in CIEL ERP
									</button>
								<?php else: ?>
									<span>Nicio actiune disponibila</span>
								<?php endif; ?>
							<?php else: ?>
								<button id="myc_connect_remove_ciel_erp_document" type="button" class="btn btn-danger" data-action-url="<?php echo $ciel_erp_remove_order_action; ?>">
									Sterge document din CIEL ERP
								</button>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<?php echo $html_loading_indicator; ?>
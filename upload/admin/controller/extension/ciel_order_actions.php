<?php
use Ciel\Api\Integration\Orders\BatchDischargePolicy;
use Ciel\Api\Integration\Orders\DischargePolicies\NoBatchDischargePolicy;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithLogging;

class ControllerExtensionCielOrderActions extends CielController {
	use WithCielIntegration;
	use WithLogging;

	public function issueDocument() {
		$response = $this->_createAjaxResponse();

		if ($this->_isHttpPost() && $this->_issueDocumentEnabled()) {
			$orderId = $this->_getOrderIdFromUrl();
			if (!empty($orderId)) {
				if ($this->_shouldAddDocumentIssuedOrderNoteOnOrderAction()) {
					$result = $this->_issueDocumentForOrderWithOrderNote($orderId);
				} else {
					$result = $this->_issueDocumentForOrderWithoutOrderNote($orderId);
				}

				$response->wasIssued = $result->wasIssued;
				$response->success = $result->success;
			}
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _getOrderIdFromUrl() {
		return isset($this->request->get['order_id'])
			? intval($this->request->get['order_id'])
			: 0;
	}

	private function _issueDocumentForOrderWithOrderNote($orderId) {
		$result = $this->_issueDocumentForOrderWithoutOrderNote($orderId);
		if ($result->wasIssued) {
			//TODO: add order note
		}

		return $result;
	}

	private function _issueDocumentForOrderWithoutOrderNote($orderId) {
		$result = new \stdClass();
		$result->wasIssued = false;
		$result->success = false;

		try {
			$result->wasIssued = $this->_processIssueDocumentForOrder($orderId);
			$result->success = true;
		} catch (Exception $exc) {
			$this->_logError($exc, 'Error issuing document for order');
		}

		return $result;
	}

	private function _processIssueDocumentForOrder($orderId) {
		$wasIssued = false;

		if (!$this->_isDocumentIssuedForOrder($orderId)) {
			$status = $this->_determineDocumentPrerequisiteStatus($orderId);
			if ($status->hasItemsWithBatchTracking()) {
				$this->_logDebug('Order has items with batch tracking. Batch tracking not supported, exiting...');
				return $wasIssued;
			} else {
				$this->_logDebug('Order does not have items with batch tracking. Proceeding...');
			}

			if ($status->canOrderItemsBeAddedToDocument()) {
				//Export partner data for this order
				$this->_tryAutoConnectOrderPartner($orderId);
				$this->_exportLocalOrderPartner($orderId);

				//Issue the document
				$this->_issueDocumentForOrder($orderId, 
					new NoBatchDischargePolicy());

				$this->_logDebug('The document has been successfully issued for order <' . $orderId . '>.');
				$wasIssued = true;
			} else {
				$this->_logDebug('The document cannot be issued as not all items are connected to CIEL ERP');
			}
		} else {
			$this->_logDebug('There already is a document issued for this order. Will not re-issue.');
		}

		if ($wasIssued && $this->_shouldSyncStocksOnIssueDocument()) {
			$this->_syncStocksForOrder($orderId);
		}

		return $wasIssued;
	}

	private function _isDocumentIssuedForOrder($orderId) {
		return $this->_getOrderIntegration()
			->isDocumentIssuedForOrder($orderId);
	}

	private function _determineDocumentPrerequisiteStatus($orderId) {
		return $this->_getOrderIntegration()
			->determineOrderDocumentPreRequisitesStatus($orderId);
	}

	private function _tryAutoConnectOrderPartner($orderId) {
		$this->_getPartnerIntegration()
			->tryAutoConnectOrderPartner($orderId);
	}

	private function _exportLocalOrderPartner($orderId) {
		$this->_getPartnerIntegration()
			->exportLocalOrderPartner($orderId);
	}

	private function _issueDocumentForOrder($orderId, BatchDischargePolicy $batchDischargePolicy) {
		$this->_getOrderIntegration()
			->issueDocumentForOrder($orderId, 
				$batchDischargePolicy);
	}

	private function _shouldSyncStocksOnIssueDocument() {
		return $this->_issueDocumentAsValid() 
			&& $this->_issueSaleInvoice();
	}

	private function _syncStocksForOrder($orderId) {
		try {
			$this->_processUpdateStocksForOrder($orderId);
		} catch (Exception $exc) {
			$this->_logError($exc);
		}
	}

	private function _processUpdateStocksForOrder($orderId) {
		$localArticleIds = $this->_getLocalArticleIds($orderId);
		if (!empty($localArticleIds)) {
			$this->_logDebug('Found <' .  count($localArticleIds). '> articles to update for order <' . $orderId . '>.');
			$this->_updateStockForArticles($localArticleIds);
		} else {
			$this->_logDebug('No articles found to update for order <' . $orderId . '>.');
		}
	}

	private function _getLocalArticleIds($orderId) {
		return $this->_getOrderIntegration()
			->getLocalArticleIdsForOrder($orderId);
	}

	private function _updateStockForArticles($productIds) {
		$this->_getArticleIntegration()
			->updateStockForArticles($productIds);
	}

	public function removeDocument() {
		$response = $this->_createAjaxResponse();

		if ($this->_isHttpPost()) {
			$orderId = $this->_getOrderIdFromUrl();
			if (!empty($orderId)) {
				if ($this->_shouldAddDocumentRemovedOrderNoteOnOrderAction()) {
					$result = $this->_removeDocumentForOrderWithOrderNote($orderId);
				} else {
					$result = $this->_removeDocumentForOrderWithoutOrderNote($orderId);
				}

				$response->wasRemoved = $result->wasRemoved;
				$response->success = $result->success;
			}
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _removeDocumentForOrderWithOrderNote($orderId) {
		return $this->_removeDocumentForOrderWithoutOrderNote($orderId);
	}

	private function _removeDocumentForOrderWithoutOrderNote($orderId) {
		$result = new \stdClass();
		$result->wasRemoved = false;
		$result->success = false;

		try {
			$result->wasRemoved = $this->_processRemoveDocumentForOrder($orderId);
			$result->success = true;
		} catch (Exception $exc) {
			$this->_logError($exc);
		}

		return $result;
	}

	private function _processRemoveDocumentForOrder($orderId) {
		$wasRemoved = false;

		if ($this->_isDocumentIssuedForOrder($orderId)) {
			$this->_removeDocumentDocumentForOrder($orderId);
			$this->_logDebug('The document has been successfully removed for order <' . $orderId . '>.');
			$wasRemoved = true;
		} else {
			$this->_logDebug('There is not document issued for this order. Nothing to remove.');
		}

		if ($wasRemoved && $this->_shouldSyncStocksOnRemoveDocument()) {
			$this->_syncStocksForOrder($orderId);
		}

		return $wasRemoved;
	}

	private function _removeDocumentDocumentForOrder($orderId) {
		$this->_getOrderIntegration()
			->removeDocumentForOrder($orderId);
	}

	private function _shouldSyncStocksOnRemoveDocument() {
		return $this->_issueDocumentAsValid() 
			&& $this->_issueSaleInvoice();
	}

	public function getRemoteDocumentLines() {

	}
}
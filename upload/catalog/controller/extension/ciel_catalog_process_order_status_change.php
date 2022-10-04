<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielOrderIntegration;

class ControllerExtensionCielCatalogProcessOrderStatusChange extends CielController {
	use WithCielOrderIntegration;

	public function index(&$route, &$args, &$output) {
		$this->_logDebug('Begin processing order history add...');

		if (!empty($args) && is_array($args) && count($args) >= 2) {
			$orderId = intval($args[0]);
			$orderStatusId = intval($args[1]);

			if ($orderId > 0 && $orderStatusId > 0) {
				$this->_processOrderHistoryAdd($orderId, 
					$orderStatusId);
			} else {
				$this->_logDebug('Order id or order status id was empty.');
			}
		} else {
			$this->_logDebug('Unsupported arguments provided: ' . print_r($args, true));
		}

		$this->_logDebug('Done processing order history add.');
		return $output;
	}	

	private function _processOrderHistoryAdd($orderId, $orderStatusId) {
		$this->_logDebug('Using order id = <' . $orderId . '> and order status id = <' . $orderStatusId . '>.');

		$this->_maybeAutoIssueDocumentOnOrderStatusChanged($orderId, 
			$orderStatusId);
		$this->_maybeAutoRemoveDocumentOnOrderStatusChanged($orderId, 
			$orderStatusId);
	}

	private function _maybeAutoIssueDocumentOnOrderStatusChanged($orderId, $orderStatusId) {
		try {
			$this->_processMaybeAutoIssueDocumentOnOrderStatusChanged($orderId, 
				$orderStatusId);
		} catch (Exception $exc) {
			$this->_logError($exc, 'Error processing document auto issue.');
		}
	}

	private function _processMaybeAutoIssueDocumentOnOrderStatusChanged($orderId, $orderStatusId) {
		$this->_logDebug('Order status changed. Attempting to process order document generation.');

		if ($this->_shouldAutoIssueDocumentForOrderStatus($orderStatusId)) {
			$this->_logDebug('Conditions are met for automatically generating order document.');

			if ($this->_shouldAddDocumentIssuedOrderNoteOnOrderAction()) {
				$result = $this->_issueDocumentForOrderWithOrderNote($orderId);
			} else {
				$result = $this->_issueDocumentForOrderWithoutOrderNote($orderId);
			}

			if ($result->wasIssued) {
				$this->_logDebug('Document was issued for order <' . $orderId . '>.');
			} else {
				$this->_logDebug('Document was not issued for order <' . $orderId . '>.');
			}
		} else {
			$this->_logDebug('Conditions are not met for automatically generating order document.');
		}
	}

	private function _shouldAutoIssueDocumentForOrderStatus($orderStatusId) {
		return $this->_issueDocumentEnabled() 
			&& in_array($orderStatusId, 
				$this->_getIssueDocumentWhen());
	}
	
	private function _getIssueDocumentWhen() {
		return $this->_getStoreBinding()
			->getIssueDocumentWhen();
	}

	private function _maybeAutoRemoveDocumentOnOrderStatusChanged($orderId, $orderStatusId) {
		try {
			$this->_processMaybeAutoRemoveDocumentOnOrderStatusChanged($orderId, 
				$orderStatusId);
		} catch (Exception $exc) {
			$this->_logError($exc, 'Error processing document auto removal.');
		}
	}

	private function _processMaybeAutoRemoveDocumentOnOrderStatusChanged($orderId, $orderStatusId) {
		$this->_logDebug('Order status changed. Attempting to process order document removal.');

		if ($this->_shouldAutoRemoveDocumentForOrderStatus($orderStatusId)) {
			$this->_logDebug('Conditions are met for automatically removing order document.');

			if ($this->_shouldAddDocumentRemovedOrderNoteOnOrderAction()) {
				$result = $this->_removeDocumentForOrderWithOrderNote($orderId);
			} else {
				$result = $this->_removeDocumentForOrderWithoutOrderNote($orderId);
			}

			if ($result->wasRemoved) {
				$this->_logDebug('Document was removed for order <' . $orderId . '>.');
			} else {
				$this->_logDebug('Document was not removed for order <' . $orderId . '>.');
			}
		} else {
			$this->_logDebug('Conditions are not met for automatically removing order document.');
		}
	}

	private function _shouldAutoRemoveDocumentForOrderStatus($orderStatusId) {
		return $this->_issueDocumentEnabled() 
			&& in_array($orderStatusId, 
				$this->_getCancelDocumentWhen());
	}

	private function _getCancelDocumentWhen() {
		return $this->_getStoreBinding()
			->getCancelDocumentWhen();
	}
}
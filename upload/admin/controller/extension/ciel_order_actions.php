<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielOrderIntegration;

class ControllerExtensionCielOrderActions extends CielController {
	use WithCielOrderIntegration;

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

	public function getRemoteDocumentLines() {
		$response = $this->_createAjaxResponse(array(
			'documentLines' => array()
		));

		if ($this->_isHttpGet()) {
			$orderId = $this->_getOrderIdFromUrl();
			if (!empty($orderId)) {
				try {	
					$response->documentLines = $this->_getRemoteDocumentLines($orderId);
					$response->success = true;
				} catch (Exception $exc) {
					$this->_logError($exc);
				}
			}
		}

		$this->_renderJsonToResponseOutput($response);
	}
}
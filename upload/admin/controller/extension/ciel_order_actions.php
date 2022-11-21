<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielOrderIntegration;

class ControllerExtensionCielOrderActions extends CielController {
	use WithCielOrderIntegration;

	public function __construct(\Registry $registry) {
		parent::__construct($registry);
		$this->_setTextDomain('extension/ciel_order_actions');
	}

	public function checkDocumentIssued() {
		$response = $this->_createEmptyCheckDocumentIssuedResponse();

		if ($this->_isHttpGet() && $this->_issueDocumentConfigured()) {
			$orderId = $this->_getOrderIdFromUrl();
			if (!empty($orderId)) {
				try {
					$response->isCielDocumentIssued = $this->_isDocumentIssuedForOrder($orderId);
					$response->issuedMessage = $this->_t('ciel_order_document_issue_success');
					$response->removedMessage = $this->_t('ciel_order_document_remove_success');
					$response->success = true;
				} catch (Exception $exc) {
					$this->_logError($exc);
				}
			}
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _createEmptyCheckDocumentIssuedResponse() {
		return $this->_createAjaxResponse(array(
			'isCielDocumentIssued' => null,
			'issuedMessage' => null,
			'removedMessage' => null
		));
	}

	public function issueDocument() {
		$response = $this->_createAjaxResponse();

		if ($this->_isHttpPost() && $this->_issueDocumentConfigured()) {
			$orderId = $this->_getOrderIdFromUrl();
			if (!empty($orderId)) {
				if ($this->_shouldAddDocumentIssuedOrderNoteOnOrderAction()) {
					$result = $this->_issueDocumentForOrderWithOrderNote($orderId);
				} else {
					$result = $this->_issueDocumentForOrderWithoutOrderNote($orderId);
				}

				$response->wasIssued = $result->wasIssued;
				$response->success = $result->success;

				if ($result->wasIssued) {
					$response->message = $result->success
						? $this->_t('ciel_order_document_issue_success')
						: $this->_t('ciel_order_document_issue_failed');
				}
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

				if ($result->wasRemoved) {
					$response->message = $result->success
						? $this->_t('ciel_order_document_remove_success')
						: $this->_t('ciel_order_document_remove_failed');
				}
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
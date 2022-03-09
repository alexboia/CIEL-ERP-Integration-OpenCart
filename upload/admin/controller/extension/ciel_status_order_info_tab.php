<?php

use Ciel\Api\Data\DocumentType;
use CielIntegration\CielController;
use CielIntegration\ContentsAppender;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\Integration\Admin\WithLookupDataProvider;

class ControllerExtensionCielStatusOrderInfoTab extends CielController {
	use WithCielIntegration;
	use WithLookupDataProvider;

	public function assets() {
		if ($this->_isOrderViewPage()) {
			$this->_includeLoadingIndicatorScript();
			$this->_includeOperationStatusScript();
			$this->_includeCommonScript();
			$this->_addHeaderScript('extension/ciel_order_actions.js');
		}
	}

	private function _isOrderViewPage() {
		return $this->_isRoute('sale/order/info');
	}

	public function index(&$route, &$data, &$output) {
		$orderId = $this->_getOrderIdFromUrl();
		if (empty($orderId)) {
			return $output;
		}

		$viewData = array();
		$isCielDocumentIssued = $this->_isDocumentIssuedForOrder($orderId);
		
		//Basic order data
		$viewData['is_ciel_document_issued'] = $isCielDocumentIssued;
		if ($isCielDocumentIssued) {
			$viewData['has_warning'] = false;
			$viewData['ciel_document_issue_enabled'] = false;
			$viewData['ciel_remote_document_data'] = $this
				->_getOrderRemoteDocumentData($orderId);			
		} else {
			$preRequisiteStatus = $this
				->_determineDocumentPrerequisiteStatus($orderId);
			$canOrderItemsBeAddedToDocument = $preRequisiteStatus
				->canOrderItemsBeAddedToDocument();
			
			$viewData['can_order_items_be_added_to_document'] = 
				$canOrderItemsBeAddedToDocument;
			$viewData['ciel_document_prerequisite_status'] = $preRequisiteStatus
				->asPlainObject();
			$viewData['ciel_document_issue_enabled'] = $this->_issueDocumentEnabled() 
				&& $canOrderItemsBeAddedToDocument;
			$viewData['has_warning'] = !$viewData['ciel_document_issue_enabled']
				|| !$viewData['can_order_items_be_added_to_document'];
		}

		//Html fragments
		$viewData['html_loading_indicator'] = $this->_renderLoadingIndicator();

		//Order actions data
		$orderActionsData = array(
			'order_id' => $orderId
		);

		$viewData['ciel_erp_issue_order_action'] = $this
			->_createRouteUrl('extension/ciel_order_actions/issueDocument', 
				$orderActionsData);
		$viewData['ciel_erp_remove_order_action'] = $this
			->_createRouteUrl('extension/ciel_order_actions/removeDocument', 
				$orderActionsData);
		$viewData['ciel_erp_view_order_document_lines_action'] = $this
			->_createRouteUrl('extension/ciel_order_actions/getRemoteDocumentLines', 
				$orderActionsData);

		//Add our contents to the view
		$viewContents = $this->_renderView('extension/ciel_status_order_info_tab_contents', 
			$viewData);

		$contentsAppender = new ContentsAppender('#content > div.container-fluid');
		$contentsAppender->addContent($viewContents);

		return $contentsAppender
			->rewrite($output);
	}

	private function _isDocumentIssuedForOrder($orderId) {
		return $this->_getOrderIntegration()
			->isDocumentIssuedForOrder($orderId);
	}

	private function _getOrderRemoteDocumentData($orderId) {
		$remoteDocumentData = $this->_getOrderIntegration()
			->lookupRemoteDocumentDataForOrder($orderId);

		return array_merge($remoteDocumentData, array(
			'type_name' => $this
				->_getRemoteDocumentTypeName($remoteDocumentData)
		));
	}

	private function _getRemoteDocumentTypeName($remoteDocumentData) {
		return $this->_getLookupDataProvider()
			->getDocumentTypeName($remoteDocumentData['type']);
	}

	private function _determineDocumentPrerequisiteStatus($orderId) {
		return $this->_getOrderIntegration()
			->determineOrderDocumentPreRequisitesStatus($orderId);
	}

	private function _getOrderIdFromUrl() {
		return isset($this->request->get['order_id'])
			? intval($this->request->get['order_id'])
			: 0;
	}

	private function _getOrderIntegration() {
		return $this->_getIntegrationFactory()
			->getOrderIntegration();
	}
}
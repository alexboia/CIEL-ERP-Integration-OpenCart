<?php
use CielIntegration\CielController;
use CielIntegration\ContentsAppender;
use CielIntegration\Integration\Admin\Article\ProductResolver;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\Integration\Admin\WithLookupDataProvider;

class ControllerExtensionCielStatusOrderInfoTab extends CielController {
	use WithCielIntegration;
	use WithLookupDataProvider;

	public function assets() {
		if ($this->_isOrderViewPage()) {
			$this->_includeCommonStylesheet();
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
		$isStoreBound = $this->_isStoreBound();
		
		//Basic order data
		$viewData['is_store_bound'] = $isStoreBound;
		$viewData['is_ciel_document_issued'] = $isCielDocumentIssued;

		if ($isCielDocumentIssued) {
			$viewData['ciel_has_warning'] = false;
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
			
			$viewData['ciel_document_issue_configured'] = 
				$this ->_issueDocumentConfigured();
			$viewData['ciel_document_issue_enabled'] = 
				$viewData['ciel_document_issue_configured']
					&& $canOrderItemsBeAddedToDocument;		
			$viewData['ciel_has_warning'] = 
				!$viewData['ciel_document_issue_enabled'];

			$disconnectedLocalProductsIds = $preRequisiteStatus
				->getDisconnectedItemsLocalIds();

			if (!empty($disconnectedLocalProductsIds)) {
				$viewData['ciel_disconnected_local_products'] = 
					$this->_getLocalProductsInformation($disconnectedLocalProductsIds);
			} else {
				$viewData['ciel_disconnected_local_products'] = 
					array();
			}
		}

		//Html fragments
		$viewData['html_loading_indicator'] = $this
			->_renderLoadingIndicator();

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
		$viewData['ciel_erp_check_document_issued_action'] = $this
			->_createRouteUrl('extension/ciel_order_actions/checkDocumentIssued', 
				$orderActionsData);

		//Labels and messages
		$viewData = $this->_loadTexts($viewData, array(
			'lbl_txt_yes',
			'lbl_txt_no',
			'lbl_tab_order_label',
			'lbl_subsection_products_not_connected_title',
			'lbl_subsection_integration_status_title',
			'lbl_missing_product_placeholder',
			'msg_order_cant_issue_not_configured',
			'msg_order_cant_issue_not_all_products_connected',
			'msg_order_cant_issue_batch_tracking_not_posssible',
			'msg_order_cant_issue_batch_tracking_not_available',
			'lbl_order_ciel_erp_document_issued',
			'lbl_order_ciel_erp_document_type',
			'lbl_order_actions',
			'lbl_order_action_issue_document',
			'lbl_order_action_remove_document',
			'msg_order_no_actions_available',
			'msg_order_action_store_not_bound'
		));

		//Add our contents to the view
		$viewContents = $this->_renderView('extension/ciel_status_order_info_tab_contents', 
			$viewData);

		$contentsAppender = new ContentsAppender('#content > div.container-fluid');
		$contentsAppender 
			->enableCleanRepair()
			->addContent($viewContents);

		$output = $contentsAppender
			->rewrite($output);
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
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

	private function _getLocalProductsInformation(array $producIds) {
		return $this->_getProductResolver()
			->getLocalProductsInformation($producIds);
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

	private function _getProductResolver() {
		return new ProductResolver($this->registry);
	}
}
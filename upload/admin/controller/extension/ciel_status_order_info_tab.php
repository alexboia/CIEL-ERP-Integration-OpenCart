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
		$viewData['ciel_erp_check_document_issued_action'] = $this
			->_createRouteUrl('extension/ciel_order_actions/checkDocumentIssued', 
				$orderActionsData);

		//Labels and messages
		$viewData['lbl_txt_yes'] = $this
			->_t('lbl_txt_yes');
		$viewData['lbl_txt_no'] = $this
			->_t('lbl_txt_no');
		$viewData['lbl_tab_order_label'] = $this
			->_t('lbl_tab_order_label');
		$viewData['msg_order_cant_issue_not_all_products_connected'] = $this
			->_t('msg_order_cant_issue_not_all_products_connected');
		$viewData['msg_order_cant_issue_batch_tracking_not_posssible'] = $this
			->_t('msg_order_cant_issue_batch_tracking_not_posssible');
		$viewData['msg_order_cant_issue_batch_tracking_not_available'] = $this
			->_t('msg_order_cant_issue_batch_tracking_not_available');
		$viewData['lbl_order_ciel_erp_document_issued'] = $this
			->_t('lbl_order_ciel_erp_document_issued');
		$viewData['lbl_order_ciel_erp_document_type'] = $this
			->_t('lbl_order_ciel_erp_document_type');
		$viewData['lbl_order_actions'] = $this
			->_t('lbl_order_actions');
		$viewData['lbl_order_action_issue_document'] = $this
			->_t('lbl_order_action_issue_document');
		$viewData['lbl_order_action_remove_document'] = $this
			->_t('lbl_order_action_remove_document');
		$viewData['msg_order_no_actions_available'] = $this
			->_t('msg_order_no_actions_available');

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
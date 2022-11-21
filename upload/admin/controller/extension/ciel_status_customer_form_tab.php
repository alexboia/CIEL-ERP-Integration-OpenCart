<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Partner\PartnerResolver;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\TabPanelContentsRewriter;

class ControllerExtensionCielStatusCustomerFormTab extends CielController {
	use WithCielIntegration;
	
	public function assets() {
		return;
	}

	public function index(&$route, &$data, &$output) {
		$customerId = $this->_getCustomerIdFromUrl();
		if (empty($customerId)) {
			return $output;
		}

		$viewData = array();
		$remotePartnerData = $this->_getProductRemotePartnerData($customerId);

		//Basic customer data
		$viewData['is_store_bound'] = $this->_isStoreBound();
		$viewData['remote_partner_data'] = $remotePartnerData;
		$viewData['is_connected_to_ciel_erp'] = !empty($remotePartnerData['remote_partner_code']);

		//Labels
		$viewData = $this->_loadTexts($viewData, array(
			'lbl_txt_yes',
			'lbl_txt_no',
			'lbl_customer_connected_to_ciel_erp',
			'lbl_customer_ciel_erp_partner_code',
			'lbl_customer_ciel_erp_partner_address_worksite_id',
			'msg_customer_action_store_not_bound'
		));

		//Add our contents to the view
		$viewContents = $this->_renderView('extension/ciel_status_customer_form_tab_contents', 
			$viewData);

		$tabPanelRewriter = new TabPanelContentsRewriter('#form-customer');
		$tabPanelRewriter
			->enableCleanRepair()
			->addTab('tab-ciel-customer-status', 
				$this->_t('lbl_tab_customer_label'), 
				$viewContents);

		return $tabPanelRewriter
			->rewrite($output);
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}

	private function _getProductRemotePartnerData($customerId) {
		$partnerResolver = $this->_getPartnerResolver();
		$remotePartnerData = $partnerResolver
			->getRemotePartnerBindingInformation($customerId);

		if (empty($remotePartnerData)) {
			$remotePartnerData = $partnerResolver->getEmptyRemotePartnerBindingInformation($customerId);
		}

		return $remotePartnerData;
	}

	private function _getCustomerIdFromUrl() {
		return isset($this->request->get['customer_id'])
			? intval($this->request->get['customer_id'])
			: 0;
	}

	private function _getPartnerResolver() {
		return new PartnerResolver($this->registry);
	}
}
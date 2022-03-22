<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Partner\PartnerResolver;
use CielIntegration\TabPanelContentsRewriter;

class ControllerExtensionCielStatusCustomerFormTab extends CielController {
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
		$viewData['remote_partner_data'] = $remotePartnerData;
		$viewData['is_connected_to_ciel_erp'] = !empty($remotePartnerData['remote_partner_code']);

		//Add our contents to the view
		$viewContents = $this->_renderView('extension/ciel_status_customer_form_tab_contents', 
			$viewData);

		$tabPanelRewriter = new TabPanelContentsRewriter('#form-customer');
		$tabPanelRewriter->addTab('tab-ciel-customer-status', 
			'Integrare CIEL ERP', 
			$viewContents);

		return $tabPanelRewriter
			->rewrite($output);
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
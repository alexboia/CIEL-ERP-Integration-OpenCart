<?php

use CielIntegration\CielController;
use CielIntegration\TabPanelContentsRewriter;

class ControllerExtensionCielStatusCustomerFormTab extends CielController {
	public function assets() {

	}

	public function index(&$route, &$data, &$output) {
		//Add our contents to the view
		$viewContents = $this->_renderView('extension/ciel_status_customer_form_tab_contents', 
			array());

		$tabPanelRewriter = new TabPanelContentsRewriter('#form-customer');
		$tabPanelRewriter->addTab('tab-ciel-customer-status', 
			'Integrare CIEL ERP', 
			$viewContents);

		return $tabPanelRewriter
			->rewrite($output);
	}
}
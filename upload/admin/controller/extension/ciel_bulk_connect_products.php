<?php
use CielIntegration\CielController;
use CielIntegration\PageButtonsAppender;

class ControllerExtensionCielBulkConnectProducts extends CielController {
	public function assets() {
		if ($this->_isProductListingPage()) {
			$this->_includeCommonStylesheet();
			$this->_includeLoadingIndicatorScript();
			$this->_includeOperationStatusScript();
			$this->_includeCommonScript();
			$this->_addHeaderScript('extension/ciel_bulk_connect_products.js');
		}
	}

	private function _isProductListingPage() {
		return $this->_isRoute('catalog/product');
	}

	public function index(&$route, &$data, &$output) {
		$buttonsAppender = new PageButtonsAppender();
		$buttonsAppender
			->enableCleanRepair()
			->addButton('ciel-connect-selected', 'fa-plug', 'btn-primary');

		return $buttonsAppender
			->rewrite($output);
	}
}
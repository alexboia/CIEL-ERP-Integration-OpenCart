<?php
use CielIntegration\CielController;
use CielIntegration\ContentsAppender;
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
		$viewData = array();

		//Action URLs
		$viewData['ciel_erp_connect_action_url'] = $this
			->_createRouteUrl('extension/ciel_product_actions/connect', 
				array(
					'product_id' => '%product_id%'
				));

		//Html fragments
		$viewData['html_loading_indicator'] = $this
			->_renderLoadingIndicator();

		$viewContents = $this->_renderView('extension/ciel_bulk_connect_products', 
			$viewData);

		//Add buttons
		$buttonsAppender = new PageButtonsAppender();
		$buttonsAppender
			->enableCleanRepair()
			->addButton('ciel-connect-selected', 'fa-plug', 'btn-primary');

		$output = $buttonsAppender
			->rewrite($output);
			
		//Add view contents
		$contentsAppender = new ContentsAppender('#form-product');
		$contentsAppender
			->enableCleanRepair()
			->addContent($viewContents);

		$output = $contentsAppender
			->rewrite($output);
	}
}
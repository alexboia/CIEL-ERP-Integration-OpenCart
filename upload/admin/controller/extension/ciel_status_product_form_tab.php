<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Article\ProductResolver;
use CielIntegration\TabPanelContentsRewriter;

class ControllerExtensionCielStatusProductFormTab extends CielController {
	public function assets() {
		if ($this->_isProductEditingPage()) {
			$this->_includeLoadingIndicatorScript();
			$this->_includeOperationStatusScript();
			$this->_includeCommonScript();
			$this->_addHeaderScript('extension/ciel_product_actions.js');
		}
	}

	private function _isProductEditingPage() {
		return $this->_isRoute('catalog/product/edit');
	}

	public function index(&$route, &$data, &$output) {
		$productId = $this->_getProductIdFromUrl();
		if (empty($productId)) {
			return $output;
		}

		$viewData = array();
		$remoteArticleData = $this->_getProductRemoteArticleData($productId);

		//Basic product data
		$viewData['remote_article_data'] = $remoteArticleData;
		$viewData['is_connected_to_ciel_erp'] = !empty($remoteArticleData['remote_id']);

		//Html fragments
		$viewData['html_loading_indicator'] = $this->_renderLoadingIndicator();

		//Product actions data
		$productActionParams = array(
			'product_id' => $productId
		);

		$viewData['ciel_erp_connect_action_url'] = $this
			->_createRouteUrl('extension/ciel_product_actions/connect', 
				$productActionParams);
		$viewData['ciel_erp_update_all_action_url'] = $this
			->_createRouteUrl('extension/ciel_product_actions/updateAll', 
				$productActionParams);
		$viewData['ciel_erp_update_stocks_action_url'] = $this
			->_createRouteUrl('extension/ciel_product_actions/updateStocks', 
				$productActionParams);

		//Add our contents to the view
		$viewContents = $this->_renderView('extension/ciel_status_product_form_tab_contents', 
			$viewData);

		$tabPanelRewriter = new TabPanelContentsRewriter('#form-product');
		$tabPanelRewriter->addTab('tab-ciel-status', 
			'Integrare CIEL ERP', 
			$viewContents);

		return $tabPanelRewriter
			->rewrite($output);
	}

	private function _getProductRemoteArticleData($productId) {
		$productResolver = $this->_getProductResolver();
		$remoteArticleData = $productResolver
			->getRemoteArticleData($productId);

		if (empty($remoteArticleData)) {
			$remoteArticleData = $productResolver->getEmptyRemoteArticleData($productId);
		}

		return $remoteArticleData;
	}

	private function _getProductIdFromUrl() {
		return isset($this->request->get['product_id'])
			? intval($this->request->get['product_id'])
			: 0;
	}

	private function _getProductResolver() {
		return new ProductResolver($this->registry);
	}
}
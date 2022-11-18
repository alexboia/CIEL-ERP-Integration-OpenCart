<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Article\ProductResolver;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\TabPanelContentsRewriter;

class ControllerExtensionCielStatusProductFormTab extends CielController {
	use WithCielIntegration;

	public function assets() {
		if ($this->_isProductEditingPage()) {
			$this->_includeCommonStylesheet();
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
		$viewData['is_store_bound'] = $this->_isStoreBound();
		$viewData['remote_article_data'] = $remoteArticleData;
		$viewData['is_connected_to_ciel_erp'] = !empty($remoteArticleData['remote_id']);

		if (!$viewData['is_connected_to_ciel_erp']) {
			$viewData['can_be_connected_to_ciel_erp'] = $this->_productHasSku($productId);
		} else {
			$viewData['can_be_connected_to_ciel_erp'] = true;
		}

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

		//Labels
		$viewData['lbl_txt_yes'] = $this
			->_t('lbl_txt_yes');
		$viewData['lbl_txt_no'] = $this
			->_t('lbl_txt_no');
		$viewData['lbl_product_connected_to_ciel_erp'] = $this
			->_t('lbl_product_connected_to_ciel_erp');
		$viewData['lbl_product_ciel_erp_article_id'] = $this
			->_t('lbl_product_ciel_erp_article_id');
		$viewData['lbl_product_ciel_erp_vat_option_name'] = $this
			->_t('lbl_product_ciel_erp_vat_option_name');
		$viewData['lbl_product_ciel_erp_vat_quota_value'] = $this
			->_t('lbl_product_ciel_erp_vat_quota_value');
		$viewData['lbl_product_ciel_erp_batch_tracking_enabled'] = $this
			->_t('lbl_product_ciel_erp_batch_tracking_enabled');
		$viewData['lbl_product_actions'] = $this
			->_t('lbl_product_actions');
		$viewData['lbl_product_action_update_full'] = $this
			->_t('lbl_product_action_update_full');
		$viewData['lbl_product_action_update_stocks'] = $this
			->_t('lbl_product_action_update_stocks');
		$viewData['lbl_product_action_connect'] = $this
			->_t('lbl_product_action_connect');
		$viewData['msg_product_no_sku'] = $this
			->_t('msg_product_no_sku');
		$viewData['msg_product_no_actions_available'] = $this
			->_t('msg_product_no_actions_available');
		$viewData['msg_product_action_store_not_bound'] = $this
			->_t('msg_product_action_store_not_bound');

		//Add our contents to the view
		$viewContents = $this->_renderView('extension/ciel_status_product_form_tab_contents', 
			$viewData);

		$tabPanelRewriter = new TabPanelContentsRewriter('#form-product');
		$tabPanelRewriter
			->enableCleanRepair()
			->addTab('tab-ciel-status', 
				$this->_t('lbl_tab_product_label'), 
				$viewContents);

		return $tabPanelRewriter
			->rewrite($output);
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
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

	private function _productHasSku($productId) {
		return $this->_getProductResolver()
			->productHasSku($productId);
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
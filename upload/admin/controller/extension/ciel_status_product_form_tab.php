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
		$viewData = $this->_loadTexts($viewData, array(
			'lbl_txt_yes',
			'lbl_txt_no',
			'lbl_product_connected_to_ciel_erp',
			'lbl_product_ciel_erp_article_id',
			'lbl_product_ciel_erp_vat_option_name',
			'lbl_product_ciel_erp_vat_quota_value',
			'lbl_product_ciel_erp_batch_tracking_enabled',
			'lbl_product_actions',
			'lbl_product_action_update_full',
			'lbl_product_action_update_stocks',
			'lbl_product_action_connect',
			'msg_product_no_sku',
			'msg_product_no_actions_available',
			'msg_product_action_store_not_bound'
		));

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
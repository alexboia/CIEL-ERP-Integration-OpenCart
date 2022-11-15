<?php
use Ciel\Api\Data\ProductSyncMode;
use Ciel\Api\Exception\WebserviceCommunicationException;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\Integration\Admin\WithLookupDataProvider;
use CielIntegration\WithAdminLayoutLoader;
use CielIntegration\WithLogging;

class ControllerExtensionCielSyncProducts extends CielController {
	use WithAdminLayoutLoader;
	use WithLookupDataProvider;
	use WithCielIntegration;
	use WithLogging;

	public function __construct(\Registry $registry) {
		parent::__construct($registry);
		$this->_setTextDomain('extension/ciel_sync_products');
	}

	public function index() {
		//Prepare document assets
		$this->_setDocumentTitleLangKey('ciel_sync_products_title');
		$this->_includeCommonStylesheet();
		
		$this->_includeLoadingIndicatorScript();
		$this->_includeOperationStatusScript();
		$this->_includeCommonScript();
		$this->_addHeaderScript('extension/ciel_sync_products.js');

		//Prepare data
		$data = $this->_loadAdminLayout();
		$data['is_bound'] = $this->_isStoreBound();
		$data['product_sync_modes'] = $this->_getSupportedProductSyncModes();

		$data['ciel_sync_products_title'] = $this
			->_t('ciel_sync_products_title');
		$data['ciel_sync_products_info'] = $this
			->_t('ciel_sync_products_info');
		$data['ciel_sync_products_explanation'] = $this
			->_t('ciel_sync_products_explanation');
		$data['ciel_err_sync_products_store_not_bound'] = $this
			->_t('ciel_err_sync_products_store_not_bound');

		$data['ciel_sync_products_eligible_count_explanation'] = $this
			->_t('ciel_sync_products_eligible_count_explanation');
		$data['ciel_sync_products_eligible_count_header'] = $this
			->_t('ciel_sync_products_eligible_count_header');
		$data['ciel_sync_products_updated_count_header'] = $this
			->_t('ciel_sync_products_updated_count_header');

		$data['ciel_sync_products_success_msg'] = $this
			->_t('ciel_sync_products_success_msg');
		$data['ciel_sync_products_error_msg'] = $this
			->_t('ciel_sync_products_error_msg');

		$data['ciel_update_btn_text'] = $this
			->_t('ciel_update_btn_text');
		$data['ciel_update_btn_action'] = $this
			->_createRouteUrl('extension/ciel_sync_products/execute');

		$data['txt_cancel_action'] = $this
			->_t('button_cancel');
		$data['url_cancel_action'] = $this
			->_createRouteUrl('common/dashboard');

		$data['html_loading_indicator'] = $this
			->_renderLoadingIndicator();
		$data['html_breadcrumbs'] = $this
			->_renderBreadcrumbs($this->_getBreadcrumbsData());

		//Render view
		$this->_renderViewToResponseOutput('extension/ciel_sync_products', 
			$data);
	}

	private function _getBreadcrumbsData() {
		$breadcrumbs = $this->_getBaseBreadcrumbs();
		$breadcrumbs[] = array(
			'text' => $this->_t('ciel_sync_products_title'),
			'href' => $this->_createRouteUrl('extension/ciel_sync_products')
		);

		return array(
			'breadcrumbs' => $breadcrumbs
		);
	}

	public function execute() {
		if ($this->_isHttpPost() && $this->_isStoreBound()) {
			$modeId = $this->_getProductSyncModeFromHttpPost();
			$response = $this->_syncProducts($modeId);
		} else {
			$response = $this->_createEmptySyncProductsAjaxResponse();
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _getProductSyncModeFromHttpPost() {
		$modeId = isset($this->request->post['ciel_product_sync_mode'])
			? $this->request->post['ciel_product_sync_mode']
			: null;

		if (empty($modeId) || !ProductSyncMode::isSupported($modeId)) {
			$modeId = ProductSyncMode::StockOnly;
		}

		return $modeId;
	}

	private function _syncProducts($modeId) {
		$response = $this->_createEmptySyncProductsAjaxResponse();

		try {
			$response->result = $this->_processSyncProducts($modeId);
			$response->message = $this->_t('ciel_sync_products_success_msg');
			$response->success = true;
		} catch (WebserviceCommunicationException $exc) {
			$response->message = $this->_t('ciel_sync_products_connection_error_msg');
			$this->_logError($exc);
		} catch (Exception $exc) {
			$response->message = $this->_t('ciel_sync_products_error_msg');
			$this->_logError($exc);
		}

		return $response;
	}

	private function _createEmptySyncProductsAjaxResponse() {
		return $this->_createAjaxResponse(array(
			'result' => array(
				'eligible' => 0,
				'updated' => 0
			)
		));
	}

	private function _processSyncProducts($modeId) {
		if ($modeId == ProductSyncMode::AllInformation) {
			return $this->_processUpdateAllProductsInfo();
		} else {
			return $this->_processUpdateProductsStocksOnly();
		}
	}

	private function _processUpdateAllProductsInfo() {
		$this->_logDebug('Begin updating all products information.');	
		$updateResult = $this->_updateInformationForAllArticles();

		$this->_logDebug('Successfully updated all products information.');
		return $updateResult->toArray();
	}

	private function _updateInformationForAllArticles() {
		return $this->_getArticleIntegration()
			->updateInformationForAllArticles();
	}

	private function _processUpdateProductsStocksOnly() {
		$this->_logDebug('Begin updating all product stocks only.');	
		$updateResult = $this->_updateStocksForAllArticles();

		$this->_logDebug('Successfully updated all product stocks only.');
		return $updateResult->toArray();
	}

	private function _updateStocksForAllArticles() {
		return $this->_getArticleIntegration()
			->updateStocksForAllArticles();
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}
}
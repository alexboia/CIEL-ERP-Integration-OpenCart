<?php

use Ciel\Api\Exception\WebserviceCommunicationException;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Article\ProductResolver;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithAdminLayoutLoader;
use CielIntegration\WithLogging;

class ControllerExtensionCielImportOcRomania extends CielController {
	use WithCielIntegration;
	use WithAdminLayoutLoader;
	use WithLogging;

	public function __construct(\Registry $registry) {
		parent::__construct($registry);
		$this->_setTextDomain('extension/ciel_import_oc_romania');
	}

	public function index() {
		//Prepare document assets
		$this->_setDocumentTitleLangKey('ciel_import_oc_romania_title');
		$this->_addStylesheet('extension/ciel_common.css');
		
		$this->_includeLoadingIndicatorScript();
		$this->_includeOperationStatusScript();
		$this->_includeCommonScript();
		$this->_addHeaderScript('extension/ciel_import_oc_romania.js');

		//Prepare data
		$data = $this->_loadAdminLayout();
		$data['is_bound'] = $this->_isStoreBound();

		$data['ciel_import_oc_romania_title'] = $this->_t('ciel_import_oc_romania_title');
		$data['ciel_import_oc_romania_info'] = $this->_t('ciel_import_oc_romania_info');
		$data['ciel_err_migrate_products_store_not_bound'] = $this->_t('ciel_err_migrate_products_store_not_bound');

		$data['ciel_migrate_products_eligible_count_header'] = $this->_t('ciel_migrate_products_eligible_count_header');
		$data['ciel_migrate_products_updated_count_header'] = $this->_t('ciel_migrate_products_updated_count_header');

		$data['ciel_migrate_btn_text'] = $this->_t('ciel_migrate_btn_text');
		$data['ciel_migrate_btn_action'] = $this->_createRouteUrl('extension/ciel_import_oc_romania/execute');

		$data['txt_cancel_action'] = $this->_t('button_cancel');
		$data['url_cancel_action'] = $this->_createRouteUrl('common/dashboard');

		$data['html_loading_indicator'] = $this->_renderLoadingIndicator();
		$data['html_breadcrumbs'] = $this->_renderBreadcrumbs($this->_getBreadcrumbsData());

		$this->_renderViewToResponseOutput('extension/ciel_import_oc_romania', 
			$data);
	}

	private function _getBreadcrumbsData() {
		$breadcrumbs = $this->_getBaseBreadcrumbs();
		$breadcrumbs[] = array(
			'text' => $this->_t('ciel_import_oc_romania_title'),
			'href' => $this->_createRouteUrl('extension/ciel_import_oc_romania')
		);

		return array(
			'breadcrumbs' => $breadcrumbs
		);
	}

	public function execute() {
		if ($this->_isHttpPost() && $this->_isStoreBound()) {
			$response = $this->_migrateProducts();
		} else {
			$response = $this->_createEmptyMigrateProductsAjaxResponse();
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _createEmptyMigrateProductsAjaxResponse() {
		return $this->_createAjaxResponse(array(
			'connected' => array(),
			'result' => array(
				'eligible' => 0,
				'updated' => 0
			)
		));
	}

	private function _migrateProducts() {
		$response = $this->_createEmptyMigrateProductsAjaxResponse();

		try {
			$result = $this->_processMigrateProducts();
			$response->connected = $result['connected'];
			$response->result['eligible'] = $result['eligible'];
			$response->result['updated'] = count($result['connected']);

			$response->message = $this->_t('ciel_migrate_products_success_msg');
			$response->success = true;
		} catch (WebserviceCommunicationException $exc) {
			$response->message = $this->_t('ciel_migrate_products_connection_error_msg');
			$this->_logError($exc);
		} catch (Exception $exc) {
			$response->message = $this->_t('ciel_migrate_products_error_msg');
			$this->_logError($exc);
		}

		return $response;
	}

	private function _processMigrateProducts() {
		$connected = array();
		$ocRomaniaConnectedProducts = $this->_getOcRomaniaConnectedProducts();
		$eligible = count($ocRomaniaConnectedProducts);

		$this->_logDebug('Found <' . $eligible . '> eligible products for migration.');

		foreach ($ocRomaniaConnectedProducts as $cProduct) {
			$id = $cProduct['id'];
			$sku = $cProduct['sku'];

			try {
				$this->_processMigrateProduct($id, $sku);
				$connected[] = array(
					'id' => $id,
					'sku' => $sku
				);
			} catch (Exception $exc) {
				$this->_logError($exc, 'Failed to migrate product with sku <' . $sku . '>');
			}
		}

		$this->_logDebug('Done processing product migration.');
		return array(
			'eligible' => $eligible,
			'connected' => $connected
		);
	}

	private function _processMigrateProduct($id, $sku) {
		$productResolver = $this->_getProductResolver();
		$articleIntegration = $this->_getArticleIntegration();

		if (!$productResolver->isConnectedToCielErp($id)) {
			$articleIntegration->tryAutoConnectArticleByLocalCode($id);
			$this->_logDebug('Product with sku <' . $sku . '> has been migrated.');
		} else {
			$this->_logDebug('Product with sku <' . $sku . '> is already connected. Skipping...');
		}
	}

	private function _getOcRomaniaConnectedProducts() {
		return $this->_getOcRomaniaConnectedProductsProvider()
			->getSyncedProducts();
	}

	private function _getOcRomaniaConnectedProductsProvider() {
		return $this->_getIntegrationFactory()
			->getOcRomaniaConnectedProductsProvider();
	}

	private function _getProductResolver() {
		return new ProductResolver($this->registry);
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}
}
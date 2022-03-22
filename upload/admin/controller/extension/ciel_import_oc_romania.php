<?php

use Ciel\Api\Exception\WebserviceCommunicationException;
use CielIntegration\CielController;
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

		$data['ciel_import_oc_romania_title'] = 
			$this->_t('ciel_import_oc_romania_title');
		$data['ciel_import_oc_romania_info'] = 
			$this->_t('ciel_import_oc_romania_info');
		$data['ciel_err_migrate_products_store_not_bound'] = 
			$this->_t('ciel_err_migrate_products_store_not_bound');

		$data['ciel_migrate_products_eligible_count_header'] = 
			$this->_t('ciel_migrate_products_eligible_count_header');
		$data['ciel_migrate_products_updated_count_header'] = 
			$this->_t('ciel_migrate_products_updated_count_header');
		$data['ciel_migrate_products_not_found_count_header'] = 
			$this->_t('ciel_migrate_products_not_found_count_header');

		$data['ciel_migrate_btn_text'] = $this->_t('ciel_migrate_btn_text');
		$data['ciel_migrate_btn_action'] = $this->_createRouteUrl('extension/ciel_import_oc_romania/execute');

		$data['ciel_download_not_found_btn_text'] = $this->_t('ciel_download_not_found_btn_text');
		$data['ciel_download_not_found_action'] = $this->_createRouteUrl('extension/ciel_import_oc_romania/exportNotFound');

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
			$response->notFound = $result['notFound'];
			$response->result['eligible'] = $result['eligible'];
			$response->result['updated'] = count($result['connected']);
			$response->result['notFound'] = count($result['notFound']);

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
		$eligibleProductsIds = $this->_getOcRomaniaConnectedProductIds();
		$eligible = count($eligibleProductsIds);

		if ($eligible > 0) {
			$this->_logDebug('Found <' . $eligible . '> eligible products for migration.');
			$result = $this->_connectProductsByIds($eligibleProductsIds);
		} else {
			$result = array(
				'connected' => array(),
				'notFound' => array()
			);
		}

		$this->_logDebug('Done processing product migration.');
		return array(
			'eligible' => $eligible,
			'connected' => $result['connected'],
			'notFound' => $result['notFound']
		);
	}

	private function _connectProductsByIds($productsIds) {
		return $this->_getArticleIntegration()
			->tryAutoConnectArticlesByLocalCodes($productsIds);
	}

	private function _getOcRomaniaConnectedProductIds() {
		$productIds = array();
		$products = $this->_getOcRomaniaConnectedProductsProvider()
			->getSyncedProducts();

		foreach ($products as $p) {
			$productIds[] = $p['id'];
		}

		return $productIds;
	}

	private function _getOcRomaniaConnectedProductsProvider() {
		return $this->_getIntegrationFactory()
			->getOcRomaniaConnectedProductsProvider();
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}

	public function exportNotFound() {
		if ($this->_isHttpPost()) {
			$ids = $this->_getNotFoundProductIdsFromHttpPost();
			if (!empty($ids)) {
				$productsData = $this->_getNotFoundProductsInformation($ids);
				$csvData = $this->_generateNotFoundProductsCsv($productsData);
				$this->_sendNotFoundProductsCsv($csvData);
			}
		}

		die;
	}

	private function _getNotFoundProductIdsFromHttpPost() {
		$idsTxt = isset($this->request->post['not_found_ids'])
			? $this->request->post['not_found_ids']
			: array();

		$ids = explode(';', 
			$idsTxt);

		return array_map('intval', 
			$ids);
	}

	private function _getNotFoundProductsInformation($ids) {
		$productsData = array();
		$model = $this->_getProductModel();

		foreach ($ids as $id) {
			$product = $model->getProduct($id);
			$productsData[] = array(
				$id,
				$product['sku'],
				$product['model'],
				$product['name'],
				''
			);
		}

		return $productsData;
	}

	private function _generateNotFoundProductsCsv($productsData) {
		ob_start();
		$outputStream = fopen('php://output', 'w'); 

		$headerFields = $this->_getNotFoundProductsCsvHeader();
		fputcsv($outputStream, 
			$headerFields);

		foreach ($productsData as $p) {
			fputcsv($outputStream, 
				$p);
		}

		fclose($outputStream);
		return ob_get_clean();
	}

	private function _getNotFoundProductsCsvHeader() {
		return array(
			$this->_t('txt_not_found_csv_id_header'),
			$this->_t('txt_not_found_csv_sku_header'),
			$this->_t('txt_not_found_csv_model_header'),
			$this->_t('txt_not_found_csv_name_header'),
			$this->_t('txt_not_found_csv_new_sku_header')
		);
	}

	private function _sendNotFoundProductsCsv($csvData) {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="oc-romania-not-connected-products.csv";');

		echo $csvData;
		die;
	}

	/**
	 * @return \ModelCatalogProduct
	 */
	private function _getProductModel() {
		$this->load->model('catalog/product');
		return $this->model_catalog_product;
	}
}
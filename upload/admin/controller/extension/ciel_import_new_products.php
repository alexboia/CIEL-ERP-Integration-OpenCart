<?php

use Ciel\Api\Exception\RemoteArticleNotFoundException;
use Ciel\Api\Exception\WebserviceCommunicationException;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithAdminLayoutLoader;
use CielIntegration\WithLogging;

class ControllerExtensionCielImportNewProducts extends CielController {
	use WithCielIntegration;
	use WithAdminLayoutLoader;
	use WithLogging;

	public function __construct(\Registry $registry) {
		parent::__construct($registry);
		$this->_setTextDomain('extension/ciel_import_new_products');
	}

	public function index() {
		//Prepare document assets
		$this->_setDocumentTitleLangKey('ciel_import_new_products_title');
		$this->_addStylesheet('extension/ciel_common.css');

		$this->_includeLoadingIndicatorScript();
		$this->_includeOperationStatusScript();
		$this->_includeCommonScript();
		$this->_addHeaderScript('extension/ciel_import_new_products.js');

		//Prepare data
		$data = $this->_loadAdminLayout();

		$data['ciel_import_new_products_title'] = 
			$this->_t('ciel_import_new_products_title');
		$data['ciel_import_new_products_info'] = 
			$this->_t('ciel_import_new_products_info');
		$data['ciel_err_import_new_products_store_not_bound'] = 
			$this->_t('ciel_err_import_new_products_store_not_bound');
		$data['ciel_err_import_new_products_error_computing_products'] = 
			$this->_t('ciel_err_import_new_products_error_computing_products');

		$data['is_bound'] = $this->_isStoreBound();
		if ($data['is_bound']) {
			$data['new_remote_products'] = $this->_getPotentialNewRemoteProducts();
		} else {
			$data['new_remote_products'] = array();
		}

		$data['txt_cancel_action'] = $this->_t('button_cancel');
		$data['url_cancel_action'] = $this->_createRouteUrl('common/dashboard');

		$data['ciel_import_new_products_text'] = $this->_t('ciel_import_new_products_text');
		$data['ciel_import_new_products_action'] = $this->_createRouteUrl('extension/ciel_import_new_products/execute');

		$data['html_loading_indicator'] = $this->_renderLoadingIndicator();
		$data['html_breadcrumbs'] = $this->_renderBreadcrumbs($this->_getBreadcrumbsData());

		$this->_renderViewToResponseOutput('extension/ciel_import_new_products', 
			$data);
	}

	private function _getPotentialNewRemoteProducts() {
		$newRemoteProducts = null;

		try {
			$newRemoteProducts = $this->_tryDetermineNewRemoteProducts();
		} catch (Exception $exc) {
			$this->_logError($exc);
		}

		return $newRemoteProducts;
	}

	private function _tryDetermineNewRemoteProducts() {
		return $this->_getArticleIntegration()
			->tryDetermineNewRemoteProducts();
	}

	private function _getBreadcrumbsData() {
		$breadcrumbs = $this->_getBaseBreadcrumbs();
		$breadcrumbs[] = array(
			'text' => $this->_t('ciel_import_new_products_title'),
			'href' => $this->_createRouteUrl('extension/ciel_import_new_products_title')
		);

		return array(
			'breadcrumbs' => $breadcrumbs
		);
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}

	public function execute() {
		$response = null;
		if ($this->_isHttpPost() && $this->_isStoreBound()) {
			$remoteIds = $this->_getRemoteIdsFromHttpPost();
			if (!empty($remoteIds)) {
				$response = $this->_importProductsFromRemoteArticles($remoteIds);
			}
		}

		if ($response == null) {
			$response = $this->_createEmptyImportProductsAjaxResponse();
		}

		return $response;
	}

	private function _getRemoteIdsFromHttpPost() {
		return isset($this->request->post['import_remote_ids']) 
				&& is_array($this->request->post['import_remote_ids'])
			? array_map('intval', $this->request->post['import_remote_ids'])
			: array();
	}

	private function _importProductsFromRemoteArticles($remoteIds) {
		$atLeastOneSucceeded = false;
		$response = $this->_createEmptyImportProductsAjaxResponse();

		foreach ($remoteIds as $remoteId) {
			$importProductResult = $this->_importProductFromRemoteArticle($remoteId);

			$atLeastOneSucceeded = $atLeastOneSucceeded 
				|| $importProductResult->success;

			$response->results[$remoteId] = 
				$importProductResult;
		}

		if ($atLeastOneSucceeded) {
			$response->success = true;
			$this->_logDebug('Sucessfully imported at least one article.');
		} else {
			$this->_logDebug('No article could be imported.');
		}

		return $response;
	}

	private function _createEmptyImportProductsAjaxResponse() {
		return $this->_createAjaxResponse(array(
			'results' => array()
		));
	}

	private function _importProductFromRemoteArticle($remoteId) {
		$importProductResult = $this->_createAjaxResponse();

		try {
			$importProductResult->success = $this
				->_processImportProductFromRemoteArticle($remoteId);
			$importProductResult->message = $this
				->_t('Product successfully imported from remote article');
		} catch (RemoteArticleNotFoundException $exc) {
			$importProductResult->message = $this->_t('ciel_import_new_products_not_found_error_msg');
			$this->_logError($exc, 'Remote article not found (by <' . $exc->getIdentifierType() . '>, value <' . $exc->getIdentifierValue() . '>)');
		} catch (WebserviceCommunicationException $exc) {
			$importProductResult->message = $this->_t('ciel_import_new_products_connection_error_msg');
			$this->_logError($exc, 'CIEL ERP Server communication issue.');
		} catch (Exception $exc) {
			$importProductResult->message = $this->_t('ciel_import_new_products_error_msg');
			$this->_logError($exc, 'Failed to import remote article');
		}

		return $importProductResult;
	}

	private function _processImportProductFromRemoteArticle($remoteId) {
		$this->_logDebug('Begin importing remote article with id=<' . $remoteId . '>...');

		$this->_getArticleIntegration()
			->importArticle($remoteId);

		$this->_logDebug('Sucessfully imported remote article');
		return true;
	}
}
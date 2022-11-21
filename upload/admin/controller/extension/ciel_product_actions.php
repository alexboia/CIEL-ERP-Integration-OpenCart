<?php

use Ciel\Api\Exception\RemoteArticleNotFoundException;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithLogging;

class ControllerExtensionCielProductActions extends CielController {
	use WithCielIntegration;
	use WithLogging;

	public function __construct(\Registry $registry) {
		parent::__construct($registry);
		$this->_setTextDomain('extension/ciel_product_actions');
	}

	public function connect() {
		$response = $this->_createAjaxResponse();
		
		if ($this->_isHttpPost()) {
			$productId = $this->_getProductIdFromUrl();
			if (!empty($productId)) {
				try {
					$response->success = $this->_doConnectToCielErp($productId);
					$response->message = $response->success 
						? $this->_t('ciel_product_connect_product_success_msg')
						: $this->_t('msg_product_no_sku');
				} catch (RemoteArticleNotFoundException $exc) {
					$response->message = $this->_t('ciel_product_connect_product_not_found_error_msg');
					$this->_logRemoteArticleNotFoundError($exc);
				} catch (Exception $exc) {
					$response->message = $this->_t('ciel_product_connect_product_error_msg');
					$this->_logGenericProductActionError($exc);
				}
			}
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _getProductIdFromUrl() {
		return isset($this->request->get['product_id'])
			? intval($this->request->get['product_id'])
			: 0;
	}

	private function _doConnectToCielErp($productId) {
		$articleIntegration = $this->_getArticleIntegration();
		if ($articleIntegration->canBeMatchedByLocalCode($productId)) {
			$this->_logProductCanBeConnected($productId);
			$articleIntegration->tryAutoConnectArticleByLocalCode($productId);
			return true;
		} else {
			$this->_logProductCannotBeConnected($productId);
			return false;
		}
	}

	private function _logRemoteArticleNotFoundError(RemoteArticleNotFoundException $exc) {
		$this->_logError($exc, 'Could not execute product action. Remote article not found (by <' . $exc->getIdentifierType() . '>, value <' . $exc->getIdentifierValue() . '>).');
	}

	private function _logGenericProductActionError(Exception $exc) {
		$this->_logError($exc, 'Could not execute product action.');
	}
	
	private function _logProductCanBeConnected($productId) {
		$this->_logDebug(sprintf('Product with id <%s> CAN be connected to NextUpERP. Will proceed...', 
			$productId));
	}

	private function _logProductCannotBeConnected($productId) {
		$this->_logDebug(sprintf('Product with id <%s> CANNOT be connected to NextUpERP.', 
			$productId));
	}

	public function updateAll() {
		$response = $this->_createAjaxResponse();

		if ($this->_isHttpPost()) {
			$productId = $this->_getProductIdFromUrl();
			if (!empty($productId)) {
				try {
					$response->success = $this->_doUpdateAllProductInformation($productId);
					$response->message = $this->_t('ciel_product_updated_product_success_msg');
				} catch (RemoteArticleNotFoundException $exc) {
					$this->_logRemoteArticleNotFoundError($exc);
					$response->message = $this->_t('ciel_product_udpate_product_not_found_error_msg');
				} catch (Exception $exc) {
					$this->_logGenericProductActionError($exc);
					$response->message = $this->_t('ciel_product_udpate_product_error_msg');
				}
			}
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _doUpdateAllProductInformation($productId) {
		$articleIntegration = $this->_getArticleIntegration();
		if ($articleIntegration->isArticleConnected($productId)) {
			$articleIntegration->updateArticleFromRemoteSource($productId);
			return true;
		} else {
			return false;
		}
	}

	public function updateStocks() {
		$response = $this->_createAjaxResponse();

		if ($this->_isHttpPost()) {
			$productId = $this->_getProductIdFromUrl();
			if (!empty($productId)) {
				try {
					$response->success = $this->_doUpdateStocks($productId);
				} catch (RemoteArticleNotFoundException $exc) {
					$this->_logRemoteArticleNotFoundError($exc);
				} catch (Exception $exc) {
					$this->_logGenericProductActionError($exc);
				}
			}
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _doUpdateStocks($productId) {
		$articleIntegration = $this->_getArticleIntegration();
		if ($articleIntegration->isArticleConnected($productId)) {
			$articleIntegration->updateStockForArticle($productId);
			return true;
		} else {
			return false;
		}
	}
}
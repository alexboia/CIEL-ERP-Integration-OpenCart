<?php

use Ciel\Api\Exception\RemoteArticleNotFoundException;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;

class ControllerExtensionCielProductActions extends CielController {
	use WithCielIntegration;

	public function connect() {
		$response = $this->_createAjaxResponse();
		
		if ($this->_isHttpPost()) {
			$productId = $this->_getProductIdFromUrl();
			if (!empty($productId)) {
				try {
					$response->success = $this->_doConnectToCielErp($productId);
				} catch (RemoteArticleNotFoundException $exc) {
					//TODO: logging
				} catch (Exception $exc) {
					//TODO: logging
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
			$articleIntegration->tryAutoConnectArticleByLocalCode($productId);
			return true;
		} else {
			return false;
		}
	}

	private function _getArticleIntegration() {
		return $this->_getIntegrationFactory()
			->getArticleIntegration();
	}

	public function updateAll() {
		$response = $this->_createAjaxResponse();

		if ($this->_isHttpPost()) {
			$productId = $this->_getProductIdFromUrl();
			if (!empty($productId)) {
				try {
					$response->success = $this->_doUpdateAllProductInformation($productId);
				} catch (RemoteArticleNotFoundException $exc) {
					//TODO: logging
				} catch (Exception $exc) {
					//TODO: logging
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
					//TODO: logging
				} catch (Exception $exc) {
					//TODO: logging
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
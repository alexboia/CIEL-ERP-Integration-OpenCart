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
					$this->_doConnectToCielErp($productId);
					$response->success = true;
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
		$articleIntegration->tryAutoConnectArticleByLocalCode($productId);
	}

	private function _getArticleIntegration() {
		return $this->_getIntegrationFactory()
			->getArticleIntegration();
	}

	public function update_all() {

	}

	public function update_stocks() {

	}
}
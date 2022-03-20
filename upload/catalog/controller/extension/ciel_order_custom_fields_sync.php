<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Order\OrderCustomFieldsSyncService;
use CielIntegration\WithLogging;

class ControllerExtensionCielOrderCustomFieldsSync extends CielController {
	use WithLogging;

	public function afterAdd(&$route, &$args, &$output) {
		$orderId = intval($output);
		$orderData = $args[0];

		$this->_syncOrderCustomerCustomFields($orderId, 
			$orderData);
	}

	public function afterEdit(&$route, &$args, &$output) {
		$orderId = $args[0];
		$orderData = $args[1];

		$this->_syncOrderCustomerCustomFields($orderId, 
			$orderData);
	}

	private function _syncOrderCustomerCustomFields($orderId, $orderData) {
		$this->_logDebug('Intercepted order save for order id <' . $orderId . '>.');
		$this->_getOrderCustomFieldsSyncService()
			->syncOrderCustomerCustomFields($orderId, 
				$orderData);
	}

	private function _getOrderCustomFieldsSyncService() {
		return new OrderCustomFieldsSyncService($this->registry);
	}
}
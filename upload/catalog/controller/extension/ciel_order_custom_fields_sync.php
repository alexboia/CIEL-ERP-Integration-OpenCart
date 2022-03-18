<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithLogging;

class ControllerExtensionCielOrderCustomFieldsSync extends CielController {
	use WithCielIntegration;
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
	
		$remoteOrderModel = $this->_getRemoteOrderModel();
		$customerId = $this->_extractOrderCustomerId($orderData);
		$remoteOrderCustomerBillingInformation = $this->_extractRemoteOrderCustomerBillingInformation($orderData);

		$remoteOrderModel->setBillingInformation($orderId,
			$customerId,
			$remoteOrderCustomerBillingInformation);
	}

	private function _extractOrderCustomerId($orderData) {
		return !empty($orderData['customer_id'])
			? intval($orderData['customer_id'])
			: 0;
	}

	private function _extractRemoteOrderCustomerBillingInformation($orderData) {
		$billingType = $this->_determineCustomerPersonType($orderData);
		$billingInformation = $this->_extractAdditionalBillingFields($orderData);

		return array_merge($billingInformation, array(
			'billing_type' => $billingType
		));
	}

	private function _determineCustomerPersonType($orderData) {
		$customerGroupId = $this->_findCustomerGroupId($orderData);
		return $this->_determinePersonTypeFromCustomerGroupId($customerGroupId);
	}

	private function _findCustomerGroupId($orderData) {
		return intval($orderData['customer_group_id'])
			? intval($orderData['customer_group_id'])
			: 0;
	}

	private function _extractAdditionalBillingFields($orderData) {
		$customFields = array();

		if (!empty($orderData['payment_custom_field'])) {
			$customFields = $orderData['payment_custom_field'];
		}

		return $this->_extractAdditionalBillingFieldsFromCustomFields($customFields);
	}
}
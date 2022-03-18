<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithInputSanitization;
use CielIntegration\WithLogging;

class ControllerExtensionCielCustomFieldsSync extends CielController {
	use WithLogging;
	use WithCielIntegration;
	use WithInputSanitization;

	public function afterAdd(&$route, &$args, &$output) {
		$customerId = intval($output);
		$customerData = $args[0];

		$this->_syncCustomerCustomFields($customerId, 
			$customerData);
	}

	public function afterEdit(&$route, &$args, &$output) {
		$customerId = $args[0];
		$customerData = $args[1];

		$this->_syncCustomerCustomFields($customerId, 
			$customerData);
	}

	private function _syncCustomerCustomFields($customerId, $customerData) {
		$this->_logDebug('Intercepted customer save for customer id <' . $customerId . '>.');

		$remotePartnerModel = $this->_getRemotePartnerModel();
		$remoteCustomerBillingInformation = $this->_extractRemoteCustomerBillingInformation($customerData);

		$remotePartnerModel->setBillingInformation($customerId, 
			$remoteCustomerBillingInformation);
	}

	private function _extractRemoteCustomerBillingInformation($customerData) {
		$billingType = $this->_determineCustomerPersonType($customerData);
		$billingInformation = $this->_extractAdditionalBillingFields($customerData);

		return array_merge($billingInformation, array(
			'billing_type' => $billingType
		));
	}

	private function _determineCustomerPersonType($customerData) {
		$customerGroupId = $this->_findCustomerGroupId($customerData);
		return $this->_determinePersonTypeFromCustomerGroupId($customerGroupId);
	}

	private function _findCustomerGroupId($customerData) {
		return intval($customerData['customer_group_id'])
			? intval($customerData['customer_group_id'])
			: 0;
	}

	private function _extractAdditionalBillingFields($customerData) {
		$customFields = array();
		$defaultAddress = $this->_findDefaultAddress($customerData);

		if ($defaultAddress != null && !empty($defaultAddress['custom_field'])) {
			$customFields = $defaultAddress['custom_field'];
		}

		return $this->_extractAdditionalBillingFieldsFromCustomFields($customFields);
	}

	private function _findDefaultAddress($customerData) {
		$defaultAddress = null;
		$addresses = isset($customerData) && is_array($customerData['address'])
			? $customerData['address']
			: array();

		if (!empty($addresses)) {
			$defaultAddress = reset($addresses);
			foreach ($addresses as $addr) {
				if ($addr['default'] == 1) {
					$defaultAddress = $addr;
					break;
				}
			}
		}

		return $defaultAddress;
	}
}
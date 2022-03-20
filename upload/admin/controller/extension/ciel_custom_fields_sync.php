<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Partner\CustomerCustomFieldsSyncService;
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
		$this->_getCustomerCustomFieldsSyncService()
			->syncCustomerCustomFields($customerId, 
				$customerData);
	}

	private function _getCustomerCustomFieldsSyncService() {
		return new CustomerCustomFieldsSyncService($this->registry);
	}
}
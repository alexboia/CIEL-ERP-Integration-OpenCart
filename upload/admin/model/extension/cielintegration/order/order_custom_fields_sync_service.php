<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\Order\Model\LocalOrder;
    use CielIntegration\Integration\Admin\Partner\CustomerCustomFieldsSyncService;
    use CielIntegration\Integration\Admin\WithCielIntegration;
    use CielIntegration\WithLogging;

	class OrderCustomFieldsSyncService extends IntegrationService {
		use WithCielIntegration;
		use WithLogging;

		public function syncOrderCustomerCustomFields($orderId, $orderData) {
			$this->_logDebug('Syncing order custom address fields for order id <' . $orderId . '>.');

			if (empty($orderData)) {
				$this->_logDebug('Null or empty order data given. Retrieving by ID...');
				$orderData = $this->_getOrderData($orderId);
				if (empty($orderData)) {
					$this->_logDebug('No order data found. Exiting...');
					return;
				}
			}
		
			$remoteOrderModel = $this->_getRemoteOrderModel();
			$customerId = $this->_extractOrderCustomerId($orderData);
			$remoteOrderCustomerBillingInformation = $this->_extractRemoteOrderCustomerBillingInformation($orderData);
	
			$remoteOrderModel->setBillingInformation($orderId,
				$customerId,
				$remoteOrderCustomerBillingInformation);

			if ($customerId > 0) {
				$this->_getCustomerCustomFieldsSyncService()
					->syncCustomerCustomFields($customerId,
						null);
			}
		}

		private function _getOrderData($orderId) {
			return $this->_getLocalOrderModel()
				->getOrder($orderId);
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

		private function _getLocalOrderModel() {
			return new LocalOrder($this->registry);
		}

		private function _getCustomerCustomFieldsSyncService() {
			return new CustomerCustomFieldsSyncService($this->registry);
		}
	}
}
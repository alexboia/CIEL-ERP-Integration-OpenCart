<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\Partner\PartnerResolver;

	class OrderPartnerResolver extends IntegrationService {
		private $_orderResolver;

		private $_partnerResolver;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
			$this->_orderResolver = 
				new OrderResolver($registry);
			$this->_partnerResolver = 
				new PartnerResolver($registry);
		}

		public function isOrderCustomerConnectedToCielErp($orderId) {
			return $this->_partnerResolver
					->isConnectedToCielErp($orderId)
				|| $this->_orderResolver
					->isOrderCustomerConnectedToCielErp($orderId);
		}

		public function getOrderCustomerRemotePartnerBindingInformation($orderId, $customerId) {
			$orderPartnerBindingInformation = $this->_orderResolver
				->getOrderCustomerRemotePartnerBindingInformation($orderId);

			if (empty($orderPartnerBindingInformation)) {
				$orderPartnerBindingInformation = $this->_orderResolver
					->getEmptyOrderCustomerRemotePartnerBindingInformation();
			}

			$customerPartnerBindingInformation = $this->_partnerResolver
				->getRemotePartnerBindingInformation($customerId);

			if (empty($customerPartnerBindingInformation)) {
				$customerPartnerBindingInformation = array();
			}

			return myc_merge_if_value_empty($orderPartnerBindingInformation, 
				$customerPartnerBindingInformation);
		}

		public function getOrderCustomerBillingAddressInformation($orderId, $customerId) {
			$orderBillingAddressInformation = $this->_orderResolver
				->getOrderCustomerBillingAddressInformation($orderId);

			if (empty($orderBillingAddressInformation)) {
				$orderBillingAddressInformation = $this->_orderResolver
					->getEmptyOrderCustomerBillingAddressInformation();
			}

			$customerBillingAddressInformation = $this->_partnerResolver
				->getCustomerBillingAddressInformation($customerId);

			if (empty($customerBillingAddressInformation)) {
				$customerBillingAddressInformation = array();
			}

			return myc_merge_if_value_empty($orderBillingAddressInformation, 
				$customerBillingAddressInformation);
		}

	}
}
<?php
namespace CielIntegration\Integration\Admin\Partner {

    use CielIntegration\Integration\Admin\IntegrationService;
    use InvalidArgumentException;

	class RemotePartnerToLocalCustomerMarshaller extends IntegrationService {	
		/**
		 * @var int
		 */
		private $_customerId;

		/**
		 * @var PartnerUpdateService
		 */
		private $_customerUpdateService;

		public function __construct($customerId, \Registry $registry) {
			parent::__construct($registry);

			if (empty($customerId) && $customerId != 0) {
				throw new InvalidArgumentException('Customer id may not be empty.');
			}

			$this->_customerId = $customerId;
			$this->_customerUpdateService = 
				new PartnerUpdateService($customerId, 
					$registry);
		}

		public function setCustomerBindingInformation(array $remotePartnerData, array $remoteShopBillingAddressData) {
			$this->_customerUpdateService->setCustomerBindingInformation($remotePartnerData, 
				$remoteShopBillingAddressData);
		}

		public function setOrderCustomerBindingInformation($orderId, array $remotePartnerData, array $remoteShopBillingAddressData) {
			$this->_customerUpdateService->setOrderCustomerBindingInformation($orderId, 
				$remotePartnerData, 
				$remoteShopBillingAddressData);
		}

		public function importOrderCustomerBindingInformation($orderId) { 
			$this->_customerUpdateService
				->importOrderCustomerBindingInformation($orderId);
		}

		public function getCustomerId() {
			return $this->_customerId;
		}
	}
}
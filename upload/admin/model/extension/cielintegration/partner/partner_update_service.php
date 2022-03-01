<?php
namespace CielIntegration\Integration\Admin\Partner {

    use CielIntegration\Integration\Admin\IntegrationService;
    use InvalidArgumentException;

	class PartnerUpdateService extends IntegrationService {
		/**
		 * @var int
		 */
		private $_customerId;

		public function __construct($customerId, \Registry $registry) {
			parent::__construct($registry);

			if (empty($customerId)) {
				throw new InvalidArgumentException('Customer id may not be empty.');
			}

			$this->_customerId = $customerId;
		}

		public function setCustomerBindingInformation(array $remotePartnerData, array $remoteShopBillingAddressData) {
			$remotePartnerCode = $this->_getRemotePartnerCode($remotePartnerData);
			$addrWorksiteId = $this->_getRemoteBillingAddrPartnerWorksiteId($remoteShopBillingAddressData);

			$remotePartnerModel = $this->_getRemotePartnerModel();
			$remotePartnerModel->setBindingInformation($this->_customerId, 
				$remotePartnerCode, 
				$addrWorksiteId);
		}

		private function _getRemotePartnerCode(array $remotePartnerData) {
			return $remotePartnerData['Code'];
		}

		private function _getRemoteBillingAddrPartnerWorksiteId(array $remoteShopBillingAddressData) {
			$billingAddrPartnerWorksiteId = null;
			if (!empty($remoteShopBillingAddressData)
				&& !empty($remoteShopBillingAddressData['PartnerWorksiteId'])) {
				$billingAddrPartnerWorksiteId = $remoteShopBillingAddressData['PartnerWorksiteId'];
			}

			return $billingAddrPartnerWorksiteId;
		}

		public function setOrderCustomerBindingInformation($orderId, array $remotePartnerData, array $remoteShopBillingAddressData) {
			$remotePartnerCode = $this->_getRemotePartnerCode($remotePartnerData);
			$addrWorksiteId = $this->_getRemoteBillingAddrPartnerWorksiteId($remoteShopBillingAddressData);

			$remoteOrderModel = $this->_getRemoteOrderModel();
			$remoteOrderModel->setCustomerBindingInformation($orderId, 
				$this->_customerId, 
				$remotePartnerCode, 
				$addrWorksiteId);
		}

		public function importOrderCustomerBindingInformation($orderId) {
			$remotePartnerModel = $this->_getRemotePartnerModel();
			$customerData = $remotePartnerModel->getByCustomerId($this->_customerId);

			if (!empty($customerData)) {
				$remoteOrderModel = $this->_getRemoteOrderModel();
				$remoteOrderModel->setCustomerBindingInformation($orderId, 
					$this->_customerId, 
					$customerData['remote_partner_code'], 
					$customerData['remote_partner_addr_worksite_id']);
			}
		}
	}
}
<?php
namespace CielIntegration\Integration\Admin\Partner {

    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\Partner\Model\LocalCustomer;

	class PartnerResolver extends IntegrationService {
		public function getCustomer($customerId) {
			if (empty($customerId)) {
				return null;
			}

			return $this->_getLocalCustomerModel()
				->getCustomer($customerId);
		}

		/**
		 * @return LocalCustomer
		 */
		private function _getLocalCustomerModel() {
			return new LocalCustomer($this->registry);
		}

		public function getCustomerAddress($addressId) {
			if (empty($addressId)) {
				return null;
			}

			return $this->_getLocalCustomerModel()
				->getAddress($addressId);
		}

		public function getCustomerIdForOrder($orderId) {
			if (empty($orderId)) {
				return null;
			}

			return $this->_getLocalCustomerModel()
				->getCustomerIdForOrder($orderId);
		}

		public function customerExists($customerId) {
			if (empty($customerId)) {
				return false;
			}

			return $this->_getLocalCustomerModel()
				->customerExists($customerId);
		}

		public function getCustomerBillingAddressInformation($customerId) {
			if (empty($customerId)) {
				return null;
			}

			$remotePartnerData = $this->_getRemotePartnerModel()
				->getByCustomerId($customerId);

			if (empty($remotePartnerData)) {
				return null;
			}

			return array(
				'address_billing_type' 
					=> $remotePartnerData['billing_type'],
				'address_tax_attribute' 
					=> $remotePartnerData['billing_company_tax_attribute'],
				'address_tax_code' 
					=> $remotePartnerData['billing_company_tax_code'],
				'address_bank' 
					=> $remotePartnerData['billing_company_bank'],
				'address_iban' 
					=> $remotePartnerData['billing_company_iban'],
				'address_trade_reg_number' 
					=> $remotePartnerData['billing_company_trade_register_number']
			);
		}

		public function getRemotePartnerBindingInformation($customerId) {
			if (empty($customerId)) {
				return null;
			}

			$remotePartnerData = $this->_getRemotePartnerModel()
				->getByCustomerId($customerId);

			return $this->_extractRemotePartnerBindingInformation($remotePartnerData);
		}

		private function _extractRemotePartnerBindingInformation($remotePartnerData) {
			return !empty($remotePartnerData) && !empty($remotePartnerData['remote_partner_code'])
				? array(
					'remote_partner_code' 
						=> $remotePartnerData['remote_partner_code'],
					'remote_partner_addr_worksite_id' 
						=> $remotePartnerData['remote_partner_addr_worksite_id']
				)
				: null;
		}

		public function getEmptyRemotePartnerBindingInformation($customerId) {
			return array(
				'remote_partner_code' => null,
				'remote_partner_addr_worksite_id' => null
			);
		}

		public function isConnectedToCielErp($customerId) {
			if (empty($customerId)) {
				return false;
			}

			return $this->_getRemotePartnerModel()
				->isConnectedToCielErp($customerId);
		}

		public function areConnectedToCielErp(array $customerIds) {
			if (empty($customerIds)) {
				return array();
			}

			return $this->_getRemotePartnerModel()	
				->areConnectedToCielErp($customerIds);
		}

		/**
		 * @return \DB
		 */
		protected function _getDb() {
			return $this->db;
		}
	}
}
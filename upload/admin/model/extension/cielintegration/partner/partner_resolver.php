<?php
namespace CielIntegration\Integration\Admin\Partner {

    use CielIntegration\Integration\Admin\IntegrationService;
    use ModelCustomerCustomer;

	class PartnerResolver extends IntegrationService {
		public function getCustomer($customerId) {
			if (empty($customerId)) {
				return null;
			}

			$customerModel = $this->_getCustomerModel();
			return $customerModel
				->getCustomer($customerId);
		}

		/**
		 * @return ModelCustomerCustomer
		 */
		private function _getCustomerModel() {
			$this->load->model('customer/customer');
			return $this->model_customer_customer;
		}

		public function getCustomerAddress($addressId) {
			if (empty($addressId)) {
				return null;
			}

			$customerModel = $this->_getCustomerModel();
			return $customerModel
				->getAddress($addressId);
		}

		public function getCustomerIdForOrder($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT customer_id FROM `' . DB_PREFIX . 'order` WHERE order_id = "' . intval($orderId) . '"');

			$row = $result->row;
			return !empty($row) && !empty($row['customer_id'])
				? intval($row['customer_id'])
				: 0;
		}

		public function customerExists($customerId) {
			if (empty($customerId)) {
				return false;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT COUNT(customer_id) as customer_count FROM `' . DB_PREFIX . 'product` WHERE customer_id = "' . intval($customerId) . '"');
			
			$row = $result->row;
			return !empty($row) && !empty($row['customer_count'])
				? intval($row['customer_count']) > 0
				: false;
		}

		public function getCustomerBillingAddressInformation($customerId) {
			if (empty($customerId)) {
				return null;
			}

			$remotePartnerModel = $this->_getRemotePartnerModel();
			$remotePartnerData = $remotePartnerModel->getByCustomerId($customerId);
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

			$remotePartnerModel = $this->_getRemotePartnerModel();
			$remotePartnerData = $remotePartnerModel->getByCustomerId($customerId);

			return !empty($remotePartnerData) && !($remotePartnerData['remote_partner_code'])
				? array(
					'remote_partner_code' 
						=> $remotePartnerData['remote_partner_code'],
					'remote_partner_addr_worksite_id' 
						=> $remotePartnerData['remote_partner_addr_worksite_id']
				)
				: null;
		}

		public function isConnectedToCielErp($customerId) {
			$bindingInformation = $this->getCustomerBillingAddressInformation($customerId);
			return !empty($bindingInformation) 
				&& !empty($bindingInformation['remote_partner_code']);
		}

		/**
		 * @return \DB
		 */
		protected function _getDb() {
			return $this->db;
		}
	}
}
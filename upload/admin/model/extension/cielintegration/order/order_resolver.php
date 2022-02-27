<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;
    use ModelSaleOrder;

	class OrderResolver extends IntegrationService {
		public function getOrder($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$orderModel = $this->_getOrderModel();
			return $orderModel->getOrder($orderId);
		}
		
		/**
		 * @return ModelSaleOrder
		 */
		private function _getOrderModel() {
			$this->load->model('sale/order');
			return $this->model_sale_order;
		}

		public function getOrderCustomerBillingAddressInformation($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$remoteOrderModel = $this->_getRemoteOrderModel();
			$remoteOrderData = $remoteOrderModel->getByOrderId($orderId);
			if (empty($remoteOrderData)) {
				return null;
			}

			return array(
				'address_billing_type' 
					=> $remoteOrderData['billing_type'],
				'address_tax_attribute' 
					=> $remoteOrderData['billing_company_tax_attribute'],
				'address_tax_code' 
					=> $remoteOrderData['billing_company_tax_code'],
				'address_bank' 
					=> $remoteOrderData['billing_company_bank'],
				'address_iban' 
					=> $remoteOrderData['billing_company_iban'],
				'address_trade_reg_number' 
					=> $remoteOrderData['billing_company_trade_register_number']
			);
		}

		public function getEmptyOrderCustomerBillingAddressInformation() {
			return array(
				'address_billing_type' => '',
				'address_tax_attribute' => '',
				'address_tax_code' => '',
				'address_bank' => '',
				'address_iban' => '',
				'address_trade_reg_number' => ''
			);
		}

		public function getRemoteOrderPartnerBindingInformation($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$remoteOrderModel = $this->_getRemoteOrderModel();
			$remoteOrderData = $remoteOrderModel->getByOrderId($orderId);

			return !empty($remoteOrderData) && !($remoteOrderData['remote_partner_code'])
				? array(
					'remote_partner_code' 
						=> $remoteOrderData['remote_partner_code'],
					'remote_partner_addr_worksite_id' 
						=> $remoteOrderData['remote_partner_addr_worksite_id']
				)
				: null;
		}

		public function getEmptyRemoteOrderPartnerBindingInformation() {
			return array(
				'remote_partner_code' => null,
				'remote_partner_addr_worksite_id' => null
			);
		}
	}
}
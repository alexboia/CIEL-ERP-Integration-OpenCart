<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;
    use ModelSaleOrder;

	class OrderResolver extends IntegrationService {
		public function orderExists($orderId) {
			if (empty($orderId)) {
				return false;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT COUNT(order_id) as order_count FROM `' . DB_PREFIX . 'order` WHERE order_id = "' . intval($orderId) . '"');
			
			$row = $result->row;
			return !empty($row) && !empty($row['order_count'])
				? intval($row['order_count']) > 0
				: false;
		}

		public function getOrder($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$orderModel = $this->_getOrderModel();
			return $orderModel
				->getOrder($orderId);
		}
		
		/**
		 * @return ModelSaleOrder
		 */
		private function _getOrderModel() {
			$this->load->model('sale/order');
			return $this->model_sale_order;
		}

		public function getOrderProducts($orderId) {
			if (empty($orderId)) {
				return array();
			}

			$orderModel = $this->_getOrderModel();
			return $orderModel
				->getOrderProducts($orderId);
		}

		public function getOrderTotals($orderId) {
			if (empty($orderId)) {
				return array();
			}

			$orderModel = $this->_getOrderModel();
			return $orderModel
				->getOrderTotals($orderId);
		}

		public function isRemoteDocumentIssuedForOrder($orderId) {
			if (empty($orderId)) {
				return false;
			}

			$remoteDocumentData = $this->lookupRemoteDocumentData($orderId);
			return $this->_isRemoteDocumentIssued($remoteDocumentData);
		}

		private function _isRemoteDocumentIssued($remoteDocumentData) {
			return $remoteDocumentData != null
				&& !empty($remoteDocumentData['id']);
		}

		public function areRemoteDocumentsIssuedForOrders(array $orderIds) {
			if (empty($orderIds)) {
				return array();
			}

			$remoteDocumentsStatus = array();
			$remoteDocumentsData = $this->lookupRemoteDocumentsData($orderIds);

			foreach ($remoteDocumentsData as $id => $remoteDocumentData) {
				$remoteDocumentsStatus[$id] = $this->_isRemoteDocumentIssued($remoteDocumentData);
			}
			
			return $remoteDocumentsStatus;
		}

		public function lookupRemoteDocumentData($orderId) {
			if (empty($orderId)) {
				return false;
			}

			$remoteOrderData = $this->_getRemoteOrderModel()
				->getByOrderId($orderId);

			if (!empty($remoteOrderData)) {
				return $this->_getRemoteDocumentData($remoteOrderData);
			} else {
				return null;
			}
		}

		private function _getRemoteDocumentData($remoteOrderData) {
			return array(
				'id' => $remoteOrderData['remote_document_id'],
				'type' => $remoteOrderData['remote_document_type']
			);
		}

		public function lookupRemoteDocumentsData(array $orderIds) {
			if (empty($orderIds)) {
				return array();
			}

			$documentsData = array_fill_keys($orderIds, 
				$this->getEmptyRemoteDocumentData());

			$remoteOrdersData = $this->_getRemoteOrderModel()
				->getByOrderIds($orderIds);

			foreach ($remoteOrdersData as $remoteOrderData) {
				$orderId = intval($remoteOrderData['order_id']);
				$documentsData[$orderId] = $this->_getRemoteDocumentData($remoteOrderData);
			}

			return $documentsData;
		}

		public function getEmptyRemoteDocumentData() {
			return array(
				'id' => null,
				'type' => null
			);
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

		public function getOrderCustomerRemotePartnerBindingInformation($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$remoteOrderModel = $this->_getRemoteOrderModel();
			$remoteOrderData = $remoteOrderModel->getByOrderId($orderId);

			return !empty($remoteOrderData) && !empty($remoteOrderData['remote_partner_code'])
				? array(
					'remote_partner_code' 
						=> $remoteOrderData['remote_partner_code'],
					'remote_partner_addr_worksite_id' 
						=> $remoteOrderData['remote_partner_addr_worksite_id']
				)
				: null;
		}

		public function isOrderCustomerConnectedToCielErp($orderId) {
			$bindingInformation = $this->getOrderCustomerRemotePartnerBindingInformation($orderId);
			return !empty($bindingInformation) 
				&& !empty($bindingInformation['remote_partner_code']);
		}

		public function getEmptyOrderCustomerRemotePartnerBindingInformation() {
			return array(
				'remote_partner_code' => null,
				'remote_partner_addr_worksite_id' => null
			);
		}

		/**
		 * @return \DB
		 */
		protected function _getDb() {
			return $this->db;
		}
	}
}
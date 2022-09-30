<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\Order\Model\LocalOrder;

	class OrderResolver extends IntegrationService {
		public function orderExists($orderId) {
			if (empty($orderId)) {
				return false;
			}

			$orderModel = $this->_getLocalOrderModel();
			return $orderModel
				->orderExists($orderId);
		}

		public function getOrder($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$orderModel = $this->_getLocalOrderModel();
			return $orderModel
				->getOrder($orderId);
		}
		
		/**
		 * @return LocalOrder
		 */
		private function _getLocalOrderModel() {
			return new LocalOrder($this->registry);
		}

		public function getOrderProducts($orderId) {
			if (empty($orderId)) {
				return array();
			}

			$orderModel = $this->_getLocalOrderModel();
			return $orderModel
				->getOrderProducts($orderId);
		}

		public function getOrderTotals($orderId) {
			if (empty($orderId)) {
				return array();
			}

			$orderModel = $this->_getLocalOrderModel();
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

			$remoteOrderData = $this->_getRemoteOrderByOrderId($orderId);
			if (empty($remoteOrderData)) {
				return null;
			}

			return array(
				'address_billing_type' => $remoteOrderData['billing_type'],
				'address_tax_attribute' => !empty($remoteOrderData['billing_company_tax_attribute'])
					? $remoteOrderData['billing_company_tax_attribute']
					: null,
				'address_tax_code' => !empty($remoteOrderData['billing_company_tax_code'])
					? $remoteOrderData['billing_company_tax_code']
					: null,
				'address_bank' => !empty($remoteOrderData['billing_company_bank'])
					? $remoteOrderData['billing_company_bank']
					: null,
				'address_iban' => !empty($remoteOrderData['billing_company_iban'])
					? $remoteOrderData['billing_company_iban']
					: null,
				'address_trade_reg_number' => !empty($remoteOrderData['billing_company_trade_register_number'])
					? $remoteOrderData['billing_company_trade_register_number']
					: null
			);
		}

		private function _getRemoteOrderByOrderId($orderId) {
			$remoteOrderData = $this
				->_getRemoteOrderModel()
				->getByOrderId($orderId);

			if (!empty($remoteOrderData)) {
				foreach ($remoteOrderData as $key => $value) {
					$remoteOrderData[$key] = trim($value);
				}
			}

			return $remoteOrderData;
		}

		public function getEmptyOrderCustomerBillingAddressInformation() {
			return array(
				'address_billing_type' => '',
				'address_tax_attribute' => null,
				'address_tax_code' => null,
				'address_bank' => null,
				'address_iban' => null,
				'address_trade_reg_number' => null
			);
		}

		public function getOrderCustomerRemotePartnerBindingInformation($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$remoteOrderData = $this->_getRemoteOrderByOrderId($orderId);
			if (empty($remoteOrderData)) {
				return null;
			}

			return $this->_extractRemotePartnerBindingInformation($remoteOrderData);
		}

		private function _extractRemotePartnerBindingInformation(array $remoteOrderData) {
			return !empty($remoteOrderData['remote_partner_code'])
				? array(
					'remote_partner_code'  => $remoteOrderData['remote_partner_code'],
					'remote_partner_addr_worksite_id' => !empty($remoteOrderData['remote_partner_addr_worksite_id'])
						? $remoteOrderData['remote_partner_addr_worksite_id']
						: null
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
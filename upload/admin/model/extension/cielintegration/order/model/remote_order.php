<?php
namespace CielIntegration\Integration\Admin\Order\Model {
    use CielIntegration\Integration\Admin\IntegrationModel;

	class RemoteOrder extends IntegrationModel {
		const BASE_TABLE_NAME = 'mycciel_oc_remote_order';

		const ID_COLUMN_KEY = 'order_id';

		public function add(array $remoteOrderInfo) {
			return $this->_add($remoteOrderInfo);
		}

		public function update(array $remoteOrderInfo) {
			return $this->_update($remoteOrderInfo);
		}

		public function setRemoteDocumentData($orderId, $documentId, $documentType) {
			if (empty($orderId)) {
				return;
			}

			$orderData = $this->getByOrderId($orderId);
			if (empty($orderId)) {
				return;
			}

			$orderData = array_merge($orderData, array(
				'remote_document_id' => $documentId,
				'remote_document_type' => $documentType
			));

			$this->update($orderData);
		}

		public function clearRemoteDocumentData($orderId) {
			if (empty($orderId)) {
				return;
			}

			$orderData = $this->getByOrderId($orderId);
			if (empty($orderId)) {
				return;
			}

			$orderData = array_merge($orderData, array(
				'remote_document_id' => null,
				'remote_document_type' => null
			));

			$this->update($orderData);
		}

		public function setCustomerBindingInformation($orderId, $customerId, $remoteCode, $billingAddrPartnerWorksiteId) {
			$addRecord = false;
			$orderData = $this->getByOrderId($orderId);
			if (empty($orderData)) {
				$addRecord = true;
				$orderData = array(
					'order_id' => $orderId
				);
			}

			$orderData = array_merge($orderData, array(
				'customer_id' => 
					$customerId,
				'remote_partner_code' => 
					$remoteCode,
				'remote_partner_addr_worksite_id' => 
					$billingAddrPartnerWorksiteId
			));

			if ($addRecord) {
				$this->add($orderData);
			} else {
				$this->update($orderData);
			}
		}

		public function setBillingInformation($orderId, $customerId, $billingInformation) {
			$addRecord = false;
			$orderData = $this->getByOrderId($orderId);
			if (empty($orderData)) {
				$addRecord = true;
				$orderData = array(
					'order_id' => $orderId
				);
			}

			$orderData = array_merge($orderData, array(
				'customer_id' => 
					$customerId,
				'billing_type' => !empty($billingInformation['billing_type'])
					? $billingInformation['billing_type'] 
					: null,
				'billing_company_tax_attribute' => !empty($billingInformation['billing_company_tax_attribute'])
					? $billingInformation['billing_company_tax_attribute']
					: '',
				'billing_company_trade_register_number' => !empty($billingInformation['billing_company_trade_register_number'])
					? $billingInformation['billing_company_trade_register_number']
					: '',
				'billing_company_tax_code' => !empty($billingInformation['billing_company_tax_code'])
					? $billingInformation['billing_company_tax_code']
					: '',
				'billing_company_iban' => !empty($billingInformation['billing_company_iban'])
					? $billingInformation['billing_company_iban']
					: '',
				'billing_company_bank' => !empty($billingInformation['billing_company_bank'])
					? $billingInformation['billing_company_bank']
					: ''
			));

			if ($addRecord) {
				$this->add($orderData);
			} else {
				$this->update($orderData);
			}
		}

		public function addAll(array $remoteOrdersInfos) {
			if (empty($remoteOrdersInfos)) {
				return;
			}

			return $this->_addAll($remoteOrdersInfos);
		}

		public function removeByOrderId($orderId) {
			return $this->_removeByModelId($orderId);
		}

		public function removeAll() {
			return $this->_removeAll();
		}

		public function getByOrderId($orderId) {
			return $this->_getOneByModelId($orderId);
		}

		public function getByOrderIds(array $orderIds) {
			if (empty($orderIds)) {
				return array();
			}

			return $this->_getAllByModelIds($orderIds);
		}

		public function countRemoteOrders() {
			$db = $this->_getDb();
			$query = 'SELECT COUNT(1) AS `remote_order_count`
				FROM `' . $this->_getFullTableName() . '` `ro`
				WHERE `ro`.`mycciel_oc_remote_document_id` IS NOT NULL
					AND `ro`.`mycciel_oc_remote_document_id` > 0';
			
			$result = $db->query($query);
			if (!empty($result) && !empty($result->row)) {
				$row = $result->row;
				return isset($row['remote_order_count']) 
					? intval($row['remote_order_count']) 
					: 0;
			} else {
				return 0;
			}
		}

		protected function _getFullTableName() {
			return DB_PREFIX . $this->_getTableName();
		}

		protected function _getTableName() {
			return self::BASE_TABLE_NAME;
		}

		protected function _getIdColumnKey() {
			return self::ID_COLUMN_KEY;
		}
	}
}
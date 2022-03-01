<?php
namespace CielIntegration\Integration\Admin\Order\Model {
    use CielIntegration\Integration\Admin\IntegrationModel;

	class RemoteOrder extends IntegrationModel {
		const BASE_TABLE_NAME = 'mycciel_oc_remote_order';

		const ID_COLUMN_KEY = 'oc_order_id';

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
			$orderData = $this->getByOrderId($orderId);
			if (empty($orderData)) {
				$orderData = array(
					'order_id' => $orderId
				);
			}

			$orderData = array_merge($orderData, array(
				'customer_id' => $customerId,
				'remote_partner_code' => $remoteCode,
				'remote_partner_addr_worksite_id' => $billingAddrPartnerWorksiteId
			));

			$this->update($orderData);
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

		protected function _getTableName() {
			return self::BASE_TABLE_NAME;
		}

		protected function _getIdColumnKey() {
			return self::ID_COLUMN_KEY;
		}
	}
}
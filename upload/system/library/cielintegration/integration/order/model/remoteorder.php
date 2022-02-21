<?php
namespace CielIntegration\Integration\Order\Model {
    use CielIntegration\Integration\IntegrationModel;

	class RemoteOrder extends IntegrationModel {
		const BASE_TABLE_NAME = 'mycciel_oc_remote_order';

		const ID_COLUMN_KEY = 'oc_order_id';

		public function add(array $remoteOrderInfo) {
			return $this->_add($remoteOrderInfo);
		}

		public function update(array $remoteOrderInfo) {
			return $this->_update($remoteOrderInfo);
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
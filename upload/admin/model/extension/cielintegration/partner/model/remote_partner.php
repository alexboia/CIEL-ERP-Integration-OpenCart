<?php
namespace CielIntegration\Integration\Admin\Partner\Model {
    use CielIntegration\Integration\Admin\IntegrationModel;

	class RemotePartner extends IntegrationModel {
		const BASE_TABLE_NAME = 'mycciel_oc_remote_customer';

		const ID_COLUMN_KEY = 'customer_id';

		public function add(array $remotePartnerInfo) {
			return $this->_add($remotePartnerInfo);
		}

		public function update(array $remotePartnerInfo) {
			return $this->_update($remotePartnerInfo);
		}

		public function setBindingInformation($customerId, $remoteCode, $billingAddrPartnerWorksiteId) {
			$addRecord = false;
			$productData = $this->getByCustomerId($customerId);
			if (empty($productData)) {
				$addRecord = true;
				$productData = array(
					'customer_id' => $customerId
				);
			}

			$productData = array_merge($productData, array(
				'remote_partner_code' => $remoteCode,
				'remote_partner_addr_worksite_id' => $billingAddrPartnerWorksiteId
			));

			if ($addRecord) {
				$this->add($productData);
			} else {
				$this->update($productData);
			}
		}

		public function isConnectedToCielErp($customerId) {
			$record = $this->_getOneByModelId($customerId);
			return !empty($record) 
				&& !empty($record['remote_partner_code']);
		}

		public function areConnectedToCielErp(array $customerIds) {
			$status = array_fill_keys($customerIds, false);
			$records = $this->_getAllByModelIds($customerIds);

			foreach ($records as $r) {
				$customerId = $r['customer_id'];
				$status[$customerId] = !empty($r['remote_partner_code']);
			}

			return $status;
		}

		public function addAll(array $remotePartnersInfos) {
			if (empty($remotePartnersInfos)) {
				return;
			}

			return $this->_addAll($remotePartnersInfos);
		}

		public function removeByCustomerId($customerId) {
			return $this->_removeByModelId($customerId);
		}

		public function removeAll() {
			return $this->_removeAll();
		}

		public function getByCustomerId($customerId) {
			return $this->_getOneByModelId($customerId);
		}

		public function getByCustomerIds(array $customerIds) {
			if (empty($customerIds)) {
				return array();
			}

			return $this->_getAllByModelIds($customerIds);
		}

		protected function _getTableName() {
			return self::BASE_TABLE_NAME;
		}

		protected function _getIdColumnKey() {
			return self::ID_COLUMN_KEY;
		}
	}
}
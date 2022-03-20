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
			$customerData = $this->getByCustomerId($customerId);
			if (empty($customerData)) {
				$addRecord = true;
				$customerData = array(
					'customer_id' => $customerId
				);
			}

			$customerData = array_merge($customerData, array(
				'remote_partner_code' => $remoteCode,
				'remote_partner_addr_worksite_id' => $billingAddrPartnerWorksiteId
			));

			if ($addRecord) {
				$this->add($customerData);
			} else {
				$this->update($customerData);
			}
		}

		public function setBillingInformation($customerId, $billingInformation) {
			$addRecord = false;
			$customerData = $this->getByCustomerId($customerId);
			if (empty($customerData)) {
				$addRecord = true;
				$customerData = array(
					'customer_id' => $customerId
				);
			}

			$customerData = array_merge($customerData, array(
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
				$this->add($customerData);
			} else {
				$this->update($customerData);
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
<?php

use Ciel\Api\Data\DocumentIssueType;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithInputSanitization;
use CielIntegration\WithLogging;

class ControllerExtensionCielCustomFieldsSync extends CielController {
	use WithLogging;
	use WithCielIntegration;
	use WithInputSanitization;

	public function afterAdd(&$route, &$args, &$output) {
		$customerId = intval($output);
		$customerData = $args[0];

		$this->_syncCustomerCustomFields($customerId, 
			$customerData);
	}

	public function afterEdit(&$route, &$args, &$output) {
		$customerId = $args[0];
		$customerData = $args[1];

		$this->_syncCustomerCustomFields($customerId, 
			$customerData);
	}

	private function _syncCustomerCustomFields($customerId, $customerData) {
		$this->_logDebug('Intercepted customer save for customer id <' . $customerId . '>.');

		$remotePartnerModel = $this->_getRemotePartnerModel();
		$remoteCustomerBillingInformation = $this->_extractRemoteCustomerBillingInformation($customerData);
		$this->_logDebug(print_r($remoteCustomerBillingInformation, true));

		$remotePartnerModel->setBillingInformation($customerId, 
			$remoteCustomerBillingInformation);
	}

	private function _extractRemoteCustomerBillingInformation($customerData) {
		$billingInformation = $this->_extractAdditionalBillingFields($customerData);
		$billingInformation = array_merge($billingInformation, array(
			'billing_type' => $this->_determineCustomerPersonType($customerData)
		));

		return $billingInformation;
	}

	private function _determineCustomerPersonType($customerData) {
		$workflow = $this->_getWorkflow();
		$personType = DocumentIssueType::Person;
		$customerGroupId = $this->_findCustomerGroupId($customerData);

		if ($customerGroupId == $workflow->getPJPersonTypeCustomerGroupId()) {
			$personType = DocumentIssueType::Company;
		}

		return $personType;
	}

	private function _findCustomerGroupId($customerData) {
		return intval($customerData['customer_group_id'])
			? intval($customerData['customer_group_id'])
			: 0;
	}

	private function _extractAdditionalBillingFields($customerData) {
		$data = array(
			'billing_company_tax_attribute' => null,
			'billing_company_tax_code' => null,
			'billing_company_trade_register_number' => null,
			'billing_company_bank' => null,
			'billing_company_iban' => null
		);

		$workflow = $this->_getWorkflow();
		$defaultAddress = $this->_findDefaultAddress($customerData);
		$this->_logDebug(print_r($defaultAddress, true));
		if ($defaultAddress != null && !empty($defaultAddress['custom_field'])) {
			foreach ($defaultAddress['custom_field'] as $id => $postedValue) {
				if ($id == $workflow->getVatCodeCustomFieldId()) {
					$vatCodeParts = myc_extract_vat_code_parts($postedValue);
					$data['billing_company_tax_attribute'] = $vatCodeParts['attribute'];
					$data['billing_company_tax_code'] = $vatCodeParts['code'];
				} else if ($id == $workflow->getRegComNumberCustomFieldId()) {
					$data['billing_company_trade_register_number'] = $postedValue;
				} else if ($id == $workflow->getBankAccountCustomFieldId()) {
					$data['billing_company_iban'] = $postedValue;
				} else if ($id == $workflow->getBankNameCustomFieldId()) {
					$data['billing_company_bank'] = $postedValue;
				}
			}
		}

		return $data;
	}

	private function _findDefaultAddress($customerData) {
		$defaultAddress = null;
		$addresses = isset($customerData) && is_array($customerData['address'])
			? $customerData['address']
			: array();

		if (!empty($addresses)) {
			$defaultAddress = reset($addresses);
			foreach ($addresses as $addr) {
				if ($addr['default'] == 1) {
					$defaultAddress = $addr;
					break;
				}
			}
		}

		return $defaultAddress;
	}
}
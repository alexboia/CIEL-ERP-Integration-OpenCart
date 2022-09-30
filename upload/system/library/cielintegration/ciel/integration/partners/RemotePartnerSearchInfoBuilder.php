<?php
namespace Ciel\Api\Integration\Partners {

    use Ciel\Api\Data\DocumentIssueType;

	class RemotePartnerSearchInfoBuilder {
		private $_localCustomerData;

		public function __construct(array $localCustomerData) {
			$this->_localCustomerData = $localCustomerData;	
		}

		public function buildSearchData() {
			$code = !empty($this->_localCustomerData['code'])
				? $this->_localCustomerData['code'] 
				: null;
			$name = $this->_determineSearchName();
			$taxAttribute = !empty($this->_localCustomerData['address']['address_tax_attribute'])
				? $this->_localCustomerData['address']['address_tax_attribute']
				: null;
			$taxCode = !empty($this->_localCustomerData['address']['address_tax_code'])
				? $this->_localCustomerData['address']['address_tax_code']
				: null;
			$emailAddress = isset($this->_localCustomerData['address']['address_email']) 
				? $this->_localCustomerData['address']['address_email']
				: '';
			$phoneNumber = isset($this->_localCustomerData['address']['address_phone']) 
				? $this->_localCustomerData['address']['address_phone']
				: '';

			return new RemotePartnerSearchInfo($code, 
				$name, 
				$taxAttribute, 
				$taxCode, 
				$emailAddress, 
				$phoneNumber);
		}

		private function _determineSearchName() {
			$searchName = '';
			if ($this->_searchForRemotePartnerAsCompany()) {
				$searchName = $this->_getBillingAddressCompanyName();
			} else {
				$searchName = $this->_getBillingAddressFullName();
			}
			return $searchName;
		}

		private function _getBillingAddressCompanyName() {
			return isset($this->_localCustomerData['address']['address_company_name'])
				? $this->_localCustomerData['address']['address_company_name']
				: '';
		}

		private function _getBillingAddressFullName() {
			$nameParts = array(); 
			if (!empty($this->_localCustomerData['address']['address_first_name'])) {
				$nameParts[] = $this->_localCustomerData['address']['address_first_name'];
			}
			if (!empty($this->_localCustomerData['address']['address_last_name'])) {
				$nameParts[] = $this->_localCustomerData['address']['address_last_name'];
			}

			return join(' ', $nameParts);
		}

		private function _searchForRemotePartnerAsCompany() {
			return $this->_determineLocalCustomerBillingType() 
				== DocumentIssueType::Company;
		}

		private function _determineLocalCustomerBillingType() {
			$searchBillingType = $this->_getBillingType();
			if (empty($searchBillingType)) {
				$searchBillingType = $this->_inferBillingType();
			}

			return $searchBillingType;
		}

		private function _getBillingType() {
			return !empty($this->_localCustomerData['address']['address_billing_type'])
				? $this->_localCustomerData['address']['address_billing_type']
				: null;
		}

		private function _inferBillingType() {
			return $this->_hasSearchTaxDataForCompany()
				? DocumentIssueType::Company
				: DocumentIssueType::Person;
		}

		private function _hasSearchTaxDataForCompany() {
			$taxCode = !empty($this->_localCustomerData['address']['address_tax_code'])
				? $this->_localCustomerData['address']['address_tax_code']
				: null;

			return !empty($taxCode) && !$this->_isValidPersonalNumericCode($taxCode);
		}

		private function _isValidPersonalNumericCode($value) {
			return preg_match('/^([0-9]{13})$/', $value);
		}
	}
}
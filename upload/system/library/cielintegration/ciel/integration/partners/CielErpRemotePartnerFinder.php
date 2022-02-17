<?php
namespace Ciel\Api\Integration\Partners {

	use Ciel\Api\Data\DocumentIssueType;
	use Ciel\Api\Data\PartnerAddressType;

	class CielErpRemotePartnerFinder {
		private $_localPartnerData;

		private $_searchPartnerCode;

		private $_searchBillingAddressEmail;
		
		private $_searchTaxCode;

		private $_searchTaxAttribute;

		private $_searchShopBillingAddressExternalKey;

		private $_searchName;

		private $_searchBillingAddressPhone;

		private $_usePhoneForPartnerMatching = false;

		public function __construct(array $localCustomerData) {
			$this->_localPartnerData = $localCustomerData;
			$this->_initSearchData();
		}

		private function _initSearchData() {
			$this->_searchPartnerCode = $this->_getSearchPartnerCode();
			$this->_searchName = $this->_getSearchName();
			$this->_searchBillingAddressEmail = $this->_getSearchBillingAddressEmail();
			
			$this->_searchTaxCode = $this->_getSearchTaxCode();
			$this->_searchTaxAttribute = $this->_getSearchTaxAttribute();
			$this->_searchBillingAddressPhone = $this->_getSearchBillingAddressPhone();

			$this->_searchShopBillingAddressExternalKey = $this->_getSearchBillingAddressExternalKey();
		}

		public function setUsePhoneForPartnerMatching($value) {
			$this->_usePhoneForPartnerMatching = $value;
			$this->_initSearchData();
			return $this;
		}

		private function _getSearchPartnerCode() {
			return $this->_localPartnerData['code'];
		}

		private function _getSearchBillingAddressEmail() {
			return isset($this->_localPartnerData['address']['address_email']) 
				? $this->_normalizeSearchData($this->_localPartnerData['address']['address_email'])
				: '';
		}

		private function _getSearchTaxCode() {
			return isset($this->_localPartnerData['address']['address_tax_code'])
				? $this->_normalizeSearchData($this->_localPartnerData['address']['address_tax_code'])
				: '';
		}

		private function _getSearchTaxAttribute() {
			return isset($this->_localPartnerData['address']['address_tax_attribute'])
				? $this->_normalizeSearchData($this->_localPartnerData['address']['address_tax_attribute'])
				: '';
		}

		private function _getSearchBillingAddressPhone() {
			return $this->_normalizeSearchPhone($this->_getBillingAddressPhone());
		}

		private function _getBillingAddressPhone() {
			return isset($this->_localPartnerData['address']['address_phone'])
				? $this->_localPartnerData['address']['address_phone']
				: '';
		}

		private function _normalizeSearchPhone($originalPhone) {
			if (empty($originalPhone)) {
				return '';
			}

			$normalizedSearchPhone = $this->_normalizeSearchData($originalPhone);
			$normalizedSearchPhone = preg_replace('/[^0-9]/mi', '', $normalizedSearchPhone);

			return $normalizedSearchPhone;
		}

		private function _getSearchBillingAddressExternalKey() {
			return $this->_usePhoneForPartnerMatching
				? $this->_deriveSearchBillingAddressExternalKeyFromBillingAddressPhone()
				: $this->_deriveSearchBillingAddressExternalKeyFromAccountEmail();
		}

		private function _deriveSearchBillingAddressExternalKeyFromBillingAddressPhone() {
			$phone = $this->_getBillingAddressPhone();
			return !empty($phone)
				? PartnerAddressUtility::deriveExternalAddressKeyFromPhone($phone)
				: null;
		}

		private function _deriveSearchBillingAddressExternalKeyFromAccountEmail() {
			$email = $this->_getAccountEmail();
			return !empty($email)
				? PartnerAddressUtility::deriveExternalAddressKeyFromEmail($email)
				: null;
		}

		private function _getAccountEmail() {
			return $this->_localPartnerData['email'];
		}

		private function _getSearchName() {
			$searchName = '';
			if ($this->_searchForRemotePartnerAsCompany()) {
				$searchName = $this->_getBillingAddressCompanyName();
			} else {
				$searchName = $this->_getBillingAddressFullName();
			}

			return $this->_normalizeSearchData($searchName);
		}

		private function _getBillingAddressCompanyName() {
			return isset($this->_localPartnerData['address']['address_company_name'])
				? $this->_localPartnerData['address']['address_company_name']
				: '';
		}

		private function _getBillingAddressFullName() {
			$nameParts = array(); 
			if (!empty($this->_localPartnerData['address']['address_first_name'])) {
				$nameParts[] = $this->_localPartnerData['address']['address_first_name'];
			}
			if (!empty($this->_localPartnerData['address']['address_last_name'])) {
				$nameParts[] = $this->_localPartnerData['address']['address_last_name'];
			}

			return join(' ', $nameParts);
		}

		private function _searchForRemotePartnerAsCompany() {
			return $this->_getLocalPartnerSearchBillingType() == DocumentIssueType::Company;
		}

		private function _getLocalPartnerSearchBillingType() {
			$searchBillingType = $this->_getBillingType();
			if (empty($searchBillingType)) {
				$searchBillingType = $this->_inferBillingType();
			}

			return $searchBillingType;
		}

		private function _getBillingType() {
			return !empty($this->_localPartnerData['address']['address_billing_type'])
				? $this->_localPartnerData['address']['address_billing_type']
				: null;
		}

		private function _inferBillingType() {
			return $this->_hasSearchTaxDataForCompany()
				? DocumentIssueType::Company
				: DocumentIssueType::Person;
		}

		private function _hasSearchTaxDataForCompany() {
			$searchTaxCode = $this->_getSearchTaxCode();
			return !empty($searchTaxCode) && !$this->_isValidPersonalNumericCode($searchTaxCode);
		}

		private function _isValidPersonalNumericCode($value) {
			return preg_match('/^([0-9]{13})$/', $value);
		}

		public function findMatchingRemotePartner(array $remotePartnersData) {
			$foundRemotePartner = $this->_findMatchingRemotePartner($remotePartnersData);
			if (!empty($foundRemotePartner)) {
				$foundRemotePartnerShopBillingAddress = $this->_findMatchingPartnerShopBillingAddress($foundRemotePartner);
			} else {
				$foundRemotePartnerShopBillingAddress = null;
			}

			return new RemotePartnerMatch(
				$foundRemotePartner, 
				$foundRemotePartnerShopBillingAddress
			);
		}

		private function _findMatchingRemotePartner(array $remotePartnersData) {
			$foundRemotePartner = null;

			foreach ($remotePartnersData as $remotePartnerData) {
				if ($this->_isMatch($remotePartnerData)) {
					$foundRemotePartner = $remotePartnerData;
					break;
				}
			}

			return $foundRemotePartner;
		}

		private function _findMatchingPartnerShopBillingAddress(array $remotePartnerData) {
			return !empty($this->_searchShopBillingAddressExternalKey)
				? PartnerAddressUtility::findRemotePartnerBillingAddressData($remotePartnerData, 
					$this->_searchShopBillingAddressExternalKey)
				: null;
		}

		private function _isMatch(array $remotePartnerData) {
			return $this->_isMatchByPartnerCode($remotePartnerData)
				|| $this->_isMatchByFinancialData($remotePartnerData)
				|| $this->_isMatchByBame($remotePartnerData)
				|| $this->_isMatchByDefaultAddressPhone($remotePartnerData)
				|| $this->_isMatchByDefaultAddressEmailAddress($remotePartnerData);
		}

		private function _isMatchByFinancialData(array $remotePartnerData) {
			return $this->_isMatchByTaxAttribute($remotePartnerData) 
				&& $this->_isMatchByTaxCode($remotePartnerData);
		}

		private function _isMatchByTaxCode(array $remotePartnerData) {
			$matches = false;
			if (!empty($this->_searchTaxCode)) {
				$matches = ($this->_searchTaxCode == $this->_getNormalizedRemotePartnerTaxCode($remotePartnerData));
			}
			return $matches;
		}

		private function _getNormalizedRemotePartnerTaxCode(array $remotePartnerData) {
			return isset($remotePartnerData['TaxCode']) 
				? $this->_normalizeSearchData($remotePartnerData['TaxCode']) 
				: '';
		}

		private function _isMatchByTaxAttribute(array $remotePartnerData) {
			$matches = false;
			if (!empty($this->_searchTaxAttribute)) {
				$matches = ($this->_searchTaxAttribute == $this->_getNormalizedRemotePartnerTaxAttribute($remotePartnerData));
			}
			return $matches;
		}

		private function _getNormalizedRemotePartnerTaxAttribute(array $remotePartnerData) {
			return isset($remotePartnerData['TaxAttribute']) 
				? $this->_normalizeSearchData($remotePartnerData['TaxAttribute']) 
				: '';
		}

		private function _isMatchByBame(array $remotePartnerData) {
			$matches = false;
			if (!empty($this->_searchName)) {
				$matches = $this->_searchName == $this->_getNormalizedRemotePartnerName($remotePartnerData);
			}
			return $matches;
		}

		private function _getNormalizedRemotePartnerName(array $remotePartnerData) {
			return isset($remotePartnerData['Name']) 
				? $this->_normalizeSearchData($remotePartnerData['Name']) 
				: '';
		}

		private function _isMatchByPartnerCode(array $remotePartnerData) {
			$matches = false; 
			if (!empty($this->_searchPartnerCode)) {
				$matches = $this->_searchPartnerCode == $this->_normalizeSearchData($remotePartnerData['Code']);
			}
			return $matches;
		}

		private function _isMatchByDefaultAddressEmailAddress(array $remotePartnerData) {
			$matches = false;
			if ($this->_canAttemptMatchByEmail($remotePartnerData)) {
				foreach ($remotePartnerData['Addresses'] as $remotePartnerAddressData) {
					if ($this->_isDefaultRemotePartnerAddressAndMatchesEmail($remotePartnerAddressData)) {
						$matches = true;
						break;
					}
				}
			}
			return $matches;
		}

		private function _canAttemptMatchByEmail(array $remotePartnerData) {
			return !$this->_usePhoneForPartnerMatching
				&& !empty($this->_searchBillingAddressEmail) 
				&& $this->_remotePartnerHasAddresses($remotePartnerData);
		}

		private function _remotePartnerHasAddresses(array $remotePartnerData) {
			return !empty($remotePartnerData['Addresses']) 
				&& is_array($remotePartnerData['Addresses']);
		}

		private function _isDefaultRemotePartnerAddressAndMatchesEmail(array $remotePartnerAddressData) {
			$rEmail = $this->_getNormalizedRemotePartnerEmailAddress($remotePartnerAddressData);
			return $this->_isDefaultRemotePartnerAddress($remotePartnerAddressData) 
				&& ($rEmail == $this->_searchBillingAddressEmail);
		}

		private function _getNormalizedRemotePartnerEmailAddress(array $remotePartnerAddressData) {
			return isset($remotePartnerAddressData['Email']) 
				? $this->_normalizeSearchData($remotePartnerAddressData['Email']) 
				: '';
		}

		private function _isDefaultRemotePartnerAddress(array $remotePartnerAddressData) {
			return isset($remotePartnerAddressData['IsDefault']) 
				&& $remotePartnerAddressData['IsDefault'] == true;
		}

		private function _isMatchByDefaultAddressPhone(array $remotePartnerData) {
			$matches = false;
			if ($this->_canAttemptMatchByPhone($remotePartnerData)) {
				foreach ($remotePartnerData['Addresses'] as $remotePartnerAddressData) {
					if ($this->_isDefaultRemotePartnerAddressAndMatchesPhone($remotePartnerAddressData)) {
						$matches = true;
						break;
					}
				}
			}
			return $matches;
		}

		private function _canAttemptMatchByPhone(array $remotePartnerData) {
			return $this->_usePhoneForPartnerMatching
				&& !empty($this->_searchBillingAddressPhone)
				&& $this->_remotePartnerHasAddresses($remotePartnerData);
		}

		private function _isDefaultRemotePartnerAddressAndMatchesPhone(array $remotePartnerAddressData) {
			$rPhone = $this->_getNormalizedRemotePartnerPhone($remotePartnerAddressData);
			return $this->_isDefaultRemotePartnerAddress($remotePartnerAddressData) 
				&& ($rPhone == $this->_searchBillingAddressPhone);
		}

		private function _getNormalizedRemotePartnerPhone(array $remotePartnerAddressData) {
			return isset($remotePartnerAddressData['MobilePhone']) 
				? $this->_normalizeSearchPhone($remotePartnerAddressData['MobilePhone']) 
				: '';
		}

		private function _normalizeSearchData($value) {
			return strtoupper(trim($value));
		}

		public function getLocalCustomerDataForMatching() {
			return $this->_localPartnerData;
		}
	}
}
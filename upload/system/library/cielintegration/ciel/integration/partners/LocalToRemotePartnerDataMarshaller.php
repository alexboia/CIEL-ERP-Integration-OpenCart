<?php
namespace Ciel\Api\Integration\Partners {
	use Ciel\Api\Data\PartnerAddressType;

	class LocalToRemotePartnerDataMarshaller {
		const DEFAULT_REMOTE_PARTNER_ADDRESS_NAME = 'Adresa Facturare E-Shop';

		private $_localPartnerData = null;

		private $_marshalAddressAsDefaultRemotePartnerAddress = false;

		private $_usePhoneForPartnerMatching = false;

		public function __construct(array $localCustomerData) {
			$this->_localPartnerData = $localCustomerData;
		}

		public function setMarshalAddressAsDefaultRemotePartnerAddress($value) {
			$this->_marshalAddressAsDefaultRemotePartnerAddress = $value;
			return $this;
		}

		public function setUsePhoneForPartnerMatching($value) {
			$this->_usePhoneForPartnerMatching = $value;
			return $this;
		}

		public function getRemotePartnerData() {
			$customerAddr = $this->_getLocalCustomerAddressData();

			return array(
				'Code' => $this->_getRemoteCodeFromLocalCustomerData(),
				'Name' => $this->_getPartnerName($customerAddr),
				'TaxCode' => !empty($customerAddr['address_tax_code'])
					? $customerAddr['address_tax_code']
					: null,
				'TaxAttribute' => !empty($customerAddr['address_tax_attribute'])
					? $customerAddr['address_tax_attribute']
					: null,
				'TradeRegisterNumber' => !empty($customerAddr['address_trade_reg_number'])
					? $customerAddr['address_trade_reg_number']
					: null,
				'Bank' => !empty($customerAddr['address_bank'])
					? $customerAddr['address_bank'] 
					: null,
				'IBAN' => !empty($customerAddr['address_iban'])
					? $customerAddr['address_iban']
					: null,
				'Observation' => '',
				'Active' => true,
				'Blocked' => false,
				'Addresses' => array(
					$this->_getRemoteAddressData()
				)
			);
		}

		private function _getPartnerName(array $customerAddr) {
			$partnerName = $this->_getBillingCompanyName($customerAddr);
			if (empty($partnerName)) {
				$partnerName = $this->_composeBillingPersonFullName($customerAddr);
			}
			return $partnerName;
		}

		private function _composeBillingPersonFullName(array $customerAddr) {
			$fullNameParts = array();

			if (!empty($customerAddr['address_first_name'])) {
				$fullNameParts[] = $customerAddr['address_first_name'];
			}

			if (!empty($customerAddr['address_last_name'])) {
				$fullNameParts[] = $customerAddr['address_last_name'];
			}

			return join(' ', $fullNameParts);
		}

		private function _getBillingCompanyName(array $customerAddr) {
			return !empty($customerAddr['address_company_name']) 
				? $customerAddr['address_company_name'] 
				: '';
		}

		private function _getRemoteAddressData() {
			$remoteAddr = null;
			$customerAddr = $this->_getLocalCustomerAddressData();

			$remoteAddr = array(
				'Name' => self::DEFAULT_REMOTE_PARTNER_ADDRESS_NAME,
				'ExternalKey' => $this->_computeExternalAddressKey(),
				'StreetName' => $this->_composeBillingStreetName($customerAddr),
				'StreetNumber' => '',
				'CountryName' => isset($customerAddr['address_country_name']) 
					? $customerAddr['address_country_name'] 
					: '',
				'BuildingNumber' => '',
				'BuildingEntryNumber' => '',
				'ApartmentNumber' => '',
				'FloorNumber' => '',
				'CountyName' => isset($customerAddr['address_county_name']) 
					? $customerAddr['address_county_name'] 
					: '',
				'CityName' => isset($customerAddr['address_city_name']) 
					? $customerAddr['address_city_name'] 
					: '',
				'PostalCode' => isset($customerAddr['address_postal_code'])
					? $customerAddr['address_postal_code']
					: '',
				'MobilePhone' => isset($customerAddr['address_phone'])
					? $customerAddr['address_phone']
					: '',
				'Email' => $customerAddr['address_email'],
				'AddressType' => PartnerAddressType::Worksite,
				'AtHeadOffice' => true,
				'IsDefault' => $this->_marshalAddressAsDefaultRemotePartnerAddress
			);

			return $remoteAddr;
		}

		private function _computeExternalAddressKey() {
			return $this->_usePhoneForPartnerMatching 
				? $this->_deriveExternalAddressKeyFromBillingAddressPhone()
				: $this->_deriveExternalAddressKeyFromAccountEmail();
		}

		private function _deriveExternalAddressKeyFromAccountEmail() {
			$email = $this->_getLocalPartnerAccountEmail();
			return !empty($email) 
				? PartnerAddressUtility::deriveExternalAddressKeyFromEmail($email)
				: null;
		}

		private function _deriveExternalAddressKeyFromBillingAddressPhone() {
			$phone = $this->_getLocalPartnerBillingAddressPhone();
			return !empty($phone)
				? PartnerAddressUtility::deriveExternalAddressKeyFromPhone($phone)
				: null;
		}

		private function _composeBillingStreetName(array $customerAddr) {
			$addressLinesJoinedParts = array();

			if (!empty($customerAddr['address_lines_1'])) {
				$addressLinesJoinedParts[] = $customerAddr['address_lines_1'];
			}

			if (!empty($customerAddr['address_lines_2'])) {
				$addressLinesJoinedParts[] = $customerAddr['address_lines_2'];
			}

			return join(', ', $addressLinesJoinedParts);
		}

		private function _getLocalPartnerAccountEmail() {
			return $this->_localPartnerData['email'];
		}

		private function _getLocalPartnerBillingAddressPhone() {
			$customerAddr = $this->_getLocalCustomerAddressData();
			return isset($customerAddr['address_phone'])
				? trim($customerAddr['address_phone'])
				: '';
		}

		private function _getLocalCustomerAddressData() {
			return $this->_localPartnerData['address'];
		}

		private function _getRemoteCodeFromLocalCustomerData() {
			return $this->_localPartnerData['code'];
		}
	}
}
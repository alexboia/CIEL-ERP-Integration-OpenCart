<?php
namespace Ciel\Api\Integration\Partners {
	use Ciel\Api\Data\PartnerAddressType;

	class LocalToRemotePartnerDataMarshaller {
		const DEFAULT_REMOTE_PARTNER_ADDRESS_NAME = 'Adresa Facturare E-Shop';

		private $_localCustomerData = null;

		private $_marshalAddressAsDefaultRemotePartnerAddress = false;

		public function __construct(array $localCustomerData) {
			$this->_localCustomerData = $localCustomerData;
		}

		public function setMarshalAddressAsDefaultRemotePartnerAddress($value) {
			$this->_marshalAddressAsDefaultRemotePartnerAddress = $value;
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
				'StreetNumber' => '-',
				'CountryName' => isset($customerAddr['address_country_name']) 
					? $customerAddr['address_country_name'] 
					: '-',
				'RezidenceName' => $this->_determineResidenceName($customerAddr),
				'BuildingNumber' => null,
				'BuildingEntryNumber' => null,
				'ApartmentNumber' => null,
				'FloorNumber' => null,
				'CountyName' => isset($customerAddr['address_county_name']) 
					? $customerAddr['address_county_name'] 
					: '-',
				'CityName' => isset($customerAddr['address_city_name']) 
					? $customerAddr['address_city_name'] 
					: '-',
				'PostalCode' => isset($customerAddr['address_postal_code'])
					? $customerAddr['address_postal_code']
					: null,
				'MobilePhone' => isset($customerAddr['address_phone'])
					? $customerAddr['address_phone']
					: null,
				'Email' => $customerAddr['address_email'],
				'AddressType' => PartnerAddressType::Worksite,
				'AtHeadOffice' => true,
				'IsDefault' => $this->_marshalAddressAsDefaultRemotePartnerAddress
			);

			return $remoteAddr;
		}

		private function _computeExternalAddressKey() {
			return PartnerAddressUtility::determineExternalAddressKey($this->_localCustomerData);
		}

		private function _determineResidenceName(array $customerAddr) {
			$residenceName = 'Romania';
			if (!empty($customerAddr['address_country_name'])) {
				$testCountryName = strtoupper(trim($customerAddr['address_country_name']));
				if ($testCountryName != 'ROMANIA' 
						&& $testCountryName != 'ROMÂNIA' 
						&& $testCountryName != 'RO') {
					$residenceName = 'UE';
				}
			}
			return $residenceName;
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

		private function _getLocalCustomerAddressData() {
			return $this->_localCustomerData['address'];
		}

		private function _getRemoteCodeFromLocalCustomerData() {
			return !empty($this->_localCustomerData['code'])
				? trim($this->_localCustomerData['code'])
				: null;
		}
	}
}
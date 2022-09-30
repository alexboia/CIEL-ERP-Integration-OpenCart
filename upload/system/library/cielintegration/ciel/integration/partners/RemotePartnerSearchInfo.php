<?php
namespace Ciel\Api\Integration\Partners {
	class RemotePartnerSearchInfo {
		private $_code;
		
		private $_name;

		private $_taxAttribute;

		private $_taxCode;

		private $_emailAddress;

		private $_phoneNumber;

		public function __construct($code, 
				$name, 
				$taxAttribute, 
				$taxCode, 
				$emailAddress,
				$phoneNumber) {
			$this->_code = $this->_normalizeSearchData($code);
			$this->_name = $this->_normalizeSearchData($name);
			$this->_taxAttribute = $this->_normalizeSearchData($taxAttribute);
			$this->_taxCode = $this->_normalizeSearchData($taxCode);
			$this->_emailAddress = $this->_normalizeSearchData($emailAddress);
			$this->_phoneNumber = $this->_normalizeSearchPhone($phoneNumber);
		}

		private function _normalizeSearchData($value) {
			return strtoupper(trim($value));
		}

		private function _normalizeSearchPhone($originalPhone) {
			if (empty($originalPhone)) {
				return '';
			}

			$normalizedSearchPhone = $this->_normalizeSearchData($originalPhone);
			$normalizedSearchPhone = preg_replace('/[^0-9]/mi', '', $normalizedSearchPhone);

			return $normalizedSearchPhone;
		}

		public function matchesCode($code) {
			$matches = false;
			if (!empty($this->_code)) {
				$matches = ($this->_code == $this->_normalizeSearchData($code));
			}
			return $matches;
		}

		public function matchesName($name) {
			$matches = false;
			if (!empty($this->_name)) {
				$matches = ($this->_name == $this->_normalizeSearchData($name));
			}
			return $matches;
		}

		public function matchesEmail($emailAddress) {
			$matches = false;
			if (!empty($this->_emailAddress)) {
				$matches = ($this->_emailAddress == $this->_normalizeSearchData($emailAddress));
			}
			return $matches;
		}

		public function matchesPhone($phone) {
			$matches = false;
			if (!empty($this->_phoneNumber)) {
				$matches = ($this->_phoneNumber == $this->_normalizeSearchPhone($phone));
			}
			return $matches;
		}

		public function matchesTaxAttribute($taxAttribute) {
			$matches = false;
			$normalizedTaxAttribute = $this->_normalizeSearchData($taxAttribute);
			
			if (!empty($this->_taxAttribute)) {
				$matches = ($this->_taxAttribute == $normalizedTaxAttribute);
			} else {
				$matches = empty($normalizedTaxAttribute);
			}

			return $matches;
		}

		public function matchesTaxCode($taxCode) {
			$matches = false;
			if (!empty($this->_searchTaxCode)) {
				$matches = ($this->_searchTaxCode == $this->_normalizeSearchData($taxCode));
			}
			return $matches;
		}

		public function getCode() {
			return $this->_code;
		}

		public function getName() {
			return $this->_name;
		}

		public function getTaxAttribute() {
			return $this->_taxAttribute;
		}

		public function getTaxCode() {
			return $this->_taxCode;
		}

		public function getEmailAddress() {
			return $this->_emailAddress;
		}

		public function getPhoneNumber() {
			return $this->_phoneNumber;
		}

		public function getDescription() {
			$parts = array(
				'Name: <' . (!empty($this->_name) 
					? $this->_name 
					: '[empty]') . '>',
				'Tax attribute: <' . (!empty($this->_taxAttribute) 
					? $this->_taxAttribute 
					: '[empty]') . '>',
				'Tax code: <' . (!empty($this->_taxCode) 
					? $this->_taxCode 
					: '[empty]') . '>',
				'E-mail: <' . (!empty($this->_emailAddress) 
					? $this->_emailAddress 
					: '[empty]') . '>',
				'Phone: <' . (!empty($this->_phoneNumber) 
					? $this->_phoneNumber 
					: '[empty]') . '>'
			);

			return join('; ', $parts);
		}

		public function __toString() {
			return $this->getDescription();
		}
	}
}
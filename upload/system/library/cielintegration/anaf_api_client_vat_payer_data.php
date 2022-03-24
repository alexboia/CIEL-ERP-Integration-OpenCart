<?php
namespace CielIntegration {
	class AnafApiClientVatPayerData {
		private $_data;

		public function __construct(array $data) {
			$this->_data = $data;
		}

		public function getVatCode() {
			return !empty($this->_data['cui'])
				? $this->_data['cui']
				: null;
		}

		public function getPersonName() {
			return !empty($this->_data['denumire'])
				? $this->_data['denumire']
				: null;
		}

		public function getRegComRegistryNumber() {
			return !empty($this->_data['nrRegCom'])
				? $this->_data['nrRegCom']
				: null;
		}

		public function isVatPayer() {
			return !empty($this->_data['scpTVA'])
				&& ($this->_data['scpTVA'] == true);
		}

		public function getPostalCode() {
			return !empty($this->_data['codPostal'])
				? $this->_data['codPostal']
				: null;
		}

		public function getPhoneNumber() {
			return !empty($this->_data['telefon'])
				? $this->_data['telefon']
				: null;
		}

		public function getFullAddress() {
			return !empty($this->_data['adresa'])
				? $this->_data['adresa']
				: null;
		}

		public function getFullVatCode() {
			if ($this->isVatPayer()) {
				return sprintf('RO%s', $this->getVatCode());
			} else {
				return $this->getVatCode();
			}
		}

		public function toArray() {
			return array(
				'person_name' => $this->getPersonName(),
				'vat_code' => $this->getVatCode(),
				'vat_code_full' => $this->getFullVatCode(),
				'phone_number' => $this->getPhoneNumber(),
				'full_address' => $this->getFullAddress(),
				'postal_code' => $this->getPostalCode()
			);
		}

		public function getRawData() {
			return $this->_data;
		}
	}
}
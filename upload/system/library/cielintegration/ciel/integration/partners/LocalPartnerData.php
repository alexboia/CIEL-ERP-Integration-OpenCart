<?php
namespace Ciel\Api\Integration\Partners {
	class LocalPartnerData {
		/**
		 * @var array|null
		 */
		private $_localPartnerData;

		public function __construct($localPartnerData) {
			$this->_localPartnerData = $localPartnerData;
		}

		public function exists() {
			return !empty($this->_localPartnerData);
		}

		public function getRemotePartnerCode() {
			$code = null;
			if ($this->exists()) {
				$code = !empty($this->_localPartnerData['code'])
					? $this->_localPartnerData['code']
					: null;
			}

			return $code;
		}

		public function hasRemotePartnerCode() {
			return !empty($this->getRemotePartnerCode());
		}

		public function getLocalPartnerId() {
			$code = null;
			if ($this->exists()) {
				$code = !empty($this->_localPartnerData['id'])
					? $this->_localPartnerData['id']
					: null;
			}

			return $code;
		}

		public function isRegisteredLocalUser() {
			return $this->exists() && !empty($this->getLocalPartnerId());
		}

		public function getData() {
			return $this->_localPartnerData;
		}

		public function getAccountEmail() {
			return $this->exists() 
				? $this->_localPartnerData['email'] 
				: null;
		}

		public function getBillingAddressPhoneNumber() {
			if ($this->exists()) {
				$addressData = $this->getAddressData();
				return isset($addressData['address_phone']) 
					? $addressData['address_phone'] 
					: null;
			} else {
				return null;
			}
		}

		public function getAddressData() {
			return $this->exists() && isset($this->_localPartnerData['address'])
				? $this->_localPartnerData['address']
				: null;
		}
	}
}
<?php
namespace Ciel\Api\Integration\Partners {
	class RemotePartnerMatch {
		/**
		 * @var array|null
		 */
		private $_remotePartnerData = null;

		/**
		 * @var array|null
		 */
		private $_remotePartnerShopBillingAddress = null;

		/**
		 * @var array|null
		 */
		private $_remotePartnerDefaultAddress = null;

		public function __construct($remotePartnerData, $remotePartnerShopBillingAddress) {
			$this->_remotePartnerData = $remotePartnerData;
			$this->_remotePartnerShopBillingAddress = $remotePartnerShopBillingAddress;
			
			if ($this->_isRemotePartnerDataMatch($remotePartnerData)) {
				$this->_initDefaultRemotePartnerAddressData($remotePartnerData);
			} else {
				$this->_remotePartnerDefaultAddress = null;
			}
		}

		private function _isRemotePartnerDataMatch($remotePartnerData) {
			return !empty($remotePartnerData) && is_array($remotePartnerData);
		}

		private function _initDefaultRemotePartnerAddressData(array $remotePartnerData) {
			$remotePartnerDefaultAddress = null;

			if ($this->_remotePartnerHasAddresses($remotePartnerData)) {
				foreach ($remotePartnerData['Addresses'] as $addr) {
					if ($this->_isDefaultRemotePartnerAddress($addr)) {
						$remotePartnerDefaultAddress = $addr;
						break;
					}
				}
			}

			$this->_remotePartnerDefaultAddress = $remotePartnerDefaultAddress;
		}

		private function _remotePartnerHasAddresses(array $remotePartnerData) {
			return !empty($remotePartnerData['Addresses']) 
				&& is_array($remotePartnerData['Addresses']);
		}

		private function _isDefaultRemotePartnerAddress(array $remotePartnerAddressData) {
			return isset($remotePartnerAddressData['IsDefault']) 
				&& $remotePartnerAddressData['IsDefault'] == true;
		}

		public function isMatch () {
			return $this->_isRemotePartnerDataMatch($this->_remotePartnerData);
		}

		public function hasMatchingShopBillingAddress() {
			return !empty($this->_remotePartnerShopBillingAddress);
		}

		public function getRemotePartnerData() {
			return $this->_remotePartnerData;
		}

		public function getRemotePartnerShopBillingAddressData() {
			return $this->_remotePartnerShopBillingAddress;
		}

		public function getRemotePartnerDefaultAddress() {
			return $this->_remotePartnerDefaultAddress;
		}
	}
}
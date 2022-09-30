<?php
namespace Ciel\Api\Integration\Partners {
    use Ciel\Api\CielLogger;

	class RemotePartnerFinder {
		/**
		 * @var RemotePartnerSearchInfo
		 */
		private $_searchInfo;

		/**
		 * @var CielLogger
		 */
		private $_logger;

		private $_usePhoneForPartnerMatching = false;

		private $_useNameForPartnerMatching = true;

		public function __construct(RemotePartnerSearchInfo $searchInfo, CielLogger $logger) {
			$this->_searchInfo = $searchInfo;
			$this->_logger = $logger;
		}

		/**
		 * @return array|null 
		 */
		public function findRemotePartner(array $remotePartnersData) {
			if (empty($remotePartnersData)) {
				$this->_logger->logDebug('Empty remote partner list given. Exiting with null...');
				return null;
			}

			return $this->_findMatchingRemotePartner($remotePartnersData);
		}

		/**
		 * @return array|null 
		 */
		private function _findMatchingRemotePartner(array $remotePartnersData) {
			$foundRemotePartner = null;
			$this->_logger->logDebug('Begin searching partner with criteria: <' . $this->_searchInfo->getDescription() . '> ...');

			foreach ($remotePartnersData as $remotePartnerData) {
				if ($this->_isExcludedPartner($remotePartnerData)) {
					continue;
				}

				if ($this->_isMatch($remotePartnerData)) {
					$foundRemotePartner = $remotePartnerData;
					break;
				}
			}

			if (!empty($foundRemotePartner)) {
				$this->_logger->logDebug('Remote partner found for criteria. Partner code is <' . $foundRemotePartner['Code'] . '>.');
			} else {
				$this->_logger->logDebug('No remote partner found for criteria.');
			}

			return $foundRemotePartner;
		}

		private function _isExcludedPartner(array $remotePartnerData) {
			return empty($remotePartnerData['Code'])
				|| $remotePartnerData['Code'] == 'INTERN';
		}

		private function _isMatch(array $remotePartnerData) {
			return $this->_isMatchByCode($remotePartnerData)
				|| $this->_isMatchByFinancialData($remotePartnerData)
				|| $this->_isMatchByEmailAddress($remotePartnerData)
				|| $this->_isMatchByPhone($remotePartnerData)
				|| $this->_isMatchByBame($remotePartnerData);
		}

		private function _isMatchByCode(array $remotePartnerData) {
			$code = $this->_getRemotePartnerCode($remotePartnerData);
			return $this->_searchInfo->matchesCode($code);
		}

		private function _getRemotePartnerCode(array $remotePartnerData) {
			return isset($remotePartnerData['Code']) 
				? $remotePartnerData['Code'] 
				: '';
		}

		private function _isMatchByFinancialData(array $remotePartnerData) {
			return $this->_isMatchByTaxAttribute($remotePartnerData) 
				&& $this->_isMatchByTaxCode($remotePartnerData);
		}

		private function _isMatchByTaxAttribute(array $remotePartnerData) {
			$taxAttribute = $this->_getRemotePartnerTaxAttribute($remotePartnerData);
			return $this->_searchInfo->matchesTaxAttribute($taxAttribute);
		}

		private function _getRemotePartnerTaxAttribute(array $remotePartnerData) {
			return isset($remotePartnerData['TaxAttribute']) 
				? $remotePartnerData['TaxAttribute'] 
				: '';
		}

		private function _isMatchByTaxCode(array $remotePartnerData) {
			$taxCode = $this->_getRemotePartnerTaxCode($remotePartnerData);
			return $this->_searchInfo->matchesTaxCode($taxCode);
		}

		private function _getRemotePartnerTaxCode(array $remotePartnerData) {
			return isset($remotePartnerData['TaxCode']) 
				? $remotePartnerData['TaxCode'] 
				: '';
		}

		private function _isMatchByEmailAddress(array $remotePartnerData) {
			$matches = false;
			if ($this->_canAttemptMatchByEmail($remotePartnerData)) {
				foreach ($remotePartnerData['Addresses'] as $remotePartnerAddressData) {
					$emailAddress = $this->_getRemotePartnerEmailAddress($remotePartnerAddressData);
					if ($this->_searchInfo->matchesEmail($emailAddress)) {
						$matches = true;
						break;
					}
				}
			}
			return $matches;
		}

		private function _canAttemptMatchByEmail(array $remotePartnerData) {
			return !$this->_usePhoneForPartnerMatching 
				&& $this->_remotePartnerHasAddresses($remotePartnerData);
		}

		private function _remotePartnerHasAddresses(array $remotePartnerData) {
			return !empty($remotePartnerData['Addresses']) 
				&& is_array($remotePartnerData['Addresses']);
		}

		private function _getRemotePartnerEmailAddress(array $remotePartnerAddressData) {
			return isset($remotePartnerAddressData['Email']) 
				? $remotePartnerAddressData['Email'] 
				: '';
		}

		private function _isMatchByPhone(array $remotePartnerData) {
			$matches = false;
			if ($this->_canAttemptMatchByPhone($remotePartnerData)) {
				foreach ($remotePartnerData['Addresses'] as $remotePartnerAddressData) {
					$phone = $this->_getRemotePartnerPhone($remotePartnerAddressData);
					if ($this->_searchInfo->matchesPhone($phone)) {
						$matches = true;
						break;
					}
				}
			}
			return $matches;
		}

		private function _canAttemptMatchByPhone(array $remotePartnerData) {
			return $this->_usePhoneForPartnerMatching
				&& $this->_remotePartnerHasAddresses($remotePartnerData);
		}

		private function _getRemotePartnerPhone(array $remotePartnerAddressData) {
			return isset($remotePartnerAddressData['MobilePhone']) 
				? $remotePartnerAddressData['MobilePhone'] 
				: '';
		}

		private function _isMatchByBame(array $remotePartnerData) {
			if ($this->_useNameForPartnerMatching) {
				$name = $this->_getRemotePartnerName($remotePartnerData);
				return $this->_searchInfo->matchesName($name);
			} else {
				return false;
			}
		}

		private function _getRemotePartnerName(array $remotePartnerData) {
			return isset($remotePartnerData['Name']) 
				? $remotePartnerData['Name'] 
				: '';
		}

		public function setUsePhoneForPartnerMatching($value) {
			$this->_usePhoneForPartnerMatching = $value;
			return $this;
		}

		public function setUseNameForPartnerMatching($value) {
			$this->_useNameForPartnerMatching = $value;
			return $this;
		}
	}
}
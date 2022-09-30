<?php
namespace Ciel\Api\Integration\Partners {

    use Ciel\Api\CielLogger;

	class RemotePartnerAddressFinder {
		/**
		 * @var RemotePartnerAddressSearchInfo
		 */
		private $_searchInfo;

		/**
		 * @var CielLogger
		 */
		private $_logger;

		public function __construct(RemotePartnerAddressSearchInfo $searchInfo, CielLogger $logger) {
			$this->_searchInfo = $searchInfo;
			$this->_logger = $logger;
		}

		public function findRemotePartnerAddress(array $remotePartnerData) {
			if (empty($remotePartnerData)) {
				$this->_logger->logDebug('Empty remote partner data given. Exiting with null...');
				return null;
			}

			return $this->_findMatchingRemotePartnerAddress($remotePartnerData);
		}

		private function _findMatchingRemotePartnerAddress(array $remotePartnerData) {
			$foundRemotePartnerAddress = null;
			$this->_logger->logDebug('Begin searching address with criteria: <' . $this->_searchInfo->getDescription() . '> ...');

			if ($this->_remotePartnerHasAddresses($remotePartnerData)) {
				foreach ($remotePartnerData['Addresses'] as $remotePartnerAddressData) {
					$key = $this->_getRemotePartnerAddressExternalKey($remotePartnerAddressData);
					if ($this->_searchInfo->matchesKey($key)) {
						$foundRemotePartnerAddress = $remotePartnerAddressData;
						break;
					}
				}
			}

			if (!empty($foundRemotePartnerAddress)) {
				$this->_logger->logDebug('Remote partner address found for criteria. Address ID is <' . $foundRemotePartnerAddress['Id'] . '>.');
			} else {
				$this->_logger->logDebug('No remote partner address found for criteria.');
			}

			return $foundRemotePartnerAddress;
		}

		private function _remotePartnerHasAddresses(array $remotePartnerData) {
			return !empty($remotePartnerData['Addresses']) 
				&& is_array($remotePartnerData['Addresses']);
		}

		private function _getRemotePartnerAddressExternalKey(array $remotePartnerAddressData) {
			return isset($remotePartnerAddressData['ExternalKey']) 
				? $remotePartnerAddressData['ExternalKey'] 
				: '';
		}
	}
}
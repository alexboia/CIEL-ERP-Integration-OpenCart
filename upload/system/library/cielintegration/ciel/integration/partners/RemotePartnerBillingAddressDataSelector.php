<?php
namespace Ciel\Api\Integration\Partners {
	class RemotePartnerBillingAddressDataSelector {
		/**
		 * @var LocalPartnerData
		 */
		private $_localPartnerData;

		private $_usePhoneForPartnerMatching = false;

		public function __construct(LocalPartnerData $localPartnerData) {
			$this->_localPartnerData = $localPartnerData;
		}

		public function setUsePhoneForPartnerMatching($value) {
            $this->_usePhoneForPartnerMatching = $value;
            return $this;
        }

		public function selectRemoteBillingAddressData(array $remotePartnerData) {
			if ($this->_localPartnerData->exists()) {
				return PartnerAddressUtility::findRemotePartnerBillingAddressData($remotePartnerData, $this->computeRemoteBillingAddressExternalKey());		
			} else {
				return null;
			}
		}

		public function computeRemoteBillingAddressExternalKey() {
			if ($this->_localPartnerData->exists()) {
				$email = $this->_localPartnerData->getAccountEmail();
				$phone = $this->_localPartnerData->getBillingAddressPhoneNumber();

				return $this->_usePhoneForPartnerMatching 
					? PartnerAddressUtility::deriveExternalAddressKeyFromPhone($phone)
					: PartnerAddressUtility::deriveExternalAddressKeyFromEmail($email);
			} else {
				return null;
			}
		}
	}
}
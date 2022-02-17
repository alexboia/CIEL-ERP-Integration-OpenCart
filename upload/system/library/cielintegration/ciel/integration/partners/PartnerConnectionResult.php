<?php
namespace Ciel\Api\Integration\Partners {
	class PartnerConnectionResult {
		/**
		 * @var \Ciel\Api\Integration\Partners\LocalPartnerData
		 */
		private $_localPartnerData;

		/**
		 * @var \Ciel\Api\Integration\Partners\RemotePartnerMatch|null
		 */
		private $_remotePartnerMatch;

		public function __construct($localPartnerData, $remotePartnerMatch) {
			$this->_localPartnerData = $localPartnerData;
			$this->_remotePartnerMatch = $remotePartnerMatch;
		}

		public function wasAlreadyConnected() {
			return $this->_localPartnerData->hasRemotePartnerCode() 
				&& empty($this->_remotePartnerMatch);
		}

		public function wasNotConnectedButMatchFound() {
			return !$this->_localPartnerData->hasRemotePartnerCode() 
				&& !empty($this->_remotePartnerMatch) 
				&& $this->_remotePartnerMatch->isMatch();
		}

		public function wasNotConnectedAndNoMatchFound() {
			return !$this->_localPartnerData->hasRemotePartnerCode() 
				&& !empty($this->_remotePartnerMatch) 
				&& !$this->_remotePartnerMatch->isMatch();
		}

		public function localPartnerIsRegisteredLocalUser() {
			return $this->_localPartnerData->isRegisteredLocalUser();
		}
	}
}
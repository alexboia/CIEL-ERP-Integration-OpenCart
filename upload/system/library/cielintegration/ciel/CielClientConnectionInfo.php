<?php
namespace Ciel\Api {

    use InvalidArgumentException;

	class CielClientConnectionInfo {
		private $_serverUrl;

		private $_userName;

		private $_password;

		private $_societyCode;

		private $_timeoutSeconds = 30;

		public function __construct($serverUrl, 
			$userName = null, 
			$password = null, 
			$societyCode = null,
			$timeoutSeconds = 30) {
				
			if (empty($serverUrl)) {
				throw new InvalidArgumentException('The server URL may not be empty');
			}

			$this->_serverUrl = $serverUrl;
			$this->_userName = $userName;
			$this->_password = $password;
			$this->_societyCode = $societyCode;
			$this->_timeoutSeconds = $timeoutSeconds;
		}

		public function getServerUrl() {
			return $this->_serverUrl;
		}

		public function getUserName() {
			return $this->_userName;
		}

		public function hasUserName() {
			return !empty($this->getUserName());
		}

		public function getPassword() {
			return $this->_password;
		}

		public function getSocietyCode() {
			return $this->_societyCode;
		}

		public function hasSocietyCode() {
			return !empty($this->getSocietyCode());
		}

		public function getTimeoutSeconds() {
			return $this->_timeoutSeconds;
		}
	}
}
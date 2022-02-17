<?php
namespace Ciel\Api\Session {
	class CielClientSessionCredentials {
		private $_userName;

		private $_password;

		private $_societyCode;

		public function __construct($userName, $password, $societyCode) {
			$this->_userName = $userName;
			$this->_password = $password;
			$this->_societyCode = $societyCode;
		}

		public function computeHash() {
			return sha1($this->_userName . $this->_password . $this->_societyCode);
		}

		public function getUserName() {
			return $this->_userName;
		}

		public function getPassword() {
			return $this->_password;
		}

		public function getSocietyCode() {
			return $this->_societyCode;
		}
	}
}
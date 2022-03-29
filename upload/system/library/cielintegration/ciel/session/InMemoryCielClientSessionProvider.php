<?php
namespace Ciel\Api\Session {
	class InMemoryCielClientSessionProvider implements CielClientSessionProvider {
		private $_sessions = array();

		public function setup() {
			return;
		}

		public function registerSessionToken(CielClientSessionCredentials $credentials, $token) { 
			$hash = $credentials->computeHash();
			$this->_sessions[$hash] = $token;
			return $token;
		}

		public function resolveSessionToken(CielClientSessionCredentials $credentials) { 
			$hash = $credentials->computeHash();
			return isset($this->_sessions[$hash])
				? $this->_sessions[$hash]
				: null;
		}

		public function clearSessionToken(CielClientSessionCredentials $credentials) {
			$hash = $credentials->computeHash();
			if (isset($this->_sessions[$hash])) {
				$this->_sessions[$hash] = null;
				unset($this->_sessions[$hash]);
			}
		}

		public function clearSessionTokenByTokenValue($token) {
			$removeHash = null;

			foreach ($this->_sessions as $hash => $tokenValue) {
				if ($tokenValue === $token) {
					$removeHash = $hash;
					break;
				}
			}

			if ($removeHash !== null) {
				unset($this->_sessions[$removeHash]);
			}
		}

		public function isSupported() { 
			return true;
		}
	}
}
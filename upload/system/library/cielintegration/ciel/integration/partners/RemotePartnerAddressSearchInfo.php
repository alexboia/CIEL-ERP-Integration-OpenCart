<?php
namespace Ciel\Api\Integration\Partners {
	class RemotePartnerAddressSearchInfo {
		private $_key;

		public function __construct($key) {
			$this->_key = $this->_normalizeSearchData($key);
		}

		private function _normalizeSearchData($value) {
			return strtoupper(trim($value));
		}

		public function matchesKey($key) {
			$matches = false;
			if (!empty($key)) {
				$matches = ($this->_key == $this->_normalizeSearchData($key));
			}
			return $matches;
		}

		public function getKey() {
			return $this->_key;
		}

		public function getDescription() {
			$parts = array(
				'External key: <' . (!empty($this->_key) 
					? $this->_key 
					: '[empty]') . '>'
			);

			return join('; ', $parts);
		}

		public function __toString() {
			return $this->getDescription();
		}
	}
}
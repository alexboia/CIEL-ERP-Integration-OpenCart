<?php
namespace CielIntegration\Integration\Admin {
	trait WithLookupDataProvider {
		/**
		 * @var LookupDataProvider
		 */
		private $_lookupDataProvider;

		protected function _getDocumentTypeName($typeId) {
			return $this->_getLookupDataProvider()
				->getDocumentTypeName($typeId);
		}

		protected function _getSupportedDocumentTypes() {
			return $this->_getLookupDataProvider()
				->getSupportedDocumentTypes();
		}

		protected function _getSupportedStockUpdateModes() {
			return $this->_getLookupDataProvider()
				->getSupportedStockUpdateModes();
		}

		protected function _getSupportedDocumentStatusTypes() {
			return $this->_getLookupDataProvider()
				->getSupportedDocumentStatusTypes();
		}

		protected function _getOpenCartOrderStatuses() {
			return $this->_getLookupDataProvider()
				->getOpenCartOrderStatuses();
		}

		protected function _getOpenCartStockStatuses() {
			return $this->_getLookupDataProvider()
				->getOpenCartStockStatuses();
		}

		protected function _getOpenCartCustomerGroups() {
			return $this->_getLookupDataProvider()
				->getOpenCartCustomerGroups();
		}

		protected function _getOpenCartWeightClasses() {
			return $this->_getLookupDataProvider()
				->getOpenCartWeightClasses();
		}

		protected function _getOpenCartLengthClasses() {
			return $this->_getLookupDataProvider()
				->getOpenCartLengthClasses();
		}

		protected function _getOpenCartGeoZones() {
			return $this->_getLookupDataProvider()
				->getOpenCartGeoZones();
		}

		protected function _getOpenCartCustomerCustomFields($customerGroupId = null, $location = 'address') {
			return $this->_getLookupDataProvider()
				->getOpenCartCustomerCustomFields($customerGroupId, 
					$location);
		}

		protected function _getSupportedProductSyncModes() {
			return $this->_getLookupDataProvider()
				->getSupportedProductSyncModes();
		}

		private function _getLookupDataProvider() {
			if ($this->_lookupDataProvider === null) {
				$this->_lookupDataProvider = new LookupDataProvider($this->registry);
			}
			return $this->_lookupDataProvider;
		}
	}
}
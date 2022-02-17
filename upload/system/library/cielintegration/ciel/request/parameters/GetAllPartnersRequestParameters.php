<?php
namespace Ciel\Api\Request\Parameters {
	use Ciel\Api\Request\CielRequestParameters;

	class GetAllPartnersRequestParameters extends CielRequestParameters {
		private $_includeAddresses = null;

		private $_includeOnlyDefaultAddress = null;

		private $_includeOnlyPartnersForMobileDevice = null;

		private $_includeOnlyUnblockedPartners = null;

		private $_pageNumber = null;

		private $_itemsPerPage = null;

		public function setIncludeAddresses($val) {
			$this->_includeAddresses = $val;
			return $this;
		}

		public function setIncludeOnlyDefaultAddress($val) {
			$this->_includeOnlyDefaultAddress = $val;
			return $this;
		}

		public function setIncludeOnlyPartnersForMobileDevice($val) {
			$this->_includeOnlyPartnersForMobileDevice = $val;
			return $this;
		}

		public function setIncludeOnlyUnblockedPartners($val) {
			$this->_includeOnlyUnblockedPartners = $val;
			return $this;
		}

		public function setPagination($pageNumber, $itemsPerPage = 1) {
			$this->_pageNumber = $pageNumber;
			$this->_itemsPerPage = $itemsPerPage;
			return $this;
		}

		public function getParams() {
			$params = array();
			if ($this->_includeAddresses !== null) {
				$params['includeAddresses'] = $this->_includeAddresses;
			}
			if ($this->_includeOnlyDefaultAddress !== null) {
				$params['includeOnlyDefaultAddress'] = $this->_includeOnlyDefaultAddress;
			}
			if ($this->_includeOnlyPartnersForMobileDevice !== null) {
				$params['includeOnlyPartnersForMobileDevice'] = $this->_includeOnlyPartnersForMobileDevice;
			}
			if ($this->_includeOnlyUnblockedPartners !== null) {
				$params['includeOnlyUnblockedPartners'] = $this->_includeOnlyUnblockedPartners;
			}
			if ($this->_pageNumber !== null) {
				$params['pageNumber'] = $this->_pageNumber;
				$params['itemsPerPage'] = $this->_itemsPerPage !== null 
					? $this->_itemsPerPage 
					: 1;
			}
			return $params;   
		}
	}
}
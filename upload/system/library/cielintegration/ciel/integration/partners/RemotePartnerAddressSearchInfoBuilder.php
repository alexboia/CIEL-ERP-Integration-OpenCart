<?php
namespace Ciel\Api\Integration\Partners {
	class RemotePartnerAddressSearchInfoBuilder {
		private $_localCustomerData;

		public function __construct(array $localCustomerData) {
			$this->_localCustomerData = $localCustomerData;	
		}

		public function buildSearchData() {
			$externalKey = PartnerAddressUtility::determineExternalAddressKey($this->_localCustomerData);
			return new RemotePartnerAddressSearchInfo($externalKey);
		}
	}
}
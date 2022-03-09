<?php
namespace CielIntegration {
	class CustomerDataCollector {
		private $_customerListingData = array();

		public function __construct($customerListingData) {
			$this->_customerListingData = $customerListingData;
		}

		public function collectIds() {
			$ids = array();
			if (!empty($this->_customerListingData['customers'])) {
				foreach ($this->_customerListingData['customers'] as $c) {
					$ids[] = intval($c['customer_id']);
				}
			}
			return $ids;
		}
	}
}
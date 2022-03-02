<?php
namespace CielIntegration {
	class OrderDataCollector {
		private $_orderListingData;

		public function __construct($orderListingData) {
			$this->_orderListingData = $orderListingData;
		}

		public function collectIds() {
			$ids = array();
			if (!empty($this->_orderListingData['orders'])) {
				foreach ($this->_orderListingData['orders'] as $o) {
					$ids[] = intval($o['order_id']);
				}
			}
			return $ids;
		}
	}
}
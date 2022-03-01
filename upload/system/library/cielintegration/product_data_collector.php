<?php
namespace CielIntegration {
	class ProductDataCollector {
		private $_productListingData = array();

		public function __construct($productListingData) {
			$this->_productListingData = $productListingData;
		}

		public function collectIds(){
			$ids = array();
			if (!empty($this->_productListingData['products'])) {
				foreach ($this->_productListingData['products'] as $p) {
					$ids[] = intval($p['product_id']);
				}
			}
			return $ids;
		}
	}
}
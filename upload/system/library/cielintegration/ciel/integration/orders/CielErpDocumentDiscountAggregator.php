<?php
namespace Ciel\Api\Integration\Orders {
	class CielErpDocumentDiscountAggregator {
		private $_discountsForVatQuotas = array();

		public function collectForOrderProductLine(array $orderLineProduct) {
			if (!empty($orderLineProduct['item_discount']) && $orderLineProduct['item_discount'] > 0) {
				$discountVatQuotaValue = $orderLineProduct['item_vat_out_quota_value'];
				
				if (!isset($this->_discountsForVatQuotas[$discountVatQuotaValue])) {
					$this->_discountsForVatQuotas[$discountVatQuotaValue] = array(
						'discount_value' => $orderLineProduct['item_discount'],
						'discount_tax_value' => $orderLineProduct['item_discount_tax']
					);
				} else {
					$this->_discountsForVatQuotas[$discountVatQuotaValue]['discount_value'] 
						+= $orderLineProduct['item_discount'];
					$this->_discountsForVatQuotas[$discountVatQuotaValue]['discount_tax_value'] 
						+= $orderLineProduct['item_discount_tax'];
				}
			}
		}

		public function getDiscountInfoForVatQuotas() {
			return $this->_discountsForVatQuotas;
		}

		public function hasDiscountInfo() {
			return !empty($this->_discountsForVatQuotas);
		}

		public function clear() {
			$this->_discountsForVatQuotas = array();
		}
	}
}
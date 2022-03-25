<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\Article\ProductResolver;
    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\PriceFormatter;

	class LocalOrderToRemoteDocumentMarshaller extends IntegrationService {
		const DISCOUNT_DELTA = 0.1;

		/**
		 * @var OrderResolver
		 */
		private $_orderResolver;

		/**
		 * @var OrderPartnerResolver
		 */
		private $_orderPartnerResolver;

		/**
		 * @var ProductResolver
		 */
		private $_productResolver;

		/**
		 * @var PriceFormatter
		 */
		private $_priceFormatter;

		private $_sourceOrderId;

		private $_sourceOrder = null;

		private $_sourceOrderProducts = null;

		private $_sourceOrderTotals = null;

		private $_computedOrderShipping = null;

		private $_computedOrderProductLines = array();

		private $_computedOrderDiscountLines = array();

		private $_computedTotalSaleTax = 0;

		public function __construct($orderId, \Registry $registry) {
			parent::__construct($registry);

			$this->_productResolver = 
				new ProductResolver($registry);
			$this->_orderResolver = 
				new OrderResolver($registry);
			$this->_orderPartnerResolver = 
				new OrderPartnerResolver($registry);
			$this->_priceFormatter = 
				new PriceFormatter($registry);

			$this->_setOrder($orderId);
		}

		private function _setOrder($orderId) {
			$this->_sourceOrderId = 
				$orderId;
			$this->_sourceOrder = $this->_orderResolver
				->getOrder($orderId);
			$this->_sourceOrderProducts = $this->_orderResolver
				->getOrderProducts($orderId);
			$this->_sourceOrderTotals = $this->_orderResolver
				->getOrderTotals($orderId);
		}

		public function buildRemoteDocumentData() {
			$this->_reset();
			return $this->_buildOrderInformation();
		}

		private function _reset() {
			$this->_computedOrderShipping = null;
			$this->_computedOrderProductLines = array();	
			$this->_computedOrderDiscountLines = array();
			$this->_computedTotalSaleTax = 0;
		}

		private function _buildOrderInformation() {
			$orderShipping = $this->_buildOrderShipping();
			$orderProductLines = $this->_buildOrderProductLines();
			$orderDiscountLines = $this->_buildOrderDiscountLines();
			$customerBindingInformation = $this->_getOrderCustomerRemotePartnerBindingInformation();

			$orderData = array(
				'order_partner_code' => !empty($customerBindingInformation)
					? $customerBindingInformation['remote_partner_code']
					: null,
				'order_partner_billing_addr_worksite_id' => !empty($customerBindingInformation)
					? $customerBindingInformation['remote_partner_addr_worksite_id']
					: null,

				'order_curency_symbol' => $this->_sourceOrder['currency_code'],
				'order_notes' => $this->_sourceOrder['comment'],

				'order_shipping' => $this->_shouldAddShippingToDocument() 
					? $orderShipping 
					: null,
				'order_product_lines' => $orderProductLines,
				'order_discount_lines' => $orderDiscountLines
			);

			return $orderData;
		}

		private function _getOrderCustomerRemotePartnerBindingInformation() {
			$customerId = intval($this->_sourceOrder['customer_id']);
			return $this->_orderPartnerResolver
				->getOrderCustomerRemotePartnerBindingInformation($this->_sourceOrderId, 
					$customerId);
		}

		private function _buildOrderShipping() {
			$shippingPriceNoVat = $this->_getShippingPrice();
			$shippingPriceWithVat = $this->_calculateShippingPriceWithVat($shippingPriceNoVat);

			$this->_computedOrderShipping = array(
				'shipping_total_no_vat' => $shippingPriceNoVat,
				'shipping_price_out_with_vat' => $shippingPriceWithVat
			);

			$this->_computedTotalSaleTax += ($shippingPriceWithVat 
				- $shippingPriceNoVat);

			return $this->_computedOrderShipping;
		}

		private function _getShippingPrice() {
			$shipping = 0;

			foreach ($this->_sourceOrderTotals as $totalItem) {
				if ($this->_isShippingTotalItem($totalItem)) {
					$shipping += floatval($totalItem['value']);
				}
			}
			
			return $shipping;
		}

		private function _isShippingTotalItem(array $totalItem) {
			return $totalItem['code'] 
				== 'shipping';
		}

		private function _calculateShippingPriceWithVat($shippingPriceWithoutVat) {
			$shippingVatQuotaValue = $this->_getConfiguredShippingVatQuotaValue();
			return $this->_calculatePriceWithVat($shippingPriceWithoutVat, 
				$shippingVatQuotaValue);
		}

		private function _getConfiguredShippingVatQuotaValue() {
			return $this->_getStoreBinding()
				->getShippingVatQuotaValue();
		}

		private function _calculatePriceWithVat($priceWithoutVat, $vatQuotaValue) {
			return $this->_priceFormatter
				->calculatePriceWithVat($priceWithoutVat, 
					$vatQuotaValue);
		}

		private function _buildOrderProductLines() {
			$lines = array();
			foreach ($this->_sourceOrderProducts as $orderProduct) {
				$docProductLine = $this->_buildOrderProductLine($orderProduct);;
				if ($docProductLine !== null) {
					$lines[] = $docProductLine;
				}
			}

			$this->_computedOrderProductLines = $lines;
			return $this->_computedOrderProductLines;
		}

		private function _buildOrderProductLine($orderProduct) {
			//Notes about opencart product item structure:
			//	- only final, discounted price is stored, 
			//		so we need to lookup the product itself to see 
			//		if there is any product-sale type of discount
			//	- stored tax is calculated per-unit price, 
			//		so we need to multiply it by quantity to obtain 
			//		the total tax due for a product
			//	- product sku is not stored
			//	- product tax class id is not stored

			$productId = intval($orderProduct['product_id']);
			$product = $this->_getProduct($productId);
			if (empty($product)) {
				return null;
			}
			
			$quantity = intval($orderProduct['quantity']);
			$saleUnitPriceNoVat = floatval($orderProduct['price']);

			$productTaxClassId = intval($product['tax_class_id']);
			$baseUnitPriceNoVat = floatval($product['price']);
			if (empty($baseUnitPriceNoVat)) {
				$baseUnitPriceNoVat = $saleUnitPriceNoVat;
			}

			$saleUnitPriceTax = floatval($orderProduct['tax']);
			$totalSalePriceNoVat = floatval($orderProduct['total']);
			$totalSalePriceTax = $saleUnitPriceTax * $quantity;

			//If the base price is greater than the sale price, we consider this to be a discount
			//	otherwise, it's a regular price change and we just keep the sale price
			if ($baseUnitPriceNoVat - $saleUnitPriceNoVat >= self::DISCOUNT_DELTA) {
				$totalBasePriceNoVat = $baseUnitPriceNoVat * $quantity;
				$baseUnitPriceTax = $this->_calculateTax($baseUnitPriceNoVat, 
					$productTaxClassId);
				$totalBasePriceTax = $this->_calculateTax($totalBasePriceNoVat, 
					$productTaxClassId);
			} else {
				$baseUnitPriceNoVat = $saleUnitPriceNoVat;
				$totalBasePriceNoVat = $totalSalePriceNoVat;
				$totalBasePriceTax = $totalSalePriceTax;
				$baseUnitPriceTax = $saleUnitPriceTax;
			}

			$discount = $totalBasePriceNoVat 
				- $totalSalePriceNoVat;
			$discountTax = $totalBasePriceTax 
				- $totalSalePriceTax;

			$documentLine = array(
				'item_id' => $orderProduct['order_product_id'],
				'item_name' => $orderProduct['name'],
				'item_remote_id' => $this->_getRemoteArticleId($productId),
				'item_code' => $this->_getProductCode($productId),
				'item_product_id' => $productId,
				'item_variation_id' => null,
				'item_batch_tracking_enabled' => $this->_isBatchTrackingEnabled($productId),
				'item_qty' => $quantity,

				'item_price_out_no_vat' => $totalBasePriceNoVat,
				'item_price_out_with_vat' => $totalBasePriceNoVat + $totalBasePriceTax,

				'item_price_out_discounted_no_vat' => $totalSalePriceNoVat,
				'item_price_out_discounted_with_vat' => $totalSalePriceNoVat + $totalSalePriceTax,

				'item_unit_price_out_no_vat' => $baseUnitPriceNoVat,
				'item_unit_price_out_with_vat' => $baseUnitPriceNoVat + $baseUnitPriceTax,

				'item_unit_price_out_discounted_no_vat' => $saleUnitPriceNoVat,
				'item_unit_price_out_discounted_with_vat' => $saleUnitPriceNoVat + $saleUnitPriceTax,

				'item_vat_out_option_name' => $this->_getVatOutOptionName($productId),
				'item_vat_out_quota_value' => $this->_getVatOutQuotaValue($productId),

				'item_discount' => $discount,
				'item_discount_tax' => $discountTax,
				'item_discount_percent' => $this->_computeDiscountPercent($discount, 
					$totalBasePriceNoVat)
			);

			$this->_computedTotalSaleTax += $totalSalePriceTax;
			return $documentLine;
		}

		private function _getProduct($productId) {
			return $this->_productResolver
				->getProduct($productId);
		}

		private function _getRemoteArticleId($productId) {
			return $this->_productResolver
				->lookupRemoteArticleId($productId);
		}

		private function _getProductCode($productId) {
			return $this->_productResolver
				->lookupProductSku($productId);
		}

		private function _isBatchTrackingEnabled($productId) {
			return $this->_productResolver
				->getBatchTrackingStatus($productId) == 1;
		}

		private function _calculateTax($amount, $taxClassId) {
			if (empty($taxClassId)) {
				return 0;
			}

			$taxCalc = $this->_getTaxCalculator();
			return $taxCalc->getTax($amount, 
				$taxClassId);
		}

		private function _getVatOutOptionName($productId) {
			return $this->_productResolver
				->getVatOutOptionName($productId);
		}

		private function _getVatOutQuotaValue($productId) {
			return $this->_productResolver
				->getVatOutQuotaValue($productId);
		}

		private function _computeDiscountPercent($discount, $subtotal) {
			return $subtotal > 0 && $discount > 0 
				? $this->_roundPrice(((float)$discount / $subtotal) * 100) 
				: 0;
		}

		private function _roundPrice($price) {
			return $this->_priceFormatter
				->roundPrice($price);
		}

		private function _buildOrderDiscountLines() {
			$discounts = array();
			$totalDiscount = $this->_calculateTotalDiscount();
			$discountVatQuotaValue = $this->_getDiscountDocumentVatQuotaValue();
			$totalDiscountNotVat = $this->_calculatePriceNoVat($totalDiscount, 
				$discountVatQuotaValue);
			
			if ($totalDiscount >= self::DISCOUNT_DELTA) {
				$discounts[] = array(
					'item_discount' => $totalDiscountNotVat,
					'item_discount_tax' => $totalDiscount - $totalDiscountNotVat,
					'item_vat_out_quota_value' => $discountVatQuotaValue
				);
			}

			$this->_computedOrderDiscountLines = $discounts;
			return $this->_computedOrderDiscountLines;
		}

		private function _calculateTotalDiscount() {
			$totalDiscountNotVat = $this->_calculateTotalDiscountNoVat();
			$totalDiscountTax = $this->_calculateTotalDiscountTax();
			return $this->_roundPrice($totalDiscountNotVat + $totalDiscountTax);
		}

		private function _calculateTotalDiscountNoVat() {
			$totalDiscount = 0;
			foreach ($this->_sourceOrderTotals as $totalItem) {
				if ($this->_isTotalDiscountItem($totalItem)) {
					$totalDiscount += abs(floatval($totalItem['value']));
				}
			}
			return $totalDiscount;
		}

		private function _isTotalDiscountItem($totalItem) {
			return !in_array($totalItem['code'], 
				array(
					'tax', 
					'sub_total', 
					'total', 
					'shipping'
				)
			);
		}

		private function _calculateTotalDiscountTax() {
			return $this->_calculateTotalUnDiscountedTax() 
				- $this->_calculateTotalTax();
		}

		private function _calculateTotalUnDiscountedTax() {
			return $this->_computedTotalSaleTax;
		}

		private function _calculateTotalTax() {
			$totalTax = 0;
			foreach ($this->_sourceOrderTotals as $totalItem) {
				if ($this->_isTaxTotalItem($totalItem)) {
					$totalTax += floatval($totalItem['value']);
				}
			}
			return $totalTax;
		}

		private function _isTaxTotalItem($totalItem) {
			return $totalItem['code'] == 'tax';
		}

		private function _getDiscountDocumentVatQuotaValue() {
			return $this->_getConfiguredShippingVatQuotaValue();
		}

		private function _calculatePriceNoVat($priceWithVat, $vatQuotaValue) {
			$priceNoVat = $this->_priceFormatter
				->calculatePriceWithoutVat($priceWithVat, 
					$vatQuotaValue);
			return $this->_roundPrice($priceNoVat);
		}

		private function _shouldAddShippingToDocument() {
			return $this->_getWorkflow()
				->getAddShippingToDocument();
		}

		/**
		 * @return \Cart\Tax
		 */
		private function _getTaxCalculator() {
			return $this->tax;
		}
	}
}
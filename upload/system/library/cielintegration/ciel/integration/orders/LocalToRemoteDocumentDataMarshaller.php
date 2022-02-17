<?php
namespace Ciel\Api\Integration\Orders {
	class LocalToRemoteDocumentDataMarshaller {
		/**
		 * @var RemoteShippingArticleResolver
		 */
		private $_remoteShippingArticleResolver;

		/**
		 * @var RemoteDiscountArticleResolver
		 */
		private $_remoteDiscountArticleResolver;

		/**
		 * @var RemoteDocumentOptions
		 */
		private $_options;

		public function __construct(
			RemoteShippingArticleResolver $remoteShippingArticleResolver, 
			RemoteDiscountArticleResolver $remoteDiscountArticleResolver,
			RemoteDocumentOptions $options
		) {
			$this->_remoteShippingArticleResolver = $remoteShippingArticleResolver;
			$this->_remoteDiscountArticleResolver = $remoteDiscountArticleResolver;
			$this->_options = $options;
		}

		public function getRemoteDocumentData(array $orderData) {
			return $this->_createDocument($orderData);
		}

		private function _createDocument(array $orderData) {
            $document = $this->_createDocumentHeader($orderData);
			$shippingArticle = $this->_getShippingArticle();
			$discountAggregator = new CielErpDocumentDiscountAggregator();

            foreach ($orderData['order_product_lines'] as $orderLineProduct) {
                $discountAggregator->collectForOrderProductLine($orderLineProduct);
                $document['DocumentLines'][] = $this->_createDocumentArticleLine($orderLineProduct);
            }

            if (!empty($orderData['order_shipping'])) {
                $document['DocumentLines'][] = $this->_createDocumentShippingLine($orderData['order_shipping'], 
                    $shippingArticle);
            }

            if ($discountAggregator->hasDiscountInfo()) {
                foreach ($discountAggregator->getDiscountInfoForVatQuotas() as $vatQuotaValue => $discountInfo) {
                    $document['DocumentLines'][] = $this->_createDocumentDiscountLine($vatQuotaValue, 
                        $discountInfo);
                }
            }

            return $document;
        }

		private function _createDocumentHeader(array $orderData) {
			$document = array(
				'PartnerCode' => $orderData['order_partner_code'],
				'CurrencySymbol' => $orderData['order_curency_symbol'],
				'WarehouseCode' => $this->_options->getWarehouseCode(),
				'Notes' => $orderData['order_notes'],
				'DocumentLines' => array()
			);

			$series = $this->_options->getDocumentSeries();
			if (!empty($series)) {
				$document['Series'] = $series;
			}

			$dueDays = $this->_options->getDueDays();
			if (!empty($dueDays)) {
				$document['DueDate'] = $this->_formatDueDate($dueDays);
			}

			if (!empty($orderData['order_partner_billing_addr_worksite_id'])) {
				$document['PartnerWorksiteId'] = $orderData['order_partner_billing_addr_worksite_id'];
			}

			return $document;
		}

		private function _getShippingArticle() {
			return $this->_remoteShippingArticleResolver
				->getShippingArticle();
		}

		private function _createDocumentArticleLine(array $orderLineProduct) {
            return array(
                'ArticleCode' => $orderLineProduct['item_code'],
                'Quantity' => $orderLineProduct['item_qty'],
                'PriceOut' => $orderLineProduct['item_unit_price_out_no_vat'],
                'PriceOutWithVAT' => $orderLineProduct['item_unit_price_out_with_vat'],
                'VatOutOptionId' => null,
                'VATOutOptionName' => $orderLineProduct['item_vat_out_option_name'],
                'VATOutQuotaId' => null,
                'VATOutQuotaValue' => $orderLineProduct['item_vat_out_quota_value']
            );
        }

		private function _createDocumentDiscountLine($vatQuotaValue, array $discountInfo) {
            $discountArticle = $this->_getRemoteDiscountArticleForVatQuotaValue($vatQuotaValue);
            return array(
                'ArticleCode' => $discountArticle['Code'],
                'Quantity' => -1,
                'PriceInLocalCurrency' => $discountInfo['discount_value'],
                'PriceOut' => $discountInfo['discount_value'],
                'PriceOutWithVAT' => $discountInfo['discount_value'] + $discountInfo['discount_tax_value'],
                'VatOutOptionId' => null,
                'VATOutOptionName' => $discountArticle['VatOutOptionName'],
                'VATOutQuotaId' => null,
                'VATOutQuotaValue' => $vatQuotaValue
            );
        }

		private function _getRemoteDiscountArticleForVatQuotaValue($vatQuotaValue) {
			return $this->_remoteDiscountArticleResolver
				->getDiscountArticleForVatQuotaValue($vatQuotaValue);
		}

		private function _createDocumentShippingLine(array $orderShipping, array $remoteShippingArticle) {
            return array(
                'ArticleCode' => $remoteShippingArticle['Code'],
                'Quantity' => 1,
                'PriceOut' => $orderShipping['shipping_total_no_vat'],
                'PriceOutWithVAT' => $orderShipping['shipping_price_out_with_vat'],
                'VatOutOptionId' => null,
                'VATOutOptionName' => $remoteShippingArticle['VatOutOptionName'],
                'VATOutQuotaId' => null,
                'VATOutQuotaValue' => $this->_options->getShippingVatQuotaValue(),
                'Discount' => 0
            );
        }

		private function _formatDueDate($dueDays) {
			return date('Y-m-d', time() + $dueDays * 24 * 3600);
		}
	}
}

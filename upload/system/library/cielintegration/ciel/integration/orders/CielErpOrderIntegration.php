<?php
namespace Ciel\Api\Integration\Orders {

	use Ciel\Api\CielClient;
	use Ciel\Api\Data\DocumentType;
	use Ciel\Api\Exception\DocumentAlreadyIssuedForOrderException;
    use Ciel\Api\Exception\DocumentCancellationNotSupportedException;
    use Ciel\Api\Exception\IssueDocumentNotEnabledException;
	use Ciel\Api\Exception\LocalOrderNotFoundException;
	use Ciel\Api\Exception\OrderNotEligibleForDocumentIssueException;
	use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
	use Ciel\Api\Integration\Orders\DischargePolicies\NoBatchDischargePolicy;
	use Ciel\Api\Integration\Orders\Providers\CielErpLocalOrderAdapter;
	use Ciel\Api\Request\Parameters\AddSaleInvoiceRequestParameters;
	use Ciel\Api\Request\Parameters\AddSaleOrderRequestParameters;
    use Ciel\Api\Request\Parameters\CancelSaleInvoicesParameters;
    use Ciel\Api\Request\Parameters\DeleteDocumentRequestParams;
	use Ciel\Api\Request\Parameters\GetArticleByCodeRequestParameters;
	use Ciel\Api\Request\Parameters\SelectFromViewRequestParameters;
	use InvalidArgumentException;

	class CielErpOrderIntegration {
		/**
		 * @var \Ciel\Api\Integration\Binding\CielErpToStoreBinding
		 */
		private $_storeBinding;

		/**
		 * @var \Ciel\Api\Integration\Orders\Providers\CielErpLocalOrderAdapter
		 */
		private $_adapter;

		public function __construct(CielErpToStoreBinding $storeBinding, 
			CielErpLocalOrderAdapter $adapter) {
			$this->_storeBinding = $storeBinding;
			$this->_adapter = $adapter;
		}

		/**
		 * @return \Ciel\Api\CielClient 
		 */
		private function _getCielClientAndLogon() {
		   return $this->_storeBinding->getAmbientCielClient(true);
		}

		public function isDocumentIssuedForOrder($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			return $this->_adapter->isDocumentIssuedForOrder($localId);
		}

		public function lookupRemoteDocumentDataForOrder($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			return $this->_adapter->lookupRemoteDocumentDataForOrder($localId);
		}

		public function canDocumentBeIssuedForOrder($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			return $this->_adapter->canDocumentBeIssuedForOrder($localId);
		}

		public function canOrderItemsBeAddedToDocument($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			return $this->_adapter->canOrderItemsBeAddedToDocument($localId);
		}

		public function getRemoteArticleIdsForOrder($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			$articleIds = array();
			$orderData = $this->_adapter->getOrderData($localId);

			if (!empty($orderData) && !empty($orderData['order_product_lines'])) {
				foreach ($orderData['order_product_lines'] as $item) {
					if (!empty($item['item_remote_id'])) {
						$articleIds[] = intval($item['item_remote_id']);
					}
				}
			}

			return array_unique($articleIds);
		}

		public function getLocalArticleIdsForOrder($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			$articleIds = array();
			$orderData = $this->_adapter->getOrderData($localId);

			if (!empty($orderData) && !empty($orderData['order_product_lines'])) {
				foreach ($orderData['order_product_lines'] as $item) {
					$articleIds[] = intval($item['item_product_id']);
				}
			}

			return array_unique($articleIds);
		}

		public function issueDocumentForOrder($localId, BatchDischargePolicy $batchDischargePolicy) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			$documentType = $this->_getIssueDocumentType();
			if (empty($documentType)) {
				throw new IssueDocumentNotEnabledException();
			}

			if ($this->isDocumentIssuedForOrder($localId)) {
				throw new DocumentAlreadyIssuedForOrderException($localId);
			}

			if (!$this->canDocumentBeIssuedForOrder($localId)) {
				throw new OrderNotEligibleForDocumentIssueException($localId);
			}

			if (!$this->isBatchDischargePossible()) {
				$batchDischargePolicy = new NoBatchDischargePolicy();
			}

			$orderData = $this->_adapter->getOrderData($localId);
			if (!empty($orderData)) {
				$client = $this->_getCielClientAndLogon();
				$shippingArticle = $this->_getShippingArticle($client);

				if (!empty($shippingArticle)) {
					$documentId = null;

					$status = $this->_getStatusForNewDocument();
					$document = $this->_createDocument($orderData,
						$shippingArticle, 
						$batchDischargePolicy);

					switch ($documentType) {
						case DocumentType::SaleOrder:
							$documentId = $this->_createSaleOrder($client, 
								$document, 
								$status);
						break;
						case DocumentType::SaleInvoice:
							$documentId = $this->_createSaleInvoice($client, 
								$document, 
								$status);
						break;
					}
				}

				if (!empty($documentId)) {
					$this->_adapter->setDocumentIssuedForOrder($localId, 
						$documentId, 
						$documentType);
				}
			}
		}

		private function _getIssueDocumentType() {
			return $this->_storeBinding->getIssueDocumentType();
		}

		public function removeDocumentForOrder($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if (!$this->isDocumentIssuedForOrder($localId)) {
				return;
			}

			$remoteDocumentData = $this->_adapter->lookupRemoteDocumentDataForOrder($localId);
			if (!empty($remoteDocumentData)) {
				if ($this->_remoteDocumentCanBeDeleted($remoteDocumentData)) {
					$client = $this->_getCielClientAndLogon();
					$this->_deleteRemoteDocument($client, $remoteDocumentData);
				}

				$this->_adapter->setDocumentRemovedForOrder($localId);
			} else {
				throw new LocalOrderNotFoundException('id', $localId);
			}
		}

		public function cancelDocumentForOrder($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if (!$this->isDocumentCancellationSupported()) {
				throw new DocumentCancellationNotSupportedException($localId);
			}

			if (!$this->isDocumentIssuedForOrder($localId)) {
				return;
			}

			$remoteDocumentData = $this->_adapter->lookupRemoteDocumentDataForOrder($localId);
			if (!empty($remoteDocumentData)) {
				if ($this->_remoteDocumentCanBeCancelled($remoteDocumentData)) {
					$client = $this->_getCielClientAndLogon();
					$this->_cancelRemoteDocument($client, $remoteDocumentData);
				}

				$this->_adapter->setRemoteDocumentCancelledForOrder($localId);
			} else {
				throw new LocalOrderNotFoundException('id', $localId);
			}
		}

		/**
		 * @return OrderItemBatchConfiguration[]
		 */
		public function getBatchesForOrderProducts($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			$orderItemsBatches = array();
			$orderData = $this->_adapter->getOrderData($localId);

			if (!empty($orderData) && !empty($orderData['order_product_lines'])) {
				$warehouseBatches = $this->_getBatchesForCurrentWarehouse();
				if (!empty($warehouseBatches)) {
					$binder = new OrderItemBatchBinder($orderData['order_product_lines']);
					$orderItemsBatches = $binder->bind($warehouseBatches);
				}
			}

			return $orderItemsBatches;
		}

		private function _getBatchesForCurrentWarehouse() {
			$client = $this->_getCielClientAndLogon();
			return $client->selectFromView((new SelectFromViewRequestParameters())
				->setViewName('CIEL_WebShop_ProductLots')
				->setQuery(array(
					'Where' => array(array(
						'Column' => 'StockWarehouseId',
						'Operator' => '=',
						'Value' => $this->_getCurrentWarehouseId()
					))
				)));
		}

		private function _getCurrentWarehouseId() {
			return $this->_storeBinding->getWarehouseId();
		}

		public function getRemoteDocumentLinesForLocalOrderId($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			$remoteDocumentData = $this->_adapter->lookupRemoteDocumentDataForOrder($localId);
			if (!empty($remoteDocumentData)) {
				return $this->_getRemoteDocumentLines($remoteDocumentData['id']);
			} else {
				return null;
			}
		}

		private function _getRemoteDocumentLines($remoteDocumentId) {
			$client = $this->_getCielClientAndLogon();
			return $client->selectFromView((new SelectFromViewRequestParameters())
				->setViewName('CIEL_Android_DocumentLinesFromDocument')
				->setQuery(array(
					'Where' => array(array(
						'Column' => 'DocumentId',
						'Operator' => '=',
						'Value' => $remoteDocumentId
					))
				)));
		}

		private function _createDocument(array $orderData, 
			array $shippingArticle, 
			BatchDischargePolicy $batchDischargePolicy) {

			$document = $this->_createDocumentHeader($orderData);
			$discountAggregator = new CielErpDocumentDiscountAggregator();

			$documentLines = array();
			foreach ($orderData['order_product_lines'] as $orderLineProduct) {
				$discountAggregator->collectForOrderProductLine($orderLineProduct);
				$articleLinesForProductLine = $this->_createDocumentArticleLines($orderLineProduct, $batchDischargePolicy);
				foreach ($articleLinesForProductLine as $articleLine) {
					$documentLines[] = $articleLine;
				}
			}

			if (!empty($orderData['order_discount_lines'])) {
				foreach ($orderData['order_discount_lines'] as $discountLine) {
					$discountAggregator->collectForOrderProductLine($discountLine);
				}
			}

			if (!empty($orderData['order_shipping'])) {
				$documentLines[] = $this->_createDocumentShippingLine(
					$orderData['order_shipping'], 
					$shippingArticle
				);
			}

			if ($discountAggregator->hasDiscountInfo()) {
				foreach ($discountAggregator->getDiscountInfoForVatQuotas() as $vatQuotaValue => $discountInfo) {
					$documentLines[] = $this->_createDocumentDiscountLine(
						$vatQuotaValue, 
						$discountInfo
					);
				}
			}

			$document['DocumentLines'] = $documentLines;
			$discountAggregator->clear();

			$document = $this->_setDocumentOptions($document, 
				$orderData);

			return $document;
		}

		private function _createDocumentHeader(array $orderData) {
			$document = array(
				'PartnerCode' => $orderData['order_partner_code'],
				'CurrencySymbol' => $orderData['order_curency_symbol'],
				'WarehouseCode' => $this->_getWarehouseCodeForNewDocument(),
				'Notes' => $orderData['order_notes'],
				'DocumentLines' => array()
			);

			$series = $this->_getSeriesForNewDocument();
			if (!empty($series)) {
				$document['Series'] = $series;
			}

			if (empty($orderData['order_date'])) {
				$document['Date'] = $this->_formatNow();
			} else {
				$document['Date'] = $orderData['order_date'];
			}

			$dueDays = $this->_getDueDaysForNewDocument();
			if (!empty($dueDays)) {
				$document['DueDate'] = $this->_formatDueDate($dueDays);
			}

			if (!empty($orderData['order_partner_billing_addr_worksite_id'])) {
				$document['PartnerWorksiteId'] = $orderData['order_partner_billing_addr_worksite_id'];
			}

			return $document;
		}

		private function _createDocumentDiscountLine($vatQuotaValue, array $discountInfo) {
			$discountArticle = $this->_storeBinding->setupDiscountForVatQuotaValueIfNeeded($vatQuotaValue);
			
			$discountValue = $this->_formatAmount($discountInfo['discount_value']);
			$discountValueWithVat = $this->_formatAmount($discountInfo['discount_value'] 
				+ $discountInfo['discount_tax_value']);
			
			return array(
				'ArticleCode' => $discountArticle['Code'],
				'Quantity' => -1,
				'PriceInLocalCurrency' => $discountValue,
				'PriceOut' => $discountValue,
				'PriceOutWithVAT' => $discountValueWithVat,
				'VatOutOptionId' => null,
				'VATOutOptionName' => $discountArticle['VatOutOptionName'],
				'VATOutQuotaId' => null,
				'VATOutQuotaValue' => $vatQuotaValue
			);
		}

		private function _formatAmount($amount) {
			return number_format($amount, 2, '.', '');
		}

		private function _createDocumentArticleLines(array $orderLineProduct, BatchDischargePolicy $batchDischargePolicy) {
			$articleLines = array();
			$itemCode = $orderLineProduct['item_code'];

			$baseArticleLine = array(
				'ArticleCode' => $itemCode,
				'Quantity' => null,
				'PriceOut' => $orderLineProduct['item_unit_price_out_no_vat'],
				'PriceOutWithVAT' => $orderLineProduct['item_unit_price_out_with_vat'],
				'VatOutOptionId' => null,
				'VATOutOptionName' => $orderLineProduct['item_vat_out_option_name'],
				'VATOutQuotaId' => null,
				'VATOutQuotaValue' => $orderLineProduct['item_vat_out_quota_value']
			);

			$batchesToDischarge = $batchDischargePolicy->determineBatchesToDischarge($itemCode);
			if (!empty($batchesToDischarge)) {
				foreach ($batchesToDischarge as $batch) {
					$articleLines[] = array_merge($baseArticleLine, array(
						'LotId' => $batch['item_lot_id'],
						'Lot_Code' => $batch['item_lot_code'],
						'Quantity' => $batch['item_qty']
					));
				}
			} else {
				$articleLines[] = array_merge($baseArticleLine, array(
					'Quantity' => $orderLineProduct['item_qty']
				));
			}

			return $articleLines;
		}

		private function _createDocumentShippingLine(array $orderShipping, array $shippingArticle) {
			return array(
				'ArticleCode' => $shippingArticle['Code'],
				'Quantity' => 1,
				'PriceOut' => $orderShipping['shipping_total_no_vat'],
				'PriceOutWithVAT' => $orderShipping['shipping_price_out_with_vat'],
				'VatOutOptionId' => null,
				'VATOutOptionName' => $shippingArticle['VatOutOptionName'],
				'VATOutQuotaId' => null,
				'VATOutQuotaValue' => $this->_storeBinding->getShippingVatQuotaValue(),
				'Discount' => 0
			);
		}

		private function _setDocumentOptions(array &$document, array $orderData) {
			if (!empty($orderData['order_options'])) {
				$orderOptions = $orderData['order_options'];
				
				if (isset($orderOptions['add_vat_on_payment_note']) 
					&& $orderOptions['add_vat_on_payment_note'] === true) {
					$orderData['IsVatCollectableOnPayment'] = true;
				}

				if (isset($orderOptions['is_simplified_invoice'])
					&& $orderOptions['is_simplified_invoice'] === true) {
					$orderOptions['IsSimplifiedInvoice'] = true;
				}
			}

			return $document;
		}

		/**
		 * @param int $localId 
		 * @return \Ciel\Api\Integration\Orders\DocumentPreRequisiteStatus
		 */
		public function determineOrderDocumentPreRequisitesStatus($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			return $this->_adapter->determineOrderDocumentPreRequisitesStatus($localId);
		}

		public function issueDocumentEnabled() {
			$issueDocumentType = $this->_storeBinding
				->getIssueDocumentType();

			return $issueDocumentType == DocumentType::SaleInvoice 
				|| $issueDocumentType == DocumentType::SaleOrder;
		}

		public function isBatchDischargeSupported() {
			return $this->_adapter->isBatchDischargeSupported();
		}

		public function isDocumentCancellationSupported() {
			return $this->_adapter->isDocumentCancellationSupported();
		}

		public function isBatchDischargePossible() {
			return $this->_isBoundToEnGrossWarehouse();
		}

		private function _isBoundToEnGrossWarehouse() {
			return $this->_storeBinding->isBoundToEnGrossWarehouse();
		}

		private function _getShippingArticle(CielClient $client) {
			$getByCodeParams = (new GetArticleByCodeRequestParameters())
				->setCode($this->_getShippingArticleCode())
				->setIncludeArticleWarehousePrice(false)
				->setIncludeArticleWarehouseStock(false);

			return $client->getArticleByCode($getByCodeParams);
		}

		private function _createSaleOrder(CielClient $client, array $document, $status) {
			return $client->addSaleOrder((new AddSaleOrderRequestParameters())
				->setDocument($document)
				->setDocumentStatusId($status));
		}

		private function _createSaleInvoice(CielClient $client, array $document, $status) {
			return $client->addSaleInvoice((new AddSaleInvoiceRequestParameters())
				->setDocument($document)
				->setDocumentStatusId($status));
		}

		private function _deleteRemoteDocument(CielClient $client, $remoteDocumentData) {
			return $client->deleteDocument((new DeleteDocumentRequestParams())
				->setDocumentId($remoteDocumentData['id']));
		}

		private function _cancelRemoteDocument(CielClient $client, $remoteDocumentData) {
			if ($this->_isSaleInvoice($remoteDocumentData)) {
				$this->_cancelRemoteSaleInvoice($client, $remoteDocumentData);
			}
		}

		private function _cancelRemoteSaleInvoice(CielClient $client, $remoteDocumentData) {
			return $client->cancelInvoices((new CancelSaleInvoicesParameters())
				->addDocumentId($remoteDocumentData['id']));
		}

		private function _remoteDocumentCanBeDeleted($remoteDocumentData) {
			return $this->_isSaleInvoice($remoteDocumentData);
		}

		private function _remoteDocumentCanBeCancelled($remoteDocumentData) {
			return $this->_isSaleInvoice($remoteDocumentData);
		}

		private function _isSaleInvoice($remoteDocumentData) {
			return $remoteDocumentData['type'] == DocumentType::SaleInvoice;
		}

		private function _formatDueDate($dueDays) {
			return date('Y-m-d', time() + $dueDays * 24 * 3600);
		}

		private function _formatNow() {
			return date('Y-m-d', time());
		}

		private function _getShippingArticleCode() {
			return $this->_storeBinding->getShippingArticleCode();
		}

		private function _getWarehouseCodeForNewDocument() {
			return $this->_storeBinding->getWarehouseCode();
		}

		private function _getStatusForNewDocument() {
			return $this->_storeBinding->getIssueDocumentWithStatus();
		}

		public function _getSeriesForNewDocument() {
			return $this->_storeBinding->getIssueDocumentWithSeries();
		}

		private function _getDueDaysForNewDocument() {
			return $this->_storeBinding->getIssueDocumentWithDueDays();
		}
	}
}
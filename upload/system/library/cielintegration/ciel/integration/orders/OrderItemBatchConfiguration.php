<?php
namespace Ciel\Api\Integration\Orders {
	class OrderItemBatchConfiguration {
		private $_lineItem = array();

		/**
		 * @var OrderItemBatch[]
		 */
		private $_itemBatches = array();

		private $_remainingQuantity;

		public function __construct(array $lineItem) {
			$this->_lineItem = $lineItem;
			$this->_remainingQuantity = $lineItem['item_qty'];
		}

		public function requestQuantityFromBatch(array $itemBatchInfo) {
			$itemBatch = new OrderItemBatch($itemBatchInfo);
			if ($itemBatch->getMaxQuantity() > 0) {
				$this->_remainingQuantity = $itemBatch->requestQuantity($this->_remainingQuantity);
				$this->_itemBatches[] = $itemBatch;
			}
		}

		public function getLineItem() {
			return $this->_lineItem;
		}

		public function getItemId() {
			return $this->_lineItem['item_id'];
		}

		public function getItemName() {
			return $this->_lineItem['item_name'];
		}

		public function getProductId() {
			return $this->_lineItem['item_product_id'];
		}

		public function getProductCode() {
			return $this->_lineItem['item_code'];
		}

		public function getRequiredQuantity() {
			return $this->_lineItem['item_qty'];
		}

		public function getRemainingQuantity() {
			return $this->_remainingQuantity;
		}

		public function getItemBatches() {
			return $this->_itemBatches;
		}

		public function entireQuantitySatisfied() {
			return $this->_remainingQuantity == 0;
		}

		public function asPlainObject() {
			$itemBatchConfig = new \stdClass();
			$itemBatchConfig->itemId = $this->getItemId();
			$itemBatchConfig->itemName = $this->getItemName();
			
			$itemBatchConfig->productId = $this->getProductId();
			$itemBatchConfig->productCode = $this->getProductCode();

			$itemBatchConfig->requiredQuantity = $this->getRequiredQuantity();
			$itemBatchConfig->remainingQuantity = $this->getRemainingQuantity();
			$itemBatchConfig->entireQuantitySatisfied = $this->entireQuantitySatisfied();

			$itemBatchConfig->itemBatches = array();
			foreach ($this->getItemBatches() as $b) {
				$itemBatchConfig->itemBatches[] = $b->asPlainObject();
			}
			
			$itemBatchConfig->lineItem = $this->getLineItem();
			return $itemBatchConfig;
		}
	}
}
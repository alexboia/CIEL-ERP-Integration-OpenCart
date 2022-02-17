<?php
namespace Ciel\Api\Integration\Orders {
	class OrderItemBatch {
		private $_productCode;

		private $_batchId;

		private $_batchCode;

		private $_batchName;

		private $_batchExpiration;

		private $_maxQuantity;

		private $_requestedQuantity;

		private $_requiredQuantity;

		public function __construct(array $remoteBatchInfo) {
			$this->_batchId = $remoteBatchInfo['LotId'];
			$this->_batchCode = $remoteBatchInfo['LotCode'];
			$this->_batchName = $remoteBatchInfo['LotName'];
			$this->_batchExpiration = $remoteBatchInfo['LotExpiryDate'];
			$this->_productCode = $remoteBatchInfo['TargetArticleCode'];
			$this->_maxQuantity = $remoteBatchInfo['StockQuantityForLot'];
		}	

		public function requestQuantity($requiredQuantity) {
			$this->_requiredQuantity = $requiredQuantity;
			if ($this->_maxQuantity > $requiredQuantity) {
				$this->_requestedQuantity = $requiredQuantity;
			} else {
				$this->_requestedQuantity = $this->_maxQuantity;
			}

			return $requiredQuantity 
				- $this->_requestedQuantity;
		}

		public function getProductCode() {
			return $this->_productCode;
		}

		public function getBatchId() {
			return $this->_batchId;
		}

		public function getBatchCode() {
			return $this->_batchCode;
		}

		public function getBatchName() {
			return $this->_batchName;
		}

		public function getMaxQuantity() {
			return $this->_maxQuantity;
		}

		public function getBatchExpiration() {
			return $this->_batchExpiration;
		}

		public function getRequestedQuantity() {
			return $this->_requestedQuantity;
		}

		public function getRequiredQuantity() {
			return $this->_requiredQuantity;
		}

		public function asPlainObject() {
			$itemBatch = new \stdClass();
			$itemBatch->batchId = $this->getBatchId();
			$itemBatch->batchCode = $this->getBatchCode();
			$itemBatch->batchName = $this->getBatchName();
			$itemBatch->batchExpiration = $this->getBatchExpiration();
			$itemBatch->maxQuantity = $this->getMaxQuantity();
			$itemBatch->productCode = $this->getProductCode();
			$itemBatch->requestedQuantity = $this->getRequestedQuantity();
			$itemBatch->requiredQuantity = $this->getRequiredQuantity();
			return $itemBatch;
		}
	}
}
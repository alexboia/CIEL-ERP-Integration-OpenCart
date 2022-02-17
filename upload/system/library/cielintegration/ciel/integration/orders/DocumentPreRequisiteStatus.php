<?php
namespace Ciel\Api\Integration\Orders {
	class DocumentPreRequisiteStatus {
		/**
		 * @var DocumentPreRequisiteStatusItem[]
		 */
		private $_statusItems = array();

		private $_hasDisconnectedItems = false;

		private $_disconnecteItemsLocalIds = array();

		private $_hasItemsWithBatchTracking = false;

		private $_batchTrackedItemsLocalIds = array();

		private $_isBatchTrackingPossible = false;

		private $_isBatchTrackingSupported = false;

		public function isBatchTrackingPossible() {
			return $this->_isBatchTrackingPossible;
		}

		public function setIsBatchTrackingPossible($value) {
			$this->_isBatchTrackingPossible = $value;
			return $this;
		}

		public function isBatchTrackingSupported() {
			return $this->_isBatchTrackingSupported;
		}

		public function setIsBatchTrackingSupported($value) {
			$this->_isBatchTrackingSupported = $value;
			return $this;
		}

		public function addStatusItem(DocumentPreRequisiteStatusItem $item) {
			if (!$item->isConnected()) {
				$this->_hasDisconnectedItems = true;
				$this->_disconnecteItemsLocalIds[] = $item->getLocalId();
			}

			if ($item->isBatchTrackingEnabled()) {
				$this->_hasItemsWithBatchTracking = true;
				$this->_batchTrackedItemsLocalIds[] = $item->getLocalId();
			}

			$this->_statusItems[$item->getCode()] = $item;
		}

		public function getStatusItems() {
			return $this->_statusItems;
		}

		public function hasDisconnectedItems() {
			return $this->_hasDisconnectedItems;
		}

		public function getDisconnectedItemsLocalIds() {
			return $this->_disconnecteItemsLocalIds;
		}

		public function hasItemsWithBatchTracking() {
			return $this->_hasItemsWithBatchTracking;
		}

		public function getBatchTrackedItemsLocalIds() {
			return $this->_batchTrackedItemsLocalIds;
		}

		public function areAllItemsConnected() {
			return !$this->hasDisconnectedItems();
		}

		public function asPlainObject() {
			$status = new \stdClass();

			$status->statusItems = array();
			foreach ($this->getStatusItems() as $code => $statusItem) {
				$status->statusItems[$code] = $statusItem->asPlainObject();
			}

			$status->areAllItemsConnected = $this->areAllItemsConnected();

			$status->hasDisconnectedItems = $this->hasDisconnectedItems();
			$status->disconnectedLocalItemIds = $this->getDisconnectedItemsLocalIds();

			$status->isBatchTrackingSupported = $this->isBatchTrackingSupported();
			$status->isBatchTrackingPossible = $this->isBatchTrackingPossible();

			$status->hasItemsWithBatchTracking = $this->hasItemsWithBatchTracking();
			$status->batchTrackedLocalItemIds = $this->getBatchTrackedItemsLocalIds();

			return $status;
		}

		public function canOrderItemsBeAddedToDocument() {
			if (!$this->areAllItemsConnected()) {
				return false;
			}

			if ($this->hasItemsWithBatchTracking()) {
				return $this->isBatchTrackingPossible() 
					&& $this->isBatchTrackingSupported();
			} else {
				return true;
			}
		}
	}
}
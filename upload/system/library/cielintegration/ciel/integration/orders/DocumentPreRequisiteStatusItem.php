<?php
namespace Ciel\Api\Integration\Orders {
	class DocumentPreRequisiteStatusItem {
		/**
		 * @var int
		 */
		private $_localId;

		/**
		 * @var string
		 */
		private $_code;

		/**
		 * @var boolean
		 */
		private $_isConencted;

		/**
		 * @var boolean
		 */
		private $_isBatchTrackingEnabled;

		public function __construct($localId, $code, $isConnected, $isBatchTrackingEnabled) {
			$this->_localId = $localId;
			$this->_code = $code;
			$this->_isConencted = $isConnected;
			$this->_isBatchTrackingEnabled = $isBatchTrackingEnabled;
		}

		public function isConnected() {
			return $this->_isConencted;
		}

		public function isBatchTrackingEnabled() {
			return $this->_isBatchTrackingEnabled;
		}

		public function getCode() {
			return $this->_code;
		}

		public function getLocalId() {
			return $this->_localId;
		}

		public function asPlainObject() {
			$statusItem = new \stdClass();
			$statusItem->isConnected = $this->isConnected();
			$statusItem->isBatchTrackingEnabled = $this->isBatchTrackingEnabled();
			$statusItem->code = $this->getCode();
			$statusItem->localId = $this->getLocalId();
			return $statusItem;
		}
	}
}
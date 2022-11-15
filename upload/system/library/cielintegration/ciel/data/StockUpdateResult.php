<?php
namespace Ciel\Api\Data {

	use ArrayAccess;
	use Exception;

	class StockUpdateResult implements ArrayAccess {
		const KEY_UPDATED = 'updated';

		const KEY_REASON = 'reason';

		private $_updated = false;

		private $_reason = null;

		public function __construct($updated, $reason) {
			$this->_updated = $updated;
			$this->_reason = $reason;
		}

		public static function failedProductNotConnected() {
			return new self(false, StockUpdateFailureReason::NotConnected);
		}

		public static function failedShopStockManagementNotEnabled() {
			return new self(false, StockUpdateFailureReason::NotManagingStock);
		}

		public static function failedNoSourceStockData() {
			return new self(false, StockUpdateFailureReason::NoStockData);
		}

		public static function successful() {
			return new self(true, null);
		}

		public function offsetExists($offset): bool { 
			return $offset === self::KEY_UPDATED 
				|| $offset === self::KEY_REASON;
		}

		public function offsetGet($offset) { 
			if ($offset === self::KEY_UPDATED){
				return $this->getUpdated();
			} else if ($offset === self::KEY_REASON) {
				return $this->getReason();
			} else {
				return null;
			}
		}

		public function offsetSet($offset, $value): void { 
			$this->_throwReadOnly();
		}

		private function _throwReadOnly() {
			throw new Exception('Stock update result is read only!');
		}

		public function offsetUnset($offset): void { 
			$this->_throwReadOnly();
		}

		public function getUpdated() {
			return $this->_updated;
		}

		public function getReason() {
			return $this->_reason;
		}

		public function toArray() {
			return array(
				self::KEY_REASON => $this->getReason(),
				self::KEY_UPDATED => $this->getUpdated()
			);
		}
	}
}
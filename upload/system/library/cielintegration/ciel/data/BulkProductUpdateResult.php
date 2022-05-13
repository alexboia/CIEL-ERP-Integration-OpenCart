<?php
namespace Ciel\Api\Data {
	use ArrayAccess;
    use Exception;

	class BulkProductUpdateResult implements ArrayAccess {
		const KEY_ELIGIBLE_COUNT = 'eligible';

		const KEY_UPDATED_COUNT = 'updated';

		private $_eligibleCount = 0;

		private $_updatedCount = 0;

		public function __construct($eligibleCount, $updatedCount) {
			$this->_eligibleCount = $eligibleCount;
			$this->_updatedCount = $updatedCount;
		}

		public static function successful($eligibleCount, $updatedCount) {
			return new self($eligibleCount, $updatedCount);
		}

		public function offsetExists(mixed $offset): bool { 
			return $offset === self::KEY_ELIGIBLE_COUNT 
				|| $offset === self::KEY_UPDATED_COUNT;
		}

		public function offsetGet(mixed $offset): mixed { 
			if ($offset === self::KEY_ELIGIBLE_COUNT) {
				return $this->getEligibleCount();
			} else if ($offset === self::KEY_UPDATED_COUNT) {
				return $this->getUpdatedCount();
			} else {
				return null;
			}
		}

		public function offsetSet(mixed $offset, mixed $value): void { 
			$this->_throwReadOnly();
		}

		private function _throwReadOnly() {
			throw new Exception('Bulk stock update result is read only!');
		}

		public function offsetUnset(mixed $offset): void { 
			$this->_throwReadOnly();
		}

		public function getEligibleCount() {
			return $this->_eligibleCount;
		}

		public function getUpdatedCount() {
			return $this->_updatedCount;
		}
	}
}
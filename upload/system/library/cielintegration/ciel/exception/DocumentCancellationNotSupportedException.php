<?php
namespace Ciel\Api\Exception {
	class DocumentCancellationNotSupportedException extends CielException {
		private $_orderId;
		
		public function __construct($orderId) {
			parent::__construct();
			$this->_orderId = $orderId;
		}

		public function getOrderId() {
			return $this->_orderId;
		}
	}
}
<?php 
namespace Ciel\Api\Exception {
    class DocumentAlreadyIssuedForOrderException extends CielException {
        private $_orderId;
        
        public function __construct($orderId) {
            parent::__construct();
        }

        public function getOrderId() {
            return $this->_orderId;
        }
    }
}
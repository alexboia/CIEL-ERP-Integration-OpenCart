<?php 
namespace Ciel\Api\Exception {
    class VatQuotaValueNotSupportedException extends CielException {
        private $_vatQuotaValue;

        public function __construct($vatQuotaValue) {
            parent::__construct();
            $this->_vatQuotaValue = $vatQuotaValue;    
        }

        public function getVatQuotaValue() {
            return $this->_vatQuotaValue;
        }
    }
}
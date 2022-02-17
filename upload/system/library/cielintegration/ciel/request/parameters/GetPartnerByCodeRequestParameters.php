<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class GetPartnerByCodeRequestParameters extends CielRequestParameters {
        private $_code = null;

        private $_includeAddresses = null;

        private $_includeOnlyDefaultAddress = null;

        public function setCode($val) {
            $this->_code = $val;
            return $this;
        }

        public function setIncludeAddresses($val) {
            $this->_includeAddresses = $val;
            return $this;
        }

        public function setIncludeOnlyDefaultAddress($val) {
            $this->_includeOnlyDefaultAddress = $val;
            return $this;
        }

        public function getParams() {
            $params = array();
            $params['code'] = $this->_code;
            if ($this->_includeAddresses !== null) {
                $params['includeAddresses'] = $this->_includeAddresses;
            }
            if ($this->_includeOnlyDefaultAddress !== null) {
                $params['includeOnlyDefaultAddress'] = $this->_includeOnlyDefaultAddress;
            }
            return $params;   
        }
    }
}
<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class GetPartnerByIdRequestParameters extends CielRequestParameters {
        private $_id = null;

        private $_includeAddresses = null;

        private $_includeOnlyDefaultAddress = null;

        public function setId($val) {
            $this->_id = $val;
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
            $params['id'] = $this->_id;
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
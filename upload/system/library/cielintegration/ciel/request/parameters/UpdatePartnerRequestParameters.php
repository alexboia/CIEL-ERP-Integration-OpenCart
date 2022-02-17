<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class UpdatePartnerRequestParameters extends CielRequestParameters {
        private $_partnerCode = null;

        private $_propertiesToUpdate = null;

        private $_addressesToUpdate = null;

        public function setPartnerCode($val) {
            $this->_partnerCode = $val;
            return $this;
        }

        public function setPropertiesToUpdate($val) {
            $this->_propertiesToUpdate = $val;
            return $this;
        }

        public function setAddressesToUpdate($val) {
            $this->_addressesToUpdate = $val;
            return $this;
        }

        public function getParams() {
            return array(
                'partnerCode' => $this->_partnerCode,
                'properties' => $this->_propertiesToUpdate,
                'addressesToUpdate' => $this->_addressesToUpdate
            );
        }
    }
}
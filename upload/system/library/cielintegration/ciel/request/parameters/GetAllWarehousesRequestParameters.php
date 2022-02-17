<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class GetAllWarehousesRequestParameters extends CielRequestParameters {
        private $_includeOnlyWarehousesForMobileDevice = null;

        public function setIncludeOnlyWarehousesForMobileDevice($val) {
            $this->_includeOnlyWarehousesForMobileDevice = $val;
            return $this;
        }

        public function getParams() {
            $params = array();
            if ($this->_includeOnlyWarehousesForMobileDevice !== null) {
                $params['includeOnlyWarehousesForMobileDevice'] = $this->_includeOnlyWarehousesForMobileDevice;
            }
            return $params;   
        }
    }
}
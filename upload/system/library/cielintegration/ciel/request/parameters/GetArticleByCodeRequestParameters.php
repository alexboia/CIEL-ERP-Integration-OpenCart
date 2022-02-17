<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class GetArticleByCodeRequestParameters extends CielRequestParameters {
        private $_code = null;

        private $_includeArticleWarehousePrice = null;

        private $_includeArticleWarehouseStock = null;

        private $_stockDate = null;

        public function setCode($val) {
            $this->_code = $val;
            return $this;
        }

        public function setIncludeArticleWarehousePrice($val) {
            $this->_includeArticleWarehousePrice = $val;
            return $this;
        }

        public function setIncludeArticleWarehouseStock($val) {
            $this->_includeArticleWarehouseStock = $val;
            return $this;
        }

        public function setStockDate($val) {
            $this->_stockDate = $val;
            return $this;
        }

        public function getParams() {
            $params = array();

            $params['code'] = $this->_code;
            if ($this->_includeArticleWarehousePrice !== null) {
                $params['includeArticleWarehousePrice'] = $this->_includeArticleWarehousePrice;
            }
            if ($this->_includeArticleWarehouseStock !== null) {
                $params['includeArticleWarehouseStock'] = $this->_includeArticleWarehouseStock;
            }
            if ($this->_stockDate !== null) {
                $params['stockDate'] = $this->_stockDate;
            }

            return $params;   
        }
    }
}
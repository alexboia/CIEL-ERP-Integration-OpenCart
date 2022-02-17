<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class GetStocksForArticleByCodeRequestParameters extends CielRequestParameters {
        private $_stockDate = null;

        private $_code = null;

        public function setStockDate($val) {
            $this->_stockDate = $val;
            return $this;
        }

        public function setCode($val) {
            $this->_code = $val;
            return $this;
        }

        public function getParams() {
            $params = array();

            $params['code'] = $this->_code;
            if ($this->_stockDate !== null) {
                $params['stockDate'] = $this->_stockDate;
            }

            return $params;
        }
    }
}
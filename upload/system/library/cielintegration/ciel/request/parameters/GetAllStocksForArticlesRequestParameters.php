<?php 
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class GetAllStocksForArticlesRequestParameters extends CielRequestParameters {
        private $_stockDate = null;

        private $_articleType = null;

        private $_includeOnlyArticlesForMobileDevice = null;

        private $_database = null;

        private $_excludeArticlesWithBatch = null;

        public function setStockDate($val) {
            $this->_stockDate = $val;
            return $this;
        }

        public function setArticleType($val) {
            $this->_articleType = $val;
            return $this;
        }

        public function setIncludeOnlyArticlesForMobileDevice($val) {
            $this->_includeOnlyArticlesForMobileDevice = $val;
            return $this;
        }

        public function setDatabase($val) {
            $this->_database = $val;
            return $this;
        }

        public function setExcludeArticlesWithBatch($val) {
            $this->_excludeArticlesWithBatch = $val;
            return $this;
        }

        public function getParams() {
            $params = array();
            if ($this->_stockDate !== null) {
                $params['stockDate'] = $this->_stockDate;
            }
            if ($this->_articleType !== null) {
                $params['articleType'] = $this->_articleType;
            }
            if ($this->_includeOnlyArticlesForMobileDevice !== null) {
                $params['includeOnlyArticlesForMobileDevice'] = $this->_includeOnlyArticlesForMobileDevice;
            }
            if ($this->_database !== null) {
                $params['database'] = $this->_database;
            }
            if ($this->_excludeArticlesWithBatch !== null) {
                $params['excludeArticlesWithBatch'] = $this->_excludeArticlesWithBatch;
            }

            return $params;
        }
    }
}
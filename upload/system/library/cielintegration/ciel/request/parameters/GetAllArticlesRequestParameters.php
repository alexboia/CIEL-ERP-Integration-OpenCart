<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class GetAllArticlesRequestParameters extends CielRequestParameters {
        private $_includeArticleWarehousePrice = null;

        private $_includeArticleWarehouseStock = null;

        private $_stockDate = null;

        private $_articleType = null;

        private $_includeOnlyArticlesForMobileDevice = null;

        private $_excludeArticlesWithBatch = null;

        private $_articleCategoryId = null;

        private $_articleName = null;

        private $_pageNumber = null;

        private $_itemsPerPage = null;

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

        public function setArticleType($val) {
            $this->_articleType = $val;
            return $this;
        }

        public function setIncludeOnlyArticlesForMobileDevice($val) {
            $this->_includeOnlyArticlesForMobileDevice = $val;
            return $this;
        }

        public function setExcludeArticlesWithBatch($val) {
            $this->_excludeArticlesWithBatch = $val;
            return $this;
        }

        public function setArticleCategoryId($val) {
            $this->_articleCategoryId = $val;
            return $this;
        }

        public function setArticleName($val) {
            $this->_articleName = $val;
            return $this;
        }

        public function setPagination($pageNumber, $itemsPerPage = 1) {
            $this->_pageNumber = $pageNumber;
            $this->_itemsPerPage = $itemsPerPage;
            return $this;
        }

        public function getParams() {
            $params = array();
            
            if ($this->_includeArticleWarehousePrice !== null) {
                $params['includeArticleWarehousePrice'] = $this->_includeArticleWarehousePrice;
            }
            if ($this->_includeArticleWarehouseStock !== null) {
                $params['includeArticleWarehouseStock'] = $this->_includeArticleWarehouseStock;
            }
            if ($this->_stockDate !== null) {
                $params['stockDate'] = $this->_stockDate;
            }
            if ($this->_articleType !== null) {
                $params['articleType'] = $this->_articleType;
            }
            if ($this->_includeOnlyArticlesForMobileDevice !== null) {
                $params['includeOnlyArticlesForMobileDevice'] = $this->_includeOnlyArticlesForMobileDevice;
            }
            if ($this->_excludeArticlesWithBatch !== null) {
                $params['excludeArticlesWithBatch'] = $this->_excludeArticlesWithBatch;
            }
            if ($this->_articleCategoryId !== null) {
                $params['articleCategoryId'] = $this->_articleCategoryId;
            }
            if ($this->_articleName !== null) {
                $params['articleName'] = $this->_articleName;
            }

            if ($this->_pageNumber !== null) {
                $params['pageNumber'] = $this->_pageNumber;
                $params['itemsPerPage'] = $this->_itemsPerPage !== null 
                    ? $this->_itemsPerPage 
                    : 1;
            }

            return $params;   
        }
    }
}
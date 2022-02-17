<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class AddAssociationRequestParameters extends CielRequestParameters {
        private $_documentLookupTypeId;

        private $_seriesConfigurationId;

        private $_automaticGeneration = null;

        private $_worksiteId = null;

        private $_warehouseId = null;

        private $_isDefault = null;

        private $_agentId = null;

        private $_usedInContract = null;

        public function setDocumentLookupTypeId($documentLookupTypeId) {
            $this->_documentLookupTypeId = $documentLookupTypeId;
            return $this;
        }

        public function setSeriesConfigurationId($seriesConfigurationId) {
            $this->_seriesConfigurationId = $seriesConfigurationId;
            return $this;
        }

        public function setWorksiteId($worksiteId) {
            $this->_worksiteId = $worksiteId;
            return $this;
        }

        public function setWarehouseId($warehouseId) {
            $this->_warehouseId = $warehouseId;
            return $this;
        }

        public function setAutomaticGeneration($automaticGeneration) {
            $this->_automaticGeneration = $automaticGeneration;
            return $this;
        }

        public function setIsDefault($isDefault) {
            $this->_isDefault = $isDefault;
            return $this;
        }

        public function setAgentId($agentId) {
            $this->_agentId = $agentId;
            return $this;
        }

        public function setUsedInContract($usedInContract) {
            $this->_usedInContract = $usedInContract;
            return $this;
        }

        public function getParams() {
            $params = array();
            $params['DocumentLookupTypeId'] = $this->_documentLookupTypeId;
            $params['SeriesConfigurationId'] = $this->_seriesConfigurationId;

            if ($this->_automaticGeneration !== null) {
                $params['AutomaticGeneration'] = $this->_automaticGeneration;
            }

            if ($this->_worksiteId !== null) {
                $params['WorksiteId'] = $this->_worksiteId;
            }

            if ($this->_warehouseId !== null) {
                $params['WarehouseId'] = $this->_warehouseId;
            }

            if ($this->_agentId !== null) {
                $params['AgentId'] = $this->_agentId;
            }

            if ($this->_isDefault !== null) {
                $params['IsDefault'] = $this->_isDefault;
            }

            if ($this->_usedInContract !== null) {
                $params['UsedInContract'] = $this->_usedInContract;
            }

            return $params;
        }
    }
}
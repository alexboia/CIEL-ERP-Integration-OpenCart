<?php
namespace Ciel\Api\Exception {
    class RemoteArticleNotFoundException extends CielException {
        private $_identifierType;

        private $_identifierValue;

        public function __construct($identifierType, $identifierValue) {
            parent::__construct();

            $this->_identifierType = $identifierType;
            $this->_identifierValue = $identifierValue;
        }

        public function getIdentifierType() {
            return $this->_identifierType;
        }

        public function getIdentifierValue() {
            return $this->_identifierValue;
        }
    }
}
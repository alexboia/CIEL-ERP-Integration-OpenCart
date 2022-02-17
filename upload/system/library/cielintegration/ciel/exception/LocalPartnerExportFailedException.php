<?php
namespace Ciel\Api\Exception {
    class LocalPartnerExportFailedException extends CielException {
        private $_exportActionType;

        public function __construct($exportActionType) {
            $this->_exportActionType = $exportActionType;
        }

        public function getExportActionType() {
            return $this->_exportActionType;
        }
    }
}
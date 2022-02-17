<?php
namespace Ciel\Api\Exception {
    class IssueDocumentNotEnabledException extends CielException {
        public function __construct() {
            parent::__construct();
        }
    }
}
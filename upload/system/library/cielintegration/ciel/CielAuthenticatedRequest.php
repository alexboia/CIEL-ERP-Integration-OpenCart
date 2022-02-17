<?php 
namespace Ciel\Api {
    class CielAuthenticatedRequest extends CielRequest {
        private $_authenticationToken;

        public function __construct($method, $authenticationToken, array $parameters = array()) {
            parent::__construct($method, $parameters);
            $this->_authenticationToken = $authenticationToken;
        }

        public function isAuthenticationRequired() {
            return true;
        }

        public function getAuthenticationToken() {
            return $this->_authenticationToken;
        }
    }
}
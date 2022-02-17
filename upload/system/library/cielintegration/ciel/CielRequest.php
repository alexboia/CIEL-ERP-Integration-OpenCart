<?php 
namespace Ciel\Api {
    class CielRequest {
        private $_method;

        private $_parameters;

        public function __construct($method, array $parameters = array()) {
            $this->_method = $method;
            $this->_parameters = $parameters;
        }

        public function getMethod() {
            return $this->_method;   
        }

        public function getParameters() {
            return $this->_parameters;
        }

        public function setParameter($name, $value) {
            $this->_parameters[$name] = $value;
        }

        public function getParameter($name) {
            return isset($this->_parameters[$name]) 
                ? $this->_parameters[$name] 
                : null;
        }

        public function isAuthenticationRequired() {
            return false;
        }

        public function getAuthenticationToken() {
            return null;
        }
    }
}
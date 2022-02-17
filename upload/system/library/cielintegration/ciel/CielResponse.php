<?php
namespace Ciel\Api {
    class CielResponse {
        /**
         * The result data
         * @var array|string|bool
         */
        private $_result;

        /**
         * The error, if any
         * @var \Ciel\Api\CielError
         */
        private $_error;

        public function __construct($result, $error = null) {
            $this->_result = $result;
            $this->_error = $error != null 
                ? new CielError($error) 
                : null;
        }

        /**
         * Retrieve the result data
         * @return array|string|bool
         */
        public function getResult() {
            return $this->_result;
        }

        /**
         * Retrieves the error
         * @return CielError 
         */
        public function getError() {
            return $this->_error;
        }

        public function hasError() {
            return !empty($this->_error);
        }

        public function hasResult() {
            return !empty($this->_result);
        }
    }
}
<?php
namespace Ciel\Api {
    use InvalidArgumentException;

    class CielError {
        private $_errorData;

        public function __construct($errorData) {
            if (!is_null($errorData) && !is_array($errorData)) {
                throw new InvalidArgumentException('Error data must either be null or an array');
            }

            $this->_errorData = $errorData;
        }

        public function __get($name) {
            if (empty($name)) {
                throw new InvalidArgumentException('Name must not be null');
            }

            $key = ucfirst($name);
            return isset($this->_errorData[$key]) 
                ? $this->_errorData[$key] 
                : null;
        }

        public function getData() {
            return $this->_errorData;
        }
    }
}
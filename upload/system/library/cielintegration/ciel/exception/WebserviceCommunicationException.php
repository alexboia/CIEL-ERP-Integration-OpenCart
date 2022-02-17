<?php 
namespace Ciel\Api\Exception {
    class WebserviceCommunicationException extends \Exception {
        private $_statusCode;

        public function __construct($statusCode, $message = '') {
            parent::__construct($message);
            $this->_statusCode = $statusCode;
        }

        public function getStatusCode() {
            return $this->_statusCode;
        }
    }
}
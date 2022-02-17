<?php 
namespace Ciel\Api\Exception {
    use Ciel\Api\CielError;

    class WebserviceErrorException extends \Exception {
        /**
         * @var \Ciel\Api\CielError
         */
        private $_error;

        public function __construct($error, $message = '') {
            parent::__construct($message);
            $this->_error = $error;
        }

        public function getError() {
            return $this->_error;
        }
    }
}
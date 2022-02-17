<?php 
namespace Ciel\Api\Exception {
    class WebserviceResponseFormatException extends \Exception {
        public function __construct($message = '', $code = 0) {
            parent::__construct($message, $code);
        }
    }
}
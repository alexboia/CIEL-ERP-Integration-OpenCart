<?php 
namespace Ciel\Api\Exception {
    class MissingWebserviceResponseBodyException extends \Exception {
        public function __construct($message = '') {
            parent::__construct($message);
        }
    }
}
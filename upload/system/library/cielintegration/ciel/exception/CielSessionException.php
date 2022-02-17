<?php
namespace Ciel\Api\Exception {
	use Exception;

	class CielSessionException extends CielException {
		public function __construct($message = '', $code = 0) {
            parent::__construct($message, $code);
        }
	}
}
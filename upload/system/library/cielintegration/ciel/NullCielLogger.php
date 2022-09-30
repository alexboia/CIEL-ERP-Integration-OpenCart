<?php
namespace Ciel\Api {
    use Exception;

	class NullCielLogger implements CielLogger {
		public function logDebug($message, $context = array()) { 
			return;
		}

		public function logException($message, Exception $exc, $context = array()) { 
			return;
		}
	}
}
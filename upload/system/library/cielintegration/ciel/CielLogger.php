<?php
namespace Ciel\Api {
	interface CielLogger {
		function logDebug($message, $context = array());

		function logException($message, \Exception $exc, $context = array());
	}
}
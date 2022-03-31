<?php
namespace CielIntegration {

    use Ciel\Api\Exception\WebserviceErrorException;
    use Exception;
    use Log;

	trait WithLogging {
		private static $_CIEL_DEBUG_FILE_NAME = 'ciel-oc-debug.log';

		private static $_CIEL_ERROR_FILE_NAME = 'ciel-oc-error.log';

		/**
		 * @var \Log
		 */
		private $_debugLogger = null;

		/**
		 * @var \Log
		 */
		private $_errorLogger = null;

		protected function _getDebugLogger() {
			if ($this->_debugLogger === null) {
				$this->_debugLogger = new Log(self::$_CIEL_DEBUG_FILE_NAME);
			}
			return $this->_debugLogger;
		}

		protected function _logDebug($message) {
			$debugLogger = $this->_getDebugLogger();
			$debugLogger->write(sprintf('[DEBUG] %s', 
				$message));
		}

		protected function _getErrorLogger() {
			if ($this->_errorLogger === null) {
				$this->_errorLogger = new Log(self::$_CIEL_ERROR_FILE_NAME);
			}
			return $this->_errorLogger;
		}

		protected function _logError(Exception $exc, $message = null) {
			$logMessageParts = array();
			if (!empty($message)) {
				$logMessageParts[] = $message;
			}

			$logMessageParts[] = get_class($exc);
			$logMessageParts[] = $exc->getMessage();
			if ($exc instanceof WebserviceErrorException) {
				$error = $exc->getError();
				$logMessageParts[] = print_r($error->getData(), true);
			}

			$logMessageParts[] = $exc->getTraceAsString();
			$logMessage = join(' - ', $logMessageParts);

			$errorLogger = $this->_getErrorLogger();
			$errorLogger->write(sprintf('[ERROR] %s', 
				$logMessage));
		}

		protected function _getDebugLogFileManager() {
			return new LogFileManager(self::$_CIEL_DEBUG_FILE_NAME);
		}

		protected function _getErrorLogFileManager() {
			return new LogFileManager(self::$_CIEL_ERROR_FILE_NAME);
		}
	}
}
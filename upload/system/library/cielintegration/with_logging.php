<?php
namespace CielIntegration {

    use Exception;
    use Log;

	trait WithLogging {
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
				$this->_debugLogger = new Log('ciel-oc-debug.log');
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
				$this->_errorLogger = new Log('ciel-oc-error.log');
			}
			return $this->_errorLogger;
		}

		protected function _logError(Exception $exc, $message = null) {
			$logMessageParts = array();
			if (!empty($message)) {
				$logMessageParts[] = $message;
			}

			$logMessageParts[] = $exc->getMessage();
			$logMessageParts[] = $exc->getTraceAsString();

			$logMessage = join(' - ', $logMessageParts);

			$errorLogger = $this->_getErrorLogger();
			$errorLogger->write(sprintf('[ERROR] %s', 
				$logMessage));
		}
	}
}
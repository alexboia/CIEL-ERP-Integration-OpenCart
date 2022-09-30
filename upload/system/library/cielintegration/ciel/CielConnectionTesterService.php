<?php
namespace Ciel\Api {
    use Exception;

	class CielConnectionTesterService {
		/**
		 * @var CielClientFactory
		 */
		private $_cielClientFactory;

		/**
		 * @var CielLogger
		 */
		private $_logger;

		public function __construct(CielClientFactory $cielClientFactory, CielLogger $logger) {
			$this->_cielClientFactory = $cielClientFactory;
			$this->_logger = $logger;
		}

		public function testConnection($serverUrl, 
				$userName, 
				$password, 
				$societyCode, 
				$timeoutSeconds) {

			if (empty($serverUrl) 
				|| empty($userName) 
				|| empty($societyCode)) {
				return CielConnectionTestResult::ERR_CONNECTION_TEST_NOT_BOUND;
			}

			$result = CielConnectionTestResult::ERR_CONNECTION_TEST_OK;

			try {
				$result = $this->_processTestCielWebServiceConnection($serverUrl, 
					$userName, 
					$password, 
					$societyCode, 
					$timeoutSeconds);
			} catch (Exception $exc) {
				$this->_logger->logException('Error testing CIEL ERP connection.', $exc);
				$result = CielConnectionTestResult::ERR_CONNECTION_TEST_GENERIC;
			}

			return $result;
		}

		private function _processTestCielWebServiceConnection($serverUrl, 
			$userName, 
			$password, 
			$societyCode, 
			$timeoutSeconds) {

			$connectionInfo = new CielClientConnectionInfo($serverUrl, 
				$userName, 
				$password, 
				$societyCode, 
				$timeoutSeconds);
			
			$client = $this->_cielClientFactory
				->createCielClientForConnectionInfo($connectionInfo);

			if ($client->isAuthenticated()) {
				$client->logout();
				$result = CielConnectionTestResult::ERR_CONNECTION_TEST_OK;
			} else {
				$result = CielConnectionTestResult::ERR_CONNECTION_TEST_FAIL_AUTH;
			}

			return $result;
		}
	}
}
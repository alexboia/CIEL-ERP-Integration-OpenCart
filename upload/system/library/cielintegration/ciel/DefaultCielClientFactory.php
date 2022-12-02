<?php
namespace Ciel\Api {

    use Ciel\Api\Session\CielClientSessionProvider;
    use InvalidArgumentException;

	class DefaultCielClientFactory implements CielClientFactory {
		/**
		 * @var CielClientSessionProvider
		 */
		private $_sessionProvider;

		/**
		 * @var CielClientAmbientConnectionInfoProvider
		 */
		private $_ambientConnectionInfoProvider;

		/**
		 * @var CielClient
		 */
		private $_ambientCielClient;

		private $_sessionProviderSetup = false;

		public function __construct(CielClientSessionProvider $sessionProvider, 
				CielClientAmbientConnectionInfoProvider $ambientConnectionOptionsProvider) {
			$this->_sessionProvider = $sessionProvider;
			$this->_ambientConnectionInfoProvider = $ambientConnectionOptionsProvider;	
		}

		public function createCielClientForEndpointAndOptions($endpoint, array $options) {
			if (empty($endpoint)) {
				throw new InvalidArgumentException('Endpoint may not be empty');
			}

			$this->_setupSessionProvider();

			$client = new CielClient($endpoint, $options);
			$client->setSessionProvider($this->_sessionProvider);

			return $client;
		}

		private function _setupSessionProvider() {
			if (!$this->_sessionProviderSetup) {
				$this->_sessionProvider->setup();
				$this->_sessionProviderSetup = true;
			}
		}

		public function createCielClientForConnectionInfo(CielClientConnectionInfo $info) { 
			$client = $this->createCielClientForEndpointAndOptions($info->getServerUrl(), 
				$this->_createCielClientOptions($info));

			if ($info->hasUserName() && $info->hasSocietyCode()) {
				$client->logon($info->getUserName(), 
					$info->getPassword(), 
					$info->getSocietyCode());
			}

			return $client;
		}

		private function _createCielClientOptions(CielClientConnectionInfo $info) {
			return 	array(
				'timeoutSeconds' => $info->getTimeoutSeconds()
			);
		}

		public function getAmbientCielClient() { 
			if ($this->_ambientCielClient === null) {
				$this->_ambientCielClient = $this->createCielClientForConnectionInfo($this->_getAmbientConnectionInfo());
			}
			return $this->_ambientCielClient;
		}

		private function _getAmbientConnectionInfo() {
			return $this->_ambientConnectionInfoProvider
				->getAmbientConnectionInfo();
		}
	}
}
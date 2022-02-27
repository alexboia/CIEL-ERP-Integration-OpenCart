<?php
namespace CielIntegration\Integration\Admin\Binding {

    use Ciel\Api\Integration\Binding\Providers\CielErpToStoreBindingAdapter;
    use Ciel\Api\Session\DbTableSessionCielClientSessionProvider;
    use CielIntegration\Integration\Admin\Binding\Model\IntegrationSettings;
    use CielIntegration\Integration\Admin\OpenCartCielConfig;
    use CielIntegration\Integration\Admin\OpenCartFileBasedCielCache;

	class OpenCartCielErpToStoreBindingAdapter implements CielErpToStoreBindingAdapter {
		/**
		 * @var OpenCartCielConfig
		 */
		private $_config;

		/**
		 * @var OpenCartFileBasedCielCache
		 */
		private $_cache;

		/**
		 * @var DbTableSessionCielClientSessionProvider
		 */
		private $_clientSessionProvider;

		/**
		 * @var IntegrationSettings
		 */
		private $_bindingSettingsModel;

		public function __construct(\Registry $registry) {
			$this->_config = new OpenCartCielConfig();
			$this->_cache = new OpenCartFileBasedCielCache($this->_config->getGenericCacheDuration());
			$this->_bindingSettingsModel = new IntegrationSettings($registry);

			$this->_clientSessionProvider = new DbTableSessionCielClientSessionProvider(
				$this->_getDbTableSessionProviderConnectionOptions(),
				$this->_getDbTableSessionProviderTokenOptions(),
				$this->_getDbTableSessionProviderTableOptions()
			);
		}

		private function _getDbTableSessionProviderConnectionOptions() {
			return array(
				'host' => DB_HOSTNAME,
				'port' => DB_PORT,
				'dbName' => DB_DATABASE,
				'dbUserName' => DB_USERNAME,
				'dbPassword' => DB_PASSWORD,
				'charset' => 'utf8'
			);
		}

		private function _getDbTableSessionProviderTokenOptions() {
			return array();
		}

		private function _getDbTableSessionProviderTableOptions() {
			return array(
				'prefix' => DB_PREFIX
			);
		}

		public function saveBindingData(array $data) { 
			$this->_bindingSettingsModel
				->saveBindingSettings($data);
		}

		public function getBindingData() { 
			return $this->_bindingSettingsModel
				->getBindingSettings();
		}

		public function clearBindingData() { 
			$this->_bindingSettingsModel
				->clearSettings();
		}

		public function getClientSessionProvider() { 
			return $this->_clientSessionProvider;
		}

		public function getCache() { 
			return $this->_cache;
		}

		public function getConfig() { 
			return $this->_config;
		}
	}
}
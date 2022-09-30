<?php
namespace Ciel\Api\Integration\Binding\RemoteDataProviders {

    use Ciel\Api\CielCache;
    use Ciel\Api\CielClientFactory;
    use Ciel\Api\CielConfig;
    use Ciel\Api\CielLogger;
    use Ciel\Api\Data\WarehouseType;
    use Ciel\Api\Request\Parameters\GetAllWarehousesRequestParameters;

	class WarehouseDataProvider {
		const WH_CACHE_KEY = 'ciel_warehouses';

		/**
		 * @var CielClientFactory
		 */
		private $_cielClientFactory;

		/**
		 * @var CielConfig
		 */
		private $_config;

		/**
		 * @var CielCache
		 */
		private $_cache;

		/**
		 * @var CielLogger
		 */
		private $_logger;

		public function __construct(CielClientFactory $cielClientFactory, 
				CielConfig $config,
				CielCache $cache, 
				CielLogger $logger) {
			$this->_cielClientFactory = $cielClientFactory;
			$this->_config = $config;
			$this->_cache = $cache;
			$this->_logger = $logger;
		}

		public function getAvailableWarehouses() {
			$warehouses = $this->_getCachedWarehouses();
			if ($warehouses === false || $warehouses === null) {
				$this->_logger->logDebug('Warehouses not found in cache. Will retrieve from server...');

				$client = $this->_getAmbientCielClient();
				$warehousesRaw = $client->getAllWarehouses(new GetAllWarehousesRequestParameters());

				$warehouses = array();
				if (!empty($warehousesRaw)) {
					foreach ($warehousesRaw as $w) {
						$wDropdownInfo = $this->_createWarehouseDropdownInfo($w);
						if ($wDropdownInfo != null) {
							$warehouses[$w['Id']] = $wDropdownInfo;
						}
					}
				}

				$this->_logger->logDebug('Found and processed <' . count($warehouses) . '> warehouses.');
				if (!empty($warehouses)) {
					$this->_setCachedWarehouses($warehouses);
				}
			} else {
				$this->_logger->logDebug('Warehouses found in cache. Will return cached result.');
			}

			return $warehouses;
		}

		private function _getCachedWarehouses() {
			return $this->_cache
				->get(self::WH_CACHE_KEY);
		}

		private function _setCachedWarehouses($warehouses) {
			$cacheDuration = $this->_config
				->getWarehousesCacheDuration();

			$this->_cache
				->set(self::WH_CACHE_KEY, 
					$warehouses, 
					$cacheDuration);
		}

		private function _getAmbientCielClient() {
			return $this->_cielClientFactory
				->getAmbientCielClient();
		}

		private function _createWarehouseDropdownInfo($rawWarehouse) {
			$typeId = $rawWarehouse['WarehouseTypeName'];
			$type = WarehouseType::parse($typeId);

			if ($type != null) {
				return array(
					'code' => $rawWarehouse['Code'],
					'name' => $rawWarehouse['Name'],
					'type' => $typeId,
					'displayLabel' => $this->_formatWarehouseName( 
						$rawWarehouse['Name'], 
						$rawWarehouse['Code'], 
						$typeId),
					'properties' => $type->asPlainObject()
				);
			} else {
				return null;
			}
		}

		private function _formatWarehouseName($name, $code, $typeId) {
			$format = $this->_getWarehouseDisplayLabelFormat();
			return str_replace(array('%name', '%code', '%type_id'), 
				array($name, $code, $typeId), 
				$format);
		}

		private function _getWarehouseDisplayLabelFormat() {
			return $this->_config
				->getWarehouseDisplayLabelFormat();
		}
	}
}
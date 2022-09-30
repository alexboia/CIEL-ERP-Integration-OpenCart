<?php
namespace Ciel\Api\Integration\Binding\RemoteDataProviders {

    use Ciel\Api\CielCache;
    use Ciel\Api\CielClientFactory;
    use Ciel\Api\CielConfig;
    use Ciel\Api\CielLogger;
    use Ciel\Api\Request\Parameters\SelectFromViewRequestParameters;

	class VatQuotaDataProvider {
		const VQ_CACHE_KEY = 'ciel_vat_quotas';

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

		public function getAvailableVatQuotas() {
			$quotas = $this->_getCachedVatQuotas();
			if ($quotas === false || $quotas === null) {
				$this->_logger->logDebug('VAT quotas not found in cache. Will retrieve from server...');

				$client = $this->_getAmbientCielClient();
				$quotasRaw = $client->selectFromView(
					(new SelectFromViewRequestParameters())
						->setViewName('CIEL_PS_Quotas')
				);

				$quotas = array();
				if (!empty($quotasRaw) && is_array($quotasRaw)) {
					foreach ($quotasRaw as $q) {
						$quotas[$q['name']] = $q['value'];
					}
				}

				$this->_logger->logDebug('Found and processed <' . count($quotas) . '> VAT quotas.');
				if (!empty($quotas)) {
					$this->_setCachedVatQuotas($quotas);
				}
			} else {
				$this->_logger->logDebug('VAT quotas found in cache. Will return cached result.');
			}

			return $quotas;
		}

		private function _getCachedVatQuotas() {
			return $this->_cache
				->get(self::VQ_CACHE_KEY);
		}

		private function _setCachedVatQuotas($vatQuotas) {
			$cacheDuration = $this->_config
				->getVatQuotasCacheDuration();

			$this->_cache
				->set(self::VQ_CACHE_KEY, 
					$vatQuotas, 
					$cacheDuration);
		}

		private function _getAmbientCielClient() {
			return $this->_cielClientFactory
				->getAmbientCielClient();
		}
	}
}
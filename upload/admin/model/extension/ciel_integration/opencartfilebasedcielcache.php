<?php
namespace CielIntegration\Integration\Admin {

    use Cache;
    use Ciel\Api\CielCache;

	class OpenCartFileBasedCielCache implements CielCache {
		const KEY_PREFIX = 'lvdcieloc_';

		const CACHE_DURATION_ONEDAY_IN_SECONDS = 24*3600;

		/**
		 * @var Cache
		 */
		private $_cache;

		public function __construct($cacheDureation = self::CACHE_DURATION_ONEDAY_IN_SECONDS) {
			$this->_cache = new \Cache('file', $cacheDureation);
		}

		public function set($key, $value, $secondsUntilExpiration) { 
			$this->_cache->set($this->_getCacheKey($key), $value);
		}

		private function _getCacheKey($key) {
			return self::KEY_PREFIX . $key;
		}

		public function get($key) { 
			return $this->_cache->get($this->_getCacheKey($key));
		}

		public function remove($key) { 
			$this->_cache->delete($this->_getCacheKey($key));
		}

		public function clear() { 
			//TODO: implement manually
			return;
		}
	}
}
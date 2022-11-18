<?php
namespace CielIntegration {
	class Bootstrapper {
		/**
		 * @var Bootstrapper
		 */
		private static $_current = null;

		private static $_cielApiBootstrapped = false;

		private static $_internalLibrariesBootstrapped = false;

		private static $_polyfillBootstrapped = false;

		public static function bootstrap() {
			$me = self::_getCurrent();
			$me->_polyfill();
			$me->_bootstrapCielApi();
			$me->_bootstrapInternalLibraries();
		}

		private static function _getCurrent() {
			if (self::$_current === null) {
				self::$_current = new self();
			}
			return self::$_current;
		}

		private function _bootstrapCielApi() {
			if (!$this->_isCielApiBootstrapped()) {
				$this->_setupCielApiAutoloader();
				$this->_setCielApiBootstrapped();
			}
		}

		private function _setupCielApiAutoloader() {
			require_once __DIR__ . '/ciel/CielAutoloader.php';
			\Ciel\Api\CielAutoloader::enable();
		}

		private function _isCielApiBootstrapped() {
			return self::$_cielApiBootstrapped;
		}

		private function _setCielApiBootstrapped() {
			self::$_cielApiBootstrapped = true;
		}

		private function _bootstrapInternalLibraries() {
			if (!$this->_areInternalLibrariesBootstrapped()) {
				$this->_includeHelpers();
				$this->_setupInternalLibrariesAutoloader();
				$this->_setInternalLibrariesBootstrapped();
			}
		}

		private function _includeHelpers() {
			require_once __DIR__ . '/helper/string.php';
			require_once __DIR__ . '/helper/array.php';
			require_once __DIR__ . '/helper/html.php';
			require_once __DIR__ . '/helper/tidy.php';
		}

		private function _setupInternalLibrariesAutoloader() {
			Autoloader::enable();
		}

		private function _areInternalLibrariesBootstrapped() {
			return self::$_internalLibrariesBootstrapped;
		}

		private function _setInternalLibrariesBootstrapped() {
			self::$_internalLibrariesBootstrapped = true;
		}

		private function _polyfill() {
			if (!$this->_isPolyfillBootstrapped()) {
				require_once __DIR__ . '/polyfill.php';
				$this->_setPolyfillBootstapped();
			}
		}

		private function _isPolyfillBootstrapped() {
			return self::$_polyfillBootstrapped;
		}

		private function _setPolyfillBootstapped() {
			self::$_polyfillBootstrapped = true;
		}
	}
}
<?php
namespace CielIntegration {
	class Bootstrapper {
		/**
		 * @var Bootstrapper
		 */
		private static $_current = null;

		private static $_cielApiBootstrapped = false;

		private static $_vendorBootstrapped = false;

		public static function bootstrap() {
			$me = self::_getCurrent();
			$me->_polyfill();
			$me->_bootstrapVendor();
			$me->_bootstrapCielApi();
		}

		private static function _getCurrent() {
			if (self::$_current === null) {
				self::$_current = new self();
			}
			return self::$_current;
		}

		private function _bootstrapCielApi() {
			if (!$this->_isCielApiBootstrapped()) {
				require_once __DIR__ . '/ciel/CielAutoloader.php';
				\Ciel\Api\CielAutoloader::enable();
				$this->_setCielApiBootstrapped();
			}
		}

		private function _isCielApiBootstrapped() {
			return self::$_cielApiBootstrapped;
		}

		private function _setCielApiBootstrapped() {
			self::$_cielApiBootstrapped = true;
		}

		private function _bootstrapVendor() {
			VendorAutoloader::enable();
		}

		private function _polyfill() {
			require_once __DIR__ . '/polyfill.php';
		}
	}
}
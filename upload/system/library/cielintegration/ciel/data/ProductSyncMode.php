<?php
namespace Ciel\Api\Data {
	class ProductSyncMode {
		const StockOnly = 'stock-only';

		const AllInformation = 'all-information';

		public static function isSupported($modeId) {
			return in_array($modeId, self::getAvailableModes());
		}

		public static function getAvailableModes() {
			return array(
				self::StockOnly,
				self::AllInformation
			);
		}
	}
}
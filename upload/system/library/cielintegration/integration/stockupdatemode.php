<?php
namespace CielIntegration\Integration {
	class StockUpdateMode {
		const Manual = 'stock-update-manual';

		const SystemCron = 'stock-update-system-cron';

		const OpenCartCron = 'stock-update-oc-cron';

		public static function getSupportedTypeIds() {
			return array(
				self::Manual,
				self::SystemCron,
				self::OpenCartCron
			);
		}

		public static function isTypeIdSupported($typeId) {
			return in_array($typeId, self::getSupportedTypeIds());
		}
	}
}
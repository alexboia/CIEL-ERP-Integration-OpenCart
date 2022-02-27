<?php
namespace CielIntegration\Integration\Admin {
	class PriceFormatter extends IntegrationService {
		const DEFAULT_PRICE_PRECISION = 4;
		
		public function roundPrice($price) {
			return round($price, self::DEFAULT_PRICE_PRECISION);
		}
	}
}
<?php
namespace CielIntegration\Integration\Admin {
	class PriceFormatter extends IntegrationService {
		const DEFAULT_PRICE_PRECISION = 4;
		
		public function roundPrice($price) {
			return round($price, self::DEFAULT_PRICE_PRECISION);
		}

		public function calculatePriceWithoutVat($priceWithVat, $vatQuotaValue) {
			return (double)$priceWithVat / (1 + ($vatQuotaValue / 100));
		}

		public function calculatePriceWithVat($priceWithoutVat, $vatQuotaValue) {
			return (double)$priceWithoutVat * (1 + ($vatQuotaValue / 100));
		}
	}
}
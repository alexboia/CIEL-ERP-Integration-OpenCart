<?php
namespace CielIntegration\Integration\Admin {
    use Ciel\Api\CielConfig;

	class OpenCartCielConfig implements CielConfig {
		const CACHE_DURATION_ONEDAY_IN_SECONDS = 24*3600;

		public function getShippingArticleCode() { 
			return defined('LVDCIELOC_SHIPPING_ARTICLE_CODE') 
				? constant('LVDCIELOC_SHIPPING_ARTICLE_CODE') 
				: 'CES_SHIPPING';
		}

		public function getDiscountArticleCodeFormat() { 
			return defined('LVDCIELOC_DISOCUNT_ARTICLE_CODE_FORMAT') 
				? constant('LVDCIELOC_DISOCUNT_ARTICLE_CODE_FORMAT') 
				: 'CES_DISCOUNT_%quota_value';
		}

		public function getDiscountArticleNameFormat() { 
			return defined('LVDCIELOC_DISOCUNT_ARTICLE_NAME_FORMAT') 
                ? constant('LVDCIELOC_DISOCUNT_ARTICLE_NAME_FORMAT') 
                : 'Discount - TVA %quota_value%';
		}

		public function getGenericCacheDuration() {
			return defined('LVDCIELOC_CACHE_DURATION') 
				? constant('LVDCIELOC_CACHE_DURATION') 
				: self::CACHE_DURATION_ONEDAY_IN_SECONDS;
		}

		public function getWarehousesCacheDuration() { 
			return $this->getGenericCacheDuration();
		}

		public function getVatQuotasCacheDuration() { 
			return $this->getGenericCacheDuration();
		}

		public function getWarehouseDisplayLabelFormat() { 
			return defined('LVDCIELOC_WAREHOUSE_DISPLAY_LABEL_FORMAT') 
				? constant('LVDCIELOC_WAREHOUSE_DISPLAY_LABEL_FORMAT') 
				: '%name (%code, %type_id)';
		}

		public function isPriceUpdateEnabled() { 
			return defined('LVDCIELOC_PRICE_UPDATE_ENABLED') 
                ? constant('LVDCIELOC_PRICE_UPDATE_ENABLED') === true
                : true;
		}

		public function isTaxRatesUpdateEnabled() { 
			return defined('LVDCIELOC_TAX_RATES_UPDATE_ENABLED') 
				? constant('LVDCIELOC_TAX_RATES_UPDATE_ENABLED') === true
				: true;
		}

		public function isStockUpdateEnabled() { 
			return defined('LVDCIELOC_STOCK_UPDATE_ENABLED') 
				? constant('LVDCIELOC_STOCk_UPDATE_ENABLED') === true
				: true;
		}

		public function usePhoneForPartnerMatching() { 
			return defined('LVDCIELOC_USE_PARTNER_PHONE_MATCHING')
				? constant('LVDCIELOC_USE_PARTNER_PHONE_MATCHING') === true
				: false;
		}

		public function useNameForPartnerMatching() {
			return false;
		}
	}
}
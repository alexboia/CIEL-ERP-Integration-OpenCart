<?php
namespace Ciel\Api\Integration\Orders {
	use Ciel\Api\Integration\Binding\CielErpToStoreBinding;

	class CielClientRemoteDiscountArticleResolver implements RemoteDiscountArticleResolver {
		/**
		 * @var CielErpToStoreBinding
		 */
		private $_storeBinding;

		public function __construct(CielErpToStoreBinding $storeBinding) {	
			$this->_storeBinding = $storeBinding;
		}

		public function getDiscountArticleForVatQuotaValue($vatQuotaValue) { 
			return $this->_storeBinding->setupDiscountForVatQuotaValueIfNeeded($vatQuotaValue);
		}
	}
}
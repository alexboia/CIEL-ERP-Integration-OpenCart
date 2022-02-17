<?php
namespace Ciel\Api\Integration\Orders {

	use Ciel\Api\CielClient;
	use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
	use Ciel\Api\Request\Parameters\GetArticleByCodeRequestParameters;

	class CielClientRemoteShippingArticleResolver implements RemoteShippingArticleResolver {
		/**
		 * @var CielErpToStoreBinding
		 */
		private $_storeBinding;

		/**
		 * @var array|null
		 */
		private $_ShippingArticle = null;

		public function __construct(CielErpToStoreBinding $storeBinding) {
			$this->_storeBinding = $storeBinding;
		}

		public function getShippingArticle() { 
			if ($this->_ShippingArticle === null) {
				$cielClient = $this->_getCielClientAndLogon();
				$this->_ShippingArticle = $this->_getShippingArticle($cielClient);
			}
			return $this->_ShippingArticle;
		}

		/**
		 * @return \Ciel\Api\CielClient 
		 */
		private function _getCielClientAndLogon() {
			return $this->_storeBinding->getAmbientCielClient(true);
		}

		private function _getShippingArticle(CielClient $client) {
			$articleCode = $this->_getShippingArticleCode();
			$getByCodeParams = (new GetArticleByCodeRequestParameters())
				->setCode($articleCode)
				->setIncludeArticleWarehousePrice(false)
				->setIncludeArticleWarehouseStock(false);

			return $client->getArticleByCode($getByCodeParams);
		}

		private function _getShippingArticleCode() {
			return $this->_storeBinding->getShippingArticleCode();
		}
	}
}
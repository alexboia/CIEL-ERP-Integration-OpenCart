<?php
namespace Ciel\Api\Integration\Articles\Providers {

	use InvalidArgumentException;
    use Ciel\Api\CielClient;
    use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
    use Ciel\Api\Request\Parameters\SelectFromViewRequestParameters;

	class DefaultCielErpArticleBatchInformationProvider implements CielErpArticleBatchInformationProvider {

		/**
		 * @var \Ciel\Api\Integration\Binding\CielErpToStoreBinding
		 */
		private $_storeBinding;

		public function __construct(CielErpToStoreBinding $storeBinding) {
			$this->_storeBinding = $storeBinding;
		}

		public function getBatchesForArticle($code) { 
			if (empty($code)) {
				throw new InvalidArgumentException('Article codes may not be empty.');
			}

			$batches = $this->getBatchesForArticles(array($code));
			return $batches[$code];
		}

		public function getBatchesForArticles(array $codes) { 
			if (empty($codes)) {
				throw new InvalidArgumentException('Article codes may not be empty.');
			}

			$result = array();
			$client = $this->_getCielClientAndLogon();
			
			foreach ($codes as $code) {
				if (!empty($code)) {
					$batches = $this->_getBatchesForArticleCode($client, $code);
					$result[$code] = !empty($batches) 
						? $this->_convertBatchesToCommonFormat($batches) 
						: array();
				}
			}

			return $result;
		}

		private function _getBatchesForArticleCode(CielClient $client, $code) {
			return $client->selectFromView($this->_getSelectionParameters($code));
		}
		
		private function _getSelectionParameters($code) {
			return (new SelectFromViewRequestParameters())
				->setViewName('CIEL_WebShop_ProductLots')
				->setQuery(array(
					'Where' => array(array(
						'Column' => 'TargetArticleCode',
						'Operator' => '=',
						'Value' => $code
					), array(
						'Column' => 'StockWarehouseId',
						'Operator' => '=',
						'Value' => $this->_getWarehouseId()
					))
				));
		}

		private function _convertBatchesToCommonFormat(array $batches) {
			$result = array();
			foreach ($batches as $b) {
				$result[] = array(
					'lot_id' => $b['LotId'],
					'lot_code' => $b['LotCode'],
					'lot_name' => $b['LotName'],
					'is_default' => $b['IsDefaultLot'],
					'lot_stock_quantity' => $b['StockQuantityForLot']
				);
			}
			return $result;
		}

		/**
		 * @return \Ciel\Api\CielClient 
		 */
		private function _getCielClientAndLogon() {
			return $this->_storeBinding->getAmbientCielClient(true);
		}

		private function _getWarehouseId() {
			return $this->_storeBinding->getWarehouseId();
		}
	}
}
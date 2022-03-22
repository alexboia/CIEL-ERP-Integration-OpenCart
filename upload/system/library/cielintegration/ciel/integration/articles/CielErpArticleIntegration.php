<?php
namespace Ciel\Api\Integration\Articles {

	use Ciel\Api\Data\ArticleSelectionType;
	use Ciel\Api\Data\LocalProductType;
	use Ciel\Api\Data\MatchConfidence;
	use Ciel\Api\Exception\LocalArticleNotFoundException;
	use Ciel\Api\Exception\RemoteArticleNotFoundException;
	use Ciel\Api\Integration\Articles\Providers\CielErpLocalArticleAdapter;
    use Ciel\Api\Integration\Articles\Providers\DefaultCielErpArticleBatchInformationProvider;
    use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
    use Ciel\Api\Request\Parameters\GetAllArticlesRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllStocksForArticlesRequestParameters;
	use Ciel\Api\Request\Parameters\GetArticleByCodeRequestParameters;
	use Ciel\Api\Request\Parameters\GetArticleByIdRequestParameters;
	use Ciel\Api\Request\Parameters\GetStocksForArticleByCodeRequestParameters;
	use Exception;
	use InvalidArgumentException;

	class CielErpArticleIntegration {
		/**
		 * @var \Ciel\Api\Integration\Binding\CielErpToStoreBinding
		 */
		private $_storeBinding;

		/**
		 * 
		 * @var \Ciel\Api\Integration\Articles\Providers\CielErpLocalArticleAdapter
		 */
		private $_adapter;

		/**
		 * @var \Ciel\Api\Integration\Articles\Providers\DefaultCielErpArticleBatchInformationProvider
		 */
		private $_batchInformationProvider;

		private $_excludeCodesFromRemote = array(
			'SYSTEM_DISCOUNT_CORRECTION',
			'AJUSTARE_VALORI_DOC',
			'SYSTEM_COMMERCIAL_DISCOUNT',
			'SYSTEM_FINANCIAL_DISCOUNT',
			'SYSTEM_ADVANCE_PAYMENTS_CORPORATE',
			'SYSTEM_ADVANCE_PAYMENTS_NONCORPORATE',
			'CES_SHIPPING',
			'CES_DISCOUNT_19',
			'CES_DISCOUNT_20',
			'CES_DISCOUNT_9',
			'CES_DISCOUNT_5',
			'CES_DISCOUNT_24'
		);

		public function __construct(CielErpToStoreBinding $storeBinding, 
			CielErpLocalArticleAdapter $adapter) {
			$this->_storeBinding = $storeBinding;
			$this->_batchInformationProvider = new DefaultCielErpArticleBatchInformationProvider($storeBinding);
			$this->_adapter = $adapter;
		}

		/**
		 * @return \Ciel\Api\CielClient 
		 */
		private function _getCielClientAndLogon() {
			return $this->_storeBinding->getAmbientCielClient(true);
		}

		public function connectArticleWithRemoteDataOrId($localId, $remoteDataOrId) {
			$remoteData = null;

			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			if (empty($remoteDataOrId)) {
				throw new InvalidArgumentException('Remote data or Id must not be empty');
			}

			//If this is not an array, convert it to int 
			//  and see if it corresponds to a remote article
			if (!is_array($remoteDataOrId)) {
				$articleId = intval($remoteDataOrId);
				if ($articleId > 0) {
					//If remote data not found, quit and throw
					$remoteData = $this->_getRemoteArticleById($articleId);
					if (empty($remoteData)) {
						throw new RemoteArticleNotFoundException('id', $articleId);
					}
				} else {
					throw new InvalidArgumentException('Remote Id must not be empty');
				}
			} else {
				$remoteData = $remoteDataOrId;
			}

			//Connect local article with the remote data
			$this->_adapter->connectArticleAndUpdateWithRemoteData($localId, 
				$remoteData);
		}

		public function tryAutoConnectArticleByLocalCode($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			$connectedLocalIds = array();
			$localCode = $this->_adapter
				->lookupLocalArticleCode($localId);

			if (empty($localCode)) {
				throw new LocalArticleNotFoundException('id', $localId);
			}

			try {
				foreach ($localCode as $pId => $c) {
					$remoteData = $this->_getRemoteArticleByCode($c);
					if (!empty($remoteData)) {
						$this->_adapter->connectArticleAndUpdateWithRemoteData($pId, $remoteData);
						$connectedLocalIds[] = $pId;
					} else {
						throw new RemoteArticleNotFoundException('code', $c);
					}
				}
			} catch (Exception $exc) {
				foreach ($connectedLocalIds as $pId) {
					$this->_adapter->disconnectArticle($pId);
				}
				throw $exc;
			}
		}

		public function tryAutoConnectArticlesByLocalCodes($localIds) {
			if (empty($localIds)) {
				throw new InvalidArgumentException('Local Ids must not be empty');
			}

			$connected = array();
			$notFound = array();
			$localCodes = $this->_lookupLocalArticlesCodes($localIds);
			$remoteArticles = $this->_getAllFilteredRemoteArticles(true, true);
			
			foreach ($localCodes as $pId => $c) {
				$remoteData = !empty($remoteArticles[$c]) 
					? $remoteArticles[$c] 
					: null;

				if ($remoteData != null) {
					if (!$this->isArticleConnected($pId)) {
						$this->_adapter->connectArticleAndUpdateWithRemoteData($pId, $remoteData);
						$connected[] = array(
							'id' => $pId,
							'code' => $c
						);
					}
				} else {
					$notFound[] = array(
						'id' => $pId,
						'code' => $c
					);
				}
			}

			return array(
				'connected' => $connected,
				'notFound' => $notFound
			);
		}

		private function _lookupLocalArticlesCodes($localIds) {
			$localCodes = array(); 
			foreach ($localIds as $localId) {
				$localCodesForId = $this->_adapter->lookupLocalArticleCode($localId);
				foreach ($localCodesForId as $pId => $c) {
					$localCodes[$pId] = $c;
				}
			}
			return $localCodes;
		}

		public function canBeMatchedByLocalCode($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			//To see if an article can be matched by local code,
			//  it needs to have one, so see if it, indeed, does
			$localCode = $this->_adapter
				->lookupLocalArticleCode($localId);

			return !empty($localCode);
		}

		public function updateArticleFromRemoteSource($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			//Article must be already connected
			//  so if it's not, simply do nothing
			if ($this->isArticleConnected($localId)) {
				$localCode = $this->_adapter
					->lookupLocalArticleCode($localId);
				
				foreach ($localCode as $pId => $c) {
					$remoteData = $this->_getRemoteArticleByCode($c);
					if (empty($remoteData)) {
						throw new RemoteArticleNotFoundException('code', $c);
					}
					$this->_adapter->updateArticleFromRemoteData($pId, $remoteData);
				}
			}
		}

		public function importArticle($remoteDataOrId) {
			if (empty($remoteDataOrId)) {
				throw new InvalidArgumentException('Remote Id must not be empty');
			}

			if (!is_array($remoteDataOrId)) {
				$articleId = intval($remoteDataOrId);
				if ($articleId > 0) {
					$remoteData = $this->_getRemoteArticleById($articleId);
					if (empty($remoteData)) {
						throw new RemoteArticleNotFoundException('id', $articleId);
					}
				} else {
					throw new InvalidArgumentException('Remote Id must not be empty');
				}
			} else {
				$remoteData = $remoteDataOrId;
			}

			return $this->_adapter
				->createArticleFromRemoteData($remoteData);
		}

		public function lookupLocalArticleCode($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			return $this->_adapter
				->lookupLocalArticleCode($localId);
		}

		public function getDefaultArticleExportParameters() {
			return new CielErpArticleExportParameters();
		}

		public function exportArticlesForCielImport(CielErpArticleExportParameters $parameters) {
			$localArticles = $this->_getArticlesForExport();
			$exporter = new CielImportArticlesExporter($localArticles, $parameters);
			return $exporter->makeCsv();
		}

		private function _getArticlesForExport() {
			return $this->_adapter->getAllLocalArticlesForExport();
		}

		public function tryDetermineNewRemoteProducts() {
			$maybeNewRemoteProducts = array();

			$localArticleCodes = $this->_getAllLocalArticleCodes();
			$remoteArticles = $this->_getAllFilteredRemoteArticles(true, true);

			foreach ($remoteArticles as $remoteArticle) {
				if ($this->_shouldExcludeRemoteArticleFromProcessing($remoteArticle)) {
					continue;
				}

				$remoteArticleCode = $remoteArticle['Code'];
				if (empty($localArticleCodes[$remoteArticleCode])) {
					continue;
				}

				$maybeNewRemoteProducts[] = array(
					'id' => $remoteArticle['Id'],
					'code' => $remoteArticleCode,
					'name' => $remoteArticle['Name'],
					'category' => isset($remoteArticle['ArticleCategoryName'])
						? $remoteArticle['ArticleCategoryName']
						: null
				);
			}

			return $maybeNewRemoteProducts;
		}

		private function _getAllLocalArticleCodes() {
			$localArticleCodes = array();
			$localArticles = $this->_adapter->getAllLocalArticles();

			foreach ($localArticles as $localArticle) {
				$code = $localArticle['code'];
				$localArticleCodes[$code] = $code;
			}

			return $localArticleCodes;
		}

		public function tryMatchAllArticles($similarityMatchThreshold = 0.25) {
			$matchVariations = $this->_storeBinding
				->getMatchArticleVariations();

			$warehouseCode = $this->_storeBinding
				->getWarehouseCode(); 

			$result = array(
				'local' => array(),
				'remote' => array(),
				'matched' => array(),
				'unmatched' => array(
					'local' => array(),
					'remote' => array()
				)
			);

			$remoteArticles = $this
				->_getAllRemoteArticles();

			$localArticles = $this->_adapter
				->getAllLocalArticles();

			$processLocalArticles = $this->_getLocalArticlesToProcess($localArticles, 
				$matchVariations);

			$processRemoteArticles = $this->_getRemoteArticlesToProcess($remoteArticles, 
				$processLocalArticles['connected'], 
				$warehouseCode);

			unset($localArticles);
			unset($remoteArticles);

			$result['local'] = $processLocalArticles['notConnected'];
			$result['remote'] = $processRemoteArticles;

			foreach ($processLocalArticles['notConnected'] as $localArticle) {
				$foundRemoteKey = null;
				$foundRemoteArticle = null;
				$confidence = null;
				
				$code = strtolower(trim($localArticle['code']));
				$name = strtolower(trim($localArticle['name']));

				foreach ($processRemoteArticles as $remoteKey => $remoteArticle) {
					$remoteCode = strtolower(trim($remoteArticle['Code']));
					$remoteName = strtolower(trim($remoteArticle['Name']));

					if ($code == $remoteCode || $name == $remoteName) {
						$foundRemoteKey = $remoteKey;
						$foundRemoteArticle = $remoteArticle;
						$confidence = MatchConfidence::Exact;
						break;
					}
				}

				if ($foundRemoteKey !== null) {
					unset($processRemoteArticles[$foundRemoteKey]);
					$result['matched'][] = array(
						'local' => $localArticle['id'],
						'remote' => $foundRemoteArticle['Id'],
						'confidence' => $confidence
					);
				} else {
					$result['unmatched']['local'][] = $localArticle['id'];
				}
			}

			foreach ($processRemoteArticles as $remoteArticle) {
				$result['unmatched']['remote'][] = $remoteArticle['Id'];
			}

			return $result;
		}

		private function _getRemoteArticlesToProcess($remoteArticles, 
			$alreadyConnectedLocalArticles, 
			$warehouseCode) {
			$processRemoteArticles = array();

			foreach ($remoteArticles as $remoteArticle) {
				if ($this->_shouldExcludeRemoteArticleFromProcessing($remoteArticle)) {
					continue;
				}

				$salePriceForWarehouse = $this->_getArticleSalePriceForWarehouse($remoteArticle, 
					$warehouseCode);
				$stockForWarehouse = $this->_getArticleStockForWarehouse($remoteArticle,
					$warehouseCode);

				if (!empty($salePriceForWarehouse)) {
					$remoteArticle['ArticlesWarehousesPrices'] = 
						array($salePriceForWarehouse);
				} else {
					$remoteArticle['ArticlesWarehousesPrices'] = 
						array();
				}

				if (!empty($stockForWarehouse)) {
					$remoteArticle['ArticlesWarehouseStocks'] = 
						array($stockForWarehouse);
				} else {
					$remoteArticle['ArticlesWarehouseStocks'] =
						array();
				}

				$processRemoteArticles[$remoteArticle['Id']] = $remoteArticle;
			}

			foreach ($alreadyConnectedLocalArticles as $localArticle) {
				unset($processRemoteArticles[$localArticle['remoteId']]);
			}

			return $processRemoteArticles;
		}

		private function _shouldExcludeRemoteArticleFromProcessing($remoteArticle) {
			return in_array($remoteArticle['Code'], 
				$this->_excludeCodesFromRemote);
		}

		private function _getLocalArticlesToProcess($localArticles, $matchVariations) {
			$processLocalArticles = array(
				'connected' => array(),
				'notConnected' => array()
			);

			foreach ($localArticles as $article) {
				if ($this->_isArticleConnected($article)) {
					$processLocalArticles['connected'][$article['id']] = $article;
					continue;
				}

				if ($article['type'] == LocalProductType::Variable && $matchVariations) {
					foreach ($article['variations'] as $variation) {
						if ($this->_isArticleConnected($variation)) { 
							$processLocalArticles['connected'][$variation['id']] = $variation;
						} else {
							$processLocalArticles['notConnected'][$variation['id']] = $this->_getVariationWithParentData($variation, $article);
						}
					}
				} else {
					$processLocalArticles['notConnected'][$article['id']] = $article;
				}
			}

			return $processLocalArticles;
		}

		private function _isArticleConnected(array $article) {
			return !empty($article['remoteId']) && $article['remoteId'] > 0;
		}

		private function _getVariationWithParentData(array $variation, array $parent) {
			return array_merge($variation, array(
				'parent' => $this->_getLocalArticleDataWithoutVariations($parent)
			));
		}

		private function _getLocalArticleDataWithoutVariations($localArticle) {
			return array_filter($localArticle, 
				function($key) { 
					return $key != 'variations';
				}, 
				ARRAY_FILTER_USE_KEY);
		}

		public function updateStockForArticle($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Local article id must not be empty');
			}

			$results = array();
			if ($this->isArticleConnected($localId)) {
				$localCode = $this->_adapter
					->lookupLocalArticleCode($localId);
				
				foreach ($localCode as $pId => $c) {
					$stocks = $this->_getStocksForSingleArticleByCode($c);
					$results[$c] = $this->_adapter
						->updateStocksForConnectedArticle($pId, $stocks);
				}

				return $results;
			}

			return $results;
		}

		public function updateStockForArticles(array $localIds) {
			$results = array();

			foreach ($localIds as $localId) {
				try {
					$result = array(
						'result' => $this->updateStockForArticle($localId),
						'error' => null
					);
				} catch (Exception $exc) {
					$result = array(
						'result' => null,
						'error' => $exc
					);
				}

				$results[$localId] = $result;
			}

			return $results;
		}

		public function updateStocksForAllArticles() {
			$stocks = $this->_getFilteredStocksForAllArticles();
			return $this->_adapter->updateStocksForAllConnectedArticles($stocks);
		}

		private function _getFilteredStocksForAllArticles($filterWarehouse = true) {
			$stocks = array();
			$stocksRaw = $this->_getStocksForAllArticles();

			if (!empty($stocksRaw)) {
				$currentWhCode = trim($this->_storeBinding
					->getWarehouseCode());

				foreach ($stocksRaw as $stockItem) {
					$code = trim($stockItem['ArticleCode']);
					$whCode = trim($stockItem['WarehouseCode']);

					if (!$filterWarehouse || $currentWhCode == $whCode) {
						$stocks[$code] = $stockItem;
					}
				}

				return $stocks;
			} else {
				return array();
			}
		}

		private function _getStocksForAllArticles() {
			$client = $this->_getCielClientAndLogon();
			$params = (new GetAllStocksForArticlesRequestParameters())
				->setArticleType(ArticleSelectionType::StorableOrServices);

			return $client->getAllStocksForArticles($params);
		}

		public function updateInformationForAllArticles() {
			$remoteArticles = $this->_getAllFilteredRemoteArticles(true, true);
			return $this->_adapter->updateAllConnectedArticlesFromRemoteData($remoteArticles);
		}

		private function _getAllFilteredRemoteArticles($filterPrices, $filterStocks) {
			$articles = array();
			$rawArticles = $this->_getAllRemoteArticles();

			foreach ($rawArticles as $article) {
				$code = trim($article['Code']);
				$articles[$code] = $this->_filterRemoteArticle($article, 
					$filterPrices, 
					$filterStocks);
			}

			return $articles;
		}

		public function isArticleConnected($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Local article Id must not be empty');
			}

			$connected = true;
			$localCode = $this->_adapter
				->lookupLocalArticleCode($localId);

			if (!empty($localCode)) {
				foreach ($localCode as $pId => $c) {
					$connected = $connected && $this->_adapter->isArticleConnected($pId);
				}
			} else {
				$connected = false;
			}

			return $connected;
		}

		public function disconnectAllArticles() {
			$this->_adapter->disconnectAllArticles();
		}

		public function disconnecteArticleByLocalId($localId) {
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}
			$this->_adapter->disconnectArticle($localId);
		}

		private function _getAllRemoteArticles() {
			$client = $this->_getCielClientAndLogon();
			$params = (new GetAllArticlesRequestParameters())
				->setIncludeArticleWarehousePrice(true)
				->setIncludeArticleWarehouseStock(true)
				->setArticleType(ArticleSelectionType::StorableOrServices);

			$remoteArticles = $client
				->getAllArticles($params);

			return $remoteArticles;
		}

		private function _getStocksForSingleArticleByCode($code, $filterWarehouse = true) {
			$client = $this->_getCielClientAndLogon();
			$params = (new GetStocksForArticleByCodeRequestParameters())
				->setCode($code);

			$stocks = null;
			$stocksRaw = $client->getStocksForArticlesByCode($params);

			if (!empty($stocksRaw)) {
				$currentWhCode = trim($this->_storeBinding
					->getWarehouseCode());

				foreach ($stocksRaw as $stockItem) {
					$whCode = trim($stockItem['WarehouseCode']);
					if (!$filterWarehouse || $currentWhCode == $whCode) {
						$stocks = $stockItem;
						break;
					}
				}
			}

			return $stocks;
		}

		private function _getRemoteArticleById($remoteId, $filterPrices = true, $filterStocks = true) {
			$params = (new GetArticleByIdRequestParameters())
				->setId($remoteId)
				->setIncludeArticleWarehousePrice(true)
				->setIncludeArticleWarehouseStock(true);

			$client = $this->_getCielClientAndLogon();
			$article = $client->getArticleById($params);

			if (!empty($article)) {
				return $this->_filterRemoteArticle($article, 
					$filterPrices, 
					$filterStocks);
			} else {
				return null;
			}
		}

		private function _getRemoteArticleByCode($remoteCode, $filterPrices = true, $filterStocks = true) {
			$params = (new GetArticleByCodeRequestParameters())
				->setCode($remoteCode)
				->setIncludeArticleWarehousePrice(true)
				->setIncludeArticleWarehouseStock(true);

			$client = $this->_getCielClientAndLogon();
			$article = $client->getArticleByCode($params);

			if (!empty($article)) {
				return $this->_filterRemoteArticle($article, 
					$filterPrices, 
					$filterStocks);
			} else {
				return null;
			}
		}

		private function _filterRemoteArticle($article, $filterPrices, $filterStocks) {
			$currentWhCode = trim($this->_storeBinding
				->getWarehouseCode());

			if ($filterStocks) {
				$stocks = $this->_getArticleStockForWarehouse($article, $currentWhCode);
				$article['ArticlesWarehouseStocks'] = $stocks != null 
					? array($stocks) 
					: array();
			}

			if ($filterPrices) {
				$prices = $this->_getArticleSalePriceForWarehouse($article, $currentWhCode);
				$article['ArticlesWarehousesPrices'] = $prices != null
					? array($prices)
					: array();
			}

			return $article;
		}

		private function _getArticleSalePriceForWarehouse($article, $warehouseCode) {
			$salePrice = null;
			$outPrices = isset($article['ArticlesWarehousesPrices']) 
				? $article['ArticlesWarehousesPrices'] 
				: null;

			if (!empty($outPrices) && is_array($outPrices)) {
				foreach ($outPrices as $op) {
					if ($op['WarehouseCode'] == $warehouseCode && $op['ArticleId'] == $article['Id']) {
						$salePrice = $op;
						break;
					}
				}
			}

			return $salePrice;
		}

		private function _getArticleStockForWarehouse($article, $warehouseCode) {
			$stock = null;
			$stocks = isset($article['ArticlesWarehouseStocks']) 
				? $article['ArticlesWarehouseStocks'] 
				: null;

			if (!empty($stocks) && is_array($stocks)) {
				foreach ($stocks as $s) {
					if ($s['WarehouseCode'] == $warehouseCode && $s['ArticleId'] == $article['Id']) {
						$stock = $s;
						break;
					}
				}
			}

			return $stock;
		}

		public function getBatchTrackingStatusForSingleArticle($localId) {
			if (empty($localId)) {
                throw new InvalidArgumentException('Local article id must not be empty');
            }

			$result = array();
			$localCode = $this->_adapter
				->lookupLocalArticleCode($localId);

			if (!empty($localCode)) {
				foreach ($localCode as $pId => $c) {
					$batches = array();
					$batchTrackingEnabled =  $this->_adapter->getBatchTrackingStatusForSingleArticle($pId);

					if ($batchTrackingEnabled) {
						$batches = $this->_getBatchesForArticleCode($c);
					}

					$result[$c] = array(
						'enabled' => $batchTrackingEnabled,
						'batches' => $batches
					);
				}
			}

			return $result;
		}

		private function _getBatchesForArticleCode($code) {
			return $this->_batchInformationProvider->getBatchesForArticle($code);
		}
	}
}